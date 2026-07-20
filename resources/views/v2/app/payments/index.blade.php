@extends('v2.layout.simple.master')

@php
    $historyText = trans('v2_history');
    $historyText['payments'] = $historyText['payments'] ?? [
        'page_title' => 'Payments',
        'panel_title' => 'Payments',
        'panel_subtitle' => 'Search and review payment history.',
    ];
    $historyText['filters']['service'] = $historyText['filters']['retailer'] ?? 'Retailer';
    $historyText['filters']['all_services'] = $historyText['filters']['all_retailers'] ?? 'Select a retailer';
    $historyText['filters']['search_placeholder'] = $historyText['filters']['payment_search_placeholder'] ?? 'User, customer ID, comment';
    $historyText['js']['empty_title'] = $historyText['js']['payment_empty_title'] ?? 'No payments found';
    $historyText['js']['empty_description'] = $historyText['js']['payment_empty_description'] ?? 'Try another date range, retailer, or search term.';
    $page_title = $historyText['payments']['page_title'];
    $historyCssVersion = @filemtime(public_path('assets/css/v2-history.css')) ?: time();
    $historyJsVersion = @filemtime(public_path('assets/js/v2-history.js')) ?: time();
    $historyPartialData = [
        'historyText' => $historyText,
        'historyType' => 'payments',
        'retailers' => $retailers ?? collect(),
        'services' => collect(),
        'fromDate' => $from_date ?? '',
        'toDate' => $to_date ?? '',
        'searchValue' => old('query', request()->input('query')),
        'canSeeCost' => false,
    ];
    $historyBreadcrumb = [
        'data' => [
            ['name' => $historyText['payments']['page_title'], 'url' => '', 'active' => 'yes'],
        ],
    ];
    $historyConfig = [
        'type' => 'payments',
        'fetchUrl' => route('payments.v2.fetch'),
        'csrfToken' => csrf_token(),
        'locale' => app()->getLocale(),
        'defaultFromDate' => $from_date ?? '',
        'defaultToDate' => $to_date ?? '',
        'columns' => $historyText['columns'],
        'labels' => $historyText['js'] + [
            'processing' => trans('common.processing'),
            'records10' => '10 ' . ($historyText['actions']['records'] ?? 'records'),
            'records25' => '25 ' . ($historyText['actions']['records'] ?? 'records'),
            'records50' => '50 ' . ($historyText['actions']['records'] ?? 'records'),
            'showAll' => $historyText['actions']['show_all'] ?? 'Show all',
            'dateError' => $historyText['filters']['date_error'] ?? 'Select a valid date range.',
            'servicesAll' => $historyText['filters']['all_services'] ?? 'Select a retailer',
            'servicesAllSelected' => $historyText['filters']['all_selected'] ?? 'All',
            'servicesSelected' => $historyText['filters']['selected_services'] ?? 'selected',
            'selectAll' => $historyText['filters']['select_all'] ?? 'Select all',
            'clear' => $historyText['filters']['deselect_all'] ?? 'Clear',
            'resetFilters' => $historyText['filters']['reset'] ?? 'Reset filters',
            'emptyTitle' => $historyText['js']['empty_title'] ?? 'No payments found',
            'emptyDescription' => $historyText['js']['empty_description'] ?? 'Try another date range, retailer, or search term.',
            'info' => 'Showing _START_ to _END_ of _TOTAL_ lines',
            'infoEmpty' => 'No lines to show',
            'infoFiltered' => '(filtered from _MAX_ total lines)',
            'previous' => 'Previous',
            'next' => 'Next',
            'downloadExcel' => trans('common.download_as_excel'),
            'refresh' => $historyText['actions']['refresh'],
        ],
    ];
    $historyPage = [
        'classPrefix' => 'v2-history',
        'wrapperAttributes' => [
            'data-history-page' => 'payments',
        ],
        'showHeader' => false,
        'panelId' => 'v2HistoryPanel',
        'panelTitle' => $historyText['payments']['panel_title'],
        'panelSubtitle' => $historyText['payments']['panel_subtitle'],
        'panelActionsView' => 'v2.app.history.partials.actions',
        'panelActionsData' => $historyPartialData,
        'toolbarView' => 'v2.app.history.partials.filters',
        'toolbarData' => $historyPartialData,
        'bodyView' => 'v2.app.history.partials.table',
        'bodyData' => $historyPartialData,
    ];
@endphp

@section('body_class', 'v2-history-page v2-history-transactions-page v2-history-payments-page')

@section('style')
    <link href="{{ asset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/v2-history.css') }}?v={{ $historyCssVersion }}" rel="stylesheet">
@endsection

@include('v2.layout.simple.breadcrumb', $historyBreadcrumb)

@section('content')
    @include('v2.common.resource-table-page', $historyPage)
@endsection

@section('script')
    <script src="{{ asset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ asset('vendor/datatables/app.js') }}"></script>
    <script src="{{ asset('vendor/date-picker/jquery-ui.js') }}"></script>
    <script src="{{ asset('vendor/common/handlebars-v4.0.11.js') }}"></script>
    <script>
        window.v2History = {!! json_encode($historyConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    </script>
    <script src="{{ asset('assets/js/v2-history.js') }}?v={{ $historyJsVersion }}"></script>
@endsection
