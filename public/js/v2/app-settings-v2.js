(function ($) {
  'use strict';

  var $app = $('#app-settings-v2');
  if (!$app.length) {
    return;
  }

  var csrfToken = String($app.data('csrf') || '');
  var saveUrl = String($app.data('save-url') || '');
  var isDirty = false;
  var isSaving = false;
  var $sectionStack = $('.app-settings-v2-stack');
  var $sections = $('.app-settings-v2-section');
  var $sectionLinks = $('.app-settings-v2-section-link');
  var $appearanceTabs = $('[data-appearance-mode]');
  var $themeGroups = $('[data-theme-mode]');
  var allowedLogoTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/bmp', 'image/x-ms-bmp', 'image/webp'];
  var allowedLogoExtension = /\.(png|jpe?g|gif|bmp|webp)$/i;
  var maxLogoSize = 2 * 1024 * 1024;
  var themeColorFields = [
    'theme_primary_color',
    'theme_accent_color',
    'theme_login_color',
    'theme_header_color',
    'theme_header_text_color',
    'theme_sidebar_color',
    'theme_sidebar_active_color',
    'theme_sidebar_text_color',
    'theme_button_color',
    'theme_button_text_color',
    'theme_dashboard_background_color',
    'theme_dashboard_card_color',
    'theme_dashboard_text_color',
    'theme_dashboard_muted_color',
    'theme_dashboard_border_color',
    'theme_dark_surface_color',
    'theme_dark_card_color',
    'theme_dark_text_color',
    'theme_dark_muted_color',
    'theme_dark_border_color'
  ];
  var themeCssMap = {
    theme_primary_color: ['--theme-primary', '--legacy-brand-blue', '--theme-default', '--theme-deafult', '--v2-nav-blue', '--v2-nav-hover-ink', '--v2-nav-hover-icon', '--dash-blue', '--dash-violet', '--profile-v2-blue', '--menu-v2-blue', '--ccpl-primary'],
    theme_accent_color: ['--theme-accent', '--legacy-brand-sky', '--v2-header-blue', '--dash-cyan'],
    theme_login_color: ['--theme-login-primary'],
    theme_header_color: ['--theme-header-bg', '--v2-header-primary', '--v2-header-primary-dark'],
    theme_header_text_color: ['--theme-header-text', '--v2-header-ink'],
    theme_sidebar_color: ['--theme-sidebar-bg'],
    theme_sidebar_active_color: ['--theme-sidebar-active', '--v2-sidebar-active-start', '--v2-nav-primary', '--v2-nav-blue-dark'],
    theme_sidebar_text_color: ['--theme-sidebar-text'],
    theme_button_color: ['--theme-button-bg', '--settings-v2-primary', '--profile-v2-blue-dark', '--ccpl-primary-dark'],
    theme_button_text_color: ['--theme-button-text'],
    theme_dashboard_background_color: ['--theme-dashboard-bg'],
    theme_dashboard_card_color: ['--theme-dashboard-card'],
    theme_dashboard_text_color: ['--theme-dashboard-text', '--dash-ink-1'],
    theme_dashboard_muted_color: ['--theme-dashboard-muted', '--dash-ink-2'],
    theme_dashboard_border_color: ['--theme-dashboard-border'],
    theme_dark_surface_color: ['--theme-dark-surface'],
    theme_dark_card_color: ['--theme-dark-card'],
    theme_dark_text_color: ['--theme-dark-text'],
    theme_dark_muted_color: ['--theme-dark-muted'],
    theme_dark_border_color: ['--theme-dark-border']
  };
  var themeRgbMap = {
    theme_primary_color: ['--theme-primary-rgb'],
    theme_accent_color: ['--theme-accent-rgb'],
    theme_login_color: ['--theme-login-rgb'],
    theme_header_color: ['--theme-header-rgb'],
    theme_header_text_color: ['--theme-header-text-rgb'],
    theme_sidebar_color: ['--theme-sidebar-rgb'],
    theme_sidebar_active_color: ['--theme-sidebar-active-rgb'],
    theme_sidebar_text_color: ['--theme-sidebar-text-rgb'],
    theme_button_color: ['--theme-button-rgb'],
    theme_dashboard_background_color: ['--theme-dashboard-bg-rgb'],
    theme_dashboard_card_color: ['--theme-dashboard-card-rgb'],
    theme_dashboard_text_color: ['--theme-dashboard-text-rgb'],
    theme_dashboard_muted_color: ['--theme-dashboard-muted-rgb'],
    theme_dashboard_border_color: ['--theme-dashboard-border-rgb'],
    theme_dark_surface_color: ['--theme-dark-surface-rgb'],
    theme_dark_card_color: ['--theme-dark-card-rgb'],
    theme_dark_text_color: ['--theme-dark-text-rgb'],
    theme_dark_muted_color: ['--theme-dark-muted-rgb'],
    theme_dark_border_color: ['--theme-dark-border-rgb']
  };

  function toast(type, message) {
    if (window.Swal) {
      window.Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        timer: 2400,
        showConfirmButton: false
      });
      return;
    }

    window.alert(message);
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
        var sessionError = new Error('Session expired. Refresh the page and sign in again.');
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

  function errorMessage(error) {
    if (error && error.status === 419) {
      return 'Session expired. Refresh the page and sign in again.';
    }
    if (error && error.status === 403) {
      return 'Permission denied for this settings action.';
    }
    if (error && error.status >= 500) {
      return 'Server error while saving settings.';
    }
    return error && error.message ? error.message : 'Unable to save settings.';
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

  function setDirty(value) {
    isDirty = !!value;
    $('#settings-dirty-badge').toggleClass('is-visible', isDirty);
    $('#settings-save-state').text(isDirty ? 'Unsaved changes are ready.' : 'Ready to save changes.');
  }

  function setSaving(value) {
    isSaving = !!value;
    $('#settings-save-btn').prop('disabled', isSaving);
    $('#settings-save-btn span').text(isSaving ? 'Saving...' : 'Save settings');
    $('.app-settings-v2-panel, .app-settings-v2-actions').toggleClass('app-settings-v2-saving', isSaving);
  }

  function clearErrors() {
    $('.app-settings-v2-error').text('');
  }

  function setFieldError(field, message) {
    $('[data-error-for="' + field + '"]').text(message || '');
  }

  function normalizedSectionId(hash) {
    var sectionId = $.trim(String(hash || ''));
    if (!sectionId) {
      sectionId = $sections.first().attr('id') || '';
    }

    if (sectionId.charAt(0) === '#') {
      sectionId = sectionId.substring(1);
    }

    return sectionId;
  }

  function activateSection(sectionId, options) {
    options = options || {};
    sectionId = normalizedSectionId(sectionId);

    var $targetSection = sectionId ? $sections.filter('#' + sectionId) : $();
    if (!$targetSection.length) {
      $targetSection = $sections.first();
      sectionId = $targetSection.attr('id') || '';
    }

    if (!$targetSection.length) {
      return;
    }

    $sectionStack.addClass('is-sectioned');
    $sections.removeClass('is-active').attr('aria-hidden', 'true');
    $targetSection.addClass('is-active').attr('aria-hidden', 'false');

    $sectionLinks.removeClass('is-active').removeAttr('aria-current');
    $sectionLinks.each(function () {
      var href = $(this).attr('href') || '';
      var linkSectionId = normalizedSectionId(href);
      var isActive = linkSectionId === sectionId;
      $(this)
        .toggleClass('is-active', isActive)
        .attr('aria-current', isActive ? 'page' : null);
    });

    if (sectionId && options.updateHash !== false && window.history && window.history.replaceState) {
      window.history.replaceState(null, '', '#' + sectionId);
    }

    if (options.scroll !== false && $targetSection[0] && typeof $targetSection[0].scrollIntoView === 'function') {
      $targetSection[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function activateSectionForField(field) {
    if (!field) {
      return;
    }

    var $field = $('[name="' + field + '"], #' + field).first();
    if (!$field.length) {
      return;
    }

    var $section = $field.closest('.app-settings-v2-section');
    if ($section.length) {
      activateSection($section.attr('id'), { scroll: true });
    }

    var fieldMode = $field.closest('[data-theme-mode]').data('theme-mode');
    if (fieldMode === 'light' || fieldMode === 'dark') {
      activateAppearanceMode(fieldMode);
    }
  }

  function activateFirstErrorSection(errors) {
    var firstField = '';

    $.each(errors || {}, function (field) {
      firstField = field;
      return false;
    });

    if (!firstField) {
      $('.app-settings-v2-error').each(function () {
        if ($.trim($(this).text())) {
          firstField = $(this).data('error-for') || '';
          return false;
        }
      });
    }

    activateSectionForField(firstField);
  }

  function activateAppearanceMode(mode) {
    mode = mode === 'dark' ? 'dark' : 'light';

    $appearanceTabs.each(function () {
      var isActive = $(this).data('appearance-mode') === mode;
      $(this)
        .toggleClass('is-active', isActive)
        .attr('aria-selected', isActive ? 'true' : 'false');
    });

    $themeGroups.each(function () {
      var groupMode = $(this).data('theme-mode');
      $(this).toggleClass('is-mode-hidden', groupMode !== 'shared' && groupMode !== mode);
    });
  }

  function normalizeHex(value) {
    value = $.trim(String(value || '')).toUpperCase();
    if (value.charAt(0) !== '#') {
      value = '#' + value;
    }

    return /^#[0-9A-F]{6}$/.test(value) ? value : '';
  }

  function hexToRgb(value) {
    var normalized = normalizeHex(value);
    if (!normalized) {
      return '';
    }

    return [
      parseInt(normalized.substr(1, 2), 16),
      parseInt(normalized.substr(3, 2), 16),
      parseInt(normalized.substr(5, 2), 16)
    ].join(', ');
  }

  function colorLuma(value) {
    var normalized = normalizeHex(value);
    if (!normalized) {
      return 0;
    }

    return (
      (parseInt(normalized.substr(1, 2), 16) * 299) +
      (parseInt(normalized.substr(3, 2), 16) * 587) +
      (parseInt(normalized.substr(5, 2), 16) * 114)
    ) / 1000;
  }

  function colorLooksDark(value) {
    return colorLuma(value) < 110;
  }

  function collectThemeValues() {
    var values = {};

    $.each(themeColorFields, function (_, field) {
      values[field] = normalizeHex($('#' + field).val());
    });

    return values;
  }

  function repairedLightThemeValues(values) {
    var repaired = $.extend({}, values);
    var lightSurfaceLooksDark = colorLooksDark(repaired.theme_dashboard_background_color) &&
      colorLooksDark(repaired.theme_dashboard_card_color);
    var lightSurfaceMatchesDark = repaired.theme_dashboard_background_color === repaired.theme_dark_surface_color &&
      repaired.theme_dashboard_card_color === repaired.theme_dark_card_color;

    if (!lightSurfaceLooksDark && !lightSurfaceMatchesDark) {
      return repaired;
    }

    if (colorLooksDark(repaired.theme_header_color)) {
      repaired.theme_header_color = '#FFFFFF';
      repaired.theme_header_text_color = '#1F2937';
    }

    if (colorLooksDark(repaired.theme_sidebar_color)) {
      repaired.theme_sidebar_color = '#FFFFFF';
      repaired.theme_sidebar_text_color = '#1F2937';
    }

    repaired.theme_dashboard_background_color = '#F8F6F4';
    repaired.theme_dashboard_card_color = '#FFFFFF';
    repaired.theme_dashboard_text_color = '#1F2937';
    repaired.theme_dashboard_muted_color = '#6B7280';
    repaired.theme_dashboard_border_color = '#E7DED7';

    return repaired;
  }

  function setRootVar(name, value) {
    window.document.documentElement.style.setProperty(name, value, 'important');
  }

  function setElementsStyle(selector, property, value) {
    $(selector).each(function () {
      this.style.setProperty(property, value, 'important');
    });
  }

  function applyDarkHeaderPreview(values) {
    var darkHeader = colorLooksDark(values.theme_header_color)
      ? values.theme_header_color
      : values.theme_dark_card_color;
    var darkHeaderText = colorLooksDark(values.theme_header_color)
      ? values.theme_header_text_color
      : values.theme_dark_text_color;
    var darkHeaderRgb = hexToRgb(darkHeader);
    var darkHeaderTextRgb = hexToRgb(darkHeaderText);

    if (darkHeader) {
      setRootVar('--theme-dark-header-bg', darkHeader);
    }
    if (darkHeaderRgb) {
      setRootVar('--theme-dark-header-rgb', darkHeaderRgb);
    }
    if (darkHeaderText) {
      setRootVar('--theme-dark-header-text', darkHeaderText);
    }
    if (darkHeaderTextRgb) {
      setRootVar('--theme-dark-header-text-rgb', darkHeaderTextRgb);
    }
  }

  function applyThemePreview() {
    var rawValues = collectThemeValues();
    var values = repairedLightThemeValues(rawValues);

    $.each(themeColorFields, function (_, field) {
      var value = values[field];
      var rgb = hexToRgb(value);

      if (!value) {
        return;
      }

      $.each(themeCssMap[field] || [], function (_, cssVar) {
        setRootVar(cssVar, value);
      });

      $.each(themeRgbMap[field] || [], function (_, cssVar) {
        if (rgb) {
          setRootVar(cssVar, rgb);
        }
      });
    });

    var primary = normalizeHex($('#theme_primary_color').val());
    if (primary) {
      setRootVar('--v2-sidebar-active-end', primary);
    }

    var header = values.theme_header_color;
    var headerText = values.theme_header_text_color;
    var headerRgb = hexToRgb(header);
    var headerTextRgb = hexToRgb(headerText);
    var sidebarActiveRgb = hexToRgb(values.theme_sidebar_active_color);
    var primaryRgb = hexToRgb(values.theme_primary_color);

    if (headerTextRgb) {
      setRootVar('--v2-header-muted', 'rgba(' + headerTextRgb + ', .78)');
    }
    if (primaryRgb) {
      setRootVar('--v2-nav-hover-bg', 'rgba(' + primaryRgb + ', .08)');
      setRootVar('--v2-nav-hover-border', 'rgba(' + primaryRgb + ', .18)');
      setRootVar('--v2-sidebar-hover', 'rgba(' + primaryRgb + ', .08)');
    }
    if (sidebarActiveRgb) {
      setRootVar('--v2-sidebar-border', 'rgba(' + sidebarActiveRgb + ', .16)');
    }
    setRootVar('--v2-nav-hover-shadow', 'none');

    if (header && headerRgb) {
      setElementsStyle('.page-header[data-v2-header]', '--v2-header-primary', header);
      setElementsStyle('.page-header[data-v2-header]', '--v2-header-primary-dark', header);
      setElementsStyle('.page-header[data-v2-header]', 'background', header);
      setElementsStyle('.page-header[data-v2-header]', 'background-image', 'linear-gradient(135deg, rgba(' + headerRgb + ', .98), rgba(' + headerRgb + ', .90))');
      setElementsStyle('.page-header[data-v2-header]', 'border-bottom-color', 'rgba(' + headerRgb + ', .35)');
    }

    if (headerText && headerTextRgb) {
      setElementsStyle('.page-header[data-v2-header]', '--v2-header-ink', headerText);
      setElementsStyle('.page-header[data-v2-header]', '--v2-header-muted', 'rgba(' + headerTextRgb + ', .78)');
      setElementsStyle('.page-header[data-v2-header]', 'color', headerText);
      setElementsStyle('.page-header[data-v2-header] .header-wrapper, .page-header[data-v2-header] .v2-header-shell, .page-header[data-v2-header] .v2-header-brand, .page-header[data-v2-header] .v2-header-breadcrumb-card', 'background', 'transparent');
      setElementsStyle('.page-header[data-v2-header] .header-wrapper, .page-header[data-v2-header] .v2-header-shell, .page-header[data-v2-header] .v2-header-brand, .page-header[data-v2-header] .v2-header-breadcrumb-card', 'background-image', 'none');
      setElementsStyle('.page-header[data-v2-header] .header-wrapper, .page-header[data-v2-header] .v2-header-shell, .page-header[data-v2-header] .v2-header-brand, .page-header[data-v2-header] .v2-header-breadcrumb-card', 'color', headerText);
      setElementsStyle('.page-header[data-v2-header] .toggle-sidebar, .page-header[data-v2-header] .v2-page-title-heading, .page-header[data-v2-header] .v2-header-breadcrumb-list, .page-header[data-v2-header] .v2-header-breadcrumb-list *, .page-header[data-v2-header] svg, .page-header[data-v2-header] i', 'color', headerText);
    }

    applyDarkHeaderPreview(rawValues);
  }

  function syncThemeText(field, value) {
    var normalized = normalizeHex(value);
    if (!normalized) {
      return false;
    }

    $('#' + field + '_text').val(normalized);
    $('#' + field).val(normalized);
    setFieldError(field, '');
    return true;
  }

  function applyThemePreset(values) {
    if (typeof values === 'string') {
      try {
        values = JSON.parse(values);
      } catch (e) {
        values = {};
      }
    }

    $.each(values || {}, function (field, value) {
      syncThemeText(field, value);
    });

    applyThemePreview();
    setDirty(true);
    validate();
  }

  function applyErrors(errors) {
    clearErrors();
    $.each(errors || {}, function (field, messages) {
      setFieldError(field, $.isArray(messages) ? messages[0] : messages);
    });
    activateFirstErrorSection(errors);
  }

  function validate() {
    var valid = true;
    clearErrors();

    var logoInput = $('#app_logo')[0];
    var logoFile = logoInput && logoInput.files && logoInput.files.length ? logoInput.files[0] : null;

    if (logoFile) {
      var logoTypeAllowed = allowedLogoTypes.indexOf(String(logoFile.type || '').toLowerCase()) !== -1;
      var logoExtensionAllowed = allowedLogoExtension.test(String(logoFile.name || ''));

      if (!logoTypeAllowed || !logoExtensionAllowed) {
        setFieldError('app_logo', 'Use a PNG, JPG, GIF, BMP, or WebP image.');
        valid = false;
      } else if (logoFile.size > maxLogoSize) {
        setFieldError('app_logo', 'The logo must not be larger than 2 MB.');
        valid = false;
      }
    }

    if (!$.trim($('#app_name').val() || '')) {
      setFieldError('app_name', 'Application name is required.');
      valid = false;
    }

    if (!$('#app_currency').val()) {
      setFieldError('app_currency', 'Choose a default currency.');
      valid = false;
    }

    if (!$('#app_lang').val()) {
      setFieldError('app_lang', 'Choose a default language.');
      valid = false;
    }

    if (!$('#app_timezone').val()) {
      setFieldError('app_timezone', 'Choose a default timezone.');
      valid = false;
    }

    if (!$('#per_page').val()) {
      setFieldError('per_page', 'Choose records per page.');
      valid = false;
    }

    if (!$('#record_order').val()) {
      setFieldError('record_order', 'Choose default order.');
      valid = false;
    }

    if (!$('#record_method').val()) {
      setFieldError('record_method', 'Choose record method.');
      valid = false;
    }

    if (!$('#bus_v2_design').val()) {
      setFieldError('bus_v2_design', 'Choose bus design format.');
      valid = false;
    }

    $.each(themeColorFields, function (_, field) {
      if (!normalizeHex($('#' + field).val()) || !normalizeHex($('#' + field + '_text').val())) {
        setFieldError(field, 'Use a valid hex color.');
        valid = false;
      }
    });

    $('#settings-save-btn').prop('disabled', !valid || isSaving);
    return valid;
  }

  function updateLogoPreview(file) {
    if (!file || !file.type || file.type.indexOf('image/') !== 0) {
      return;
    }

    var reader = new FileReader();
    reader.onload = function (event) {
      $('#app-logo-preview').attr('src', event.target.result);
    };
    reader.readAsDataURL(file);
  }

  function save(form) {
    if (!validate()) {
      activateFirstErrorSection();
      toast('error', 'Please correct the highlighted settings.');
      return;
    }

    setSaving(true);

    requestJson(saveUrl || form.action, {
      method: 'POST',
      body: new window.FormData(form)
    }).then(function (response) {
      if (response.data && response.data.logo_url) {
        $('#app-logo-preview').attr('src', response.data.logo_url);
      }

      setDirty(false);
      toast('success', response.message || 'Application settings saved.');
    }).catch(function (error) {
      if (error.status === 422) {
        applyErrors(error.errors || {});
        toast('error', error.message || 'Please correct the highlighted settings.');
        validate();
        return;
      }

      showError(error, function () {
        save(form);
      });
    }).finally(function () {
      setSaving(false);
      validate();
    });
  }

  $(function () {
    activateSection(window.location.hash, { updateHash: false, scroll: false });
    activateAppearanceMode('light');
    applyThemePreview();
    validate();

    $sectionLinks.on('click', function (event) {
      event.preventDefault();
      activateSection($(this).attr('href'), { scroll: false });
    });

    $appearanceTabs.on('click', function () {
      activateAppearanceMode($(this).data('appearance-mode'));
    });

    $('#app-settings-v2-form').on('input change', 'input, select, textarea', function () {
      setDirty(true);
      validate();
    });

    $('#app_logo').on('change', function () {
      updateLogoPreview(this.files && this.files.length ? this.files[0] : null);
    });

    $('.app-settings-v2-color-input').on('input change', function () {
      if (syncThemeText(this.id, this.value)) {
        applyThemePreview();
      }
    });

    $('.app-settings-v2-color-text').on('input change', function () {
      var field = $(this).data('color-source');
      var normalized = normalizeHex(this.value);

      if (!field) {
        return;
      }

      if (normalized) {
        $('#' + field).val(normalized).trigger('change');
        $(this).val(normalized);
        setFieldError(field, '');
        applyThemePreview();
        return;
      }

      setFieldError(field, 'Use a valid hex color.');
      validate();
    });

    $('[data-theme-preset]').on('click', function () {
      applyThemePreset($(this).data('theme-values'));
    });

    $('#app-settings-v2-form').on('submit', function (event) {
      event.preventDefault();
      save(this);
    });

    window.addEventListener('beforeunload', function (event) {
      if (!isDirty || isSaving) {
        return;
      }

      event.preventDefault();
      event.returnValue = '';
    });

    window.addEventListener('hashchange', function () {
      activateSection(window.location.hash, { updateHash: false, scroll: false });
    });
  });
})(jQuery);
