@php
    $type = $usersV2Type ?? ($usersV2Config['type'] ?? 'users');
    $labels = $usersV2Config['labels'] ?? [];
    $quickLinks = $usersV2Config['quickLinks'] ?? [];
@endphp

<div class="v2-users-toolbar">
    <div class="v2-users-tabs" role="navigation" aria-label="{{ $labels['userPages'] ?? 'User pages' }}">
        @foreach($quickLinks as $quickLink)
            <a href="{{ $quickLink['url'] ?? '#' }}" class="v2-users-tab {{ !empty($quickLink['active']) ? 'active' : '' }}">
                <i class="{{ $quickLink['icon'] ?? 'fa fa-circle' }}"></i>
                <span>{{ $quickLink['label'] ?? '' }}</span>
            </a>
        @endforeach
    </div>

    @if($type === 'all-users')
        <form id="usersV2FilterForm" class="v2-users-filters" role="search">
            <label for="usersV2ParentId">
                <span>{{ $labels['manager'] ?? 'Manager' }}</span>
                <select name="parent_id" id="usersV2ParentId" class="select-picker">
                    <option value="">{{ $labels['selectManager'] ?? 'Select Manager' }}</option>
                    @foreach($user_list as $userListItem)
                        <option value="{{ $userListItem->id }}">{{ $userListItem->username }}</option>
                    @endforeach
                </select>
            </label>

            <label for="usersV2Status">
                <span>{{ $labels['status'] ?? 'Status' }}</span>
                <select name="status" id="usersV2Status" class="select-picker">
                    <option value="">{{ $labels['selectStatus'] ?? 'Select Status' }}</option>
                    <option value="1">{{ $labels['active'] ?? 'Active' }}</option>
                    <option value="2">{{ $labels['inactive'] ?? 'Inactive' }}</option>
                </select>
            </label>

            <button type="submit" class="v2-users-btn v2-users-btn-primary">
                <i class="fa fa-filter"></i>
                <span>{{ $labels['search'] ?? 'Search' }}</span>
            </button>
            <button type="button" class="v2-users-btn" id="usersV2ClearFilters">
                <i class="fa fa-times"></i>
                <span>{{ $labels['reset'] ?? 'Reset' }}</span>
            </button>
        </form>
    @endif

    @if($type === 'users' && !empty($usersV2Config['canManageCorrections']))
        <div class="v2-users-ops">
            <button type="button" class="v2-users-btn v2-users-btn-danger" id="usersV2RunResetCorrections">
                <i class="fa fa-play"></i>
                <span>{{ $labels['runResetCorrections'] ?? 'Run Reset Corrections' }}</span>
            </button>
            <button type="button" class="v2-users-btn v2-users-btn-warning" id="usersV2OpenResetCorrections">
                <i class="fa fa-history"></i>
                <span>{{ $labels['resetCorrections'] ?? 'Reset Corrections' }}</span>
            </button>
        </div>
    @endif
</div>
