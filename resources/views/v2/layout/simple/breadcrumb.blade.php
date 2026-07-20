@php
    $breadcrumbItems = isset($data) && is_array($data) ? array_values($data) : [];
    $dashboardActive = empty($breadcrumbItems);
    $dashboardUrl = \Illuminate\Support\Facades\Route::has('dashboard.v2') ? route('dashboard.v2') : url('dashboard-v2');
    $locale = session('locale', 'en');
    $homeLabel = $locale === 'fr' ? 'Accueil' : 'Home';
    $dashboardLabel = $locale === 'fr' ? 'Tableau de bord' : 'Dashboard';
    $pageTitle = $page_title ?? $dashboardLabel;
    $showCurrentBreadcrumb = !empty($show_current_breadcrumb);
@endphp

@section('breadcrumb-title')
    <div class="v2-page-title-main">
        <h3 class="v2-page-title-heading">{{ $pageTitle }}</h3>
    </div>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item v2-breadcrumb-item">
        <a class="v2-breadcrumb-link" href="{{ $dashboardUrl }}">
            <i class="fa fa-home v2-breadcrumb-icon" aria-hidden="true"></i>
            <span>{{ $homeLabel }}</span>
        </a>
    </li>

    @if($dashboardActive && $showCurrentBreadcrumb)
        <li class="breadcrumb-item v2-breadcrumb-item active" aria-current="page">
            <span class="v2-breadcrumb-current">
                <span>{{ $pageTitle }}</span>
            </span>
        </li>
    @else
        @foreach($breadcrumbItems as $item)
            @php
                $isActive = !empty($item['active']);
                $itemUrl = trim((string) ($item['url'] ?? ''));
                $isUnsafeBreadcrumbUrl = preg_match('/^(?:javascript:|data:|vbscript:|\/\/)/i', $itemUrl) === 1;
                $safeItemUrl = (!$isUnsafeBreadcrumbUrl && $itemUrl !== '') ? $itemUrl : null;
                $rawName = $item['name'] ?? '';
                $itemName = is_array($rawName)
                    ? ($rawName[session('locale')] ?? reset($rawName) ?? '')
                    : $rawName;
            @endphp
            @if(!$isActive || $showCurrentBreadcrumb)
                <li class="breadcrumb-item v2-breadcrumb-item {{ $isActive ? 'active' : '' }}" @if($isActive) aria-current="page" @endif>
                    @if(!$isActive && !empty($safeItemUrl))
                        <a class="v2-breadcrumb-link" href="{{ $safeItemUrl }}">
                            <span>{{ $itemName }}</span>
                        </a>
                    @else
                        <span class="v2-breadcrumb-current">
                            <span>{{ $itemName }}</span>
                        </span>
                    @endif
                </li>
            @endif
        @endforeach
    @endif
@endsection
