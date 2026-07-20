(function (window, $) {
    if (!$ || !window.busV2Config) {
        return;
    }

    var config = window.busV2Config;
    var jqueryUiDatepicker = $.fn.datepicker;
    var bookingTimerInterval = null;
    var bookingExpiredState = false;
    var bookingStorageKey = "busV2BookingExpiry";
    var checkoutRootSelector = ".bus-v2-checkout, .bco[data-booking-expires-at]";
    var checkoutFormSelector = ".bus-v2-checkout-form, .bco-form";
    var lastRenderedStateHtml = "";
    var pendingSortButton = null;
    var flashSweetAlertOpen = false;
    var reservationSweetAlertOpen = false;
    var reservationLoaderObserver = null;
    var lazyResultsInitialCount = 10;
    var lazyResultsStepCount = 10;
    var lazyRevealQueued = false;
    var designClass = config.design ? "bus-v2-design-" + String(config.design).replace(/[^a-z0-9_-]/gi, "") : "";
    var cities = $.map(config.cities || [], function (city) {
        return {
            value: city.id || "",
            label: city.name || "",
            latitude: city.coordinates ? city.coordinates.latitude : "",
            longitude: city.coordinates ? city.coordinates.longitude : ""
        };
    });

    if (designClass) {
        document.documentElement.classList.add(designClass);
    }

    function i18n(path, fallback) {
        var value = config.i18n || {};
        var parts = String(path || "").split(".");
        var index;

        for (index = 0; index < parts.length; index += 1) {
            if (!value || typeof value !== "object" || !Object.prototype.hasOwnProperty.call(value, parts[index])) {
                return fallback;
            }

            value = value[parts[index]];
        }

        return value === undefined || value === null || value === "" ? fallback : value;
    }

    function replaceCountTemplate(template, count, fallback) {
        return String(template || fallback || "").replace(/__COUNT__|:count/g, count);
    }

    function passengerSummaryLabel(type, count) {
        if (type === "adult") {
            return replaceCountTemplate(
                count === 1 ? i18n("passengers.adultOne", "__COUNT__ adult") : i18n("passengers.adultOther", "__COUNT__ adults"),
                count,
                "__COUNT__ adult"
            );
        }

        return replaceCountTemplate(
            count === 1 ? i18n("passengers.childOne", "__COUNT__ child") : i18n("passengers.childOther", "__COUNT__ children"),
            count,
            "__COUNT__ child"
        );
    }

    function syncInputWrapState(input) {
        var $input = $(input);
        $input.closest(".bus-v2-input-wrap").toggleClass("is-filled", $.trim($input.val()).length > 0);
    }

    function animateElement($element, className, duration) {
        if (!$element || !$element.length) {
            return;
        }

        $element.removeClass(className);
        if ($element[0]) {
            void $element[0].offsetWidth;
        }
        $element.addClass(className);

        window.setTimeout(function () {
            $element.removeClass(className);
        }, duration || 320);
    }

    function getSearchCard() {
        var $form = $("#busV2SearchForm");
        var $card = $form.closest(".bus-v2-search-card, .bpr-card--luxury");

        if (!$card.length) {
            $card = $form.find(".bpr-card--luxury, .bus-v2-search-card").first();
        }

        return $card;
    }

    function pulseSearchCard(className, duration) {
        var $card = getSearchCard();
        if (!$card.length) {
            return;
        }

        $card.removeClass(className);
        if ($card[0]) {
            void $card[0].offsetWidth;
        }
        $card.addClass(className);

        window.setTimeout(function () {
            $card.removeClass(className);
        }, duration || 320);
    }

    function setSearchProgress(isSearching) {
        getSearchCard().toggleClass("is-searching", !!isSearching);
        setSearchButtonLoading(!!isSearching);
    }

    function prefersReducedMotion() {
        return !!(window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches);
    }

    function setSearchButtonLoading(isLoading) {
        var $button = $("#busV2SearchButton");
        var loadingText;
        var loadingAltText;

        if (!$button.length) {
            return;
        }

        if (isLoading) {
            if (!$button.data("original-html")) {
                $button.data("original-html", $button.html());
            }

            loadingText = $button.data("loading-text") || i18n("search.searching", "Searching buses...");
            loadingAltText = $button.data("loading-alt-text") || i18n("search.findingRoutes", "Finding best routes...");

            $button
                .prop("disabled", true)
                .addClass("is-loading")
                .html(
                    '<span class="bus-v2-button-loading">' +
                        '<span class="bus-v2-button-loading-icon"><i class="fas fa-bus"></i></span>' +
                        '<span class="bus-v2-button-loading-copy">' +
                            '<strong>' + escapeHtml(loadingText) + '</strong>' +
                            '<small>' + escapeHtml(loadingAltText) + '</small>' +
                        '</span>' +
                    '</span>'
                );

            return;
        }

        $button
            .prop("disabled", false)
            .removeClass("is-loading")
            .html($button.data("original-html") || $button.html());
    }

    function animateRouteSwap() {
        var $routeGrid = $("#busV2SearchForm .bus-v2-route-grid");
        if (!$routeGrid.length) {
            return;
        }

        $routeGrid.removeClass("is-swapping");
        if ($routeGrid[0]) {
            void $routeGrid[0].offsetWidth;
        }
        $routeGrid.addClass("is-swapping");

        window.setTimeout(function () {
            $routeGrid.removeClass("is-swapping");
        }, 440);
    }

    function flash(message, type, errors) {
        var Swal = sweetAlert();
        var isSuccess = type === "success";
        var cleanMessage = normalizeFlashMessage(message);
        var optionsLoadedMatch = isSuccess ? cleanMessage.match(/^(.+?\bbus options? loaded\.?)(?:\s+(.*))?$/i) : null;
        var title = optionsLoadedMatch ? optionsLoadedMatch[1] : (isSuccess ? "Success" : "Action needed");
        var body = optionsLoadedMatch
            ? (optionsLoadedMatch[2] || "The best available trips are ready for booking.")
            : (cleanMessage || (isSuccess ? "Request completed successfully." : "Please review the highlighted issue and try again."));
        var html = '<div class="bus-v2-flash-alert-copy"><p>' + escapeHtml(body) + "</p>";

        if (errors && errors.length) {
            html += '<ul class="bus-v2-flash-alert-errors">';
            $.each(errors, function (_, item) {
                html += "<li>" + escapeHtml(normalizeFlashMessage(item)) + "</li>";
            });
            html += "</ul>";
        }
        html += "</div>";

        if (!Swal) {
            window.alert(title + "\n" + body);
            return;
        }

        flashSweetAlertOpen = true;
        Swal.fire({
            icon: isSuccess ? "success" : "error",
            title: title,
            html: html,
            confirmButtonText: "OK",
            showConfirmButton: !isSuccess,
            timer: isSuccess ? 1800 : undefined,
            timerProgressBar: isSuccess,
            buttonsStyling: false,
            customClass: {
                popup: "bus-v2-flash-swal",
                title: "bus-v2-flash-alert-title",
                htmlContainer: "bus-v2-flash-alert-html",
                confirmButton: "bus-v2-flash-alert-confirm"
            }
        }).then(function () {
            flashSweetAlertOpen = false;
        });
    }

    function normalizeFlashMessage(value) {
        if (value === null || value === undefined) {
            return "";
        }

        return String(value)
            .replace(/([.!?])([A-Za-z])/g, "$1 $2")
            .replace(/\s+/g, " ")
            .trim();
    }

    function clearFlash() {
        var Swal = sweetAlert();

        if (flashSweetAlertOpen && !reservationSweetAlertOpen && Swal && typeof Swal.close === "function") {
            Swal.close();
        }

        flashSweetAlertOpen = false;
    }

    function sweetAlert() {
        return window.Swal && typeof window.Swal.fire === "function" ? window.Swal : null;
    }

    function isReservationCreateForm($form, isSearchForm, isSortForm) {
        var action = String($form.attr("action") || "").toLowerCase();

        return !isSearchForm && !isSortForm && action.indexOf("create-reservations") !== -1;
    }

    function reservationProviderLabel($form) {
        var action = String($form.attr("action") || "").toLowerCase();

        if (action.indexOf("bla") !== -1) {
            return "BlaBlaCar Bus";
        }

        if (action.indexOf("flix") !== -1) {
            return "FlixBus";
        }

        return "bus";
    }

    function reservationCreatingHtml(message) {
        return [
            '<div class="bus-v2-reservation-loader bus-v2-reservation-loader--svg" aria-hidden="true">',
            '    <svg class="bus-v2-reservation-svg" viewBox="0 0 520 230" focusable="false">',
            '        <defs>',
            '            <linearGradient id="busV2ReservationCoachBody" x1="76" y1="58" x2="446" y2="182" gradientUnits="userSpaceOnUse">',
            '                <stop offset="0" stop-color="#ffffff"/>',
            '                <stop offset="0.46" stop-color="#f8fafc"/>',
            '                <stop offset="1" stop-color="#c6ccd6"/>',
            '            </linearGradient>',
            '            <linearGradient id="busV2ReservationCoachFront" x1="362" y1="58" x2="462" y2="182" gradientUnits="userSpaceOnUse">',
            '                <stop offset="0" stop-color="#374151"/>',
            '                <stop offset="1" stop-color="#020617"/>',
            '            </linearGradient>',
            '            <linearGradient id="busV2ReservationCoachGlass" x1="88" y1="74" x2="430" y2="132" gradientUnits="userSpaceOnUse">',
            '                <stop offset="0" stop-color="#111827"/>',
            '                <stop offset="0.58" stop-color="#020617"/>',
            '                <stop offset="1" stop-color="#374151"/>',
            '            </linearGradient>',
            '            <linearGradient id="busV2ReservationCoachWheel" x1="-22" y1="-22" x2="22" y2="22" gradientUnits="userSpaceOnUse">',
            '                <stop offset="0" stop-color="#111827"/>',
            '                <stop offset="1" stop-color="#020617"/>',
            '            </linearGradient>',
            '            <clipPath id="busV2ReservationSvgRoadClip">',
            '                <rect x="52" y="181" width="416" height="18" rx="9"/>',
            '            </clipPath>',
            '            <clipPath id="busV2ReservationCoachGlassClip">',
            '                <path d="M105 76 H346 C386 76 417 101 428 134 H105 C91 134 82 124 86 111 L92 91 C94 82 98 78 105 76 Z"/>',
            '            </clipPath>',
            '        </defs>',
            '        <g class="bus-v2-svg-road">',
            '            <path class="bus-v2-svg-road-bed" d="M60 182 H464 C476 182 483 188 479 196 C475 204 457 208 432 208 H88 C63 208 45 204 41 196 C37 188 48 182 60 182 Z"/>',
            '            <g clip-path="url(#busV2ReservationSvgRoadClip)">',
            '                <rect class="bus-v2-svg-road-line" x="485" y="188" width="78" height="4" rx="2"/>',
            '                <rect class="bus-v2-svg-road-line" x="655" y="188" width="78" height="4" rx="2"/>',
            '                <rect class="bus-v2-svg-road-line" x="825" y="188" width="78" height="4" rx="2"/>',
            '            </g>',
            '        </g>',
            '        <g class="bus-v2-svg-bus bus-v2-svg-coach">',
            '            <ellipse class="bus-v2-svg-shadow" cx="260" cy="184" rx="171" ry="21"/>',
            '            <path class="bus-v2-svg-body" fill="url(#busV2ReservationCoachBody)" d="M78 92 C82 69 102 54 131 54 H354 C398 54 433 82 443 121 L452 154 C457 174 443 188 420 188 H93 C69 188 55 173 62 151 L72 110 C74 101 75 96 78 92 Z"/>',
            '            <path class="bus-v2-svg-roof" d="M95 71 C106 50 126 42 158 42 H341 C375 42 407 61 428 96 L435 109 H83 Z"/>',
            '            <path class="bus-v2-svg-front" fill="url(#busV2ReservationCoachFront)" d="M357 58 C399 64 433 92 443 125 L452 156 C457 174 444 188 421 188 H357 Z"/>',
            '            <path class="bus-v2-svg-glass-band" fill="url(#busV2ReservationCoachGlass)" d="M105 76 H346 C386 76 417 101 428 134 H105 C91 134 82 124 86 111 L92 91 C94 82 98 78 105 76 Z"/>',
            '            <g class="bus-v2-svg-window-cuts" clip-path="url(#busV2ReservationCoachGlassClip)">',
            '                <path class="bus-v2-svg-glass-highlight" d="M95 87 H397"/>',
            '                <path class="bus-v2-svg-window-separator" d="M151 76 V134 M200 76 V134 M249 76 V134 M298 76 V134 M347 76 V134"/>',
            '            </g>',
            '            <path class="bus-v2-svg-front-glass" d="M367 79 C394 86 414 105 423 130 H367 Z"/>',
            '            <path class="bus-v2-svg-door" d="M318 78 H357 V176 H318 Z"/>',
            '            <path class="bus-v2-svg-door-glass" fill="url(#busV2ReservationCoachGlass)" d="M324 85 H351 V129 H324 Z"/>',
            '            <circle class="bus-v2-svg-door-handle" cx="349" cy="143" r="2.8"/>',
            '            <path class="bus-v2-svg-side-highlight" d="M86 145 H351"/>',
            '            <path class="bus-v2-svg-stripe" d="M81 156 H383 C400 156 416 164 424 176 H87 C76 176 70 169 73 162 C74 158 77 156 81 156 Z"/>',
            '            <path class="bus-v2-svg-front-nose" d="M388 139 H452 L456 156 C461 174 446 188 421 188 H388 Z"/>',
            '            <path class="bus-v2-svg-bumper" d="M386 171 H451 C459 171 464 176 461 182 C458 187 447 190 432 190 H386 Z"/>',
            '            <path class="bus-v2-svg-grille" d="M420 151 H452 V162 H420 Z"/>',
            '            <circle class="bus-v2-svg-light" cx="448" cy="142" r="5"/>',
            '            <path class="bus-v2-svg-light-beam" d="M453 136 C483 128 502 132 514 144 C497 153 477 153 453 146 Z"/>',
            '            <path class="bus-v2-svg-mirror" d="M421 96 C438 94 450 100 454 111 C439 112 428 108 420 103 Z"/>',
            '            <path class="bus-v2-svg-tail-light" d="M74 131 H83 V151 H70 Z"/>',
            '            <g class="bus-v2-svg-wheel bus-v2-svg-wheel--rear" transform="translate(144 184)">',
            '                <g class="bus-v2-svg-wheel-spin">',
            '                    <circle class="bus-v2-svg-tire" fill="url(#busV2ReservationCoachWheel)" r="25"/>',
            '                    <circle class="bus-v2-svg-rim" r="14"/>',
            '                    <circle class="bus-v2-svg-hub" r="5"/>',
            '                    <path class="bus-v2-svg-spoke" d="M0 -18 V18 M-18 0 H18 M-12.5 -12.5 L12.5 12.5 M12.5 -12.5 L-12.5 12.5"/>',
            '                </g>',
            '            </g>',
            '            <g class="bus-v2-svg-wheel bus-v2-svg-wheel--front" transform="translate(365 184)">',
            '                <g class="bus-v2-svg-wheel-spin">',
            '                    <circle class="bus-v2-svg-tire" fill="url(#busV2ReservationCoachWheel)" r="25"/>',
            '                    <circle class="bus-v2-svg-rim" r="14"/>',
            '                    <circle class="bus-v2-svg-hub" r="5"/>',
            '                    <path class="bus-v2-svg-spoke" d="M0 -18 V18 M-18 0 H18 M-12.5 -12.5 L12.5 12.5 M12.5 -12.5 L-12.5 12.5"/>',
            '                </g>',
            '            </g>',
            '        </g>',
            '    </svg>',
            '</div>',
            '<p class="bus-v2-reservation-copy">' + escapeHtml(message) + '</p>'
        ].join("");
    }

    function showReservationCreatingAlert($form) {
        var provider = reservationProviderLabel($form);
        var text = String(i18n("messages.creatingReservationDescription", "Please wait while we create your :provider reservation."))
            .replace(":provider", provider);
        var title = i18n("messages.creatingReservation", "Creating reservation");
        var $overlay = $("#busV2ReservationPageLoader");

        if (!$overlay.length) {
            $overlay = $(
                '<div id="busV2ReservationPageLoader" class="bus-v2-reservation-page-loader" role="status" aria-live="polite">' +
                    '<div class="bus-v2-reservation-page-card">' +
                        '<h2 class="bus-v2-reservation-page-title"></h2>' +
                        '<div data-bus-v2-reservation-animation></div>' +
                    '</div>' +
                '</div>'
            );
            $("body").append($overlay);
        }

        reservationSweetAlertOpen = true;
        $overlay.find(".bus-v2-reservation-page-title").text(title);
        $overlay.find("[data-bus-v2-reservation-animation]").html(reservationCreatingHtml(text));
        normalizeReservationLoaderMarkup($overlay);
        $("body").addClass("bus-v2-reservation-overlay-open");
        (window.requestAnimationFrame || window.setTimeout)(function () {
            $overlay.addClass("is-visible");
        });
    }

    function closeReservationCreatingAlert() {
        var $overlay = $("#busV2ReservationPageLoader");

        if ($overlay.length) {
            $overlay.removeClass("is-visible");
            window.setTimeout(function () {
                if (!$overlay.hasClass("is-visible")) {
                    $overlay.remove();
                }
            }, 220);
        }

        $("body").removeClass("bus-v2-reservation-overlay-open");
        reservationSweetAlertOpen = false;
    }

    function normalizeReservationLoaderMarkup(context) {
        var $scope = $(context || document);
        var $legacyLoaders = $scope.is(".bus-v2-reservation-loader")
            ? $scope.not(".bus-v2-reservation-loader--svg")
            : $scope.find(".bus-v2-reservation-loader").not(".bus-v2-reservation-loader--svg");

        $legacyLoaders.each(function () {
            var $loader = $(this);
            var $container = $loader.closest("[data-bus-v2-reservation-animation]");
            var message = $.trim($container.find(".bus-v2-reservation-copy").first().text() || "");
            var html = reservationCreatingHtml(message);

            if ($container.length) {
                $container.html(html);
                return;
            }

            $loader.replaceWith($(html).filter(".bus-v2-reservation-loader"));
        });
    }

    function watchReservationLoaderMarkup() {
        if (reservationLoaderObserver || !window.MutationObserver || !document.body) {
            return;
        }

        reservationLoaderObserver = new window.MutationObserver(function (mutations) {
            $.each(mutations, function (_, mutation) {
                $.each(mutation.addedNodes || [], function (_, node) {
                    if (node && node.nodeType === 1) {
                        normalizeReservationLoaderMarkup(node);
                    }
                });
            });
        });

        reservationLoaderObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
        normalizeReservationLoaderMarkup(document.body);
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) {
            return "";
        }

        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function flattenErrors(errors) {
        var items = [];
        $.each(errors || {}, function (_, value) {
            if ($.isArray(value)) {
                items = items.concat(value);
            } else if (value) {
                items.push(value);
            }
        });
        return items;
    }

    function clearFieldError($input) {
        var errorKey;
        var $field;

        if (!$input || !$input.length) {
            return;
        }

        errorKey = $input.data("error-key");
        $field = $input.closest(".bus-v2-field");

        $field.removeClass("has-error");
        $input.attr("aria-invalid", "false");

        if (errorKey) {
            $field.find('[data-error-for="' + errorKey + '"]').text("");
        } else {
            $field.find(".bus-v2-field-error").text("");
        }
    }

    function clearFormErrors(scope) {
        var $scope = $(scope);
        if (!$scope.length) {
            return;
        }

        $scope.find(".bus-v2-field.has-error").removeClass("has-error");
        $scope.find("[data-error-key]").attr("aria-invalid", "false");
        $scope.find(".bus-v2-field-error").text("");
    }

    function setFieldError($input, message) {
        var errorKey = $input.data("error-key");
        var $field = $input.closest(".bus-v2-field");
        var $error = $field.find('[data-error-for="' + errorKey + '"]').first();

        $field.addClass("has-error");
        $input.attr("aria-invalid", "true");
        $error.text(message || "");
    }

    function normalizeFrenchPhone(value) {
        var digits = String(value || "").replace(/\D+/g, "");

        if (!digits) {
            return "";
        }

        if (digits.indexOf("33") === 0) {
            digits = digits.substring(2);
        }

        digits = digits.replace(/^0+/, "");

        return digits ? "+33" + digits : "";
    }

    function displayFrenchPhone(value) {
        var normalized = normalizeFrenchPhone(value);
        var digits;
        var pairs = [];
        var index;

        if (!normalized || normalized === "+33") {
            return "";
        }

        digits = "0" + normalized.replace(/^\+33/, "");

        for (index = 0; index < digits.length; index += 2) {
            pairs.push(digits.substring(index, index + 2));
        }

        return $.trim(pairs.join(" "));
    }

    function isValidFrenchPhone(value) {
        return /^\+33[67]\d{8}$/.test(String(value || ""));
    }

    function syncPhoneHiddenValue($input) {
        var $hidden = $($input.data("phone-hidden"));
        var normalized = normalizeFrenchPhone($input.val());

        if ($hidden.length) {
            $hidden.val(normalized);
        }

        return normalized;
    }

    function initPhoneInputs(scope) {
        $(scope).find("[data-phone-visible]").each(function () {
            var input = this;
            var $input = $(input);
            var $hidden = $($input.data("phone-hidden"));
            var hiddenValue = $hidden.val();

            $input.val(displayFrenchPhone(hiddenValue));

            if (window.intlTelInput && !$input.data("phone-ready")) {
                window.intlTelInput(input, {
                    initialCountry: "fr",
                    onlyCountries: ["fr"],
                    allowDropdown: false,
                    separateDialCode: true,
                    nationalMode: true,
                    autoPlaceholder: "polite",
                    formatOnDisplay: false
                });

                $input.data("phone-ready", true);
            }

            syncPhoneHiddenValue($input);
        });
    }

    function syncFrenchPhoneInputs(scope) {
        $(scope).find("[data-phone-visible]").each(function () {
            syncPhoneHiddenValue($(this));
        });
    }

    function parseDocumentDate(value) {
        var input = $.trim(String(value || ""));
        var parts;
        var year;
        var month;
        var day;
        var date;

        if (!input) {
            return null;
        }

        if (/^\d{2}\.\d{2}\.\d{4}$/.test(input)) {
            parts = input.split(".");
            day = parseInt(parts[0], 10);
            month = parseInt(parts[1], 10) - 1;
            year = parseInt(parts[2], 10);
            date = new Date(year, month, day);

            if (date && date.getFullYear() === year && date.getMonth() === month && date.getDate() === day) {
                date.setHours(0, 0, 0, 0);
                return date;
            }
        }

        return null;
    }

    function validationMessageForInput($input) {
        var key = String($input.data("error-key") || "");
        var value = $.trim($input.val());
        var parsedDate;
        var normalizedPhone;
        var today = new Date();

        today.setHours(0, 0, 0, 0);

        if (key.indexOf("firstname.") === 0) {
            return value ? "" : i18n("checkoutValidation.firstName", "Enter the first name.");
        }

        if (key.indexOf("lastname.") === 0) {
            return value ? "" : i18n("checkoutValidation.lastName", "Enter the last name.");
        }

        if (key.indexOf("birthdate.") === 0) {
            if (!value) {
                return i18n("checkoutValidation.birthdate", "Choose the date of birth.");
            }

            parsedDate = parseDocumentDate(value);

            if (!parsedDate) {
                return i18n("checkoutValidation.birthdate", "Choose the date of birth.");
            }

            if (parsedDate >= today) {
                return i18n("checkoutValidation.birthdatePast", "Date of birth must be in the past.");
            }

            var passengerKind = $input.data("passenger-kind");
            var ageMs = today.getTime() - parsedDate.getTime();
            var ageYears = Math.floor(ageMs / (365.25 * 24 * 3600 * 1000));

            if (passengerKind === "adult" && ageYears < 18) {
                return i18n("checkoutValidation.birthdateAdult", "Adults must be at least 18 years old.");
            }

            if (passengerKind === "child" && ageYears >= 18) {
                return i18n("checkoutValidation.birthdateChild", "Children must be under 18 years old.");
            }

            return "";
        }

        if (key.indexOf("gender.") === 0) {
            return value ? "" : i18n("checkoutValidation.gender", "Select the gender.");
        }

        if (key.indexOf("email.") === 0) {
            if (!value) {
                return i18n("checkoutValidation.email", "Enter the email address.");
            }

            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
                ? ""
                : i18n("checkoutValidation.email", "Enter the email address.");
        }

        if (key.indexOf("phone_number.") === 0) {
            normalizedPhone = normalizeFrenchPhone(value);

            if (!normalizedPhone) {
                return i18n("checkoutValidation.phone", "Enter the phone number.");
            }

            return isValidFrenchPhone(normalizedPhone)
                ? ""
                : i18n("checkoutValidation.phoneInvalid", "Enter a valid French phone number.");
        }

        if (key.indexOf("citizenship.") === 0) {
            return value ? "" : i18n("checkoutValidation.citizenship", "Select the citizenship.");
        }

        if (key.indexOf("identification_number.") === 0) {
            return value ? "" : i18n("checkoutValidation.passportNumber", "Enter the passport number.");
        }

        if (key.indexOf("identification_expiry_date.") === 0) {
            if (!value) {
                return i18n("checkoutValidation.passportExpiry", "Choose the passport expiry date.");
            }

            parsedDate = parseDocumentDate(value);

            if (!parsedDate) {
                return i18n("checkoutValidation.passportExpiry", "Choose the passport expiry date.");
            }

            return parsedDate >= today
                ? ""
                : i18n("checkoutValidation.passportExpiryFuture", "Passport expiry must be today or later.");
        }

        if (key.indexOf("visa_permit_type.") === 0) {
            return value ? "" : i18n("checkoutValidation.visa", "Select the visa or permit type.");
        }

        return "";
    }

    function validatePassengerCard($card, showErrors) {
        var valid = true;
        var $firstInvalid = $();

        if (showErrors) {
            clearFormErrors($card);
        }

        $card.find("[data-error-key]").each(function () {
            var $input = $(this);
            var message = validationMessageForInput($input);

            if (message) {
                valid = false;

                if (!$firstInvalid.length) {
                    $firstInvalid = $input;
                }

                if (showErrors) {
                    setFieldError($input, message);
                }
            }
        });

        return {
            valid: valid,
            firstInvalid: $firstInvalid
        };
    }

    function activePassengerCard($list) {
        var $active = $list.find("[data-passenger-step-card].is-active").first();

        if ($active.length) {
            return $active;
        }

        return $list.find(".bus-v2-passenger-card, .bco-pax-card").first();
    }

    function updatePassengerStepControls($list) {
        var count = parseInt($list.data("step-count"), 10) || 0;
        var currentIndex = parseInt($list.attr("data-active-step"), 10) || 0;
        var $activeCard = activePassengerCard($list);
        var validation = validatePassengerCard($activeCard, false);
        var $next = $list.find("[data-passenger-step-next]");
        var $submit = $list.closest("form").find("[data-passenger-submit]");
        var $summaryNote = $list.closest(".bus-v2-checkout-grid").find("[data-passenger-step-summary-note]");

        if (count > 1 && $next.length) {
            $next.toggle(currentIndex < count - 1);
            $next
                .prop("disabled", bookingExpiredState || currentIndex >= count - 1)
                .toggleClass("is-disabled", !validation.valid)
                .attr("aria-disabled", bookingExpiredState || currentIndex >= count - 1 ? "true" : "false");
        }

        if ($submit.length) {
            $submit.prop("disabled", count > 1 ? (currentIndex < count - 1 || !validation.valid || bookingExpiredState) : bookingExpiredState);
        }

        if ($summaryNote.length) {
            $summaryNote.toggleClass("is-ready", currentIndex === count - 1 && validation.valid && !bookingExpiredState);
        }
    }

    function updateCheckoutSubmitState($form) {
        var $list = $form.find("[data-passenger-step-list]").first();
        var $submit = $form.find("[data-passenger-submit]");

        if (!$submit.length) {
            return;
        }

        if ($list.length) {
            updatePassengerStepControls($list);
            return;
        }

        $submit.prop("disabled", !validatePassengerList($form, false) || bookingExpiredState);
    }

    function validatePassengerList($list, showErrors) {
        var $cards = $list.find("[data-passenger-step-card]");
        var firstInvalidIndex = null;
        var $firstInvalidField = $();

        if (!$cards.length) {
            $cards = $list.find(".bus-v2-passenger-card");
        }

        $cards.each(function (index) {
            var result = validatePassengerCard($(this), showErrors);

            if (!result.valid && firstInvalidIndex === null) {
                firstInvalidIndex = index;
                $firstInvalidField = result.firstInvalid;
            }
        });

        if (firstInvalidIndex !== null) {
            if ($list.is("[data-passenger-step-list]")) {
                setPassengerStep($list, firstInvalidIndex);
            }

            if (showErrors && $firstInvalidField.length) {
                window.setTimeout(function () {
                    var offset = $firstInvalidField.offset();

                    if (offset) {
                        $("html, body").animate({ scrollTop: Math.max(offset.top - 150, 0) }, 220);
                    }

                    $firstInvalidField.trigger("focus");
                }, 50);
            }

            return false;
        }

        return true;
    }

    function validateCheckoutForm($form, showErrors) {
        var $lists = $form.find("[data-passenger-step-list]");
        var valid = true;

        if (!$lists.length) {
            return validatePassengerList($form, showErrors);
        }

        $lists.each(function () {
            if (!validatePassengerList($(this), showErrors)) {
                valid = false;
                return false;
            }
        });

        return valid;
    }

    function saveBookingExpiry(expiresAt) {
        if (expiresAt) {
            window.localStorage.setItem(bookingStorageKey, String(expiresAt));
        }
    }

    function clearBookingExpiry() {
        window.localStorage.removeItem(bookingStorageKey);
    }

    function storedBookingExpiry() {
        return parseInt(window.localStorage.getItem(bookingStorageKey), 10) || 0;
    }

    function stopBookingTimer() {
        if (bookingTimerInterval) {
            window.clearInterval(bookingTimerInterval);
            bookingTimerInterval = null;
        }
    }

    function hideExpiryModal() {
        $("#busV2ExpiryModal").removeClass("is-visible").attr("aria-hidden", "true");
    }

    function showExpiryModal() {
        var $modal = $("#busV2ExpiryModal");

        $modal.addClass("is-visible").attr("aria-hidden", "false");

        window.setTimeout(function () {
            $modal.find("[data-bus-v2-restart-booking]").trigger("focus");
        }, 30);
    }

    function disableCheckoutForms() {
        $(checkoutRootSelector).addClass("is-expired");
        $(checkoutFormSelector).find("input:not([type='hidden']), select, textarea, button").prop("disabled", true);
    }

    function enableCheckoutForms() {
        $(checkoutRootSelector).removeClass("is-expired");
        $(checkoutFormSelector).find("input:not([type='hidden']), select, textarea, button").prop("disabled", false);
        $(checkoutFormSelector).each(function () {
            var $list = $(this).find("[data-passenger-step-list]").first();

            if ($list.length) {
                setPassengerStep($list, parseInt($list.attr("data-active-step"), 10) || 0);
                return;
            }

            updateCheckoutSubmitState($(this));
        });
    }

    function updateBookingCountdownText(expiresAt) {
        var remaining = Math.max(0, expiresAt - Math.floor(Date.now() / 1000));
        var minutes = Math.floor(remaining / 60);
        var seconds = remaining % 60;
        var text = remaining > 0
            ? String(minutes).padStart(2, "0") + ":" + String(seconds).padStart(2, "0")
            : i18n("checkout.timerExpired", "Expired");

        $("[data-booking-countdown]").text(text);
    }

    function expireBookingSession() {
        bookingExpiredState = true;
        stopBookingTimer();
        clearBookingExpiry();
        updateBookingCountdownText(0);
        disableCheckoutForms();
        showExpiryModal();
    }

    function initBookingTimer(scope) {
        var $scope = $(scope);
        var $checkout = $scope.is(checkoutRootSelector)
            ? $scope.first()
            : $scope.find(checkoutRootSelector).first();
        var expiresAt = 0;
        var expired = false;

        stopBookingTimer();

        if (!$checkout.length) {
            bookingExpiredState = false;
            clearBookingExpiry();
            hideExpiryModal();
            return;
        }

        expiresAt = parseInt($checkout.data("booking-expires-at"), 10) || storedBookingExpiry();
        expired = String($checkout.data("booking-expired")) === "1";

        if (expiresAt) {
            saveBookingExpiry(expiresAt);
        }

        if (!expiresAt || expired || expiresAt <= Math.floor(Date.now() / 1000)) {
            expireBookingSession();
            return;
        }

        bookingExpiredState = false;
        hideExpiryModal();
        enableCheckoutForms();
        updateBookingCountdownText(expiresAt);

        bookingTimerInterval = window.setInterval(function () {
            if (expiresAt <= Math.floor(Date.now() / 1000)) {
                expireBookingSession();
                return;
            }

            updateBookingCountdownText(expiresAt);
        }, 1000);
    }

    function setPassengerStep($list, stepIndex, options) {
        var count = parseInt($list.data("step-count"), 10) || 0;
        var index = Math.max(0, Math.min(stepIndex, count - 1));
        var $cards;
        var $activeCard;
        var $dots;
        var $prev;
        var $next;
        var $submit;
        var $note;
        var $summaryNote;
        var template;
        var label;
        var settings = options || {};

        if (!$list.length || count <= 1) {
            return;
        }

        $cards = $list.find("[data-passenger-step-card]");
        $dots = $list.find("[data-passenger-step-target]");
        $prev = $list.find("[data-passenger-step-prev]");
        $next = $list.find("[data-passenger-step-next]");
        $submit = $list.closest("form").find("[data-passenger-submit]");
        $note = $list.find("[data-passenger-step-note]");
        $summaryNote = $list.closest(".bus-v2-checkout-grid").find("[data-passenger-step-summary-note]");
        template = String($list.find("[data-passenger-step-label]").data("template") || "");
        label = template
            .replace(/__CURRENT__/g, index + 1)
            .replace(/__TOTAL__/g, count);

        $list.attr("data-active-step", index);

        $cards.removeClass("is-active").attr("hidden", true);
        $cards.eq(index).addClass("is-active").removeAttr("hidden");
        $activeCard = $cards.eq(index);

        $dots.each(function () {
            var $dot = $(this);
            var dotIndex = parseInt($dot.data("passenger-step-target"), 10) || 0;

            $dot.toggleClass("is-active", dotIndex === index);
            $dot.toggleClass("is-complete", dotIndex < index);
            $dot.attr("aria-selected", dotIndex === index ? "true" : "false");
        });

        $list.find("[data-passenger-step-label]").text(label);
        $prev.prop("disabled", index === 0);
        $next.toggle(index < count - 1);
        $submit.prop("disabled", bookingExpiredState || index < count - 1);

        if ($note.length) {
            $note.text(index === count - 1 ? $note.data("note-ready") : $note.data("note-pending"));
        }

        if ($summaryNote.length) {
            $summaryNote
                .text(index === count - 1 ? $summaryNote.data("note-ready") : $summaryNote.data("note-pending"))
                .toggleClass("is-ready", index === count - 1);
        }

        updatePassengerStepControls($list);

        if ((settings.scroll || settings.focus) && $activeCard.length) {
            window.setTimeout(function () {
                var offset = $activeCard.offset();
                var $targetField;

                if (settings.scroll && offset) {
                    $("html, body").animate({ scrollTop: Math.max(offset.top - 130, 0) }, 200);
                }

                if (settings.focus) {
                    $targetField = $activeCard.find("input:not([type='hidden']), select, textarea").filter(":visible").first();
                    if ($targetField.length) {
                        $targetField.trigger("focus");
                    }
                }
            }, 40);
        }
    }

    function showPassengerStepForInput($input) {
        var $list = $input.closest("[data-passenger-step-list]");
        var $card = $input.closest("[data-passenger-step-card]");
        var stepIndex;

        if (!$list.length || !$card.length) {
            return;
        }

        stepIndex = parseInt($card.data("passenger-step-index"), 10) || 0;
        setPassengerStep($list, stepIndex);
    }

    function initPassengerSteps(scope) {
        $(scope).find("[data-passenger-step-list]").each(function () {
            var $list = $(this);
            var count = parseInt($list.data("step-count"), 10) || 0;
            var activeStep = parseInt($list.attr("data-active-step"), 10) || 0;

            if (count <= 1) {
                return;
            }

            setPassengerStep($list, activeStep);
        });

        $(scope).find(checkoutFormSelector).each(function () {
            updateCheckoutSubmitState($(this));
        });
    }

    function applyFormErrors(scope, errors) {
        var $scope = $(scope);
        var $firstInvalid = $();

        if (!$scope.length) {
            return;
        }

        clearFormErrors($scope);

        $.each(errors || {}, function (key, value) {
            var message = $.isArray(value) ? value[0] : value;
            var fallbackKey = key;
            var $input;
            var $field;
            var $error;

            if (String(key).indexOf("identification_issuing_country.") === 0) {
                fallbackKey = key.replace("identification_issuing_country.", "citizenship.");
            } else if (String(key).indexOf("identification_type.") === 0) {
                fallbackKey = key.replace("identification_type.", "identification_number.");
            }

            $input = $scope.find('[data-error-key="' + key + '"], [data-error-key="' + fallbackKey + '"]').first();
            $field = $input.closest(".bus-v2-field");

            if (!$input.length || !message) {
                return;
            }

            $error = $scope.find('[data-error-for="' + key + '"], [data-error-for="' + fallbackKey + '"]').first();

            $field.addClass("has-error");
            $input.attr("aria-invalid", "true");
            $error.text(message);

            if (!$firstInvalid.length) {
                $firstInvalid = $input;
            }
        });

        if ($firstInvalid.length) {
            showPassengerStepForInput($firstInvalid);

            window.setTimeout(function () {
                var offset = $firstInvalid.offset();

                if (offset) {
                    $("html, body").animate({ scrollTop: Math.max(offset.top - 150, 0) }, 220);
                }

                $firstInvalid.trigger("focus");
            }, 60);
        }
    }

    function setSearchError(id, message) {
        $(id).text(message || "");
    }

    function clearSearchErrors() {
        setSearchError("#busV2FromError", "");
        setSearchError("#busV2ToError", "");
        setSearchError("#busV2DateError", "");
        setSearchError("#busV2ReturnDateError", "");
        setSearchError("#busV2PassengersError", "");
    }

    function parseYmdDate(value) {
        var parts = String(value || "").split("-");
        if (parts.length !== 3) {
            return null;
        }

        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10) - 1;
        var day = parseInt(parts[2], 10);
        var date = new Date(year, month, day);

        return isNaN(date.getTime()) ? null : date;
    }

    function formatYmdDate(date) {
        if (!(date instanceof Date) || isNaN(date.getTime())) {
            return "";
        }

        return [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, "0"),
            String(date.getDate()).padStart(2, "0")
        ].join("-");
    }

    function formatDocumentDate(date) {
        if (!(date instanceof Date) || isNaN(date.getTime())) {
            return "";
        }

        return [
            String(date.getDate()).padStart(2, "0"),
            String(date.getMonth() + 1).padStart(2, "0"),
            date.getFullYear()
        ].join(".");
    }

    function shiftDate(date, years, days) {
        var next = new Date(date.getTime());
        if (typeof years === "number") {
            next.setFullYear(next.getFullYear() + years);
        }
        if (typeof days === "number") {
            next.setDate(next.getDate() + days);
        }
        return next;
    }

    function currentLocale() {
        return String(config.locale || "en").toLowerCase().indexOf("fr") === 0 ? "fr" : "en";
    }

    function datepickerI18n() {
        var texts = i18n("datepicker", {}) || {};

        return {
            closeText: texts.closeText || "Done",
            prevText: texts.prevText || "Prev",
            nextText: texts.nextText || "Next",
            currentText: texts.currentText || "Today",
            monthNames: texts.monthNames || ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            monthNamesShort: texts.monthNamesShort || ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            dayNames: texts.dayNames || ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
            dayNamesMin: texts.dayNamesMin || ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"]
        };
    }

    function travelCalendarMonths() {
        return 1;
    }

    function hasUiDatepicker() {
        return typeof jqueryUiDatepicker === "function";
    }

    function callUiDatepicker($input) {
        if (!$input || !$input.length || !hasUiDatepicker()) {
            return null;
        }

        return jqueryUiDatepicker.apply($input, Array.prototype.slice.call(arguments, 1));
    }

    function showTravelDatepicker($input) {
        if (!$input || !$input.length) {
            return;
        }

        $input.trigger("focus");

        if (hasUiDatepicker() && $input.hasClass("hasDatepicker")) {
            callUiDatepicker($input, "show");
            positionDatepickerPopup($input);
        }
    }

    function markDatepickerShell($input, isOpen) {
        $input.closest(".bus-v2-input-wrap").toggleClass("is-picker-open", !!isOpen);
        $input.closest(".bus-v2-field").toggleClass("is-picker-open", !!isOpen);
    }

    function decorateDatepickerPopup(kind) {
        window.setTimeout(function () {
            $("#ui-datepicker-div")
                .addClass("bus-v2-datepicker-popup")
                .attr("data-bus-v2-kind", kind || "default");
        }, 0);
    }

    function positionDatepickerPopup($input) {
        window.setTimeout(function () {
            var $popup = $("#ui-datepicker-div");
            var input = $input && $input.get(0);

            if (!$popup.length || !input || !$popup.is(":visible")) {
                return;
            }

            $popup.addClass("bus-v2-datepicker-popup");

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
                .toggleClass("is-open-above", openAbove)
                .css({
                    top: top,
                    left: left,
                    zIndex: 2200
                });
        }, 0);
    }

    function buildDatepickerOptions(kind, overrides) {
        var localeTexts = datepickerI18n();
        var customBeforeShow = overrides && overrides.beforeShow;
        var customOnClose = overrides && overrides.onClose;
        var customOnSelect = overrides && overrides.onSelect;
        var customOnChangeMonthYear = overrides && overrides.onChangeMonthYear;
        var options = $.extend({}, localeTexts, overrides || {});

        options.dateFormat = kind === "travel"
            ? ((config.datepicker && config.datepicker.travelFormat) || "yy-mm-dd")
            : ((config.datepicker && config.datepicker.documentFormat) || "dd.mm.yy");
        options.firstDay = currentLocale() === "fr" ? 1 : 0;
        options.constrainInput = false;
        options.showAnim = "fadeIn";
        options.duration = 160;
        options.showOtherMonths = true;
        options.selectOtherMonths = true;
        options.beforeShow = function (input, inst) {
            markDatepickerShell($(input), true);
            decorateDatepickerPopup(kind);
            positionDatepickerPopup($(input));

            if (typeof customBeforeShow === "function") {
                return customBeforeShow.call(this, input, inst);
            }

            return undefined;
        };
        options.onClose = function (selectedDate, inst) {
            markDatepickerShell($(this), false);

            if (typeof customOnClose === "function") {
                customOnClose.call(this, selectedDate, inst);
            }
        };
        options.onChangeMonthYear = function (year, month, inst) {
            positionDatepickerPopup($(this));

            if (typeof customOnChangeMonthYear === "function") {
                customOnChangeMonthYear.call(this, year, month, inst);
            }
        };
        options.onSelect = function (selectedDate, inst) {
            var pickedDate = callUiDatepicker($(this), "getDate");

            if (pickedDate) {
                $(this).val(kind === "travel" ? formatYmdDate(pickedDate) : formatDocumentDate(pickedDate));
            }

            if (typeof customOnSelect === "function") {
                customOnSelect.call(this, selectedDate, inst);
            }
        };

        return options;
    }

    function attachDatepicker($input, options) {
        if (!$input.length || !hasUiDatepicker()) {
            return;
        }

        if ($input.hasClass("hasDatepicker")) {
            callUiDatepicker($input, "destroy");
        }

        callUiDatepicker($input, options);

        if ($input.val()) {
            var initialDate = callUiDatepicker($input, "getDate");
            var kind = $input.data("datepicker-kind");

            if (initialDate) {
                $input.val(kind === "travel" ? formatYmdDate(initialDate) : formatDocumentDate(initialDate));
            }
        }
    }

    function currentTripType() {
        return $("#busV2TripType").val() || "one_way";
    }

    function formatWeekdayLabel(date) {
        if (!(date instanceof Date) || isNaN(date.getTime())) {
            return "";
        }

        try {
            return date.toLocaleDateString(undefined, { weekday: "long" });
        } catch (error) {
            return "";
        }
    }

    function syncTravelDayLabels() {
        var departureDate = parseYmdDate($("#busV2DepartureDate").val());
        var returnDate = parseYmdDate($("#busV2ReturnDate").val());

        $("#busV2DepartureDayName").text(formatWeekdayLabel(departureDate));
        $("#busV2ReturnDayName").text(formatWeekdayLabel(returnDate));
    }

    function syncReturnDateConstraints() {
        var departureDate = parseYmdDate($("#busV2DepartureDate").val());
        var returnDate = parseYmdDate($("#busV2ReturnDate").val());
        var $returnDate = $("#busV2ReturnDate");
        var returnMinDate = departureDate || new Date();

        if ($returnDate.length) {
            $returnDate.attr("min", formatYmdDate(returnMinDate));
            if ($returnDate.hasClass("hasDatepicker")) {
                callUiDatepicker($returnDate, "option", "minDate", returnMinDate);
            }
        }

        if (currentTripType() === "round_trip" && departureDate && !returnDate) {
            var suggestedReturn = new Date(departureDate.getTime());
            suggestedReturn.setDate(suggestedReturn.getDate() + 1);
            if ($returnDate.hasClass("hasDatepicker")) {
                callUiDatepicker($returnDate, "setDate", suggestedReturn);
                $returnDate.val(formatYmdDate(suggestedReturn));
            } else {
                $returnDate.val(formatYmdDate(suggestedReturn));
            }
            returnDate = suggestedReturn;
        }

        if (departureDate && returnDate && returnDate < departureDate) {
            if ($returnDate.hasClass("hasDatepicker")) {
                callUiDatepicker($returnDate, "setDate", departureDate);
                $returnDate.val(formatYmdDate(departureDate));
            } else {
                $returnDate.val(formatYmdDate(departureDate));
            }
        }

        syncTravelDayLabels();
    }

    function syncTripTypeUI() {
        var tripType = currentTripType();
        var isRoundTrip = tripType === "round_trip";
        var $searchGrid = $("#busV2SearchForm .bus-v2-search-grid");
        var $searchCard = getSearchCard();

        $(".js-bus-v2-trip-type").each(function () {
            var $button = $(this);
            var isActive = $button.data("value") === tripType;
            $button.toggleClass("is-active", isActive).attr("aria-pressed", isActive ? "true" : "false");
        });

        $searchGrid.toggleClass("is-one-way", !isRoundTrip);
        $searchGrid.toggleClass("is-round-trip", isRoundTrip);
        $searchCard.toggleClass("is-one-way", !isRoundTrip);
        $searchCard.toggleClass("is-round-trip", isRoundTrip);
        $("#busV2ReturnField").prop("hidden", !isRoundTrip);
        if (isRoundTrip) {
            syncReturnDateConstraints();
        } else {
            setSearchError("#busV2ReturnDateError", "");
        }
    }

    function syncPassengerStepperButtons() {
        $(".js-bus-v2-stepper").each(function () {
            var $button = $(this);
            var $target = $($button.data("target"));
            var current = parseInt($target.val(), 10) || 0;
            var step = parseInt($button.data("step"), 10) || 0;
            var min = parseInt($button.data("min"), 10);
            var max = parseInt($button.data("max"), 10);
            var isDisabled = false;

            if (step < 0 && !isNaN(min) && current <= min) {
                isDisabled = true;
            }

            if (step > 0 && !isNaN(max) && current >= max) {
                isDisabled = true;
            }

            $button.prop("disabled", isDisabled).toggleClass("is-disabled", isDisabled);
        });
    }

    function updatePassengerSummary(shouldAnimate) {
        var adult = Math.max(1, parseInt($("#busV2Adult").val(), 10) || 1);
        var child = Math.max(0, parseInt($("#busV2Child").val(), 10) || 0);
        var adultLabel = passengerSummaryLabel("adult", adult);
        var childLabel = passengerSummaryLabel("child", child);
        var parts = [adultLabel];
        var totalCount = adult + child;

        if (child > 0) {
            parts.push(childLabel);
        }

        $("#busV2Adult").val(adult);
        $("#busV2Child").val(child);
        $("#busV2AdultCount").text(adult);
        $("#busV2ChildCount").text(child);
        $("#busV2PassengerCountBadge").text(replaceCountTemplate(i18n("passengers.total", "__COUNT__ total"), totalCount, "__COUNT__ total"));
        $("#busV2ChildRow").toggleClass("is-empty", child < 1);

        var summary = parts.join(", ");
        $("#busV2Passengers").val(summary);
        $("#busV2PassengersDisplay").text(summary);
        $("#busV2PassengerPanelSummary").text(summary);

        syncPassengerStepperButtons();

        if (shouldAnimate) {
            animateElement($("#busV2PassengersDisplay"), "is-updated", 280);
            animateElement($("#busV2PassengerPanelSummary"), "is-updated", 280);
            animateElement($("#busV2PassengerCountBadge"), "is-updated", 320);
        }
    }

    function animatePassengerChange(targetSelector, direction) {
        var isAdult = targetSelector === "#busV2Adult";
        var $count = $(isAdult ? "#busV2AdultCount" : "#busV2ChildCount");
        var $row = $(isAdult ? "#busV2AdultRow" : "#busV2ChildRow");
        var countClass = direction > 0 ? "is-bump-up" : "is-bump-down";

        animateElement($count, countClass, 260);
        animateElement($row, "is-updated", 320);
    }

    function syncPassengerPanelPlacement() {
        var $field = $(".bus-v2-passenger-field");
        var $panel = $("#busV2PassengerPanel");
        var viewportHeight;
        var viewportWidth;
        var fieldRect;
        var panelRect;
        var spaceAbove;
        var spaceBelow;
        var preferredSpace;
        var openUp;
        var panelMaxHeight;
        var panelWidth;
        var maxPanelWidth;
        var alignRight;

        if (!$field.length || !$panel.length || $panel.prop("hidden")) {
            return;
        }

        $panel.removeClass("is-open-up is-open-down is-align-right is-align-left");

        viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
        viewportWidth = window.innerWidth || document.documentElement.clientWidth || 0;
        fieldRect = $field[0].getBoundingClientRect();
        panelRect = $panel[0].getBoundingClientRect();
        spaceAbove = fieldRect.top;
        spaceBelow = viewportHeight - fieldRect.bottom;
        openUp = spaceAbove >= (panelRect.height + 18) && spaceAbove >= spaceBelow;
        preferredSpace = openUp ? spaceAbove : spaceBelow;
        panelMaxHeight = Math.max(220, Math.min(420, preferredSpace - 18));
        maxPanelWidth = Math.max(280, viewportWidth - 24);
        panelWidth = Math.min(Math.max(fieldRect.width, 340), maxPanelWidth);
        alignRight = fieldRect.left + panelWidth > (viewportWidth - 12);

        $panel.toggleClass("is-open-up", openUp);
        $panel.toggleClass("is-open-down", !openUp);
        $panel.toggleClass("is-align-right", alignRight);
        $panel.toggleClass("is-align-left", !alignRight);
        $panel.css({
            left: alignRight ? "auto" : "0",
            right: alignRight ? "0" : "auto",
            width: Math.round(panelWidth) + "px",
            maxHeight: panelMaxHeight + "px"
        });
    }

    function togglePassengerPanel(forceOpen) {
        var $trigger = $("[data-passenger-toggle]");
        var $panel = $("#busV2PassengerPanel");
        var $field = $(".bus-v2-passenger-field");
        var $card = getSearchCard();
        if (!$trigger.length || !$panel.length) {
            return;
        }

        var shouldOpen = typeof forceOpen === "boolean" ? forceOpen : $panel.prop("hidden");
        $panel.prop("hidden", !shouldOpen);
        $trigger.toggleClass("is-open", shouldOpen).attr("aria-expanded", shouldOpen ? "true" : "false");
        $field.toggleClass("is-panel-open", shouldOpen);
        $card.toggleClass("has-passenger-panel-open", shouldOpen);

        if (shouldOpen) {
            $panel.removeClass("is-open");
            if ($panel[0]) {
                void $panel[0].offsetWidth;
            }
            syncPassengerPanelPlacement();
            $panel.addClass("is-open");
        } else {
            $panel.removeClass("is-open is-open-up is-open-down");
        }
    }

    function clearHiddenRouteFields(prefix) {
        $(prefix + "Id").val("");
        $(prefix === "#busV2From" ? "#busV2GeoLatFrom" : "#busV2GeoLatTo").val("");
        $(prefix === "#busV2From" ? "#busV2GeoLonFrom" : "#busV2GeoLonTo").val("");
    }

    function escapeRegExp(value) {
        return String(value || "").replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    }

    function escapeHtml(value) {
        return String(value || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function highlightAutocompleteMatch(label, term) {
        var safeLabel = escapeHtml(label);
        var query = $.trim(term || "");
        var pattern;

        if (!query) {
            return safeLabel;
        }

        pattern = new RegExp("(" + escapeRegExp(query) + ")", "ig");
        return safeLabel.replace(pattern, "<mark>$1</mark>");
    }

    function styleAutocompleteInstance($input) {
        var instance = $input.autocomplete("instance");
        var $wrap = $input.closest(".bus-v2-input-wrap");

        if (!instance) {
            return;
        }

        instance._renderItem = function (ul, item) {
            var label = item.label || item.value || "";
            var term = this.term || "";
            var hint = i18n("search.autocompleteHint", "Suggested city");

            return $("<li>")
                .append(
                    '<div class="bus-v2-autocomplete-item">' +
                        '<span class="bus-v2-autocomplete-icon"><i class="fas fa-search"></i></span>' +
                        '<span class="bus-v2-autocomplete-copy">' +
                            '<span class="bus-v2-autocomplete-label">' + highlightAutocompleteMatch(label, term) + '</span>' +
                            '<span class="bus-v2-autocomplete-meta">' + escapeHtml(hint) + '</span>' +
                        '</span>' +
                    '</div>'
                )
                .appendTo(ul);
        };

        instance._resizeMenu = function () {
            this.menu.element.outerWidth($wrap.outerWidth());
        };
    }

    function normalizeLocationLabel(value) {
        var text = $.trim(String(value || "")).toLowerCase();

        if (typeof text.normalize === "function") {
            text = text.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        }

        return text.replace(/\s+/g, " ");
    }

    function findCityByLabel(value) {
        var normalized = normalizeLocationLabel(value);
        var match = null;

        if (!normalized) {
            return null;
        }

        $.each(cities, function (_, city) {
            if (normalizeLocationLabel(city.label) === normalized) {
                match = city;
                return false;
            }

            return true;
        });

        return match;
    }

    function applyRouteSelection($input, hiddenSelector, latSelector, lonSelector, city) {
        if (!city) {
            return false;
        }

        $input.val(city.label || "");
        $(hiddenSelector).val(city.value || "");
        $(latSelector).val(city.latitude || "");
        $(lonSelector).val(city.longitude || "");
        syncInputWrapState($input);
        return true;
    }

    function commitTypedRoute($input, hiddenSelector, latSelector, lonSelector, errorSelector, errorMessage) {
        var typedValue = $.trim($input.val());
        var matchedCity;

        if (!typedValue) {
            $(hiddenSelector).val("");
            $(latSelector).val("");
            $(lonSelector).val("");
            setSearchError(errorSelector, "");
            syncInputWrapState($input);
            return false;
        }

        matchedCity = findCityByLabel(typedValue);

        if (matchedCity) {
            applyRouteSelection($input, hiddenSelector, latSelector, lonSelector, matchedCity);
            setSearchError(errorSelector, "");
            return true;
        }

        $(hiddenSelector).val("");
        $(latSelector).val("");
        $(lonSelector).val("");
        setSearchError(errorSelector, errorMessage);
        syncInputWrapState($input);
        return false;
    }

    function wireAutocomplete(selector, hiddenSelector, latSelector, lonSelector, errorSelector, errorMessage) {
        var $input = $(selector);
        if (!$input.length || $input.data("uiAutocomplete")) {
            return;
        }

        $input.autocomplete({
            minLength: 2,
            delay: 120,
            source: cities,
            open: function () {
                $(this).autocomplete("widget").addClass("bus-v2-autocomplete");
            },
            select: function (_, ui) {
                applyRouteSelection($(selector), hiddenSelector, latSelector, lonSelector, ui.item);
                setSearchError(errorSelector, "");
                return false;
            }
        });

        styleAutocompleteInstance($input);

        $input.on("input", function () {
            $(hiddenSelector).val("");
            $(latSelector).val("");
            $(lonSelector).val("");
            setSearchError(errorSelector, "");
            syncInputWrapState(this);
        });

        $input.on("keydown", function (event) {
            var instance = $input.autocomplete("instance");
            var menuActive = !!(instance && instance.menu && instance.menu.active);

            if (menuActive) {
                return;
            }

            if (event.key === "Tab" || event.key === "Enter") {
                commitTypedRoute($input, hiddenSelector, latSelector, lonSelector, errorSelector, errorMessage);
            }
        });

        $input.on("blur", function () {
            commitTypedRoute($input, hiddenSelector, latSelector, lonSelector, errorSelector, errorMessage);
        });
    }

    function syncCitizenshipFields(scope) {
        $(scope).find(".bus-v2-citizenship").each(function () {
            var $select = $(this);
            var targetId = "#" + $select.data("target");
            var iso3 = $select.find("option:selected").data("iso3");
            if (iso3 && !$(targetId).val()) {
                $(targetId).val(iso3);
            }
        });
    }

    function initCheckoutSelect2(scope) {
        var $scope = $(scope || document);

        if (!$.fn.select2) {
            return;
        }

        $scope.find(".bco-select").each(function () {
            var $select = $(this);
            var $field = $select.closest(".bco-field");
            var $bookingRoot = $select.closest(".bco");
            var placeholder = $select.find("option[value='']").first().text() || $select.attr("placeholder") || "Select an option";

            if ($select.data("select2")) {
                return;
            }

            $select.select2({
                width: "100%",
                dropdownParent: $bookingRoot.length ? $bookingRoot : $(document.body),
                dropdownCssClass: "bco-select2-dropdown",
                minimumResultsForSearch: $select.find("option").length > 8 ? 0 : Infinity,
                placeholder: placeholder,
                language: {
                    noResults: function () {
                        var locale = String(config.locale || "en").toLowerCase();

                        if (locale.indexOf("fr") === 0) {
                            return "Aucun resultat";
                        }

                        if (locale.indexOf("pt") === 0) {
                            return "Nenhum resultado";
                        }

                        return "No results found";
                    }
                }
            });

            $select.on("select2:open", function () {
                $field.addClass("is-select2-open");
            });

            $select.on("select2:close", function () {
                $field.removeClass("is-select2-open");
                $select.trigger("blur");
            });
        });
    }

    function configureDateInputs(scope) {
        var today = new Date();
        var todayValue = formatYmdDate(today);
        var maxTravelDate = formatYmdDate(shiftDate(today, 0, 120));
        var maxTravelDateObject = shiftDate(today, 0, 120);
        var $scope = $(scope || document);

        $scope.find("#busV2DepartureDate").attr({
            min: todayValue,
            max: maxTravelDate
        });
        attachDatepicker($scope.find("#busV2DepartureDate"), buildDatepickerOptions("travel", {
            minDate: today,
            maxDate: maxTravelDateObject,
            numberOfMonths: travelCalendarMonths(),
            onSelect: function () {
                syncReturnDateConstraints();
                $(this).trigger("change");
            }
        }));

        $scope.find("#busV2ReturnDate").attr({
            min: todayValue,
            max: maxTravelDate
        });
        attachDatepicker($scope.find("#busV2ReturnDate"), buildDatepickerOptions("travel", {
            minDate: today,
            maxDate: maxTravelDateObject,
            numberOfMonths: travelCalendarMonths(),
            onSelect: function () {
                $(this).trigger("change");
            }
        }));

        $scope.find(".bus-v2-birthdate").each(function () {
            var $input = $(this);
            var isAdult = $input.data("passenger-kind") === "adult";
            var minDate = isAdult ? shiftDate(today, -100, 0) : shiftDate(today, -15, 0);
            var maxDate = isAdult ? shiftDate(today, -14, 0) : today;

            $input.attr({
                max: formatYmdDate(maxDate),
                min: formatYmdDate(minDate)
            });

            attachDatepicker($input, buildDatepickerOptions("birthdate", {
                minDate: minDate,
                maxDate: maxDate,
                changeMonth: true,
                changeYear: true,
                yearRange: isAdult ? "c-100:c-14" : "c-15:c"
            }));
        });

        $scope.find(".bus-v2-expirydate").attr("min", todayValue).each(function () {
            attachDatepicker($(this), buildDatepickerOptions("expiry", {
                minDate: today,
                changeMonth: true,
                changeYear: true,
                yearRange: "c:c+15"
            }));
        });
    }

    function initStateScope(scope) {
        configureDateInputs(scope);
        syncCitizenshipFields(scope);
        initCheckoutSelect2(scope);
        initPhoneInputs(scope);
        initPassengerSteps(scope);
        initBookingTimer(scope);
        initRoundTripProviders(scope);
        initLazyResultLists(scope);
    }

    function initSearchScope() {
        wireAutocomplete(
            "#busV2From",
            "#busV2FromId",
            "#busV2GeoLatFrom",
            "#busV2GeoLonFrom",
            "#busV2FromError",
            i18n("errors.origin", "Select a valid origin from the suggestions.")
        );
        wireAutocomplete(
            "#busV2To",
            "#busV2ToId",
            "#busV2GeoLatTo",
            "#busV2GeoLonTo",
            "#busV2ToError",
            i18n("errors.destination", "Select a valid destination from the suggestions.")
        );
        configureDateInputs(document);
        syncTripTypeUI();
        syncReturnDateConstraints();
        syncTravelDayLabels();
        updatePassengerSummary(false);
        syncInputWrapState("#busV2From");
        syncInputWrapState("#busV2To");
    }

    function validateSearchForm() {
        clearSearchErrors();

        var valid = true;
        var fromName = $.trim($("#busV2From").val());
        var toName = $.trim($("#busV2To").val());
        var fromId = $.trim($("#busV2FromId").val());
        var toId = $.trim($("#busV2ToId").val());
        var date = $.trim($("#busV2DepartureDate").val());
        var returnDate = $.trim($("#busV2ReturnDate").val());
        var tripType = currentTripType();
        var adult = parseInt($("#busV2Adult").val(), 10) || 0;
        var child = parseInt($("#busV2Child").val(), 10) || 0;
        var departureObj = parseYmdDate(date);
        var returnObj = parseYmdDate(returnDate);

        if (!fromName || !fromId) {
            setSearchError("#busV2FromError", i18n("errors.origin", "Select a valid origin from the suggestions."));
            valid = false;
        }

        if (!toName || !toId) {
            setSearchError("#busV2ToError", i18n("errors.destination", "Select a valid destination from the suggestions."));
            valid = false;
        }

        if (fromId && toId && fromId === toId) {
            setSearchError("#busV2ToError", i18n("errors.differentDestination", "Choose a different destination."));
            valid = false;
        }

        if (!date) {
            setSearchError("#busV2DateError", i18n("errors.departure", "Choose a travel date."));
            valid = false;
        }

        if (tripType === "round_trip") {
            if (!returnDate) {
                setSearchError("#busV2ReturnDateError", i18n("errors.returnDate", "Choose a return date."));
                valid = false;
            } else if (departureObj && returnObj && returnObj < departureObj) {
                setSearchError("#busV2ReturnDateError", i18n("errors.returnAfterDeparture", "Return date must be on or after departure."));
                valid = false;
            }
        }

        if ((adult + child) < 1) {
            setSearchError("#busV2PassengersError", i18n("errors.passenger", "Add at least one passenger."));
            valid = false;
        }

        return valid;
    }

    function setLoading($form, isLoading) {
        if ($form.attr("id") === "busV2SearchForm") {
            setSearchButtonLoading(isLoading);
            return;
        }

        var $button = $form.find('button[type="submit"]:not(:disabled)').first();
        if (!$button.length) {
            $button = $form.find('button[type="submit"]').first();
        }
        if (!$button.length) {
            return;
        }

        if (isLoading) {
            $button.data("original-html", $button.html());
            $button.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> ' + ($button.data("loading-text") || i18n("messages.processing", "Processing...")));
        } else {
            $button.prop("disabled", false).html($button.data("original-html") || $button.html());
        }
    }

    function replaceState(html, options) {
        if (typeof html !== "string") {
            return;
        }

        options = options || {};
        var $state = $("#busV2State");

        lastRenderedStateHtml = html;
        $state.html(html).removeClass("is-loading is-entered is-search-loading");
        if ($state[0]) {
            void $state[0].offsetWidth;
        }
        $state.addClass("is-entered");

        window.setTimeout(function () {
            $state.removeClass("is-entered");
        }, 420);

        initStateScope(document.getElementById("busV2State"));
        var top = $("#busV2State").offset();
        if (!options.preserveScroll && top) {
            $("html, body").animate({ scrollTop: top.top - 14 }, 220);
        }
    }

    function updateRoundTripSelectionCard($provider, leg, $input) {
        var $card = $provider.find('[data-roundtrip-selection-card="' + leg + '"]');

        if (!$card.length) {
            return;
        }

        if (!$input || !$input.length || !$input.is(":checked")) {
            $card.prop("hidden", true);
            return;
        }

        $card.find('[data-roundtrip-selection-window="' + leg + '"]').text($input.data("trip-window") || "");
        $card.find('[data-roundtrip-selection-route="' + leg + '"]').text($input.data("trip-route") || "");
        $card.find('[data-roundtrip-selection-meta="' + leg + '"]').text([
            $input.data("trip-duration") || "",
            $input.data("trip-price") || ""
        ].join(" | ").replace(/^ \| | \| $/g, ""));
        $card.prop("hidden", false);
    }

    function setRoundTripProviderError($provider, message) {
        $provider.find("[data-roundtrip-error]").text(message || "");
    }

    function syncRoundTripProvider($provider) {
        if (!$provider || !$provider.length) {
            return;
        }

        var $outboundInput = $provider.find('input[name="outbound_trip"]:checked').first();
        var $returnInput = $provider.find('input[name="return_trip"]:checked').first();
        var hasOutboundOptions = $provider.find('input[name="outbound_trip"]').length > 0;
        var hasReturnOptions = $provider.find('input[name="return_trip"]').length > 0;
        var canReserve = hasOutboundOptions && hasReturnOptions;
        var hasOutbound = $outboundInput.length > 0;
        var hasReturn = $returnInput.length > 0;
        var $returnPanel = $provider.find("[data-roundtrip-return-panel]");
        var $submit = $provider.find("[data-roundtrip-submit]");
        var $summaryStage = $provider.find("[data-roundtrip-summary-stage]");
        var $summaryTitle = $provider.find("[data-roundtrip-summary-title]");
        var $summaryCopy = $provider.find("[data-roundtrip-summary-copy]");
        var $outboundStep = $provider.find('[data-roundtrip-step-indicator="outbound"]');
        var $returnStep = $provider.find('[data-roundtrip-step-indicator="return"]');
        var providerName = $provider.data("provider-name") || "this provider";

        $provider.find("[data-roundtrip-option]").removeClass("is-selected");
        $provider.find('input[name="outbound_trip"]:checked, input[name="return_trip"]:checked').each(function () {
            $(this).closest("[data-roundtrip-option]").addClass("is-selected");
        });

        if (!canReserve) {
            $submit.prop("disabled", true);
            $returnPanel.addClass("is-locked");
            updateRoundTripSelectionCard($provider, "outbound", null);
            updateRoundTripSelectionCard($provider, "return", null);
            return;
        }

        $returnPanel.toggleClass("is-locked", !hasOutbound);
        $provider.find('input[name="return_trip"]').prop("disabled", !hasOutbound);
        $provider.find("[data-roundtrip-back]").prop("hidden", !hasOutbound);
        $provider.find("[data-roundtrip-unlock-copy]").prop("hidden", hasOutbound);

        $outboundStep.toggleClass("is-active", !hasOutbound).toggleClass("is-complete", hasOutbound);
        $returnStep
            .toggleClass("is-locked", !hasOutbound)
            .toggleClass("is-active", hasOutbound && !hasReturn)
            .toggleClass("is-complete", hasReturn);

        if (!hasOutbound) {
            $summaryStage.text("Step 1 of 2");
            $summaryTitle.text("Select the outbound departure");
            $summaryCopy.text("Choose the trip out first. The return leg will unlock immediately after you pick it.");
        } else if (!hasReturn) {
            $summaryStage.text("Step 2 of 2");
            $summaryTitle.text("Select the return departure");
            $summaryCopy.text("Outbound selected. Now choose the trip back to continue with " + providerName + ".");
        } else {
            $summaryStage.text("Ready to reserve");
            $summaryTitle.text("Review both selected departures");
            $summaryCopy.text("Both legs are selected. Continue to create the reservation and passenger checkout.");
        }

        updateRoundTripSelectionCard($provider, "outbound", $outboundInput);
        updateRoundTripSelectionCard($provider, "return", $returnInput);

        $submit.prop("disabled", !(hasOutbound && hasReturn));
    }

    function initRoundTripProviders(scope) {
        var $scope = $(scope || document);

        $scope.find("[data-bus-v2-roundtrip-provider]").each(function () {
            syncRoundTripProvider($(this));
        });
    }

    function resultLazyLists(scope) {
        return $(scope || document)
            .find(".bus-v2-result-list, .bus-v2-desk-trip-list")
            .filter(function () {
                return $(this).children(".bus-v2-trip-card").length > 0;
            });
    }

    function ensureLazyControls($list) {
        var $controls = $list.next("[data-bus-v2-lazy-controls]");

        if (!$controls.length) {
            $controls = $(
                '<div class="bus-v2-lazy-controls" data-bus-v2-lazy-controls>' +
                    '<span class="bus-v2-lazy-count" data-bus-v2-lazy-count></span>' +
                    '<button type="button" class="bus-v2-button bus-v2-button--ghost bus-v2-lazy-load-more" data-bus-v2-lazy-more>Load more</button>' +
                '</div>'
            );
            $list.after($controls);
        }

        return $controls;
    }

    function syncLazyResultList($list) {
        var $cards = $list.children(".bus-v2-trip-card");
        var total = $cards.length;
        var visibleCount = parseInt($list.data("bus-v2-visible-count"), 10);
        var $controls;
        var remaining;

        if (!total) {
            $list.removeAttr("data-bus-v2-lazy-list").removeData("bus-v2-visible-count");
            $list.next("[data-bus-v2-lazy-controls]").remove();
            return;
        }

        if (!visibleCount || visibleCount < 1) {
            visibleCount = lazyResultsInitialCount;
        }

        visibleCount = Math.min(visibleCount, total);
        $list.data("bus-v2-visible-count", visibleCount);

        $cards.each(function (index) {
            var $card = $(this);
            var isHidden = index >= visibleCount;

            $card.toggleClass("bus-v2-lazy-hidden", isHidden);
            if (isHidden) {
                $card.attr("aria-hidden", "true");
            } else {
                $card.removeAttr("aria-hidden");
            }
        });

        if (total <= lazyResultsInitialCount || visibleCount >= total) {
            $list.removeAttr("data-bus-v2-lazy-list");
            $list.next("[data-bus-v2-lazy-controls]").remove();
            return;
        }

        remaining = total - visibleCount;
        $list.attr("data-bus-v2-lazy-list", "true");
        $controls = ensureLazyControls($list);
        $controls.find("[data-bus-v2-lazy-count]").text("Showing " + visibleCount + " of " + total + " trips");
        $controls.find("[data-bus-v2-lazy-more]").text("Load " + Math.min(lazyResultsStepCount, remaining) + " more");
    }

    function revealLazyResults($list, amount) {
        var current = parseInt($list.data("bus-v2-visible-count"), 10) || lazyResultsInitialCount;
        var total = $list.children(".bus-v2-trip-card").length;

        if (!total || current >= total) {
            return;
        }

        $list.data("bus-v2-visible-count", Math.min(total, current + (amount || lazyResultsStepCount)));
        syncLazyResultList($list);
    }

    function runLazyReveal() {
        var viewportBottom = $(window).scrollTop() + $(window).height() + 280;

        lazyRevealQueued = false;

        $("[data-bus-v2-lazy-list]").each(function () {
            var $list = $(this);
            var $controls = $list.next("[data-bus-v2-lazy-controls]");
            var offset;

            if (!$list.is(":visible") || !$controls.length || !$controls.is(":visible")) {
                return;
            }

            offset = $controls.offset();
            if (offset && offset.top <= viewportBottom) {
                revealLazyResults($list, lazyResultsStepCount);
            }
        });
    }

    function scheduleLazyReveal() {
        var raf = window.requestAnimationFrame || function (callback) {
            return window.setTimeout(callback, 16);
        };

        if (lazyRevealQueued) {
            return;
        }

        lazyRevealQueued = true;
        raf(runLazyReveal);
    }

    function initLazyResultLists(scope) {
        resultLazyLists(scope).each(function () {
            syncLazyResultList($(this));
        });
    }

    function validateRoundTripReservationForm($form) {
        if ($form.is("[data-rt-form]")) {
            var outboundTrip = $.trim(String($form.find('[data-rt-outbound-input]').val() || ""));
            var $returnInputNew = $form.find('input[name="return_trip"]:checked').first();

            if (!outboundTrip) {
                return false;
            }
            if (!$returnInputNew.length) {
                return false;
            }

            return true;
        }

        if (!$form.is("[data-bus-v2-roundtrip-provider]")) {
            return true;
        }

        var $outboundInput = $form.find('input[name="outbound_trip"]:checked').first();
        var $returnInput = $form.find('input[name="return_trip"]:checked').first();

        setRoundTripProviderError($form, "");

        if (!$outboundInput.length) {
            setRoundTripProviderError($form, "Select the outbound departure first.");
            return false;
        }

        if (!$returnInput.length) {
            setRoundTripProviderError($form, "Select the return departure to continue.");
            return false;
        }

        return true;
    }

    function searchLoadingContext($form) {
        var from = $.trim($form.find('[name="cityFrom"]').first().val() || $("#busV2From").val() || "");
        var to = $.trim($form.find('[name="cityTo"]').first().val() || $("#busV2To").val() || "");
        var date = $.trim($form.find('[name="departureDate"]').first().val() || $("#busV2DepartureDate").val() || "");
        var passengers = $.trim($form.find('[name="passengers"]').first().val() || $("#busV2Passengers").val() || "");

        return {
            route: $.trim([from, to].join(" -> ")),
            date: date,
            passengers: passengers
        };
    }

    function showResultsLoadingState($form, options) {
        var template = document.getElementById("busV2ResultsLoadingTemplate");
        var $state = $("#busV2State");
        var context = searchLoadingContext($form);

        options = options || {};

        if (!template || !$state.length) {
            return;
        }

        $state
            .html(template.innerHTML)
            .removeClass("is-entered")
            .addClass("is-search-loading");

        $state.find("[data-bus-v2-loading-route]").text(context.route || i18n("results.route", "Route"));
        $state.find("[data-bus-v2-loading-date]").text(context.date || i18n("results.journeyDate", "Journey date"));
        $state.find("[data-bus-v2-loading-passengers]").text(context.passengers || i18n("results.travellers", "Travellers"));

        if (!options.preserveScroll && !prefersReducedMotion()) {
            window.setTimeout(function () {
                var offset = $state.offset();

                if (offset) {
                    $("html, body").stop(true).animate({ scrollTop: Math.max(offset.top - 18, 0) }, 420);
                }
            }, 80);
        }
    }

    function restorePreviousState(options) {
        if (lastRenderedStateHtml) {
            replaceState(lastRenderedStateHtml, options);
        }
    }

    function setSortButtonLoading($form, isLoading, $trigger) {
        if (!$form || !$form.length) {
            return;
        }

        var $buttons = $form.find("[data-bus-v2-quick-sort], [data-bus-v2-quick-carrier]");
        $form.toggleClass("is-sorting", !!isLoading);
        $buttons.prop("disabled", !!isLoading);

        if (isLoading) {
            var $button = $trigger && $trigger.length ? $trigger : $buttons.filter(".is-active").first();

            if ($button.length) {
                $button.data("original-html", $button.html());
                $button.addClass("is-loading").html('<i class="fas fa-spinner fa-spin"></i> ' + $.trim($button.text()));
            }

            return;
        }

        $buttons.each(function () {
            var $button = $(this);
            var originalHtml = $button.data("original-html");

            if (originalHtml) {
                $button.html(originalHtml);
                $button.removeData("original-html");
            }

            $button.removeClass("is-loading");
        });
    }

    function submitAjaxForm(form) {
        var $form = $(form);
        var isSearchForm = $form.attr("id") === "busV2SearchForm";
        var isSortForm = $form.attr("id") === "busV2SortForm";
        var isReservationForm = isReservationCreateForm($form, isSearchForm, isSortForm);
        var $pendingSortButton = isSortForm && pendingSortButton ? $(pendingSortButton) : $();
        var formData;

        pendingSortButton = null;

        if (isSearchForm && !validateSearchForm()) {
            return;
        }

        if (!isSearchForm) {
            if (bookingExpiredState) {
                expireBookingSession();
                return;
            }

            if (!validateRoundTripReservationForm($form)) {
                syncRoundTripProvider($form);
                flash("Select both the outbound and return trip before continuing.", "error");
                return;
            }

            syncFrenchPhoneInputs($form);

            if (!validateCheckoutForm($form, true)) {
                flash(i18n("checkoutValidation.completeCurrent", "Complete the current passenger before continuing."), "error");
                return;
            }
        }

        formData = new FormData(form);

        clearFlash();
        if (isSearchForm) {
            setSearchProgress(true);
            showResultsLoadingState($form);
        } else if (isSortForm) {
            setSortButtonLoading($form, true, $pendingSortButton);
            showResultsLoadingState($form, { preserveScroll: true });
        }
        if (!isSearchForm) {
            clearFormErrors($form);
        }
        if (!isSearchForm && !isSortForm) {
            $("#busV2State").addClass("is-loading");
        }
        if (isReservationForm) {
            showReservationCreatingAlert($form);
        }
        setLoading($form, true);

        $.ajax({
            url: $form.attr("action"),
            method: $form.attr("method") || "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": config.csrfToken
            }
        }).done(function (response) {
            setSearchProgress(false);
            setLoading($form, false);
            closeReservationCreatingAlert();
            if (isSortForm) {
                setSortButtonLoading($form, false);
            }
            $("#busV2State").removeClass("is-loading");

            if (response && response.success === false) {
                var handledErrors = response.errors || {};

                if (response.html !== undefined) {
                    replaceState(response.html, { preserveScroll: isSortForm });
                } else if (isSearchForm || isSortForm) {
                    restorePreviousState({ preserveScroll: isSortForm });
                }

                if (isSearchForm) {
                    if (handledErrors.cityFrom || handledErrors.cityFromHid) {
                        setSearchError("#busV2FromError", (handledErrors.cityFrom || handledErrors.cityFromHid)[0]);
                    }
                    if (handledErrors.cityTo || handledErrors.cityToHid) {
                        setSearchError("#busV2ToError", (handledErrors.cityTo || handledErrors.cityToHid)[0]);
                    }
                    if (handledErrors.departureDate) {
                        setSearchError("#busV2DateError", handledErrors.departureDate[0]);
                    }
                    if (handledErrors.returnDate) {
                        setSearchError("#busV2ReturnDateError", handledErrors.returnDate[0]);
                    }
                    if (handledErrors.adult || handledErrors.child) {
                        setSearchError("#busV2PassengersError", (handledErrors.adult || handledErrors.child)[0]);
                    }
                } else if (!$.isEmptyObject(handledErrors)) {
                    applyFormErrors($form, handledErrors);
                }

                flash(
                    response.message || i18n("messages.genericError", "Something went wrong."),
                    "error",
                    isSearchForm ? flattenErrors(handledErrors) : []
                );
                return;
            }

            if (response.html !== undefined) {
                replaceState(response.html, { preserveScroll: isSortForm });
            }

            if (response.message && !isSortForm) {
                flash(response.message, "success");
            }
        }).fail(function (xhr) {
            setSearchProgress(false);
            setLoading($form, false);
            closeReservationCreatingAlert();
            if (isSortForm) {
                setSortButtonLoading($form, false);
            }
            $("#busV2State").removeClass("is-loading");

            var response = xhr.responseJSON || {};
            var errors = response.errors || {};
            var $errorForm = $form;

            if (response.expired) {
                if (response.html) {
                    replaceState(response.html);
                }

                expireBookingSession();
                flash(response.message || i18n("messages.sessionExpired", "Your booking session has expired. Please restart."), "error");
                return;
            }

            if (response.html) {
                replaceState(response.html, { preserveScroll: isSortForm });

                if (!isSearchForm) {
                    $errorForm = $("#busV2State").find('form[action="' + $form.attr("action") + '"]').first();
                }
            } else if (isSearchForm || isSortForm) {
                restorePreviousState({ preserveScroll: isSortForm });
            }

            if (isSearchForm) {
                if (errors.cityFrom || errors.cityFromHid) {
                    setSearchError("#busV2FromError", (errors.cityFrom || errors.cityFromHid)[0]);
                }
                if (errors.cityTo || errors.cityToHid) {
                    setSearchError("#busV2ToError", (errors.cityTo || errors.cityToHid)[0]);
                }
                if (errors.departureDate) {
                    setSearchError("#busV2DateError", errors.departureDate[0]);
                }
                if (errors.returnDate) {
                    setSearchError("#busV2ReturnDateError", errors.returnDate[0]);
                }
                if (errors.adult || errors.child) {
                    setSearchError("#busV2PassengersError", (errors.adult || errors.child)[0]);
                }
            } else if (!$.isEmptyObject(errors)) {
                applyFormErrors($errorForm.length ? $errorForm : $form, errors);
            }

            flash(
                response.message || i18n("messages.genericError", "Something went wrong."),
                "error",
                isSearchForm ? flattenErrors(errors) : []
            );
        });
    }

    function resetSearchForm() {
        $("#busV2From, #busV2To").val("");
        $("#busV2FromId, #busV2ToId, #busV2GeoLatFrom, #busV2GeoLonFrom, #busV2GeoLatTo, #busV2GeoLonTo").val("");
        $("#busV2DepartureDate").val(config.defaults.date || "");
        $("#busV2ReturnDate").val("");
        $("#busV2TripType").val("one_way");
        $("#busV2Adult").val(1);
        $("#busV2Child").val(0);
        syncTripTypeUI();
        updatePassengerSummary(false);
        clearSearchErrors();
    }

    function decodeTripPayload(raw) {
        var value = $.trim(String(raw || ""));
        var decoded = value;

        if (!value) {
            return null;
        }

        try {
            decoded = window.atob(value);
        } catch (error) {
            decoded = value;
        }

        try {
            return JSON.parse(decoded);
        } catch (error) {
            return null;
        }
    }

    function normalizeTripDetailsPayload(payload) {
        var data = payload || {};
        var legs = $.isArray(data.legs) ? data.legs : [];
        var rawStops = $.isArray(data.stops) ? data.stops.slice() : [];
        var stops = [];
        var fromName = data.from_name || "";
        var toName = data.to_name || "";

        function addStop(stop) {
            var item = stop || {};
            var name = item.name || "";
            var normalized;

            if (!name) {
                return;
            }

            normalized = {
                name: name,
                time: item.time || item.departure_time || item.arrival_time || "",
                arrival_time: item.arrival_time || "",
                duration: item.duration || "",
                type: item.type || "stop"
            };

            if (stops.length && String(stops[stops.length - 1].name || "").toLowerCase() === String(name).toLowerCase()) {
                $.each(["time", "arrival_time", "duration", "type"], function (_, key) {
                    if (!stops[stops.length - 1][key] && normalized[key]) {
                        stops[stops.length - 1][key] = normalized[key];
                    }
                });

                if (normalized.type === "arrival") {
                    stops[stops.length - 1].type = "arrival";
                }

                return;
            }

            stops.push(normalized);
        }

        if (rawStops.length) {
            addStop({
                name: fromName,
                time: data.departure || "",
                type: "departure"
            });

            $.each(rawStops, function (_, stop) {
                addStop(stop);
            });

            addStop({
                name: toName,
                time: data.arrival || "",
                type: "arrival"
            });
        } else if (legs.length) {
            $.each(legs, function (index, leg) {
                var fromName = leg.from_name || "";
                var toName = leg.to_name || "";

                if (fromName && (index === 0 || !stops.length || stops[stops.length - 1].name !== fromName)) {
                    addStop({
                        name: fromName,
                        time: leg.departure || "",
                        type: index === 0 ? "departure" : "stop"
                    });
                }

                if (toName) {
                    addStop({
                        name: toName,
                        time: leg.arrival || "",
                        type: index === legs.length - 1 ? "arrival" : "stop"
                    });
                }
            });
        } else {
            addStop({
                name: fromName,
                time: data.departure || "",
                type: "departure"
            });
            addStop({
                name: toName,
                time: data.arrival || "",
                type: "arrival"
            });
        }

        return {
            provider: data.provider || "",
            operator: data.operator || data.bus_type || "",
            route: data.route || [data.from_name || "", data.to_name || ""].join(" -> "),
            departure: data.departure || "",
            arrival: data.arrival || "",
            duration: data.duration || ((parseInt(data.duration_hour || 0, 10) || 0) + "h " + (parseInt(data.duration_minutes || 0, 10) || 0) + "m"),
            amenities: $.isArray(data.amenities) ? data.amenities : [],
            stops: stops
        };
    }

    function renderTripDetailsHtml(details) {
        var amenitiesHtml = "";
        var stopsHtml = "";

        $.each(details.amenities || [], function (_, amenity) {
            var icon = amenity && amenity.icon ? amenity.icon : "fas fa-check";
            var label = amenity && (amenity.label || amenity.key) ? (amenity.label || amenity.key) : "";
            var key = amenity && (amenity.key || amenity.label) ? String(amenity.key || amenity.label).toLowerCase().replace(/[^a-z0-9_-]+/g, "-").replace(/^-+|-+$/g, "") : "amenity";

            if (!label) {
                return;
            }

            amenitiesHtml += '<span class="bus-v2-journey-amenity bus-v2-amenity-chip bus-v2-amenity-chip--' + escapeHtml(key || "amenity") + '" title="' + escapeHtml(label) + '"><i class="' + escapeHtml(icon) + '" aria-hidden="true"></i><span>' + escapeHtml(label) + "</span></span>";
        });

        $.each(details.stops || [], function (_, stop) {
            var name = stop && stop.name ? stop.name : "";
            var time = stop && (stop.time || stop.arrival_time) ? (stop.time || stop.arrival_time) : "";
            var duration = stop && stop.duration ? stop.duration : "";
            var type = stop && stop.type ? String(stop.type) : "stop";
            var label = type === "departure" ? "Departure" : (type === "arrival" ? "Arrival" : "Stop");

            if (!name) {
                return;
            }

            if (type !== "departure" && type !== "arrival") {
                type = "stop";
            }

            stopsHtml += '<div class="bus-v2-journey-stop-item is-' + escapeHtml(type) + '">' +
                '<span class="bus-v2-journey-stop-marker"></span>' +
                '<div class="bus-v2-journey-stop-copy">' +
                    "<strong>" + escapeHtml(name) + "</strong>" +
                    "<span>" + escapeHtml(label) + (duration ? " - " + escapeHtml(duration) : "") + "</span>" +
                "</div>" +
                '<span class="bus-v2-journey-stop-time">' + escapeHtml(time || "-") + "</span>" +
            "</div>";
        });

        if (!stopsHtml) {
            stopsHtml = '<div class="bus-v2-empty-copy">No stop details available for this trip.</div>';
        }

        return "" +
            '<div class="bus-v2-journey-details-stack">' +
                '<div class="bus-v2-journey-details-card">' +
                    '<div class="bus-v2-journey-details-head">' +
                        '<span><i class="far fa-clock"></i> ' + escapeHtml(details.departure || "--:--") + " - " + escapeHtml(details.arrival || "--:--") + "</span>" +
                        "<strong>" + escapeHtml(details.duration || "") + "</strong>" +
                    "</div>" +
                    '<div class="bus-v2-journey-stops">' + stopsHtml + "</div>" +
                "</div>" +
                '<div class="bus-v2-journey-amenities">' +
                    "<h4>Amenities</h4>" +
                    '<div class="bus-v2-journey-amenity-list">' + (amenitiesHtml || '<span class="bus-v2-chip">No amenities listed</span>') + "</div>" +
                "</div>" +
            "</div>";
    }

    function hasRemoteTripDetails(payload) {
        var data = payload || {};

        return !!(
            ($.isArray(data.stops) && data.stops.length) ||
            ($.isArray(data.legs) && data.legs.length) ||
            data.from_name ||
            data.to_name ||
            data.departure ||
            data.arrival
        );
    }

    function closeTripDetailsModal() {
        $("[data-rt-trip-modal]").prop("hidden", true).removeClass("is-open");
    }

    function resolveTripDetailsModal() {
        var $modals = $("[data-rt-trip-modal]");
        var $modal = $modals.filter(function () {
            return this.parentNode === document.body;
        }).first();

        if (!$modal.length) {
            $modal = $modals.first();
        }

        if ($modal.length) {
            $modals.not($modal).remove();

            if ($modal.parent()[0] !== document.body) {
                $modal.appendTo(document.body);
            }
        }

        return $modal;
    }

    function openTripDetailsModal(direction, tripEncoded) {
        var $modal = resolveTripDetailsModal();
        var localTrip = decodeTripPayload(tripEncoded) || {};
        var localDetails = normalizeTripDetailsPayload(localTrip);

        if (!$modal.length) {
            return;
        }

        $modal.find("[data-rt-modal-direction]").text(direction === "return" ? "Return trip details" : "Departure trip details");
        $modal.find("[data-rt-modal-title]").text(localDetails.route || "Trip details");
        $modal.find("[data-rt-modal-subtitle]").text((localDetails.departure || "--:--") + " - " + (localDetails.arrival || "--:--"));
        $modal.find("[data-rt-modal-body]").html(renderTripDetailsHtml(localDetails));
        $modal.prop("hidden", false).addClass("is-open");

        if (!config.routes || !config.routes.tripStops) {
            return;
        }

        $.ajax({
            url: config.routes.tripStops,
            method: "POST",
            data: { trip: tripEncoded || "" },
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": config.csrfToken
            }
        }).done(function (response) {
            var remoteTrip = (response && response.data) || {};
            var details;

            if (!hasRemoteTripDetails(remoteTrip)) {
                return;
            }

            details = normalizeTripDetailsPayload($.extend({}, localTrip, remoteTrip));

            if (!$modal.hasClass("is-open")) {
                return;
            }

            $modal.find("[data-rt-modal-title]").text(details.route || localDetails.route || "Trip details");
            $modal.find("[data-rt-modal-subtitle]").text((details.departure || localDetails.departure || "--:--") + " - " + (details.arrival || localDetails.arrival || "--:--"));
            $modal.find("[data-rt-modal-body]").html(renderTripDetailsHtml(details));
        });
    }

    $(function () {
        lastRenderedStateHtml = $("#busV2State").html();
        initSearchScope();
        initStateScope(document.getElementById("busV2State"));
        watchReservationLoaderMarkup();

        $(window).on("scroll.busV2Lazy resize.busV2Lazy", scheduleLazyReveal);

        $(document).on("click", "[data-bus-v2-lazy-more]", function () {
            var $list = $(this)
                .closest("[data-bus-v2-lazy-controls]")
                .prev("[data-bus-v2-lazy-list]");

            revealLazyResults($list, lazyResultsStepCount);
        });

        $(document).on("click", "[data-passenger-toggle]", function (event) {
            event.preventDefault();
            event.stopPropagation();
            togglePassengerPanel();
        });

        $(document).on("click", ".js-bus-v2-passenger-apply", function () {
            togglePassengerPanel(false);
        });

        $(document).on("click", ".js-bus-v2-trip-type", function () {
            $("#busV2TripType").val($(this).data("value"));
            pulseSearchCard("is-trip-switching", 320);
            syncTripTypeUI();
        });

        $(document).on("change", "#busV2DepartureDate", function () {
            syncReturnDateConstraints();
        });

        $(document).on("change", "#busV2ReturnDate", function () {
            syncTravelDayLabels();
        });

        $(document).on("click", ".js-bus-v2-datepicker", function () {
            showTravelDatepicker($(this));
        });

        $(document).on("click", "#busV2FromField, #busV2ToField", function (event) {
            var $target = $(event.target);
            var $card = $(this);
            var selector = $card.is("#busV2FromField") ? "#busV2From" : "#busV2To";
            var $input = $(selector);

            if (!$input.length || $target.is("input, button, a, label") || $target.closest("input, button, a, label, .ui-autocomplete").length) {
                return;
            }

            $input.trigger("focus");

            window.setTimeout(function () {
                var input = $input.get(0);
                if (input && typeof input.setSelectionRange === "function") {
                    var length = ($input.val() || "").length;
                    input.setSelectionRange(length, length);
                }
            }, 0);
        });

        $(document).on("click", ".bpr-detail-card--date", function (event) {
            if ($(event.target).closest("input, button, a, .ui-datepicker, #ui-datepicker-div").length) {
                return;
            }

            showTravelDatepicker($("#busV2DepartureDate"));
        });

        $(document).on("click", "#busV2ReturnField .bpr-return-card", function (event) {
            if ($(event.target).closest("input, button, a, .ui-datepicker, #ui-datepicker-div").length) {
                return;
            }

            showTravelDatepicker($("#busV2ReturnDate"));
        });

        $(document).on("click", "#busV2PaxCard", function (event) {
            var $target = $(event.target);

            if ($target.closest(".bus-v2-passenger-panel, .bpr-pax-panel, .bsk-pax-panel").length || $target.closest(".js-bus-v2-stepper, .js-bus-v2-passenger-apply").length) {
                return;
            }

            togglePassengerPanel(true);
        });

        $(document).on("click", ".bus-v2-passenger-field", function (event) {
            var $target = $(event.target);

            if ($target.closest("[data-passenger-toggle], .bus-v2-passenger-panel, .bpr-pax-panel, .bsk-pax-panel").length || $target.closest(".js-bus-v2-stepper, .js-bus-v2-passenger-apply").length) {
                return;
            }

            togglePassengerPanel(true);
        });

        $(document).on("focus blur input", "#busV2From, #busV2To", function (event) {
            $(this).closest(".bus-v2-input-wrap").toggleClass("is-focused", event.type === "focus");
            if (event.type === "blur") {
                $(this).closest(".bus-v2-input-wrap").removeClass("is-focused");
            }
            syncInputWrapState(this);
        });

        $(document).on("input change", checkoutRootSelector + " [data-error-key]", function () {
            var $input = $(this);
            var $list = $input.closest("[data-passenger-step-list]");

            clearFieldError($input);

            if ($input.is("[data-phone-visible]")) {
                syncPhoneHiddenValue($input);
            }

            if ($list.length) {
                updatePassengerStepControls($list);
                return;
            }

            updateCheckoutSubmitState($input.closest("form"));
        });

        $(document).on("blur", checkoutRootSelector + " [data-error-key]", function () {
            var $input = $(this);
            var $list = $input.closest("[data-passenger-step-list]");
            var message = validationMessageForInput($input);

            if (message) {
                setFieldError($input, message);
            } else {
                clearFieldError($input);
            }

            if ($list.length) {
                updatePassengerStepControls($list);
                return;
            }

            updateCheckoutSubmitState($input.closest("form"));
        });

        $(document).on("blur", "[data-phone-visible]", function () {
            var $input = $(this);

            syncPhoneHiddenValue($input);
            $input.val(displayFrenchPhone($input.val()));
        });

        $(document).on("click", "[data-passenger-step-prev]", function () {
            var $list = $(this).closest("[data-passenger-step-list]");
            var current = parseInt($list.attr("data-active-step"), 10) || 0;

            setPassengerStep($list, current - 1, { scroll: true, focus: true });
        });

        $(document).on("click", "[data-passenger-step-next]", function () {
            var $list = $(this).closest("[data-passenger-step-list]");
            var current = parseInt($list.attr("data-active-step"), 10) || 0;
            var result = validatePassengerCard(activePassengerCard($list), true);

            if (!result.valid) {
                if (result.firstInvalid.length) {
                    window.setTimeout(function () {
                        var offset = result.firstInvalid.offset();

                        if (offset) {
                            $("html, body").animate({ scrollTop: Math.max(offset.top - 150, 0) }, 220);
                        }

                        result.firstInvalid.trigger("focus");
                    }, 40);
                }

                updatePassengerStepControls($list);
                return;
            }

            setPassengerStep($list, current + 1, { scroll: true, focus: true });
        });

        $(document).on("click", "[data-passenger-step-target]", function () {
            var $target = $(this);
            var $list = $target.closest("[data-passenger-step-list]");
            var current = parseInt($list.attr("data-active-step"), 10) || 0;
            var stepIndex = parseInt($target.data("passenger-step-target"), 10) || 0;
            var result;

            if (stepIndex > current) {
                result = validatePassengerCard(activePassengerCard($list), true);

                if (!result.valid) {
                    if (result.firstInvalid.length) {
                        window.setTimeout(function () {
                            var offset = result.firstInvalid.offset();

                            if (offset) {
                                $("html, body").animate({ scrollTop: Math.max(offset.top - 150, 0) }, 220);
                            }

                            result.firstInvalid.trigger("focus");
                        }, 40);
                    }

                    updatePassengerStepControls($list);
                    return;
                }
            }

            setPassengerStep($list, stepIndex, { scroll: true, focus: true });
        });

        $(document).on("mousedown", function (event) {
            if (!$(event.target).closest(".bus-v2-passenger-field").length) {
                togglePassengerPanel(false);
            }
        });

        $(window).on("resize scroll", function () {
            syncPassengerPanelPlacement();
        });

        $(document).on("click", ".js-bus-v2-stepper", function () {
            var $button = $(this);
            var $target = $($button.data("target"));
            var current = parseInt($target.val(), 10) || 0;
            var next = current + (parseInt($button.data("step"), 10) || 0);
            var min = parseInt($button.data("min"), 10);
            var max = parseInt($button.data("max"), 10);

            if (!isNaN(min) && next < min) {
                next = min;
            }
            if (!isNaN(max) && next > max) {
                next = max;
            }

            if (next === current) {
                return;
            }

            $target.val(next);
            updatePassengerSummary(true);
            animatePassengerChange($button.data("target"), next - current);
        });

        $(document).on("change", ".bus-v2-citizenship", function () {
            var $select = $(this);
            $("#" + $select.data("target")).val($select.find("option:selected").data("iso3") || "");
        });

        $(document).on("submit", "#busV2SearchForm, .js-bus-v2-ajax-form", function (event) {
            event.preventDefault();
            submitAjaxForm(this);
        });

        $(document).on("change", ".js-bus-v2-sort-select", function () {
            $("#busV2SortForm").trigger("submit");
        });

        $(document).on("click", "[data-bus-v2-quick-sort]", function () {
            var sortValue = String($(this).data("bus-v2-quick-sort") || "1");

            pendingSortButton = this;
            $("#busV2SortBy").val(sortValue);
            $("#busV2SortForm").trigger("submit");
        });

        $(document).on("click", "[data-bus-v2-quick-carrier]", function () {
            var carrierValue = String($(this).data("bus-v2-quick-carrier") || "");

            pendingSortButton = this;
            $("#busV2SortCarrier").val(carrierValue);
            $("#busV2SortForm").trigger("submit");
        });

        $(document).on("click", "#busV2Swap", function () {
            var from = $("#busV2From").val();
            var fromId = $("#busV2FromId").val();
            var fromLat = $("#busV2GeoLatFrom").val();
            var fromLon = $("#busV2GeoLonFrom").val();

            animateRouteSwap();

            $("#busV2From").val($("#busV2To").val());
            $("#busV2FromId").val($("#busV2ToId").val());
            $("#busV2GeoLatFrom").val($("#busV2GeoLatTo").val());
            $("#busV2GeoLonFrom").val($("#busV2GeoLonTo").val());

            $("#busV2To").val(from);
            $("#busV2ToId").val(fromId);
            $("#busV2GeoLatTo").val(fromLat);
            $("#busV2GeoLonTo").val(fromLon);

            syncInputWrapState("#busV2From");
            syncInputWrapState("#busV2To");
            clearSearchErrors();
        });

        $(document).on("click", "[data-bus-v2-reset]", function () {
            clearFlash();
            $.ajax({
                url: config.routes.reset,
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": config.csrfToken
                }
            }).done(function (response) {
                clearBookingExpiry();
                hideExpiryModal();
                resetSearchForm();
                togglePassengerPanel(false);
                replaceState(response.html || "");
                if (response.message) {
                    flash(response.message, "success");
                }
            }).fail(function () {
                flash(i18n("messages.resetFailed", "Unable to reset the flow."), "error");
            });
        });

        $(document).on("click", "[data-bus-v2-restart-booking]", function () {
            clearBookingExpiry();
            window.location.href = config.routes.restart || config.routes.index || window.location.pathname;
        });

        $(document).on("change", '[data-bus-v2-roundtrip-provider] input[name="outbound_trip"]', function () {
            var $provider = $(this).closest("[data-bus-v2-roundtrip-provider]");
            var $returnPanel = $provider.find("[data-roundtrip-return-panel]");

            setRoundTripProviderError($provider, "");
            syncRoundTripProvider($provider);

            if ($returnPanel.length) {
                var offset = $returnPanel.offset();

                if (offset) {
                    $("html, body").stop(true).animate({ scrollTop: Math.max(offset.top - 24, 0) }, 260);
                }
            }
        });

        $(document).on("change", '[data-bus-v2-roundtrip-provider] input[name="return_trip"]', function () {
            var $provider = $(this).closest("[data-bus-v2-roundtrip-provider]");

            setRoundTripProviderError($provider, "");
            syncRoundTripProvider($provider);
        });

        $(document).on("click", "[data-roundtrip-back]", function () {
            var $provider = $(this).closest("[data-bus-v2-roundtrip-provider]");
            var $outboundSection = $provider.find('[data-roundtrip-step-indicator="outbound"]').first();

            $provider.find('input[name="return_trip"]').prop("checked", false);
            setRoundTripProviderError($provider, "");
            syncRoundTripProvider($provider);

            if ($outboundSection.length) {
                var offset = $outboundSection.offset();

                if (offset) {
                    $("html, body").stop(true).animate({ scrollTop: Math.max(offset.top - 24, 0) }, 220);
                }
            }
        });

        // ── Round-trip split (simultaneous outbound + return selection) ──────────

        $(document).on("click", "[data-bus-v2-roundtrip-flow] [data-rt-select-outbound]", function () {
            var $btn   = $(this);
            var $card  = $btn.closest("[data-rt-outbound-card]");
            var $flow  = $btn.closest("[data-bus-v2-roundtrip-flow]");
            var prov   = $card.data("provider");

            // Mark this outbound card as selected; deselect others
            $flow.find("[data-rt-outbound-card]").removeClass("is-selected");
            $card.addClass("is-selected");

            // Update all outbound select buttons (reset label/icon)
            $flow.find(".bus-v2-rt-select-btn").each(function () {
                $(this).find(".bus-v2-rt-select-btn-check").prop("hidden", true);
                $(this).find(".bus-v2-rt-select-btn-label").text("Select departure");
                $(this).removeClass("bus-v2-rt-select-btn--chosen");
            });

            // Mark this button as chosen
            $btn.addClass("bus-v2-rt-select-btn--chosen");
            $btn.find(".bus-v2-rt-select-btn-check").prop("hidden", false);
            $btn.find(".bus-v2-rt-select-btn-label").text("Departure selected");

            // Populate the outbound hidden input in the matching provider form
            $flow.find('[data-rt-returns-for="' + prov + '"] [data-rt-outbound-input]').val($card.data("trip-value") || "");

            // Fill the recap bar
            $flow.find("[data-rt-recap-window]").text($card.data("trip-window") || "");
            $flow.find("[data-rt-recap-route]").text($card.data("trip-route")   || "");
            $flow.find("[data-rt-selection-bar]").prop("hidden", false);

            // Reset any previously-chosen return selection
            $flow.find('input[name="return_trip"]').prop("checked", false);
            $flow.find("[data-rt-card-submit]").prop("disabled", true);
            $flow.find(".bus-v2-rt-return-card").removeClass("is-selected");
            $flow.find("[data-rt-select-return]").removeClass("bus-v2-rt-return-select-btn--chosen");
            $flow.find(".bus-v2-rt-return-select-check").prop("hidden", true);
            $flow.find(".bus-v2-rt-return-select-label").text("Select return");
            $flow.find("[data-rt-sel-return]").addClass("bus-v2-rt-selection-leg--pending");
            $flow.find("[data-rt-sel-return] strong").text("Choose return trip ->");

            // Show matching provider's returns; hide others; hide placeholder
            $flow.find("[data-rt-return-placeholder]").prop("hidden", true);
            $flow.find("[data-rt-returns-for]").prop("hidden", true);
            $flow.find('[data-rt-returns-for="' + prov + '"]').prop("hidden", false);

            // Step flow: hide departure list and focus the return selection on the same page.
            $flow.removeClass("is-choosing-departure").addClass("is-choosing-return");
            $flow.find(".bus-v2-rt-split-col--return").removeClass("is-step-entering");
            if ($flow.find(".bus-v2-rt-split-col--return")[0]) {
                void $flow.find(".bus-v2-rt-split-col--return")[0].offsetWidth;
            }
            $flow.find(".bus-v2-rt-split-col--return").addClass("is-step-entering");

            // Scroll the return column into view on mobile
            var $returnCol = $flow.find(".bus-v2-rt-split-col--return");
            if ($returnCol.length) {
                $("html, body").stop(true).animate({ scrollTop: Math.max($returnCol.offset().top - 18, 0) }, 300);
            }
        });

        $(document).on("click", "[data-bus-v2-roundtrip-flow] [data-rt-change-departure]", function (event) {
            var $flow = $(this).closest("[data-bus-v2-roundtrip-flow]");
            var $outboundCol = $flow.find(".bus-v2-rt-split-col--outbound").first();

            event.preventDefault();

            $flow.removeClass("is-choosing-return").addClass("is-choosing-departure");
            $flow.find("[data-rt-selection-bar]").prop("hidden", true);
            $flow.find("[data-rt-outbound-card]").removeClass("is-selected");
            $flow.find(".bus-v2-rt-select-btn").removeClass("bus-v2-rt-select-btn--chosen");
            $flow.find(".bus-v2-rt-select-btn-check").prop("hidden", true);
            $flow.find(".bus-v2-rt-select-btn-label").text("Select departure");
            $flow.find("[data-rt-returns-for]").prop("hidden", true);
            $flow.find("[data-rt-return-placeholder]").prop("hidden", false);
            $flow.find("[data-rt-outbound-input]").val("");
            $flow.find('input[name="return_trip"]').prop("checked", false);
            $flow.find("[data-rt-card-submit]").prop("disabled", true);
            $flow.find(".bus-v2-rt-return-card").removeClass("is-selected");
            $flow.find("[data-rt-select-return]").removeClass("bus-v2-rt-return-select-btn--chosen");
            $flow.find(".bus-v2-rt-return-select-check").prop("hidden", true);
            $flow.find(".bus-v2-rt-return-select-label").text("Select return");
            $flow.find("[data-rt-sel-return]").addClass("bus-v2-rt-selection-leg--pending");
            $flow.find("[data-rt-sel-return] strong").text("Choose return trip ->");

            if ($outboundCol.length) {
                $("html, body").stop(true).animate({ scrollTop: Math.max($outboundCol.offset().top - 18, 0) }, 260);
            }
        });

        $(document).on("change", "[data-bus-v2-roundtrip-flow] [data-rt-return-radio]", function () {
            var $flow  = $(this).closest("[data-bus-v2-roundtrip-flow]");
            var $label = $(this).closest(".bus-v2-rt-return-card");

            $flow.find(".bus-v2-rt-return-card").removeClass("is-selected");
            $label.addClass("is-selected");
            $flow.find("[data-rt-card-submit]").prop("disabled", true);
            $label.find("[data-rt-card-submit]").prop("disabled", false);
            $flow.find("[data-rt-select-return]").removeClass("bus-v2-rt-return-select-btn--chosen");
            $flow.find(".bus-v2-rt-return-select-check").prop("hidden", true);
            $flow.find(".bus-v2-rt-return-select-label").text("Select return");
            $label.find("[data-rt-select-return]").addClass("bus-v2-rt-return-select-btn--chosen");
            $label.find(".bus-v2-rt-return-select-check").prop("hidden", false);
            $label.find(".bus-v2-rt-return-select-label").text("Return selected");

            // Update recap bar return leg
            var retWindow = ($label.find(".bus-v2-trip-stop strong").first().text() || "") +
                            " – " +
                            ($label.find(".bus-v2-trip-stop--arrival strong").first().text() || "");
            $flow.find("[data-rt-sel-return] strong").text(retWindow);
            $flow.find("[data-rt-sel-return]").removeClass("bus-v2-rt-selection-leg--pending");
        });

        $(document).on("click", "[data-bus-v2-roundtrip-flow] [data-rt-select-return]", function (event) {
            var $card = $(this).closest(".bus-v2-rt-return-card");
            var $radio = $card.find("[data-rt-return-radio]").first();

            event.preventDefault();
            event.stopPropagation();

            if (!$radio.length || $radio.prop("disabled")) {
                return;
            }

            $radio.prop("checked", true).trigger("change");
        });

        $(document).on("click", "[data-rt-view-details]", function (event) {
            var $button = $(this);
            var tripEncoded = String($button.data("rt-trip") || "");
            var direction = String($button.data("rt-direction") || "outbound");

            event.preventDefault();
            event.stopImmediatePropagation();

            openTripDetailsModal(direction, tripEncoded);

            return false;
        });

        $(document).on("click", "[data-rt-modal-close]", function (event) {
            event.preventDefault();
            event.stopPropagation();
            closeTripDetailsModal();
        });

        $(document).on("keydown", function (event) {
            if (event.key === "Escape") {
                closeTripDetailsModal();
            }
        });

        // ─────────────────────────────────────────────────────────────────────────

        $(document).on("click", "[data-bus-v2-scroll-search]", function () {
            var offset = $("#busV2SearchForm").offset();

            if (offset) {
                $("html, body").animate({ scrollTop: Math.max(offset.top - 18, 0) }, 320);
            }
        });

        $(document).on("click", "[data-bus-v2-scroll-first-trip]", function () {
            var $trip = $(".bus-v2-trip-card:not(.bus-v2-lazy-hidden)").not(".is-disabled").first();
            var offset;

            if (!$trip.length) {
                $trip = $(".bus-v2-trip-card:not(.bus-v2-lazy-hidden)").first();
            }

            offset = $trip.offset();

            if (offset) {
                $("html, body").animate({ scrollTop: Math.max(offset.top - 18, 0) }, 320);
            }
        });
    });
})(window, window.jQuery);
