@extends('v2.layout.simple.master')

@php
    $historyText = trans('v2_history');
    $page_title = $historyText['orders']['page_title'];
    $historyCssVersion = @filemtime(public_path('assets/css/v2-history.css')) ?: time();
    $historyJsVersion = @filemtime(public_path('assets/js/v2-history.js')) ?: time();
    $historyPartialData = [
        'historyText' => $historyText,
        'historyType' => 'orders',
        'services' => $services ?? collect(),
        'fromDate' => $from_date ?? '',
        'toDate' => $to_date ?? '',
        'activeRange' => $active_range ?? 'all',
        'showQuickRanges' => true,
        'searchValue' => old('query', request()->input('user')),
        'canSeeCost' => in_array((int) auth()->user()->group_id, [1, 2, 3], true),
    ];
    $historyBreadcrumb = [
        'data' => [
            ['name' => $historyText['orders']['page_title'], 'url' => '', 'active' => 'yes'],
        ],
    ];
    $historyConfig = [
        'type' => 'orders',
        'fetchUrl' => route('orders.v2.data'),
        'csrfToken' => csrf_token(),
        'canSeeCost' => $historyPartialData['canSeeCost'],
        'columns' => $historyText['columns'],
        'labels' => $historyText['js'] + [
            'processing' => trans('common.processing'),
            'records10' => '10 ' . ($historyText['actions']['records'] ?? 'records'),
            'records25' => '25 ' . ($historyText['actions']['records'] ?? 'records'),
            'records50' => '50 ' . ($historyText['actions']['records'] ?? 'records'),
            'showAll' => $historyText['actions']['show_all'] ?? 'Show all',
            'dateError' => $historyText['filters']['date_error'] ?? 'Select a valid date range.',
            'servicesAll' => $historyText['filters']['all_services'] ?? 'All services',
            'servicesAllSelected' => $historyText['filters']['all_selected'] ?? 'All',
            'servicesSelected' => $historyText['filters']['selected_services'] ?? 'selected',
            'downloadExcel' => trans('common.download_as_excel'),
            'refresh' => trans('common.refresh'),
            'print' => trans('common.btn_print'),
        ],
    ];
    $historyPage = [
        'classPrefix' => 'v2-history',
        'wrapperAttributes' => [
            'data-history-page' => 'orders',
        ],
        'showHeader' => false,
        'panelId' => 'v2HistoryPanel',
        'panelTitle' => $historyText['orders']['panel_title'],
        'panelSubtitle' => $historyText['orders']['panel_subtitle'],
        'panelActionsView' => 'v2.app.history.partials.actions',
        'panelActionsData' => $historyPartialData,
        'toolbarView' => 'v2.app.history.partials.filters',
        'toolbarData' => $historyPartialData,
        'bodyView' => 'v2.app.history.partials.table',
        'bodyData' => $historyPartialData,
    ];
@endphp

@section('body_class', 'v2-history-page v2-history-transactions-page v2-history-themed-page v2-history-pin-history-page v2-history-orders-page')

@section('style')
    <link href="{{ asset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/v2-history.css') }}?v={{ $historyCssVersion }}&theme=auto" rel="stylesheet">
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
