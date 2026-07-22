@php
  $themeContext = $themeContext ?? 'app';
  $themeColor = function ($key, $default) {
      $value = defined($key) ? constant($key) : $default;
      $value = strtoupper(trim((string) $value));

      return preg_match('/^#[0-9A-F]{6}$/', $value) ? $value : $default;
  };
  $hexRgb = function ($hex) {
      $hex = ltrim($hex, '#');

      return hexdec(substr($hex, 0, 2)) . ', ' . hexdec(substr($hex, 2, 2)) . ', ' . hexdec(substr($hex, 4, 2));
  };
  $contrastColor = function ($hex) {
      $hex = ltrim($hex, '#');
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));
      $luma = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

      return $luma > 156 ? '#111827' : '#FFFFFF';
  };

  $themePrimary = $themeColor('THEME_PRIMARY_COLOR', '#1764A8');
  $themeAccent = $themeColor('THEME_ACCENT_COLOR', '#1DABF2');
  $themeLogin = $themeColor('THEME_LOGIN_COLOR', '#1764A8');
  $themeHeader = $themeColor('THEME_HEADER_COLOR', '#FFFFFF');
  $themeHeaderText = $themeColor('THEME_HEADER_TEXT_COLOR', $contrastColor($themeHeader));
  $themeSidebar = $themeColor('THEME_SIDEBAR_COLOR', '#FFFFFF');
  $themeSidebarActive = $themeColor('THEME_SIDEBAR_ACTIVE_COLOR', '#1764A8');
  $themeSidebarText = $themeColor('THEME_SIDEBAR_TEXT_COLOR', '#1F2937');
  $themeButton = $themeColor('THEME_BUTTON_COLOR', '#1764A8');
  $themeButtonText = $themeColor('THEME_BUTTON_TEXT_COLOR', '#FFFFFF');
  $themeDashboardBg = $themeColor('THEME_DASHBOARD_BACKGROUND_COLOR', '#F4F8FC');
  $themeDashboardCard = $themeColor('THEME_DASHBOARD_CARD_COLOR', '#FFFFFF');
  $themeDashboardText = $themeColor('THEME_DASHBOARD_TEXT_COLOR', '#1F2937');
  $themeDashboardMuted = $themeColor('THEME_DASHBOARD_MUTED_COLOR', '#6B7280');
  $themeDashboardBorder = $themeColor('THEME_DASHBOARD_BORDER_COLOR', '#D8E3EE');
  $themeDarkSurface = $themeColor('THEME_DARK_SURFACE_COLOR', '#161311');
  $themeDarkCard = $themeColor('THEME_DARK_CARD_COLOR', '#221A16');
  $themeDarkText = $themeColor('THEME_DARK_TEXT_COLOR', '#F5F5F5');
  $themeDarkMuted = $themeColor('THEME_DARK_MUTED_COLOR', '#A8A8A8');
  $themeDarkBorder = $themeColor('THEME_DARK_BORDER_COLOR', '#3A2A22');
  $colorLuma = function ($hex) {
      $hex = ltrim($hex, '#');
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));

      return (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
  };
  $themeLightHeader = $themeHeader;
  $themeLightHeaderText = $themeHeaderText;
  $themeLightSidebar = $themeSidebar;
  $themeLightSidebarText = $themeSidebarText;
  $themeLightDashboardBg = $themeDashboardBg;
  $themeLightDashboardCard = $themeDashboardCard;
  $themeLightDashboardText = $themeDashboardText;
  $themeLightDashboardMuted = $themeDashboardMuted;
  $themeLightDashboardBorder = $themeDashboardBorder;

  $lightSurfaceLooksDark = $colorLuma($themeDashboardBg) < 110 && $colorLuma($themeDashboardCard) < 110;
  $lightSurfaceMatchesDark = $themeDashboardBg === $themeDarkSurface && $themeDashboardCard === $themeDarkCard;

  if ($lightSurfaceLooksDark || $lightSurfaceMatchesDark) {
      if ($colorLuma($themeHeader) < 110) {
          $themeLightHeader = '#FFFFFF';
          $themeLightHeaderText = '#1F2937';
      }

      if ($colorLuma($themeSidebar) < 110) {
          $themeLightSidebar = '#FFFFFF';
          $themeLightSidebarText = '#1F2937';
      }

      $themeLightDashboardBg = '#F8F6F4';
      $themeLightDashboardCard = '#FFFFFF';
      $themeLightDashboardText = '#1F2937';
      $themeLightDashboardMuted = '#6B7280';
      $themeLightDashboardBorder = '#E7DED7';
  }

  $themeDarkHeader = $colorLuma($themeHeader) < 110 ? $themeHeader : $themeDarkCard;
  $themeDarkHeaderText = $colorLuma($themeHeader) < 110 ? $themeHeaderText : $themeDarkText;
@endphp

<style id="v2-dynamic-theme">
:root{
  --theme-primary: {{ $themePrimary }} !important;
  --theme-primary-rgb: {{ $hexRgb($themePrimary) }} !important;
  --theme-accent: {{ $themeAccent }} !important;
  --theme-accent-rgb: {{ $hexRgb($themeAccent) }} !important;
  --theme-login-primary: {{ $themeLogin }} !important;
  --theme-login-rgb: {{ $hexRgb($themeLogin) }} !important;
  --theme-header-bg: {{ $themeLightHeader }} !important;
  --theme-header-rgb: {{ $hexRgb($themeLightHeader) }} !important;
  --theme-header-text: {{ $themeLightHeaderText }} !important;
  --theme-header-text-rgb: {{ $hexRgb($themeLightHeaderText) }} !important;
  --theme-dark-header-bg: {{ $themeDarkHeader }} !important;
  --theme-dark-header-rgb: {{ $hexRgb($themeDarkHeader) }} !important;
  --theme-dark-header-text: {{ $themeDarkHeaderText }} !important;
  --theme-dark-header-text-rgb: {{ $hexRgb($themeDarkHeaderText) }} !important;
  --theme-sidebar-bg: {{ $themeLightSidebar }} !important;
  --theme-sidebar-rgb: {{ $hexRgb($themeLightSidebar) }} !important;
  --theme-sidebar-active: {{ $themeSidebarActive }} !important;
  --theme-sidebar-active-rgb: {{ $hexRgb($themeSidebarActive) }} !important;
  --theme-sidebar-text: {{ $themeLightSidebarText }} !important;
  --theme-sidebar-text-rgb: {{ $hexRgb($themeLightSidebarText) }} !important;
  --theme-button-bg: {{ $themeButton }} !important;
  --theme-button-rgb: {{ $hexRgb($themeButton) }} !important;
  --theme-button-text: {{ $themeButtonText }} !important;
  --theme-dashboard-bg: {{ $themeLightDashboardBg }} !important;
  --theme-dashboard-bg-rgb: {{ $hexRgb($themeLightDashboardBg) }} !important;
  --theme-dashboard-card: {{ $themeLightDashboardCard }} !important;
  --theme-dashboard-card-rgb: {{ $hexRgb($themeLightDashboardCard) }} !important;
  --theme-dashboard-text: {{ $themeLightDashboardText }} !important;
  --theme-dashboard-text-rgb: {{ $hexRgb($themeLightDashboardText) }} !important;
  --theme-dashboard-muted: {{ $themeLightDashboardMuted }} !important;
  --theme-dashboard-muted-rgb: {{ $hexRgb($themeLightDashboardMuted) }} !important;
  --theme-dashboard-border: {{ $themeLightDashboardBorder }} !important;
  --theme-dashboard-border-rgb: {{ $hexRgb($themeLightDashboardBorder) }} !important;
  --theme-dark-surface: {{ $themeDarkSurface }} !important;
  --theme-dark-surface-rgb: {{ $hexRgb($themeDarkSurface) }} !important;
  --theme-dark-card: {{ $themeDarkCard }} !important;
  --theme-dark-card-rgb: {{ $hexRgb($themeDarkCard) }} !important;
  --theme-dark-text: {{ $themeDarkText }} !important;
  --theme-dark-text-rgb: {{ $hexRgb($themeDarkText) }} !important;
  --theme-dark-muted: {{ $themeDarkMuted }} !important;
  --theme-dark-muted-rgb: {{ $hexRgb($themeDarkMuted) }} !important;
  --theme-dark-border: {{ $themeDarkBorder }} !important;
  --theme-dark-border-rgb: {{ $hexRgb($themeDarkBorder) }} !important;
  --legacy-brand-blue: var(--theme-primary) !important;
  --legacy-brand-sky: var(--theme-accent) !important;
  --theme-deafult: var(--theme-primary) !important;
  --theme-default: var(--theme-primary) !important;
  --v2-nav-blue: var(--theme-primary) !important;
  --v2-nav-primary: var(--theme-sidebar-active) !important;
  --v2-nav-blue-dark: var(--theme-sidebar-active) !important;
  --v2-nav-hover-ink: var(--theme-primary) !important;
  --v2-nav-hover-icon: var(--theme-primary) !important;
  --v2-nav-hover-bg: rgba(var(--theme-primary-rgb), .08) !important;
  --v2-nav-hover-border: rgba(var(--theme-primary-rgb), .18) !important;
  --v2-nav-hover-shadow: none !important;
  --v2-sidebar-surface: var(--theme-sidebar-bg) !important;
  --v2-sidebar-border: rgba(var(--theme-sidebar-active-rgb), .16) !important;
  --v2-sidebar-hover: rgba(var(--theme-primary-rgb), .08) !important;
  --v2-sidebar-active-start: var(--theme-sidebar-active) !important;
  --v2-sidebar-active-end: var(--theme-primary) !important;
  --v2-sidebar-muted: var(--theme-dashboard-muted) !important;
  --dash-blue: var(--theme-primary) !important;
  --dash-violet: var(--theme-primary) !important;
  --dash-cyan: var(--theme-accent) !important;
  --dash-ink-1: var(--theme-dashboard-text) !important;
  --dash-ink-2: var(--theme-dashboard-muted) !important;
  --card-border: rgba(var(--theme-dashboard-border-rgb), .92) !important;
  --card-soft: rgba(var(--theme-dashboard-card-rgb), .74) !important;
  --profile-v2-blue: var(--theme-primary) !important;
  --profile-v2-blue-dark: var(--theme-button-bg) !important;
  --menu-v2-blue: var(--theme-primary) !important;
  --ccpl-primary: var(--theme-primary) !important;
  --ccpl-primary-dark: var(--theme-button-bg) !important;
  --settings-v2-primary: var(--theme-button-bg) !important;
}

.btn-primary,
.btn-theme,
.btn.btn-primary,
.btn.btn-theme,
button.btn-primary,
button.btn-theme{
  color: var(--theme-button-text) !important;
  background: var(--theme-button-bg) !important;
  background-image: none !important;
  border-color: var(--theme-button-bg) !important;
  box-shadow: 0 10px 20px rgba(var(--theme-button-rgb), .20) !important;
}

.btn-primary:hover,
.btn-primary:focus,
.btn-theme:hover,
.btn-theme:focus,
.btn.btn-primary:hover,
.btn.btn-primary:focus,
.btn.btn-theme:hover,
.btn.btn-theme:focus{
  color: var(--theme-button-text) !important;
  background: var(--theme-primary) !important;
  border-color: var(--theme-primary) !important;
  box-shadow: 0 12px 24px rgba(var(--theme-button-rgb), .28) !important;
}

@if($themeContext === 'auth')
body{
  --legacy-brand-blue: var(--theme-login-primary) !important;
  --field-focus: var(--theme-login-primary) !important;
  --field-ring: rgba(var(--theme-login-rgb), .18) !important;
  --otp-ring: rgba(var(--theme-login-rgb), .22) !important;
  --brand-grad: linear-gradient(145deg, var(--theme-primary) 0%, var(--theme-login-primary) 48%, var(--theme-accent) 100%) !important;
  --btn-shadow-hov: rgba(var(--theme-button-rgb), .28) !important;
  --btn-shadow-act: rgba(var(--theme-button-rgb), .20) !important;
}

body,
body.light{
  --page-grad:
    radial-gradient(900px 480px at 8% 12%, rgba(var(--theme-accent-rgb), .14) 0%, rgba(var(--theme-accent-rgb), 0) 65%),
    radial-gradient(760px 420px at 92% 8%, rgba(var(--theme-login-rgb), .12) 0%, rgba(var(--theme-login-rgb), 0) 60%),
    linear-gradient(135deg, #edf6fc 0%, #f6fbfd 46%, #ffffff 100%) !important;
  --side-bg:
    radial-gradient(circle at top left, rgba(var(--theme-accent-rgb), .16) 0%, rgba(var(--theme-accent-rgb), 0) 48%),
    linear-gradient(180deg, rgba(245, 250, 253, .98), rgba(231, 243, 251, .94)) !important;
  --card-border: rgba(var(--theme-login-rgb), .14) !important;
  --card-shadow: 0 28px 70px rgba(var(--theme-login-rgb), .16) !important;
}

@media (prefers-color-scheme: dark) {
  body:not(.light){
    --page-grad:
      radial-gradient(900px 480px at 8% 12%, rgba(var(--theme-accent-rgb), .16) 0%, rgba(var(--theme-accent-rgb), 0) 65%),
      radial-gradient(760px 420px at 92% 8%, rgba(var(--theme-login-rgb), .20) 0%, rgba(var(--theme-login-rgb), 0) 60%),
      linear-gradient(135deg, var(--theme-dark-surface) 0%, var(--theme-dark-card) 100%) !important;
    --side-bg:
      radial-gradient(circle at top left, rgba(var(--theme-accent-rgb), .18) 0%, rgba(var(--theme-accent-rgb), 0) 50%),
      linear-gradient(180deg, rgba(var(--theme-dark-surface-rgb), .98), rgba(var(--theme-dark-card-rgb), .94)) !important;
    --card-bg: rgba(var(--theme-dark-card-rgb), .94) !important;
    --panel-grad: linear-gradient(180deg, rgba(var(--theme-dark-card-rgb), .96), rgba(var(--theme-dark-surface-rgb), .94)) !important;
    --card-border: rgba(var(--theme-dark-border-rgb), .92) !important;
    --card-shadow: 0 28px 70px rgba(0, 0, 0, .36) !important;
    --text-default: var(--theme-dark-text) !important;
    --text-muted: var(--theme-dark-muted) !important;
    --text-subtle: rgba(var(--theme-dark-muted-rgb), .76) !important;
    --field-bg: rgba(var(--theme-dark-card-rgb), .88) !important;
    --field-border: rgba(var(--theme-dark-border-rgb), .95) !important;
    --toggle-bg: rgba(var(--theme-dark-card-rgb), .76) !important;
    --toggle-border: rgba(var(--theme-dark-border-rgb), .9) !important;
    --side-border: rgba(var(--theme-dark-border-rgb), .9) !important;
    --chip-bg: rgba(var(--theme-dark-card-rgb), .72) !important;
    --chip-border: rgba(var(--theme-dark-border-rgb), .9) !important;
    --chip-text: var(--theme-dark-text) !important;
  }
}

body.dark-mode,
body.dark-only{
  --page-grad:
    radial-gradient(900px 480px at 8% 12%, rgba(var(--theme-accent-rgb), .16) 0%, rgba(var(--theme-accent-rgb), 0) 65%),
    radial-gradient(760px 420px at 92% 8%, rgba(var(--theme-login-rgb), .20) 0%, rgba(var(--theme-login-rgb), 0) 60%),
    linear-gradient(135deg, var(--theme-dark-surface) 0%, var(--theme-dark-card) 100%) !important;
  --side-bg:
    radial-gradient(circle at top left, rgba(var(--theme-accent-rgb), .18) 0%, rgba(var(--theme-accent-rgb), 0) 50%),
    linear-gradient(180deg, rgba(var(--theme-dark-surface-rgb), .98), rgba(var(--theme-dark-card-rgb), .94)) !important;
  --card-bg: rgba(var(--theme-dark-card-rgb), .94) !important;
  --panel-grad: linear-gradient(180deg, rgba(var(--theme-dark-card-rgb), .96), rgba(var(--theme-dark-surface-rgb), .94)) !important;
  --card-border: rgba(var(--theme-dark-border-rgb), .92) !important;
  --card-shadow: 0 28px 70px rgba(0, 0, 0, .36) !important;
  --text-default: var(--theme-dark-text) !important;
  --text-muted: var(--theme-dark-muted) !important;
  --text-subtle: rgba(var(--theme-dark-muted-rgb), .76) !important;
  --field-bg: rgba(var(--theme-dark-card-rgb), .88) !important;
  --field-border: rgba(var(--theme-dark-border-rgb), .95) !important;
  --toggle-bg: rgba(var(--theme-dark-card-rgb), .76) !important;
  --toggle-border: rgba(var(--theme-dark-border-rgb), .9) !important;
  --side-border: rgba(var(--theme-dark-border-rgb), .9) !important;
  --chip-bg: rgba(var(--theme-dark-card-rgb), .72) !important;
  --chip-border: rgba(var(--theme-dark-border-rgb), .9) !important;
  --chip-text: var(--theme-dark-text) !important;
}

body.dark-mode .auth-side,
body.dark-only .auth-side{
  background: var(--side-bg) !important;
  border-color: rgba(var(--theme-dark-border-rgb), .92) !important;
}

body.dark-mode .auth-title,
body.dark-mode .auth-context-card strong,
body.dark-only .auth-title,
body.dark-only .auth-context-card strong{
  color: var(--theme-dark-text) !important;
}

body.dark-mode .auth-copy,
body.dark-mode .auth-context-card span,
body.dark-mode .auth-context-card small,
body.dark-only .auth-copy,
body.dark-only .auth-context-card span,
body.dark-only .auth-context-card small{
  color: var(--theme-dark-muted) !important;
}

body::before{
  background: radial-gradient(circle, rgba(var(--theme-accent-rgb), .18) 0%, rgba(var(--theme-accent-rgb), 0) 72%) !important;
}

body::after,
.login-card::after,
.auth-side::before{
  background: radial-gradient(circle, rgba(var(--theme-login-rgb), .14) 0%, rgba(var(--theme-login-rgb), 0) 72%) !important;
}

.login-card::before{
  background: linear-gradient(90deg, rgba(var(--theme-accent-rgb), .20), rgba(var(--theme-accent-rgb), .90), rgba(var(--theme-login-rgb), .48)) !important;
}

.auth-chip i,
.auth-kicker,
#resend-otp,
.btn-link{
  color: var(--theme-login-primary) !important;
}
@endif

@if($themeContext === 'app')
html body .page-wrapper.compact-wrapper .page-header[data-v2-header],
html body .page-wrapper.compact-wrapper .page-header[data-v2-header].close_icon,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header],
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header],
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header],
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header],
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header]{
  --v2-header-primary: var(--theme-header-bg) !important;
  --v2-header-primary-dark: var(--theme-header-bg) !important;
  --v2-header-blue: var(--theme-accent) !important;
  --v2-header-ink: var(--theme-header-text) !important;
  --v2-header-muted: rgba(var(--theme-header-text-rgb), .76) !important;
  --v2-header-line: rgba(var(--theme-header-rgb), .38) !important;
  --v2-header-glass: rgba(var(--theme-header-rgb), .96) !important;
  background: var(--theme-header-bg) !important;
  background-image: linear-gradient(135deg, rgba(var(--theme-header-rgb), .98), rgba(var(--theme-header-rgb), .90)) !important;
  border-bottom-color: rgba(var(--theme-header-rgb), .35) !important;
  color: var(--theme-header-text) !important;
  box-shadow: 0 18px 42px rgba(var(--theme-header-rgb), .18) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .header-wrapper,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-area,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card{
  background: transparent !important;
  border-color: transparent !important;
  color: var(--theme-header-text) !important;
  box-shadow: none !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-page-title-heading,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .nav-menus,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .nav-menus a,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .nav-menus button,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger small,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] svg,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] i{
  color: var(--theme-header-text) !important;
  stroke: currentColor !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut{
  color: var(--theme-header-text) !important;
  background: rgba(var(--theme-header-text-rgb), .10) !important;
  border-color: rgba(var(--theme-header-text-rgb), .16) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut:hover,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut.active{
  background: rgba(var(--theme-header-text-rgb), .15) !important;
  border-color: rgba(var(--theme-header-text-rgb), .24) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut-icon,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut-icon i{
  color: var(--theme-primary, #2563eb) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--amber .v2-service-shortcut-icon{
  background: rgba(245, 158, 11, .14) !important;
  border: 1px solid rgba(245, 158, 11, .22) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--amber .v2-service-shortcut-icon,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--amber .v2-service-shortcut-icon i{
  color: #f59e0b !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--green .v2-service-shortcut-icon{
  background: rgba(22, 165, 107, .14) !important;
  border: 1px solid rgba(22, 165, 107, .20) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--green .v2-service-shortcut-icon,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--green .v2-service-shortcut-icon i{
  color: #16a56b !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--blue .v2-service-shortcut-icon{
  background: rgba(var(--theme-primary-rgb, 37, 99, 235), .14) !important;
  border: 1px solid rgba(var(--theme-primary-rgb, 37, 99, 235), .20) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--blue .v2-service-shortcut-icon,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--blue .v2-service-shortcut-icon i{
  color: var(--theme-primary, #2563eb) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger{
  color: var(--theme-header-text) !important;
  background: rgba(255, 255, 255, .14) !important;
  border-color: rgba(255, 255, 255, .24) !important;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .10), 0 10px 22px rgba(0, 0, 0, .12) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle .v2-theme-option,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option{
  color: rgba(var(--theme-header-text-rgb), .78) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header],
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header],
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header],
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header]{
  --v2-header-primary: var(--theme-dark-header-bg) !important;
  --v2-header-primary-dark: var(--theme-dark-header-bg) !important;
  --v2-header-ink: var(--theme-dark-header-text) !important;
  --v2-header-muted: rgba(var(--theme-dark-header-text-rgb), .76) !important;
  --v2-header-line: rgba(var(--theme-dark-header-rgb), .38) !important;
  --v2-header-glass: rgba(var(--theme-dark-header-rgb), .96) !important;
  background: var(--theme-dark-header-bg) !important;
  background-image: linear-gradient(135deg, rgba(var(--theme-dark-header-rgb), .98), rgba(var(--theme-dark-header-rgb), .90)) !important;
  border-bottom-color: rgba(var(--theme-dark-header-rgb), .35) !important;
  color: var(--theme-dark-header-text) !important;
  box-shadow: 0 18px 42px rgba(0, 0, 0, .20) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .header-wrapper,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-area,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .header-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-area,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .header-wrapper,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-area,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card{
  color: var(--theme-dark-header-text) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-page-title-heading,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger small,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] svg,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] i,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-page-title-heading,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger small,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] svg,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] i,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-page-title-heading,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger small,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] svg,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] i{
  color: var(--theme-dark-header-text) !important;
  stroke: currentColor !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut{
  color: var(--theme-dark-header-text) !important;
  background: rgba(var(--theme-dark-header-text-rgb), .10) !important;
  border-color: rgba(var(--theme-dark-header-text-rgb), .16) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut:hover,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut.active,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut.active,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut:hover,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut.active{
  background: rgba(var(--theme-dark-header-text-rgb), .15) !important;
  border-color: rgba(var(--theme-dark-header-text-rgb), .24) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--amber .v2-service-shortcut-icon,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--amber .v2-service-shortcut-icon,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--amber .v2-service-shortcut-icon{
  background: rgba(245, 158, 11, .16) !important;
  border-color: rgba(245, 158, 11, .26) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--amber .v2-service-shortcut-icon,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--amber .v2-service-shortcut-icon i,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--amber .v2-service-shortcut-icon,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--amber .v2-service-shortcut-icon i,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--amber .v2-service-shortcut-icon,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--amber .v2-service-shortcut-icon i{
  color: #fbbf24 !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--green .v2-service-shortcut-icon,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--green .v2-service-shortcut-icon,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--green .v2-service-shortcut-icon{
  background: rgba(22, 165, 107, .18) !important;
  border-color: rgba(22, 165, 107, .28) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--green .v2-service-shortcut-icon,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--green .v2-service-shortcut-icon i,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--green .v2-service-shortcut-icon,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--green .v2-service-shortcut-icon i,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--green .v2-service-shortcut-icon,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--green .v2-service-shortcut-icon i{
  color: #34d399 !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--blue .v2-service-shortcut-icon,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--blue .v2-service-shortcut-icon,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--blue .v2-service-shortcut-icon{
  background: rgba(var(--theme-primary-rgb, 37, 99, 235), .18) !important;
  border-color: rgba(var(--theme-primary-rgb, 37, 99, 235), .28) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--blue .v2-service-shortcut-icon,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--blue .v2-service-shortcut-icon i,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--blue .v2-service-shortcut-icon,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--blue .v2-service-shortcut-icon i,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--blue .v2-service-shortcut-icon,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut--blue .v2-service-shortcut-icon i{
  color: #60a5fa !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger{
  color: var(--theme-dark-header-text) !important;
  background: rgba(255, 255, 255, .12) !important;
  border-color: rgba(255, 255, 255, .20) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle .v2-theme-option,
html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle .v2-theme-option,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle .v2-theme-option,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option{
  color: rgba(var(--theme-dark-header-text-rgb), .78) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--light,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--dark,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option.active,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option.selected,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-avatar{
  color: var(--theme-button-text) !important;
  background: var(--theme-button-bg) !important;
  border-color: var(--theme-button-bg) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown{
  background: var(--theme-dashboard-card) !important;
  border-color: rgba(var(--theme-dashboard-border-rgb), .92) !important;
  color: var(--theme-dashboard-text) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown{
  background: var(--theme-dark-card) !important;
  border-color: rgba(var(--theme-dark-border-rgb), .92) !important;
  color: var(--theme-dark-text) !important;
}

html body,
html body .page-wrapper.compact-wrapper,
html body .page-wrapper.compact-wrapper .page-body-wrapper,
html body .page-wrapper.compact-wrapper .page-body-wrapper .page-body,
html body .page-wrapper.compact-wrapper .page-body-wrapper footer{
  background: var(--theme-dashboard-bg) !important;
  color: var(--theme-dashboard-text) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .container-fluid:not(.app-settings-v2-page){
  color: var(--theme-dashboard-text) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .root-console-head,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-health-card,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-card,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-system-grid,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-system-card,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-panel,
html body .page-wrapper.compact-wrapper .page-body-wrapper .kpi-tile,
html body .page-wrapper.compact-wrapper .page-body-wrapper .panel-modern{
  background: var(--theme-dashboard-card) !important;
  border-color: rgba(var(--theme-dashboard-border-rgb), .95) !important;
  color: var(--theme-dashboard-text) !important;
  box-shadow: 0 16px 34px rgba(var(--theme-primary-rgb), .07) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .root-console-title,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-health-value,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-value,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-system-value,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-title,
html body .page-wrapper.compact-wrapper .page-body-wrapper .kpi-value,
html body .page-wrapper.compact-wrapper .page-body-wrapper .panel-modern .panel-heading strong{
  color: var(--theme-dashboard-text) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .root-console-subtitle,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-health-label,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-label,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-system-label,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-system-detail,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-progress-value,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-table th,
html body .page-wrapper.compact-wrapper .page-body-wrapper .kpi-label,
html body .page-wrapper.compact-wrapper .page-body-wrapper .panel-modern .text-muted{
  color: var(--theme-dashboard-muted) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-table td{
  color: var(--theme-dashboard-text) !important;
  border-top-color: rgba(var(--theme-dashboard-border-rgb), .75) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-head{
  border-bottom-color: rgba(var(--theme-dashboard-border-rgb), .85) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .root-workspace-pill,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-action{
  color: var(--theme-primary) !important;
  background: rgba(var(--theme-primary-rgb), .08) !important;
  border-color: rgba(var(--theme-primary-rgb), .22) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .root-console-emblem,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-health-icon,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-icon,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-system-icon,
html body .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-icon,
html body .page-wrapper.compact-wrapper .page-body-wrapper .kpi-tile .kpi-icon{
  color: var(--theme-primary) !important;
  background: rgba(var(--theme-primary-rgb), .12) !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-console-emblem,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-health-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-attention-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-system-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-activity-icon{
  color: var(--theme-primary, #2563eb) !important;
  background: rgba(var(--theme-primary-rgb, 37, 99, 235), .09) !important;
  border: 1px solid rgba(var(--theme-primary-rgb, 37, 99, 235), .12) !important;
  box-shadow: none !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-health-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-attention-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-system-icon{
  width: 46px !important;
  height: 46px !important;
  min-width: 46px !important;
  min-height: 46px !important;
  flex: 0 0 46px !important;
  border-radius: 12px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-size: 18px !important;
  line-height: 1 !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-console-emblem{
  color: #16a56b !important;
  background: rgba(22, 165, 107, .10) !important;
  border-color: rgba(22, 165, 107, .16) !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-activity-icon{
  color: var(--theme-primary, #2563eb) !important;
  background: rgba(var(--theme-primary-rgb, 37, 99, 235), .09) !important;
  border-color: rgba(var(--theme-primary-rgb, 37, 99, 235), .12) !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-health-card.tone-green .root-health-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-attention-card.tone-green .root-attention-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-attention-card.ok .root-attention-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-system-card.ok .root-system-icon{
  color: #16a56b !important;
  background: rgba(22, 165, 107, .10) !important;
  border-color: rgba(22, 165, 107, .16) !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-health-card.tone-amber .root-health-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-attention-card.tone-orange .root-attention-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-attention-card.tone-yellow .root-attention-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-attention-card.warning .root-attention-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-system-card.warning .root-system-icon{
  color: #f59e0b !important;
  background: rgba(245, 158, 11, .10) !important;
  border-color: rgba(245, 158, 11, .20) !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-health-card.tone-red .root-health-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-attention-card.tone-red .root-attention-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-attention-card.danger .root-attention-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-system-card.danger .root-system-icon{
  color: #e11d48 !important;
  background: rgba(225, 29, 72, .10) !important;
  border-color: rgba(225, 29, 72, .14) !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-health-card.tone-blue .root-health-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-health-card.tone-purple .root-health-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .root-console-shell .root-attention-card.tone-cyan .root-attention-icon{
  color: var(--theme-primary, #2563eb) !important;
  background: rgba(var(--theme-primary-rgb, 37, 99, 235), .09) !important;
  border-color: rgba(var(--theme-primary-rgb, 37, 99, 235), .12) !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile .kpi-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-status-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-overview-icon{
  color: var(--theme-primary, #2563eb) !important;
  background: rgba(var(--theme-primary-rgb, 37, 99, 235), .09) !important;
  border: 1px solid rgba(var(--theme-primary-rgb, 37, 99, 235), .12) !important;
  box-shadow: none !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile .kpi-icon.kpi-icon--metric,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile .kpi-icon.kpi-icon--plain,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile .kpi-icon.kpi-icon--orb,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile .kpi-icon.kpi-icon--ring,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile .kpi-icon.kpi-icon--pill{
  width: 46px !important;
  height: 46px !important;
  min-width: 46px !important;
  min-height: 46px !important;
  flex: 0 0 46px !important;
  padding: 0 !important;
  border-radius: 12px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  align-self: flex-start !important;
  justify-self: start !important;
  font-size: 18px !important;
  line-height: 1 !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile .kpi-icon.blue,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile.blue .kpi-icon{
  color: var(--theme-primary, #2563eb) !important;
  background: rgba(var(--theme-primary-rgb, 37, 99, 235), .09) !important;
  border-color: rgba(var(--theme-primary-rgb, 37, 99, 235), .12) !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile .kpi-icon.green,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile.green .kpi-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-card.tone-green .retailer-action-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-status-card.tone-green .retailer-status-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-overview-stat.tone-activity .retailer-overview-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-overview-stat.tone-performance .retailer-overview-icon{
  color: #16a56b !important;
  background: rgba(22, 165, 107, .10) !important;
  border-color: rgba(22, 165, 107, .16) !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile .kpi-icon.amber,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile.amber .kpi-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-card.tone-amber .retailer-action-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-status-card.tone-amber .retailer-status-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-overview-stat.tone-pending .retailer-overview-icon{
  color: #f59e0b !important;
  background: rgba(245, 158, 11, .10) !important;
  border-color: rgba(245, 158, 11, .20) !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile .kpi-icon.red,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile.red .kpi-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-status-card.tone-red .retailer-status-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-overview-stat.tone-balance .retailer-overview-icon{
  color: #e11d48 !important;
  background: rgba(225, 29, 72, .10) !important;
  border-color: rgba(225, 29, 72, .14) !important;
}

html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile .kpi-icon.purple,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-tile.purple .kpi-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-card.tone-purple .retailer-action-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-card.tone-blue .retailer-action-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-status-card.tone-purple .retailer-status-icon,
html body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-status-card.tone-blue .retailer-status-icon{
  color: var(--theme-primary, #2563eb) !important;
  background: rgba(var(--theme-primary-rgb, 37, 99, 235), .09) !important;
  border-color: rgba(var(--theme-primary-rgb, 37, 99, 235), .12) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .root-progress-fill{
  background: linear-gradient(90deg, var(--theme-primary), var(--theme-button-bg)) !important;
}

html body.dark-only,
html body.dark-only .page-wrapper.compact-wrapper,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .page-body,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper footer,
html.dark body,
html.dark body .page-wrapper.compact-wrapper,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .page-body,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper footer,
[data-bs-theme="dark"] body,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .page-body,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper footer{
  background: var(--theme-dark-surface) !important;
  color: var(--theme-dark-text) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-console-head,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-health-card,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-card,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-system-grid,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-system-card,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-panel,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .kpi-tile,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .panel-modern,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-console-head,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-health-card,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-card,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-system-grid,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-system-card,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-panel,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .kpi-tile,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .panel-modern,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-console-head,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-health-card,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-card,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-system-grid,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-system-card,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-panel,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .kpi-tile,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .panel-modern{
  background: var(--theme-dark-card) !important;
  border-color: rgba(var(--theme-dark-border-rgb), .95) !important;
  color: var(--theme-dark-text) !important;
  box-shadow: 0 16px 34px rgba(0, 0, 0, .20) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-console-title,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-health-value,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-value,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-system-value,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-title,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-console-title,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-health-value,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-value,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-system-value,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-title,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-console-title,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-health-value,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-value,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-system-value,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-title{
  color: var(--theme-dark-text) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-console-subtitle,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-health-label,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-label,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-system-label,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-system-detail,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-progress-value,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-table th,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-console-subtitle,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-health-label,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-label,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-system-label,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-system-detail,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-progress-value,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-table th,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-console-subtitle,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-health-label,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-label,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-system-label,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-system-detail,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-progress-value,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-table th{
  color: var(--theme-dark-muted) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-table td,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-table td,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-table td{
  color: var(--theme-dark-text) !important;
  border-top-color: rgba(var(--theme-dark-border-rgb), .82) !important;
}

html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-welcome-copy h2,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-section-head h4,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .panel-modern .panel-heading strong,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-heading-title,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-value,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-label,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .table-modern tbody td,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-welcome-copy h2,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-section-head h4,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .panel-modern .panel-heading strong,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-heading-title,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-value,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-label,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .table-modern tbody td,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-welcome-copy h2,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-section-head h4,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .panel-modern .panel-heading strong,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-heading-title,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-value,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-label,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .table-modern tbody td{
  color: var(--theme-dark-text) !important;
}

html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-welcome-copy p,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-feature-list li,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-label,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-subtitle,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-value,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-description,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-orders-meta,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-chart-subtitle,
html body.dark-only.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .table-modern thead th,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-welcome-copy p,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-feature-list li,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-label,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-subtitle,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-value,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-description,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-orders-meta,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-chart-subtitle,
html.dark body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .table-modern thead th,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-welcome-copy p,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-feature-list li,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-label,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-subtitle,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-value,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-description,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-orders-meta,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-chart-subtitle,
[data-bs-theme="dark"] body.dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .table-modern thead th{
  color: var(--theme-dark-muted) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-head,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-head,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-head{
  border-bottom-color: rgba(var(--theme-dark-border-rgb), .9) !important;
}

html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header],
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header].close_icon{
  background: var(--theme-header-bg) !important;
  background-image: linear-gradient(135deg, rgba(var(--theme-header-rgb), .98), rgba(var(--theme-header-rgb), .90)) !important;
  border-bottom-color: rgba(var(--theme-header-rgb), .35) !important;
  color: var(--theme-header-text) !important;
}

html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-page-title-heading,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger small,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] svg,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] i{
  color: var(--theme-header-text) !important;
  stroke: currentColor !important;
}

html body:not(.dark-only),
html body:not(.dark-only) .page-wrapper.compact-wrapper,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .page-body,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper footer{
  background: var(--theme-dashboard-bg) !important;
  color: var(--theme-dashboard-text) !important;
}

html body:not(.dark-only) .app-settings-v2-page{
  --settings-v2-ink: var(--theme-dashboard-text) !important;
  --settings-v2-muted: var(--theme-dashboard-muted) !important;
  --settings-v2-line: rgba(var(--theme-dashboard-border-rgb), .78) !important;
  --settings-v2-soft: rgba(var(--theme-dashboard-bg-rgb), .70) !important;
  --settings-v2-panel: var(--theme-dashboard-card) !important;
  --settings-v2-field: var(--theme-dashboard-card) !important;
}

html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-console-head,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-health-card,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-card,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-system-grid,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-system-card,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-panel,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .kpi-tile,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .panel-modern{
  background: var(--theme-dashboard-card) !important;
  border-color: rgba(var(--theme-dashboard-border-rgb), .95) !important;
  color: var(--theme-dashboard-text) !important;
}

html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-console-title,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-health-value,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-value,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-system-value,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-title{
  color: var(--theme-dashboard-text) !important;
}

html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-console-subtitle,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-health-label,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-attention-label,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-system-label,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-system-detail,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-progress-value,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-body-wrapper .root-activity-table th{
  color: var(--theme-dashboard-muted) !important;
}

html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-welcome-copy h2,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-section-head h4,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .panel-modern .panel-heading strong,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-heading-title,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-value,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-label,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .table-modern tbody td{
  color: var(--theme-dashboard-text) !important;
}

html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-welcome-copy p,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-feature-list li,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-label,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .kpi-subtitle,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-value,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-action-description,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-orders-meta,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .retailer-chart-subtitle,
html body:not(.dark-only).dashboard-v2-page .page-wrapper.compact-wrapper .page-body-wrapper .retailer-dashboard .table-modern thead th{
  color: var(--theme-dashboard-muted) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar],
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] > div,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper{
  background: var(--theme-sidebar-bg) !important;
  color: var(--theme-sidebar-text) !important;
  border-color: rgba(var(--theme-sidebar-active-rgb), .16) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-label,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-name,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-group{
  color: var(--theme-sidebar-text) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .dynamic-icon,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .dynamic-icon::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame{
  color: var(--theme-primary) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar],
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] > div,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar],
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] > div,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar],
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] > div,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper{
  background: var(--theme-dark-surface) !important;
  color: var(--theme-dark-text) !important;
  border-color: rgba(var(--theme-dark-border-rgb), .92) !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-label,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-name,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-group,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-label,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-name,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-group,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-label,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-name,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-group{
  color: var(--theme-dark-text) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list:hover > a.sidebar-link,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li:hover > a,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li.active > a,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.active{
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--theme-sidebar-active), var(--theme-primary)) !important;
  border-color: rgba(255, 255, 255, .16) !important;
  box-shadow: inset 3px 0 0 rgba(255, 255, 255, .22) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list:hover > a.sidebar-link *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li:hover > a *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li.active > a *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.active *{
  color: #ffffff !important;
}

/* Keep the V2 sidebar from falling back to hardcoded blue accents. */
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links .sidebar-list:hover > a.sidebar-link,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links .sidebar-list > a.sidebar-link:focus,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links .sidebar-list.active > a.sidebar-link,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links .sidebar-list > a.sidebar-link.active{
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--theme-sidebar-active), var(--theme-primary)) !important;
  border-color: rgba(255, 255, 255, .16) !important;
  box-shadow: inset 3px 0 0 rgba(255, 255, 255, .22) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame{
  color: var(--theme-primary) !important;
  background: rgba(var(--theme-primary-rgb), .10) !important;
  border-color: rgba(var(--theme-primary-rgb), .18) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame i,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame i::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame .dynamic-icon,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame .dynamic-icon::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame i,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame i::before{
  color: var(--theme-primary) !important;
  -webkit-text-fill-color: var(--theme-primary) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list:hover > a.sidebar-link .v2-sidebar-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus .v2-sidebar-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li:hover > a .v2-sidebar-child-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:focus .v2-sidebar-child-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li.active > a .v2-sidebar-child-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.active .v2-sidebar-child-icon-frame{
  color: #ffffff !important;
  background: var(--theme-sidebar-active) !important;
  border-color: rgba(255, 255, 255, .16) !important;
  box-shadow: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list:hover > a.sidebar-link .v2-sidebar-icon-frame *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus .v2-sidebar-icon-frame *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li:hover > a .v2-sidebar-child-icon-frame *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:focus .v2-sidebar-child-icon-frame *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li.active > a .v2-sidebar-child-icon-frame *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.active .v2-sidebar-child-icon-frame *{
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge{
  color: var(--theme-primary) !important;
  background: rgba(var(--theme-primary-rgb), .10) !important;
  border-color: rgba(var(--theme-primary-rgb), .18) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list:hover > a.sidebar-link .v2-sidebar-child-count,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-child-count,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-child-count,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list:hover > a.sidebar-link .v2-sidebar-menu-badge,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-menu-badge,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-menu-badge{
  color: #ffffff !important;
  background: rgba(255, 255, 255, .18) !important;
  border-color: rgba(255, 255, 255, .28) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn{
  color: var(--theme-primary) !important;
  border-color: rgba(var(--theme-primary-rgb), .18) !important;
  box-shadow: inset 0 0 0 1px rgba(var(--theme-primary-rgb), .18), 0 10px 22px rgba(var(--theme-primary-rgb), .10) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn:hover,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn:focus-visible{
  color: #ffffff !important;
  background: var(--theme-primary) !important;
  box-shadow: 0 12px 26px rgba(var(--theme-primary-rgb), .24) !important;
}

html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header],
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header],
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header].close_icon,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header].close_icon{
  --v2-header-primary: var(--theme-header-bg) !important;
  --v2-header-primary-dark: var(--theme-header-bg) !important;
  --v2-header-ink: var(--theme-header-text) !important;
  --v2-header-muted: rgba(var(--theme-header-text-rgb), .78) !important;
  background: var(--theme-header-bg) !important;
  background-image: linear-gradient(135deg, rgba(var(--theme-header-rgb), .98), rgba(var(--theme-header-rgb), .90)) !important;
  color: var(--theme-header-text) !important;
  border-bottom-color: rgba(var(--theme-header-rgb), .35) !important;
}

html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] > .header-wrapper.v2-header-shell,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header] > .header-wrapper.v2-header-shell,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card{
  background: transparent !important;
  background-image: none !important;
  color: var(--theme-header-text) !important;
  border-color: transparent !important;
  box-shadow: none !important;
}

html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .toggle-sidebar,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header] .toggle-sidebar,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-page-title-heading,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-page-title-heading,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] svg,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header] svg,
html body:not(.dark-only) .page-wrapper.compact-wrapper .page-header[data-v2-header] i,
html body.light .page-wrapper.compact-wrapper .page-header[data-v2-header] i{
  color: var(--theme-header-text) !important;
  stroke: currentColor !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li.active > a.li-a,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.li-a.active{
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--theme-sidebar-active), var(--theme-primary)) !important;
  border-color: rgba(var(--theme-sidebar-active-rgb), .30) !important;
  box-shadow: 0 8px 18px rgba(var(--theme-sidebar-active-rgb), .24) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li.active > a.li-a *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.li-a.active *{
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active::before{
  background: #ffffff !important;
  box-shadow: 0 0 0 4px rgba(255, 255, 255, .16) !important;
}
@endif
</style>
