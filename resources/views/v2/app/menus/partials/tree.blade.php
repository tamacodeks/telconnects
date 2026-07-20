@foreach($items as $item)
  @php
    $displayName = $item['name'];
    if ($displayName === 'All User') {
      $displayName = 'All Users';
    } elseif ($displayName === 'Accessed Topup') {
      $displayName = 'Top-up Access';
    }
  @endphp
  <li data-id="{{ $item['id'] }}" class="dd-item dd3-item v2-tree-item {{ (string) $selectedId === (string) $item['id'] ? 'is-editing' : '' }}">
    <div class="dd-handle dd3-handle" title="Drag {{ $displayName }} to reorder" role="button" tabindex="0" aria-label="Drag {{ $displayName }} to reorder">
      <i class="fa fa-bars" aria-hidden="true"></i>
    </div>
    <div class="dd3-content v2-tree-row" data-menu-name="{{ strtolower($displayName) }}" data-menu-url="{{ strtolower($item['url']) }}" data-menu-section="{{ strtolower($item['section'] ?? 'services') }}" data-menu-status="{{ (int) $item['status'] === 1 ? 'active' : 'inactive' }}" title="Updated {{ $item['updated_at'] ?: 'not available' }}">
      <span class="v2-tree-main">
        <span class="v2-tree-icon"><i class="{{ $item['icon'] ?: 'fa fa-sitemap' }}" aria-hidden="true"></i></span>
        <span class="v2-tree-copy">
          <strong>{{ $displayName }}</strong>
          <small>{{ $item['url'] ?: '#' }} &middot; {{ $item['section_label'] ?? ucfirst($item['section'] ?? 'services') }}</small>
        </span>
      </span>
      <span class="v2-tree-actions">
        <button type="button"
          class="v2-status js-status-toggle {{ (int) $item['status'] === 1 ? 'is-active' : 'is-inactive' }}"
          aria-label="Change status for {{ $displayName }}"
          title="Change status for {{ $displayName }}"
          data-id="{{ (int) $item['id'] }}"
          data-next-status="{{ (int) $item['status'] === 1 ? 0 : 1 }}">
          <i class="fa {{ (int) $item['status'] === 1 ? 'fa-check' : 'fa-ban' }}" aria-hidden="true"></i>
          {{ (int) $item['status'] === 1 ? 'Active' : 'Off' }}
        </button>
        <a href="{{ url('menus-v2/'.$item['id'].'?template='.$selectedGroupId) }}" class="v2-tree-edit" data-id="{{ (int) $item['id'] }}" title="Edit {{ $displayName }}" aria-label="Edit {{ $displayName }}">
          <i class="fa fa-pencil" aria-hidden="true"></i>
        </a>
      </span>
    </div>
    @if(!empty($item['children']))
      <ol class="dd-list">
        @include('v2.app.menus.partials.tree', [
          'items' => $item['children'],
          'selectedId' => $selectedId,
          'selectedGroupId' => $selectedGroupId
        ])
      </ol>
    @endif
  </li>
@endforeach
