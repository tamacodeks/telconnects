(function ($) {
  "use strict";

  var config = window.usersV2 || {};
  var labels = config.labels || {};
  var table = null;

  function escapeHtml(value) {
    return String(value === null || typeof value === "undefined" ? "" : value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function stripHtml(value) {
    return $("<div>").html(value || "").text();
  }

  function rawDisplay(value, type) {
    return type === "display" ? (value || "") : stripHtml(value);
  }

  function actionTone($item) {
    var icon = String($item.find("i").attr("class") || "").toLowerCase();
    var href = String($item.attr("href") || "").toLowerCase();

    if (icon.indexOf("id-card") !== -1 || href.indexOf("/view/") !== -1) {
      return "view";
    }
    if (icon.indexOf("edit") !== -1 || href.indexOf("/update/") !== -1) {
      return "edit";
    }
    if (icon.indexOf("user-secret") !== -1 || href.indexOf("/impersonate/") !== -1) {
      return "login";
    }
    if (icon.indexOf("sync") !== -1 || href.indexOf("refresh") !== -1) {
      return "sync";
    }
    if (icon.indexOf("times") !== -1 || icon.indexOf("trash") !== -1 || href.indexOf("/remove/") !== -1) {
      return "delete";
    }

    return "default";
  }

  function actionCell(value, type) {
    if (type !== "display") {
      return stripHtml(value);
    }

    var $source = $("<div>").html(value || "");
    var html = [];

    $source.find("a,button").each(function () {
      var $item = $(this);
      if ($item.hasClass("hide")) {
        return;
      }

      var tag = this.tagName.toLowerCase() === "button" ? "button" : "a";
      var iconClass = $item.find("i").attr("class") || "fa fa-circle";
      var label = $.trim($item.attr("title") || $item.text() || labels.action || "Action");
      var tone = actionTone($item);
      var attrs = [
        'class="v2-users-action-btn v2-users-action-' + tone + '"',
        'title="' + escapeHtml(label) + '"',
        'aria-label="' + escapeHtml(label) + '"'
      ];

      if (tag === "a") {
        attrs.push('href="' + escapeHtml($item.attr("href") || "#") + '"');
        if ($item.attr("target")) {
          attrs.push('target="' + escapeHtml($item.attr("target")) + '"');
        }
        if ($item.attr("rel")) {
          attrs.push('rel="' + escapeHtml($item.attr("rel")) + '"');
        }
      } else {
        attrs.push('type="button"');
      }

      if ($item.attr("onclick")) {
        attrs.push('onclick="' + escapeHtml($item.attr("onclick")) + '"');
      }
      if ($item.attr("id")) {
        attrs.push('id="' + escapeHtml($item.attr("id")) + '"');
      }

      html.push("<" + tag + " " + attrs.join(" ") + '><i class="' + escapeHtml(iconClass) + '"></i></' + tag + ">");
    });

    return '<div class="v2-users-action-group">' + html.join("") + "</div>";
  }

  function numberColumn() {
    return {
      data: null,
      orderable: false,
      searchable: false,
      render: function (data, type, row, meta) {
        return meta.row + meta.settings._iDisplayStart + 1;
      }
    };
  }

  function detailColumn() {
    return {
      className: "details-control",
      orderable: false,
      searchable: false,
      data: null,
      defaultContent: "",
      width: "34px"
    };
  }

  function userCell(data, type, row) {
    if (type !== "display") {
      return data || "";
    }

    var meta = [];
    if (row && row.email) {
      meta.push(escapeHtml(row.email));
    }
    if (row && row.mobile) {
      meta.push(escapeHtml(row.mobile));
    }

    return '<span class="v2-users-user-cell"><strong>' + escapeHtml(data || "-") + "</strong>" +
      (meta.length ? "<span>" + meta.join(" &middot; ") + "</span>" : "") +
      "</span>";
  }

  function textCell(data, type) {
    if (type !== "display") {
      return data || "";
    }

    return '<span class="v2-users-text-cell"><strong>' + escapeHtml(data || "-") + "</strong></span>";
  }

  function mutedCell(data, type) {
    if (type !== "display") {
      return data || "";
    }

    return '<span class="v2-users-text-cell v2-users-text-cell--muted"><strong>' + escapeHtml(data || "-") + "</strong></span>";
  }

  function dateOnlyCell(data, type) {
    if (type !== "display") {
      return data || "";
    }

    return '<span class="v2-users-date-cell"><strong>' + escapeHtml(data || "-") + "</strong></span>";
  }

  function contactCell(data, type, row) {
    if (type !== "display") {
      return data || "";
    }

    var mobile = row && row.mobile ? row.mobile : "";
    return '<span class="v2-users-user-cell">' +
      '<strong>' + escapeHtml(data || "-") + "</strong>" +
      (mobile && mobile !== data ? "<span>" + escapeHtml(mobile) + "</span>" : "") +
      "</span>";
  }

  function compactUserCell(data, type, row) {
    if (type !== "display") {
      return data || "";
    }

    var pieces = [];
    if (row && row.cust_id) {
      pieces.push("#" + escapeHtml(row.cust_id));
    }
    if (row && row.email) {
      pieces.push(escapeHtml(row.email));
    }
    if (row && row.mobile) {
      pieces.push(escapeHtml(row.mobile));
    }

    return '<span class="v2-users-user-cell v2-users-user-cell--compact">' +
      '<strong>' + escapeHtml(data || "-") + "</strong>" +
      (pieces.length ? "<span>" + pieces.join(" &middot; ") + "</span>" : "") +
      "</span>";
  }

  function roleCell(data, type, row) {
    if (type !== "display") {
      return data || "";
    }

    var representative = row && row.representative ? row.representative : "";
    return '<span class="v2-users-role-cell"><strong>' + escapeHtml(data || "-") + "</strong>" +
      (representative ? "<span>" + escapeHtml(representative) + "</span>" : "") +
      "</span>";
  }

  function balanceCreditCell(data, type, row) {
    if (type !== "display") {
      return stripHtml(data);
    }

    return '<span class="v2-users-money-cell"><strong>' + escapeHtml(data || "-") + "</strong>" +
      '<span>' + escapeHtml(row && row.credit_limit ? row.credit_limit : "-") + "</span></span>";
  }

  function lastSeenCell(data, type, row) {
    if (type !== "display") {
      return data || "";
    }

    return '<span class="v2-users-date-cell"><strong>' + escapeHtml(data || "-") + "</strong>" +
      '<span>' + escapeHtml(row && row.created_at ? row.created_at : "") + "</span></span>";
  }

  function statusPill(value, tone) {
    return '<span class="v2-users-status v2-users-status-' + tone + '">' + escapeHtml(value || "-") + "</span>";
  }

  function displayStatus(data, type) {
    if (type !== "display") {
      return stripHtml(data);
    }

    var value = stripHtml(data);
    var key = value.toLowerCase();
    var activeLabel = String(labels.active || "Active").toLowerCase();
    var inactiveLabel = String(labels.inactive || "Inactive").toLowerCase();
    var enabledLabel = "enabled";
    var disabledLabel = "disabled";
    var clickedLabel = String(labels.clicked || "Clicked").toLowerCase();
    var notClickedLabel = String(labels.notClicked || "Not Clicked").toLowerCase();
    var tone = key === activeLabel || key === clickedLabel || key === enabledLabel || key === "active" || key === "clicked" ? "success" : "muted";
    if (key === inactiveLabel || key === notClickedLabel || key === disabledLabel || key === "inactive" || key === "not clicked" || key.indexOf("not") !== -1) {
      tone = "warning";
    }

    if (key === activeLabel || key === "active") {
      value = labels.active || value;
    } else if (key === inactiveLabel || key === "inactive") {
      value = labels.inactive || value;
    } else if (key === clickedLabel || key === "clicked") {
      value = labels.clicked || value;
    } else if (key === notClickedLabel || key === "not clicked") {
      value = labels.notClicked || value;
    } else if (!value) {
      value = labels.unknown || "-";
    }

    return statusPill(value || data, tone);
  }

  function authMethodBadge(row, type) {
    if (type === "display" && row && row.auth_method) {
      return row.auth_method;
    }

    var method = parseInt(row && row.method, 10);
    if (isNaN(method)) {
      method = 0;
    }

    if (type !== "display") {
      return method;
    }

    if (method === 1) {
      return '<span class="auth-method-badge auth-method-badge--otp" title="' + escapeHtml(labels.authIpOtpTitle || "OTP is required when the login IP changes") + '">' + escapeHtml(labels.authIpOtp || "1 - IP OTP") + "</span>";
    }

    if (method === 2) {
      return '<span class="auth-method-badge auth-method-badge--totp" title="' + escapeHtml(labels.authTotpTitle || "Authenticator 2FA is used when enabled and verified") + '">' + escapeHtml(labels.authTotp || "2 - 2FA") + "</span>";
    }

    return '<span class="auth-method-badge auth-method-badge--none" title="' + escapeHtml(labels.authNoneTitle || "No extra authentication step") + '">' + escapeHtml(labels.authNone || "0 - No Auth") + "</span>";
  }

  function columnsForType() {
    if (config.type === "users") {
      return [
        detailColumn(),
        { data: "username", name: "users.username", className: "v2-users-primary-col", render: compactUserCell },
        { data: "name", name: "user_groups.name", className: "v2-users-role-col", render: roleCell },
        { data: null, name: "users.method", orderable: true, searchable: false, className: "text-center v2-users-auth-col", render: function (data, type, row) { return authMethodBadge(row, type); } },
        { data: "v2_access", name: "users.v2_enabled", orderable: false, searchable: false, className: "text-center v2-users-v2-col", render: rawDisplay },
        { data: "balance", name: "users.balance", orderable: false, searchable: false, className: "text-end v2-users-money-col", render: balanceCreditCell },
        { data: "last_online_at", name: "users.last_activity", orderable: false, searchable: false, className: "v2-users-date-col", render: lastSeenCell },
        { data: "action", name: "users.action", orderable: false, searchable: false, className: "v2-users-actions-col", render: actionCell }
      ];
    }

    if (config.type === "user-info") {
      return [
        { data: null, orderable: false, searchable: false, className: "v2-users-sl-col", render: numberColumn().render },
        { data: "username", name: "users.username", className: "v2-users-primary-col", render: userCell },
        { data: "name", name: "user_groups.name", className: "v2-users-role-col", render: textCell },
        { data: "ip_address", name: "users.ip_address", className: "v2-users-ip-col", render: mutedCell },
        { data: "email", name: "users.email", className: "v2-users-contact-col", render: contactCell }
      ];
    }

    if (config.type === "all-users") {
      return [
        detailColumn(),
        { data: null, orderable: false, searchable: false, className: "v2-users-sl-col", render: numberColumn().render },
        { data: "username", name: "users.username", className: "v2-users-primary-col", render: userCell },
        { data: "name", name: "name", orderable: false, searchable: false, className: "v2-users-role-col", render: textCell },
        { data: "representative", name: "representative", orderable: false, searchable: false, className: "v2-users-rep-col", render: mutedCell },
        { data: "last_activity", name: "last_activity", orderable: false, className: "v2-users-date-col", render: dateOnlyCell },
        { data: "status", name: "status", orderable: false, searchable: false, className: "text-center v2-users-status-col", render: displayStatus },
        { data: "balance", name: "orders.balance", orderable: false, searchable: false, className: "text-end v2-users-money-col", render: textCell },
        { data: "credit_limit", name: "credit_limit", orderable: false, searchable: false, className: "text-end v2-users-money-col", render: mutedCell }
      ];
    }

    if (config.type === "refresh-popup") {
      return [
        { data: null, orderable: false, searchable: false, className: "v2-users-sl-col", render: numberColumn().render },
        { data: "parent_name", name: "parent_users.username", className: "v2-users-primary-col", render: textCell },
        { data: "parent_status", name: "parent_users.status", orderable: false, searchable: false, className: "text-center v2-users-status-col", render: displayStatus },
        { data: "username", name: "users.username", className: "v2-users-primary-col", render: userCell },
        { data: "status", name: "users.status", orderable: false, searchable: false, className: "text-center v2-users-status-col", render: displayStatus },
        { data: "last_activity", name: "users.last_activity", orderable: false, searchable: false, className: "v2-users-date-col", render: dateOnlyCell }
      ];
    }

    if (config.type === "user-groups") {
      return [
        detailColumn(),
        { data: null, orderable: false, searchable: false, className: "v2-users-sl-col", render: numberColumn().render },
        { data: "name", name: "name", className: "v2-users-primary-col", render: textCell },
        { data: "description", name: "description", orderable: false, searchable: false, className: "v2-users-desc-col", render: mutedCell },
        { data: "status", name: "status", orderable: false, searchable: false, className: "text-center v2-users-status-col", render: displayStatus },
        { data: "level_access", name: "level_access", orderable: false, searchable: false, className: "v2-users-level-col", render: mutedCell },
        { data: "created_at", name: "created_at", orderable: false, searchable: false, className: "v2-users-date-col", render: dateOnlyCell },
        { data: "updated_at", name: "updated_at", orderable: false, searchable: false, className: "v2-users-date-col", render: dateOnlyCell },
        { data: "action", name: "action", orderable: false, searchable: false, className: "v2-users-actions-col", render: actionCell }
      ];
    }

    return [numberColumn()];
  }

  function ajaxConfig() {
    if (config.type !== "all-users") {
      return config.fetchUrl;
    }

    return {
      url: config.fetchUrl,
      data: function (data) {
        data.parent_id = $("#usersV2ParentId").val();
        data.status = $("#usersV2Status").val();
      }
    };
  }

  function dataTableButtons() {
    return [
      "pageLength",
      {
        extend: "excel",
        text: '<i class="fa fa-file-excel"></i>',
        titleAttr: labels.downloadExcel || "Export"
      }
    ];
  }

  function initTable() {
    function updateFooterState() {
      var pageInfo = table && table.page ? table.page.info() : null;
      $("#usersV2PageLength").val(String(table && table.page ? table.page.len() : config.pageLength || 25));
      $("#usersV2Panel")
        .toggleClass("v2-users-single-page", !pageInfo || pageInfo.pages <= 1)
        .toggleClass("has-single-page", !pageInfo || pageInfo.pages <= 1);
    }

    table = $("#usersV2Table").DataTable({
      autoWidth: false,
      pageLength: typeof config.pageLength === "undefined" ? 25 : parseInt(config.pageLength, 10),
      processing: '<span class="loader"></span>',
      language: {
        processing: labels.processing || labels.loading || "Processing",
        info: labels.showingEntries || "Showing _START_ to _END_ of _TOTAL_ entries",
        infoEmpty: labels.showingEmpty || "No entries to show",
        infoFiltered: labels.filteredEntries || "(filtered from _MAX_ total entries)",
        emptyTable: labels.emptyTable || "No records found",
        zeroRecords: labels.zeroRecords || "No matching records found",
        paginate: {
          previous: labels.previous || "Previous",
          next: labels.next || "Next"
        }
      },
      serverSide: true,
      searching: config.type !== "all-users",
      ajax: ajaxConfig(),
      columns: columnsForType(),
      order: config.order || [],
      dom: "Brt<'v2-users-table-footer'<'v2-users-table-status'i><'v2-users-table-pager'p>>",
      lengthMenu: [
        [10, 25, 50, -1],
        [labels.records10 || "10 records", labels.records25 || "25 records", labels.records50 || "50 records", labels.showAll || "Show all"]
      ],
      buttons: dataTableButtons()
    });

    $("#usersV2PageLength").val(String(config.pageLength || 25)).on("change", function () {
      table.page.len(parseInt($(this).val(), 10)).draw();
    });

    table.on("draw.dt", updateFooterState);
    table.on("init.dt", updateFooterState);
    setTimeout(updateFooterState, 0);

    $("#usersV2Refresh").on("click", function () {
      table.ajax.reload(null, false);
    });

    $("#usersV2Export").on("click", function () {
      var excelButton = table.button(".buttons-excel");
      if (excelButton) {
        excelButton.trigger();
      }
    });
  }

  function detailValue(row, field) {
    var value = row ? row[field.key] : "";
    if (field.html) {
      return value || "-";
    }

    return escapeHtml(value || "-");
  }

  function detailHtml(row) {
    var fields = config.detailFields || [];
    if (!fields.length) {
      return "";
    }

    return '<div class="v2-users-detail-grid">' + fields.map(function (field) {
      return '<div class="v2-users-detail-item"><span>' + escapeHtml(field.label || field.key || "") + '</span><strong>' + detailValue(row, field) + "</strong></div>";
    }).join("") + "</div>";
  }

  function initDetails() {
    $("#usersV2Table tbody").on("click", "td.details-control", function () {
      var tr = $(this).closest("tr");
      var row = table.row(tr);

      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass("shown");
        return;
      }

      row.child(detailHtml(row.data())).show();
      tr.addClass("shown");
    });
  }

  function alertBox(type, message) {
    if (window.Swal) {
      Swal.fire({
        title: type === "success" ? (labels.success || "Success") : (labels.information || "Information"),
        html: message || "",
        icon: type === "success" ? "success" : "error"
      });
      return;
    }

    if ($.alert) {
      $.alert({
        title: labels.information || "Information",
        content: message || "",
        type: type === "success" ? "success" : "red",
        backgroundDismiss: true
      });
      return;
    }

    window.alert(stripHtml(message || ""));
  }

  function csrfHeaders() {
    return {
      "X-CSRF-TOKEN": config.csrfToken || $('meta[name="csrf-token"]').attr("content")
    };
  }

  function todayParts() {
    var today = new Date();
    var yyyy = today.getFullYear();
    var mm = String(today.getMonth() + 1).padStart(2, "0");
    var dd = String(today.getDate()).padStart(2, "0");
    return {
      label: yyyy + "-" + mm + "-" + dd,
      date: yyyy + "-" + mm + "-" + dd
    };
  }

  function initResetCorrections() {
    var modalElement = document.getElementById("usersV2ResetCorrectionsModal");
    var modal = null;

    if (modalElement && window.bootstrap && bootstrap.Modal) {
      modal = new bootstrap.Modal(modalElement);
    }

    $("#usersV2OpenResetCorrections").on("click", function () {
      var parts = todayParts();
      $("#usersV2ResetDateLabel").text(parts.label);
      $("#usersV2ResetFrom").val("00:00");
      $("#usersV2ResetTo").val("23:59");

      if (modal) {
        modal.show();
      } else {
        $(modalElement).modal("show");
      }
    });

    $("#usersV2RunResetCorrections").on("click", function () {
      if (!window.confirm(labels.resetCorrectionsPrompt || "Run reset corrections for today?")) {
        return;
      }

      $("#usersV2Panel").LoadingOverlay("show");
      $.ajax({
        url: config.runResetUrl,
        type: "POST",
        dataType: "json",
        headers: csrfHeaders(),
        complete: function () {
          $("#usersV2Panel").LoadingOverlay("hide");
        },
        error: function (jqXHR) {
          alertBox("error", (jqXHR.responseJSON && jqXHR.responseJSON.message) || "Unable to run reset corrections.");
        },
        success: function (data) {
          alertBox("success", data.message || labels.updated || "Updated.");
        }
      });
    });

    $("#usersV2SubmitResetCorrections").on("click", function () {
      var fromVal = $("#usersV2ResetFrom").val();
      var toVal = $("#usersV2ResetTo").val();
      var parts = todayParts();

      if (!fromVal || !toVal) {
          alertBox("error", labels.selectTimeRange || "Please select From and To time.");
        return;
      }

      $("#usersV2Panel").LoadingOverlay("show");
      $.ajax({
        url: config.resetUrl,
        type: "POST",
        dataType: "json",
        data: {
          from: parts.date + " " + fromVal + ":00",
          to: parts.date + " " + toVal + ":00"
        },
        headers: csrfHeaders(),
        complete: function () {
          $("#usersV2Panel").LoadingOverlay("hide");
        },
        error: function (jqXHR) {
          alertBox("error", (jqXHR.responseJSON && jqXHR.responseJSON.message) || labels.resetCorrectionsFailed || "Unable to reset corrections.");
        },
        success: function (data) {
          var range = data.from && data.to ? "<br>" + escapeHtml(labels.range || "Range") + ": " + escapeHtml(data.from) + " " + escapeHtml(labels.to || "to") + " " + escapeHtml(data.to) : "";
          alertBox("success", escapeHtml(data.message || "") + "<br>" + escapeHtml(labels.totalUpdated || "Total updated") + ": " + escapeHtml(data.rows_updated || 0) + range);
          if (modal) {
            modal.hide();
          } else {
            $(modalElement).modal("hide");
          }
        }
      });
    });
  }

  function initV2Toggle() {
    $("#usersV2Table").on("click", ".js-v2-access-toggle", function () {
      var button = $(this);
      var nextEnabled = button.data("enabled") == 1 ? 0 : 1;

      button.prop("disabled", true);
      $.ajax({
        url: button.data("url"),
        type: "POST",
        dataType: "json",
        data: {
          enabled: nextEnabled
        },
        headers: csrfHeaders(),
        error: function (jqXHR) {
          button.prop("disabled", false);
          alertBox("error", (jqXHR.responseJSON && jqXHR.responseJSON.message) || labels.v2AccessFailed || "Unable to update V2 access.");
        },
        success: function (data) {
          table.ajax.reload(null, false);
          alertBox("success", data.message || labels.updated || "Updated.");
        }
      });
    });
  }

  function initFilters() {
    $(".select-picker").selectpicker();

    $("#usersV2FilterForm").on("submit", function (event) {
      event.preventDefault();
      table.draw();
    });

    $("#usersV2ClearFilters").on("click", function () {
      $("#usersV2ParentId").val("");
      $("#usersV2Status").val("");
      $(".select-picker").selectpicker("refresh");
      table.draw();
    });
  }

  $(function () {
    initTable();
    initDetails();
    initFilters();
    initResetCorrections();
    initV2Toggle();
  });
})(jQuery);
