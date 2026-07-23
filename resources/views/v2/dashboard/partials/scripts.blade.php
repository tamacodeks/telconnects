{{-- External libs (ensure not duplicated by your layout) --}}
{{--<script src="{{ asset('assets/js/clock.js') }}"></script>--}}
{{-- Removed Apex & Moment since unused --}}
<script defer src="{{ asset('assets/js/notify/bootstrap-notify.min.js') }}"></script>
{{--<script src="{{ asset('assets/js/dashboard/default.js') }}"></script>--}}
<script defer src="{{ asset('assets/js/notify/index.js') }}"></script>
<script defer src="{{ asset('assets/js/typeahead/handlebars.js') }}"></script>
<script defer src="{{ asset('assets/js/typeahead/typeahead.bundle.js') }}"></script>
<script defer src="{{ asset('assets/js/typeahead/typeahead.custom.js') }}"></script>
{{-- Removed duplicate: assets/js/typeahead-search/handlebars.js --}}
{{-- Removed: assets/js/typeahead-search/typeahead-custom.js --}}
<script defer src="{{ asset('assets/js/height-equal.js') }}"></script>
<script defer src="{{ asset('assets/js/animation/wow/wow.min.js') }}"></script>

<script>
(function($){

  /* -------- utils -------- */
  function debounce(fn, wait){ var t; return function(){ var c=this,a=arguments; clearTimeout(t); t=setTimeout(function(){ fn.apply(c,a); }, wait); }; }
  function esc(str){ return $('<div>').text(str==null?'':String(str)).html(); }
  var DASH_CACHE_PREFIX = 'dashboard:v2-retailer-8:user-{{ (int) optional($dashboardAuthUser)->id }}:group-{{ (int) optional($dashboardAuthUser)->group_id }}:';
  var DASH_CACHE_TTL = 60000;
  var DASH_IS_RETAILER = @json($isRetailer);
  var DASH_I18N = @json($retailerText);
  var RETAILER_INTRO_DELAY = 8000;
  var retailerIntroStartedAt = Date.now();
  var retailerBannerSwapTimer = null;
  var retailerBannerHasSlides = false;
  var bannerRenderRequest = 0;

  function keepRetailerWelcomeCard(){
    if (!DASH_IS_RETAILER) return;
    retailerBannerHasSlides = false;
    if (retailerBannerSwapTimer) {
      clearTimeout(retailerBannerSwapTimer);
      retailerBannerSwapTimer = null;
    }
    $('#retailerWelcomeCard').removeClass('is-hidden').attr('aria-hidden', 'false');
    $('#retailerBannerCard').removeClass('is-visible').attr('aria-hidden', 'true');
  }

  function scheduleRetailerBannerSwap(){
    if (!DASH_IS_RETAILER || !retailerBannerHasSlides) return;
    if (retailerBannerSwapTimer) clearTimeout(retailerBannerSwapTimer);
    var elapsed = Date.now() - retailerIntroStartedAt;
    var wait = Math.max(0, RETAILER_INTRO_DELAY - elapsed);
    retailerBannerSwapTimer = setTimeout(function(){
      if (!retailerBannerHasSlides) return;
      $('#retailerWelcomeCard').addClass('is-hidden').attr('aria-hidden', 'true');
      $('#retailerBannerCard').addClass('is-visible').attr('aria-hidden', 'false');
    }, wait);
  }

  function cacheKey(url, params){
    params = params || {};
    return DASH_CACHE_PREFIX + url + ':' + Object.keys(params).sort().map(function(k){ return k+'='+params[k]; }).join('&');
  }

  function cachedData(url, params){
    try {
      var raw = sessionStorage.getItem(cacheKey(url, params));
      if (!raw) return null;
      var cached = JSON.parse(raw);
      if (!cached || !cached.stored_at || (Date.now() - cached.stored_at) > (cached.ttl || DASH_CACHE_TTL)) return null;
      return cached;
    } catch(e) {
      return null;
    }
  }

  function storeCache(url, params, data, ttl){
    try {
      sessionStorage.setItem(cacheKey(url, params), JSON.stringify({
        stored_at: Date.now(),
        ttl: ttl || DASH_CACHE_TTL,
        signature: data && data._meta ? data._meta.signature : JSON.stringify(data).length,
        data: data
      }));
    } catch(e) {}
  }

  function dashboardFetch(url, params, options){
    params = params || {};
    options = options || {};
    var query = new URLSearchParams(params).toString();
    var fullUrl = query ? url + '?' + query : url;
    var cached = options.cache !== false ? cachedData(url, params) : null;

    if (cached && typeof options.onData === 'function') {
      options.onData(cached.data, true);
    }

    if (options.keepOld) {
      $('.container-fluid').addClass('dashboard-refreshing');
    }

    return fetch(fullUrl, {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    }).then(function(response){
      if (!response.ok) throw new Error('Request failed: '+response.status);
      return response.json();
    }).then(function(data){
      $('#dashboard-error').removeClass('is-visible');
      $('.container-fluid').removeClass('dashboard-refreshing');
      var ttl = data && data._meta && data._meta.ttl ? Number(data._meta.ttl) * 1000 : DASH_CACHE_TTL;
      var signature = data && data._meta ? data._meta.signature : JSON.stringify(data).length;
      if (!cached || cached.signature !== signature) {
        storeCache(url, params, data, ttl);
        if (typeof options.onData === 'function') options.onData(data, false);
      }
      return data;
    }).catch(function(error){
      $('.container-fluid').removeClass('dashboard-refreshing');
      showDashboardError(error.message || DASH_I18N.error_load || 'Dashboard request failed');
      if (!cached && typeof options.onError === 'function') options.onError(error);
      return null;
    });
  }

  function showDashboardError(message){
    $('#dashboard-error-message').text(message || DASH_I18N.error_load || 'Dashboard data failed to load.');
    $('#dashboard-error').addClass('is-visible');
    if (window.Swal && Swal.fire) {
      Swal.fire({ toast:true, icon:'error', title:(DASH_I18N.refresh_failed || 'Dashboard refresh failed'), position:'top-end', timer:2600, showConfirmButton:false });
    }
  }

  function animateCountText($scope){
    $scope.find('.kpi-value,.root-health-value,.root-attention-value,.root-system-value').each(function(){
      var el = this;
      var text = $(el).text().trim();
      if (!/^\d+(\.\d+)?$/.test(text.replace(/,/g, ''))) return;
      var target = Number(text.replace(/,/g, ''));
      var decimals = text.indexOf('.') > -1 ? 2 : 0;
      var start = 0;
      var started = performance.now();
      var duration = 650;
      function frame(now){
        var p = Math.min(1, (now - started) / duration);
        var eased = 1 - Math.pow(1 - p, 3);
        var value = start + (target - start) * eased;
        el.textContent = value.toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        if (p < 1) requestAnimationFrame(frame);
      }
      requestAnimationFrame(frame);
    });
  }

  function lazyRun(elementId, fn){
    var el = document.getElementById(elementId);
    if (!el) return;
    if (!('IntersectionObserver' in window)) { fn(); return; }
    var observer = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if (entry.isIntersecting) {
          observer.disconnect();
          fn();
        }
      });
    }, { rootMargin: '160px' });
    observer.observe(el);
  }

  function deferDashboardTask(fn, timeout){
    if (typeof fn !== 'function') return;
    var wait = Number(timeout) || 120;
    if ('requestIdleCallback' in window) {
      requestIdleCallback(fn, { timeout: Math.max(wait, 500) });
    } else {
      setTimeout(fn, wait);
    }
  }

  window.dashboardFetch = dashboardFetch;
  window.lazyRun = lazyRun;
  window.debounce = debounce;
  window.deferDashboardTask = deferDashboardTask;

  /* -------- SUMMARY + KPI tiles -------- */
  function loadDashboardSummary(){
    if (!($('#kpi-grid').length || $('#banner-slides').length || $('#root-health-grid').length || $('#retailerHeroSummary').length)) return;
    dashboardFetch('/dashboard/summary', {}, {
      keepOld: true,
      onData: function(s){
        renderKPIs(s);
        renderRetailerWorkspace(s);
        renderBanners(s.banners||[]);
        animateCountText($('.container-fluid'));
      }
    });
  }
var KPI_LINKS = {
    // total_resellers:  "{{ url('/users') }}",
    orders_today:     "{{ url('/orders-v2?date=today') }}",
    todays_amount:    "{{ url('/transactions-v2?date=today') }}",
    month_amount:     "{{ url('/transactions-v2?range=this-month') }}",
    last_activity:    "{{ url('/activity') }}",
    ip_address:       "#",

    // NEW
    failed_callback:  "{{ url('callback/list') }}",
    payment_add:  "{{ url('payment/add') }}",
    profile:      "{{ \Illuminate\Support\Facades\Route::has('profile.v2') ? route('profile.v2') : url('profile-v2') }}",
    limits:       "{{ url('limit/add') }}",
    topup_v2:     "{{ url('tama-topup-v2') }}",
    cards_v2:     "{{ url('calling-cards-v2') }}",
    bus_v2:       "{{ route('bus.v2') }}",
    manage_routes:    "{{ url('tamatopup/alterroute') }}",
    orders:           "{{ url('orders-v2') }}",
    tickets:          "{{ url('tickets/manage') }}",
    service_access:   "{{ url('service-access') }}",
    admin_users:      "{{ \Illuminate\Support\Facades\Route::has('users.v2') ? route('users.v2') : url('users-v2') }}",
    services:         "{{ url('services') }}",
    menus:            "{{ url('menus') }}",
    activities:       "{{ url('activities') }}"
  };

  loadDashboardSummary();
  $('#dashboard-retry').on('click', function(){ loadDashboardSummary(); loadOrders(); });

  function iconClass(raw) {
    var icon = (raw || 'fa-line-chart').toString().trim();
    if (!icon || icon === 'fa') {
      return 'fa fa-line-chart';
    }
    if (icon.indexOf(' ') === -1 && icon.indexOf('fa-') === 0) {
      return 'fa ' + icon;
    }
    return icon;
  }

  function dashboardMetricNumber(value){
    var text = String(value == null ? '' : value).replace(/\s/g, '').replace(/[^\d,.-]/g, '');
    if (text.indexOf(',') > -1 && text.indexOf('.') === -1) {
      text = text.replace(',', '.');
    } else {
      text = text.replace(/,/g, '');
    }
    var number = Number(text);
    return isNaN(number) ? 0 : number;
  }

  function dashboardMetricDisplay(value, options){
    options = options || {};
    var raw = String(value == null ? '' : value).trim();
    if (!raw || (!/\d/.test(raw) && !/[€$£]/.test(raw))) return raw;
    var number = dashboardMetricNumber(raw);
    if (options.absolute) number = Math.abs(number);
    var symbolMatch = raw.match(/[€$£]/);
    if (!symbolMatch) return raw;
    return (number < 0 ? '-' : '') + symbolMatch[0] + Math.abs(number).toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function dashboardPercent(part, total){
    part = dashboardMetricNumber(part);
    total = dashboardMetricNumber(total);
    if (!total || total <= 0) return null;
    return Math.max(0, Math.min(100, Math.round((part / total) * 100)));
  }

  function dashboardTrendTemplate(template, value){
    return String(template || '').replace(':value', value);
  }

  function metricTrend(text, tone, icon){
    if (!text) return '';
    return (
      '<span class="kpi-trend '+esc(tone || 'neutral')+'">'+
        (icon ? '<i class="'+iconClass(icon)+'" aria-hidden="true"></i>' : '')+
        '<span>'+esc(text)+'</span>'+
      '</span>'
    );
  }

  function kpiTile(href, fa, label, value, color, tileClass, meta){
    if ($.isPlainObject(tileClass)) {
      meta = tileClass;
      tileClass = '';
    }
    meta = meta || {};
    var classes = ['kpi-tile'];
    var iconClasses = ['kpi-icon'];
    if (color) classes.push(color);
    if (color) iconClasses.push(color);
    if (tileClass) classes.push(tileClass);
    if (meta.danger || String(value || '').indexOf('-') > -1) classes.push('is-danger-value');
    if (meta.positive) classes.push('is-positive-value');
    if (meta.hideIcon) classes.push('kpi-tile--no-icon');
    if (meta.iconClass) {
      iconClasses = iconClasses.concat(String(meta.iconClass).split(/\s+/).filter(Boolean));
    }
    var subtitle = meta.subtitle ? '<p class="kpi-subtitle">'+esc(meta.subtitle)+'</p>' : '';
    var trend = metricTrend(meta.trend, meta.trendTone, meta.trendIcon);
    var iconMarkup = meta.hideIcon ? '' : '<div class="'+esc(iconClasses.join(' '))+'"><i class="'+iconClass(fa)+'" aria-hidden="true"></i></div>';

    return (
      '<div class="col-sm-6">'+
        '<a class="'+esc(classes.join(' '))+'" href="'+esc(href)+'" aria-label="'+esc(label)+'">'+
          iconMarkup+
          '<div class="kpi-content"><p class="kpi-label">'+esc(label)+'</p><p class="kpi-value">'+esc(value)+'</p>'+subtitle+'</div>'+
          trend+
        '</a>'+
      '</div>'
    );
  }

  function rootMetricCard(href, fa, label, value, tone){
    return (
      '<a class="root-health-card tone-'+esc(tone || 'blue')+'" href="'+esc(href || '#')+'" aria-label="'+esc(label)+'">'+
        '<span class="root-health-icon"><i class="'+iconClass(fa)+'" aria-hidden="true"></i></span>'+
        '<span class="root-health-content"><p class="root-health-label">'+esc(label)+'</p><p class="root-health-value">'+esc(value)+'</p></span>'+
      '</a>'
    );
  }

  function rootActiveUsersCard(href, active, total){
    active = Number(active || 0);
    total = Number(total || 0);
    var percent = total > 0 ? Math.round((active / total) * 1000) / 10 : 0;
    var width = Math.max(0, Math.min(100, percent));

    return (
      '<a class="root-health-card root-health-card-progress tone-green" href="'+esc(href || '#')+'" aria-label="Active Users">'+
        '<span class="root-health-icon"><i class="fa fa-users" aria-hidden="true"></i></span>'+
        '<span class="root-health-content">'+
          '<p class="root-health-label">Active Users</p>'+
          '<p class="root-health-value">'+esc(active)+' / '+esc(total)+'</p>'+
          '<span class="root-progress-row">'+
            '<span class="root-progress-track"><span class="root-progress-fill" style="width:'+esc(width)+'%"></span></span>'+
            '<span class="root-progress-value">'+esc(percent)+'%</span>'+
          '</span>'+
        '</span>'+
      '</a>'
    );
  }

  function rootSplitCard(primaryHref, secondaryHref, rootAdmins, menuItems){
    return (
      '<a class="root-health-card root-health-split tone-amber" href="'+esc(primaryHref || '#')+'" aria-label="Root Admins and Root Menu Items">'+
        '<span class="root-split-item">'+
          '<span class="root-health-icon"><i class="fa fa-shield" aria-hidden="true"></i></span>'+
          '<span class="root-health-content"><p class="root-health-label">Root Admins</p><p class="root-health-value">'+esc(rootAdmins || 0)+'</p></span>'+
        '</span>'+
        '<span class="root-health-divider" aria-hidden="true"></span>'+
        '<span class="root-split-item" data-href="'+esc(secondaryHref || '#')+'">'+
          '<span class="root-health-icon"><i class="fa fa-th-list" aria-hidden="true"></i></span>'+
          '<span class="root-health-content"><p class="root-health-label">Root Menu Items</p><p class="root-health-value">'+esc(menuItems || 0)+'</p></span>'+
        '</span>'+
      '</a>'
    );
  }

  function rootAttentionCard(item){
    var level = item.level || 'ok';
    var toneMap = {
      'Inactive Users': 'orange',
      'Disabled Services': 'red',
      'Open Tickets': 'orange',
      'Warnings Today': 'red',
      'Menu Issues': 'green',
      'Empty Providers': 'cyan',
      'Failed Orders Today': 'yellow'
    };
    var tone = toneMap[item.label || ''] || (level === 'danger' ? 'red' : (level === 'warning' ? 'yellow' : 'green'));
    var value = item.value === undefined || item.value === null ? 0 : item.value;
    return (
      '<a class="root-attention-card '+esc(level)+' tone-'+esc(tone)+'" href="'+esc(item.url || '#')+'" aria-label="'+esc(item.label || 'Needs Attention')+'">'+
        '<span class="root-attention-icon"><i class="'+iconClass(item.icon || 'fa-info-circle')+'" aria-hidden="true"></i></span>'+
        '<span class="root-attention-content"><p class="root-attention-label">'+esc(item.label || 'Needs Attention')+'</p><p class="root-attention-value">'+esc(value)+'</p></span>'+
      '</a>'
    );
  }

  function rootSystemCard(item){
    var level = item.level || 'ok';
    return (
      '<div class="root-system-card '+esc(level)+'" aria-label="'+esc(item.label || 'System')+'">'+
        '<span class="root-system-icon"><i class="'+iconClass(item.icon || 'fa-server')+'" aria-hidden="true"></i></span>'+
        '<span class="root-system-content"><p class="root-system-label">'+esc(item.label || 'System')+'</p><p class="root-system-value">'+esc(item.value || '-')+'</p><span class="root-system-detail" title="'+esc(item.detail || '')+'">'+esc(item.detail || '')+'</span></span>'+
        '<span class="root-system-dot" aria-hidden="true"></span>'+
      '</div>'
    );
  }

  function rootActivityRow(item){
    var level = item.level || 'ok';
    return (
      '<tr>'+
        '<td><span class="root-activity-type '+esc(level)+'">'+esc(item.type || 'info')+'</span></td>'+
        '<td>'+esc(item.title || '-')+'</td>'+
        '<td>'+esc(item.user || 'System')+'</td>'+
        '<td>'+esc(item.time || '-')+'</td>'+
      '</tr>'
    );
  }

  function retailerActionDisplay(action){
    action = action || {};
    var label = String(action.label || '').toLowerCase();
    var url = String(action.url || '');
    if (action.key === 'topup' || url.indexOf('tama-topup') > -1 || label.indexOf('topup') > -1) {
      return {
        label: DASH_I18N.topup || 'Topup',
        detail: DASH_I18N.topup_detail || 'Mobile topup',
        description: DASH_I18N.topup_description || 'Recharge mobile numbers quickly.',
        icon: action.icon || 'fa-mobile-alt',
        tone: action.tone || 'amber',
        url: url || KPI_LINKS.topup_v2
      };
    }
    if (url.indexOf('calling-cards') > -1 || label.indexOf('calling') > -1) {
      return {
        label: DASH_I18N.calling_cards || 'Calling cards',
        detail: DASH_I18N.recharge_cards_detail || 'Calling cards',
        description: DASH_I18N.recharge_cards_description || 'Sell recharge cards quickly.',
        icon: action.icon || 'fa-credit-card',
        tone: action.tone || 'green',
        url: url || KPI_LINKS.cards_v2
      };
    }
    if (url.indexOf('bus') > -1 || label.indexOf('bus') > -1) {
      return {
        label: DASH_I18N.bus_tickets || 'Bus tickets',
        detail: DASH_I18N.bus_detail || 'Flix Bus & BlaBlaBus',
        description: DASH_I18N.bus_description || 'Book bus tickets in seconds.',
        icon: action.icon || 'fa-bus',
        tone: action.tone || 'purple',
        url: url || KPI_LINKS.bus_v2
      };
    }
    return {
      label: action.label || DASH_I18N.service || 'Service',
      detail: action.detail || DASH_I18N.open || 'Open',
      description: action.description || DASH_I18N.open || 'Open',
      icon: action.icon || 'fa-external-link-alt',
      tone: action.tone || 'blue',
      url: url || '#'
    };
  }

  function retailerActionCard(action){
    action = retailerActionDisplay(action);
    var tone = action.tone || 'blue';
    return (
      '<a class="retailer-action-card tone-'+esc(tone)+'" href="'+esc(action.url || '#')+'" aria-label="'+esc((action.label || 'Action')+' - '+(action.description || action.detail || ''))+'">'+
        '<span class="retailer-action-icon"><i class="'+iconClass(action.icon || 'fa-external-link')+'" aria-hidden="true"></i></span>'+
        '<span class="retailer-action-copy"><p class="retailer-action-label">'+esc(action.label || DASH_I18N.action || 'Action')+'</p><p class="retailer-action-value">'+esc(action.detail || DASH_I18N.open || 'Open')+'</p><p class="retailer-action-description">'+esc(action.description || '')+'</p></span>'+
        '<span class="retailer-action-arrow" aria-hidden="true"><i class="fa fa-arrow-right"></i></span>'+
      '</a>'
    );
  }

  function retailerStatusCard(label, value, fa, tone, href){
    return (
      '<a class="retailer-status-card tone-'+esc(tone || 'blue')+'" href="'+esc(href || '#')+'" aria-label="'+esc(label)+'">'+
        '<span class="retailer-status-icon"><i class="'+iconClass(fa || 'fa-info-circle')+'" aria-hidden="true"></i></span>'+
        '<span style="min-width:0;flex:1"><p class="retailer-status-label">'+esc(label)+'</p><p class="retailer-status-value">'+esc(value)+'</p></span>'+
      '</a>'
    );
  }

  function hideDashboardBanner(){
    if (DASH_IS_RETAILER) {
      keepRetailerWelcomeCard();
      return;
    }

    $('[data-dashboard-banner-slot]').hide().attr('aria-hidden', 'true');
    $('[data-dashboard-kpi-slot]').each(function(){
      var $slot = $(this);
      if (!$slot.data('banner-layout-classes')) {
        $slot.data('banner-layout-classes', $slot.attr('class') || '');
      }
      $slot.removeClass('col-md-6 col-lg-6').addClass('col-md-12');
    });
  }

  function showDashboardBanner(){
    if (DASH_IS_RETAILER) return;
    $('[data-dashboard-banner-slot]').show().attr('aria-hidden', 'false');
    $('[data-dashboard-kpi-slot]').each(function(){
      var originalClasses = $(this).data('banner-layout-classes');
      if (originalClasses) $(this).attr('class', originalClasses);
    });
  }

  function retailerHeroAlertChip(text, tone, fa){
    return (
      '<span class="retailer-hero-alert tone-'+esc(tone || 'neutral')+'">'+
        (fa ? '<i class="'+iconClass(fa)+'" aria-hidden="true"></i>' : '')+
        '<span>'+esc(text)+'</span>'+
      '</span>'
    );
  }

  function retailerHeroTransactionItem(row){
    if (!row) return '';
    var product = row.tt_operator || row.product_name || row.service_name || '-';
    var service = row.service_name || '-';
    var dateText = retailerDateText(row.date);
    var amount = retailerFormatAmount(row.order_amount);
    var status = statusClass(row.status);
    var statusText = retailerStatusText(row.status || '-');

    return (
      '<a class="retailer-hero-transaction" href="'+esc(retailerOrderUrl(row))+'" aria-label="'+esc(product)+'">'+
        '<span class="retailer-hero-transaction-main">'+
          '<strong>'+esc(product)+'</strong>'+
          '<span>'+esc(service)+' - '+esc(dateText)+'</span>'+
        '</span>'+
        '<span class="retailer-hero-transaction-side">'+
          '<span class="label label-'+status+'">'+esc(statusText)+'</span>'+
          '<strong>'+esc(amount)+'</strong>'+
        '</span>'+
      '</a>'
    );
  }

  function renderRetailerHeroTransactions(rows){
    if (!$('#retailerHeroTransactions').length) return;
    rows = $.isArray(rows) ? rows.slice(0, 3) : [];

    if (!rows.length) {
      $('#retailerHeroTransactions').html('<div class="retailer-hero-empty">'+esc(DASH_I18N.no_recent_orders || 'No recent orders')+'</div>');
      return;
    }

    $('#retailerHeroTransactions').html(rows.map(retailerHeroTransactionItem).join(''));
  }

  function retailerLastOrderCard(order){
    if (!order) {
      return '<div class="retailer-empty">'+esc(DASH_I18N.no_recent_orders || 'No recent orders')+'</div>';
    }

    return (
      '<a href="'+esc(KPI_LINKS.orders)+'" class="retailer-last-order-id">'+
        '<i class="fa fa-hashtag" aria-hidden="true"></i><span>'+esc(DASH_I18N.order || 'Order')+' '+esc(order.id || '-')+'</span>'+
      '</a>'+
      '<h5 class="retailer-last-order-title">'+esc(order.product_name || order.service_name || '-')+'</h5>'+
      '<p class="retailer-last-order-meta">'+esc(order.service_name || '-')+' - '+esc(order.date || '-')+'</p>'+
      '<div class="retailer-last-order-footer">'+
        '<span class="label label-'+statusClass(order.status)+'">'+esc(retailerStatusText(order.status || '-'))+'</span>'+
        '<strong>'+esc(order.order_amount || '0.00')+'</strong>'+
      '</div>'
    );
  }

  function latestOrderRowToRetailerCard(row){
    if (!row) return null;
    return {
      id: row.id || '-',
      date: row.date || '-',
      service_name: row.service_name || '-',
      status: row.status || '-',
      product_name: row.tt_operator || row.product_name || row.service_name || '-',
      order_amount: row.order_amount || '0.00'
    };
  }

  function defaultRetailerActions(){
    return [
      { key:'topup', label:'Topup', detail:'Mobile topup', url:KPI_LINKS.topup_v2, icon:'fa-mobile-alt', tone:'amber' },
      { label:'Calling Cards', detail:'Calling cards', url:KPI_LINKS.cards_v2, icon:'fa-credit-card', tone:'green' },
      { label:'Bus Tickets', detail:'FlixBus and BlaBlaBus', url:KPI_LINKS.bus_v2, icon:'fa-bus', tone:'purple' }
    ];
  }

  function renderRetailerWorkspace(s){
    if (!$('#retailer-actions-grid').length) return;
    var actions = ($.isArray(s.quick_actions) && s.quick_actions.length) ? s.quick_actions : defaultRetailerActions();
    var hasTopup = actions.some(function(action){
      var normalized = retailerActionDisplay(action || {});
      var url = String((normalized && normalized.url) || '').toLowerCase();
      return (action && action.key === 'topup') || url.indexOf('tama-topup') > -1;
    });
    if (!hasTopup) {
      actions = actions.slice(0, 2);
      actions.push({ key:'topup', label:'Topup', detail:'Mobile topup', url:KPI_LINKS.topup_v2, icon:'fa-mobile-alt', tone:'amber' });
    }
    actions.sort(function(left, right){
      function order(action){
        var normalized = retailerActionDisplay(action);
        var label = String((normalized && normalized.label) || '').toLowerCase();
        var url = String((normalized && normalized.url) || '').toLowerCase();
        if ((action && action.key === 'topup') || url.indexOf('tama-topup') > -1 || label.indexOf('topup') > -1) return 1;
        if (url.indexOf('calling-cards') > -1 || label.indexOf('calling') > -1 || label.indexOf('cartes') > -1) return 2;
        if (url.indexOf('bus') > -1 || label.indexOf('bus') > -1) return 3;
        return 9;
      }
      return order(left) - order(right);
    });

    $('#retailer-actions-grid').html(actions.map(retailerActionCard).join(''));
  }

  function renderRootDashboard(s){
    var metrics = s.root_metrics || {};
    var activeUsers = Number(metrics.active_users || 0);
    var totalUsers = Number(metrics.total_users || 0);
    var health = [
      rootActiveUsersCard(KPI_LINKS.admin_users, activeUsers, totalUsers),
      rootMetricCard("{{ \Illuminate\Support\Facades\Route::has('user-groups.v2') ? route('user-groups.v2') : url('user-groups-v2') }}", 'fa-object-group', 'User Groups', metrics.user_groups || 0, 'blue'),
      rootSplitCard(KPI_LINKS.admin_users, "{{ url('menus-v2') }}", metrics.root_admins || 0, metrics.menu_items || 0),
      rootMetricCard(KPI_LINKS.services, 'fa-cubes', 'Active Services', metrics.active_services || 0, 'green'),
      rootMetricCard("{{ url('myservice') }}", 'fa-globe', 'Telecom Providers', metrics.telecom_providers || 0, 'blue')
    ];
    var hiddenRootAlerts = ['Menu Issues', 'Empty Providers'];
    var attention = (s.root_attention || []).filter(function(item){
      return hiddenRootAlerts.indexOf(item.label || '') === -1;
    });

    $('#root-health-grid').html(health.join(''));
    $('#root-attention-grid').html(attention.map(rootAttentionCard).join('') || '<div class="root-attention-card ok tone-green"><span class="root-attention-icon"><i class="fa fa-check" aria-hidden="true"></i></span><span class="root-attention-content"><p class="root-attention-label">Pending Alerts</p><p class="root-attention-value">0</p></span></div>');
    $('#root-system-grid').html((s.root_system_health || []).map(rootSystemCard).join('') || '<div class="root-system-card ok"><span class="root-system-icon"><i class="fa fa-check" aria-hidden="true"></i></span><span class="root-system-content"><p class="root-system-label">System</p><p class="root-system-value">Online</p><span class="root-system-detail">No checks returned</span></span><span class="root-system-dot" aria-hidden="true"></span></div>');
    $('#root-activity-tbody').html((s.root_recent_activity || []).map(rootActivityRow).join('') || '<tr><td colspan="4" class="text-center text-muted">No recent activity</td></tr>');
  }

  function renderRetailerHeroSummary(s){
    if (!$('#retailerHeroSummary').length) return;

    var balanceRaw = s.retailer_balance || '0.00';
    var todaySalesRaw = s.today_transaction || '0.00';
    var todayOrders = Number(s.retailer_today_orders || s.total_orders_today || 0);
    var successToday = Number(s.retailer_today_success || 0);
    var pendingToday = Number(s.retailer_today_pending || 0);
    var failedToday = Number(s.retailer_today_failed || 0);
    var totalPending = Number(s.retailer_pending_orders != null ? s.retailer_pending_orders : pendingToday);
    var recentOrder = s.retailer_last_order || null;
    var recentValue = '--';
    var recentMeta = DASH_I18N.no_recent_orders || 'No recent orders';
    var todaySales = dashboardMetricDisplay(todaySalesRaw);
    var pendingMeta = [];
    var performanceMeta = [];
    var alerts = [];
    var alertCount = pendingToday + failedToday;

    if (recentOrder && (recentOrder.id || recentOrder.service_name || recentOrder.product_name)) {
      if (recentOrder.id) {
        recentValue = (DASH_I18N.order || 'Order') + ' #' + recentOrder.id;
      } else {
        recentValue = recentOrder.product_name || recentOrder.service_name || '--';
      }

      recentMeta = [
        recentOrder.product_name || recentOrder.service_name || '',
        retailerStatusText(recentOrder.status || ''),
        recentOrder.date || ''
      ].filter(function(part){
        return !!String(part || '').trim();
      }).join(' | ');
    }

    if (pendingToday > 0) {
      pendingMeta.push(pendingToday + ' ' + (DASH_I18N.status_pending || 'Pending') + ' ' + (DASH_I18N.trend_today || 'today'));
    }
    if (successToday > 0) {
      pendingMeta.push(successToday + ' ' + (DASH_I18N.status_success || 'Success'));
    }
    if (!pendingMeta.length) {
      pendingMeta.push(DASH_I18N.no_recent_orders || 'No recent orders');
    }

    if (successToday > 0) {
      performanceMeta.push(successToday + ' ' + (DASH_I18N.status_success || 'Success'));
    }
    if (pendingToday > 0) {
      performanceMeta.push(pendingToday + ' ' + (DASH_I18N.status_pending || 'Pending'));
    }
    if (failedToday > 0) {
      performanceMeta.push(failedToday + ' ' + (DASH_I18N.status_failed || 'Failed'));
    }
    if (!performanceMeta.length) {
      performanceMeta.push(DASH_I18N.no_recent_orders || 'No recent orders');
    }

    if (pendingToday > 0) {
      alerts.push(retailerHeroAlertChip(pendingToday + ' ' + (DASH_I18N.status_pending || 'Pending') + ' ' + (DASH_I18N.trend_today || 'today'), 'warning', 'fa-clock-o'));
    }
    if (failedToday > 0) {
      alerts.push(retailerHeroAlertChip(failedToday + ' ' + (DASH_I18N.status_failed || 'Failed'), 'danger', 'fa-exclamation-circle'));
    }
    if (!alerts.length && successToday > 0) {
      alerts.push(retailerHeroAlertChip(successToday + ' ' + (DASH_I18N.status_success || 'Success'), 'success', 'fa-check-circle'));
    }
    if (!alerts.length) {
      alerts.push(retailerHeroAlertChip(DASH_I18N.all_clear || 'All clear', 'neutral', 'fa-check'));
    }

    $('#retailerHeroBalance').text(dashboardMetricDisplay(balanceRaw));
    $('#retailerHeroBalanceMeta').text(DASH_I18N.balance_subtitle || 'Real-time account position');
    $('#retailerHeroActivity').text(recentValue);
    $('#retailerHeroActivityMeta').text(recentMeta);
    $('#retailerHeroPending').text(totalPending);
    $('#retailerHeroPendingMeta').text(pendingMeta.join(' | '));
    $('#retailerHeroPerformance').text(todaySales);
    $('#retailerHeroPerformanceMeta').text(performanceMeta.join(' | '));
    $('#retailerHeroAlertCounter').text(alertCount > 0 ? (alertCount + ' ' + (DASH_I18N.alerts || 'alerts')) : (DASH_I18N.all_clear || 'All clear'));
    $('#retailerHeroAlerts').html(alerts.join(''));
    $('#retailerHeroSummaryOrders').text(todayOrders);
    $('#retailerHeroSummarySales').text(todaySales);
    $('#retailerHeroSummarySuccess').text(successToday);
    $('#retailerHeroSummaryPending').text(pendingToday);
  }

  function renderKPIs(s){
    var tiles = [];
    var groupId = Number(s.group_id || 0);

    if (groupId === 1) {
      renderRootDashboard(s);
      return;
    }

    if (groupId === 6) {
      tiles.push(kpiTile('#',                  'fa-history',          'Orders In Progress',  (s.orders_in_progress || 0), ''));
      tiles.push(kpiTile('#',                  'fa-check-circle',     'Closed Orders',       (s.closed_orders || 0),      'green'));
      tiles.push(kpiTile(KPI_LINKS.orders_today,'fa-calendar-check-o','Orders Today',        s.total_orders_today || 0,   'amber'));
      tiles.push(kpiTile(KPI_LINKS.payment_add,'fa-money',            'Payments',            'Add Payments',              ''));
    } else if (groupId === 4) {
      renderRetailerHeroSummary(s);
      var balanceRaw = s.retailer_balance || '0.00';
      var creditLimitRaw = s.retailer_credit_limit || '0.00';
      var dailyLimitRaw = s.retailer_daily_limit || (DASH_I18N.trend_not_set || 'Not set');
      var remainingRaw = s.retailer_remaining_limit || (DASH_I18N.trend_not_set || 'Not set');
      var limitUsedRaw = s.retailer_limit_used || '0.00';
      var todaySalesRaw = s.today_transaction || '0.00';
      var monthSalesRaw = s.total_transaction || '0.00';
      var balanceValue = dashboardMetricDisplay(balanceRaw);
      var creditLimitValue = dashboardMetricDisplay(creditLimitRaw, { absolute: true });
      var dailyLimitValue = dashboardMetricDisplay(dailyLimitRaw);
      var remainingValue = dashboardMetricDisplay(remainingRaw);
      var limitUsedValue = dashboardMetricDisplay(limitUsedRaw);
      var todayOrders = Number(s.retailer_today_orders || s.total_orders_today || 0);
      var monthOrders = Number(s.retailer_month_orders || 0);
      var todaySales = dashboardMetricDisplay(todaySalesRaw);
      var monthSales = dashboardMetricDisplay(monthSalesRaw);
      var balanceNumber = dashboardMetricNumber(balanceRaw);
      var dailyLimitNumber = dashboardMetricNumber(dailyLimitRaw);
      var remainingNumber = dashboardMetricNumber(remainingRaw);
      var balanceDanger = balanceNumber < 0;
      var remainingDanger = dailyLimitNumber > 0 && remainingNumber <= 0;
      var successToday = Number(s.retailer_today_success || 0);
      var pendingToday = Number(s.retailer_today_pending || 0);
      var balanceSubtitle = DASH_I18N.available_funds || 'Available funds';
      var dailySubtitle = dailyLimitNumber > 0
        ? dashboardTrendTemplate(DASH_I18N.trend_used || ':value used', limitUsedValue)
        : (DASH_I18N.daily_limit_subtitle || 'Spending cap for the day');
      var remainingSubtitle = dailyLimitNumber > 0
        ? dashboardTrendTemplate(DASH_I18N.trend_remaining || ':value available', remainingValue)
        : (DASH_I18N.remaining_today_subtitle || 'Available limit left today');
      var ordersSubtitle = pendingToday > 0
        ? pendingToday + ' ' + (DASH_I18N.status_pending || 'Pending') + ' ' + (DASH_I18N.trend_today || 'today')
        : (DASH_I18N.orders_today_subtitle || 'Orders created today');
      var salesSubtitle = successToday > 0
        ? successToday + ' ' + (DASH_I18N.status_success || 'Success')
        : (DASH_I18N.todays_sales_subtitle || 'Revenue captured today');

      tiles.push(kpiTile(KPI_LINKS.profile, 'fa-wallet', DASH_I18N.balance || 'Current balance', balanceValue, balanceDanger ? 'red' : (balanceNumber > 0 ? 'green' : 'blue'), {
        danger: balanceDanger,
        positive: balanceNumber > 0,
        subtitle: balanceSubtitle,
        iconClass: 'kpi-icon--metric'
      }));
      tiles.push(kpiTile(KPI_LINKS.profile, 'fa-credit-card', DASH_I18N.credit_limit || 'Credit limit', creditLimitValue, 'blue', {
        subtitle: DASH_I18N.credit_limit_subtitle || 'Maximum allowed credit',
        iconClass: 'kpi-icon--metric'
      }));
      tiles.push(kpiTile(KPI_LINKS.limits, 'fa-calendar', DASH_I18N.daily_limit || 'Daily limit', dailyLimitValue, 'amber', {
        subtitle: dailySubtitle,
        iconClass: 'kpi-icon--metric'
      }));
      tiles.push(kpiTile(KPI_LINKS.limits, 'fa-check-circle', DASH_I18N.remaining_today || 'Remaining today', remainingValue, remainingDanger ? 'red' : (remainingNumber > 0 ? 'green' : 'blue'), {
        danger: remainingDanger,
        positive: remainingNumber > 0,
        subtitle: remainingSubtitle,
        iconClass: 'kpi-icon--metric'
      }));
      tiles.push(kpiTile(KPI_LINKS.orders_today, 'fa-shopping-basket', DASH_I18N.orders_today || 'Orders today', todayOrders, 'green', {
        subtitle: ordersSubtitle,
        iconClass: 'kpi-icon--metric'
      }));
      tiles.push(kpiTile(KPI_LINKS.todays_amount, 'fa-line-chart', DASH_I18N.todays_sales || "Today's sales", todaySales, dashboardMetricNumber(todaySales) > 0 ? 'green' : 'blue', {
        positive: dashboardMetricNumber(todaySales) > 0,
        subtitle: salesSubtitle,
        iconClass: 'kpi-icon--metric'
      }));
      tiles.push(kpiTile(KPI_LINKS.month_amount, 'fa-area-chart', DASH_I18N.this_month || 'This month', monthSales, 'blue', {
        positive: dashboardMetricNumber(monthSales) > 0,
        subtitle: DASH_I18N.this_month_subtitle || 'Month-to-date revenue',
        iconClass: 'kpi-icon--metric'
      }));
      tiles.push(kpiTile(KPI_LINKS.orders, 'fa-list-ol', DASH_I18N.month_orders || 'Month orders', monthOrders, 'amber', {
        subtitle: DASH_I18N.month_orders_subtitle || 'Orders completed this month',
        iconClass: 'kpi-icon--metric'
      }));
    } else if (groupId === 2 || groupId === 3) {
      tiles.push(kpiTile(KPI_LINKS.admin_users,          'fa-users',        'Total Resellers',      (s.total_resellers || 0), ''));
      tiles.push(kpiTile(KPI_LINKS.orders_today,         'fa-list-ol',      'Total Orders',         (s.total_orders || 0),   'green'));
      tiles.push(kpiTile(KPI_LINKS.todays_amount,        'fa-wallet',           "Today's Transaction", s.today_transaction || '0.00', 'amber'));
      tiles.push(kpiTile(KPI_LINKS.month_amount,         'fa-history',      'This Month',           s.total_transaction || '0.00', ''));
    } else {
      // Admin/default dashboard
      tiles.push(kpiTile(KPI_LINKS.month_amount,    'fa-line-chart',       'This Month',          s.total_transaction,             ''));
      tiles.push(kpiTile(KPI_LINKS.orders_today,    'fa-calendar-check-o', 'Orders Today',        s.total_orders_today,            'green'));
      tiles.push(kpiTile(KPI_LINKS.todays_amount,   'fa-wallet',           "Today's Transaction", s.today_transaction,             'amber'));
      tiles.push(kpiTile(KPI_LINKS.payment_add,     'fa-money',            'Payments',            'Add Payments',                  ''));
      tiles.push(
        kpiTile(
          KPI_LINKS.failed_callback,
          'fa-exclamation-triangle',
          'View Failed Callback',
          (s.failed_callbacks_count != null ? (s.failed_callbacks_count + ' failed') : 'Failed transaction'),
          'amber'
        )
      );
      tiles.push(
        kpiTile(
          KPI_LINKS.manage_routes,
          'fa-sitemap',
          'Manage Routes',
          (s.routes_count != null ? (s.routes_count + ' routes') : 'Routing table'),
          ''
        )
      );
    }

    $('#kpi-grid').html(tiles.join(''));
  }


  function renderBanners(banners){
    if (!$('#banner-slides').length) return;
    var requestId = ++bannerRenderRequest;
    if (!banners.length) {
      hideDashboardBanner();
      return;
    }
    var base = @json(asset('images'));
    var loadedBanners = [];
    var pending = banners.length;

    function finishBannerLoad(){
      pending -= 1;
      if (pending > 0 || requestId !== bannerRenderRequest) return;

      loadedBanners.sort(function(a, b){ return a.index - b.index; });
      if (!loadedBanners.length) {
        $('#banner-slides').empty();
        hideDashboardBanner();
        return;
      }

      var slides = loadedBanners.map(function(item, i){
        return (
          '<div class="carousel-item '+(i===0?'active':'')+'">' +
            '<img src="'+esc(item.url)+'" class="d-block w-100" alt="'+esc(item.title)+'">' +
            (item.title ? '<div class="carousel-caption">'+esc(item.title)+'</div>' : '') +
          '</div>'
        );
      }).join('');

      $('#banner-slides').html(slides);
      $('#bannerCarousel .carousel-control-prev, #bannerCarousel .carousel-control-next').toggle(loadedBanners.length > 1);
      retailerBannerHasSlides = true;
      showDashboardBanner();

      var el = document.getElementById('bannerCarousel');
      if (el && window.bootstrap && bootstrap.Carousel) {
        new bootstrap.Carousel(el, { interval: 5000, pause: 'hover' });
      }
      scheduleRetailerBannerSwap();
    }

    banners.forEach(function(b, i){
      var imgName = b && b.banner ? b.banner : 'banner/banner_default_image.png';
      var title = b && b.title ? b.title : '';
      var image = new window.Image();
      var url = base + '/' + String(imgName).replace(/^\/+/, '');

      image.onload = function(){
        if (image.naturalWidth > 0 && image.naturalHeight > 0) {
          loadedBanners.push({ index: i, title: title, url: url });
        }
        finishBannerLoad();
      };
      image.onerror = finishBannerLoad;
      image.src = url;
    });
  }


  /* -------- Latest Orders -------- */
  var LATEST_ORDERS_LIMIT = 10;
  var retailerOrdersState = {
    rows: [],
    page: 1,
    perPage: LATEST_ORDERS_LIMIT
  };

  function retailerStatusKey(status){
    var s = String(status || '').toLowerCase();
    if (s.indexOf('failed') > -1 || s.indexOf('fail') > -1 || s.indexOf('cancel') > -1 || s.indexOf('reject') > -1 || s.indexOf('refund') > -1 || s.indexOf('error') > -1) return 'failed';
    if (s.indexOf('pending') > -1 || s.indexOf('progress') > -1 || s.indexOf('accepted') > -1 || s.indexOf('processing') > -1 || s.indexOf('placed') > -1 || s.indexOf('open') > -1) return 'pending';
    if (s.indexOf('success') > -1 || s.indexOf('complete') > -1 || s.indexOf('closed') > -1 || s.indexOf('done') > -1) return 'success';
    return s ? 'success' : '';
  }

  function statusClass(s){
    var key = retailerStatusKey(s);
    if(key === 'success') return 'success';
    if(key === 'pending') return 'warning';
    if(key === 'failed') return 'danger';
    return 'success';
  }

  function retailerStatusText(status){
    var key = retailerStatusKey(status);
    if (key === 'success') return DASH_I18N.status_success || 'Success';
    if (key === 'pending') return DASH_I18N.status_pending || 'Pending';
    if (key === 'failed') return DASH_I18N.status_failed || 'Failed';
    return status || '-';
  }

  function retailerDateText(value){
    var text = String(value || '');
    var match = text.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}:\d{2}(?::\d{2})?)/);
    if (!match) return text;
    var months = DASH_I18N.months_short || ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'Jun.', 'Jul.', 'Aug.', 'Sep.', 'Oct.', 'Nov.', 'Dec.'];
    var monthIndex = Math.max(0, Math.min(11, Number(match[2]) - 1));
    return Number(match[3]) + ' ' + months[monthIndex] + ' ' + match[1] + ', ' + match[4];
  }

  function retailerOrderDateValue(value){
    var match = String(value || '').match(/^(\d{4}-\d{2}-\d{2})/);
    return match ? match[1] : '';
  }

  function retailerAmountNumber(value){
    var text = String(value == null ? '' : value).replace(/\s/g, '').replace(/[^\d,.-]/g, '');
    if (text.indexOf(',') > -1 && text.indexOf('.') === -1) {
      text = text.replace(',', '.');
    } else {
      text = text.replace(/,/g, '');
    }
    var number = Number(text);
    return isNaN(number) ? 0 : number;
  }

  function retailerFormatAmount(value){
    var number = retailerAmountNumber(value);
    return '\u20ac' + number.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function retailerOrderSearchText(row){
    return [
      row.id,
      row.date,
      row.username,
      row.tt_operator,
      row.product_name,
      row.service_name,
      row.order_amount,
      row.status
    ].join(' ').toLowerCase();
  }

  function populateRetailerServiceFilter(rows){
    var $service = $('#retailer-order-service');
    if (!$service.length) return;
    var selected = $service.val() || '';
    var seen = {};
    var options = ['<option value="">'+esc(DASH_I18N.all_services || 'All services')+'</option>'];
    rows.forEach(function(row){
      var service = String(row.service_name || '').trim();
      if (!service || seen[service]) return;
      seen[service] = true;
      options.push('<option value="'+esc(service)+'">'+esc(service)+'</option>');
    });
    $service.html(options.join(''));
    if (selected && seen[selected]) {
      $service.val(selected);
    }
  }

  function retailerOrderFilters(){
    return {
      search: String($('#retailer-order-search').val() || '').toLowerCase().trim(),
      status: String($('#retailer-order-status').val() || '').toLowerCase(),
      service: String($('#retailer-order-service').val() || ''),
      from: String($('#retailer-order-from').val() || ''),
      to: String($('#retailer-order-to').val() || '')
    };
  }

  function filteredRetailerOrders(){
    var filters = retailerOrderFilters();
    return retailerOrdersState.rows.filter(function(row){
      var dateValue = retailerOrderDateValue(row.date);
      if (filters.search && retailerOrderSearchText(row).indexOf(filters.search) === -1) return false;
      if (filters.status && retailerStatusKey(row.status) !== filters.status) return false;
      if (filters.service && String(row.service_name || '') !== filters.service) return false;
      if (filters.from && dateValue && dateValue < filters.from) return false;
      if (filters.to && dateValue && dateValue > filters.to) return false;
      return true;
    });
  }

  function retailerOrderUrl(row){
    return KPI_LINKS.orders + (row && row.id ? '?user=' + encodeURIComponent(row.id) : '');
  }

  function retailerOrdersEmptyRow(message){
    return (
      '<tr class="retailer-orders-empty-row">'+
        '<td colspan="6">'+
          '<div class="retailer-orders-empty">'+
            '<i class="fa fa-inbox" aria-hidden="true"></i>'+
            '<span>'+esc(message)+'</span>'+
          '</div>'+
        '</td>'+
      '</tr>'
    );
  }

  function retailerOrderRow(row){
    var rowDate = DASH_IS_RETAILER ? retailerDateText(row.date) : row.date;
    var rowStatus = DASH_IS_RETAILER ? retailerStatusText(row.status) : row.status;
    var status = statusClass(row.status);
    var product = row.tt_operator || row.product_name || '-';
    return (
      '<tr class="retailer-order-row" tabindex="0" role="link" data-order-url="'+esc(retailerOrderUrl(row))+'">'+
        '<td>'+esc(rowDate)+'</td>'+
        '<td>'+esc(row.username || '-')+'</td>'+
        '<td>'+esc(product)+'</td>'+
        '<td>'+esc(row.service_name || '-')+'</td>'+
        '<td><span class="retailer-order-amount">'+esc(retailerFormatAmount(row.order_amount))+'</span></td>'+
        '<td><span class="label label-'+status+'">'+esc(rowStatus || '-')+'</span></td>'+
      '</tr>'
    );
  }

  function retailerOrdersMeta(from, to, total){
    if (!total) return '';
    var text = DASH_I18N.showing_orders || 'Showing :from-:to of :total orders';
    return text.replace(':from', from).replace(':to', to).replace(':total', total);
  }

  function renderRetailerOrdersPagination(total, totalPages){
    var $pagination = $('#orders-pagination');
    if (!$pagination.length) return;
    if (totalPages <= 1) {
      $pagination.empty();
      return;
    }
    var current = retailerOrdersState.page;
    var start = Math.max(1, current - 2);
    var end = Math.min(totalPages, start + 4);
    start = Math.max(1, end - 4);
    var html = [
      '<button type="button" class="retailer-orders-page-btn" data-page="'+(current - 1)+'" '+(current <= 1 ? 'disabled' : '')+'>'+esc(DASH_I18N.previous || 'Previous')+'</button>'
    ];
    for (var page = start; page <= end; page++) {
      html.push('<button type="button" class="retailer-orders-page-btn '+(page === current ? 'active' : '')+'" data-page="'+page+'">'+page+'</button>');
    }
    html.push('<button type="button" class="retailer-orders-page-btn" data-page="'+(current + 1)+'" '+(current >= totalPages ? 'disabled' : '')+'>'+esc(DASH_I18N.next || 'Next')+'</button>');
    $pagination.html(html.join(''));
  }

  function renderRetailerOrdersTable(){
    if (!$('#orders-tbody').length) return;
    var filtered = filteredRetailerOrders();
    var total = filtered.length;
    var totalPages = Math.max(1, Math.ceil(total / retailerOrdersState.perPage));
    retailerOrdersState.page = Math.max(1, Math.min(retailerOrdersState.page, totalPages));
    var start = (retailerOrdersState.page - 1) * retailerOrdersState.perPage;
    var pageRows = filtered.slice(start, start + retailerOrdersState.perPage);
    var emptyMessage = retailerOrdersState.rows.length ? (DASH_I18N.no_filtered_orders || 'No orders match the selected filters') : (DASH_I18N.no_recent_orders || 'No recent orders');

    $('#orders-tbody').html(pageRows.map(retailerOrderRow).join('') || retailerOrdersEmptyRow(emptyMessage));
    $('#orders-results-meta').text(total ? retailerOrdersMeta(start + 1, start + pageRows.length, total) : '');
    $('#retailer-order-export').prop('disabled', !total);
    renderRetailerOrdersPagination(total, totalPages);
  }

  function exportRetailerOrders(){
    var rows = filteredRetailerOrders();
    if (!rows.length) return;
    var headers = ['Date', 'User', 'Product', 'Service', 'Amount', 'Status'];
    function csvCell(value){
      return '"' + String(value == null ? '' : value).replace(/"/g, '""') + '"';
    }
    var lines = [headers.map(csvCell).join(',')].concat(rows.map(function(row){
      return [
        row.date,
        row.username,
        row.tt_operator || row.product_name || '-',
        row.service_name,
        retailerFormatAmount(row.order_amount),
        retailerStatusText(row.status)
      ].map(csvCell).join(',');
    }));
    var blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = 'latest-orders.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  }

  function loadOrders(){
    if (!$('#orders-tbody').length) return;
    dashboardFetch('/dashboard/orders', { per_page: LATEST_ORDERS_LIMIT, page: 1 }, {
      keepOld: true,
      onData: function(res){
        var orderRows = res.data || [];
        retailerOrdersState.rows = orderRows;
        retailerOrdersState.page = 1;
        populateRetailerServiceFilter(orderRows);
        renderRetailerHeroTransactions(orderRows);
        renderRetailerOrdersTable();
        if ($('#retailer-last-order').length && $('#retailer-last-order .retailer-empty').length && orderRows.length) {
          $('#retailer-last-order').html(retailerLastOrderCard(latestOrderRowToRetailerCard(orderRows[0])));
        }
      }
    });
  }

  $('#retailer-order-search').on('input', function(){
    retailerOrdersState.page = 1;
    renderRetailerOrdersTable();
  });
  $('#retailer-order-status, #retailer-order-service, #retailer-order-from, #retailer-order-to').on('change', function(){
    retailerOrdersState.page = 1;
    renderRetailerOrdersTable();
  });
  $('#retailer-order-export').on('click', exportRetailerOrders);
  $('#orders-pagination').on('click', 'button[data-page]', function(){
    var page = Number($(this).data('page'));
    if (!page || $(this).prop('disabled')) return;
    retailerOrdersState.page = page;
    renderRetailerOrdersTable();
  });
  $('#orders-tbody').on('click', 'tr[data-order-url]', function(){
    window.location.href = $(this).data('order-url');
  });
  $('#orders-tbody').on('keydown', 'tr[data-order-url]', function(event){
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      window.location.href = $(this).data('order-url');
    }
  });
  deferDashboardTask(loadOrders, 180);

  /* Root usergroup 1 is setup/control only; no transaction chart or provider balance calls. */
  @if(false)
  if (document.getElementById('monthlyTransactionsChart')) {
  $.getJSON('/dashboard/monthly-transactions')
   .done(function(d){
      $('#chart-skel').hide(); $('#monthlyTransactionsChart').show();
      var ctx = document.getElementById('monthlyTransactionsChart').getContext('2d');
      new Chart(ctx, {
        type:'line',
        data:{
          labels:d.labels,
          datasets:[{
            label:'Monthly Transactions',
            data:d.data,
            borderWidth:2, tension:.25, pointRadius:2, fill:true,
            backgroundColor:'rgba(23,100,168,0.12)', borderColor:'rgba(23,100,168,1)'
          }]
        },
        options:{
          responsive:true, maintainAspectRatio:false,
          interaction:{ mode:'index', intersect:false },
          plugins:{ legend:{ display:false } },
          scales:{
            x:{ ticks:{ maxRotation:0, autoSkip:true } },
            y:{ beginAtZero:true, ticks:{ callback:function(v){ return "€ "+Number(v).toLocaleString(); } } }
          }
        }
      });
   });
  }

  function setBalances(b){
    function euro(v){ if (v==null || v==='') return '€ —'; var n=Number(v); if(isNaN(n)) return '€ —'; return '€ '+n.toLocaleString(); }
    if (b.reloadly) $('#val-reloadly').text(euro(b.reloadly.value)).closest('.kpi-tile').css('opacity', (!b.reloadly.ok ? .6 : 1));
    if (b.ding)     $('#val-ding').text(euro(b.ding.value)).closest('.kpi-tile').css('opacity', (!b.ding.ok ? .6 : 1));
    if (b.transfer) $('#val-transfer').text(euro(b.transfer.value)).closest('.kpi-tile').css('opacity', (!b.transfer.ok ? .6 : 1));
  }
  $.getJSON('/dashboard/balances', { mode:'cache' }).done(setBalances);
  $.getJSON('/dashboard/balances', { mode:'refresh' }).done(setBalances);
  @endif

})(jQuery);
</script>

<script>
(function($){
  var RangeState = { mode: 'today', from: null, to: null, label: 'Today' };
  var listeners = [];
  function notify(){
    var params = {};
    if (RangeState.mode === 'custom' && (RangeState.from || RangeState.to)) {
      if (RangeState.from) params.from = RangeState.from;
      if (RangeState.to)   params.to   = RangeState.to;
    } else { params.range = RangeState.mode; }
    listeners.forEach(function(fn){ try { fn(params, RangeState); } catch(e){} });
    $('#global-range-label').text(RangeState.label);
  }
  function setPreset(preset){
    RangeState.mode  = preset; RangeState.from = null; RangeState.to = null;
    var map = {today:'Today', last_week:'Last Week', last_month:'Last Month', last_3_months:'Last 3 Months', last_6_months:'Last 6 Months', custom:'Custom'};
    RangeState.label = map[preset] || 'Custom'; notify();
  }
  function setCustom(from, to){
    RangeState.mode = 'custom';
    var today = (new Date()).toISOString().slice(0,10);
    RangeState.from = from || to || today;
    RangeState.to   = to   || from || today;
    RangeState.label = (RangeState.from || '—')+' → '+(RangeState.to || '—');
    notify();
  }
  window.registerRangeListener = function(fn){ if (typeof fn === 'function') { listeners.push(fn); } notify(); };

  var $grp = $('#global-range-group');

  // Modal instance for BS5
  var modalEl = document.getElementById('customRangeModal');
  var customRangeModal = null;
  function ensureModal(){ if (!customRangeModal && window.bootstrap?.Modal) { customRangeModal = bootstrap.Modal.getOrCreateInstance(modalEl); } }

  $grp.on('click','button[data-range]', function(){
    $grp.find('button').removeClass('active'); $(this).addClass('active');
    var key = $(this).data('range');
    if (key === 'custom') {
      // Prefill + enforce rules before showing
      $('#custom-from').val(RangeState.from || '').removeClass('is-invalid');
      $('#custom-to').val(RangeState.to || '').prop('disabled', !RangeState.from).removeClass('is-invalid');
      if (RangeState.from) { $('#custom-to').attr('min', RangeState.from); }
      toggleApply();
      ensureModal(); customRangeModal.show();
    } else { setPreset(key); }
  });

  $('#btn-global-refresh').on('click', function(){ notify(); });

  // --- Custom Range Modal logic (strict rules) ---
  var $from = $('#custom-from');
  var $to   = $('#custom-to');
  var $apply= $('#apply-custom-range');

  function setInvalid($input, on, msgSel){
    $input.toggleClass('is-invalid', !!on);
    if (msgSel) $(msgSel).toggle(!!on);
  }
  function toggleApply(){
    var f = $from.val(), t = $to.val();
    // Enable Apply only if: (a) From is set, and (b) To empty or To >= From
    var ok = !!f && (!t || t >= f);
    $apply.prop('disabled', !ok);
  }

  // Require FROM first
  $from.on('change input', function(){
    var f = $from.val();
    if (f) {
      $to.prop('disabled', false).attr('min', f);
      setInvalid($from, false, '#from-help');
      // if existing To < From, clear To & show invalid briefly
      if ($to.val() && $to.val() < f) {
        $to.val('');
        setInvalid($to, true, '#to-help');
        // auto-hide after short delay so user can continue smoothly
        setTimeout(function(){ setInvalid($to, false, '#to-help'); }, 1500);
      }
    } else {
      $to.prop('disabled', true).val('').removeAttr('min');
      setInvalid($from, true, '#from-help');
    }
    toggleApply();
  });

  $to.on('change input', function(){
    var f = $from.val(), t = $to.val();
    if (!f) {
      // Guard: should never happen as TO is disabled until FROM
      $to.val('').prop('disabled', true);
      setInvalid($from, true, '#from-help');
    } else if (t && t < f) {
      setInvalid($to, true, '#to-help');
      $to.val('');
      // keep invalid state briefly for feedback
      setTimeout(function(){ setInvalid($to, false, '#to-help'); }, 1500);
    } else {
      setInvalid($to, false, '#to-help');
    }
    toggleApply();
  });

  $('#apply-custom-range').on('click', function(){
    var f = $from.val() || null;
    var t = $to.val()   || null;

    // If only one is provided, mirror to the other
    if (!f && t) f = t;
    if (f && !t) t = f;

    // Final guard: swap if out-of-order (shouldn’t occur due to min/validation)
    if (f && t && f > t) { var tmp=f; f=t; t=tmp; }

    setCustom(f, t);
    ensureModal(); customRangeModal.hide();
    $grp.find('button').removeClass('active');
    $('#btn-custom-range').addClass('active');
  });

  // Initialize default preset
  setPreset('today');
})(jQuery);
</script>

@if($isRetailer || $showMonthlyChart || $showServiceChart || $showTopupHealth || $showMargin || $showTopOps)
  @include('v2.dashboard.partials.chart-scripts')
@endif

{{-- Strict date logic: require FROM first + prevent earlier TO --}}
<script>
  $(document).ready(function () {
    // (Handled inside the main range script above)
    // Keeping this block empty intentionally to avoid duplicate handlers.
  });
</script>
