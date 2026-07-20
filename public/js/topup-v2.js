(function () {
    function show(el) { $(el).removeClass('hide'); }
    function hide(el) { $(el).addClass('hide'); }
    function escapeHtml(value) {
        if (value === null || value === undefined) { return ''; }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
    var v2Cache = {
        ttlMs: 60000,
        providers: {},
        products: {},
        routes: {},
        estimates: {}
    };
    var pendingReloadlyDataCode = '';
    var currentProviderLogo = '';
    var currentTransferMeta = { denominationStyle: '', isDenominated: '' };
    var currentTellusGroupKey = '';
    var currentTellusType = '';

    function buildCacheKey(parts) {
        return parts.join('|');
    }

    function getCache(store, key) {
        var item = store[key];
        if (!item) { return null; }
        if ((Date.now() - item.time) > v2Cache.ttlMs) {
            delete store[key];
            return null;
        }
        return item.value;
    }

    function setCache(store, key, value) {
        store[key] = { time: Date.now(), value: value };
    }

    var rangeEstimateTimer = null;
    var lastRangeEstimate = { amount: null, providerCode: null };
    function scheduleDingEstimate(sendAmount, providerCode) {
        if (!sendAmount || isNaN(sendAmount)) { return; }
        if (lastRangeEstimate.amount === sendAmount && lastRangeEstimate.providerCode === providerCode) { return; }
        if (rangeEstimateTimer) { clearTimeout(rangeEstimateTimer); }
        rangeEstimateTimer = setTimeout(function () {
            lastRangeEstimate.amount = sendAmount;
            lastRangeEstimate.providerCode = providerCode;
            getDingEstimate(sendAmount, providerCode, true);
        }, 400);
    }

    function setCurrentProviderLogo(logo) {
        currentProviderLogo = logo || '';
    }

    function scrollToSection(selector) {
        var $target = $(selector);
        if (!$target.length) { return; }
        var offset = $target.offset().top - 12;
        $('html, body').animate({ scrollTop: offset }, 300);
    }

    function getProviderLogoHtml(name) {
        var safeName = escapeHtml(name || '');
        var safeLogo = escapeHtml(currentProviderLogo || '');
        if (safeLogo) {
            return '<img src="' + safeLogo + '" alt="' + safeName + '">';
        }
        var initial = escapeHtml((name || '').charAt(0) || '?');
        return '<div class="tama-v2-product-logo-fallback">' + initial + '</div>';
    }

    function initSingleTabGuard() {
        var storageKey = 'tama-topup-v2-open';
        var tabId = String(Date.now()) + '-' + String(Math.random()).slice(2);
        var heartbeatMs = 8000;
        var staleMs = heartbeatMs * 2;

        function safeParse(raw) {
            try { return JSON.parse(raw); } catch (e) { return null; }
        }

        function getState() {
            return safeParse(localStorage.getItem(storageKey));
        }

        function setState() {
            localStorage.setItem(storageKey, JSON.stringify({ tabId: tabId, ts: Date.now() }));
        }

        function clearState() {
            var state = getState();
            if (state && state.tabId === tabId) {
                localStorage.removeItem(storageKey);
            }
        }

        var existing = getState();
        if (existing && existing.ts && (Date.now() - existing.ts) < staleMs) {
            $.alert({ title: 'Info', content: 'This page is already open in another tab.' });
            setTimeout(function () {
                window.location.href = api_base_url + '/dashboard';
            }, 800);
            return;
        }

        setState();
        var heartbeat = setInterval(setState, heartbeatMs);
        window.addEventListener('beforeunload', function () {
            clearInterval(heartbeat);
            clearState();
        });
    }

    function initProductionInspectGuard() {
        if (!window.v2BlockInspect) { return; }

        document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
        });

        document.addEventListener('keydown', function (e) {
            var key = String(e.key || '').toLowerCase();
            var code = e.keyCode || e.which;
            var blocked = false;

            if (code === 123) {
                blocked = true;
            }

            if (e.ctrlKey && e.shiftKey && (key === 'i' || key === 'j' || key === 'c')) {
                blocked = true;
            }

            if (e.ctrlKey && !e.shiftKey && (key === 'u' || key === 's')) {
                blocked = true;
            }

            if (blocked) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }, true);
    }

    function clearSelectionFields() {
        var ids = [
            '#ding_skuCode', '#ding_SendValue', '#ding_SendValueOriginal', '#ding_SendCurrencyIso',
            '#ding_commissionRate', '#ding_UatNumber', '#ding_euro_amount', '#ding_euro_amount_formatted',
            '#ding_dest_amount', '#ding_dest_amount_formatted',
            '#reloadly_skuCode', '#reloadly_SendValue', '#reloadly_sendValueOriginal', '#reloadly_local_amount',
            '#reloadly_description', '#reloadly_country', '#reloadly_operator',
            '#tellus_skuCode', '#tellus_SendValue', '#tellus_sendValueOriginal', '#tellus_local_amount',
            '#tellus_description', '#tellus_country', '#tellus_operator', '#tellus_currency',
            '#tellus_minSendValue', '#tellus_maxSendValue', '#tellus_priceValue',
            '#tellus_priceValueMax', '#tellus_localAmount',
            '#tellus_localAmountMin', '#tellus_localAmountMax', '#tellus_product',
            '#tellus_productId', '#tellus_productName',
            '#tellus_type', '#tellus_structure', '#tellus_infoMode',
            '#transfer_skuCode', '#transfer_SendValue', '#transfer_ReceiveValue', '#transfer_sendCurrencyIso',
            '#transfer_receiveCurrencyIso', '#transfer_operator_id', '#transfer_operator_name',
            '#transfer_country', '#transfer_display_text', '#transfer_name'
        ];
        for (var i = 0; i < ids.length; i++) {
            $(ids[i]).val('');
        }
        currentTransferMeta = { denominationStyle: '', isDenominated: '' };
        $('#amountReceived').text('');
    }

    function clearReviewModal() {
        $('#v2ReviewModalLabel').text('');
        $('#v2ReviewModalBody').empty();
    }

    function showRouteError(message) {
        var $msg = $('#routeErrorMessage');
        if (!$msg.length) { return; }
        var defaultText = window.v2ServiceNotAvailable || 'Service temporarily unavailable.';
        $msg.text(message || defaultText);
        $msg.removeClass('hide');
    }

    function hideRouteError() {
        $('#routeErrorMessage').addClass('hide');
    }

    function formatCurrencyLabel(value) {
        if (!value) { return '\u20AC'; }
        var code = String(value).trim().toUpperCase();
        if (code === 'EUR') { return '\u20AC'; }
        if (code === 'USD') { return '$'; }
        if (code === 'GBP') { return '\u00A3'; }
        return value;
    }

    function formatTransferReceive(value) {
        var amount = value.ReceiveValue !== undefined && value.ReceiveValue !== null ? String(value.ReceiveValue) : '';
        var unit = value.receiveCurrencyIso || '';
        var symbol = formatCurrencyLabel(unit);
        if (unit && symbol !== unit) {
            return symbol + amount;
        }
        if (unit) {
            return amount + ' ' + unit;
        }
        return amount;
    }

    function buildCommonRow(label, value) {
        if (!value) { return ''; }
        return '<div class="tama-v2-product-meta"><span>' + label + '</span><strong>' + escapeHtml(value) + '</strong></div>';
    }

    function buildCommonBackBody(amountText, receiveText, validityText, detailsText) {
        var html = '';
        html += buildCommonRow('Amount', amountText);
        html += buildCommonRow('Receive', receiveText);
        html += buildCommonRow('Validity', validityText);
        html += buildCommonRow('Details', detailsText);
        return html;
    }

    function formatTellusNumeric(value, forceTwoDecimals) {
        var num = parseFloat(value);
        if (isNaN(num)) { return value || ''; }
        if (forceTwoDecimals) { return num.toFixed(2); }
        if (Math.abs(num - Math.round(num)) < 0.0000001) {
            return String(Math.round(num));
        }
        return num.toFixed(2).replace(/\.00$/, '').replace(/(\.\d*[1-9])0+$/, '$1');
    }

    function extractTellusRangeMatch(value) {
        var match = String(value || '').match(/(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)/);
        if (!match) { return null; }
        return {
            min: formatTellusNumeric(match[1]),
            max: formatTellusNumeric(match[2])
        };
    }

    function extractTellusFixedDenomination(item) {
        var candidates = [item.productName, item.name, item.product];
        for (var i = 0; i < candidates.length; i++) {
            var raw = String(candidates[i] || '').trim();
            if (!raw) { continue; }
            if (extractTellusRangeMatch(raw)) { continue; }
            var match = raw.match(/(\d+(?:\.\d+)?)/);
            if (match) {
                return formatTellusNumeric(match[1]);
            }
        }
        return '';
    }

    function getTellusChargeAmount(item) {
        var structure = String(item.structure || '').toUpperCase();
        var value = structure === 'RANGE' ? item.SendValue : (item.sendValueOriginal !== undefined && item.sendValueOriginal !== null && item.sendValueOriginal !== '' ? item.sendValueOriginal : item.SendValue);
        return formatTellusNumeric(value, true);
    }

    function getTellusProductValue(item) {
        var denomination = extractTellusFixedDenomination(item);
        if (denomination) {
            return denomination;
        }
        return formatTellusNumeric(item.product || item.localAmount || item.ReceiveValue);
    }

    function getTellusReceiveAmount(item) {
        if (String(item.structure || '').toUpperCase() !== 'RANGE') {
            var denomination = extractTellusFixedDenomination(item);
            if (denomination) {
                return denomination;
            }
        }
        var receive = item.localAmount !== undefined && item.localAmount !== null && item.localAmount !== ''
            ? item.localAmount
            : item.ReceiveValue;
        return formatTellusNumeric(receive);
    }

    function setRangeMeta(minText, maxText, currencyLabel) {
        $('#rangeMin').text(minText || '');
        $('#rangeMax').text(maxText || '');
        $('#rangeCurrency').text(formatCurrencyLabel(currencyLabel));
    }

    function showRangeError(message) {
        $('#rangeError').text(message || 'Enter a valid amount.').removeClass('hide');
        $('.tama-v2-range-input').addClass('is-invalid');
    }

    function clearRangeError() {
        $('#rangeError').addClass('hide').text('');
        $('.tama-v2-range-input').removeClass('is-invalid');
    }

    function resetProducts() {
        $('#productList').empty();
        $('#productGrid').empty();
        $('#productSearch').val('');
        hideTellusCategoryFilters();
        hide('#productSection');
        hide('#rangeSection');
        hide('#summarySection');
        hide('#amountReceivedDiv');
        $('#range_amount').val('');
        clearSelectionFields();
        clearReviewModal();
        updateTabs('search');
    }

    function updateTabs(stepKey) {
        var $tabs = $('.tama-v2-tab');
        if (!$tabs.length) { return; }
        var activeIndex = -1;
        $tabs.each(function (idx) {
            if ($(this).data('step') === stepKey) { activeIndex = idx; }
        });
        if (activeIndex == -1) { activeIndex = 0; }
        $tabs.each(function (idx) {
            var $tab = $(this);
            $tab.removeClass('is-active is-done');
            if (idx < activeIndex) {
                $tab.addClass('is-done');
            } else if (idx == activeIndex) {
                $tab.addClass('is-active');
            }
            $tab.attr('aria-current', idx == activeIndex ? 'step' : 'false');
        });
    }
    function setProvider(provider) {
        $('#currentProvider').val(provider);
        resetProducts();
        hide('#providerSection');
        hide('#transferTypeSection');
        hide('#reloadlyModeSection');
        if (provider === 'reloadly') {
            $('#reloadlyMode').val('airtime');
        }
        if (provider === 'transfer') {
            hide('#transferTypeSection');
        }
    }

    var v2Iti = null;
    var v2UtilsReady = false;
    function getUtils() {
        return window.intlTelInput && window.intlTelInput.utils ? window.intlTelInput.utils : null;
    }
    function getMobile() {
        var utils = getUtils();
        if (!v2Iti || !utils) { return ''; }
        return v2Iti.getNumber(utils.numberFormat.E164) || '';
    }
    function getCountryCode() { return $('#countryCode').val(); }
    function getCountryIso() { return $('#countryIso').val(); }

    function normalizeOperatorName(name) {
        if (!name) { return ''; }
        var cleaned = String(name).replace(/\s+/g, ' ').trim();
        cleaned = cleaned.replace(/\s*data\s*bundle\s*$/i, '');
        cleaned = cleaned.replace(/\s*data\s*$/i, '');
        cleaned = cleaned.replace(/\s*bundles?\s*$/i, '');
        cleaned = cleaned.replace(/\s*airtime\s*$/i, '');
        return cleaned.trim();
    }

    function isDataOperatorName(name) {
        if (!name) { return false; }
        return /\bdata\b/i.test(String(name));
    }

    function formatOperatorLabel(name, type) {
        var base = normalizeOperatorName(name);
        if (!type) { return base; }
        if (type === 'data') { return base + ' Data Bundle'; }
        if (type === 'airtime') { return base; }
        return base;
    }

    function mapRouteToProvider(route) {
        if (route === 'transfer_to_new' || route === 'transfer_to') { return 'transfer'; }
        if (route === 'ding') { return 'ding'; }
        if (route === 'reloadly') { return 'reloadly'; }
        if (route === 'tellus') { return 'tellus'; }
        return '';
    }

    function fetchProviders() {
        if (!$('#currentProvider').val()) { return; }
        if (!getCountryCode() || !getMobile()) { return; }
        show('#mobileLoader');
        hideRouteError();
        $('#btnFetchProviders').prop('disabled', true).addClass('hide');
        $('#changeNumberLink').addClass('hide');
        $('#mobile').prop('disabled', true);
        resetProducts();
        show('#providerSection');
        updateTabs('choose');
        $('#providerList').empty();
        show('#loadingProviders');

        var provider = $('#currentProvider').val();
        var mobile = encodeURIComponent(getMobile());
        var countryCode = encodeURIComponent(getCountryCode());
        var countryIso = encodeURIComponent(getCountryIso());
        var cacheKey = buildCacheKey([
            provider,
            getMobile(),
            getCountryCode(),
            getCountryIso(),
            $('#reloadlyMode').val() || '',
            $('#transferType').val() || ''
        ]);
        var cachedProviders = getCache(v2Cache.providers, cacheKey);
        if (cachedProviders) {
            hide('#loadingProviders');
            hide('#mobileLoader');
            renderProviderList(cachedProviders, provider);
            $('#changeNumberLink').removeClass('hide');
            return;
        }
        var url = '';

        if (provider === 'ding') {
            url = api_base_url + '/tama-topup-v2/fetch/ding/providers?accountNumber=' + mobile + '&countryCode=' + countryCode + '&countryIso=' + countryIso;
        } else if (provider === 'reloadly') {
            url = api_base_url + '/tama-topup-v2/fetch/reloadly/providers?accountNumber=' + mobile + '&countryCode=' + countryCode + '&countryIsos=' + countryIso;
        } else if (provider === 'tellus') {
            url = api_base_url + '/tama-topup-v2/fetch/tellus/providers?accountNumber=' + mobile + '&countryCode=' + countryCode;
        } else if (provider === 'transfer') {
            url = api_base_url + '/tama-topup-v2/fetch/transfer/providers?accountNumber=' + mobile + '&countryCode=' + countryCode + '&countryIso=' + countryIso;
        }

        $.get(url)
            .done(function (response) {
                var list = response.data || [];
                if (provider === 'reloadly' && list.length && typeof list[0] === 'string' && list[0].indexOf('MNP') !== -1) {
                    hide('#loadingProviders');
                    hide('#mobileLoader');
                    setProvider('transfer');
                    initTransferTypeButtons();
                    fetchProviders();
                    return;
                }
                function finalizeProviderList(finalList) {
                    hide('#loadingProviders');
                    hide('#mobileLoader');
                    setCache(v2Cache.providers, cacheKey, finalList);
                    renderProviderList(finalList, provider);
                    $('#changeNumberLink').removeClass('hide');
                }
                if (provider === 'tellus' && list.length) {
                    enrichTellusProvidersWithReloadlyLogos(list, function (enrichedList) {
                        finalizeProviderList(enrichedList);
                    });
                    return;
                }
                finalizeProviderList(list);
            })
            .fail(function (xhr) {
                hide('#loadingProviders');
                hide('#mobileLoader');
                $('#btnFetchProviders').prop('disabled', false).removeClass('hide');
                $('#mobile').prop('disabled', false);
                var msg = 'Please try again.';
                try {
                    var obj = JSON.parse(xhr.responseText);
                    msg = obj.error && obj.error.message ? obj.error.message : msg;
                } catch (e) {}
                $.alert({ title: 'Info', content: msg });
            });
    }

    function buildProviderMatchKey(name, country) {
        var normalizedName = normalizeName(normalizeOperatorName(name));
        var normalizedCountry = normalizeName(country);
        return normalizedName + '|' + normalizedCountry;
    }

    function stripCountrySuffixFromProviderName(name, country) {
        var normalizedName = normalizeName(normalizeOperatorName(name));
        var normalizedCountry = normalizeName(country);
        if (!normalizedName || !normalizedCountry) {
            return normalizedName;
        }
        if (normalizedName === normalizedCountry) {
            return normalizedName;
        }
        if (normalizedName.slice(-(normalizedCountry.length + 1)) === (' ' + normalizedCountry)) {
            return normalizeName(normalizedName.slice(0, -(normalizedCountry.length + 1)));
        }
        return normalizedName;
    }

    function providerNamesLikelyMatch(leftName, leftCountry, rightName, rightCountry) {
        var leftBase = stripCountrySuffixFromProviderName(leftName, leftCountry || rightCountry);
        var rightBase = stripCountrySuffixFromProviderName(rightName, rightCountry || leftCountry);
        if (!leftBase || !rightBase) {
            return false;
        }
        if (leftBase === rightBase) {
            return true;
        }
        return leftBase.indexOf(rightBase) === 0 || rightBase.indexOf(leftBase) === 0;
    }

    function getReloadlyProviderList(onDone, onFail) {
        var cacheKey = buildCacheKey(['reloadly', 'providers', getMobile(), getCountryCode(), getCountryIso()]);
        var cachedProviders = getCache(v2Cache.providers, cacheKey);
        if (cachedProviders) {
            onDone(cachedProviders);
            return;
        }
        var url = api_base_url + '/tama-topup-v2/fetch/reloadly/providers?accountNumber=' + encodeURIComponent(getMobile())
            + '&countryCode=' + encodeURIComponent(getCountryCode())
            + '&countryIsos=' + encodeURIComponent(getCountryIso());
        $.get(url)
            .done(function (response) {
                var list = response.data || [];
                setCache(v2Cache.providers, cacheKey, list);
                onDone(list);
            })
            .fail(function () {
                if (onFail) { onFail(); }
            });
    }

    function getTellusProviderList(onDone, onFail) {
        var cacheKey = buildCacheKey(['tellus', 'providers', getMobile(), getCountryCode()]);
        var cachedProviders = getCache(v2Cache.providers, cacheKey);
        if (cachedProviders) {
            onDone(cachedProviders);
            return;
        }
        var url = api_base_url + '/tama-topup-v2/fetch/tellus/providers?accountNumber=' + encodeURIComponent(getMobile())
            + '&countryCode=' + encodeURIComponent(getCountryCode());
        $.get(url)
            .done(function (response) {
                var list = response.data || [];
                if (!list.length) {
                    setCache(v2Cache.providers, cacheKey, list);
                    onDone(list);
                    return;
                }
                enrichTellusProvidersWithReloadlyLogos(list, function (enrichedList) {
                    setCache(v2Cache.providers, cacheKey, enrichedList);
                    onDone(enrichedList);
                });
            })
            .fail(function () {
                if (onFail) { onFail(); }
            });
    }

    function enrichTellusProvidersWithReloadlyLogos(tellusList, onDone) {
        getReloadlyProviderList(function (reloadlyList) {
            var byKey = {};
            var byCode = {};
            $.each(reloadlyList || [], function (_, item) {
                var logo = item.logo || '';
                if (!logo) { return; }
                var key = buildProviderMatchKey(item.name || item.operator || '', item.country || '');
                if (key) { byKey[key] = logo; }
                if (item.provider_code) { byCode[String(item.provider_code)] = logo; }
            });
            var enriched = $.map(tellusList || [], function (item) {
                var copy = $.extend({}, item);
                if (copy.logo) {
                    delete copy.logo;
                }
                var key = buildProviderMatchKey(copy.name || copy.operator || '', copy.country || '');
                var logo = '';
                if (copy.provider_code && byCode[String(copy.provider_code)]) {
                    logo = byCode[String(copy.provider_code)];
                } else if (key && byKey[key]) {
                    logo = byKey[key];
                } else {
                    $.each(reloadlyList || [], function (_, reloadlyItem) {
                        var reloadlyLogo = reloadlyItem.logo || '';
                        if (!reloadlyLogo) { return; }
                        var sameCountry = !copy.country || !reloadlyItem.country || normalizeName(copy.country) === normalizeName(reloadlyItem.country);
                        if (!sameCountry) { return; }
                        if (providerNamesLikelyMatch(copy.name || copy.operator || '', copy.country || '', reloadlyItem.name || reloadlyItem.operator || '', reloadlyItem.country || '')) {
                            logo = reloadlyLogo;
                            return false;
                        }
                    });
                }
                if (logo) {
                    copy.logo = logo;
                }
                return copy;
            });
            onDone(enriched);
        }, function () {
            var stripped = $.map(tellusList || [], function (item) {
                var copy = $.extend({}, item);
                if (copy.logo) {
                    delete copy.logo;
                }
                return copy;
            });
            onDone(stripped);
        });
    }

    function renderProviderList(providers, provider) {
        $('#providerList').empty();
        if (!providers || providers.length === 0) {
            $('#providerList').append('<li class="tama-v2-empty">No providers found.</li>');
            return;
        }
        if (provider === 'tellus') {
            renderTellusProviderList(providers);
            return;
        }
        if (provider === 'transfer') {
            renderTransferProviderGrid(providers);
            return;
        }
        if (provider === 'ding') {
            providers = providers.slice().sort(function (a, b) {
                var aName = (a.name || '').toLowerCase();
                var bName = (b.name || '').toLowerCase();
                var aIsData = aName.indexOf('data') !== -1;
                var bIsData = bName.indexOf('data') !== -1;
                if (aIsData === bIsData) { return 0; }
                return aIsData ? 1 : -1;
            });
        }
        $.each(providers, function (idx, value) {
            var rawName = value.name || value.operator || value.provider_code;
            var name = normalizeOperatorName(rawName);
            var country = value.country || '';
            var code = value.provider_code || '';
            var logo = value.logo || '';
            var displayName = name;
            if (provider === 'ding' && isDataOperatorName(rawName)) {
                displayName = formatOperatorLabel(name, 'data');
            }
            var safeName = escapeHtml(displayName);
            var safeCountry = escapeHtml(country);
            var safeLogo = escapeHtml(logo);
            var safeInitial = escapeHtml((name || '').charAt(0));
            if (!logo && provider === 'ding') {
                var flag = code.replace(value.country_iso || '', '');
                logo = 'https://imagerepo.ding.com/logo/' + flag + '.svg';
                safeLogo = escapeHtml(logo);
            }
            if (!logo && provider === 'transfer') {
                logo = 'https://operator-logo.dtone.com/logo-' + code + '-1.jpg';
                safeLogo = escapeHtml(logo);
            }
            if (!logo && provider === 'tellus') {
                logo = 'https://operator-logo.dtone.com/logo-' + code + '-1.jpg';
                safeLogo = escapeHtml(logo);
            }
            var li = $('<li class="tama-v2-provider"></li>');
            var btn = $('<button type="button" class="tama-v2-provider-card"></button>');
            var logoHtml = safeLogo ? '<img src="' + safeLogo + '" alt="' + safeName + '">' : '<div class="tama-v2-provider-fallback">' + safeInitial + '</div>';
            btn.append('<div class="tama-v2-provider-logo">' + logoHtml + '</div>');
            btn.append('<div class="tama-v2-provider-text"><div class="tama-v2-provider-name">' + safeName + '</div><div class="tama-v2-provider-country">' + safeCountry + '</div></div>');
            btn.data('provider', provider);
            btn.data('code', code);
            btn.attr('data-code', code);
            btn.data('name', name);
            btn.data('country', country);
            btn.data('country_iso', value.country_iso || '');
            btn.data('logo', logo);
            if (provider === 'reloadly') {
                var listMode = $('#reloadlyMode').val() === 'data' ? 'data' : 'airtime';
                btn.data('reloadlyMode', listMode);
            }
            btn.on('click', function () {
                $('#providerList .tama-v2-provider').removeClass('is-active');
                $(this).closest('.tama-v2-provider').addClass('is-active');
                handleProviderSelect($(this));
            });
            li.append(btn);
            $('#providerList').append(li);
            if (provider === 'reloadly' && $('#reloadlyMode').val() !== 'data') {
                appendReloadlyDataEntryForProvider(value, logo);
            }
        });
        if (providers.length === 1) {
            $('#providerList .tama-v2-provider button').first().click();
        }
    }

    function renderTellusProviderList(providers) {
        var types = [
            { value: 'AIRTIME' },
            { value: 'DATA' }
        ];
        $.each(providers, function (idx, value) {
            var name = normalizeOperatorName(value.name || value.operator || value.provider_code);
            var country = value.country || '';
            var code = value.provider_code || '';
            var logo = value.logo || '';
            var safeCountry = escapeHtml(country);
            var safeLogo = escapeHtml(logo);
            var safeInitial = escapeHtml((name || '').charAt(0));
            $.each(types, function (_, typeCfg) {
                var li = $('<li class="tama-v2-provider"></li>');
                var btn = $('<button type="button" class="tama-v2-provider-card"></button>');
                var logoHtml = safeLogo ? '<img src="' + safeLogo + '" alt="' + escapeHtml(name) + '">' : '<div class="tama-v2-provider-fallback">' + safeInitial + '</div>';
                var displayName = typeCfg.value === 'DATA' ? formatOperatorLabel(name, 'data') : name;
                var displayCountry = typeCfg.value === 'DATA' ? 'Data bundle' : country;
                btn.append('<div class="tama-v2-provider-logo">' + logoHtml + '</div>');
                btn.append('<div class="tama-v2-provider-text"><div class="tama-v2-provider-name">' + escapeHtml(displayName) + '</div><div class="tama-v2-provider-country">' + escapeHtml(displayCountry) + '</div></div>');
                btn.data('provider', 'tellus');
                btn.data('code', code);
                btn.attr('data-code', code);
                btn.data('name', name);
                btn.data('country', country);
                btn.data('country_iso', value.country_iso || '');
                btn.data('logo', logo);
                btn.data('tellusType', typeCfg.value);
                btn.on('click', function () {
                    $('#providerList .tama-v2-provider').removeClass('is-active');
                    $(this).closest('.tama-v2-provider').addClass('is-active');
                    handleProviderSelect($(this));
                });
                li.append(btn);
                $('#providerList').append(li);
            });
        });
        $('#providerList .tama-v2-provider button').first().click();
    }

    function renderTransferProviderGrid(providers) {
        var hideAirtimeCountries = [321];
        var code = parseInt(getCountryCode(), 10);
        var shouldHideAirtime = !isNaN(code) && hideAirtimeCountries.indexOf(code) !== -1;
        var types = [
            { label: 'Airtime', type: 'RANGED_VALUE_RECHARGE', hide: shouldHideAirtime },
            { label: 'Data Bundle', type: 'FIXED_VALUE_RECHARGE', hide: false }
        ];
        $.each(providers, function (idx, value) {
            var name = value.name || value.operator || value.provider_code;
            name = normalizeOperatorName(name);
            var country = value.country || '';
            var code = value.provider_code || '';
            var logo = value.logo || '';
            if (!logo) {
                logo = 'https://operator-logo.dtone.com/logo-' + code + '-1.jpg';
            }
            var safeName = escapeHtml(name);
            var safeCountry = escapeHtml(country);
            var safeLogo = escapeHtml(logo);
            var safeInitial = escapeHtml((name || '').charAt(0));
            $.each(types, function (tIdx, item) {
                if (item.hide) { return; }
                var li = $('<li class="tama-v2-provider"></li>');
                var btn = $('<button type="button" class="tama-v2-provider-card"></button>');
                var logoHtml = safeLogo ? '<img src="' + safeLogo + '" alt="' + safeName + '">' : '<div class="tama-v2-provider-fallback">' + safeInitial + '</div>';
                btn.append('<div class="tama-v2-provider-logo">' + logoHtml + '</div>');
                var labelType = item.type === 'FIXED_VALUE_RECHARGE' ? 'data' : 'airtime';
                var labelText = formatOperatorLabel(name, labelType);
                btn.append('<div class="tama-v2-provider-text"><div class="tama-v2-provider-name">' + escapeHtml(labelText) + '</div><div class="tama-v2-provider-country">' + safeCountry + '</div></div>');
                btn.data('provider', 'transfer');
                btn.data('code', code);
                btn.data('name', name);
                btn.data('country', country);
                btn.data('country_iso', value.country_iso || '');
                btn.data('type', item.type);
                btn.data('logo', logo);
                btn.on('click', function () {
                    $('#providerList .tama-v2-provider').removeClass('is-active');
                    $(this).closest('.tama-v2-provider').addClass('is-active');
                    handleTransferCardSelect($(this));
                });
                li.append(btn);
                $('#providerList').append(li);
            });
        });
        if (providers && providers.length) {
            $('#providerList .tama-v2-provider button').first().click();
        }
    }

    function handleTransferCardSelect($btn) {
        var providerCode = $btn.data('code');
        var providerName = $btn.data('name');
        var providerCountry = $btn.data('country');
        var countryIso = $btn.data('country_iso') || getCountryIso();
        var type = $btn.data('type');
        setCurrentProviderLogo($btn.data('logo'));

        $('#providerName').val(providerName);
        $('#providerCountry').val(providerCountry);
        $('#transferType').val(type);
        $('#transfer_operator_id').val(providerCode);

        resetProducts();
        show('#productSection');
        show('#loadingProducts');
        updateTabs('amount');
        scrollToSection('#productSection');
        fetchTransferProducts(providerCode, countryIso, type);
    }

    function appendReloadlyDataEntryForProvider(value, logo) {
        var name = value.name || value.operator || value.provider_code;
        name = normalizeOperatorName(name);
        var code = value.provider_code || '';
        var country = value.country || '';
        var countryIso = value.country_iso || '';
        var safeName = escapeHtml(name);
        var safeLogo = escapeHtml(logo);
        var safeInitial = escapeHtml((name || '').charAt(0));
        var li = $('<li class="tama-v2-provider"></li>');
        var btn = $('<button type="button" class="tama-v2-provider-card is-action js-reloadly-data" data-code="' + code + '"></button>');
        var logoHtml = safeLogo ? '<img src="' + safeLogo + '" alt="' + safeName + '">' : '<div class="tama-v2-provider-fallback">' + safeInitial + '</div>';
        btn.append('<div class="tama-v2-provider-logo tama-v2-provider-logo--action">' + logoHtml + '</div>');
        var labelText = formatOperatorLabel(name, 'data');
        btn.append('<div class="tama-v2-provider-text"><div class="tama-v2-provider-name">' + escapeHtml(labelText) + '</div><div class="tama-v2-provider-country">Data bundle</div></div>');
        btn.data('name', name);
        btn.data('country', country);
        btn.data('country_iso', countryIso);
        btn.data('logo', logo);
        li.append(btn);
        $('#providerList').append(li);
    }

    function prependReloadlyBackEntry() {
        var li = $('<li class="tama-v2-provider"></li>');
        var btn = $('<button type="button" class="tama-v2-provider-card is-action js-reloadly-airtime"></button>');
        btn.append('<div class="tama-v2-provider-logo tama-v2-provider-logo--action"><i class="fa fa-arrow-left"></i></div>');
        btn.append('<div class="tama-v2-provider-text"><div class="tama-v2-provider-name">Back to operators</div><div class="tama-v2-provider-country">Airtime list</div></div>');
        li.append(btn);
        $('#providerList').prepend(li);
    }

    function handleProviderSelect($btn) {
        var provider = $btn.data('provider');
        var providerCode = $btn.data('code');
        var providerName = $btn.data('name');
        var providerCountry = $btn.data('country');
        var countryIso = $btn.data('country_iso') || getCountryIso();
        currentTellusType = provider === 'tellus' ? String($btn.data('tellusType') || '').toUpperCase() : '';
        setCurrentProviderLogo($btn.data('logo'));

        $('#providerName').val(providerName);
        $('#providerCountry').val(providerCountry);

        resetProducts();
        show('#productSection');
        show('#loadingProducts');
        updateTabs('amount');
        scrollToSection('#productSection');

        if (provider === 'ding') {
            fetchDingProducts(providerCode, countryIso);
        } else if (provider === 'reloadly') {
            var listMode = $btn.data('reloadlyMode');
            if (listMode === 'airtime') {
                $('#reloadlyMode').val('airtime');
            } else if (listMode === 'data') {
                $('#reloadlyMode').val('data');
            }
            if ($('#reloadlyMode').val() === 'data') {
                fetchReloadlyProductsById(providerCode);
            } else {
                fetchReloadlyProducts(providerCode, countryIso);
            }
        } else if (provider === 'tellus') {
            if (currentTellusType === 'DATA') {
                var mappedRoute = mapRouteToProvider($('#dataBundleRoute').val());
                if (mappedRoute === 'transfer') {
                    fetchTransferDataForProvider(providerName, providerCountry);
                } else if (mappedRoute === 'reloadly') {
                    fetchReloadlyDataForProvider(providerName, providerCountry, countryIso);
                } else if (mappedRoute === 'ding') {
                    fetchDingDataForProvider(providerName, providerCountry, countryIso);
                } else {
                    fetchTellusProducts(providerCode, countryIso);
                }
            } else {
                fetchTellusProducts(providerCode, countryIso);
            }
        } else if (provider === 'transfer') {
            var type = $('#transferType').val();
            if (!type) {
                hide('#loadingProducts');
                $.alert({ title: 'Info', content: 'Select Airtime or Data first.' });
                return;
            }
            fetchTransferProducts(providerCode, countryIso, type);
        }
    }

    function fetchDingProducts(providerCode, countryIso) {
        var url = api_base_url + '/tama-topup-v2/fetch/ding/products?countryIso=' + encodeURIComponent(countryIso)
            + '&countryCode=' + encodeURIComponent(getCountryCode())
            + '&region=' + encodeURIComponent(countryIso)
            + '&providerCode=' + encodeURIComponent(providerCode)
            + '&accountNumber=' + encodeURIComponent(getMobile());
        var cacheKey = buildCacheKey(['ding', providerCode, countryIso, getCountryCode(), getMobile()]);
        var cachedResponse = getCache(v2Cache.products, cacheKey);
        if (cachedResponse) {
            hide('#loadingProducts');
            $('#productList').empty();
            $('#productGrid').empty();
            if (cachedResponse.data && cachedResponse.data.is_denominated === true) {
                hide('#productList');
                show('#productGrid');
                renderDingGrid(cachedResponse.data.products || []);
                hide('#summarySection');
            } else if (cachedResponse.data && cachedResponse.data.products && cachedResponse.data.products.length) {
                setupRange(cachedResponse.data.products[0], 'ding');
            }
            return;
        }
        $.get(url)
            .done(function (response) {
                hide('#loadingProducts');
                $('#productList').empty();
                $('#productGrid').empty();
                setCache(v2Cache.products, cacheKey, response);
                if (response.data && response.data.is_denominated === true) {
                    hide('#productList');
                    show('#productGrid');
                    renderDingGrid(response.data.products || []);
                    hide('#summarySection');
                } else if (response.data && response.data.products && response.data.products.length) {
                    setupRange(response.data.products[0], 'ding');
                }
            })
            .fail(function () {
                hide('#loadingProducts');
                $.alert({ title: 'Info', content: 'Unable to load products.' });
            });
    }

    function renderDingList(products) {
        $.each(products, function (key, value) {
            var li = $('<li class="tama-v2-product"></li>');
            var a = $('<a href="javascript:void(0);"></a>');
            var descText = value.display_text || '';
            var fullDesc = value.description || descText;
            var amountText = formatDingPrice(value.maxSendAmountFormatted);
            var receiveText = value.maxReceiveAmountFormatted || '';
            var detailsText = value.description || value.name || descText;
            var validityText = value.validity || '';
            var logoHtml = getProviderLogoHtml($('#providerName').val());
            var front = $('<div class="tama-v2-product-face tama-v2-product-front"></div>');
            front.append('<div class="tama-v2-product-logo">' + logoHtml + '</div>');
            front.append('<div class="tama-v2-product-text"><div class="price">' + escapeHtml(amountText) + '</div></div>');
            var back = $('<div class="tama-v2-product-face tama-v2-product-back"></div>');
            back.append('<div class="tama-v2-product-logo tama-v2-product-logo--back">' + logoHtml + '</div>');
            back.append('<div class="tama-v2-product-back-body">' + buildCommonBackBody(amountText, receiveText, validityText, detailsText) + '</div>');
            var inner = $('<div class="tama-v2-product-inner"></div>');
            inner.append(front).append(back);
            a.append($('<div class="tama-v2-product-card"></div>').append(inner));
            a.on('click', function () {
                $('#productList .tama-v2-product').removeClass('is-active');
                li.addClass('is-active');
                setDingSelection(value);
                hide('#summarySection');
                clearReviewModal();
                updateTabs('amount');
                triggerReviewOrder();
            });
            li.append(a);
            $('#productList').append(li);
        });
        if (products && products.length === 1) {
            $('#productList .tama-v2-product a').first().click();
            scrollToSection('#reviewOrderBtn');
            return;
        }
        if (products && products.length) {
            scrollToSection('#productSection');
        }
    }

    function renderDingGrid(products) {
        $.each(products, function (key, value) {
            var li = $('<li class="tama-v2-product"></li>');
            var a = $('<a href="javascript:void(0);"></a>');
            var descText = value.display_text || '';
            var fullDesc = value.description || descText;
            var amountText = formatDingPrice(value.maxSendAmountFormatted);
            var receiveText = value.maxReceiveAmountFormatted || '';
            var detailsText = value.description || value.name || descText;
            var validityText = value.validity || '';
            var logoHtml = getProviderLogoHtml($('#providerName').val());
            var front = $('<div class="tama-v2-product-face tama-v2-product-front"></div>');
            front.append('<div class="tama-v2-product-logo">' + logoHtml + '</div>');
            front.append('<div class="tama-v2-product-text"><div class="price">' + escapeHtml(amountText) + '</div></div>');
            var back = $('<div class="tama-v2-product-face tama-v2-product-back"></div>');
            back.append('<div class="tama-v2-product-logo tama-v2-product-logo--back">' + logoHtml + '</div>');
            back.append('<div class="tama-v2-product-back-body">' + buildCommonBackBody(amountText, receiveText, validityText, detailsText) + '</div>');
            var inner = $('<div class="tama-v2-product-inner"></div>');
            inner.append(front).append(back);
            a.append($('<div class="tama-v2-product-card"></div>').append(inner));
            a.on('click', function () {
                $('#productGrid .tama-v2-product').removeClass('is-active');
                li.addClass('is-active');
                setDingSelection(value);
                hide('#summarySection');
                clearReviewModal();
                updateTabs('amount');
                triggerReviewOrder();
            });
            li.append(a);
            $('#productGrid').append(li);
        });
        if (products && products.length === 1) {
            $('#productGrid .tama-v2-product a').first().click();
            scrollToSection('#reviewOrderBtn');
            return;
        }
        if (products && products.length) {
            scrollToSection('#productSection');
        }
    }

    function setDingSelection(value) {
        $('#ding_skuCode').val(value.sku_code);
        $('#ding_euro_amount').val(value.maxSendValue);
        $('#ding_euro_amount_formatted').val(value.maxSendAmountFormatted);
        $('#ding_dest_amount').val(value.maxReceiveValue);
        $('#ding_dest_amount_formatted').val(value.maxReceiveAmountFormatted);
        $('#ding_commissionRate').val(value.commission_rate);
        $('#ding_SendValue').val(value.maxSendValue);
        $('#ding_SendValueOriginal').val(value.sendValueOriginal);
        $('#ding_SendCurrencyIso').val(value.sendCurrencyIso);
        $('#ding_UatNumber').val(value.uat_number);
        show('#summarySection');
    }

    function setupRange(product, provider) {
        hide('#productSection');
        show('#rangeSection');
        hide('#summarySection');
        var amount_between = between_trans + ' ' + product.minSendAmountFormatted + ' - ' + product.maxSendAmountFormatted;
        setRangeMeta(product.minSendAmountFormatted, product.maxSendAmountFormatted, product.sendCurrencyIso || '\u20AC');
        $('#betweenDenomination').text(amount_between);
        $('#range_amount')
            .attr('placeholder', amount_between)
            .attr('title', amount_between)
            .attr('min', product.minSendValue)
            .attr('max', product.maxSendValue)
            .val('');
        clearRangeError();
        $('#amountReceivedDiv').addClass('hide');
        if (provider === 'ding') {
            $('#ding_skuCode').val(product.sku_code);
            $('#ding_commissionRate').val(product.commission_rate);
            $('#ding_SendCurrencyIso').val(product.sendCurrencyIso);
            $('#ding_UatNumber').val(product.uat_number);
            $('#range_amount').off('input blur keyup').on('input keyup', function () {
                clearRangeError();
                hide('#summarySection');
                hide('#amountReceivedDiv');
                var currentVal = parseFloat($(this).val());
                var min = parseFloat($(this).attr('min'));
                var max = parseFloat($(this).attr('max'));
                if (!currentVal || currentVal < min || currentVal > max) {
                    return;
                }
                scheduleDingEstimate(currentVal, product.provider_code);
            }).on('blur', function () {
                var currentVal = parseFloat($(this).val());
                var min = parseFloat($(this).attr('min'));
                var max = parseFloat($(this).attr('max'));
                if (!currentVal || currentVal < min || currentVal > max) {
                    showRangeError('Enter amount between ' + product.minSendAmountFormatted + ' and ' + product.maxSendAmountFormatted + '.');
                    return;
                }
                getDingEstimate(currentVal, product.provider_code);
            });
        }
    }

    function getDingEstimate(sendAmount, providerCode, skipScroll) {
        hide('#amountReceivedDiv');
        var cacheKey = buildCacheKey(['ding', 'estimate', sendAmount, providerCode, getCountryCode()]);
        var cachedEstimate = getCache(v2Cache.estimates, cacheKey);
        if (cachedEstimate) {
            show('#amountReceivedDiv');
            $('#amountReceived').text(cachedEstimate.data.formattedAmount);
            $('#ding_SendValue').val(cachedEstimate.data.sentAmount);
            $('#ding_SendValueOriginal').val(cachedEstimate.data.sendValueOriginal);
            $('#ding_SendCurrencyIso').val(cachedEstimate.data.SendCurrencyIso);
            $('#ding_euro_amount').val(cachedEstimate.data.sentAmount);
            $('#ding_euro_amount_formatted').val(cachedEstimate.data.sentAmountFormatted);
            $('#ding_dest_amount').val(cachedEstimate.data.amount);
            $('#ding_dest_amount_formatted').val(cachedEstimate.data.formattedAmount);
            show('#summarySection');
            if (!skipScroll) {
                scrollToSection('#reviewOrderBtn');
            }
            return;
        }
        var url = api_base_url + '/tama-topup-v2/fetch/ding/estimate?sendAmount=' + encodeURIComponent(sendAmount)
            + '&skuCode=' + encodeURIComponent($('#ding_skuCode').val())
            + '&countryCode=' + encodeURIComponent(getCountryCode())
            + '&providerCode=' + encodeURIComponent(providerCode);
        $.get(url)
            .done(function (response) {
                setCache(v2Cache.estimates, cacheKey, response);
                show('#amountReceivedDiv');
                $('#amountReceived').text(response.data.formattedAmount);
                $('#ding_SendValue').val(response.data.sentAmount);
                $('#ding_SendValueOriginal').val(response.data.sendValueOriginal);
                $('#ding_SendCurrencyIso').val(response.data.SendCurrencyIso);
                $('#ding_euro_amount').val(response.data.sentAmount);
                $('#ding_euro_amount_formatted').val(response.data.sentAmountFormatted);
                $('#ding_dest_amount').val(response.data.amount);
                $('#ding_dest_amount_formatted').val(response.data.formattedAmount);
                show('#summarySection');
                if (!skipScroll) {
                    scrollToSection('#reviewOrderBtn');
                }
            })
            .fail(function () {
                $.alert({ title: 'Info', content: 'Unable to estimate amount.' });
            });
    }

    function fetchReloadlyProducts(providerCode, countryIso) {
        var url = api_base_url + '/tama-topup-v2/fetch/reloadly/products?accountNumber=' + encodeURIComponent(getMobile())
            + '&countryCode=' + encodeURIComponent(getCountryCode())
            + '&countryIsos=' + encodeURIComponent(countryIso);
        var cacheKey = buildCacheKey(['reloadly', 'airtime', providerCode, countryIso, getCountryCode(), getMobile()]);
        var cachedResponse = getCache(v2Cache.products, cacheKey);
        if (cachedResponse) {
            hide('#loadingProducts');
            $('#productList').empty();
            $('#productGrid').empty();
            if (cachedResponse.is_denominated === true) {
                hide('#productList');
                show('#productGrid');
                renderReloadlyGrid(cachedResponse.products || []);
                hide('#summarySection');
            } else if (cachedResponse.products && cachedResponse.products.length) {
                setupReloadlyRange(cachedResponse.products[0]);
            }
            return;
        }
        $.get(url)
            .done(function (response) {
                hide('#loadingProducts');
                $('#productList').empty();
                $('#productGrid').empty();
                setCache(v2Cache.products, cacheKey, response);
                if (response.is_denominated === true) {
                    hide('#productList');
                    show('#productGrid');
                    renderReloadlyGrid(response.products || []);
                    hide('#summarySection');
                } else if (response.products && response.products.length) {
                    setupReloadlyRange(response.products[0]);
                }
            })
            .fail(function () {
                hide('#loadingProducts');
                $.alert({ title: 'Info', content: 'Unable to load products.' });
            });
    }

    function fetchReloadlyData() {
        var url = api_base_url + '/tama-topup-v2/fetch/reloadly/data?countryCode=' + encodeURIComponent(getCountryIso());
        show('#providerSection');
        $('#providerList').empty();
        show('#loadingProviders');
        var cacheKey = buildCacheKey(['reloadly', 'data', getCountryIso()]);
        var cachedProviders = getCache(v2Cache.providers, cacheKey);
        if (cachedProviders) {
            hide('#loadingProviders');
            renderProviderList(cachedProviders, 'reloadly');
            prependReloadlyBackEntry();
            if (pendingReloadlyDataCode) {
                $('#providerList .tama-v2-provider-card[data-code="' + pendingReloadlyDataCode + '"]').first().click();
                pendingReloadlyDataCode = '';
            }
            return;
        }
        $.get(url)
            .done(function (response) {
                hide('#loadingProviders');
                var list = response.data || [];
                setCache(v2Cache.providers, cacheKey, list);
                renderProviderList(list, 'reloadly');
                prependReloadlyBackEntry();
                if (pendingReloadlyDataCode) {
                    $('#providerList .tama-v2-provider-card[data-code="' + pendingReloadlyDataCode + '"]').first().click();
                    pendingReloadlyDataCode = '';
                }
            })
            .fail(function () {
                hide('#loadingProviders');
                $.alert({ title: 'Info', content: 'Unable to load data bundles.' });
            });
    }

    function getReloadlyDataList(countryIso, onDone, onFail) {
        var cacheKey = buildCacheKey(['reloadly', 'data', countryIso]);
        var cachedProviders = getCache(v2Cache.providers, cacheKey);
        if (cachedProviders) {
            onDone(cachedProviders);
            return;
        }
        var url = api_base_url + '/tama-topup-v2/fetch/reloadly/data?countryCode=' + encodeURIComponent(countryIso);
        $.get(url)
            .done(function (response) {
                var list = response.data || [];
                setCache(v2Cache.providers, cacheKey, list);
                onDone(list);
            })
            .fail(function () {
                if (onFail) { onFail(); }
            });
    }

    function getTransferProviderList(onDone, onFail) {
        var cacheKey = buildCacheKey(['transfer', getMobile(), getCountryCode(), getCountryIso()]);
        var cachedProviders = getCache(v2Cache.providers, cacheKey);
        if (cachedProviders) {
            onDone(cachedProviders);
            return;
        }
        var url = api_base_url + '/tama-topup-v2/fetch/transfer/providers?accountNumber=' + encodeURIComponent(getMobile())
            + '&countryCode=' + encodeURIComponent(getCountryCode())
            + '&countryIso=' + encodeURIComponent(getCountryIso());
        $.get(url)
            .done(function (response) {
                var list = response.data || [];
                setCache(v2Cache.providers, cacheKey, list);
                onDone(list);
            })
            .fail(function () {
                if (onFail) { onFail(); }
            });
    }

    function getDingProviderList(countryIso, onDone, onFail) {
        var iso = countryIso || getCountryIso();
        var cacheKey = buildCacheKey(['ding', 'providers', getMobile(), getCountryCode(), iso]);
        var cachedProviders = getCache(v2Cache.providers, cacheKey);
        if (cachedProviders) {
            onDone(cachedProviders);
            return;
        }
        var url = api_base_url + '/tama-topup-v2/fetch/ding/providers?accountNumber=' + encodeURIComponent(getMobile())
            + '&countryCode=' + encodeURIComponent(getCountryCode())
            + '&countryIso=' + encodeURIComponent(iso);
        $.get(url)
            .done(function (response) {
                var list = response.data || [];
                setCache(v2Cache.providers, cacheKey, list);
                onDone(list);
            })
            .fail(function () {
                if (onFail) { onFail(); }
            });
    }

    function fetchDingDataForProvider(providerName, providerCountry, countryIso) {
        getDingProviderList(countryIso, function (list) {
            var match = null;
            var baseName = normalizeOperatorName(providerName);
            var nameKey = normalizeName(baseName);
            var countryKey = normalizeName(providerCountry);
            for (var i = 0; i < list.length; i++) {
                var itemName = normalizeName(normalizeOperatorName(list[i].name));
                var itemCountry = normalizeName(list[i].country);
                var rawName = normalizeName(list[i].name);
                var hasDataTag = rawName.indexOf('data') !== -1 || rawName.indexOf('bundle') !== -1;
                if (nameKey && itemName === nameKey && hasDataTag && (!countryKey || itemCountry === countryKey)) {
                    match = list[i];
                    break;
                }
            }
            if (!match && nameKey) {
                for (var j = 0; j < list.length; j++) {
                    var rawNameAlt = normalizeName(list[j].name);
                    var itemCountryAlt = normalizeName(list[j].country);
                    var hasDataTagAlt = rawNameAlt.indexOf('data') !== -1 || rawNameAlt.indexOf('bundle') !== -1;
                    if (hasDataTagAlt && rawNameAlt.indexOf(nameKey) !== -1 && (!countryKey || itemCountryAlt === countryKey)) {
                        match = list[j];
                        break;
                    }
                }
            }
            if (!match && list.length === 1) {
                match = list[0];
            }
            if (match && match.provider_code) {
                fetchDingProducts(match.provider_code, match.country_iso || countryIso || getCountryIso());
            } else {
                hide('#loadingProducts');
                $.alert({ title: 'Info', content: 'Unable to load data bundles.' });
            }
        }, function () {
            hide('#loadingProducts');
            $.alert({ title: 'Info', content: 'Unable to load data bundles.' });
        });
    }

    function fetchTransferDataForProvider(providerName, providerCountry) {
        getTransferProviderList(function (list) {
            var match = null;
            var nameKey = normalizeName(providerName);
            var countryKey = normalizeName(providerCountry);
            for (var i = 0; i < list.length; i++) {
                var itemName = normalizeName(list[i].name);
                var itemCountry = normalizeName(list[i].country);
                if (nameKey && itemName === nameKey && (!countryKey || itemCountry === countryKey)) {
                    match = list[i];
                    break;
                }
            }
            if (!match && list.length === 1) {
                match = list[0];
            }
            if (match && match.provider_code) {
                fetchTransferProducts(match.provider_code, match.country_iso || getCountryIso(), 'FIXED_VALUE_RECHARGE');
            } else {
                hide('#loadingProducts');
                $.alert({ title: 'Info', content: 'Unable to load data bundles.' });
            }
        }, function () {
            hide('#loadingProducts');
            $.alert({ title: 'Info', content: 'Unable to load data bundles.' });
        });
    }

    function fallbackTellusDataToTransfer(providerName, providerCountry) {
        currentTellusType = '';
        hideTellusCategoryFilters();
        $('#productList').empty();
        $('#productGrid').empty();
        show('#loadingProducts');
        fetchTransferDataForProvider(providerName, providerCountry);
    }

    function fetchTellusDataForProvider(providerName, providerCountry, countryIso) {
        currentTellusType = 'DATA';
        getTellusProviderList(function (list) {
            var match = null;
            var baseName = normalizeOperatorName(providerName);
            var nameKey = normalizeName(baseName);
            var countryKey = normalizeName(providerCountry);
            for (var i = 0; i < list.length; i++) {
                var itemName = normalizeName(normalizeOperatorName(list[i].name || list[i].operator || ''));
                var itemCountry = normalizeName(list[i].country);
                if (nameKey && itemName === nameKey && (!countryKey || itemCountry === countryKey)) {
                    match = list[i];
                    break;
                }
            }
            if (!match) {
                for (var j = 0; j < list.length; j++) {
                    var sameCountry = !countryKey || normalizeName(list[j].country) === countryKey;
                    if (!sameCountry) { continue; }
                    if (providerNamesLikelyMatch(baseName, providerCountry, list[j].name || list[j].operator || '', list[j].country || '')) {
                        match = list[j];
                        break;
                    }
                }
            }
            if (!match && list.length === 1) {
                match = list[0];
            }
            if (match && match.provider_code) {
                $('#providerName').val(normalizeOperatorName(match.name || match.operator || providerName));
                $('#providerCountry').val(match.country || providerCountry || '');
                if (match.logo) {
                    setCurrentProviderLogo(match.logo);
                }
                fetchTellusProducts(
                    match.provider_code,
                    match.country_iso || countryIso || getCountryIso(),
                    function () {
                        fallbackTellusDataToTransfer(providerName, providerCountry);
                    },
                    function () {
                        fallbackTellusDataToTransfer(providerName, providerCountry);
                    }
                );
            } else {
                fallbackTellusDataToTransfer(providerName, providerCountry);
            }
        }, function () {
            fallbackTellusDataToTransfer(providerName, providerCountry);
        });
    }

    function normalizeName(value) {
        return (value || '')
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .replace(/[^a-z0-9 ]/g, '')
            .trim();
    }

    function getTransferRangeStep(product) {
        var rawRangeStep = product.rangeStep || product.range_step || product.step || '';
        var rangeStep = parseFloat(rawRangeStep);
        if (rawRangeStep !== '' && !isNaN(rangeStep) && rangeStep > 0) {
            return rangeStep;
        }
        if (isOrangeGuineaTransferProduct(product)) {
            return 0.10;
        }
        return null;
    }

    function isOrangeGuineaTransferProduct(product) {
        var operator = normalizeName(product.operator_name || product.operator || $('#providerName').val() || product.name || '');
        var country = normalizeName(product.country || $('#providerCountry').val() || '');
        var countryIso = normalizeName(product.country_iso || product.countryIso || getCountryIso() || '');

        return operator.indexOf('orange') !== -1
            && country.indexOf('bissau') === -1
            && operator.indexOf('bissau') === -1
            && (countryIso === 'gn' || countryIso === 'gin' || country.indexOf('guinea') !== -1 || operator.indexOf('guinea') !== -1);
    }

    function fetchReloadlyDataForProvider(providerName, providerCountry, countryIso) {
        getReloadlyDataList(countryIso, function (list) {
            var match = null;
            var baseName = normalizeOperatorName(providerName);
            var nameKey = normalizeName(baseName);
            var countryKey = normalizeName(providerCountry);
            for (var i = 0; i < list.length; i++) {
                var itemName = normalizeName(normalizeOperatorName(list[i].name));
                var itemCountry = normalizeName(list[i].country);
                var rawName = normalizeName(list[i].name);
                var hasDataTag = rawName.indexOf('data') !== -1 || rawName.indexOf('bundle') !== -1;
                if (nameKey && itemName === nameKey && hasDataTag && (!countryKey || itemCountry === countryKey)) {
                    match = list[i];
                    break;
                }
            }
            if (!match && nameKey) {
                for (var j = 0; j < list.length; j++) {
                    var rawNameAlt = normalizeName(list[j].name);
                    var itemCountryAlt = normalizeName(list[j].country);
                    var hasDataTagAlt = rawNameAlt.indexOf('data') !== -1 || rawNameAlt.indexOf('bundle') !== -1;
                    if (hasDataTagAlt && rawNameAlt.indexOf(nameKey) !== -1 && (!countryKey || itemCountryAlt === countryKey)) {
                        match = list[j];
                        break;
                    }
                }
            }
            if (!match && list.length === 1) {
                match = list[0];
            }
            if (match && match.provider_code) {
                fetchReloadlyProductsById(match.provider_code);
            } else {
                hide('#loadingProducts');
                $.alert({ title: 'Info', content: 'Unable to load data bundles.' });
            }
        }, function () {
            hide('#loadingProducts');
            $.alert({ title: 'Info', content: 'Unable to load data bundles.' });
        });
    }

    function fetchReloadlyProductsById(operatorId) {
        var url = api_base_url + '/tama-topup-v2/fetch/reloadly/productsID?operator_id=' + encodeURIComponent(operatorId);
        var cacheKey = buildCacheKey(['reloadly', 'data', operatorId]);
        var cachedResponse = getCache(v2Cache.products, cacheKey);
        if (cachedResponse) {
            hide('#loadingProducts');
            $('#productList').empty();
            $('#productGrid').empty();
            if (cachedResponse.is_denominated === true) {
                hide('#productList');
                show('#productGrid');
                renderReloadlyGrid(cachedResponse.products || []);
                hide('#summarySection');
            } else if (cachedResponse.products && cachedResponse.products.length) {
                setupReloadlyRange(cachedResponse.products[0]);
            }
            return;
        }
        $.get(url)
            .done(function (response) {
                hide('#loadingProducts');
                $('#productList').empty();
                $('#productGrid').empty();
                setCache(v2Cache.products, cacheKey, response);
                if (response.is_denominated === true) {
                    hide('#productList');
                    show('#productGrid');
                    renderReloadlyGrid(response.products || []);
                    hide('#summarySection');
                } else if (response.products && response.products.length) {
                    setupReloadlyRange(response.products[0]);
                }
            })
            .fail(function () {
                hide('#loadingProducts');
                $.alert({ title: 'Info', content: 'Unable to load data bundle products.' });
            });
    }

    function renderReloadlyGrid(products) {
        $.each(products, function (key, value) {
            var li = $('<li class="tama-v2-product"></li>');
            var a = $('<a href="javascript:void(0);"></a>');
            var currency = value.sendCurrencyIso || '';
            var priceText = currency ? (currency + value.minSendValue) : ('\u20AC' + value.minSendValue);
            var amountText = priceText;
            var receiveText = value.RecivedCurrencyIso ? (value.RecivedCurrencyIso + ' ' + value.display_text) : value.display_text;
            var detailsText = value.description || value.name || '';
            var validityText = value.validity || '';
            var logoHtml = getProviderLogoHtml($('#providerName').val());
            var front = $('<div class="tama-v2-product-face tama-v2-product-front"></div>');
            front.append('<div class="tama-v2-product-logo">' + logoHtml + '</div>');
            front.append('<div class="tama-v2-product-text"><div class="price">' + escapeHtml(amountText) + '</div></div>');
            var back = $('<div class="tama-v2-product-face tama-v2-product-back"></div>');
            back.append('<div class="tama-v2-product-logo tama-v2-product-logo--back">' + logoHtml + '</div>');
            back.append('<div class="tama-v2-product-back-body">' + buildCommonBackBody(amountText, receiveText, validityText, detailsText) + '</div>');
            var inner = $('<div class="tama-v2-product-inner"></div>');
            inner.append(front).append(back);
            a.append($('<div class="tama-v2-product-card"></div>').append(inner));
            a.on('click', function () {
                $('#productGrid .tama-v2-product').removeClass('is-active');
                li.addClass('is-active');
                setReloadlySelection(value);
                hide('#summarySection');
                clearReviewModal();
                updateTabs('amount');
                triggerReviewOrder();
            });
            li.append(a);
            $('#productGrid').append(li);
        });
        if (products && products.length === 1) {
            $('#productGrid .tama-v2-product a').first().click();
            scrollToSection('#reviewOrderBtn');
            return;
        }
        if (products && products.length) {
            scrollToSection('#productSection');
        }
    }
function setReloadlySelection(value) {
        $('#reloadly_skuCode').val(value.provider_code);
        $('#reloadly_SendValue').val(value.minSendValue);
        $('#reloadly_sendValueOriginal').val(value.sendValueOriginal);
        $('#reloadly_local_amount').val(value.display_text);
        $('#reloadly_description').val(value.description || '');
        $('#reloadly_operator').val($('#providerName').val());
        $('#reloadly_country').val($('#providerCountry').val());
        show('#summarySection');
    }

    function setupReloadlyRange(product) {
        hide('#productSection');
        show('#rangeSection');
        hide('#summarySection');
        var amount_between = between_trans + ' ' + product.minSendValue + ' - ' + product.maxSendValue;
        var receiveCurrency = product.currencyCode || product.sendCurrencyIso || '';
        setRangeMeta(product.minSendValue, product.maxSendValue, product.sendCurrencyIso || receiveCurrency || '\u20AC');
        $('#betweenDenomination').text(amount_between);
        $('#range_amount')
            .attr('placeholder', amount_between)
            .attr('title', amount_between)
            .attr('min', product.minSendValue)
            .attr('max', product.maxSendValue)
            .val('');
        clearRangeError();
        $('#amountReceivedDiv').addClass('hide');
        function updateReloadlyRangeValue(showError, shouldScroll) {
            var currentVal = parseFloat($('#range_amount').val());
            var min = parseFloat($('#range_amount').attr('min'));
            var max = parseFloat($('#range_amount').attr('max'));
            if (!currentVal || currentVal < min || currentVal > max) {
                if (showError) {
                    showRangeError('Enter amount between ' + product.minSendValue + ' and ' + product.maxSendValue + '.');
                }
                hide('#summarySection');
                hide('#amountReceivedDiv');
                return false;
            }
            var exchange = parseFloat(product.exchange_rate || product.fx_rate || 1);
            var percentage = parseFloat(product.percentage || 0);
            var per = (currentVal / 100) * percentage;
            var actual = currentVal - per;
            var local = (actual * exchange).toFixed(2);
            $('#reloadly_SendValue').val(currentVal);
            $('#reloadly_sendValueOriginal').val(actual);
            $('#reloadly_local_amount').val(local);
            $('#reloadly_skuCode').val(product.provider_code);
            $('#reloadly_description').val('');
            $('#reloadly_operator').val($('#providerName').val());
            $('#reloadly_country').val($('#providerCountry').val());
            $('#amountReceived').text((receiveCurrency ? receiveCurrency + ' ' : '') + local);
            show('#amountReceivedDiv');
            show('#summarySection');
            if (shouldScroll) {
                scrollToSection('#reviewOrderBtn');
            }
            return true;
        }
        $('#range_amount').off('input blur keyup').on('input keyup', function () {
            clearRangeError();
            updateReloadlyRangeValue(false, false);
        }).on('blur', function () {
            updateReloadlyRangeValue(true, true);
        });
    }

    function hideTellusCategoryFilters() {
        $('#tellusCategoryFilters').addClass('hide').empty();
        currentTellusGroupKey = '';
    }

    function getTellusGroupMeta(product) {
        var tag = String(product.tags || 'OTHER').toUpperCase();
        var structure = String(product.structure || (product.maxSendValue ? 'RANGE' : 'FIXED')).toUpperCase();
        return {
            key: tag + '|' + structure,
            tag: tag,
            structure: structure
        };
    }

    function getTellusGroupLabel(meta) {
        var tag = meta.tag || 'OTHER';
        var structure = meta.structure || 'FIXED';
        return tag + ' / ' + structure;
    }

    function splitTellusProducts(products) {
        var grouped = {};
        $.each(products || [], function (_, product) {
            var meta = getTellusGroupMeta(product);
            if (!grouped[meta.key]) {
                grouped[meta.key] = {
                    key: meta.key,
                    tag: meta.tag,
                    structure: meta.structure,
                    products: []
                };
            }
            grouped[meta.key].products.push(product);
        });
        var groups = [];
        $.each(grouped, function (_, group) {
            groups.push(group);
        });
        var tagPriority = { AIRTIME: 1, DATA: 2 };
        var structurePriority = { FIXED: 1, RANGE: 2 };
        groups.sort(function (a, b) {
            var tA = tagPriority[a.tag] || 99;
            var tB = tagPriority[b.tag] || 99;
            if (tA !== tB) { return tA - tB; }
            var sA = structurePriority[a.structure] || 99;
            var sB = structurePriority[b.structure] || 99;
            if (sA !== sB) { return sA - sB; }
            return a.key.localeCompare(b.key);
        });
        return groups;
    }

    function renderTellusCategoryFilters(groups, activeKey, onSelect) {
        var $holder = $('#tellusCategoryFilters');
        if (!$holder.length) {
            $holder = $('<div id="tellusCategoryFilters" class="tama-v2-category-filters"></div>');
            $('.tama-v2-search').after($holder);
        }
        $holder.empty();
        if (!groups || groups.length <= 1) {
            $holder.addClass('hide');
            return;
        }
        $.each(groups, function (_, group) {
            var isActive = group.key === activeKey;
            var $btn = $('<button type="button" class="btn btn-default btn-sm"></button>');
            $btn.text(getTellusGroupLabel(group));
            $btn.css({ marginRight: '8px', marginBottom: '8px' });
            if (isActive) {
                $btn.removeClass('btn-default').addClass('btn-primary');
            }
            $btn.on('click', function () {
                onSelect(group.key);
            });
            $holder.append($btn);
        });
        $holder.removeClass('hide');
    }

    function renderTellusProductsByGroup(response, selectedKey) {
        var products = (response && response.data && response.data.products) ? response.data.products : [];
        var normalized = $.map(products, function (item) {
            return normalizeTellusProduct(item);
        });
        if (currentTellusType) {
            normalized = $.grep(normalized, function (item) {
                var itemType = String(item.type || item.tags || '').toUpperCase();
                return itemType === currentTellusType;
            });
        }
        if (!normalized.length) {
            hideTellusCategoryFilters();
            $('#productList').append('<li class="tama-v2-empty">No products available.</li>');
            return false;
        }

        var fixedProducts = $.grep(normalized, function (item) {
            return String(item.structure || '').toUpperCase() === 'FIXED';
        });

        hideTellusCategoryFilters();

        if (!fixedProducts.length) {
            $('#productList').empty();
            $('#productGrid').empty();
            hide('#rangeSection');
            show('#productSection');
            show('#productList');
            hide('#productGrid');
            $('#productList').append('<li class="tama-v2-empty">No fixed products available.</li>');
            hide('#summarySection');
            return true;
        }

        hide('#rangeSection');
        show('#productSection');
        hide('#productList');
        show('#productGrid');
        renderTellusGrid(fixedProducts);
        hide('#summarySection');
        return true;
    }

    function normalizeTellusProduct(product) {
        var out = $.extend({}, product);
        out.SkuCode = out.SkuCode || out.sku_code || out.operator_id || out.provider_code || out.productId || '';
        out.SendValue = out.SendValue !== undefined ? out.SendValue : out.minSendValue;
        out.ReceiveValue = out.ReceiveValue !== undefined ? out.ReceiveValue : out.maxReceiveValue;
        out.sendValueOriginal = out.sendValueOriginal !== undefined ? out.sendValueOriginal : out.SendValue;
        out.sendCurrencyIso = out.sendCurrencyIso || out.SendCurrencyIso || 'EUR';
        out.receiveCurrencyIso = out.receiveCurrencyIso || out.RecivedCurrencyIso || '';
        out.operator_name = out.operator_name || out.operator || $('#providerName').val();
        out.country = out.country || $('#providerCountry').val();
        out.product = out.product !== undefined && out.product !== null ? out.product : '';
        out.localAmount = out.localAmount !== undefined ? out.localAmount : out.ReceiveValue;
        out.localAmountMin = out.localAmountMin !== undefined ? out.localAmountMin : '';
        out.localAmountMax = out.localAmountMax !== undefined ? out.localAmountMax : '';
        out.productName = out.productName || out.name || '';
        out.productId = out.productId || out.SkuCode || '';
        out.type = out.type || out.tags || '';
        out.structure = out.structure || (out.maxSendValue ? 'RANGE' : 'FIXED');
        out.infoMode = out.infoMode || '';
        return out;
    }

    function parseTellusPriceRange(item) {
        if (item.localAmountMin && item.localAmountMax) {
            return {
                priceValue: formatTellusNumeric(item.localAmountMin),
                priceValueMax: formatTellusNumeric(item.localAmountMax)
            };
        }
        var match = extractTellusRangeMatch(item.productName || item.name || '');
        return {
            priceValue: match ? match.min : '',
            priceValueMax: match ? match.max : ''
        };
    }

    function fetchTellusProducts(providerCode, countryIso, onEmpty, onFail) {
        var url = api_base_url + '/tama-topup-v2/fetch/tellus/products?accountNumber=' + encodeURIComponent(getMobile())
            + '&countryCode=' + encodeURIComponent(getCountryCode())
            + '&providerCode=' + encodeURIComponent(providerCode || '');
        var cacheKey = buildCacheKey(['tellus', providerCode || '', countryIso || '', getCountryCode(), getMobile(), currentTellusType || '']);
        var cachedResponse = getCache(v2Cache.products, cacheKey);
        if (cachedResponse) {
            hide('#loadingProducts');
            $('#productList').empty();
            $('#productGrid').empty();
            if (renderTellusProductsByGroup(cachedResponse, currentTellusGroupKey) === false && typeof onEmpty === 'function') {
                onEmpty();
            }
            return;
        }
        $.get(url)
            .done(function (response) {
                hide('#loadingProducts');
                $('#productList').empty();
                $('#productGrid').empty();
                setCache(v2Cache.products, cacheKey, response);
                if (renderTellusProductsByGroup(response, currentTellusGroupKey) === false && typeof onEmpty === 'function') {
                    onEmpty();
                }
            })
            .fail(function () {
                hide('#loadingProducts');
                if (typeof onFail === 'function') {
                    onFail();
                    return;
                }
                $.alert({ title: 'Info', content: 'Unable to load products.' });
            });
    }

    function renderTellusGrid(products) {
        $.each(products, function (key, value) {
            var item = normalizeTellusProduct(value);
            var li = $('<li class="tama-v2-product"></li>');
            var a = $('<a href="javascript:void(0);"></a>');
            var amountText = formatCurrencyLabel(item.sendCurrencyIso || '\u20AC') + getTellusChargeAmount(item);
            var typeLabel = String(item.type || item.tags || '').toUpperCase();
            var receiveValue = getTellusReceiveAmount(item);
            var receiveText = item.receiveCurrencyIso ? (item.receiveCurrencyIso + ' ' + receiveValue) : receiveValue;
            var detailsParts = [];
            if (item.productName) {
                detailsParts.push(item.productName);
            } else if (item.name) {
                detailsParts.push(item.name);
            }
            if (item.description) {
                detailsParts.push(item.description);
            }
            var detailsText = detailsParts.join(' | ');
            if (typeLabel) {
                detailsText = '[' + typeLabel + '] ' + detailsText;
            }
            var validityText = item.validity || '';
            var logoHtml = getProviderLogoHtml($('#providerName').val());
            var front = $('<div class="tama-v2-product-face tama-v2-product-front"></div>');
            front.append('<div class="tama-v2-product-logo">' + logoHtml + '</div>');
            front.append('<div class="tama-v2-product-text"><div class="price">' + escapeHtml(amountText) + '</div><div>' + escapeHtml(item.productName || typeLabel) + '</div></div>');
            var back = $('<div class="tama-v2-product-face tama-v2-product-back"></div>');
            back.append('<div class="tama-v2-product-logo tama-v2-product-logo--back">' + logoHtml + '</div>');
            back.append('<div class="tama-v2-product-back-body">' + buildCommonBackBody(amountText, receiveText, validityText, detailsText) + '</div>');
            var inner = $('<div class="tama-v2-product-inner"></div>');
            inner.append(front).append(back);
            a.append($('<div class="tama-v2-product-card"></div>').append(inner));
            a.on('click', function () {
                $('#productGrid .tama-v2-product').removeClass('is-active');
                li.addClass('is-active');
                setTellusSelection(item);
                hide('#summarySection');
                clearReviewModal();
                updateTabs('amount');
                triggerReviewOrder();
            });
            li.append(a);
            $('#productGrid').append(li);
        });
        if (products && products.length === 1) {
            $('#productGrid .tama-v2-product a').first().click();
            scrollToSection('#reviewOrderBtn');
            return;
        }
        if (products && products.length) {
            scrollToSection('#productSection');
        }
    }

    function setTellusSelection(value) {
        var payableAmount = getTellusChargeAmount(value);
        var receiveAmount = getTellusReceiveAmount(value);
        var productValue = getTellusProductValue(value);
        $('#tellus_skuCode').val(value.SkuCode);
        $('#tellus_SendValue').val(payableAmount);
        $('#tellus_sendValueOriginal').val(payableAmount);
        $('#tellus_local_amount').val(receiveAmount);
        $('#tellus_description').val(value.description || '');
        $('#tellus_operator').val(value.operator_name || $('#providerName').val());
        $('#tellus_country').val(value.country || $('#providerCountry').val());
        $('#tellus_currency').val(value.receiveCurrencyIso || '');
        $('#tellus_minSendValue').val(value.minSendValue || '');
        $('#tellus_maxSendValue').val(value.maxSendValue || '');
        $('#tellus_priceValue').val('');
        $('#tellus_priceValueMax').val('');
        $('#tellus_localAmount').val(receiveAmount);
        $('#tellus_localAmountMin').val('');
        $('#tellus_localAmountMax').val('');
        $('#tellus_product').val(productValue);
        $('#tellus_productId').val(value.productId || value.SkuCode || '');
        $('#tellus_productName').val(value.productName || '');
        $('#tellus_type').val(value.type || value.tags || '');
        $('#tellus_structure').val('FIXED');
        $('#tellus_infoMode').val(value.infoMode || '');
        show('#summarySection');
    }

    function setupTellusRange(product) {
        var item = normalizeTellusProduct(product);
        var parsedRange = parseTellusPriceRange(item);
        $('#tellus_minSendValue').val(item.minSendValue || '');
        $('#tellus_maxSendValue').val(item.maxSendValue || '');
        $('#tellus_priceValue').val(parsedRange.priceValue);
        $('#tellus_priceValueMax').val(parsedRange.priceValueMax);
        $('#tellus_localAmount').val('');
        $('#tellus_localAmountMin').val(item.localAmountMin || parsedRange.priceValue || '');
        $('#tellus_localAmountMax').val(item.localAmountMax || parsedRange.priceValueMax || '');
        $('#tellus_product').val('');
        $('#tellus_productId').val(item.productId || item.SkuCode || '');
        $('#tellus_productName').val(item.productName || item.name || '');
        hide('#productSection');
        show('#rangeSection');
        hide('#summarySection');
        var amount_between = between_trans + ' ' + item.minSendValue + ' - ' + item.maxSendValue;
        setRangeMeta(item.minSendValue, item.maxSendValue, item.sendCurrencyIso || '\u20AC');
        $('#betweenDenomination').text(amount_between);
        $('#range_amount')
            .attr('placeholder', amount_between)
            .attr('title', amount_between)
            .attr('min', item.minSendValue)
            .attr('max', item.maxSendValue)
            .val('');
        clearRangeError();
        $('#amountReceivedDiv').addClass('hide');
        function updateTellusRangeValue(showError, shouldScroll) {
            var currentVal = parseFloat($('#range_amount').val());
            var min = parseFloat($('#range_amount').attr('min'));
            var max = parseFloat($('#range_amount').attr('max'));
            if (!currentVal || currentVal < min || currentVal > max) {
                if (showError) {
                    showRangeError('Enter amount between ' + item.minSendValue + ' and ' + item.maxSendValue + '.');
                }
                return false;
            }
            var localMinReal = parseFloat($('#tellus_priceValue').val());
            var localMaxReal = parseFloat($('#tellus_priceValueMax').val());
            if ((!localMinReal || !localMaxReal) && item.productName) {
                var parsedFallback = parseTellusPriceRange(item);
                localMinReal = parseFloat(parsedFallback.priceValue);
                localMaxReal = parseFloat(parsedFallback.priceValueMax);
            }
            if (!localMinReal || !localMaxReal || max <= min) {
                if (showError) {
                    showRangeError('Unable to calculate local amount for this range.');
                }
                return false;
            }
            var sendValue = currentVal.toFixed(2);
            var local = localMinReal + (((currentVal - min) / (max - min)) * (localMaxReal - localMinReal));
            var localRounded = Math.max(localMinReal, Math.min(localMaxReal, Math.round(local)));
            $('#tellus_skuCode').val(item.SkuCode);
            $('#tellus_SendValue').val(sendValue);
            $('#tellus_sendValueOriginal').val(sendValue);
            $('#tellus_local_amount').val(localRounded);
            $('#tellus_localAmount').val(localRounded);
            $('#tellus_product').val(String(localRounded));
            $('#tellus_productId').val(item.productId || item.SkuCode || '');
            $('#tellus_description').val(item.description || '');
            $('#tellus_operator').val(item.operator_name || $('#providerName').val());
            $('#tellus_country').val(item.country || $('#providerCountry').val());
            $('#tellus_currency').val(item.receiveCurrencyIso || '');
            $('#tellus_type').val(item.type || item.tags || '');
            $('#tellus_structure').val('RANGE');
            $('#tellus_infoMode').val(item.infoMode || '');
            $('#amountReceived').text((item.receiveCurrencyIso ? item.receiveCurrencyIso + ' ' : '') + localRounded);
            show('#amountReceivedDiv');
            show('#summarySection');
            if (shouldScroll) {
                scrollToSection('#reviewOrderBtn');
            }
            return true;
        }
        $('#range_amount').off('input blur keyup').on('input keyup', function () {
            clearRangeError();
            updateTellusRangeValue(false, false);
        }).on('blur', function () {
            updateTellusRangeValue(true, true);
        });
    }

    function fetchTransferProducts(providerCode, countryIso, type) {
        var url = api_base_url + '/tama-topup-v2/fetch/transfer/products?country_iso_code=' + encodeURIComponent(countryIso)
            + '&operator_id=' + encodeURIComponent(providerCode)
            + '&accountNumber=' + encodeURIComponent(getMobile())
            + '&type=' + encodeURIComponent(type);
        var cacheKey = buildCacheKey(['transfer', providerCode, countryIso, type, getCountryCode(), getMobile()]);
        var cachedResponse = getCache(v2Cache.products, cacheKey);
        if (cachedResponse) {
            hide('#loadingProducts');
            $('#productList').empty();
            $('#productGrid').empty();
            currentTransferMeta.denominationStyle = (cachedResponse.data && (cachedResponse.data.denomination_style || cachedResponse.data.denominationStyle)) || '';
            currentTransferMeta.isDenominated = (cachedResponse.data && typeof cachedResponse.data.is_denominated !== 'undefined') ? String(cachedResponse.data.is_denominated) : '';
            $('#transfer_denomination_style').val(currentTransferMeta.denominationStyle);
            $('#transfer_is_denominated').val(currentTransferMeta.isDenominated);
            if (cachedResponse.data && cachedResponse.data.is_denominated === true) {
                hide('#productList');
                show('#productGrid');
                renderTransferGrid(cachedResponse.data.products || []);
                hide('#summarySection');
            } else if (cachedResponse.data && cachedResponse.data.products && cachedResponse.data.products.length) {
                setupTransferRange(cachedResponse.data.products[0]);
            } else {
                $('#productList').append('<li class="tama-v2-empty">No products available.</li>');
            }
            return;
        }
        $.get(url)
            .done(function (response) {
                hide('#loadingProducts');
                $('#productList').empty();
                $('#productGrid').empty();
                setCache(v2Cache.products, cacheKey, response);
                currentTransferMeta.denominationStyle = (response.data && (response.data.denomination_style || response.data.denominationStyle)) || '';
                currentTransferMeta.isDenominated = (response.data && typeof response.data.is_denominated !== 'undefined') ? String(response.data.is_denominated) : '';
                $('#transfer_denomination_style').val(currentTransferMeta.denominationStyle);
                $('#transfer_is_denominated').val(currentTransferMeta.isDenominated);
                if (response.data && response.data.is_denominated === true) {
                    hide('#productList');
                    show('#productGrid');
                    renderTransferGrid(response.data.products || []);
                    hide('#summarySection');
                } else if (response.data && response.data.products && response.data.products.length) {
                    setupTransferRange(response.data.products[0]);
                } else {
                    $('#productList').append('<li class="tama-v2-empty">No products available.</li>');
                }
            })
            .fail(function () {
                hide('#loadingProducts');
                $.alert({ title: 'Info', content: 'Unable to load products.' });
            });
    }

    function renderTransferGrid(products) {
        $.each(products, function (key, value) {
            var li = $('<li class="tama-v2-product"></li>');
            var a = $('<a href="javascript:void(0);"></a>');
            var amountText = formatCurrencyLabel(value.sendCurrencyIso || '\u20AC') + value.SendValue;
            var receiveText = value.display_text && value.receiveCurrencyIso ? (value.display_text + ' ' + value.receiveCurrencyIso) : formatTransferReceive(value);
            var detailsText = value.description || value.name || '';
            var validityText = value.validity || '';
            var logoHtml = getProviderLogoHtml($('#providerName').val());
            var front = $('<div class="tama-v2-product-face tama-v2-product-front"></div>');
            front.append('<div class="tama-v2-product-logo">' + logoHtml + '</div>');
            front.append('<div class="tama-v2-product-text"><div class="price">' + escapeHtml(amountText) + '</div></div>');
            var back = $('<div class="tama-v2-product-face tama-v2-product-back"></div>');
            back.append('<div class="tama-v2-product-logo tama-v2-product-logo--back">' + logoHtml + '</div>');
            back.append('<div class="tama-v2-product-back-body">' + buildCommonBackBody(amountText, receiveText, validityText, detailsText) + '</div>');
            var inner = $('<div class="tama-v2-product-inner"></div>');
            inner.append(front).append(back);
            a.append($('<div class="tama-v2-product-card"></div>').append(inner));
            a.on('click', function () {
                $('#productGrid .tama-v2-product').removeClass('is-active');
                li.addClass('is-active');
                setTransferSelection(value);
                hide('#summarySection');
                clearReviewModal();
                updateTabs('amount');
                triggerReviewOrder();
            });
            li.append(a);
            $('#productGrid').append(li);
        });
        if (products && products.length === 1) {
            $('#productGrid .tama-v2-product a').first().click();
            scrollToSection('#reviewOrderBtn');
            return;
        }
        if (products && products.length) {
            scrollToSection('#productSection');
        }
    }
function setTransferSelection(value) {
        var receiveCurrency = value.receiveCurrencyIso || value.RecivedCurrencyIso || '';
        var receiveAmount = value.ReceiveValue || '';
        var displayText = receiveCurrency ? (receiveCurrency + ' ' + receiveAmount) : receiveAmount;
        var skuCode = value.operator_id || value.product_code || value.provider_code;
        var operatorId = $('#transfer_operator_id').val() || value.provider_code || value.operator_id || '';
        $('#transfer_name').val(value.name);
        $('#transfer_skuCode').val(skuCode);
        $('#transfer_display_text').val(displayText || value.display_text || value.name);
        $('#transfer_ReceiveValue').val(value.ReceiveValue);
        $('#transfer_SendValue').val(value.SendValue);
        $('#transfer_sendCurrencyIso').val(value.sendCurrencyIso);
        $('#transfer_receiveCurrencyIso').val(value.receiveCurrencyIso);
        $('#transfer_operator_id').val(operatorId);
        $('#transfer_operator_name').val(value.operator_name);
        $('#transfer_country').val(value.country);
        show('#summarySection');
    }

    function setupTransferRange(product) {
        hide('#productSection');
        show('#rangeSection');
        hide('#summarySection');
        var amount_between = between_trans + ' ' + product.minSendValue + ' - ' + product.maxSendValue;
        var receiveCurrency = product.RecivedCurrencyIso || product.receiveCurrencyIso || '';
        var skuCode = product.product_code || product.operator_id || product.provider_code;
        var operatorId = $('#transfer_operator_id').val() || product.provider_code || product.operator_id || '';
        var rangeStep = getTransferRangeStep(product);
        var hasRangeStep = rangeStep !== null;
        setRangeMeta(product.minSendValue, product.maxSendValue, product.sendCurrencyIso || '\u20AC');
        $('#betweenDenomination').text(amount_between);
        $('#range_amount')
            .attr('placeholder', amount_between)
            .attr('title', amount_between)
            .attr('min', product.minSendValue)
            .attr('max', product.maxSendValue)
            .attr('step', hasRangeStep ? rangeStep.toFixed(2) : '0.01')
            .val('');
        clearRangeError();
        $('#amountReceivedDiv').addClass('hide');
        function updateTransferRangeValue(showError, shouldScroll) {
            var currentVal = parseFloat($('#range_amount').val());
            var min = parseFloat($('#range_amount').attr('min'));
            var max = parseFloat($('#range_amount').attr('max'));
            if (!currentVal || currentVal < min || currentVal > max) {
                if (showError) {
                    showRangeError('Enter amount between ' + product.minSendValue + ' and ' + product.maxSendValue + '.');
                }
                return false;
            }
            // if (hasRangeStep && Math.round((currentVal - min) * 100) % Math.round(rangeStep * 100) !== 0) {
            //     if (showError) {
            //         showRangeError('Use amounts in ' + rangeStep.toFixed(2) + ' steps from ' + parseFloat(product.minSendValue).toFixed(2) + '.');
            //     }
            //     return false;
            // }
            var exchange = parseFloat(product.exchange_rate || product.fx_rate || 1);
            var sendValue = currentVal.toFixed(2);
            var local = currentVal * exchange;
            var localText = String(local);
            var localRounded = local.toFixed(2);
            $('#transfer_skuCode').val(skuCode);
            $('#transfer_SendValue').val(sendValue);
            $('#transfer_ReceiveValue').val(sendValue);
            $('#transfer_sendCurrencyIso').val(product.sendCurrencyIso);
            $('#transfer_receiveCurrencyIso').val(product.RecivedCurrencyIso || product.receiveCurrencyIso || '');
            $('#transfer_operator_id').val(operatorId);
            $('#transfer_operator_name').val(product.operator_name);
            $('#transfer_country').val(product.country);
            $('#transfer_display_text').val(localText);
            $('#transfer_name').val(product.name || 'Range');
            $('#amountReceived').text((receiveCurrency ? receiveCurrency + ' ' : '') + localRounded);
            show('#amountReceivedDiv');
            show('#summarySection');
            if (shouldScroll) {
                scrollToSection('#reviewOrderBtn');
            }
            return true;
        }
        $('#range_amount').off('input blur keyup').on('input keyup', function () {
            clearRangeError();
            hide('#summarySection');
            hide('#amountReceivedDiv');
            updateTransferRangeValue(false, false);
        }).on('blur', function () {
            updateTransferRangeValue(true, true);
        });
    }

    function buildReviewQuery(forcedProvider) {
        var provider = forcedProvider || $('#currentProvider').val();
        var params = [];
        function formatDisplayText(value) {
            var num = parseFloat(value);
            if (!isFinite(num)) { return ''; }
            return num.toFixed(2);
        }
        params.push('provider=' + encodeURIComponent(provider));
        if (provider === 'ding') {
            params.push('countryCode=' + encodeURIComponent(getCountryCode()));
            params.push('mobile=' + encodeURIComponent(getMobile()));
            params.push('provider_country=' + encodeURIComponent($('#providerCountry').val()));
            params.push('operator=' + encodeURIComponent($('#providerName').val()));
            params.push('euro_amount=' + encodeURIComponent($('#ding_euro_amount').val()));
            params.push('euro_amount_formatted=' + encodeURIComponent($('#ding_euro_amount_formatted').val()));
            params.push('dest_amount=' + encodeURIComponent($('#ding_dest_amount').val()));
            params.push('dest_amount_formatted=' + encodeURIComponent($('#ding_dest_amount_formatted').val()));
            params.push('SendAmount=' + encodeURIComponent($('#ding_SendValue').val()));
            params.push('SendCurrencyIso=' + encodeURIComponent($('#ding_SendCurrencyIso').val()));
            params.push('commissionRate=' + encodeURIComponent($('#ding_commissionRate').val()));
            params.push('skuCode=' + encodeURIComponent($('#ding_skuCode').val()));
            params.push('UatNumber=' + encodeURIComponent($('#ding_UatNumber').val()));
            params.push('SendValueOriginal=' + encodeURIComponent($('#ding_SendValueOriginal').val()));
        } else if (provider === 'reloadly') {
            params.push('mobile=' + encodeURIComponent(getMobile()));
            params.push('SendAmount=' + encodeURIComponent($('#reloadly_SendValue').val()));
            params.push('sendValueOriginal=' + encodeURIComponent($('#reloadly_sendValueOriginal').val()));
            params.push('skuCode=' + encodeURIComponent($('#reloadly_skuCode').val()));
            params.push('country=' + encodeURIComponent($('#reloadly_country').val()));
            params.push('operator=' + encodeURIComponent($('#reloadly_operator').val()));
            params.push('local_currency=' + encodeURIComponent($('#reloadly_local_amount').val()));
            params.push('countryCode=' + encodeURIComponent(getCountryCode()));
            params.push('description=' + encodeURIComponent($('#reloadly_description').val()));
        } else if (provider === 'tellus') {
            params.push('mobile=' + encodeURIComponent(getMobile()));
            params.push('SendAmount=' + encodeURIComponent($('#tellus_SendValue').val()));
            params.push('SendValue=' + encodeURIComponent($('#tellus_SendValue').val()));
            params.push('sendValueOriginal=' + encodeURIComponent($('#tellus_sendValueOriginal').val()));
            params.push('skuCode=' + encodeURIComponent($('#tellus_skuCode').val()));
            params.push('country=' + encodeURIComponent($('#tellus_country').val()));
            params.push('operator=' + encodeURIComponent($('#tellus_operator').val()));
            params.push('local_amt=' + encodeURIComponent($('#tellus_local_amount').val()));
            params.push('countryCode=' + encodeURIComponent(getCountryCode()));
            params.push('currency=' + encodeURIComponent($('#tellus_currency').val()));
            params.push('description=' + encodeURIComponent($('#tellus_description').val()));
            params.push('minSendValue=' + encodeURIComponent($('#tellus_minSendValue').val()));
            params.push('maxSendValue=' + encodeURIComponent($('#tellus_maxSendValue').val()));
            params.push('priceValue=' + encodeURIComponent($('#tellus_priceValue').val()));
            params.push('priceValueMax=' + encodeURIComponent($('#tellus_priceValueMax').val()));
            params.push('localAmount=' + encodeURIComponent($('#tellus_localAmount').val()));
            params.push('localAmountMin=' + encodeURIComponent($('#tellus_localAmountMin').val()));
            params.push('localAmountMax=' + encodeURIComponent($('#tellus_localAmountMax').val()));
            params.push('product=' + encodeURIComponent($('#tellus_product').val()));
            params.push('productId=' + encodeURIComponent($('#tellus_productId').val()));
            params.push('productName=' + encodeURIComponent($('#tellus_productName').val()));
            params.push('type=' + encodeURIComponent($('#tellus_type').val()));
            params.push('structure=' + encodeURIComponent($('#tellus_structure').val()));
            params.push('infoMode=' + encodeURIComponent($('#tellus_infoMode').val()));
        } else if (provider === 'transfer') {
            var displayText = $('#transfer_display_text').val();
            var displayTextFormatted = formatDisplayText(displayText);
            var denominationStyle = String(currentTransferMeta.denominationStyle || $('#transfer_denomination_style').val() || '').toUpperCase();
            var isDenominated = String(currentTransferMeta.isDenominated || $('#transfer_is_denominated').val() || '').toLowerCase() === 'true';
            var isEstimateStyle = (denominationStyle === 'ESTIMATE' || denominationStyle === 'EMITMATE');
            // alert(isDenominated);
            var reviewDisplayText = (!isDenominated && isEstimateStyle) ? displayText : '';
            params.push('mobile=' + encodeURIComponent(getMobile()));
            params.push('SendValue=' + encodeURIComponent($('#transfer_SendValue').val()));
            params.push('ReceiveValue=' + encodeURIComponent($('#transfer_ReceiveValue').val()));
            params.push('sendValueOriginal=' + encodeURIComponent($('#transfer_SendValue').val()));
            params.push('skuCode=' + encodeURIComponent($('#transfer_skuCode').val()));
            params.push('sendCurrencyIso=' + encodeURIComponent($('#transfer_sendCurrencyIso').val()));
            params.push('receiveCurrencyIso=' + encodeURIComponent($('#transfer_receiveCurrencyIso').val()));
            params.push('countryCode=' + encodeURIComponent(getCountryCode()));
            params.push('operator_id=' + encodeURIComponent($('#transfer_operator_id').val()));
            params.push('operator_name=' + encodeURIComponent($('#transfer_operator_name').val()));
            params.push('country=' + encodeURIComponent($('#transfer_country').val()));
            params.push('is_denominated=' + encodeURIComponent(isDenominated ? 'true' : 'false'));
            params.push('denomination_style=' + encodeURIComponent(denominationStyle.toLowerCase()));
            params.push('display_text=' + encodeURIComponent(reviewDisplayText));
            if (reviewDisplayText && displayTextFormatted) {
                params.push('display_text_formatted=' + encodeURIComponent(displayTextFormatted));
            }
            params.push('name=' + encodeURIComponent($('#transfer_name').val()));
        }
        return params.join('&');
    }
    function triggerReviewOrder() {
        var provider = $('#currentProvider').val();
        var providerOverride = '';
        if (!provider) { return; }
        if (provider === 'reloadly' && !$('#reloadly_skuCode').val()) {
            if ($('#transfer_skuCode').val()) {
                providerOverride = 'transfer';
            } else if ($('#tellus_skuCode').val() && $('#tellus_SendValue').val()) {
                providerOverride = 'tellus';
            } else if ($('#ding_skuCode').val()) {
                providerOverride = 'ding';
            } else {
                $.alert({ title: 'Info', content: 'Select a product first.' });
                return;
            }
        }
        if (provider === 'ding' && !$('#ding_skuCode').val()) {
            $.alert({ title: 'Info', content: 'Select a product first.' });
            return;
        }
        if (provider === 'tellus' && (!$('#tellus_skuCode').val() || !$('#tellus_SendValue').val())) {
            if ($('#transfer_skuCode').val()) {
                providerOverride = 'transfer';
            } else if ($('#reloadly_skuCode').val()) {
                providerOverride = 'reloadly';
            } else if ($('#ding_skuCode').val()) {
                providerOverride = 'ding';
            } else {
                $.alert({ title: 'Info', content: 'Select a product first.' });
                return;
            }
        }
        if ((provider === 'transfer' || providerOverride === 'transfer') && !$('#transfer_skuCode').val()) {
            $.alert({ title: 'Info', content: 'Select a product first.' });
            return;
        }
        var query = buildReviewQuery(providerOverride);
        if (!query) { return; }
        $.ajax({
            url: api_base_url + '/tama-topup-v2/encrypt-review',
            method: 'POST',
            data: query,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function (response) {
            var encryptedQuery = response && response.query ? response.query : '';
            if (!encryptedQuery) {
                $.alert({ title: 'Info', content: 'Unable to prepare the review link.' });
                return;
            }
            updateTabs('review');
            openV2Modal(api_base_url + '/tama-topup-v2/review?' + encryptedQuery, 'ORDER SUMMARY');
        }).fail(function () {
            $.alert({ title: 'Info', content: 'Unable to prepare the review link.' });
        });
    }

    function buildProductDetails(provider, value) {
        var amountText = '';
        var receiveText = '';
        var validityText = '';
        var detailsText = '';
        if (provider === 'ding') {
            amountText = formatDingPrice(value.maxSendAmountFormatted);
            receiveText = value.maxReceiveAmountFormatted || '';
            validityText = value.validity || '';
            detailsText = value.description || value.name || value.display_text || '';
        } else if (provider === 'reloadly') {
            amountText = formatCurrencyLabel(value.sendCurrencyIso || '\u20AC') + value.minSendValue;
            receiveText = value.RecivedCurrencyIso ? (value.RecivedCurrencyIso + ' ' + value.display_text) : value.display_text;
            validityText = value.validity || '';
            detailsText = value.description || value.name || '';
        } else if (provider === 'transfer') {
            amountText = formatCurrencyLabel(value.sendCurrencyIso || '\u20AC') + value.SendValue;
            receiveText = value.display_text && value.receiveCurrencyIso ? (value.display_text + ' ' + value.receiveCurrencyIso) : formatTransferReceive(value);
            validityText = value.validity || '';
            detailsText = value.description || value.name || '';
        }
        return buildCommonBackBody(amountText, receiveText, validityText, detailsText);
    }
function openV2Modal(url, title) {
        var $modal = $('#v2ReviewModal');
        if (!$modal.length) {
            AppModal(url, title);
            return;
        }
        $('body').addClass('tama-v2-modal-open');
        $modal.off('hidden.bs.modal.tamaV2').on('hidden.bs.modal.tamaV2', function () {
            $('body').removeClass('tama-v2-modal-open');
        });
        $('#v2ReviewModalLabel').text(title || '');
        $('#v2ReviewModalBody').html('<div class="tama-v2-loading"><div class="tama-v2-loader-net"><span class="node"></span><span class="node"></span><span class="node"></span><span class="node"></span><span class="node"></span></div><div class="tama-v2-loader-shimmer"></div></div>');
        $modal.modal('show');
        $.ajax({
            url: url,
            method: 'GET'
        }).done(function (response) {
            $('#v2ReviewModalBody').html(response);
        }).fail(function () {
            $('#v2ReviewModalBody').html('Something went wrong.');
        });
    }

    window.tamaV2FilterPriceList = function () {
        var input = document.getElementById('productSearch');
        var filter = input.value.toUpperCase();
        var lists = [document.getElementById('productList'), document.getElementById('productGrid')];
        for (var l = 0; l < lists.length; l++) {
            var ul = lists[l];
            if (!ul) { continue; }
            var li = ul.getElementsByTagName('li');
            for (var i = 0; i < li.length; i++) {
                var textNode = li[i].querySelector('.tama-v2-product-text');
                if (!textNode) { continue; }
                if (textNode.textContent.toUpperCase().indexOf(filter) > -1) {
                    li[i].style.display = '';
                } else {
                    li[i].style.display = 'none';
                }
            }
        }
    };

    function ensureLeadingPlus(value) {
        if (!value) {
            return '+';
        }
        var digits = String(value).replace(/[^\d]/g, '');
        return digits ? ('+' + digits) : '+';
    }

    function formatDingPrice(value) {
        var text = value === null || value === undefined ? '' : String(value);
        if (!text) { return '\u20AC'; }
        text = text.replace(/^\\s*EUR\\s*/i, '\u20AC ');
        if (/^[^0-9]*\u20AC/.test(text)) { return text; }
        if (/[a-zA-Z$]/.test(text)) { return text; }
        return '\u20AC' + text;
    }
    function initTransferTypeButtons() {
        var hideAirtimeCountries = [321];
        var code = parseInt(getCountryCode(), 10);
        if (!isNaN(code) && hideAirtimeCountries.indexOf(code) !== -1) {
            $('.transfer-type-btn[data-type="RANGED_VALUE_RECHARGE"]').addClass('hide');
            $('.transfer-type-btn[data-type="FIXED_VALUE_RECHARGE"]').removeClass('hide');
            $('#transferType').val('FIXED_VALUE_RECHARGE');
            $('.transfer-type-btn').removeClass('btn-primary').addClass('btn-default');
            $('.transfer-type-btn[data-type="FIXED_VALUE_RECHARGE"]').removeClass('btn-default').addClass('btn-primary');
        } else {
            $('.transfer-type-btn').removeClass('hide');
            if (!$('#transferType').val()) {
                $('#transferType').val('RANGED_VALUE_RECHARGE');
            }
            $('.transfer-type-btn').removeClass('btn-primary').addClass('btn-default');
            $('.transfer-type-btn[data-type="' + $('#transferType').val() + '"]').removeClass('btn-default').addClass('btn-primary');
        }
    }

    function maybeAutoFetchProviders() {
        if ($('#currentProvider').val() && getCountryCode() && getMobile()) {
            fetchProviders();
        }
    }

    function detectRouteAndLoad() {
        if (!getCountryCode() || !getMobile()) { return; }
        show('#mobileLoader');
        hideRouteError();
        var cacheKey = buildCacheKey(['route', getMobile(), getCountryCode(), getCountryIso()]);
        var cachedRoute = getCache(v2Cache.routes, cacheKey);
        if (cachedRoute && cachedRoute.route) {
            hide('#mobileLoader');
            var cachedProvider = cachedRoute.route;
            $('#dataBundleRoute').val(cachedRoute.data_bundle_route || '');
            if (cachedProvider === 'transfer_to_new' || cachedProvider === 'transfer_to') {
                setProvider('transfer');
                initTransferTypeButtons();
            } else if (cachedProvider === 'ding') {
                setProvider('ding');
            } else if (cachedProvider === 'reloadly') {
                setProvider('reloadly');
            } else if (cachedProvider === 'tellus') {
                setProvider('tellus');
            }
            hideRouteError();
            maybeAutoFetchProviders();
            return;
        }
        var url = api_base_url + '/tama-topup-v2/route?mobile=' + encodeURIComponent(getMobile())
            + '&countryCode=' + encodeURIComponent(getCountryCode())
            + '&countryIso=' + encodeURIComponent(getCountryIso());
        $.get(url)
            .done(function (response) {
                hide('#mobileLoader');
                if (!response || !response.route) {
                    $('#btnFetchProviders').prop('disabled', false).removeClass('hide');
                    $('#mobile').prop('disabled', false);
                    showRouteError();
                    $.alert({ title: 'Info', content: (window.v2ServiceNotAvailable || 'Service temporarily unavailable.') });
                    return;
                }
                var route = response.route;
                setCache(v2Cache.routes, cacheKey, response);
                $('#dataBundleRoute').val(response.data_bundle_route || '');
                if (route === 'transfer_to_new' || route === 'transfer_to') {
                    setProvider('transfer');
                    initTransferTypeButtons();
                } else if (route === 'ding') {
                    setProvider('ding');
                } else if (route === 'reloadly') {
                    setProvider('reloadly');
                } else if (route === 'tellus') {
                    setProvider('tellus');
                } else {
                    showRouteError();
                    $.alert({ title: 'Info', content: (window.v2ServiceNotAvailable || 'Service temporarily unavailable.') });
                    return;
                }
                hideRouteError();
                maybeAutoFetchProviders();
            })
            .fail(function () {
                hide('#mobileLoader');
                $('#btnFetchProviders').prop('disabled', false).removeClass('hide');
                $('#mobile').prop('disabled', false);
                showRouteError();
                $.alert({ title: 'Info', content: (window.v2ServiceNotAvailable || 'Service temporarily unavailable.') });
            });
    }

    $(document).ready(function () {
        initProductionInspectGuard();
        initSingleTabGuard();
        $(document).on('click', '.js-reloadly-data', function () {
            $('#reloadlyMode').val('data');
            resetProducts();
            show('#providerSection');
            show('#productSection');
            show('#loadingProducts');
            updateTabs('amount');
            scrollToSection('#productSection');
            $('#providerName').val($(this).data('name') || '');
            $('#providerCountry').val($(this).data('country') || '');
            setCurrentProviderLogo($(this).data('logo'));
            var bundleRoute = $('#dataBundleRoute').val();
            var mapped = mapRouteToProvider(bundleRoute);
            if (mapped === 'transfer') {
                fetchTransferDataForProvider($(this).data('name'), $(this).data('country'));
            } else if (mapped === 'tellus') {
                fetchTellusDataForProvider($(this).data('name'), $(this).data('country'), $(this).data('country_iso') || getCountryIso());
            } else if (mapped === 'ding') {
                fetchDingDataForProvider($(this).data('name'), $(this).data('country'), $(this).data('country_iso') || getCountryIso());
            } else {
                fetchReloadlyDataForProvider($(this).data('name'), $(this).data('country'), $(this).data('country_iso') || getCountryIso());
            }
        });
        $(document).on('click', '.js-reloadly-airtime', function () {
            $('#reloadlyMode').val('airtime');
            resetProducts();
            fetchProviders();
            scrollToSection('#providerSection');
        });
        $('.transfer-type-btn').on('click', function () {
            $('.transfer-type-btn').removeClass('btn-primary').addClass('btn-default');
            $(this).removeClass('btn-default').addClass('btn-primary');
            $('#transferType').val($(this).data('type'));
        });
        $('.reloadly-mode-btn').on('click', function () {
            $('.reloadly-mode-btn').removeClass('btn-primary').addClass('btn-default');
            $(this).removeClass('btn-default').addClass('btn-primary');
            $('#reloadlyMode').val($(this).data('mode'));
            if ($('#currentProvider').val() === 'reloadly') {
                if ($(this).data('mode') === 'data') {
                    fetchReloadlyData();
                } else {
                    maybeAutoFetchProviders();
                }
            }
        });
        $('#btnFetchProviders').on('click', function () {
            hideRouteError();
            $('#btnFetchProviders').prop('disabled', true).removeClass('hide');
            detectRouteAndLoad();
        });
        $('#changeNumberLink').on('click', function () {
            resetProducts();
            hide('#providerSection');
            hide('#reloadlyModeSection');
            hide('#transferTypeSection');
            $('#providerList').empty();
            $('#currentProvider').val('');
            setCurrentProviderLogo('');
            if (v2Iti) {
                v2Iti.setNumber('');
            }
            $('#mobile').prop('disabled', false).val('+').focus();
            $('#changeNumberLink').addClass('hide');
            $('#btnFetchProviders').prop('disabled', true).removeClass('hide');
            updateTabs('search');
            hideRouteError();
        });
        $('#reviewOrderBtn').on('click', function () {
            triggerReviewOrder();
        });

        var telInput = document.getElementById('mobile');
        var $telInput = $(telInput);
        var errorMsg = $('#span_mobile');
        var errorMapByLang = {
            en: [
                'Invalid number',
                'Invalid country code',
                'Too short',
                'Too long',
                'Invalid number'
            ],
            fr: [
                'Numero invalide',
                'Code pays invalide',
                'Trop court',
                'Trop long',
                'Numero invalide'
            ]
        };
        function getLangKey() {
            var raw = (document.documentElement.getAttribute('lang') || 'en').toLowerCase();
            return raw.split('-')[0] || 'en';
        }
        function getErrorMap() {
            var key = getLangKey();
            return errorMapByLang[key] || errorMapByLang.en;
        }
        function loadUtils() {
            var utilsUrl = api_base_url + '/vendor/intl-input/js/utils.js';
            if (window.v2IntlUtilsVersion) {
                utilsUrl += '?v=' + encodeURIComponent(window.v2IntlUtilsVersion);
            }
            return import(utilsUrl)
                .then(function (module) {
                    v2UtilsReady = true;
                    return module;
                });
        }
        v2Iti = window.intlTelInput(telInput, {
            initialCountry: '',
            separateDialCode: false,
            autoPlaceholder: 'off',
            nationalMode: false,
            formatOnDisplay: false,
            loadUtils: loadUtils
        });
        function ensureLeadingPlusInput() {
            var current = $telInput.val() || '';
            if (current.charAt(0) !== '+') {
                $telInput.val('+' + current.replace(/^\+*/, ''));
            }
        }
        function resetMobileError() {
            errorMsg.addClass('hide').text('');
            $telInput.parents('.form-group').removeClass('has-error');
        }
        function updateCountryFields() {
            if (!v2Iti) { return; }
            var countryData = v2Iti.getSelectedCountryData();
            $('#countryIso').val(countryData.iso2 || '');
            $('#countryCode').val(countryData.dialCode || '');
        }
        function updateMaxLength() {
            var utils = getUtils();
            if (!v2Iti || !utils) { return; }
            var countryData = v2Iti.getSelectedCountryData();
            if (!countryData || !countryData.iso2) {
                $telInput.removeAttr('maxlength');
                return;
            }
            var example = utils.getExampleNumber(countryData.iso2, true, utils.numberType.MOBILE);
            if (!example) {
                $telInput.removeAttr('maxlength');
                return;
            }
            var digits = String(example).replace(/\D/g, '');
            if (!digits.length) {
                $telInput.removeAttr('maxlength');
                return;
            }
            $telInput.attr('maxlength', digits.length + 5);
        }
        function updateValidState() {
            var utils = getUtils();
            if (!v2Iti || !utils) { return; }
            if (v2Iti.isValidNumber()) {
                updateCountryFields();
                initTransferTypeButtons();
                $('#btnFetchProviders').prop('disabled', false).removeClass('hide');
                hideRouteError();
                resetMobileError();
            } else {
                $('#countryIso').val('');
                $('#countryCode').val('');
                $('#btnFetchProviders').prop('disabled', true);
                var currentVal = $telInput.val() || '';
                if (currentVal.length > 1) {
                    var errorCode = v2Iti.getValidationError();
                    var map = getErrorMap();
                    var message = map[errorCode] || map[0];
                    errorMsg.text(message).removeClass('hide');
                    $telInput.parents('.form-group').addClass('has-error').removeClass('');
                } else {
                    resetMobileError();
                }
            }
        }
        $telInput.on('countrychange', function () {
            updateCountryFields();
            updateMaxLength();
            updateValidState();
        });
        $telInput.on('change keyup paste input focus', function (e) {
            var code = (e.keyCode || e.which);
            if (code == 37 || code == 38 || code == 39 || code == 40) { return; }
            resetMobileError();
            ensureLeadingPlusInput();
            var current = $telInput.val() || '';
            var cleaned = '+' + current.replace(/[^\d]/g, '');
            var maxLen = parseInt($telInput.attr('maxlength'), 10);
            if (maxLen && cleaned.length > maxLen) {
                cleaned = cleaned.slice(0, maxLen);
            }
            if (cleaned !== current) {
                $telInput.val(cleaned);
            }
            updateValidState();
        });
        $telInput.on('blur', function () {
            updateValidState();
        });
        ensureLeadingPlusInput();
        $telInput.focus();
        if (v2Iti && v2Iti.promise && typeof v2Iti.promise.then === 'function') {
            v2Iti.promise.then(function () {
                if (v2UtilsReady) {
                    updateCountryFields();
                    updateMaxLength();
                    updateValidState();
                }
            });
        }
    });
})();
