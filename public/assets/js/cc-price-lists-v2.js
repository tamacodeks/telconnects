(function (window, $) {
  "use strict";

  if (!$) {
    return;
  }

  var config = window.ccPriceListsV2 || {};
  var labels = config.labels || {};
  var request = null;
  var table = null;
  var retailerState = {
    page: 1,
    length: 12,
    total: 0,
    lastPage: 1,
    search: ""
  };

  function showLoading(active) {
    $(".ccpl-v2").toggleClass("is-loading", !!active);
    if ($.fn.LoadingOverlay) {
      $("#ccplV2Loader").LoadingOverlay(active ? "show" : "hide");
    }
  }

  function notify(type, message, callback) {
    var content = message || labels.updateFailed || "Request failed.";
    if ($.alert) {
      $.alert({
        content: content,
        type: type || "blue",
        autoClose: (labels.close || "Close") + "|4000",
        buttons: {
          close: {
            text: labels.close || "Close",
            action: callback || function () {}
          }
        }
      });
      return;
    }

    window.alert(content);
    if (callback) {
      callback();
    }
  }

  function initPopovers() {
    if ($.fn.popover) {
      $('[data-toggle="popover"]').popover({
        container: "body",
        trigger: "hover"
      });
    }
  }

  function formatCurrencyValue(value) {
    var number = parseFloat(value);
    if (Number.isNaN(number)) {
      return value === null || typeof value === "undefined" ? "" : value;
    }
    return number.toFixed(2);
  }

  function escapeHtml(value) {
    return $("<div>").text(value === null || typeof value === "undefined" ? "" : value).html();
  }

  function formatRetailPrice(value) {
    var symbol = labels.currencySymbol || "€";
    var text = value === null || typeof value === "undefined" ? "" : String(value).trim();
    if (text.indexOf(symbol) === 0 || /^[€$£]/.test(text)) {
      return text;
    }
    var amount = formatCurrencyValue(value);
    return symbol + amount;
  }

  function providerTone(value) {
    var key = String(value || "").toLowerCase();
    var tones = [
      "sfr",
      "orange",
      "lebara",
      "lyca",
      "syma",
      "vectone",
      "bouygues",
      "auchan",
      "transcash",
      "pcs"
    ];

    for (var i = 0; i < tones.length; i += 1) {
      if (key.indexOf(tones[i]) !== -1) {
        return tones[i];
      }
    }

    return "default";
  }

  function cardInitial(value) {
    var text = String(value || "").trim();
    return text ? text.charAt(0).toUpperCase() : "C";
  }

  function textFromHtml(value) {
    if (value === null || typeof value === "undefined") {
      return "";
    }
    return $("<div>").html(String(value)).text().trim();
  }

  function idSuffixFromHtml(value, prefix) {
    var pattern = new RegExp('id=["\\\']' + prefix + '([^"\\\']+)["\\\']');
    var match = String(value || "").match(pattern);
    return match ? match[1] : "";
  }

  window.isNumberKey = function (evt, id) {
    var charCode = evt.which ? evt.which : evt.keyCode;

    if (charCode === 46) {
      var input = document.getElementById(id);
      return input && input.value.indexOf(".") === -1;
    }

    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
      return false;
    }

    return true;
  };

  window.validateSalePrice = function (element) {
    var current = $(element);
    var id = current.attr("id");
    var buttonId = id ? id.replace("sp_", "btn_") : "";
    var max = parseFloat(current.data("max"));
    var value = parseFloat(current.val());

    current.next("span.help-block").remove();

    if (!Number.isNaN(max) && !Number.isNaN(value) && max < value) {
      $("#" + buttonId).attr("disabled", "disabled");
      current.closest(".form-group").addClass("has-error");
      $('<span class="help-block error"></span>')
        .text((labels.amountMust || "Amount must be less than ") + formatRetailPrice(max))
        .insertAfter(current);
      return false;
    }

    $("#" + buttonId).removeAttr("disabled");
    current.closest(".form-group").removeClass("has-error");
    return true;
  };

  window.updateSalePrice = function (rateTableId, salePrice) {
    if (!rateTableId) {
      return false;
    }

    if (request) {
      request.abort();
    }

    showLoading(true);
    request = $.ajax({
      url: config.updateUrl,
      type: "POST",
      headers: {
        "X-CSRF-TOKEN": config.csrfToken || $('meta[name="csrf-token"]').attr("content")
      },
      data: {
        rate_table_id: rateTableId,
        sale_price: salePrice
      }
    });

    request.done(function (response) {
      var payload = response && response.data ? response.data : {};
      var type = String(payload.code) === "400" ? "orange" : "green";
      var message = payload.message || (String(payload.code) === "400" ? labels.updateFailed : "");

      if (payload.result) {
        $("#sp_" + rateTableId).val(payload.result.sale_price);
        $("#sm_" + rateTableId).html(formatRetailPrice(payload.result.sale_margin));
      }

      notify(type, message, function () {
        setTimeout(function () {
          $("#sp_" + rateTableId).trigger("focus");
        }, 200);
      });
    });

    request.fail(function () {
      notify("red", labels.updateFailed);
    });

    request.always(function () {
      showLoading(false);
    });

    return false;
  };

  function tableColumns() {
    var numberColumn = {
      data: null,
      orderable: false,
      searchable: false,
      render: function (data, type, row, meta) {
        return meta.row + meta.settings._iDisplayStart + 1;
      }
    };

    if (config.mode === "retailer") {
      return [
        numberColumn,
        { data: "name", name: "calling_cards.name" },
        { data: "description", name: "description", searchable: false, orderable: false },
        {
          data: "sale_price",
          name: "rate_tables.sale_price",
          searchable: false,
          render: function (data) {
            return '<strong class="ccpl-v2-price">' + escapeHtml(formatRetailPrice(data)) + "</strong>";
          }
        }
      ];
    }

    return [
      numberColumn,
      { data: "name", name: "calling_cards.name" },
      { data: "description", name: "calling_cards.description", searchable: false, orderable: false },
      {
        data: "buying_price",
        name: "buying_price",
        searchable: false,
        orderable: false,
        render: function (data) {
          return '<strong class="ccpl-v2-price">' + escapeHtml(formatRetailPrice(data)) + "</strong>";
        }
      },
      { data: "sale_price", name: "sale_price", searchable: false, orderable: false },
      {
        data: "sale_margin",
        name: "sale_margin",
        searchable: false,
        orderable: false,
        render: function (data, type, row) {
          var id = row && row.id ? row.id : idSuffixFromHtml(data, "sm_");
          var value = textFromHtml(data) || data;
          return '<span id="sm_' + escapeHtml(id) + '">' + escapeHtml(formatRetailPrice(value)) + "</span>";
        }
      },
      { data: "action", name: "action", searchable: false, orderable: false }
    ];
  }

  function renderRetailerEmpty() {
    $("#ccplV2RetailerList").html(
      '<div class="ccpl-v2-empty-state">' +
        '<span class="ccpl-v2-empty-icon"><i class="fa fa-search"></i></span>' +
        '<strong>' + escapeHtml(labels.noPrices || "No prices found") + "</strong>" +
        '<p>' + escapeHtml(labels.noPricesHint || "Try another search term or refresh the list.") + "</p>" +
      "</div>"
    );
  }

  function renderRetailerCards(rows) {
    if (!rows || !rows.length) {
      renderRetailerEmpty();
      return;
    }

    var html = rows.map(function (row) {
      var providerName = row.provider_name || row.name || "";
      var tone = providerTone(row.provider_key || providerName);
      var initial = row.initial || cardInitial(row.name);

      return (
        '<article class="ccpl-v2-price-card ccpl-v2-provider-' + escapeHtml(tone) + '">' +
          '<div class="ccpl-v2-price-card-main">' +
            '<span class="ccpl-v2-price-card-avatar" aria-hidden="true">' +
              escapeHtml(initial) +
            "</span>" +
            '<div class="ccpl-v2-price-card-copy">' +
              '<span class="ccpl-v2-provider-chip">' + escapeHtml(providerName || "Calling card") + "</span>" +
              '<h4>' + escapeHtml(row.name) + "</h4>" +
              '<p>' + escapeHtml(row.description_short || row.description || "") + "</p>" +
            "</div>" +
          "</div>" +
          '<div class="ccpl-v2-price-card-amount">' +
            '<span>' + escapeHtml(labels.priceLabel || "Sale price") + "</span>" +
            '<strong>' + escapeHtml(formatRetailPrice(row.sale_price)) + "</strong>" +
          "</div>" +
        "</article>"
      );
    }).join("");

    $("#ccplV2RetailerList").html(html);
  }

  function renderRetailerPagination(meta) {
    meta = meta || {};
    retailerState.page = parseInt(meta.page || 1, 10);
    retailerState.length = parseInt(meta.length || retailerState.length, 10);
    retailerState.total = parseInt(meta.total || 0, 10);
    retailerState.lastPage = parseInt(meta.last_page || 1, 10);

    var from = retailerState.total ? ((retailerState.page - 1) * retailerState.length) + 1 : 0;
    var to = retailerState.total ? Math.min(retailerState.page * retailerState.length, retailerState.total) : 0;
    var status = labels.pageStatus || "Showing :from-:to of :total prices";
    status = status
      .replace(":from", from)
      .replace(":to", to)
      .replace(":total", retailerState.total);

    $("#ccplV2RetailerStatus").text(status);

    var pagerHtml = "";
    var canPrev = retailerState.page > 1;
    var canNext = retailerState.page < retailerState.lastPage;
    var start = Math.max(1, retailerState.page - 2);
    var end = Math.min(retailerState.lastPage, start + 4);
    start = Math.max(1, end - 4);

    pagerHtml += '<button type="button" class="ccpl-v2-page-btn" data-page="' + (retailerState.page - 1) + '"' + (canPrev ? "" : " disabled") + ">" + escapeHtml(labels.previous || "Previous") + "</button>";
    for (var page = start; page <= end; page += 1) {
      pagerHtml += '<button type="button" class="ccpl-v2-page-btn' + (page === retailerState.page ? " is-active" : "") + '" data-page="' + page + '">' + page + "</button>";
    }
    pagerHtml += '<button type="button" class="ccpl-v2-page-btn" data-page="' + (retailerState.page + 1) + '"' + (canNext ? "" : " disabled") + ">" + escapeHtml(labels.next || "Next") + "</button>";

    $("#ccplV2RetailerPager").html(pagerHtml);
  }

  function loadRetailerPrices(page) {
    if (request) {
      request.abort();
    }

    showLoading(true);
    request = $.ajax({
      url: config.fetchUrl,
      type: "GET",
      data: {
        page: page || retailerState.page,
        length: retailerState.length,
        search: retailerState.search
      }
    });

    request.done(function (response) {
      renderRetailerCards(response && response.data ? response.data : []);
      renderRetailerPagination(response && response.meta ? response.meta : {});
    });

    request.fail(function () {
      renderRetailerEmpty();
      renderRetailerPagination({
        page: 1,
        length: retailerState.length,
        total: 0,
        last_page: 1
      });
    });

    request.always(function () {
      showLoading(false);
    });
  }

  function initRetailerPrices() {
    var searchTimer = null;

    retailerState.length = parseInt(config.length || 12, 10);
    loadRetailerPrices(1);

    $("#ccplV2Search").on("input", function () {
      var value = this.value;
      window.clearTimeout(searchTimer);
      searchTimer = window.setTimeout(function () {
        retailerState.search = value;
        loadRetailerPrices(1);
      }, 220);
    });

    $("#ccplV2FilterForm").on("submit", function (event) {
      event.preventDefault();
      window.clearTimeout(searchTimer);
      retailerState.search = $("#ccplV2Search").val() || "";
      loadRetailerPrices(1);
    });

    $("#ccplV2Refresh").on("click", function () {
      loadRetailerPrices(retailerState.page);
    });

    $("#ccplV2RetailerPager").on("click", ".ccpl-v2-page-btn", function () {
      var page = parseInt($(this).data("page"), 10);
      if (!page || page < 1 || page > retailerState.lastPage || page === retailerState.page) {
        return;
      }
      loadRetailerPrices(page);
    });
  }

  function initTable() {
    if (!$.fn.DataTable || !$("#ccplV2Table").length) {
      return;
    }

    table = $("#ccplV2Table").DataTable({
      autoWidth: false,
      searching: true,
      pageLength: 10,
      processing: true,
      serverSide: true,
      order: [],
      language: {
        processing: labels.processing || "Processing..."
      },
      ajax: {
        url: config.fetchUrl,
        data: function (data) {
          if (config.mode !== "retailer") {
            data.rate_table_group_id = $("#ccplV2RateGroup").val() || config.defaultGroupId || "";
          }
        }
      },
      columns: tableColumns(),
      dom: "Brt<'row align-items-center mt-3'<'col-md-5'i><'col-md-7'p>>",
      lengthMenu: [
        [10, 25, 50, -1],
        [labels.records10 || "10 records", labels.records25 || "25 records", labels.records50 || "50 records", labels.showAll || "Show all"]
      ],
      buttons: [
        "pageLength",
        {
          extend: "excel",
          text: '<i class="fa fa-file-excel"></i>',
          titleAttr: labels.export || "Export"
        },
        {
          text: '<i class="fa fa-sync-alt"></i>',
          titleAttr: labels.refresh || "Refresh",
          action: function () {
            table.ajax.reload(null, false);
          }
        }
      ],
      drawCallback: function () {
        initPopovers();
      }
    });
  }

  $(document).ready(function () {
    if (config.mode === "retailer") {
      initRetailerPrices();
      return;
    }

    if ($.fn.selectpicker) {
      $(".select-picker").selectpicker();
    }

    initTable();
    initPopovers();

    $("#ccplV2FilterForm").on("submit", function (event) {
      event.preventDefault();
      if (table) {
        table.ajax.reload();
      }
    });

    $("#ccplV2Search").on("input", function () {
      if (table) {
        table.search(this.value).draw();
      }
    });

    $("#ccplV2Refresh").on("click", function () {
      if (table) {
        table.ajax.reload(null, false);
      }
    });

    $("#ccplV2Export").on("click", function () {
      if (table && table.button) {
        table.button(".buttons-excel").trigger();
      }
    });
  });
})(window, window.jQuery);
