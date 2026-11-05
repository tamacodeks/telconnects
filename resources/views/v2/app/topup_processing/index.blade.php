@extends('v2.layout.simple.master')

@php
    $page_title = $page_title ?? trans('topup_processing.title');
    $cssVersion = @filemtime(public_path('css/v2/topup-processing.css')) ?: time();
    $jsVersion = @filemtime(public_path('js/v2/topup-processing.js')) ?: time();
@endphp

@section('body_class', 'v2-users-page v2-history-themed-page topup-processing-page')

@section('style')
    <link href="{{ asset('assets/css/v2-users.css') }}?v={{ @filemtime(public_path('assets/css/v2-users.css')) ?: time() }}" rel="stylesheet">
    <link href="{{ asset('css/v2/topup-processing.css') }}?v={{ $cssVersion }}" rel="stylesheet">
@endsection

@include('v2.layout.simple.breadcrumb', ['data' => [['name' => $page_title, 'url' => '', 'active' => 'yes']]])

@section('content')
<div class="container-fluid">
    @if(session('message'))
        <div class="alert alert-{{ session('message_type') === 'warning' ? 'warning' : 'success' }} topup-processing-alert" role="alert">
            <i class="fa {{ session('message_type') === 'warning' ? 'fa-exclamation-triangle' : 'fa-check-circle' }}" aria-hidden="true"></i>
            {{ session('message') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-warning topup-processing-alert" role="alert">
            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <section class="topup-processing-shell">
        <header class="topup-processing-head">
            <div>
                <span class="topup-processing-kicker"><i class="fa fa-random" aria-hidden="true"></i> {{ trans('topup_processing.admin_only') }}</span>
                <h2>{{ trans('topup_processing.title') }}</h2>
                <p>{{ trans('topup_processing.subtitle') }}</p>
            </div>
            <div class="topup-processing-summary">
                <span><strong>{{ number_format($users->total()) }}</strong>{{ trans('topup_processing.visible_users') }}</span>
                <span class="is-queued"><strong>{{ number_format($queuedCount) }}</strong>{{ trans('topup_processing.queued_users') }}</span>
            </div>
        </header>

        <div class="topup-processing-info">
            <i class="fa fa-info-circle" aria-hidden="true"></i>
            <div><strong>{{ trans('topup_processing.default_title') }}</strong><span>{{ trans('topup_processing.default_help') }}</span></div>
        </div>

        <form class="topup-processing-filters" method="GET" action="{{ route('topup-processing.v2') }}">
            <label>
                <span>{{ trans('topup_processing.search') }}</span>
                <div class="topup-processing-input"><i class="fa fa-search" aria-hidden="true"></i><input type="search" name="search" value="{{ $search }}" placeholder="{{ trans('topup_processing.search_placeholder') }}"></div>
            </label>
            <label>
                <span>{{ trans('topup_processing.filter_mode') }}</span>
                <select name="mode" class="form-control">
                    <option value="all" {{ $mode === 'all' ? 'selected' : '' }}>{{ trans('topup_processing.all_modes') }}</option>
                    <option value="synchronous" {{ $mode === 'synchronous' ? 'selected' : '' }}>{{ trans('topup_processing.synchronous') }}</option>
                    <option value="queued" {{ $mode === 'queued' ? 'selected' : '' }}>{{ trans('topup_processing.queued') }}</option>
                </select>
            </label>
            <label>
                <span>{{ trans('topup_processing.manager') }}</span>
                <select name="manager_id" class="form-control">
                    <option value="0">{{ trans('topup_processing.all_managers') }}</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" {{ (int) $managerId === (int) $manager->id ? 'selected' : '' }}>{{ $manager->username }}{{ (int) $manager->status === 1 ? '' : ' (' . trans('topup_processing.inactive') . ')' }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="topup-processing-btn"><i class="fa fa-filter" aria-hidden="true"></i> {{ trans('topup_processing.filter') }}</button>
            <a href="{{ route('topup-processing.v2') }}" class="topup-processing-btn is-light"><i class="fa fa-undo" aria-hidden="true"></i> {{ trans('topup_processing.reset') }}</a>
        </form>

        <form id="topupProcessingForm" method="POST" action="{{ route('topup-processing.v2.save') }}">
            {{ csrf_field() }}
            <div class="topup-processing-bulk">
                <div><strong id="topupSelectedCount">0</strong><span>{{ trans('topup_processing.selected') }}</span></div>
                <label for="topupBulkMode">{{ trans('topup_processing.set_selected_to') }}</label>
                <select id="topupBulkMode" name="mode" class="form-control">
                    <option value="queued">{{ trans('topup_processing.queued') }}</option>
                    <option value="synchronous">{{ trans('topup_processing.synchronous') }}</option>
                </select>
                <button type="submit" class="topup-processing-btn is-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('topup_processing.apply') }}</button>
            </div>

            <div class="topup-processing-table-wrap">
                <table class="topup-processing-table">
                    <thead><tr>
                        <th class="is-check"><label class="topup-processing-check"><input type="checkbox" id="topupSelectAll"><span></span></label></th>
                        <th>{{ trans('topup_processing.user') }}</th>
                        <th>{{ trans('topup_processing.role') }}</th>
                        <th>{{ trans('topup_processing.manager') }}</th>
                        <th>{{ trans('topup_processing.status') }}</th>
                        <th>{{ trans('topup_processing.processing_mode') }}</th>
                    </tr></thead>
                    <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="is-check"><label class="topup-processing-check"><input type="checkbox" class="topup-user-check" name="user_ids[]" value="{{ $user->id }}"><span></span></label></td>
                            <td><strong>{{ $user->username }}</strong><small>#{{ $user->id }}@if($user->email) &middot; {{ $user->email }}@endif</small></td>
                            <td>{{ $user->role_name ?: '-' }}</td>
                            <td><strong>{{ $user->manager_name ?: trans('topup_processing.no_manager') }}</strong></td>
                            <td><span class="topup-processing-status {{ (int) $user->status === 1 ? 'is-active' : 'is-inactive' }}">{{ (int) $user->status === 1 ? trans('topup_processing.active') : trans('topup_processing.inactive') }}</span></td>
                            <td><span class="topup-processing-mode {{ $user->topup_processing_mode === 'queued' ? 'is-queued' : 'is-sync' }}"><i class="fa {{ $user->topup_processing_mode === 'queued' ? 'fa-layer-group' : 'fa-bolt' }}" aria-hidden="true"></i>{{ $user->topup_processing_mode === 'queued' ? trans('topup_processing.queued') : trans('topup_processing.synchronous') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="topup-processing-empty">{{ trans('topup_processing.no_results') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <footer class="topup-processing-pagination">
            <span>{{ trans('topup_processing.showing', ['from' => $users->firstItem() ?: 0, 'to' => $users->lastItem() ?: 0, 'total' => $users->total()]) }}</span>
            {{ $users->links() }}
        </footer>
    </section>
</div>
@endsection

@section('script')
    <script>
        window.topupProcessingText = {!! json_encode(['selectUser' => trans('topup_processing.select_user_warning')], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    </script>
    <script src="{{ asset('js/v2/topup-processing.js') }}?v={{ $jsVersion }}"></script>
@endsection
