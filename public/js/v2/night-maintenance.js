(function ($) {
  "use strict";

  var $usersTable = $("#nightUsersTable");
  var config = {
    usersUrl: $usersTable.attr("data-users-url"),
    deleteUrl: $usersTable.attr("data-delete-url"),
    csrf: $('meta[name="csrf-token"]').attr("content"),
    global: {
      enabled: $("#nightGlobalEnabled").val() === "1",
      start: $("#nightGlobalStart").val(),
      end: $("#nightGlobalEnd").val(),
      timezone: $("#nightGlobalTimezone").val()
    }
  };
  var table;
  var currentUser;
  var activePolicy = "";
  var globalModal = document.getElementById("nightGlobalModal");
  var userModal = document.getElementById("nightUserModal");
  var globalDialog = window.bootstrap && bootstrap.Modal ? new bootstrap.Modal(globalModal) : null;
  var userDialog = window.bootstrap && bootstrap.Modal ? new bootstrap.Modal(userModal) : null;

  function escapeHtml(value) {
    return $("<div>").text(value == null ? "" : String(value)).html();
  }

  function showModal(instance, element) {
    if (instance) {
      instance.show();
    } else {
      $(element).modal("show");
    }
  }

  function hideModal(instance, element) {
    if (instance) {
      instance.hide();
    } else {
      $(element).modal("hide");
    }
  }

  function feedback(form, message) {
    $(form).find(".night-modal-feedback").text(message || "Unable to save. Please try again.").prop("hidden", false);
  }

  function clearFeedback(form) {
    $(form).find(".night-modal-feedback").text("").prop("hidden", true);
  }

  function successBox(message) {
    if (window.Swal) {
      Swal.fire({ title: "Success", text: message, icon: "success" });
    } else if ($.alert) {
      $.alert({ title: "Success", content: escapeHtml(message), type: "success", backgroundDismiss: true });
    } else {
      window.alert(message);
    }
  }

  function closeWithSuccess(dialog, element, message) {
    $(element).one("hidden.bs.modal", function () { successBox(message); });
    hideModal(dialog, element);
  }

  function statusCell(data, type, row) {
    if (type !== "display") return row.enabled ? "Enabled" : "Disabled";
    if (!row.configured) return '<span class="night-status night-status-unset"><i class="fa fa-circle" aria-hidden="true"></i> Not configured</span>';
    var tone = row.enabled ? "enabled" : "disabled";
    var label = row.enabled ? "Enabled" : "Disabled";
    return '<span class="night-status night-status-' + tone + '"><i class="fa fa-circle" aria-hidden="true"></i> ' + label + '</span>';
  }

  function openUser(row) {
    currentUser = row;
    clearFeedback("#nightUserForm");
    $("#nightUserName").text(row.username);
    $("#nightUserId").val(row.id);
    $("#nightUserEnabled").val(row.setting_id ? (row.enabled ? "1" : "0") : (config.global.enabled ? "1" : "0"));
    $("#nightUserStart").val(row.allowed_start || config.global.start);
    $("#nightUserEnd").val(row.allowed_end || config.global.end);
    $("#nightUserTimezone").val(row.override_timezone || config.global.timezone);
    $("#nightRestoreGlobal").toggle(!!row.setting_id);
    showModal(userDialog, userModal);
  }

  function saveForm(form, dialog, element) {
    clearFeedback(form);
    var button = $(form).find('button[type="submit"]');
    button.prop("disabled", true);

    $.ajax({
      url: $(form).attr("action"),
      method: "POST",
      data: $(form).serialize(),
      dataType: "json",
      headers: { "X-CSRF-TOKEN": config.csrf },
      success: function (response) {
        if (form === "#nightGlobalForm") {
          config.global = {
            enabled: $("#nightGlobalEnabled").val() === "1",
            configured: true,
            start: $("#nightGlobalStart").val(),
            end: $("#nightGlobalEnd").val(),
            timezone: $("#nightGlobalTimezone").val()
          };
          $("#nightGlobalStatus")
            .text(config.global.enabled ? "Enabled" : "Disabled")
            .removeClass("is-enabled is-disabled")
            .addClass(config.global.enabled ? "is-enabled" : "is-disabled");
          $("#nightGlobalSummary").text(config.global.start + " - " + config.global.end + " · " + config.global.timezone);
        }
        closeWithSuccess(dialog, element, response.message || "Maintenance policy saved.");
        table.ajax.reload(null, false);
      },
      error: function (xhr) {
        feedback(form, xhr.responseJSON && xhr.responseJSON.message);
      },
      complete: function () {
        button.prop("disabled", false);
      }
    });
  }

  $(function () {
    table = $usersTable.DataTable({
      autoWidth: false,
      processing: true,
      serverSide: true,
      pageLength: 25,
      order: [[0, "asc"]],
      ajax: {
        url: config.usersUrl,
        data: function (request) { request.policy = activePolicy; }
      },
      dom: "Brt<'v2-users-table-footer'<'v2-users-table-status'i><'v2-users-table-pager'p>>",
      buttons: [{ extend: "excel", text: "Export", title: "Night Maintenance Users" }],
      language: {
        info: "Showing _START_ to _END_ of _TOTAL_ users",
        infoEmpty: "No enabled users to show",
        emptyTable: "No enabled users found",
        zeroRecords: "No users match your search",
        paginate: { previous: "Previous", next: "Next" }
      },
      columns: [
        { data: "username", className: "v2-users-primary-col", render: function (data, type, row) { return type === "display" ? '<span class="v2-users-user-cell"><strong>' + escapeHtml(data) + '</strong><span>ID ' + row.id + '</span></span>' : data; } },
        { data: "role", className: "v2-users-role-col", render: function (data, type) { return type === "display" ? '<span class="v2-users-role-cell"><strong>' + escapeHtml(data || "-") + '</strong></span>' : data; } },
        { data: "enabled", className: "v2-users-status-col", render: statusCell },
        { data: "mode", className: "v2-users-date-col", render: function (data, type, row) { return type === "display" ? '<span class="night-policy ' + (row.setting_id ? 'night-policy-custom' : 'night-policy-global') + '"><span class="night-policy-icon"><i class="fa ' + (row.setting_id ? 'fa-user-clock' : 'fa-globe') + '" aria-hidden="true"></i></span><span class="night-policy-copy"><strong>' + escapeHtml(data) + '</strong><small>' + escapeHtml(row.window) + '</small></span></span>' : data; } },
        { data: null, orderable: false, searchable: false, className: "v2-users-actions-col", render: function () { return '<div class="v2-users-action-group"><button type="button" class="v2-users-action-btn v2-users-action-edit night-edit-user" title="Edit maintenance" aria-label="Edit maintenance"><i class="fa fa-edit"></i></button></div>'; } }
      ],
      createdRow: function (row, data) { $(row).toggleClass("night-override-row", !!data.setting_id); }
    });

    table.on("xhr.dt", function (event, settings, response) {
      if (response && typeof response.overrideCount !== "undefined") {
        $("#nightOverrideCount").text(Number(response.overrideCount).toLocaleString());
      }
    });

    function updateFooterState() {
      var pageInfo = table.page.info();
      $("#nightPageLength").val(String(table.page.len()));
      $("#nightMaintenancePanel")
        .toggleClass("v2-users-single-page", pageInfo.pages <= 1)
        .toggleClass("has-single-page", pageInfo.pages <= 1);
    }
    table.on("draw.dt init.dt", updateFooterState);
    updateFooterState();

    $("#nightPageLength").on("change", function () { table.page.len(parseInt(this.value, 10)).draw(); });
    $("#nightRefresh").on("click", function () { table.ajax.reload(null, false); });
    $(".night-filter").on("click", function () {
      activePolicy = $(this).data("policy") || "";
      $(".night-filter").removeClass("is-active").attr("aria-pressed", "false");
      $(this).addClass("is-active").attr("aria-pressed", "true");
      table.page("first").draw();
    });
    $("#nightExport").on("click", function () { table.button(".buttons-excel").trigger(); });
    var searchTimer;
    $("#nightSearch").on("input", function () {
      var value = this.value;
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function () { table.search(value).draw(); }, 250);
    });
    $("#nightOpenGlobal").on("click", function () { clearFeedback("#nightGlobalForm"); showModal(globalDialog, globalModal); });
    $("#nightUsersTable").on("click", ".night-edit-user", function () { openUser(table.row($(this).closest("tr")).data()); });
    $("#nightGlobalForm").on("submit", function (event) { event.preventDefault(); saveForm("#nightGlobalForm", globalDialog, globalModal); });
    $("#nightUserForm").on("submit", function (event) { event.preventDefault(); saveForm("#nightUserForm", userDialog, userModal); });
    $("#nightRestoreGlobal").on("click", function () {
      if (!currentUser || !currentUser.setting_id) return;
      var button = $(this).prop("disabled", true);
      clearFeedback("#nightUserForm");
      $.ajax({
        url: config.deleteUrl + currentUser.setting_id,
        method: "DELETE",
        dataType: "json",
        headers: { "X-CSRF-TOKEN": config.csrf },
        success: function (response) {
          closeWithSuccess(userDialog, userModal, response.message || "User restored to global policy.");
          table.ajax.reload(null, false);
        },
        error: function (xhr) { feedback("#nightUserForm", xhr.responseJSON && xhr.responseJSON.message); },
        complete: function () { button.prop("disabled", false); }
      });
    });
  });
})(jQuery);
