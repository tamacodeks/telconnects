@php
    $activeLocale = session('locale', 'fr');
    $appName = defined('APP_NAME') ? APP_NAME : config('app.name');
    $appLogoFile = (defined('APP_LOGO') && APP_LOGO && file_exists(public_path('images/' . APP_LOGO)))
        ? APP_LOGO
        : 'logo.png';
    $appLogoUrl = asset('images/' . $appLogoFile);
@endphp

@extends('v2.layout.authentication.master')


@section('title', $appName . ' — Sign in securely')

@section('css')
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
@endsection

@section('content')   
<div class="auth-wrapper">
    <div class="login-card single-panel reveal-chain">
        <div class="login-shell">
            <div class="auth-side" data-ani>
                <div class="auth-side-top">
                    <a class="auth-logo d-inline-flex align-items-center justify-content-center text-decoration-none"
                       href="{{ route('login') }}">
                        <img class="img-fluid" src="{{ $appLogoUrl }}" alt="{{ $appName }}">
                    </a>

                    <div class="auth-copy-block">
                        <h1 class="auth-title">Run daily operations</h1>
                        <p class="auth-copy mb-0">
                            Manage reseller users, balances, payments, orders, and transactions in one workspace.
                            {{ $appName }} also covers mobile top-up, calling cards, travel bookings, price lists,
                            commissions, and support tickets for day-to-day operations.
                        </p>
                    </div>
                </div>

                <div class="auth-chip-grid" data-ani>
                    <span class="auth-chip"><i class="fa fa-users" aria-hidden="true"></i> Users &amp; Balances</span>
                    <span class="auth-chip"><i class="fa fa-credit-card" aria-hidden="true"></i> Payments &amp; Orders</span>
                    <span class="auth-chip"><i class="fa fa-mobile" aria-hidden="true"></i> Top-up &amp; Calling Cards</span>
                    <span class="auth-chip"><i class="fa fa-ticket" aria-hidden="true"></i> Travel &amp; Tickets</span>
                </div>
            </div>

            <div class="form-side">
                <div class="auth-toolbar" data-ani>
                    <button class="mode auth-theme-toggle" type="button" aria-label="Toggle dark and light theme">
                        <span class="theme-toggle-icon theme-toggle-light" aria-hidden="true">
                            <i class="fa fa-lightbulb-o"></i>
                        </span>
                        <span class="theme-toggle-icon theme-toggle-dark" aria-hidden="true">
                            <i class="fa fa-moon-o"></i>
                        </span>
                        <span class="theme-toggle-text">Light / Dark</span>
                    </button>
                </div>

                <div class="form-intro" data-ani>
                    <h2 class="form-title mb-2">Sign in</h2>
                    <p class="text-muted mb-0">Use your username and password to continue.</p>
                </div>

                <form id="login-form" class="theme-form" method="POST" novalidate>
                    @csrf

                    <div id="auth-feedback" class="alert auth-feedback d-none" role="alert" aria-live="polite"></div>

                    <div id="login-section">
                        <div class="credentials-panel" data-ani>
                            <div class="form-focus-wrap">
                                <label class="form-label" for="username">Username</label>
                                <input
                                    id="username"
                                    class="form-control"
                                    name="username"
                                    type="text"
                                    value="{{ old('username') }}"
                                    placeholder="Enter your username"
                                    required
                                    maxlength="50"
                                    autocomplete="username"
                                    autocapitalize="none"
                                    autocorrect="off">
                                <div class="invalid-feedback">Please enter your username.</div>
                            </div>

                            <div class="form-focus-wrap">
                                <label class="form-label" for="password">Password</label>
                                <div class="password-field">
                                    <input
                                        id="password"
                                        class="form-control"
                                        name="password"
                                        type="password"
                                        placeholder="Enter your password"
                                        required
                                        maxlength="128"
                                        autocomplete="current-password">
                                    <button class="password-toggle" type="button" aria-label="Press and hold to show password" aria-pressed="false">
                                        <span class="password-toggle-icon icon-show" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </span>
                                        <span class="password-toggle-icon icon-hide" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 3 21 21"></path>
                                                <path d="M10.58 10.58A2 2 0 0 0 13.42 13.42"></path>
                                                <path d="M9.88 5.18A10.7 10.7 0 0 1 12 5c6 0 9.75 7 9.75 7a15.98 15.98 0 0 1-4.04 4.77"></path>
                                                <path d="M6.61 6.61A16.33 16.33 0 0 0 2.25 12s3.75 7 9.75 7a10.9 10.9 0 0 0 4.04-.76"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Please enter your password.</div>
                            </div>

                            <div class="form-meta-row">
                                <label class="remember-choice" for="remember">
                                    <input id="remember" name="remember" type="checkbox" value="1">
                                    <span>Remember me</span>
                                </label>

                                <div class="lang-switch">
                                    <span class="lang-switch-label">Language</span>
                                    <select id="lang" name="lang" class="form-select form-select-sm">
                                        <option value="fr" {{ $activeLocale === 'fr' ? 'selected' : '' }}>FR</option>
                                        <option value="en" {{ $activeLocale === 'en' ? 'selected' : '' }}>EN</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="btn-center mt-4" data-ani>
                            <button class="btn btn-primary px-4 btn-animate btn-block-mobile btn-pulse-once"
                                    type="submit"
                                    id="validate-login-btn">
                                <i class="fa fa-lock me-2" aria-hidden="true"></i>Sign In
                            </button>
                        </div>
                    </div>

                    <div id="otp-section" class="d-none slide-in" aria-live="polite">
                        <div class="step-card mt-4">
                            <span class="step-badge">OTP verification</span>
                            <h4 class="step-title">Verify this device</h4>
                            <p class="otp-hint mb-3" id="otp-destination">
                                Enter the 6-digit code we sent to your registered contact.
                            </p>

                            <div class="d-flex otp-grid">
                                @for ($i = 0; $i < 6; $i++)
                                    <input
                                        class="form-control otp-box otp-input"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        maxlength="1"
                                        autocomplete="one-time-code"
                                        aria-label="OTP digit {{ $i + 1 }}">
                                @endfor
                            </div>

                            <div class="step-actions d-flex align-items-center flex-wrap gap-2 mt-3">
                                <span class="otp-hint">Need another code?</span>
                                <a href="#" id="resend-otp">Resend</a>
                                <span class="otp-hint" id="resend-countdown" hidden>
                                    Resend in <span id="resend-seconds">60</span>s
                                </span>
                            </div>

                            <div class="mt-3">
                                <button class="btn btn-link p-0" type="button" id="back-to-login-1">
                                    <i class="fa fa-arrow-left me-1" aria-hidden="true"></i>Back to sign in
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="totp-section" class="d-none slide-in" aria-live="polite">
                        <div class="step-card mt-4">
                            <span class="step-badge">Authenticator verification</span>
                            <h4 class="step-title">Enter your app code</h4>
                            <p class="otp-hint mb-3">
                                Open Google Authenticator or another compatible app and enter the current 6-digit code.
                            </p>

                            <div class="d-flex otp-grid">
                                @for ($i = 0; $i < 6; $i++)
                                    <input
                                        class="form-control otp-box totp-input"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        maxlength="1"
                                        autocomplete="one-time-code"
                                        aria-label="TOTP digit {{ $i + 1 }}">
                                @endfor
                            </div>

                            <p class="otp-hint mt-3 mb-0">Codes refresh every 30 seconds.</p>

                            <div class="mt-3">
                                <button class="btn btn-link p-0" type="button" id="back-to-login-2">
                                    <i class="fa fa-arrow-left me-1" aria-hidden="true"></i>Back to sign in
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
(function ($) {
    'use strict';

    const $doc = $(document);
    const $loginForm = $('#login-form');
    const $feedback = $('#auth-feedback');
    const $loginSection = $('#login-section');
    const $otpSection = $('#otp-section');
    const $totpSection = $('#totp-section');
    const $otpInputs = $('.otp-input');
    const $totpInputs = $('.totp-input');

    const routes = {
        login: '{{ route("secure.login.validate") }}',
        verifyOtp: '{{ route("verify.otp") }}',
        verifyTotp: '{{ route("verify.totp") }}',
        resendOtp: '{{ route("resend.otp") }}'
    };

    let resendTimer = 60;
    let timerInterval = null;

    document.addEventListener('DOMContentLoaded', function () {
        const card = document.querySelector('.login-card');
        if (card) {
            requestAnimationFrame(function () {
                card.classList.add('reveal-in');
            });
        }

        document.querySelectorAll('.reveal-chain').forEach(function (chain) {
            const items = chain.querySelectorAll('[data-ani]');
            items.forEach(function (element, index) {
                element.style.animationDelay = (0.06 * index) + 's';
            });
            chain.classList.add('on');
        });

        const signIn = document.getElementById('validate-login-btn');
        if (signIn) {
            signIn.addEventListener('animationend', function () {
                signIn.classList.remove('btn-pulse-once');
            }, { once: true });
        }

        const usernameField = document.getElementById('username');
        if (usernameField && window.innerWidth > 575) {
            usernameField.focus();
        }
    });

    function setFeedback(type, message) {
        const classes = {
            success: 'alert-success',
            info: 'alert-info',
            warning: 'alert-warning',
            danger: 'alert-danger'
        };

        $feedback
            .removeClass('d-none alert-success alert-info alert-warning alert-danger')
            .addClass(classes[type] || 'alert-danger')
            .text(message || '');
    }

    function clearFeedback() {
        $feedback
            .addClass('d-none')
            .removeClass('alert-success alert-info alert-warning alert-danger')
            .text('');
    }

    function extractMessage(xhr, fallback) {
        if (xhr && xhr.responseJSON) {
            if (xhr.responseJSON.message) {
                return xhr.responseJSON.message;
            }

            if (xhr.responseJSON.errors) {
                const firstField = Object.keys(xhr.responseJSON.errors)[0];
                if (
                    firstField &&
                    xhr.responseJSON.errors[firstField] &&
                    xhr.responseJSON.errors[firstField][0]
                ) {
                    return xhr.responseJSON.errors[firstField][0];
                }
            }
        }

        return fallback;
    }

    function toggleButtonLoading($button, loadingHtml, isLoading) {
        if (!$button.data('originalHtml')) {
            $button.data('originalHtml', $button.html());
        }

        if (isLoading) {
            $button.prop('disabled', true).html(loadingHtml);
            return;
        }

        $button.prop('disabled', false).html($button.data('originalHtml'));
    }

    function digitsFrom($fields) {
        let value = '';
        $fields.each(function () {
            value += ($(this).val() || '').replace(/\D/g, '');
        });
        return value;
    }

    function resetCodeInputs($fields) {
        $fields.val('').removeClass('is-invalid');
    }

    function stopResendTimer() {
        clearInterval(timerInterval);
        $('#resend-otp').removeClass('disabled');
        $('#resend-countdown').prop('hidden', true);
    }

    function startResendTimer(seconds) {
        stopResendTimer();
        resendTimer = seconds || 60;
        $('#resend-otp').addClass('disabled');
        $('#resend-countdown').prop('hidden', false);
        $('#resend-seconds').text(resendTimer);

        timerInterval = setInterval(function () {
            resendTimer -= 1;
            $('#resend-seconds').text(resendTimer);

            if (resendTimer <= 0) {
                stopResendTimer();
            }
        }, 1000);
    }

    function setSection(section) {
        const showOtp = section === 'otp';
        const showTotp = section === 'totp';

        $loginSection.toggleClass('d-none', section !== 'login');
        $otpSection.toggleClass('d-none', !showOtp).removeClass('showing');
        $totpSection.toggleClass('d-none', !showTotp).removeClass('showing');

        if (showOtp) {
            requestAnimationFrame(function () {
                $otpSection.addClass('showing');
                resetCodeInputs($otpInputs);
                $otpInputs.first().addClass('otp-pulse').focus();
                setTimeout(function () {
                    $otpInputs.first().removeClass('otp-pulse');
                }, 900);
            });
            return;
        }

        if (showTotp) {
            requestAnimationFrame(function () {
                $totpSection.addClass('showing');
                resetCodeInputs($totpInputs);
                $totpInputs.first().addClass('otp-pulse').focus();
                setTimeout(function () {
                    $totpInputs.first().removeClass('otp-pulse');
                }, 900);
            });
            return;
        }

        $('#username').focus();
    }

    function updateOtpDestination(response) {
        const targets = Array.isArray(response.delivery_targets) ? response.delivery_targets : [];

        $('#otp-destination').text(
            targets.length
                ? 'Enter the 6-digit code sent to ' + targets.join(' and ') + '.'
                : 'Enter the 6-digit code we sent to your registered contact.'
        );
    }

    function normalizeFieldValue($field) {
        if (!$field.length) {
            return '';
        }

        return $field.attr('id') === 'username'
            ? (($field.val() || '').trim())
            : ($field.val() || '');
    }

    function setFieldError($field, isInvalid) {
        if (!$field.length) {
            return;
        }

        $field.toggleClass('is-invalid', isInvalid);
        $field.closest('.form-focus-wrap').toggleClass('has-error', isInvalid);
    }

    function validateField($field) {
        const isInvalid = !normalizeFieldValue($field);
        setFieldError($field, isInvalid);
        return !isInvalid;
    }

    function validateLoginFields() {
        const $username = $('#username');
        const $password = $('#password');
        const validUsername = validateField($username);
        const validPassword = validateField($password);

        return {
            valid: validUsername && validPassword,
            firstInvalid: !validUsername ? $username : (!validPassword ? $password : null)
        };
    }

    function submitLogin() {
        const username = ($('#username').val() || '').trim();
        const password = $('#password').val() || '';
        const lang = $('#lang').val() || 'fr';
        const remember = $('#remember').is(':checked') ? 1 : 0;
        const $button = $('#validate-login-btn');
        const validation = validateLoginFields();

        clearFeedback();
        if (!validation.valid) {
            if (validation.firstInvalid) {
                validation.firstInvalid.focus();
            }
            return;
        }

        toggleButtonLoading(
            $button,
            '<span class="spinner-border spinner-border-sm me-2"></span>Signing in',
            true
        );

        $.ajax({
            url: routes.login,
            type: 'POST',
            data: {
                username: username,
                password: password,
                remember: remember,
                lang: lang,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.status === 'otp_required') {
                    updateOtpDestination(response);
                    setSection('otp');
                    startResendTimer(60);
                    setFeedback('success', response.message || 'A one-time code has been sent to your registered contact.');
                    return;
                }

                if (response.status === 'totp_required') {
                    setSection('totp');
                    setFeedback(
                        'info',
                        response.provider
                            ? 'Enter the code from ' + response.provider + ' to complete sign in.'
                            : 'Enter the code from your authenticator app to complete sign in.'
                    );
                    return;
                }

                if (response.status === 'authenticated') {
                    window.location.href = response.redirect_url;
                    return;
                }

                setFeedback('danger', response.message || 'Invalid credentials.');
            },
            error: function (xhr) {
                if (xhr && xhr.responseJSON && xhr.responseJSON.errors) {
                    if (xhr.responseJSON.errors.username) {
                        $('#username').addClass('is-invalid').closest('.form-focus-wrap').addClass('has-error');
                    }

                    if (xhr.responseJSON.errors.password) {
                        $('#password').addClass('is-invalid').closest('.form-focus-wrap').addClass('has-error');
                    }
                }

                setFeedback('danger', extractMessage(xhr, 'Unable to sign in right now.'));
            },
            complete: function () {
                toggleButtonLoading($button, '', false);
            }
        });
    }

    function verifyOtp(code, $fields) {
        return $.ajax({
            url: routes.verifyOtp,
            type: 'POST',
            data: {
                username: ($('#username').val() || '').trim(),
                otp: code,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.status === 'authenticated') {
                    setFeedback('success', 'Code verified. Redirecting...');
                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 500);
                    return;
                }

                resetCodeInputs($fields);
                $fields.first().focus();
                setFeedback('danger', response.message || 'Invalid code.');
            },
            error: function (xhr) {
                if (xhr.status === 409) {
                    stopResendTimer();
                    setSection('login');
                }

                resetCodeInputs($fields);
                $fields.first().focus();
                setFeedback('danger', extractMessage(xhr, 'Error verifying the OTP code.'));
            }
        });
    }

    function verifyTotp(code, $fields) {
        return $.ajax({
            url: routes.verifyTotp,
            type: 'POST',
            data: {
                username: ($('#username').val() || '').trim(),
                code: code,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.status === 'authenticated') {
                    setFeedback('success', 'Code verified. Redirecting...');
                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 500);
                    return;
                }

                resetCodeInputs($fields);
                $fields.first().focus();
                setFeedback('danger', response.message || 'Invalid code.');
            },
            error: function (xhr) {
                if (xhr.status === 409) {
                    setSection('login');
                }

                resetCodeInputs($fields);
                $fields.first().focus();
                setFeedback('danger', extractMessage(xhr, 'Error verifying the authenticator code.'));
            }
        });
    }

    function bindCodeInputs(selector, onComplete) {
        $doc.on('keydown', selector, function (event) {
            const key = event.key;
            const withCommand = event.ctrlKey || event.metaKey;
            const allowed = [
                'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight',
                'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End', 'Escape'
            ];

            if (
                allowed.includes(key) ||
                (withCommand && ['a', 'c', 'v', 'x'].includes(key.toLowerCase()))
            ) {
                if (key === 'Backspace' && !$(this).val()) {
                    $(this).prev(selector).focus();
                }
                return;
            }

            if (!/^[0-9]$/.test(key)) {
                event.preventDefault();
            }
        });

        $doc.on('input', selector, function () {
            this.value = (this.value || '').replace(/\D/g, '').slice(0, 1);

            if (this.value && $(this).next(selector).length) {
                $(this).next(selector).focus();
            }

            const $fields = $(selector);
            const code = digitsFrom($fields);

            if (code.length === $fields.length && !$fields.data('submitting')) {
                $fields.data('submitting', true);
                const request = onComplete(code, $fields);

                if (request && typeof request.always === 'function') {
                    request.always(function () {
                        $fields.removeData('submitting');
                    });
                } else {
                    setTimeout(function () {
                        $fields.removeData('submitting');
                    }, 300);
                }
            }
        });

        $doc.on('paste', selector, function (event) {
            event.preventDefault();

            const $fields = $(selector);
            const pasted = (((event.originalEvent || event).clipboardData || window.clipboardData).getData('text') || '')
                .replace(/\D/g, '')
                .slice(0, $fields.length);

            if (!pasted) {
                return;
            }

            resetCodeInputs($fields);

            for (let i = 0; i < pasted.length; i++) {
                $($fields[i]).val(pasted[i]);
            }

            if (pasted.length === $fields.length) {
                if ($fields.data('submitting')) {
                    return;
                }

                $fields.data('submitting', true);
                const request = onComplete(digitsFrom($fields), $fields);

                if (request && typeof request.always === 'function') {
                    request.always(function () {
                        $fields.removeData('submitting');
                    });
                } else {
                    setTimeout(function () {
                        $fields.removeData('submitting');
                    }, 300);
                }
            } else {
                $($fields[Math.min(pasted.length, $fields.length - 1)]).focus();
            }
        });
    }

    function setPasswordVisibility(isVisible, $button) {
        const $input = $('#password');
        if (!$input.length || !$button || !$button.length) {
            return;
        }

        $input.attr('type', isVisible ? 'text' : 'password');
        $button
            .toggleClass('is-active', isVisible)
            .attr('aria-pressed', isVisible ? 'true' : 'false')
            .attr('aria-label', isVisible ? 'Release to hide password' : 'Press and hold to show password');
    }

    $doc.on('pointerdown', '.password-toggle', function (event) {
        event.preventDefault();
        setPasswordVisibility(true, $(this));
    });

    $doc.on('pointerup pointerleave pointercancel blur', '.password-toggle', function () {
        setPasswordVisibility(false, $(this));
    });

    $doc.on('keydown', '.password-toggle', function (event) {
        if (event.key !== ' ' && event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        setPasswordVisibility(true, $(this));
    });

    $doc.on('keyup', '.password-toggle', function (event) {
        if (event.key !== ' ' && event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        setPasswordVisibility(false, $(this));
    });

    (function focusMode() {
        $doc.on('focusin', 'input, select, textarea, .form-control', function () {
            const $wrap = $(this).closest('.form-focus-wrap');
            if ($wrap.length) {
                $wrap.addClass('is-focused');
            }
        });

        $doc.on('focusout', 'input, select, textarea, .form-control', function () {
            const $wrap = $(this).closest('.form-focus-wrap');
            const $field = $(this);
            if (!$wrap.length) {
                return;
            }

            setTimeout(function () {
                if ($wrap.find(':focus').length === 0) {
                    $wrap.removeClass('is-focused');

                    if ($field.is('#username, #password')) {
                        validateField($field);
                    }
                }
            }, 0);
        });
    })();

    $loginForm.on('submit', function (event) {
        event.preventDefault();
        submitLogin();
    });

    $('#validate-login-btn').on('click', function (event) {
        event.preventDefault();
        submitLogin();
    });

    $('#back-to-login-1, #back-to-login-2').on('click', function () {
        stopResendTimer();
        clearFeedback();
        setSection('login');
    });

    $('#resend-otp').on('click', function (event) {
        event.preventDefault();

        if ($(this).hasClass('disabled')) {
            return;
        }

        $.ajax({
            url: routes.resendOtp,
            type: 'POST',
            data: {
                username: ($('#username').val() || '').trim(),
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                updateOtpDestination(response);
                startResendTimer(60);
                setFeedback('success', response.message || 'A fresh OTP has been sent to your registered contact.');
            },
            error: function (xhr) {
                if (xhr.status === 409) {
                    stopResendTimer();
                    setSection('login');
                }

                setFeedback('danger', extractMessage(xhr, 'Could not resend the OTP.'));
            }
        });
    });

    $('#username, #password').on('input', function () {
        if (normalizeFieldValue($(this))) {
            setFieldError($(this), false);
        } else {
            $(this).removeClass('is-invalid');
            $(this).closest('.form-focus-wrap').removeClass('has-error');
        }

        clearFeedback();
    });

    $('#lang, #remember').on('change', function () {
        clearFeedback();
    });

    bindCodeInputs('.otp-input', verifyOtp);
    bindCodeInputs('.totp-input', verifyTotp);

})(jQuery);
</script>
@endsection
