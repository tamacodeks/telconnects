(function ($) {
  "use strict";

  var config = window.v2History || {};
  var labels = config.labels || {};
  var columns = config.columns || {};

  function escapeHtml(value) {
    return String(value === null || typeof value === "undefined" ? "" : value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function escapeAttr(value) {
    return escapeHtml(value).replace(/`/g, "&#096;");
  }

  function stripHtml(value) {
    return $("<div>").html(value || "").text();
  }

  function rawDisplay(value, type) {
    return type === "display" ? (value || "") : stripHtml(value);
  }

  function isPinHistory() {
    return config.type === "pin-history";
  }

  function isPayments() {
    return config.type === "payments";
  }

  function isTickets() {
    return config.type === "tickets";
  }

  function isFailedTransactions() {
    return config.type === "failed-transactions";
  }

  function hasRichEmptyState() {
    return config.type === "transactions" || isFailedTransactions() || isPinHistory() || isPayments() || isTickets();
  }

  function allowsEmptyDateRange() {
    return isPayments() || isTickets();
  }

  function columnLabel(key, fallback) {
    return columns[key] || fallback;
  }

  function normalServiceName(data, row) {
    if (data === "Topup") {
      return "";
    }

    if (row && row.tt_operator === "blabla") {
      return "Bla Bus";
    }

    return data === "Tama Topup" ? "TopUp" : (data || "");
  }

  function productName(data, row) {
    var value = data || "";

    if (value && row && row.tt_operator === "blabla") {
      var parts = String(value).split(" ");
      var priceWithCurrency = parts.slice(1).join(" ");
      return '<span title="' + escapeAttr(value) + '">Bla ' + escapeHtml(priceWithCurrency) + "</span>";
    }

    return value ? '<span title="' + escapeAttr(value) + '">' + escapeHtml(value) + "</span>" : "";
  }

  function orderStatus(data) {
    var value = data === "Refunded" ? (labels.refunded || "Rembourser") : (data || "");

    if (data === "Failed" && labels.failedStatus) {
      value = labels.failedStatus;
    }

    var key = String(value).toLowerCase();
    var tone = key.indexOf("success") !== -1 || key.indexOf("ok") !== -1 ? "success" : "";
    tone = tone || (key.indexOf("refund") !== -1 || key.indexOf("rembours") !== -1 || key.indexOf("fail") !== -1 || key.indexOf("echec") !== -1 || key.indexOf("echou") !== -1 ? "danger" : "neutral");
    return '<span class="v2-history-status v2-history-status-' + tone + '">' + escapeHtml(value) + "</span>";
  }

  function failedTransactionStatus(row) {
    return orderStatus(row && row.order_status_name ? row.order_status_name : (labels.failedStatus || "Failed"));
  }

  function transactionStatus(serviceId, row) {
    var id = String(serviceId || "");

    if (id === "9" || id === "10") {
      var link = row && row.link ? String(row.link) : "";
      var instructions = row && row.instructions ? String(row.instructions) : "";
      var href = link;

      if (row && row.txn_id && String(row.txn_id).substring(0, 3) === "TXN") {
        href = "flix-bus/download/" + instructions + "," + link;
      }

      return href
        ? '<a class="v2-history-download" href="' + escapeAttr(href) + '" target="_blank" rel="noopener">' + escapeHtml(labels.download || "Download") + "</a>"
        : "";
    }

    if (id === "2") {
      return orderStatus(row && row.order_status_name === "Refunded" ? labels.refunded : (labels.topup_ok || "Topup Ok"));
    }

    if (id === "8") {
      return orderStatus(labels.topup_ok || "Topup Ok");
    }

    return orderStatus(labels.calling_card_ok || "Calling card ok");
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

  function orderColumns() {
    return [
      detailColumn(),
      numberColumn(),
      { data: "date", name: "orders.date", searchable: false, render: displayDateValue },
      { data: "username", name: "users.username", orderable: false, render: escapeHtml },
      {
        data: "service_name",
        name: "service_name",
        searchable: false,
        orderable: false,
        render: function (data, type, row) {
          return escapeHtml(normalServiceName(data, row));
        }
      },
      {
        data: "product_name",
        name: "product_name",
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return productName(data, row);
        }
      },
      { data: "order_amount", name: "order_amount", searchable: false, orderable: false, className: "v2-history-number-cell", render: escapeHtml },
      {
        data: "order_status_name",
        searchable: false,
        orderable: false,
        render: orderStatus
      }
    ];
  }

  function transactionColumns() {
    var cols = [
      detailColumn(),
      numberColumn(),
      { data: "date", name: "orders.date", render: displayDateValue },
      { data: "username", name: "users.username", render: escapeHtml },
      {
        data: "service_name",
        name: "service_name",
        searchable: false,
        orderable: false,
        render: function (data, type, row) {
          return escapeHtml(normalServiceName(data, row));
        }
      },
      { data: "txn_id", name: "txn_id", orderable: false, render: escapeHtml },
      {
        data: "product_name",
        name: "product_name",
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return productName(data, row);
        }
      },
      { data: "public_price", name: "public_price", orderable: false, searchable: false, className: "sum v2-history-number-cell", render: escapeHtml }
    ];

    if (config.canSeeCost) {
      cols.push({ data: "buying_price", name: "buying_price", orderable: false, searchable: false, className: "sum v2-history-number-cell", render: escapeHtml });
    }

    cols.push(
      { data: "order_amount", name: "order_amount", orderable: false, searchable: false, className: "sum v2-history-number-cell", render: escapeHtml },
      { data: "sale_margin", name: "sale_margin", orderable: false, searchable: false, className: "sum v2-history-number-cell", render: escapeHtml },
      {
        data: "service_id",
        searchable: false,
        orderable: false,
        render: function (data, type, row) {
          return transactionStatus(data, row);
        }
      }
    );

    return cols;
  }

  function failedTransactionColumns() {
    var cols = [
      detailColumn(),
      numberColumn(),
      { data: "date", name: "orders.date", render: displayDateValue },
      { data: "username", name: "users.username", render: escapeHtml },
      {
        data: "service_name",
        name: "service_name",
        searchable: false,
        orderable: false,
        render: function (data, type, row) {
          return escapeHtml(normalServiceName(data, row));
        }
      },
      { data: "txn_id", name: "txn_id", orderable: false, render: escapeHtml },
      {
        data: "product_name",
        name: "product_name",
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return productName(data, row);
        }
      },
      { data: "public_price", name: "public_price", orderable: false, searchable: false, className: "sum v2-history-number-cell", render: escapeHtml }
    ];

    if (config.canSeeCost) {
      cols.push({ data: "buying_price", name: "buying_price", orderable: false, searchable: false, className: "sum v2-history-number-cell", render: escapeHtml });
    }

    cols.push(
      { data: "order_amount", name: "order_amount", orderable: false, searchable: false, className: "sum v2-history-number-cell", render: escapeHtml },
      { data: "sale_margin", name: "sale_margin", orderable: false, searchable: false, className: "sum v2-history-number-cell", render: escapeHtml },
      {
        data: "order_status_name",
        searchable: false,
        orderable: false,
        render: function (data, type, row) {
          return failedTransactionStatus(row);
        }
      }
    );

    return cols;
  }

  function pinHistoryColumns() {
    return [
      numberColumn(),
      { data: "date", name: "pin_histories.date", render: displayDateValue },
      { data: "name", name: "pin_histories.name", render: escapeHtml },
      { data: "description", name: "description", orderable: false, searchable: false, render: rawDisplay },
      { data: "serial", name: "pin_histories.serial", orderable: false, render: escapeHtml },
      { data: "pin", name: "pin_histories.pin", orderable: false, render: escapeHtml },
      { data: "status", name: "status", searchable: false, orderable: false, render: rawDisplay },
      { data: "action", name: "action", searchable: false, orderable: false, render: rawDisplay }
    ];
  }

  function paymentColumns() {
    return [
      numberColumn(),
      { data: "date", name: "payments.date", render: displayDateValue },
      { data: "payment_date", name: "transactions.date", render: displayDateValue },
      { data: "cust_id", name: "users.cust_id", orderable: false, render: escapeHtml },
      { data: "username", name: "users.username", render: escapeHtml },
      { data: "amount", name: "payments.amount", searchable: false, orderable: false, className: "sum v2-history-number-cell", render: escapeHtml },
      { data: "prev_bal", name: "transactions.prev_bal", searchable: false, orderable: false, className: "v2-history-number-cell", render: escapeHtml },
      { data: "balance", name: "transactions.balance", searchable: false, orderable: false, className: "v2-history-number-cell", render: escapeHtml },
      { data: "comment", name: "payments.description", orderable: false, render: escapeHtml },
      { data: "received_by_name", name: "receivers.username", orderable: false, render: escapeHtml }
    ];
  }

  function ticketColumns() {
    return [
      numberColumn(),
      { data: "created_at", name: "tickets.created_at", render: displayDateValue },
      { data: "name", name: "pin_histories.name", render: escapeHtml },
      { data: "serial", name: "pin_histories.serial", orderable: false, render: escapeHtml },
      { data: "pin", name: "pin_histories.pin", orderable: false, searchable: false, render: escapeHtml },
      { data: "to_user", name: "to_user", orderable: false, searchable: false, render: escapeHtml },
      { data: "issue_type", name: "issue_type", orderable: false, searchable: false, render: escapeHtml },
      { data: "status", name: "tickets.status", searchable: false, orderable: false, render: rawDisplay },
      { data: "action", name: "action", searchable: false, orderable: false, render: rawDisplay }
    ];
  }

  function parseAmount(value) {
    var text = String(value || "").replace(/<[^>]*>/g, "").replace(/[^0-9.-]/g, "");
    var amount = parseFloat(text);
    return Number.isNaN(amount) ? 0 : amount;
  }

  function formatAmount(value) {
    return value.toFixed(2);
  }

  function padDatePart(value) {
    return value < 10 ? "0" + value : String(value);
  }

  function formatDate(date) {
    return [
      date.getFullYear(),
      padDatePart(date.getMonth() + 1),
      padDatePart(date.getDate())
    ].join("-");
  }

  function formatDisplayDate(date) {
    return [
      padDatePart(date.getDate()),
      padDatePart(date.getMonth() + 1),
      date.getFullYear()
    ].join("/");
  }

  function todayDate() {
    var now = new Date();
    return new Date(now.getFullYear(), now.getMonth(), now.getDate());
  }

  function minHistoryDate() {
    var today = todayDate();
    return new Date(today.getFullYear(), today.getMonth() - 3, today.getDate());
  }

  function parseHistoryDate(value) {
    var text = String(value || "").trim();
    var match = /^(\d{4})-(\d{2})-(\d{2})(?:$|[ T])/.exec(text);
    var year;
    var month;
    var day;

    if (match) {
      year = Number(match[1]);
      month = Number(match[2]) - 1;
      day = Number(match[3]);
    } else {
      match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(text);

      if (!match) {
        return null;
      }

      year = Number(match[3]);
      month = Number(match[2]) - 1;
      day = Number(match[1]);
    }

    var date = new Date(year, month, day);

    if (date.getFullYear() !== year || date.getMonth() !== month || date.getDate() !== day) {
      return null;
    }

    return date;
  }

  function displayDateValue(data, type) {
    if (type === "sort" || type === "type") {
      return data || "";
    }

    var date = parseHistoryDate(data);
    return date ? formatDisplayDate(date) : escapeHtml(data);
  }

  function compareDates(left, right) {
    return formatDate(left).localeCompare(formatDate(right));
  }

  function clampHistoryDate(date) {
    var min = minHistoryDate();
    var max = todayDate();

    if (compareDates(date, min) < 0) {
      return min;
    }

    if (compareDates(date, max) > 0) {
      return max;
    }

    return date;
  }

  function showHistoryDateError(message) {
    var $error = $("#v2HistoryDateError");

    if (!$error.length) {
      return;
    }

    $error.text(message || labels.dateError || "Select a valid date range.").prop("hidden", false);
    $(".v2-history-date").closest(".v2-history-field").addClass("has-error");
  }

  function clearHistoryDateError() {
    $("#v2HistoryDateError").prop("hidden", true);
    $(".v2-history-date").closest(".v2-history-field").removeClass("has-error");
  }

  function isoFieldFor($input) {
    var target = $input.data("iso-target");
    return target ? $("#" + target) : $();
  }

  function setHistoryDateValue($input, date) {
    var $iso = isoFieldFor($input);
    var displayValue = date ? formatDisplayDate(date) : "";
    var isoValue = date ? formatDate(date) : "";

    $input.val(displayValue);
    $iso.val(isoValue);
  }

  function syncHistoryIsoDate($input) {
    var date = parseHistoryDate($input.val());
    isoFieldFor($input).val(date ? formatDate(date) : "");
    return date;
  }

  function normalizeHistoryDateInputs() {
    $(".v2-history-date").each(function () {
      var $input = $(this);
      var date = parseHistoryDate($input.val()) || parseHistoryDate(isoFieldFor($input).val());

      if (date) {
        setHistoryDateValue($input, date);
      }
    });
  }

  function historyDateLimits($input) {
    var min = minHistoryDate();
    var max = todayDate();
    var from = parseHistoryDate($("#v2HistoryFromDate").val());
    var to = parseHistoryDate($("#v2HistoryToDate").val());

    if ($input && $input.attr("id") === "v2HistoryFromDate" && to && compareDates(to, max) < 0) {
      max = to;
    }

    if ($input && $input.attr("id") === "v2HistoryToDate" && from && compareDates(from, min) > 0) {
      min = from;
    }

    return { min: min, max: max };
  }

  function syncHistoryDatePickerLimits() {
    if (!$.fn.datepicker) {
      return;
    }

    $(".v2-history-date.hasDatepicker").each(function () {
      var $input = $(this);
      var limits = historyDateLimits($input);
      $input.datepicker("option", "minDate", limits.min);
      $input.datepicker("option", "maxDate", limits.max);
    });
  }

  function validateHistoryDateRange() {
    var min = minHistoryDate();
    var max = todayDate();
    var fromText = String($("#v2HistoryFromDate").val() || "").trim();
    var toText = String($("#v2HistoryToDate").val() || "").trim();
    var from = parseHistoryDate(fromText);
    var to = parseHistoryDate(toText);
    var error = labels.dateError || "Select dates within the last 3 months. The To date cannot be before the From date.";

    if (allowsEmptyDateRange() && fromText === "" && toText === "") {
      setHistoryDateValue($("#v2HistoryFromDate"), null);
      setHistoryDateValue($("#v2HistoryToDate"), null);
      clearHistoryDateError();
      return true;
    }

    if (!from || !to) {
      showHistoryDateError(error);
      return false;
    }

    if (compareDates(from, min) < 0 || compareDates(to, min) < 0 || compareDates(from, max) > 0 || compareDates(to, max) > 0) {
      showHistoryDateError(error);
      return false;
    }

    if (compareDates(to, from) < 0) {
      showHistoryDateError(error);
      return false;
    }

    clearHistoryDateError();
    setHistoryDateValue($("#v2HistoryFromDate"), from);
    setHistoryDateValue($("#v2HistoryToDate"), to);
    return true;
  }

  function setActiveRange(range) {
    $(".v2-history-range-btn").removeClass("is-active");

    if (range) {
      $('.v2-history-range-btn[data-v2-history-range="' + range + '"]').addClass("is-active");
    }
  }

  function setHistoryRange(range) {
    var today = todayDate();
    var minDate = minHistoryDate();
    var from = null;
    var to = null;

    if (range === "today") {
      from = today;
      to = from;
    } else if (range === "7d") {
      from = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 6);
      to = today;
    } else if (range === "month") {
      from = new Date(today.getFullYear(), today.getMonth(), 1);
      to = today;
    } else if (range === "30d") {
      from = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 29);
      to = today;
    } else if (range === "all") {
      from = minDate;
      to = today;
    }

    if (from) {
      from = clampHistoryDate(from);
    }

    if (to) {
      to = clampHistoryDate(to);
    }

    setHistoryDateValue($("#v2HistoryFromDate"), from);
    setHistoryDateValue($("#v2HistoryToDate"), to);
    clearHistoryDateError();
    syncHistoryDatePickerLimits();
    setActiveRange(range);
  }

  function mobileMeta(label, value, isHtml) {
    var output = isHtml ? (value || "") : escapeHtml(value || "");

    if (!String(output).trim()) {
      return "";
    }

    return (
      '<div class="v2-history-mobile-meta">' +
        "<span>" + escapeHtml(label) + "</span>" +
        "<strong>" + output + "</strong>" +
      "</div>"
    );
  }

  function mobileTitle(row) {
    var product = productName(row && row.product_name, row);

    return product || escapeHtml(normalServiceName(row && row.service_name, row) || "-");
  }

  function renderOrderMobileCard(row, index, start) {
    var number = start + index + 1;
    var print = row && row.print_receipt ? row.print_receipt : "";

    return (
      '<article class="v2-history-mobile-card">' +
        '<div class="v2-history-mobile-card-head">' +
          '<div class="v2-history-mobile-title">' +
            '<span class="v2-history-mobile-number">#' + number + "</span>" +
            "<h4>" + mobileTitle(row) + "</h4>" +
            '<p class="v2-history-mobile-date">' + displayDateValue(row && row.date, "display") + "</p>" +
          "</div>" +
          orderStatus(row && row.order_status_name) +
        "</div>" +
        '<div class="v2-history-mobile-meta-grid">' +
          mobileMeta(columnLabel("retailer", "User"), row && row.username, false) +
          mobileMeta(columnLabel("service", "Service"), normalServiceName(row && row.service_name, row), false) +
          mobileMeta(columnLabel("price", "Price"), row && row.order_amount, false) +
        "</div>" +
        (print ? '<div class="v2-history-mobile-actions">' + print + "</div>" : "") +
      "</article>"
    );
  }

  function renderTransactionMobileCard(row, index, start) {
    var number = start + index + 1;
    var costMeta = config.canSeeCost
      ? mobileMeta(columnLabel("buying_price", "Buying price"), row && row.buying_price, false)
      : "";

    return (
      '<article class="v2-history-mobile-card">' +
        '<div class="v2-history-mobile-card-head">' +
          '<div class="v2-history-mobile-title">' +
            '<span class="v2-history-mobile-number">#' + number + "</span>" +
            "<h4>" + mobileTitle(row) + "</h4>" +
            '<p class="v2-history-mobile-date">' + displayDateValue(row && row.date, "display") + "</p>" +
          "</div>" +
          transactionStatus(row && row.service_id, row) +
        "</div>" +
        '<div class="v2-history-mobile-meta-grid">' +
          mobileMeta(columnLabel("retailer", "User"), row && row.username, false) +
          mobileMeta(columnLabel("service", "Service"), normalServiceName(row && row.service_name, row), false) +
          mobileMeta(columnLabel("transaction_id", "Transaction ID"), row && row.txn_id, false) +
          mobileMeta(columnLabel("public_price", "Public price"), row && row.public_price, false) +
          costMeta +
          mobileMeta(columnLabel("price", "Price"), row && row.order_amount, false) +
          mobileMeta(columnLabel("sale_margin", "Sale margin"), row && row.sale_margin, false) +
        "</div>" +
      "</article>"
    );
  }

  function renderFailedTransactionMobileCard(row, index, start) {
    var number = start + index + 1;
    var costMeta = config.canSeeCost
      ? mobileMeta(columnLabel("buying_price", "Buying price"), row && row.buying_price, false)
      : "";

    return (
      '<article class="v2-history-mobile-card">' +
        '<div class="v2-history-mobile-card-head">' +
          '<div class="v2-history-mobile-title">' +
            '<span class="v2-history-mobile-number">#' + number + "</span>" +
            "<h4>" + mobileTitle(row) + "</h4>" +
            '<p class="v2-history-mobile-date">' + displayDateValue(row && row.date, "display") + "</p>" +
          "</div>" +
          failedTransactionStatus(row) +
        "</div>" +
        '<div class="v2-history-mobile-meta-grid">' +
          mobileMeta(columnLabel("retailer", "User"), row && row.username, false) +
          mobileMeta(columnLabel("service", "Service"), normalServiceName(row && row.service_name, row), false) +
          mobileMeta(columnLabel("transaction_id", "Transaction ID"), row && row.txn_id, false) +
          mobileMeta(columnLabel("public_price", "Public price"), row && row.public_price, false) +
          costMeta +
          mobileMeta(columnLabel("price", "Price"), row && row.order_amount, false) +
          mobileMeta(columnLabel("sale_margin", "Sale margin"), row && row.sale_margin, false) +
        "</div>" +
      "</article>"
    );
  }

  function renderPinHistoryMobileCard(row, index, start) {
    var number = start + index + 1;

    return (
      '<article class="v2-history-mobile-card">' +
        '<div class="v2-history-mobile-card-head">' +
          '<div class="v2-history-mobile-title">' +
            '<span class="v2-history-mobile-number">#' + number + "</span>" +
            "<h4>" + escapeHtml(row && row.name ? row.name : "-") + "</h4>" +
            '<p class="v2-history-mobile-date">' + displayDateValue(row && row.date, "display") + "</p>" +
          "</div>" +
          rawDisplay(row && row.status, "display") +
        "</div>" +
        '<div class="v2-history-mobile-meta-grid">' +
          mobileMeta(columnLabel("card_description", "Description"), stripHtml(row && row.description), false) +
          mobileMeta(columnLabel("serial", "Serial"), row && row.serial, false) +
          mobileMeta(columnLabel("pin", "PIN"), row && row.pin, false) +
        "</div>" +
        (row && row.action ? '<div class="v2-history-mobile-actions">' + rawDisplay(row.action, "display") + "</div>" : "") +
      "</article>"
    );
  }

  function renderPaymentMobileCard(row, index, start) {
    var number = start + index + 1;

    return (
      '<article class="v2-history-mobile-card">' +
        '<div class="v2-history-mobile-card-head">' +
          '<div class="v2-history-mobile-title">' +
            '<span class="v2-history-mobile-number">#' + number + "</span>" +
            "<h4>" + escapeHtml(row && row.username ? row.username : "-") + "</h4>" +
            '<p class="v2-history-mobile-date">' + displayDateValue(row && row.date, "display") + "</p>" +
          "</div>" +
          '<span class="v2-history-status v2-history-status-info">' + escapeHtml(row && row.amount ? row.amount : "-") + "</span>" +
        "</div>" +
        '<div class="v2-history-mobile-meta-grid">' +
          mobileMeta(columnLabel("customer_id", "Customer ID"), row && row.cust_id, false) +
          mobileMeta(columnLabel("payment_date", "Updated date"), displayDateValue(row && row.payment_date, "display"), false) +
          mobileMeta(columnLabel("previous_balance", "Previous balance"), row && row.prev_bal, false) +
          mobileMeta(columnLabel("current_balance", "Current balance"), row && row.balance, false) +
          mobileMeta(columnLabel("comment", "Comment"), row && row.comment, false) +
          mobileMeta(columnLabel("received_by", "Received by"), row && row.received_by_name, false) +
        "</div>" +
      "</article>"
    );
  }

  function renderTicketMobileCard(row, index, start) {
    var number = start + index + 1;

    return (
      '<article class="v2-history-mobile-card">' +
        '<div class="v2-history-mobile-card-head">' +
          '<div class="v2-history-mobile-title">' +
            '<span class="v2-history-mobile-number">#' + number + "</span>" +
            "<h4>" + escapeHtml(row && row.name ? row.name : "-") + "</h4>" +
            '<p class="v2-history-mobile-date">' + displayDateValue(row && row.created_at, "display") + "</p>" +
          "</div>" +
          rawDisplay(row && row.status, "display") +
        "</div>" +
        '<div class="v2-history-mobile-meta-grid">' +
          mobileMeta(columnLabel("serial", "Serial"), row && row.serial, false) +
          mobileMeta(columnLabel("pin", "PIN"), row && row.pin, false) +
          mobileMeta(columnLabel("to", "To"), row && row.to_user, false) +
          mobileMeta(columnLabel("type", "Type"), row && row.issue_type, false) +
        "</div>" +
        (row && row.action ? '<div class="v2-history-mobile-actions">' + rawDisplay(row.action, "display") + "</div>" : "") +
      "</article>"
    );
  }

  function renderMobileCards(table) {
    var list = $("#v2HistoryMobileList");

    if (!list.length) {
      return;
    }

    var rows = table.rows({ page: "current" }).data().toArray();
    var start = table.page.info().start || 0;

    if (!rows.length) {
      list.html(emptyStateMarkup());
      return;
    }

    list.html(rows.map(function (row, index) {
      if (config.type === "transactions") {
        return renderTransactionMobileCard(row, index, start);
      }

      if (isFailedTransactions()) {
        return renderFailedTransactionMobileCard(row, index, start);
      }

      if (isPinHistory()) {
        return renderPinHistoryMobileCard(row, index, start);
      }

      if (isPayments()) {
        return renderPaymentMobileCard(row, index, start);
      }

      if (isTickets()) {
        return renderTicketMobileCard(row, index, start);
      }

      return renderOrderMobileCard(row, index, start);
    }).join(""));
  }

  function emptyStateMarkup() {
    if (!hasRichEmptyState()) {
      return escapeHtml(labels.empty || "No matching records found");
    }

    return (
      '<div class="v2-history-empty-state">' +
        '<div class="v2-history-empty-icon" aria-hidden="true"><i class="fa fa-credit-card"></i></div>' +
        "<strong>" + escapeHtml(emptyTitleLabel()) + "</strong>" +
        "<p>" + escapeHtml(emptyDescriptionLabel()) + "</p>" +
        '<button type="button" class="v2-history-btn v2-history-btn-outline v2-history-empty-reset" data-v2-history-reset>' +
          escapeHtml(emptyResetLabel()) +
        "</button>" +
      "</div>"
    );
  }

  function emptyTitleLabel() {
    return labels.emptyTitle || labels.empty_title || labels.empty || (currentLocale() === "fr" ? "Aucune transaction trouvée" : "No transactions found");
  }

  function emptyDescriptionLabel() {
    return labels.emptyDescription || labels.empty_description || (
      currentLocale() === "fr"
        ? "Essayez une autre période, un autre service ou un autre terme de recherche."
        : "Try another date range, service, or search term."
    );
  }

  function emptyResetLabel() {
    return labels.resetFilters || labels.reset_filters || (
      currentLocale() === "fr" ? "Réinitialiser" : "Reset filters"
    );
  }

  function setLoadingState(isLoading) {
    $("#v2HistoryPanel").toggleClass("is-loading", !!isLoading);
  }

  function setResultState(table) {
    var rows = table.rows({ page: "current" }).data().toArray();
    var info = table.page.info ? table.page.info() : {};
    var isReportLike = hasRichEmptyState();
    var hasNoRows = rows.length === 0;

    $("#v2HistoryPanel")
      .toggleClass("has-empty-results", isReportLike && hasNoRows)
      .toggleClass("has-single-page", (info.pages || 0) <= 1)
      .toggleClass("has-short-results", isReportLike && rows.length > 0 && rows.length <= 3);
  }

  function currentLocale() {
    var locale = config.locale || $("html").attr("lang") || document.documentElement.lang || "en";
    return String(locale).toLowerCase().indexOf("fr") === 0 ? "fr" : "en";
  }

  function datepickerI18n() {
    if (currentLocale() === "fr") {
      return {
        closeText: "Fermer",
        prevText: "Précédent",
        nextText: "Suivant",
        currentText: "Aujourd'hui",
        monthNames: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
        monthNamesShort: ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Août", "Sep", "Oct", "Nov", "Déc"],
        dayNames: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
        dayNamesMin: ["Di", "Lu", "Ma", "Me", "Je", "Ve", "Sa"]
      };
    }

    return {
      closeText: "Done",
      prevText: "Prev",
      nextText: "Next",
      currentText: "Today",
      monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
      monthNamesShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
      dayNames: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
      dayNamesMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"]
    };
  }

  function positionHistoryDatepicker($input) {
    window.setTimeout(function () {
      var $popup = $("#ui-datepicker-div");
      var input = $input && $input.get(0);

      if (!$popup.length || !input || !$popup.is(":visible")) {
        return;
      }

      var rect = input.getBoundingClientRect();
      var popupWidth = $popup.outerWidth();
      var popupHeight = $popup.outerHeight();
      var scrollTop = $(window).scrollTop();
      var scrollLeft = $(window).scrollLeft();
      var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
      var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
      var gap = 8;
      var maxLeft = Math.max(gap, viewportWidth - popupWidth - gap);
      var openAbove = rect.bottom + gap + popupHeight > viewportHeight && rect.top > popupHeight + gap;
      var top = openAbove
        ? Math.max(scrollTop + gap, scrollTop + rect.top - popupHeight - gap)
        : scrollTop + rect.bottom + gap;
      var left = scrollLeft + Math.min(Math.max(gap, rect.left), maxLeft);

      $popup
        .removeClass("bus-v2-datepicker-popup")
        .addClass("v2-history-datepicker-popup")
        .toggleClass("is-open-above", openAbove)
        .css({
          top: top,
          left: left,
          zIndex: 2200
        });
    }, 0);
  }

  function decorateHistoryDatepicker($input) {
    window.setTimeout(function () {
      $("#ui-datepicker-div")
        .removeClass("bus-v2-datepicker-popup")
        .addClass("v2-history-datepicker-popup")
        .attr("data-v2-history-datepicker", "true");
      positionHistoryDatepicker($input);
    }, 0);
  }

  function initDatePicker() {
    if (!$.fn.datepicker) {
      return;
    }

    $(".v2-history-date").each(function () {
      var $input = $(this);
      var limits = historyDateLimits($input);

      if ($input.hasClass("hasDatepicker")) {
        $input.datepicker("destroy");
      }

      $input.datepicker($.extend({}, datepickerI18n(), {
        changeMonth: true,
        changeYear: true,
        minDate: limits.min,
        maxDate: limits.max,
        showButtonPanel: true,
        dateFormat: "dd/mm/yy",
        firstDay: currentLocale() === "fr" ? 1 : 0,
        constrainInput: false,
        showAnim: "fadeIn",
        duration: 160,
        showOtherMonths: true,
        selectOtherMonths: true,
        beforeShow: function (input) {
          $(input).closest(".v2-history-field").addClass("is-picker-open");
          syncHistoryDatePickerLimits();
          decorateHistoryDatepicker($(input));
        },
        onChangeMonthYear: function () {
          decorateHistoryDatepicker($(this));
        },
        onSelect: function () {
          setActiveRange("");
          clearHistoryDateError();
          if (validateHistoryDateRange()) {
            syncHistoryDatePickerLimits();
          }
          positionHistoryDatepicker($(this));
        },
        onClose: function () {
          $(this).closest(".v2-history-field").removeClass("is-picker-open");
        }
      }));
    });
  }

  function initSelectPicker() {
    initServiceSelect2();

    if ($.fn.selectpicker) {
      $(".select-picker").selectpicker();
    }
  }

  function initServiceSelect2() {
    var $select = $("#v2HistoryService");

    if (!$select.length || !$.fn.select2) {
      return;
    }

    var selected = $select.val();

    if ($select.data("select2")) {
      $select.select2("destroy");
      $select.val(selected || []);
    }

    $select.off("select2:open select2:clear select2:select select2:unselect change");
    $select.siblings(".select2-container").remove();

    $select.select2({
      placeholder: $select.data("placeholder") || $select.attr("title") || "All services",
      allowClear: false,
      width: "100%",
      closeOnSelect: !$select.prop("multiple") ? true : false,
      dropdownCssClass: "v2-history-service-dropdown",
      containerCssClass: "v2-history-service-container"
    });

    $select.on("select2:open", function () {
      updateServiceCount();
      decorateServiceDropdown($select);
    });

    $select.on("select2:select select2:unselect select2:clear change", function () {
      window.setTimeout(function () {
        updateServiceCount();
        decorateServiceDropdown($select);
      }, 0);
    });

    updateServiceCount();
  }

  function serviceActionLabel(mode) {
    var selector = mode === "all" ? '[data-v2-history-select="all"]' : '[data-v2-history-select="clear"]';
    var text = $(".v2-history-select-actions").find(selector).first().text().trim();

    if (text) {
      return text;
    }

    return mode === "all"
      ? (labels.selectAll || "Select all")
      : (labels.clear || "Clear");
  }

  function decorateServiceDropdown($select) {
    window.setTimeout(function () {
      var $dropdown = $(".select2-container--open .v2-history-service-dropdown").last();
      var $results = $dropdown.find(".select2-results").first();

      if (!$dropdown.length || !$results.length || $dropdown.find(".v2-history-service-dropdown-actions").length) {
        return;
      }

      var $actions = $(
        '<div class="v2-history-service-dropdown-actions">' +
          '<button type="button" data-v2-history-dropdown-select="all"></button>' +
          '<button type="button" data-v2-history-dropdown-select="clear"></button>' +
        '</div>'
      );

      $actions.find('[data-v2-history-dropdown-select="all"]').text(serviceActionLabel("all"));
      $actions.find('[data-v2-history-dropdown-select="clear"]').text(serviceActionLabel("clear"));

      $actions.insertBefore($results);
    }, 0);
  }

  function bindServiceDropdownActions() {
    $(document)
      .off("mousedown.v2HistoryServiceActions", "[data-v2-history-dropdown-select]")
      .on("mousedown.v2HistoryServiceActions", "[data-v2-history-dropdown-select]", function (event) {
        event.preventDefault();
        event.stopPropagation();
      })
      .off("click.v2HistoryServiceActions", "[data-v2-history-dropdown-select]")
      .on("click.v2HistoryServiceActions", "[data-v2-history-dropdown-select]", function (event) {
        event.preventDefault();
        event.stopPropagation();
        setServiceSelection($(this).attr("data-v2-history-dropdown-select"));
      });
  }

  function selectedServiceValues($select) {
    var value = $select.val();

    if (Array.isArray(value)) {
      return value.filter(function (item) {
        return String(item || "") !== "";
      });
    }

    return value ? [value] : [];
  }

  function refreshSelectPicker($select) {
    if ($select.data("select2")) {
      $select.trigger("change");
      return;
    }

    if ($.fn.selectpicker && $select.data("selectpicker")) {
      $select.selectpicker("refresh");
      return;
    }

    $select.trigger("change");
  }

  function updateServiceSummary($select, selected, total) {
    var $summary = $select.next(".select2-container").find(".select2-selection__rendered");

    if (!$summary.length) {
      return;
    }

    var text = $select.data("placeholder") || $select.attr("title") || "All services";

    if (selected > 0 && total > 0) {
      text = $select.prop("multiple")
        ? (selected === total
          ? (isPinHistory()
            ? (currentLocale() === "fr" ? "Toutes les cartes" : "All cards")
            : (isPayments()
              ? (currentLocale() === "fr" ? "Tous les utilisateurs" : "All retailers")
              : (currentLocale() === "fr" ? "Tous les services" : (labels.servicesAllSelected || "All services"))))
          : selected + " " + (labels.servicesSelected || "selected"))
        : $select.find("option:selected").text().trim();
    }

    var $display = $summary.children(".v2-history-service-value");

    if (!$display.length) {
      $display = $('<span class="v2-history-service-value"></span>').prependTo($summary);
    }

    $display.text(text);
    $summary
      .attr("data-summary", text)
      .toggleClass("s2-empty", selected === 0);
  }

  function updateServiceCount() {
    var $select = $("#v2HistoryService");
    var total = $select.find("option[value!='']").length;
    var selected = selectedServiceValues($select).length;
    var text = "";

    if (config.type !== "transactions" && $select.prop("multiple") && selected > 0 && total > 0) {
      text = "(" + selected + "/" + total + ")";
    }

    $("#v2HistoryServiceCount").text(text);
    updateServiceSummary($select, selected, total);
  }

  function setServiceSelection(mode) {
    var $select = $("#v2HistoryService");

    if (!$select.length) {
      return;
    }

    var shouldReopen = $select.data("select2") && $(".select2-container--open .v2-history-service-dropdown").length > 0;

    if (!$select.prop("multiple")) {
      $select.val("");
    } else if (mode === "all") {
      var values = $select.find("option[value!='']").map(function () {
        return this.value;
      }).get();

      $select.val(values);
      $select.find("option").prop("selected", false);
      values.forEach(function (value) {
        $select.find("option").filter(function () {
          return this.value === value;
        }).prop("selected", true);
      });
    } else {
      $select.val([]);
      $select.find("option").prop("selected", false);
    }

    refreshSelectPicker($select);
    updateServiceCount();

    if (shouldReopen) {
      $select.select2("close");
      window.setTimeout(function () {
        $select.select2("open");
        updateServiceCount();
      }, 0);
    }
  }

  function resetHistoryFilters(table) {
    var from = parseHistoryDate(config.defaultFromDate);
    var to = parseHistoryDate(config.defaultToDate);

    $("#v2HistorySearch").val("");
    setServiceSelection("clear");

    if (!from && !to && allowsEmptyDateRange()) {
      setHistoryDateValue($("#v2HistoryFromDate"), null);
      setHistoryDateValue($("#v2HistoryToDate"), null);
      setActiveRange("");
      clearHistoryDateError();
      syncHistoryDatePickerLimits();

      if (window.history && window.history.replaceState) {
        window.history.replaceState(null, document.title, window.location.pathname);
      }

      if (table && table.page) {
        table.page("first").draw();
      }

      return;
    }

    from = from || todayDate();
    to = to || todayDate();
    setHistoryDateValue($("#v2HistoryFromDate"), from);
    setHistoryDateValue($("#v2HistoryToDate"), to);
    setActiveRange("");
    clearHistoryDateError();
    syncHistoryDatePickerLimits();

    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, document.title, window.location.pathname);
    }

    if (table && table.page) {
      table.page("first").draw();
    }
  }

  function responseMessage(response, fallback) {
    return response && response.data && response.data.message
      ? response.data.message
      : fallback;
  }

  function showPinHistoryMessage(message, tone) {
    if ($.alert) {
      $.alert({
        content: message,
        title: labels.alertTitle || "Info",
        type: tone === "danger" ? "red" : "blue",
        icon: tone === "danger" ? "fa fa-exclamation-circle" : "fa fa-info-circle",
        theme: "material",
        buttons: {
          ok: {
            text: labels.close || "OK"
          }
        }
      });
      return;
    }

    window.alert(message);
  }

  function openPinEnquiry(url, title) {
    if (!url) {
      return;
    }

    if (typeof window.AppModal === "function") {
      window.AppModal(url, title || "", "v2-pin-enquiry-modal");
      return;
    }

    window.location.href = url;
  }

  function historyOrder() {
    if (isPinHistory()) {
      return [1, "desc"];
    }

    if (isPayments()) {
      return [1, "desc"];
    }

    if (isTickets()) {
      return [1, "desc"];
    }

    return [2, "desc"];
  }

  function historyColumns() {
    if (isPinHistory()) {
      return pinHistoryColumns();
    }

    if (isPayments()) {
      return paymentColumns();
    }

    if (isTickets()) {
      return ticketColumns();
    }

    if (isFailedTransactions()) {
      return failedTransactionColumns();
    }

    return config.type === "transactions" ? transactionColumns() : orderColumns();
  }

  function tableConfig() {
    return {
      autoWidth: false,
      searching: false,
      pageLength: 10,
      processing: true,
      serverSide: true,
      order: [historyOrder()],
      language: {
        processing: (labels.processing || "Processing") + "...",
        emptyTable: emptyStateMarkup(),
        zeroRecords: emptyStateMarkup(),
        info: labels.info || "Showing _START_ to _END_ of _TOTAL_ entries",
        infoEmpty: labels.infoEmpty || "Showing 0 to 0 of 0 entries",
        infoFiltered: labels.infoFiltered || "(filtered from _MAX_ total entries)",
        paginate: {
          previous: labels.previous || "Previous",
          next: labels.next || "Next"
        }
      },
      ajax: {
        url: config.fetchUrl,
        data: function (data) {
          if (isPinHistory()) {
            data.telecom_provider_id = $("#v2HistoryService").val();
          } else if (isPayments()) {
            data.retailer_id = $("#v2HistoryService").length ? $("#v2HistoryService").val() : [];
          } else if (isTickets()) {
            data.service_id = [];
            data.type = $("#v2HistoryStatus").val();
          } else if (isFailedTransactions()) {
            data.service_id = [];
          } else {
            data.service_id = $("#v2HistoryService").val();
          }
          data.from_date = $("#v2HistoryFromDateIso").val();
          data.to_date = $("#v2HistoryToDateIso").val();
          data.query = $("#v2HistorySearch").val();
        }
      },
      columns: historyColumns(),
      dom: "Brt<'v2-history-table-footer'<'v2-history-table-status'i><'v2-history-table-pager'p>>",
      lengthMenu: [
        [10, 25, 50, -1],
        [labels.records10 || "10 records", labels.records25 || "25 records", labels.records50 || "50 records", labels.showAll || "Show all"]
      ],
      buttons: [
        "pageLength",
        {
          extend: "excel",
          text: '<i class="fa fa-file-excel"></i>',
          titleAttr: labels.downloadExcel || "Download as Excel"
        }
      ],
      footerCallback: function () {
        if (config.type !== "transactions" && !isFailedTransactions() && !isPayments()) {
          return;
        }

        this.api().columns(".sum", { page: "current" }).every(function () {
          var total = this.data().reduce(function (sum, value) {
            return sum + parseAmount(value);
          }, 0);

          if (this.footer()) {
            $(this.footer()).html(formatAmount(total));
          }
        });
      },
      drawCallback: function () {
        setLoadingState(false);
        setResultState(this.api());
        renderMobileCards(this.api());
      }
    };
  }

  $(function () {
    if (!$.fn.DataTable || !$("#v2HistoryTable").length) {
      return;
    }

    normalizeHistoryDateInputs();
    initDatePicker();
    initSelectPicker();
    bindServiceDropdownActions();
    updateServiceCount();

    var template = window.Handlebars && $("#v2HistoryDetailsTemplate").length
      ? Handlebars.compile($("#v2HistoryDetailsTemplate").html())
      : null;
    var $table = $("#v2HistoryTable");

    $table.on("processing.dt", function (event, settings, processing) {
      setLoadingState(processing);
    });

    var table = $table.DataTable(tableConfig());

    $("#v2HistoryFilterForm").on("submit", function (event) {
      event.preventDefault();
      if (!validateHistoryDateRange()) {
        return;
      }
      table.page("first").draw();
    });

    $("#v2HistoryPageSize").on("change", function () {
      if (!validateHistoryDateRange()) {
        return;
      }
      table.page.len(Number($(this).val())).draw();
    });

    $(".v2-history-range-btn").on("click", function () {
      setHistoryRange($(this).data("v2-history-range"));
      table.page("first").draw();
    });

    $('[data-v2-history-select]').on("click", function (event) {
      event.preventDefault();
      event.stopPropagation();
      setServiceSelection($(this).data("v2-history-select"));
    });

    $("#v2HistoryService").on("changed.bs.select change", function () {
      updateServiceCount();
    });

    $(".v2-history-date").on("change", function () {
      setActiveRange("");
      syncHistoryIsoDate($(this));
      if (validateHistoryDateRange()) {
        syncHistoryDatePickerLimits();
      }
    });

    $("#v2HistoryPanel").on("click", "[data-v2-history-reset]", function (event) {
      event.preventDefault();
      resetHistoryFilters(table);
    });

    $("#v2HistoryRefresh").on("click", function () {
      if (!validateHistoryDateRange()) {
        return;
      }
      table.ajax.reload(null, false);
    });

    $("#v2HistoryExport").on("click", function () {
      if (!validateHistoryDateRange()) {
        return;
      }
      if (table.button) {
        table.button(".buttons-excel").trigger();
      }
    });

    $("#v2HistoryPanel").on("click", "[data-v2-pin-print-request]", function (event) {
      event.preventDefault();

      var $button = $(this);
      var pinId = $button.data("pin-id");
      var url = config.urls && config.urls.printRequest;

      if (!url || !pinId || $button.prop("disabled")) {
        return;
      }

      $button.prop("disabled", true).addClass("is-loading");

      $.ajax({
        url: url,
        type: "POST",
        headers: {
          "X-CSRF-TOKEN": config.csrfToken || $('meta[name="csrf-token"]').attr("content")
        },
        data: {
          pin_id: pinId
        }
      }).done(function (response) {
        var success = response && response.data && String(response.data.status) === "200";
        showPinHistoryMessage(responseMessage(response, labels.requestSent || "Request sent"), success ? "info" : "danger");
        if (success) {
          table.ajax.reload(null, false);
        }
      }).fail(function () {
        showPinHistoryMessage(labels.error || "Unable to process request.", "danger");
      }).always(function () {
        $button.prop("disabled", false).removeClass("is-loading");
      });
    });

    $("#v2HistoryPanel").on("click", "[data-v2-pin-enquiry]", function (event) {
      event.preventDefault();

      var url = $(this).data("url");
      var title = $(this).data("title") || "";
      openPinEnquiry(url, title);
    });

    if (isPinHistory() && config.autoOpenModal && config.autoOpenModal.url) {
      window.setTimeout(function () {
        openPinEnquiry(config.autoOpenModal.url, config.autoOpenModal.title || "");

        if (window.history && window.history.replaceState) {
          window.history.replaceState(null, document.title, window.location.pathname);
        }
      }, 80);
    }

    $table.find("tbody").on("click", "td.details-control", function () {
      if (!template) {
        return;
      }

      var rowElement = $(this).closest("tr");
      var row = table.row(rowElement);

      if (row.child.isShown()) {
        row.child.hide();
        rowElement.removeClass("shown");
        return;
      }

      row.child(template(row.data())).show();
      rowElement.addClass("shown");
    });
  });
})(jQuery);
