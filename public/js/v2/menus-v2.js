(function ($) {
  'use strict';

  var $app = $('#menus-v2-app');
  if (!$app.length) {
    return;
  }

  var baseUrl = String($app.data('base-url') || '').replace(/\/$/, '');
  var csrfToken = String($app.data('csrf') || '');
  var selectedGroupId = Number($app.data('selected-group-id') || 0);
  var endpoints = {
    data: String($app.data('data-url') || ''),
    save: String($app.data('save-url') || ''),
    reorder: String($app.data('reorder-url') || ''),
    statusBase: String($app.data('status-base') || '').replace(/\/$/, ''),
    removeBase: String($app.data('remove-base') || '').replace(/\/$/, '')
  };
  var existingMenus = [];
  var cache = {};
  var dataRequest = null;
  var searchTimer = null;
  var busyCount = 0;
  var formDirty = false;
  var orderDirty = false;

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function norm(value) {
    return $.trim(String(value || '')).toLowerCase();
  }

  function slugifyTitle(value) {
    return $.trim(String(value || ''))
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function collectExistingMenus() {
    existingMenus = $('#v2-menu-tree .dd-item').map(function () {
      var $item = $(this);
      var $row = $item.children('.v2-tree-row');
      return {
        id: Number($item.data('id') || 0),
        parent_id: Number($item.parents('.dd-item').first().data('id') || 0),
        name: $.trim($row.find('.v2-tree-copy strong').first().text()),
        url: $.trim($row.data('menu-url') || ''),
        icon: $.trim($row.find('.v2-tree-icon i').attr('class') || ''),
        section: $.trim($row.data('menu-section') || 'services')
      };
    }).get();
  }

  function toast(type, message) {
    if (window.Swal) {
      window.Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        timer: 2300,
        showConfirmButton: false
      });
      return;
    }

    window.alert(message);
  }

  function errorMessage(error) {
    if (error && error.status === 419) {
      return 'Session expired. Refresh the page and sign in again.';
    }
    if (error && error.status === 403) {
      return 'Permission denied for this menu action.';
    }
    if (error && error.status >= 500) {
      return 'Server error while processing the request.';
    }
    return error && error.message ? error.message : 'Request failed. Please try again.';
  }

  function showError(error, retry) {
    if (window.Swal && retry) {
      window.Swal.fire({
        icon: 'error',
        title: errorMessage(error),
        showCancelButton: true,
        confirmButtonText: 'Retry',
        cancelButtonText: 'Close'
      }).then(function (result) {
        if (result.isConfirmed) {
          retry();
        }
      });
      return;
    }

    toast('error', errorMessage(error));
  }

  function requestJson(url, options) {
    options = options || {};
    options.credentials = 'same-origin';
    options.headers = $.extend({
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrfToken
    }, options.headers || {});

    return window.fetch(url, options).then(function (response) {
      if (response.redirected && response.url && response.url.indexOf('/login') !== -1) {
        var sessionError = new Error('Session expired.');
        sessionError.status = 419;
        throw sessionError;
      }

      return response.text().then(function (text) {
        var payload = {};
        if (text) {
          try {
            payload = JSON.parse(text);
          } catch (e) {
            payload = { message: text };
          }
        }

        if (!response.ok) {
          var error = new Error(payload.message || 'Request failed.');
          error.status = response.status;
          error.errors = payload.errors || {};
          throw error;
        }

        return payload;
      });
    });
  }

  function setBusy(isBusy) {
    busyCount += isBusy ? 1 : -1;
    if (busyCount < 0) {
      busyCount = 0;
    }

    $('.menu-v2-btn, .menu-v2-mini-btn, .js-status-toggle').prop('disabled', busyCount > 0);
    $('.menu-v2-create').attr('aria-disabled', busyCount > 0 ? 'true' : 'false');
    $('#menu-search-spinner').toggleClass('is-visible', busyCount > 0);

    if (busyCount === 0) {
      validateForm();
    }
  }

  function setTreeLoading(isLoading, skeleton) {
    var hasTree = $('#v2-menu-tree .dd-item').length > 0;
    $('.menu-v2-tree-wrap').toggleClass('menu-v2-loading', !!isLoading);
    $('#menu-tree-skeleton').toggleClass('is-visible', !!isLoading && (!!skeleton || !hasTree));
    $('#menu-search-spinner').toggleClass('is-visible', !!isLoading);
  }

  function setFormDirty(value) {
    formDirty = !!value;
    $('#form-dirty-badge').toggleClass('is-visible', formDirty);
  }

  function setOrderDirty(value) {
    orderDirty = !!value;
    $('#order-dirty-badge').toggleClass('is-visible', orderDirty);
    $('#reorder-form').toggleClass('is-dirty', orderDirty);
    $('#reorder-label').text(orderDirty ? 'Save order *' : 'Save order');
    $('#reorder-help').text(orderDirty ? 'Menu order changed. Save order before leaving this page.' : 'Maximum nesting is limited to 3 levels for safer sidebar rendering.');
  }

  function fieldError(field, message) {
    $('#' + field + '-live-error').text(message || '');
  }

  function clearErrors() {
    $('.js-field-error').text('');
  }

  function applyErrors(errors) {
    clearErrors();
    $.each(errors || {}, function (field, messages) {
      fieldError(field.replace(/\./g, '_'), $.isArray(messages) ? messages[0] : messages);
    });
  }

  function addIconChoice(icon) {
    icon = $.trim(icon || '');
    if (!icon || $('#menu-icon-picker .menu-v2-icon-choice').filter(function () {
      return String($(this).data('icon') || '') === icon;
    }).length) {
      return;
    }

    $('#menu-icon-picker').append(
      '<button type="button" class="menu-v2-icon-choice" data-icon="' + esc(icon) + '" title="' + esc(icon) + '" aria-label="Use icon ' + esc(icon) + '">' +
        '<i class="' + esc(icon) + '" aria-hidden="true"></i>' +
      '</button>'
    );
    $('#menu-icon-options').append('<option value="' + esc(icon) + '"></option>');
  }

  function syncPreview() {
    var title = $.trim($('#name').val());
    var url = slugifyTitle(title);
    var icon = $.trim($('#menu_icon').val()) || 'fa fa-sitemap';

    $('#url').val(url);
    $('#menu-title-preview').text(title || 'Enter menu details');
    $('#menu-url-preview').text(url || 'URL not set');
    $('#url-full-preview').text(url ? 'Generated URL: ' + baseUrl + '/' + url : 'URL is generated automatically from the title.');
    $('#menu-icon-preview').attr('class', icon);
    $('.menu-v2-icon-choice').removeClass('is-active').filter(function () {
      return String($(this).data('icon') || '') === icon;
    }).addClass('is-active');
  }

  function validateForm() {
    var id = Number($('#menu-save-form input[name="id"]').val() || 0);
    var parentId = Number($('#parent_id').val() || 0);
    var name = $.trim($('#name').val() || '');
    var url = slugifyTitle(name);
    var icon = $.trim($('#menu_icon').val() || '');
    var section = $.trim($('#section').val() || '');
    var position = $.trim($('#position').val() || '');
    var valid = true;

    $('#url').val(url);
    clearErrors();

    if (!name || norm(name) === 'menu title') {
      fieldError('name', 'Enter a real menu title.');
      valid = false;
    }

    if (!url) {
      fieldError('url', 'URL could not be generated. Use letters or numbers in the menu title.');
      valid = false;
    }

    if (!icon || norm(icon) === 'fa fa-circle-o') {
      fieldError('menu_icon', 'Choose a specific icon class, for example fa fa-users.');
      valid = false;
    }

    if (!section) {
      fieldError('section', 'Choose a sidebar section.');
      valid = false;
    }

    if (!position) {
      fieldError('position', 'Choose where this menu should appear.');
      valid = false;
    }

    if (url) {
      var duplicateUrl = existingMenus.some(function (menu) {
        return Number(menu.id) !== id && norm(menu.url) === norm(url);
      });
      if (duplicateUrl) {
        fieldError('url', 'This generated URL already exists in the selected user group.');
        valid = false;
      }
    }

    if (name && url) {
      var duplicateNameUrl = existingMenus.some(function (menu) {
        return Number(menu.id) !== id &&
          Number(menu.parent_id || 0) === parentId &&
          norm(menu.name) === norm(name) &&
          norm(menu.url) === norm(url);
      });
      if (duplicateNameUrl) {
        fieldError('name', 'Same title and generated URL already exist under this parent.');
        fieldError('url', 'Same title and generated URL already exist under this parent.');
        valid = false;
      }
    }

    $('#menu-save-btn').prop('disabled', !valid || busyCount > 0);
    return valid;
  }

  function expandedIds() {
    return $('#v2-menu-tree .dd-item').map(function () {
      return !$(this).hasClass('dd-collapsed') ? String($(this).data('id')) : null;
    }).get();
  }

  function restoreExpanded(ids) {
    ids = ids || [];
    $('#v2-menu-tree .dd-item').each(function () {
      var $item = $(this);
      if (ids.indexOf(String($item.data('id'))) !== -1) {
        $item.removeClass('dd-collapsed').children('ol.dd-list').show();
      }
    });
  }

  function syncOrder() {
    var $tree = $('#v2-menu-tree');
    if (!$tree.length || !$.fn.nestable || !$tree.find('.dd-item').length) {
      $('#reorder').val('');
      return;
    }
    $('#reorder').val(JSON.stringify($tree.nestable('serialize')));
  }

  function initNestable(openIds) {
    var $tree = $('#v2-menu-tree');
    $tree.off('change.menuAjax');

    if (!$tree.length || !$tree.find('.dd-item').length || !$.fn.nestable) {
      syncOrder();
      return;
    }

    $tree.nestable({ maxDepth: 3 });
    if (openIds && openIds.length) {
      restoreExpanded(openIds);
    } else {
      $tree.nestable('collapseAll');
    }
    syncOrder();
    $tree.on('change.menuAjax', function () {
      syncOrder();
      setOrderDirty(true);
    });
  }

  function renderStats(stats) {
    stats = stats || {};
    $('[data-stat="total"]').text(Number(stats.total || 0));
    $('[data-stat="active"]').text(Number(stats.active || 0));
    $('[data-stat="inactive"]').text(Number(stats.inactive || 0));
    $('[data-stat="root"]').text(Number(stats.root || 0));

    var $meta = $('#menu-v2-groups .menu-v2-group[data-group-id="' + selectedGroupId + '"] small');
    if ($meta.length) {
      var oldText = $meta.text();
      var suffix = oldText.indexOf('·') >= 0 ? oldText.slice(oldText.indexOf('·')) : '· Group active';
      $meta.text(Number(stats.total || 0) + ' menus ' + suffix);
    }
  }

  function renderTree(data, openIds) {
    var html = data.tree_html || '';
    var hasTree = $.trim(html).length > 0;
    var search = $.trim(data.search || $('#menu-tree-search').val() || '');

    $('#v2-menu-tree .dd-list').html(html);
    $('#v2-menu-tree').toggleClass('d-none', !hasTree);
    $('#menu-tree-empty').toggleClass('d-none', hasTree);
    $('#menu-tree-empty i').attr('class', 'fa ' + (search ? 'fa-search' : 'fa-sitemap'));
    $('#menu-tree-empty strong').text(search ? 'No matching menus.' : 'No menus configured for this group.');
    $('#menu-tree-empty p').text(search ? 'Clear the search box to show the full menu tree.' : 'Create the first menu from the form on the right.');
    initNestable(openIds);
  }

  function renderParentOptions(items, row) {
    row = row || {};
    var currentId = Number(row.id || 0);
    var parentId = Number(row.parent_id || 0);
    var html = '<option value="0">Root level</option>';

    $.each(items || [], function (_, item) {
      if (Number(item.id) === currentId) {
        return;
      }
      html += '<option value="' + Number(item.id) + '"' + (Number(item.id) === parentId ? ' selected' : '') + '>' + esc(item.label || item.name || '') + '</option>';
    });

    $('#parent_id').html(html);
  }

  function renderForm(row, transLang) {
    row = row || {};
    var editing = Number(row.id || 0) > 0;

    $('#menu-form-card > .menu-v2-card-head .menu-v2-card-title').text(editing ? 'Edit menu' : 'Create menu');
    $('#menu-form-card > .menu-v2-card-head .menu-v2-card-subtitle').text(editing ? 'Update the selected menu item.' : 'Add a new item to this group.');
    $('#menu-save-form input[name="id"]').val(row.id || '');
    $('#menu-save-form input[name="group_id"]').val(selectedGroupId);
    $('#menu-save-form input[name="ordering"]').val(row.ordering || '');
    $('#name').val(row.name || '');
    $('#url').val(row.url || '');
    $('#menu_icon').val(row.icon || '');
    $('#section').val(row.section || 'services');
    $('#position').val(row.position || 'sidebar');
    $('input[name="is_active"][value="' + String(row.status == null ? 1 : row.status) + '"]').prop('checked', true);

    if ($('#language_title_fr').length) {
      $('#language_title_fr').val(transLang && transLang.title && transLang.title.fr ? transLang.title.fr : '');
    }

    $('.js-delete-menu').toggleClass('d-none', !editing);
    $('#delete-menu-form').attr('action', editing ? endpoints.removeBase + '/' + Number(row.id) + '?template=' + selectedGroupId : '#');
    syncPreview();
    setFormDirty(false);
    validateForm();
  }

  function renderAudit(logs) {
    var $body = $('#menu-audit-body');
    if (!$body.length) {
      return;
    }

    if (!logs || !logs.length) {
      $body.html('<div class="menu-v2-empty"><i class="fa fa-history" aria-hidden="true"></i><strong>No menu audit records yet.</strong><p>Create, update, delete, or reorder a V2 menu to generate an audit entry.</p></div>');
      return;
    }

    var html = '<div class="menu-v2-audit-list">';
    $.each(logs, function (_, log) {
      html += '<div class="menu-v2-audit-item"><div class="menu-v2-audit-main"><span class="menu-v2-audit-action"><i class="fa fa-history" aria-hidden="true"></i>' + esc(String(log.action || '').replace(/_/g, ' ')) + '</span><span class="v2-status is-active">' + esc(log.module || 'menus-v2') + '</span></div><div class="menu-v2-audit-meta"><span>User #' + esc(log.user_id || 'system') + '</span><span>' + esc(log.ip_address || 'no-ip') + '</span><span>' + esc(log.created_at || '') + '</span></div><details class="menu-v2-audit-details"><summary>View values</summary><pre>Old: ' + esc(String(log.old_values || '').slice(0, 260)) + '\nNew: ' + esc(String(log.new_values || '').slice(0, 260)) + '</pre></details></div>';
    });
    html += '</div>';
    $body.html(html);
  }

  function renderPayload(data, options) {
    options = options || {};
    data = data || {};
    selectedGroupId = Number(data.group_id || selectedGroupId);
    existingMenus = data.existing_menus || existingMenus;

    $.each(existingMenus || [], function (_, menu) {
      addIconChoice(menu.icon || '');
    });
    if (data.row && data.row.icon) {
      addIconChoice(data.row.icon);
    }

    $('#menu-save-form input[name="group_id"]').val(selectedGroupId);
    $('#reorder-form input[name="user_group_id"]').val(selectedGroupId);
    $('#menu-v2-groups .menu-v2-group').removeClass('is-active');
    $('#menu-v2-groups .menu-v2-group[data-group-id="' + selectedGroupId + '"]').addClass('is-active');
    renderStats(data.stats || {});
    renderTree(data, options.openIds || []);

    if (!options.keepForm) {
      renderParentOptions(data.flat_menus || [], data.row || {});
      renderForm(data.row || {}, data.trans_lang || {});
    }

    renderAudit(data.audit_logs || []);
    if (data.search !== undefined) {
      $('#menu-tree-search').val(data.search || '');
    }
    if (options.clearOrder !== false) {
      setOrderDirty(false);
    }
  }

  function cacheKey(groupId, editId, search) {
    return Number(groupId || selectedGroupId) + '|' + Number(editId || 0) + '|' + $.trim(search || '');
  }

  function clearGroupCache(groupId) {
    var prefix = Number(groupId || selectedGroupId) + '|';
    $.each(Object.keys(cache), function (_, key) {
      if (key.indexOf(prefix) === 0) {
        delete cache[key];
      }
    });
  }

  function loadData(options) {
    options = options || {};
    var groupId = Number(options.groupId || selectedGroupId);
    var editId = Number(options.editId || 0);
    var search = $.trim(options.search == null ? $('#menu-tree-search').val() : options.search);
    var key = cacheKey(groupId, editId, search);
    var openIds = options.openIds || expandedIds();

    if (options.useCache !== false && cache[key]) {
      renderPayload(cache[key], $.extend({}, options, { openIds: openIds }));
      return;
    }

    if (dataRequest && dataRequest.abort) {
      dataRequest.abort();
    }
    dataRequest = window.AbortController ? new window.AbortController() : null;

    var params = new URLSearchParams();
    params.set('template', groupId);
    params.set('edit_id', editId);
    if (search) {
      params.set('search', search);
    }

    setBusy(true);
    setTreeLoading(true, options.skeleton);

    requestJson(endpoints.data + '?' + params.toString(), {
      method: 'GET',
      signal: dataRequest ? dataRequest.signal : undefined
    }).then(function (response) {
      var data = response.data || {};
      cache[key] = data;
      renderPayload(data, $.extend({}, options, { openIds: openIds }));
    }).catch(function (error) {
      if (error && error.name === 'AbortError') {
        return;
      }
      showError(error, function () {
        loadData(options);
      });
    }).finally(function () {
      setTreeLoading(false);
      setBusy(false);
    });
  }

  function saveMenu(form) {
    syncPreview();
    if (!validateForm()) {
      return;
    }

    setBusy(true);
    setTreeLoading(true, false);

    requestJson(form.action, {
      method: 'POST',
      body: new window.FormData(form)
    }).then(function (response) {
      clearGroupCache(selectedGroupId);
      clearErrors();
      renderPayload(response.data || {}, { openIds: expandedIds() });
      setFormDirty(false);
      if (response.data && response.data.row && response.data.row.id) {
        window.history.pushState({}, '', baseUrl + '/menus-v2/' + Number(response.data.row.id) + '?template=' + selectedGroupId);
      }
      toast('success', response.message || 'Menu saved successfully.');
    }).catch(function (error) {
      if (error.status === 422) {
        applyErrors(error.errors || {});
        toast('error', error.message || 'Please correct the highlighted fields.');
        validateForm();
        return;
      }
      showError(error, function () {
        saveMenu(form);
      });
    }).finally(function () {
      setTreeLoading(false);
      setBusy(false);
    });
  }

  function saveOrder(form) {
    syncOrder();
    setBusy(true);
    setTreeLoading(true, false);

    requestJson(form.action, {
      method: 'POST',
      body: new window.FormData(form)
    }).then(function (response) {
      clearGroupCache(selectedGroupId);
      renderPayload(response.data || {}, { keepForm: true, openIds: expandedIds() });
      setOrderDirty(false);
      toast('success', response.message || 'Menu order saved.');
    }).catch(function (error) {
      showError(error, function () {
        saveOrder(form);
      });
    }).finally(function () {
      setTreeLoading(false);
      setBusy(false);
    });
  }

  function deleteMenu(form) {
    if ($(form).attr('action') === '#') {
      return;
    }

    var runDelete = function () {
      setBusy(true);
      setTreeLoading(true, false);

      requestJson(form.action, {
        method: 'POST',
        body: new window.FormData(form)
      }).then(function (response) {
        clearGroupCache(selectedGroupId);
        renderPayload(response.data || {}, { openIds: expandedIds() });
        setFormDirty(false);
        window.history.pushState({}, '', baseUrl + '/menus-v2?template=' + selectedGroupId);
        toast('success', response.message || 'Menu deleted.');
      }).catch(function (error) {
        showError(error, function () {
          deleteMenu(form);
        });
      }).finally(function () {
        setTreeLoading(false);
        setBusy(false);
      });
    };

    if (window.Swal) {
      window.Swal.fire({
        icon: 'warning',
        title: 'Delete this menu?',
        text: 'Child menus will move to the deleted menu parent.',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
      }).then(function (result) {
        if (result.isConfirmed) {
          runDelete();
        }
      });
    } else if (window.confirm('Delete this menu? Child menus will move to the deleted menu parent.')) {
      runDelete();
    }
  }

  function changeStatus(button) {
    var $button = $(button);
    var id = Number($button.data('id') || 0);
    var status = Number($button.data('next-status'));
    if (!id || (status !== 0 && status !== 1)) {
      return;
    }

    var body = new window.FormData();
    body.append('group_id', selectedGroupId);
    body.append('status', status);

    setBusy(true);
    setTreeLoading(true, false);

    requestJson(endpoints.statusBase + '/' + id, {
      method: 'POST',
      body: body
    }).then(function (response) {
      clearGroupCache(selectedGroupId);
      renderPayload(response.data || {}, { keepForm: true, openIds: expandedIds() });
      toast('success', response.message || 'Menu status updated.');
    }).catch(function (error) {
      showError(error, function () {
        changeStatus(button);
      });
    }).finally(function () {
      setTreeLoading(false);
      setBusy(false);
    });
  }

  $(function () {
    collectExistingMenus();
    initNestable([]);
    syncPreview();
    validateForm();

    $('#name, #menu_icon').on('input.menuAjax', function () {
      setFormDirty(true);
      syncPreview();
      validateForm();
    });

    $('#parent_id, #section, #position, input[name="is_active"], #language_title_fr').on('input.menuAjax change.menuAjax', function () {
      setFormDirty(true);
      validateForm();
    });

    $('#menu-icon-picker').on('click.menuAjax', '.menu-v2-icon-choice', function () {
      $('#menu_icon').val(String($(this).data('icon') || ''));
      setFormDirty(true);
      syncPreview();
      validateForm();
    });

    $('#menu-save-form').on('submit.menuAjax', function (event) {
      event.preventDefault();
      saveMenu(this);
    });

    $('#reorder-form').on('submit.menuAjax', function (event) {
      event.preventDefault();
      saveOrder(this);
    });

    $('#delete-menu-form').on('submit.menuAjax', function (event) {
      event.preventDefault();
      deleteMenu(this);
    });

    $('.js-delete-menu').on('click.menuAjax', function (event) {
      event.preventDefault();
      $('#delete-menu-form').trigger('submit');
    });

    $('.js-menu-add, .js-menu-clear').on('click.menuAjax', function (event) {
      event.preventDefault();
      loadData({
        groupId: selectedGroupId,
        editId: 0,
        search: $('#menu-tree-search').val(),
        useCache: false,
        openIds: []
      });
      window.history.pushState({}, '', baseUrl + '/menus-v2?template=' + selectedGroupId);
    });

    $('#expand-all').on('click.menuAjax', function () {
      if ($.fn.nestable) {
        $('#v2-menu-tree').nestable('expandAll');
      }
    });

    $('#collapse-all').on('click.menuAjax', function () {
      if ($.fn.nestable) {
        $('#v2-menu-tree').nestable('collapseAll');
      }
    });

    $('#menu-tree-search').on('input.menuAjax', function () {
      var query = this.value;
      window.clearTimeout(searchTimer);
      searchTimer = window.setTimeout(function () {
        loadData({
          groupId: selectedGroupId,
          editId: Number($('#menu-save-form input[name="id"]').val() || 0),
          search: query,
          keepForm: true,
          clearOrder: false,
          useCache: false,
          openIds: expandedIds()
        });
      }, 300);
    });

    $('#menu-v2-groups').on('click.menuAjax', '.menu-v2-group', function (event) {
      event.preventDefault();
      selectedGroupId = Number($(this).data('group-id') || selectedGroupId);
      $('#menu-tree-search').val('');
      $('#menu-v2-groups .menu-v2-group').removeClass('is-active');
      $(this).addClass('is-active');
      setFormDirty(false);
      setOrderDirty(false);
      window.history.pushState({}, '', this.href);
      loadData({
        groupId: selectedGroupId,
        editId: 0,
        search: '',
        skeleton: true,
        useCache: true,
        openIds: []
      });
    });

    $('#v2-menu-tree').on('click.menuAjax', '.v2-tree-edit', function (event) {
      event.preventDefault();
      var id = Number($(this).data('id') || $(this).closest('.dd-item').data('id') || 0);
      if (!id) {
        return;
      }
      window.history.pushState({}, '', this.href);
      loadData({
        groupId: selectedGroupId,
        editId: id,
        search: $('#menu-tree-search').val(),
        useCache: false,
        openIds: expandedIds()
      });
    });

    $('#v2-menu-tree').on('click.menuAjax', '.js-status-toggle', function (event) {
      event.preventDefault();
      changeStatus(this);
    });

    window.addEventListener('beforeunload', function (event) {
      if (!formDirty && !orderDirty) {
        return;
      }
      event.preventDefault();
      event.returnValue = '';
    });
  });
})(jQuery);
