@php
    $type = $usersV2Type ?? ($usersV2Config['type'] ?? 'users');
    $labels = $usersV2Config['labels'] ?? [];
@endphp

<label class="v2-users-page-size" for="usersV2PageLength">
    <span>{{ $labels['rows'] ?? 'Rows' }}</span>
    <select id="usersV2PageLength" aria-label="{{ $labels['rowsPerPage'] ?? ($labels['rows'] ?? 'Rows') }}">
        <option value="10">{{ $labels['records10'] ?? '10 records' }}</option>
        <option value="25">{{ $labels['records25'] ?? '25 records' }}</option>
        <option value="50">{{ $labels['records50'] ?? '50 records' }}</option>
        <option value="-1">{{ $labels['showAll'] ?? 'Show all' }}</option>
    </select>
</label>

<button type="button" class="v2-users-icon-btn" id="usersV2Export" title="{{ $labels['downloadExcel'] ?? ($labels['export'] ?? 'Export') }}" aria-label="{{ $labels['downloadExcel'] ?? ($labels['export'] ?? 'Export') }}">
    <i class="fa fa-file-excel"></i>
</button>
<button type="button" class="v2-users-icon-btn" id="usersV2Refresh" title="{{ $labels['refresh'] ?? 'Refresh' }}" aria-label="{{ $labels['refresh'] ?? 'Refresh' }}">
    <i class="fa fa-sync"></i>
</button>

@if($type === 'users' && !empty($usersV2Config['addUrl']))
    <a href="{{ $usersV2Config['addUrl'] }}" class="v2-users-btn v2-users-btn-primary">
        <i class="fa fa-plus-circle"></i>
        <span>{{ $labels['addUser'] ?? trans('users.btn_add_user') }}</span>
    </a>
@endif

@if($type === 'user-groups' && !empty($usersV2Config['addUrl']))
    <a href="{{ $usersV2Config['addUrl'] }}"
       class="v2-users-btn v2-users-btn-primary"
       onclick="AppModal(this.href,'{{ $labels['addUserGroup'] ?? (trans('common.btn_add').' '.trans('users.lbl_user_group')) }}');return false;">
        <i class="fa fa-plus-circle"></i>
        <span>{{ $labels['addUserGroup'] ?? (trans('common.btn_add').' '.trans('users.lbl_user_group')) }}</span>
    </a>
@endif
