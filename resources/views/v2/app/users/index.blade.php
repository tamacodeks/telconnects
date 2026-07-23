@extends('v2.layout.simple.master')

@php
    $page_title = $page_title ?? ($usersV2Config['title'] ?? 'Users');
    $usersV2CssVersion = @filemtime(public_path('assets/css/v2-users.css')) ?: time();
    $usersV2JsVersion = @filemtime(public_path('assets/js/v2-users.js')) ?: time();
    $usersV2PartialData = [
        'usersV2Type' => $usersV2Type ?? 'users',
        'usersV2Config' => $usersV2Config,
        'user_list' => $user_list ?? collect(),
    ];
    $usersV2Breadcrumb = [
        'data' => [
            ['name' => $page_title, 'url' => '', 'active' => 'yes'],
        ],
    ];
    $usersV2Page = [
        'classPrefix' => 'v2-users',
        'wrapperAttributes' => [
            'data-users-v2-page' => $usersV2Type ?? 'users',
        ],
        'showHeader' => $usersV2Config['showHeader'] ?? true,
        'kickerIcon' => $usersV2Config['kickerIcon'] ?? 'fa fa-users',
        'kickerText' => $usersV2Config['kickerText'] ?? 'Users',
        'title' => $usersV2Config['title'] ?? $page_title,
        'subtitle' => $usersV2Config['subtitle'] ?? '',
        'stats' => $usersV2Config['stats'] ?? [],
        'panelId' => 'usersV2Panel',
        'panelTitle' => $usersV2Config['panelTitle'] ?? $page_title,
        'panelSubtitle' => $usersV2Config['panelSubtitle'] ?? '',
        'panelActionsView' => 'v2.app.users.partials.actions',
        'panelActionsData' => $usersV2PartialData,
        'toolbarView' => 'v2.app.users.partials.toolbar',
        'toolbarData' => $usersV2PartialData,
        'bodyView' => 'v2.app.users.partials.table',
        'bodyData' => $usersV2PartialData,
    ];
@endphp

@section('body_class', 'v2-users-page v2-history-themed-page')

@section('style')
    <link href="{{ asset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/v2-users.css') }}?v={{ $usersV2CssVersion }}&theme=auto" rel="stylesheet">
@endsection

@include('v2.layout.simple.breadcrumb', $usersV2Breadcrumb)

@section('content')
    @include('v2.common.resource-table-page', $usersV2Page)
    @include('v2.app.users.partials.reset-modal', $usersV2PartialData)
@endsection

@section('script')
    <script src="{{ asset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ asset('vendor/datatables/Buttons-1.5.1/js/buttons.bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/Buttons-1.5.1/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('vendor/select-picker/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('vendor/datatables/app.js') }}"></script>
    <script>
        window.usersV2 = {!! json_encode($usersV2Config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    </script>
    <script src="{{ asset('assets/js/v2-users.js') }}?v={{ $usersV2JsVersion }}"></script>
@endsection
