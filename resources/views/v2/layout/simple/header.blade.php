<div class="page-header" data-v2-header>
  <div class="header-wrapper v2-header-shell row m-0">
    @php
      $user = auth()->user();
      $locale = session('locale', 'en');
      $langText = $locale == 'fr' ? 'FR' : 'EN';
      $groupName = optional(optional($user)->group)->name;
      $username = optional($user)->username ?: optional($user)->name ?: 'User';
      $sidebarLabel = $locale == 'fr' ? 'Basculer la navigation' : 'Toggle navigation';
      $themeLabel = $locale == 'fr' ? 'Changer le theme' : 'Toggle theme';
      $languageLabel = $locale == 'fr' ? 'Choisir la langue' : 'Select language';
      $profileLabel = $locale == 'fr' ? 'Profil' : 'Profile';
      $logoutLabel = $locale == 'fr' ? 'Deconnexion' : 'Log out';
      $profileUrl = \Illuminate\Support\Facades\Route::has('profile.v2') ? route('profile.v2') : url('profile-v2');
    @endphp

    <div class="header-logo-wrapper v2-header-brand v2-header-breadcrumb-area col-auto p-0">
      <button type="button" class="toggle-sidebar v2-header-icon-button" aria-label="{{ $sidebarLabel }}" aria-expanded="true" data-v2-no-tooltip="true">
        <i class="status_toggle middle sidebar-toggle" data-feather="align-center" aria-hidden="true"></i>
      </button>

      <div class="v2-header-breadcrumb-card" data-v2-header-breadcrumb>
        <div class="v2-header-breadcrumb-title">
          @yield('breadcrumb-title')
        </div>
        <nav class="v2-header-breadcrumb-nav" aria-label="Breadcrumb">
          <ol class="breadcrumb v2-header-breadcrumb-list">
            @yield('breadcrumb-items')
          </ol>
        </nav>
      </div>
    </div>

    <div class="nav-right v2-header-actions col-auto pull-right right-header p-0 ms-auto">
      <ul class="nav-menus">
        <li class="v2-header-theme-item">
          <button type="button" class="mode v2-header-icon-button v2-theme-toggle" aria-label="{{ $themeLabel }}" data-v2-no-tooltip="true">
            <span class="v2-theme-option v2-theme-option--light">
              <i class="fa fa-sun v2-theme-icon" aria-hidden="true"></i>
              <span class="visually-hidden">Light</span>
            </span>
            <span class="v2-theme-option v2-theme-option--dark">
              <i class="fa fa-moon v2-theme-icon" aria-hidden="true"></i>
              <span class="visually-hidden">Dark</span>
            </span>
          </button>
        </li>

        <li class="language-nav v2-language-nav">
          <div class="translate_wrapper v2-language-toggle" role="group" aria-label="{{ $languageLabel }}">
            <a href="{{ route('lang.switch','en') }}" role="button" data-value="en" class="lang v2-language-toggle-option {{ $locale == 'en' ? 'active selected' : '' }}" aria-label="Switch language to English" aria-pressed="{{ $locale == 'en' ? 'true' : 'false' }}" data-v2-no-tooltip="true">EN</a>
            <a href="{{ route('lang.switch','fr') }}" role="button" data-value="fr" class="lang v2-language-toggle-option {{ $locale == 'fr' ? 'active selected' : '' }}" aria-label="Switch language to French" aria-pressed="{{ $locale == 'fr' ? 'true' : 'false' }}" data-v2-no-tooltip="true">FR</a>
          </div>
        </li>

        <li class="profile-nav v2-profile-nav onhover-dropdown pe-0 py-0 d-none d-lg-block">
          <button type="button" class="media profile-media v2-profile-trigger d-flex align-items-center" aria-label="{{ $profileLabel }}" aria-haspopup="true" aria-expanded="false">
            <span class="v2-profile-avatar" aria-hidden="true">{{ strtoupper(substr($username, 0, 1)) }}</span>
            <span class="media-body v2-profile-copy">
              <span class="fw-bold">{{ $username }}</span>
              <small>{{ $groupName ?? 'Member' }}</small>
            </span>
            <i class="middle fa fa-angle-down" aria-hidden="true"></i>
          </button>
          <ul class="profile-dropdown onhover-show-div v2-profile-dropdown">
            <li>
              <a href="{{ $profileUrl }}"><i data-feather="user"></i><span>{{ $profileLabel }}</span></a>
            </li>

            @if(\Session::has('impersonated') && \Session::get('impersonated') == 'true')
            <li>
              <a href="{{ url('user/end/impersonate/'.\App\Library\SecurityHelper::simpleEncDec('ec',auth()->user()->id)) }}">
                <i class="fa fa-exclamation-triangle"></i>&nbsp;{{ trans('common.lbl_stop_imper') }}
              </a>
            </li>
            @endif

            <li>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
              <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i data-feather="log-out"></i> <span>{{ $logoutLabel }}</span>
              </a>
            </li>
          </ul>
        </li>

        <li class="d-lg-none">
          <a class="v2-header-icon-button" href="{{ $profileUrl }}" aria-label="{{ $profileLabel }}">
            <i data-feather="user"></i>
          </a>
        </li>

        @if(\Session::has('impersonated') && \Session::get('impersonated') == 'true')
        <li class="d-lg-none">
          <a class="v2-header-icon-button v2-header-icon-button--warning"
            href="{{ url('user/end/impersonate/'.\App\Library\SecurityHelper::simpleEncDec('ec',auth()->user()->id)) }}"
            aria-label="{{ trans('common.lbl_stop_imper') }}">
            <i class="fa fa-exclamation-triangle"></i>
          </a>
        </li>
        @endif

        <li class="d-lg-none">
          <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
          <a class="v2-header-icon-button" href="#"
            onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();"
            aria-label="{{ $logoutLabel }}">
            <i data-feather="log-out"></i>
          </a>
        </li>
      </ul>
    </div>

    <script class="result-template" type="text/x-handlebars-template">
      <div class="ProfileCard u-cf">
        <div class="ProfileCard-avatar">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0">
            <path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path>
            <polygon points="12 15 17 21 7 21 12 15"></polygon>
          </svg>
        </div>
        <div class="ProfileCard-details"></div>
      </div>
    </script>
    <script class="empty-template" type="text/x-handlebars-template">
      <div class="EmptyMessage">Your search turned up 0 results. This most likely means the backend is down, yikes!</div>
    </script>
  </div>
</div>
