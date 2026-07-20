@extends('v2.layout.simple.master')

@php
    $historyText = trans('v2_history');
    $historyText['pin_history'] = $historyText['pin_history'] ?? [
        'page_title' => 'Pin Usage History',
        'panel_title' => 'Pin Usage History',
        'panel_subtitle' => 'Search and review printed calling card PINs.',
    ];
    $historyText['filters']['service'] = $historyText['filters']['card'] ?? 'Card';
    $historyText['filters']['all_services'] = $historyText['filters']['all_cards'] ?? 'Select a card';
    $historyText['filters']['search_placeholder'] = $historyText['filters']['pin_search_placeholder'] ?? 'Card, serial, PIN, description';
    $historyText['columns']['card_name'] = $historyText['columns']['card_name'] ?? 'Card name';
    $historyText['columns']['card_description'] = $historyText['columns']['card_description'] ?? 'Description';
    $historyText['columns']['action'] = $historyText['columns']['action'] ?? 'Action';
    $historyText['js']['empty_title'] = $historyText['js']['pin_empty_title'] ?? 'No PIN history found';
    $historyText['js']['empty_description'] = $historyText['js']['pin_empty_description'] ?? 'Try another date range, card, or search term.';
    $page_title = $historyText['pin_history']['page_title'];
    $historyCssVersion = @filemtime(public_path('assets/css/v2-history.css')) ?: time();
    $historyJsVersion = @filemtime(public_path('assets/js/v2-history.js')) ?: time();
    $historyPartialData = [
        'historyText' => $historyText,
        'historyType' => 'pin-history',
        'services' => $providers ?? collect(),
        'fromDate' => $from_date ?? '',
        'toDate' => $to_date ?? '',
        'searchValue' => old('query', request()->input('query')),
        'canSeeCost' => false,
    ];
    $historyBreadcrumb = [
        'data' => [
            ['name' => $historyText['pin_history']['page_title'], 'url' => '', 'active' => 'yes'],
        ],
    ];
    $historyConfig = [
        'type' => 'pin-history',
        'fetchUrl' => route('cc-pin-history.v2.fetch'),
        'csrfToken' => csrf_token(),
        'locale' => app()->getLocale(),
        'defaultFromDate' => \Illuminate\Support\Carbon::now()->format('Y-m-d'),
        'defaultToDate' => \Illuminate\Support\Carbon::now()->format('Y-m-d'),
        'columns' => $historyText['columns'],
        'urls' => [
            'printRequest' => route('cc-pin-history.v2.print-request'),
        ],
        'labels' => $historyText['js'] + [
            'processing' => trans('common.processing'),
            'records10' => '10 ' . ($historyText['actions']['records'] ?? 'records'),
            'records25' => '25 ' . ($historyText['actions']['records'] ?? 'records'),
            'records50' => '50 ' . ($historyText['actions']['records'] ?? 'records'),
            'showAll' => $historyText['actions']['show_all'] ?? 'Show all',
            'dateError' => $historyText['filters']['date_error'] ?? 'Select a valid date range.',
            'servicesAll' => $historyText['filters']['all_services'] ?? 'Select a card',
            'servicesAllSelected' => $historyText['filters']['all_selected'] ?? 'All',
            'servicesSelected' => $historyText['filters']['selected_services'] ?? 'selected',
            'selectAll' => $historyText['filters']['select_all'] ?? 'Select all',
            'clear' => $historyText['filters']['deselect_all'] ?? 'Clear',
            'resetFilters' => $historyText['filters']['reset'] ?? 'Reset filters',
            'emptyTitle' => $historyText['js']['empty_title'] ?? 'No PIN history found',
            'emptyDescription' => $historyText['js']['empty_description'] ?? 'Try another date range, card, or search term.',
            'info' => 'Showing _START_ to _END_ of _TOTAL_ lines',
            'infoEmpty' => 'No lines to show',
            'infoFiltered' => '(filtered from _MAX_ total lines)',
            'previous' => 'Previous',
            'next' => 'Next',
            'downloadExcel' => trans('common.download_as_excel'),
            'refresh' => $historyText['actions']['refresh'],
            'requestSent' => 'Request sent',
        ],
    ];
    $historyPage = [
        'classPrefix' => 'v2-history',
        'wrapperAttributes' => [
            'data-history-page' => 'pin-history',
        ],
        'showHeader' => false,
        'panelId' => 'v2HistoryPanel',
        'panelTitle' => $historyText['pin_history']['panel_title'],
        'panelSubtitle' => $historyText['pin_history']['panel_subtitle'],
        'panelActionsView' => 'v2.app.history.partials.actions',
        'panelActionsData' => $historyPartialData,
        'toolbarView' => 'v2.app.history.partials.filters',
        'toolbarData' => $historyPartialData,
        'bodyView' => 'v2.app.history.partials.table',
        'bodyData' => $historyPartialData,
    ];
@endphp

@section('body_class', 'v2-history-page v2-history-transactions-page v2-history-pin-history-page')

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
