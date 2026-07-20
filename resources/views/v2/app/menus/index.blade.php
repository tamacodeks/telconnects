@extends('v2.layout.simple.master')

@section('title', 'Menus')

@section('css')
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/v2/menus-v2.css') }}">
@endsection

@section('content')
  @php
    $menuSections = $menuSections ?? [
      'account' => 'Account',
      'services' => 'Services',
      'history' => 'History',
      'settings' => 'Settings',
    ];
  @endphp

  @include('v2.layout.simple.breadcrumb', ['data' => [
    ['name' => 'Menus', 'url' => '', 'active' => 'yes']
  ]])

  <div class="container-fluid menu-v2-page"
       id="menus-v2-app"
       data-base-url="{{ url('/') }}"
       data-csrf="{{ csrf_token() }}"
       data-selected-group-id="{{ (int) $selectedGroupId }}"
       data-data-url="{{ route('menus.v2.data') }}"
       data-save-url="{{ route('menus.v2.save') }}"
       data-reorder-url="{{ route('menus.v2.reorder') }}"
       data-status-base="{{ url('menu-v2/status') }}"
       data-remove-base="{{ url('menu-v2/remove') }}">
    @if(session('message'))
      <div class="menu-v2-flash {{ session('message_type') }}">
        {!! session('message') !!}
      </div>
    @endif

    @if($errors->any())
      <div class="menu-v2-flash warning">
        {{ $errors->first() }}
      </div>
    @endif

    <div class="menu-v2-hero">
      <div class="menu-v2-title-wrap">
        <span class="menu-v2-emblem"><i class="fa fa-sitemap" aria-hidden="true"></i></span>
        <div>
          <h1 class="menu-v2-title">Role Sidebar Builder</h1>
          <p class="menu-v2-subtitle">
            Build the sidebar map for each role with clean ordering, parent-child structure, icons, URLs, and status control.
          </p>
        </div>
      </div>
      <a href="{{ $row['id'] ? url('menus-v2?template='.$selectedGroupId) : '#menu-form-card' }}" class="menu-v2-create js-menu-add">
        <i class="fa fa-plus" aria-hidden="true"></i>
        <span>Add New Menu</span>
      </a>
    </div>

    <div class="menu-v2-groups" id="menu-v2-groups">
      @foreach($userGroups as $group)
        <a href="{{ url('menus-v2?template='.$group->id) }}" class="menu-v2-group {{ (int) $selectedGroupId === (int) $group->id ? 'is-active' : '' }}" data-group-id="{{ (int) $group->id }}">
          <strong>{{ $group->name }}</strong>
          <small>{{ (int) ($groupMenuCounts[$group->id] ?? 0) }} menus &middot; Group {{ (int) $group->status === 1 ? 'active' : 'disabled' }}</small>
        </a>
      @endforeach
    </div>

    <div class="menu-v2-stats" id="menu-v2-stats" aria-live="polite">
      <div class="menu-v2-stat is-static">
        <span class="menu-v2-stat-icon"><i class="fa fa-list" aria-hidden="true"></i></span>
        <span>
          <small>Total menus</small>
          <strong data-stat="total">{{ $stats['total'] ?? 0 }}</strong>
        </span>
      </div>
      <div class="menu-v2-stat is-static">
        <span class="menu-v2-stat-icon success"><i class="fa fa-check" aria-hidden="true"></i></span>
        <span>
          <small>Active</small>
          <strong data-stat="active">{{ $stats['active'] ?? 0 }}</strong>
        </span>
      </div>
      <div class="menu-v2-stat is-static">
        <span class="menu-v2-stat-icon danger"><i class="fa fa-ban" aria-hidden="true"></i></span>
        <span>
          <small>Inactive</small>
          <strong data-stat="inactive">{{ $stats['inactive'] ?? 0 }}</strong>
        </span>
      </div>
      <div class="menu-v2-stat is-static">
        <span class="menu-v2-stat-icon purple"><i class="fa fa-level-up" aria-hidden="true"></i></span>
        <span>
          <small>Root items</small>
          <strong data-stat="root">{{ $stats['root'] ?? 0 }}</strong>
        </span>
      </div>
    </div>

    <div class="row">
      <div class="col-xl-7">
        <div class="menu-v2-card">
          <div class="menu-v2-card-head tree-head">
            <div>
              <h2 class="menu-v2-card-title">Sidebar structure</h2>
              <p class="menu-v2-card-subtitle">Drag to reorder. Use arrows to expand or collapse parent menu groups.</p>
              <span class="menu-v2-dirty-badge" id="order-dirty-badge">
                <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                Unsaved order changes
              </span>
            </div>
            <div class="menu-v2-tools">
              <label class="menu-v2-search" for="menu-tree-search">
                <i class="fa fa-search" aria-hidden="true"></i>
                <input type="search" id="menu-tree-search" placeholder="Search menus or URLs" autocomplete="off">
              </label>
              <span class="menu-v2-ajax-spinner" id="menu-search-spinner">
                <i class="fa fa-spinner" aria-hidden="true"></i>
                <span>Loading</span>
              </span>
              <button type="button" class="menu-v2-mini-btn" id="expand-all">
                <i class="fa fa-angle-double-down" aria-hidden="true"></i>
                <span>Expand all</span>
              </button>
              <button type="button" class="menu-v2-mini-btn" id="collapse-all">
                <i class="fa fa-angle-double-up" aria-hidden="true"></i>
                <span>Collapse all</span>
              </button>
            </div>
          </div>
          <div class="menu-v2-card-body menu-v2-tree-wrap">
            <div class="menu-v2-tree-skeleton" id="menu-tree-skeleton" aria-hidden="true">
              <div class="menu-v2-skeleton-line"></div>
              <div class="menu-v2-skeleton-line"></div>
              <div class="menu-v2-skeleton-line"></div>
              <div class="menu-v2-skeleton-line"></div>
            </div>

            <div id="v2-menu-tree" class="dd {{ count($menuTree) > 0 ? '' : 'd-none' }}">
              <ol class="dd-list">
                @include('v2.app.menus.partials.tree', [
                  'items' => $menuTree,
                  'selectedId' => $row['id'],
                  'selectedGroupId' => $selectedGroupId
                ])
              </ol>
            </div>

            <div class="menu-v2-empty {{ count($menuTree) > 0 ? 'menu-v2-filter-empty' : '' }}" id="menu-tree-empty" style="{{ count($menuTree) > 0 ? 'display:none' : '' }}">
              <i class="fa {{ count($menuTree) > 0 ? 'fa-search' : 'fa-sitemap' }}" aria-hidden="true"></i>
              <strong>{{ count($menuTree) > 0 ? 'No matching menus.' : 'No menus configured for this group.' }}</strong>
              <p>{{ count($menuTree) > 0 ? 'Clear the search box to show the full menu tree.' : 'Create the first menu from the form on the right.' }}</p>
            </div>

            <form method="POST" action="{{ url('menu-v2/re-order') }}" class="menu-v2-reorder" id="reorder-form">
              {{ csrf_field() }}
              <input type="hidden" name="reorder" id="reorder" value="">
              <input type="hidden" name="user_group_id" value="{{ $selectedGroupId }}">
              <small id="reorder-help">Maximum nesting is limited to 3 levels for safer sidebar rendering.</small>
              <button type="submit" class="menu-v2-btn primary js-menu-reorder">
                <i class="fa fa-random" aria-hidden="true"></i>
                <span id="reorder-label">Save order</span>
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-xl-5">
        <div class="menu-v2-card menu-v2-form-card" id="menu-form-card">
          <div class="menu-v2-card-head">
            <div>
              <h2 class="menu-v2-card-title">{{ $row['id'] ? 'Edit menu' : 'Create menu' }}</h2>
              <p class="menu-v2-card-subtitle">{{ $row['id'] ? 'Update the selected menu item.' : 'Add a new item to this group.' }}</p>
              <span class="menu-v2-dirty-badge" id="form-dirty-badge">
                <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                Unsaved form changes
              </span>
            </div>
          </div>
          <div class="menu-v2-card-body">
            <div class="menu-v2-preview">
              <span class="menu-v2-preview-icon"><i id="menu-icon-preview" class="{{ old('menu_icon', $row['icon'] ?: 'fa fa-sitemap') }}" aria-hidden="true"></i></span>
              <span>
                <strong id="menu-title-preview">{{ old('name', $row['name']) ?: 'Enter menu details' }}</strong>
                <small id="menu-url-preview">{{ old('url', $row['url']) ?: 'URL not set' }}</small>
              </span>
            </div>

            <form class="menu-v2-form" action="{{ url('menu-v2/save') }}" method="POST" id="menu-save-form">
              {{ csrf_field() }}
              <input type="hidden" name="id" value="{{ old('id', $row['id']) }}">
              <input type="hidden" name="group_id" value="{{ $selectedGroupId }}">
              <input type="hidden" name="ordering" value="{{ old('ordering', $row['ordering']) }}">

              <div class="form-group">
                <label for="parent_id">Parent</label>
                <select id="parent_id" name="parent_id" class="form-control">
                  <option value="0">Root level</option>
                  @foreach($flatMenus as $menuOption)
                    @if((string) $menuOption['id'] !== (string) $row['id'])
                      <option value="{{ $menuOption['id'] }}" {{ (string) old('parent_id', $row['parent_id']) === (string) $menuOption['id'] ? 'selected' : '' }}>
                        {{ $menuOption['label'] }}
                      </option>
                    @endif
                  @endforeach
                </select>
                <span class="menu-v2-field-error js-field-error" id="parent_id-live-error"></span>
              </div>

              <div class="form-group">
                <label for="section" class="is-required">Sidebar section</label>
                <select id="section" name="section" class="form-control" required aria-required="true">
                  @foreach($menuSections as $sectionKey => $sectionLabel)
                    <option value="{{ $sectionKey }}" {{ old('section', $row['section'] ?? 'services') === $sectionKey ? 'selected' : '' }}>
                      {{ $sectionLabel }}
                    </option>
                  @endforeach
                </select>
                <small class="menu-v2-help">Controls the Account, Services, History, or Settings group in the V2 sidebar.</small>
                @if($errors->has('section'))<span class="menu-v2-field-error">{{ $errors->first('section') }}</span>@endif
                <span class="menu-v2-field-error js-field-error" id="section-live-error"></span>
              </div>

              <div class="form-group">
                <label for="name" class="is-required">Menu title</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $row['name']) }}" placeholder="Example: Users" required maxlength="100" aria-required="true">
                <input type="hidden" id="url" name="url" value="{{ old('url', $row['url']) }}">
                <small class="menu-v2-help" id="url-full-preview">URL is generated automatically from the title.</small>
                @if($errors->has('name'))<span class="menu-v2-field-error">{{ $errors->first('name') }}</span>@endif
                @if($errors->has('url'))<span class="menu-v2-field-error">{{ $errors->first('url') }}</span>@endif
                <span class="menu-v2-field-error js-field-error" id="name-live-error"></span>
                <span class="menu-v2-field-error js-field-error" id="url-live-error"></span>
              </div>

              @if(defined('ENABLE_MULTI_LANG') && ENABLE_MULTI_LANG == 1)
                <div class="form-group">
                  <label for="language_title_fr">French title</label>
                  <input type="text" id="language_title_fr" name="language_title[fr]" class="form-control" value="{{ old('language_title.fr', isset($trans_lang['title']['fr']) ? $trans_lang['title']['fr'] : '') }}" placeholder="French menu title">
                </div>
              @endif

              <div class="form-group">
                <label for="menu_icon">Icon class</label>
                @php
                  $menuIconOptions = collect([
                    'fa fa-tachometer',
                    'fa fa-users',
                    'fa fa-user',
                    'fa fa-user-circle',
                    'fa fa-cogs',
                    'fa fa-cog',
                    'fa fa-gift',
                    'fa fa-sitemap',
                    'fa fa-globe',
                    'fa fa-server',
                    'fa fa-database',
                    'fa fa-info-circle',
                    'fa fa-file-pdf-o',
                    'fa fa-credit-card',
                    'fa fa-list',
                    'fa fa-check',
                    'fa fa-ban',
                    'fa fa-ticket',
                    'fa fa-plug',
                    'fa fa-refresh',
                    'fa fa-exchange',
                    'fa fa-history',
                    'fa fa-money',
                    'fa fa-shopping-cart',
                    'fa fa-bar-chart',
                  ])->merge($menus->pluck('icon'))->filter()->map(function($icon) {
                    return trim($icon);
                  })->filter()->unique()->values();
                @endphp
                <input type="text" id="menu_icon" name="menu_icon" class="form-control" value="{{ old('menu_icon', $row['icon']) }}" placeholder="fa fa-users" maxlength="155" list="menu-icon-options">
                <datalist id="menu-icon-options">
                  @foreach($menuIconOptions as $iconOption)
                    <option value="{{ $iconOption }}"></option>
                  @endforeach
                </datalist>
                <div class="menu-v2-icon-picker" id="menu-icon-picker" aria-label="Choose menu icon">
                  @foreach($menuIconOptions as $iconOption)
                    <button type="button" class="menu-v2-icon-choice {{ old('menu_icon', $row['icon']) === $iconOption ? 'is-active' : '' }}" data-icon="{{ $iconOption }}" title="{{ $iconOption }}" aria-label="Use icon {{ $iconOption }}">
                      <i class="{{ $iconOption }}" aria-hidden="true"></i>
                    </button>
                  @endforeach
                </div>
                <small class="menu-v2-help">Common examples: fa fa-users, fa fa-cogs, fa fa-gift, fa fa-globe.</small>
                @if($errors->has('menu_icon'))<span class="menu-v2-field-error">{{ $errors->first('menu_icon') }}</span>@endif
                <span class="menu-v2-field-error js-field-error" id="menu_icon-live-error"></span>
              </div>

              <div class="form-group">
                <label for="position" class="is-required">Position</label>
                <select id="position" name="position" class="form-control" required aria-required="true">
                  <option value="sidebar" {{ old('position', $row['position']) === 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                  <option value="top" {{ old('position', $row['position']) === 'top' ? 'selected' : '' }}>Top</option>
                  <option value="both" {{ old('position', $row['position']) === 'both' ? 'selected' : '' }}>Both</option>
                </select>
                <span class="menu-v2-field-error js-field-error" id="position-live-error"></span>
              </div>

              <div class="form-group">
                <label class="is-required">Status</label>
                <div class="menu-v2-switches">
                  <label class="menu-v2-switch">
                    <input type="radio" name="is_active" value="1" {{ (string) old('is_active', $row['status']) === '1' ? 'checked' : '' }}>
                    <span>Active</span>
                  </label>
                  <label class="menu-v2-switch">
                    <input type="radio" name="is_active" value="0" {{ (string) old('is_active', $row['status']) === '0' ? 'checked' : '' }}>
                    <span>Inactive</span>
                  </label>
                </div>
              </div>

              <div class="menu-v2-actions">
                <button type="submit" class="menu-v2-btn primary" id="menu-save-btn" disabled>
                  <i class="fa fa-save" aria-hidden="true"></i>
                  <span>Save menu</span>
                </button>
                <a href="{{ url('menus-v2?template='.$selectedGroupId) }}" class="menu-v2-btn js-menu-clear">
                  <i class="fa fa-eraser" aria-hidden="true"></i>
                  <span>Clear</span>
                </a>
                <button type="submit" form="delete-menu-form" class="menu-v2-btn danger js-delete-menu {{ $row['id'] ? '' : 'd-none' }}">
                  <i class="fa fa-trash" aria-hidden="true"></i>
                  <span>Delete</span>
                </button>
              </div>
            </form>
            <form method="POST" action="{{ $row['id'] ? url('menu-v2/remove/'.$row['id'].'?template='.$selectedGroupId) : '#' }}" id="delete-menu-form" class="d-none">
              {{ csrf_field() }}
            </form>
          </div>
        </div>
        @if(!empty($canViewMenuAudit))
        <div class="menu-v2-card mt-3" id="menu-audit-card">
          <div class="menu-v2-card-head">
            <div>
              <h2 class="menu-v2-card-title">Recent Menu Audit</h2>
              <p class="menu-v2-card-subtitle">Visible only to authorized root administrators.</p>
            </div>
          </div>
          <div class="menu-v2-card-body" id="menu-audit-body">
            @if($recentAuditLogs->count() > 0)
              <div class="menu-v2-audit-list">
                @foreach($recentAuditLogs as $auditLog)
                  <div class="menu-v2-audit-item">
                    <div class="menu-v2-audit-main">
                      <span class="menu-v2-audit-action">
                        <i class="fa fa-history" aria-hidden="true"></i>
                        {{ ucwords(str_replace('_', ' ', $auditLog->action)) }}
                      </span>
                      <span class="v2-status is-active">{{ $auditLog->module }}</span>
                    </div>
                    <div class="menu-v2-audit-meta">
                      <span>User #{{ $auditLog->user_id ?: 'system' }}</span>
                      <span>{{ $auditLog->ip_address ?: 'no-ip' }}</span>
                      <span>{{ $auditLog->created_at }}</span>
                    </div>
                    <details class="menu-v2-audit-details">
                      <summary>View values</summary>
                      <pre>Old: {{ \Illuminate\Support\Str::limit((string) $auditLog->old_values, 260) }}
                        New: {{ \Illuminate\Support\Str::limit((string) $auditLog->new_values, 260) }}</pre>
                    </details>
                  </div>
                @endforeach
              </div>
            @else
              <div class="menu-v2-empty">
                <i class="fa fa-history" aria-hidden="true"></i>
                <strong>No menu audit records yet.</strong>
                <p>Create, update, delete, or reorder a V2 menu to generate an audit entry.</p>
              </div>
            @endif
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>

@section('script')
<script type="text/javascript" src="{{ asset('vendor/common/jquery.nestable.js') }}"></script>
<script src="{{ asset('js/v2/menus-v2.js') }}"></script>
