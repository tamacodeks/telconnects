@extends('v2.layout.simple.master')

@section('title', 'Dashboard')

@section('style')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/common.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/v2-dashboard.css') }}?v={{ @filemtime(public_path('assets/css/v2-dashboard.css')) ?: time() }}">
@endsection

@php
  $dashboardAuthUser = auth()->user();
  $dashboardIsRetailer = ((int) optional($dashboardAuthUser)->group_id === 4);
  $dashboardLocale = session('locale', 'en') === 'fr' ? 'fr' : 'en';
  app()->setLocale($dashboardLocale);
  $retailerText = trans('dashboard.retailer');

  if (!is_array($retailerText)) {
    app()->setLocale('en');
    $retailerText = trans('dashboard.retailer');
    app()->setLocale($dashboardLocale);
  }

  $retailerCopyOverrides = [
    'welcome_prefix' => defined('DASHBOARD_WELCOME_PREFIX') ? trim((string) constant('DASHBOARD_WELCOME_PREFIX')) : '',
    'welcome_subtitle' => defined('DASHBOARD_WELCOME_SUBTITLE') ? trim((string) constant('DASHBOARD_WELCOME_SUBTITLE')) : '',
    'feature_secure' => defined('DASHBOARD_FEATURE_SECURE') ? trim((string) constant('DASHBOARD_FEATURE_SECURE')) : '',
    'feature_instant' => defined('DASHBOARD_FEATURE_INSTANT') ? trim((string) constant('DASHBOARD_FEATURE_INSTANT')) : '',
    'feature_support' => defined('DASHBOARD_FEATURE_SUPPORT') ? trim((string) constant('DASHBOARD_FEATURE_SUPPORT')) : '',
  ];

  foreach ($retailerCopyOverrides as $copyKey => $copyValue) {
    if ($copyValue !== '') {
      $retailerText[$copyKey] = $copyValue;
    }
  }

  if ($dashboardIsRetailer) {
    $page_title = $retailerText['page_title'];
  }
@endphp
@section('body_class', trim('dashboard-v2-page ' . ($dashboardIsRetailer ? 'retailer-dashboard-page' : '')))
@include('v2.layout.simple.breadcrumb', ['data' => [], 'show_current_breadcrumb' => false])

@php
  $isSuperAdmin = ((int) optional($dashboardAuthUser)->group_id === 1);
  $isRetailer = $dashboardIsRetailer;
  $dashboardUserName = optional($dashboardAuthUser)->username ?: optional($dashboardAuthUser)->name ?: 'User';
  $showBanners       = $ui['show_banners']       ?? true;
  $showBalances      = $ui['show_balances']      ?? true;
  $showKPIs          = $ui['show_kpis']          ?? true;
  $showMonthlyChart  = $ui['show_monthly_chart'] ?? true;
  $showGlobalRange   = $ui['show_global_range']  ?? true;
  $showTopOps        = $ui['show_top_ops']       ?? true;
  $showServiceChart  = $ui['show_service_chart'] ?? true;
  $showTopupHealth   = $ui['show_topup_health']  ?? true;
  $showMargin        = $ui['show_margin']        ?? true;
  $showLatestOrders  = $ui['show_latest_orders'] ?? true;
@endphp
@section('content')
@include('v2.dashboard.partials.content')
@endsection

@section('script')
@include('v2.dashboard.partials.scripts')
@endsection
