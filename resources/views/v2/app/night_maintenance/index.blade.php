@extends('v2.layout.simple.master')

@php
    $page_title = 'Night Maintenance';
    $page = [
        'classPrefix' => 'v2-users',
        'showHeader' => false,
        'panelId' => 'nightMaintenancePanel',
        'panelTitle' => 'Night maintenance',
        'panelSubtitle' => 'Default access hours and individual user rules.',
        'panelActionsView' => 'v2.app.night_maintenance.partials.actions',
        'toolbarView' => 'v2.app.night_maintenance.partials.toolbar',
        'bodyView' => 'v2.app.night_maintenance.partials.table',
    ];
@endphp

@section('body_class', 'v2-users-page v2-history-themed-page night-maintenance-page')

@section('style')
    <link href="{{ asset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/v2-users.css') }}?v={{ @filemtime(public_path('assets/css/v2-users.css')) ?: time() }}" rel="stylesheet">
    <link href="{{ asset('css/v2/night-maintenance.css') }}?v={{ @filemtime(public_path('css/v2/night-maintenance.css')) ?: time() }}" rel="stylesheet">
@endsection

@include('v2.layout.simple.breadcrumb', ['data' => [['name' => 'Night Maintenance', 'url' => '', 'active' => 'yes']]])

@section('content')
    @if(session('message'))
        <div class="container-fluid"><div class="alert alert-success">{{ session('message') }}</div></div>
    @endif

    @include('v2.common.resource-table-page', $page)

    <div class="modal fade v2-users-modal" id="nightGlobalModal" tabindex="-1" aria-labelledby="nightGlobalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="nightGlobalForm" action="{{ route('night-maintenance.v2.save') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title" id="nightGlobalTitle">Global maintenance policy</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="night-modal-help">This schedule applies to users without an individual override. The allowed window uses the selected timezone.</p>
                        <label for="nightGlobalEnabled">Maintenance status</label>
                        <select id="nightGlobalEnabled" name="enabled" class="form-control">
                            <option value="1" {{ !$global || $global->enabled ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ $global && !$global->enabled ? 'selected' : '' }}>Disabled</option>
                        </select>
                        <div class="night-modal-time-grid">
                            <div><label for="nightGlobalStart">Allowed from</label><input id="nightGlobalStart" name="allowed_start" type="time" class="form-control" value="{{ substr(optional($global)->allowed_start ?: '07:30', 0, 5) }}" required></div>
                            <div><label for="nightGlobalEnd">Allowed until</label><input id="nightGlobalEnd" name="allowed_end" type="time" class="form-control" value="{{ substr(optional($global)->allowed_end ?: '00:00', 0, 5) }}" required></div>
                        </div>
                        <label for="nightGlobalTimezone">Timezone</label>
                        <select id="nightGlobalTimezone" name="timezone" class="form-control">
                            @foreach($timezones as $timezone)
                                <option value="{{ $timezone }}" {{ (optional($global)->timezone ?: 'Europe/Paris') === $timezone ? 'selected' : '' }}>{{ $timezone }}</option>
                            @endforeach
                        </select>
                        <p class="night-modal-note">When maintenance is disabled, inherited users are unrestricted by this policy.</p>
                        <div class="night-modal-feedback" role="alert" hidden></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="v2-users-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="v2-users-btn v2-users-btn-primary"><i class="fa fa-save"></i> Save global policy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade v2-users-modal" id="nightUserModal" tabindex="-1" aria-labelledby="nightUserTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="nightUserForm" action="{{ route('night-maintenance.v2.save') }}" method="POST">
                    {{ csrf_field() }}
                    <input type="hidden" name="user_id" id="nightUserId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="nightUserTitle">Edit user maintenance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="night-modal-help">Set a schedule for <strong id="nightUserName"></strong>. An individual rule takes priority over the global policy.</p>
                        <label for="nightUserEnabled">Maintenance status</label>
                        <select id="nightUserEnabled" name="enabled" class="form-control">
                            <option value="1">Enabled for this user</option>
                            <option value="0">Disabled for this user</option>
                        </select>
                        <div class="night-modal-time-grid">
                            <div><label for="nightUserStart">Allowed from</label><input id="nightUserStart" name="allowed_start" type="time" class="form-control" required></div>
                            <div><label for="nightUserEnd">Allowed until</label><input id="nightUserEnd" name="allowed_end" type="time" class="form-control" required></div>
                        </div>
                        <label for="nightUserTimezone">Timezone</label>
                        <select id="nightUserTimezone" name="timezone" class="form-control">
                            @foreach($timezones as $timezone)
                                <option value="{{ $timezone }}">{{ $timezone }}</option>
                            @endforeach
                        </select>
                        <p class="night-modal-note">Use “Restore global policy” to remove the user's individual rule.</p>
                        <div class="night-modal-feedback" role="alert" hidden></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="nightRestoreGlobal" class="v2-users-btn v2-users-btn-warning">Restore global policy</button>
                        <button type="button" class="v2-users-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="v2-users-btn v2-users-btn-primary"><i class="fa fa-save"></i> Save user rule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ asset('vendor/datatables/Buttons-1.5.1/js/buttons.bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/Buttons-1.5.1/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/app.js') }}"></script>
    <script src="{{ asset('js/v2/night-maintenance.js') }}?v={{ @filemtime(public_path('js/v2/night-maintenance.js')) ?: time() }}"></script>
@endsection
