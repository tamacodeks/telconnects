@extends('v2.layout.simple.master')

@php
    $priceListText = trans('cc_price_lists');
    $isRetailerPriceList = $isRetailerPriceList ?? false;
    $rateGroups = $rate_groups ?? collect();
    $rateGroupCount = method_exists($rateGroups, 'count') ? $rateGroups->count() : 0;
    $defaultRateGroupId = !$isRetailerPriceList && $rateGroupCount > 0 ? optional($rateGroups->first())->id : '';
    $page_title = $priceListText['page_title'];
    $priceListPartialData = [
        'priceListText' => $priceListText,
        'isRetailerPriceList' => $isRetailerPriceList,
        'rateGroups' => $rateGroups,
        'defaultRateGroupId' => $defaultRateGroupId,
    ];
    $priceListCssVersion = @filemtime(public_path('assets/css/cc-price-lists-v2.css')) ?: time();
    $priceListJsVersion = @filemtime(public_path('assets/js/cc-price-lists-v2.js')) ?: time();
    $priceListBreadcrumb = [
        'data' => [
            ['name' => $priceListText['page_title'], 'url' => '', 'active' => 'yes'],
        ],
    ];
    $ccPriceListJsConfig = [
        'mode' => $isRetailerPriceList ? 'retailer' : 'manager',
        'fetchUrl' => $isRetailerPriceList ? route('my.cc-price-lists.v2.fetch') : route('cc-price-lists.v2.fetch'),
        'updateUrl' => route('cc-price-lists.v2.update'),
        'csrfToken' => csrf_token(),
        'defaultGroupId' => (string) $defaultRateGroupId,
        'labels' => [
            'processing' => trans('common.processing'),
            'records10' => '10 ' . trans('users.records'),
            'records25' => '25 ' . trans('users.records'),
            'records50' => '50 ' . trans('users.records'),
            'showAll' => trans('users.show_all'),
            'close' => trans('common.btn_close'),
            'updateFailed' => $priceListText['update_failed'],
            'export' => $priceListText['export'],
            'refresh' => $priceListText['refresh'],
            'amountMust' => trans('myservice.amount_must_message'),
            'currencySymbol' => $priceListText['currency_symbol'],
            'priceLabel' => $priceListText['price_label'],
            'noPrices' => $priceListText['no_prices'],
            'noPricesHint' => $priceListText['no_prices_hint'],
            'pageStatus' => $priceListText['page_status'],
            'previous' => $priceListText['previous'],
            'next' => $priceListText['next'],
        ],
    ];
    $priceListPage = [
        'classPrefix' => 'ccpl-v2',
        'showHeader' => !$isRetailerPriceList,
        'wrapperAttributes' => [
            'data-mode' => $isRetailerPriceList ? 'retailer' : 'manager',
        ],
        'kickerIcon' => 'fa fa-list-ul',
        'kickerText' => $isRetailerPriceList ? $priceListText['mode_retailer'] : $priceListText['mode_manager'],
        'title' => $priceListText['title'],
        'subtitle' => $isRetailerPriceList ? $priceListText['retailer_subtitle'] : $priceListText['subtitle'],
        'headerActionsView' => $isRetailerPriceList ? null : 'v2.myservice.calling-cards.rate-tables.partials.header-actions',
        'headerActionsData' => $priceListPartialData,
        'stats' => $isRetailerPriceList ? [] : [
            [
                'label' => $priceListText['active_table'],
                'value' => $priceListText['mode_manager'],
            ],
            [
                'label' => $priceListText['rate_groups'],
                'value' => number_format($rateGroupCount),
            ],
            [
                'label' => $priceListText['editable_prices'],
                'value' => $priceListText['yes'],
            ],
        ],
        'panelId' => 'ccplV2Loader',
        'panelTitle' => $priceListText['table_title'],
        'panelSubtitle' => $isRetailerPriceList ? $priceListText['retailer_table_subtitle'] : $priceListText['table_subtitle'],
        'panelActionsView' => 'v2.myservice.calling-cards.rate-tables.partials.table-actions',
        'panelActionsData' => $priceListPartialData,
        'toolbarView' => 'v2.myservice.calling-cards.rate-tables.partials.filters',
        'toolbarData' => $priceListPartialData,
        'bodyView' => 'v2.myservice.calling-cards.rate-tables.partials.table',
        'bodyData' => $priceListPartialData,
    ];
@endphp

@section('body_class', 'cc-price-lists-v2-page')

@section('style')
    @if(!$isRetailerPriceList)
        <link href="{{ asset('vendor/datatables/datatables.css') }}" rel="stylesheet">
        <link href="{{ asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    @endif
    <link href="{{ asset('assets/css/cc-price-lists-v2.css') }}?v={{ $priceListCssVersion }}&theme=auto" rel="stylesheet">
@endsection

@include('v2.layout.simple.breadcrumb', $priceListBreadcrumb)

@section('content')
    @include('v2.common.resource-table-page', $priceListPage)
@endsection

@section('script')
    @if(!$isRetailerPriceList)
        <script src="{{ asset('vendor/datatables/datatables.js') }}"></script>
        <script src="{{ asset('vendor/datatables/app.js') }}"></script>
        <script src="{{ asset('vendor/select-picker/js/bootstrap-select.js') }}"></script>
    @endif
    <script>
        window.ccPriceListsV2 = {!! json_encode($ccPriceListJsConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    </script>
    <script src="{{ asset('assets/js/cc-price-lists-v2.js') }}?v={{ $priceListJsVersion }}&ui=retailer-cards"></script>
@endsection
