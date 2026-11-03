<div class="night-overview">
    <div class="night-overview-main">
        <span class="night-overview-icon" aria-hidden="true"><i class="fa fa-moon"></i></span>
        <div>
            <span class="night-eyebrow">Default access policy</span>
            <div class="night-overview-policy">
                <strong id="nightGlobalSummary">{{ $global ? substr($global->allowed_start, 0, 5) . ' - ' . substr($global->allowed_end, 0, 5) . ' · ' . $global->timezone : 'No global policy configured' }}</strong>
                <span class="night-overview-state {{ $global && $global->enabled ? 'is-enabled' : 'is-disabled' }}" id="nightGlobalStatus">{{ $global ? ($global->enabled ? 'Enabled' : 'Disabled') : 'Not configured' }}</span>
            </div>
            <small>Users follow this rule unless they have an individual override.</small>
        </div>
    </div>
    <div class="night-overview-counts" aria-label="Policy summary">
        <div><strong>{{ number_format($userCount) }}</strong><span>Active users</span></div>
        <div><strong id="nightOverrideCount">{{ number_format($overrideCount) }}</strong><span>Custom rules</span></div>
    </div>
</div>

<div class="v2-users-toolbar night-toolbar">
    <div class="night-policy-filters" role="group" aria-label="Filter by policy">
        <button type="button" class="night-filter is-active" data-policy="" aria-pressed="true"><i class="fa fa-users" aria-hidden="true"></i> All users</button>
        <button type="button" class="night-filter" data-policy="custom" aria-pressed="false"><i class="fa fa-user-clock" aria-hidden="true"></i> Custom rules</button>
        <button type="button" class="night-filter" data-policy="global" aria-pressed="false"><i class="fa fa-globe" aria-hidden="true"></i> Global policy</button>
    </div>
    <label class="night-search-label" for="nightSearch"><i class="fa fa-search" aria-hidden="true"></i><input id="nightSearch" type="search" placeholder="Search username or role" autocomplete="off"></label>
</div>
