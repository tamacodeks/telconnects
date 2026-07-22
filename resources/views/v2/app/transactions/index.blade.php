@extends('v2.layout.simple.master')

@php
    $historyText = trans('v2_history');
    $historyText['filters']['all_services'] = 'Sélectionner un service';
    $historyText['filters']['from'] = 'Du';
    $historyText['filters']['to'] = 'Au';
    $historyText['filters']['search'] = 'Recherche';
    $historyText['filters']['apply'] = 'Recherche';
    $historyText['filters']['search_placeholder'] = 'Utilisateur, mobile, référence';
    $historyText['filters']['reset'] = 'Réinitialiser';
    $historyText['filters']['select_all'] = 'Tout sélectionner';
    $historyText['filters']['deselect_all'] = 'Effacer';
    $historyText['transactions']['panel_subtitle'] = 'Rechercher et consulter les transactions.';
    $historyText['actions']['page_size'] = 'Lignes';
    $historyText['actions']['records'] = 'lignes';
    $historyText['actions']['show_all'] = 'Tout afficher';
    $historyText['actions']['refresh'] = 'Actualiser';
    $historyText['actions']['export'] = 'Exporter';
    $historyText['columns']['retailer'] = 'Utilisateur';
    $historyText['columns']['transaction_id'] = 'ID transaction';
    $historyText['columns']['product'] = 'Produit';
    $historyText['columns']['public_price'] = 'Prix public';
    $historyText['columns']['buying_price'] = 'Prix achat';
    $historyText['columns']['reseller_price'] = 'Prix revendeur';
    $historyText['columns']['price'] = 'Prix';
    $historyText['columns']['sale_margin'] = 'Marge';
    $historyText['columns']['status'] = 'Statut';
    $historyText['columns']['total'] = 'Total';
    $historyText['js']['empty'] = 'Aucune transaction trouvée';
    $historyText['js']['empty_title'] = 'Aucune transaction trouvée';
    $historyText['js']['empty_description'] = 'Essayez une autre période, un autre service ou un autre terme de recherche.';
    $historyText['js']['topup_ok'] = 'Topup OK';
    $historyText['js']['calling_card_ok'] = 'Carte recharge OK';
    $historyText['js']['refunded'] = 'Remboursé';
    $historyText['js']['download'] = 'Télécharger';
    $page_title = $historyText['transactions']['page_title'];
    $historyCssVersion = @filemtime(public_path('assets/css/v2-history.css')) ?: time();
    $historyJsVersion = @filemtime(public_path('assets/js/v2-history.js')) ?: time();
    $historyPartialData = [
        'historyText' => $historyText,
        'historyType' => 'transactions',
        'services' => $services ?? collect(),
        'fromDate' => $from_date ?? '',
        'toDate' => $to_date ?? '',
        'searchValue' => old('query', request()->input('user')),
        'canSeeCost' => in_array((int) auth()->user()->group_id, [1, 2, 3], true),
    ];
    $historyBreadcrumb = [
        'data' => [
            ['name' => $historyText['transactions']['page_title'], 'url' => '', 'active' => 'yes'],
        ],
    ];
    $historyConfig = [
        'type' => 'transactions',
        'fetchUrl' => route('transactions.v2.data'),
        'csrfToken' => csrf_token(),
        'locale' => 'fr',
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
            'servicesAll' => $historyText['filters']['all_services'] ?? 'All services',
            'servicesAllSelected' => $historyText['filters']['all_selected'] ?? 'All',
            'servicesSelected' => $historyText['filters']['selected_services'] ?? 'selected',
            'selectAll' => $historyText['filters']['select_all'] ?? 'Select all',
            'clear' => $historyText['filters']['deselect_all'] ?? 'Clear',
            'resetFilters' => $historyText['filters']['reset'] ?? 'Reset filters',
            'emptyTitle' => $historyText['js']['empty_title'] ?? 'Aucune transaction trouvée',
            'emptyDescription' => $historyText['js']['empty_description'] ?? 'Essayez une autre période, un autre service ou un autre terme de recherche.',
            'info' => 'Affichage de _START_ à _END_ sur _TOTAL_ lignes',
            'infoEmpty' => 'Aucune ligne à afficher',
            'infoFiltered' => '(filtré depuis _MAX_ lignes au total)',
            'previous' => 'Précédent',
            'next' => 'Suivant',
            'downloadExcel' => trans('common.download_as_excel'),
            'refresh' => $historyText['actions']['refresh'],
            'print' => trans('common.btn_print'),
        ],
    ];
    $historyPage = [
        'classPrefix' => 'v2-history',
        'wrapperAttributes' => [
            'data-history-page' => 'transactions',
        ],
        'showHeader' => false,
        'panelId' => 'v2HistoryPanel',
        'panelTitle' => $historyText['transactions']['panel_title'],
        'panelSubtitle' => $historyText['transactions']['panel_subtitle'],
        'panelActionsView' => 'v2.app.history.partials.actions',
        'panelActionsData' => $historyPartialData,
        'toolbarView' => 'v2.app.history.partials.filters',
        'toolbarData' => $historyPartialData,
        'bodyView' => 'v2.app.history.partials.table',
        'bodyData' => $historyPartialData,
    ];
@endphp

@section('body_class', 'v2-history-page v2-history-transactions-page v2-history-themed-page v2-history-pin-history-page')

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
