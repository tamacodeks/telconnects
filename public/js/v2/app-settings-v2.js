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
    $('.app-settings-v2-card, .app-settings-v2-sticky-actions').toggleClass('app-settings-v2-saving', isSaving);
  }

  function clearErrors() {
    $('.app-settings-v2-error').text('');
  }

  function setFieldError(field, message) {
    $('[data-error-for="' + field + '"]').text(message || '');
  }

  function applyErrors(errors) {
    clearErrors();
    $.each(errors || {}, function (field, messages) {
      setFieldError(field, $.isArray(messages) ? messages[0] : messages);
    });
  }

  function validate() {
    var valid = true;
    clearErrors();

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
    validate();

    $('#app-settings-v2-form').on('input change', 'input, select, textarea', function () {
      setDirty(true);
      validate();
    });

    $('#app_logo').on('change', function () {
      updateLogoPreview(this.files && this.files.length ? this.files[0] : null);
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
  });
})(jQuery);
