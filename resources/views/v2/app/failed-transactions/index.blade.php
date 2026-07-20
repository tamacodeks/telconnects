@extends('v2.layout.simple.master')

@php
    $historyText = trans('v2_history');
    $historyText['failed_transactions'] = $historyText['failed_transactions'] ?? [
        'page_title' => 'Failed Transactions',
        'panel_title' => 'Failed Transactions',
        'panel_subtitle' => 'Search and review failed or refunded transactions.',
    ];
    $historyText['filters']['search_placeholder'] = $historyText['filters']['failed_search_placeholder'] ?? $historyText['filters']['search_placeholder'];
    $historyText['js']['empty_title'] = $historyText['js']['failed_empty_title'] ?? 'No failed transactions found';
    $historyText['js']['empty_description'] = $historyText['js']['failed_empty_description'] ?? 'Try another date range or search term.';
    $page_title = $historyText['failed_transactions']['page_title'];
    $historyCssVersion = @filemtime(public_path('assets/css/v2-history.css')) ?: time();
    $historyJsVersion = @filemtime(public_path('assets/js/v2-history.js')) ?: time();
    $historyPartialData = [
        'historyText' => $historyText,
        'historyType' => 'failed-transactions',
        'services' => $services ?? collect(),
        'fromDate' => $from_date ?? '',
        'toDate' => $to_date ?? '',
        'searchValue' => old('query', request()->input('user')),
        'canSeeCost' => in_array((int) auth()->user()->group_id, [1, 2, 3], true),
    ];
    $historyBreadcrumb = [
        'data' => [
            ['name' => $historyText['failed_transactions']['page_title'], 'url' => '', 'active' => 'yes'],
        ],
    ];
    $historyConfig = [
        'type' => 'failed-transactions',
        'fetchUrl' => route('failed-transactions.v2.fetch'),
        'csrfToken' => csrf_token(),
        'locale' => app()->getLocale(),
        'canSeeCost' => $historyPartialData['canSeeCost'],
        'defaultFromDate' => \Illuminate\Support\Carbon::now()->format('Y-m-d'),
        'defaultToDate' => \Illuminate\Support\Carbon::now()->format('Y-m-d'),
        'columns' => $historyText['columns'],
        'labels' => $historyText['js'] + [
            'processing' => trans('common.processing'),
            'records10' => '10 ' . ($historyText['actions']['records'] ?? 'records'),
            'records25' => '25 ' . ($historyText['actions']['records'] ?? 'records'),
            'records50' => '50 ' . ($historyText['actions']['records'] ?? 'records'),
            'showAll' => $historyText['actions']['show_all'] ?? 'Show all',
            'dateError' => $historyText['filters']['date_error'] ?? 'Select a valid date range.',
            'resetFilters' => $historyText['filters']['reset'] ?? 'Reset filters',
            'emptyTitle' => $historyText['js']['empty_title'] ?? 'No failed transactions found',
            'emptyDescription' => $historyText['js']['empty_description'] ?? 'Try another date range or search term.',
            'failedStatus' => $historyText['js']['failed_status'] ?? 'Failed',
            'refunded' => $historyText['js']['refunded'] ?? 'Refunded',
            'info' => $historyText['js']['info'] ?? 'Showing _START_ to _END_ of _TOTAL_ lines',
            'infoEmpty' => $historyText['js']['info_empty'] ?? 'No lines to show',
            'infoFiltered' => $historyText['js']['info_filtered'] ?? '(filtered from _MAX_ total lines)',
            'previous' => $historyText['js']['previous'] ?? 'Previous',
            'next' => $historyText['js']['next'] ?? 'Next',
            'downloadExcel' => trans('common.download_as_excel'),
            'refresh' => $historyText['actions']['refresh'],
        ],
    ];
    $historyPage = [
        'classPrefix' => 'v2-history',
        'wrapperAttributes' => [
            'data-history-page' => 'failed-transactions',
        ],
        'showHeader' => false,
        'panelId' => 'v2HistoryPanel',
        'panelTitle' => $historyText['failed_transactions']['panel_title'],
        'panelSubtitle' => $historyText['failed_transactions']['panel_subtitle'],
        'panelActionsView' => 'v2.app.history.partials.actions',
        'panelActionsData' => $historyPartialData,
        'toolbarView' => 'v2.app.history.partials.filters',
        'toolbarData' => $historyPartialData,
        'bodyView' => 'v2.app.history.partials.table',
        'bodyData' => $historyPartialData,
    ];
@endphp

@section('body_class', 'v2-history-page v2-history-transactions-page v2-history-failed-transactions-page')

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
