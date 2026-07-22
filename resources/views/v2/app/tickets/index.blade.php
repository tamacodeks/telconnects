@extends('v2.layout.simple.master')

@php
    $historyText = trans('v2_history');
    $ticketsTitle = trans('myservice.my_tickets');
    $ticketsTitle = $ticketsTitle === 'myservice.my_tickets' ? 'My Tickets' : $ticketsTitle;
    $historyText['tickets'] = [
        'page_title' => $ticketsTitle,
        'panel_title' => $ticketsTitle,
        'panel_subtitle' => app()->getLocale() === 'fr'
            ? 'Recherchez et suivez les demandes liees aux PINs de cartes recharge.'
            : 'Search and review support tickets for printed calling card PINs.',
    ];
    $historyText['filters']['status'] = trans('myservice.status') === 'myservice.status' ? 'Status' : trans('myservice.status');
    $historyText['filters']['all_statuses'] = app()->getLocale() === 'fr' ? 'Tous les statuts' : 'All statuses';
    $historyText['filters']['open'] = trans('myservice.open') === 'myservice.open' ? 'Open' : trans('myservice.open');
    $historyText['filters']['closed'] = trans('myservice.closed') === 'myservice.closed' ? 'Closed' : trans('myservice.closed');
    $historyText['filters']['search_placeholder'] = app()->getLocale() === 'fr' ? 'Carte, serie, PIN, type' : 'Card, serial, PIN, type';
    $historyText['js']['empty_title'] = app()->getLocale() === 'fr' ? 'Aucun ticket trouve' : 'No tickets found';
    $historyText['js']['empty_description'] = app()->getLocale() === 'fr'
        ? 'Essayez un autre statut, une autre date ou une recherche differente.'
        : 'Try another status, date range, or search term.';
    $historyText['columns']['card_name'] = $historyText['columns']['card_name'] ?? (trans('myservice.lbl_card_name') === 'myservice.lbl_card_name' ? 'Card name' : trans('myservice.lbl_card_name'));
    $historyText['columns']['to'] = trans('myservice.to') === 'myservice.to' ? 'To' : trans('myservice.to');
    $historyText['columns']['type'] = trans('common.type') === 'common.type' ? 'Type' : trans('common.type');
    $historyText['columns']['created_at'] = trans('common.created_at') === 'common.created_at' ? 'Created at' : trans('common.created_at');
    $historyText['columns']['action'] = trans('common.trans_tbl_action') === 'common.trans_tbl_action' ? 'Action' : trans('common.trans_tbl_action');
    $page_title = $historyText['tickets']['page_title'];
    $historyCssVersion = @filemtime(public_path('assets/css/v2-history.css')) ?: time();
    $historyJsVersion = @filemtime(public_path('assets/js/v2-history.js')) ?: time();
    $historyPartialData = [
        'historyText' => $historyText,
        'historyType' => 'tickets',
        'services' => collect(),
        'fromDate' => $from_date ?? '',
        'toDate' => $to_date ?? '',
        'searchValue' => old('query', request()->input('query')),
        'canSeeCost' => false,
    ];
    $historyBreadcrumb = [
        'data' => [
            ['name' => $historyText['tickets']['page_title'], 'url' => '', 'active' => 'yes'],
        ],
    ];
    $historyConfig = [
        'type' => 'tickets',
        'fetchUrl' => route('tickets.v2.fetch'),
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
            'resetFilters' => $historyText['filters']['reset'] ?? 'Reset filters',
            'emptyTitle' => $historyText['js']['empty_title'],
            'emptyDescription' => $historyText['js']['empty_description'],
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
            'data-history-page' => 'tickets',
        ],
        'showHeader' => false,
        'panelId' => 'v2HistoryPanel',
        'panelTitle' => $historyText['tickets']['panel_title'],
        'panelSubtitle' => $historyText['tickets']['panel_subtitle'],
        'panelActionsView' => 'v2.app.history.partials.actions',
        'panelActionsData' => $historyPartialData,
        'toolbarView' => 'v2.app.history.partials.filters',
        'toolbarData' => $historyPartialData,
        'bodyView' => 'v2.app.history.partials.table',
        'bodyData' => $historyPartialData,
    ];
@endphp

@section('body_class', 'v2-history-page v2-history-transactions-page v2-history-themed-page v2-history-pin-history-page v2-history-tickets-page')

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
