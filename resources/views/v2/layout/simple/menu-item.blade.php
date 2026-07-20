@php
    $children = $menu['children'] ?? $menu['childs'] ?? [];
    $children = is_array($children) ? $children : [];
    $menuUrl = trim((string) ($menu['url'] ?? ''));
    $menuPath = ltrim($menuUrl, '/');
    $hasUnsafeScheme = preg_match('/^(?:[a-z][a-z0-9+\-.]*:|\/\/)/i', $menuUrl) === 1;
    $hasChildren = !empty($children);
    $isActive = !$hasUnsafeScheme && $menuPath !== '' && (request()->is($menuPath) || request()->is($menuPath.'/*'));
    $menuHref = (!$hasUnsafeScheme && $menuPath !== '') ? url($menuPath) : '#';
@endphp

<li class="sidebar-list {{ $isActive ? 'active' : '' }} {{ $hasChildren ? 'dropdown' : '' }}">
    <a class="sidebar-link sidebar-title {{ $hasChildren ? '' : 'link-nav' }}"
       href="{{ $menuHref }}">
        <i class="{{ $menu['icon'] ?? 'fa fa-circle' }} dynamic-icon"></i>
        <span>{{ $menu['name'] ?? '' }}</span>
    </a>
    @if ($hasChildren)
        <ul class="sidebar-submenu">
            @foreach ($children as $childMenu)
                @include('v2.layout.simple.menu-item', ['menu' => $childMenu])
            @endforeach
        </ul>
    @endif
</li>
