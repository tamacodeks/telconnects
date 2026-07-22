@php
    $laravelVersion = intval(app()->version());
@endphp

@if($laravelVersion >= 9)
    {{-- Laravel 9+ uses Vite --}}
    @vite(['public/assets/scss/app.scss'])
@else
    {{-- Older Laravel versions load CSS assets via traditional link tags --}}
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/icofont.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/themify.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/flag-icon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/slick-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/feather-icon.css') }}">
    

    <link rel="stylesheet" href="{{ asset('assets/css/style.css?v=2') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">
@endif
<style>
:root{
  --legacy-brand-blue: #1764a8;
  --legacy-sidebar-bg: #ffffff;
  --legacy-sidebar-text: #000000;
  --legacy-sidebar-active-text: #ffffff;
}

/* ===== V2 header/sidebar themed to match legacy dashboard ===== */
.page-wrapper.compact-wrapper .page-header {
  background: var(--legacy-brand-blue) !important;
  border-bottom: 0 !important;
  box-shadow: none !important;
  min-height: 58px !important;
}
.page-wrapper.compact-wrapper .page-header .header-wrapper {
  background: var(--legacy-brand-blue) !important;
  min-height: 58px !important;
  padding-top: 4px !important;
  padding-bottom: 4px !important;
  align-items: center !important;
}
.page-wrapper.compact-wrapper .page-header .nav-menus > li > span,
.page-wrapper.compact-wrapper .page-header .nav-menus > li > a,
.page-wrapper.compact-wrapper .page-header .nav-menus > li > div,
.page-wrapper.compact-wrapper .page-header .nav-menus .profile-media .media-body > span,
.page-wrapper.compact-wrapper .page-header .nav-menus .profile-media .media-body > p,
.page-wrapper.compact-wrapper .page-header .translate_wrapper .lang .lang-txt {
  color: #ffffff !important;
}
.page-wrapper.compact-wrapper .page-header .nav-menus svg {
  color: #ffffff !important;
  fill: currentColor;
  stroke: currentColor;
}
.page-wrapper.compact-wrapper .page-header .nav-menus > li {
  height: auto !important;
  display: flex;
  align-items: center;
}
.page-wrapper.compact-wrapper .page-header .nav-menus > li > a,
.page-wrapper.compact-wrapper .page-header .nav-menus > li > span,
.page-wrapper.compact-wrapper .page-header .nav-menus > li > div {
  min-height: 34px !important;
  display: inline-flex;
  align-items: center;
}
.page-wrapper.compact-wrapper .page-header .header-logo-wrapper .logo-wrapper {
  padding: 0 !important;
}
.page-wrapper.compact-wrapper .page-header .header-logo-wrapper .logo-wrapper img {
  max-height: 34px;
  width: auto;
  object-fit: contain;
}
.page-wrapper.compact-wrapper .page-header .nav-right.right-header {
  padding-top: 0 !important;
  padding-bottom: 0 !important;
}
.page-wrapper.compact-wrapper .page-header .search-full {
  max-height: 50px;
  align-items: center;
  display: flex;
  padding: 0 8px 0 0;
  background: transparent !important;
}
.page-wrapper.compact-wrapper .page-header .search-full .form-group {
  margin: 0;
  width: 100%;
}
.page-wrapper.compact-wrapper .page-header .search-full .Typeahead,
.page-wrapper.compact-wrapper .page-header .search-full .u-posRelative {
  width: 100%;
}
.page-wrapper.compact-wrapper .page-header .search-full .u-posRelative {
  display: flex;
  align-items: center;
  min-height: 36px;
  padding: 0 12px;
  border-radius: 10px;
  background: rgba(255,255,255,.14) !important;
  border: 1px solid rgba(255,255,255,.20);
}
.page-wrapper.compact-wrapper .page-header .search-full .Typeahead-input {
  flex: 1 1 auto;
  min-width: 0;
  padding-top: 0 !important;
  padding-bottom: 0 !important;
  padding-left: 0 !important;
  padding-right: 0 !important;
  margin: 0 !important;
  line-height: 1.2 !important;
  border: 0 !important;
  background: transparent !important;
  box-shadow: none !important;
  align-self: center;
}
.page-wrapper.compact-wrapper .page-header .search-full .Typeahead-spinner {
  width: 16px;
  height: 16px;
  margin-left: 8px;
  margin-right: 8px;
  color: rgba(255,255,255,.9) !important;
  border-width: 2px;
  flex: 0 0 auto;
  align-self: center;
}
.page-wrapper.compact-wrapper .page-header .header-search,
.page-wrapper.compact-wrapper .page-header .mode {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: inline-flex !important;
  align-items: center;
  justify-content: center;
  transition: background-color .2s ease, transform .2s ease;
}
.page-wrapper.compact-wrapper .page-header .header-search:hover,
.page-wrapper.compact-wrapper .page-header .mode:hover {
  background: rgba(255,255,255,.18) !important;
  transform: translateY(-1px);
}
.page-wrapper.compact-wrapper .page-header .header-search svg,
.page-wrapper.compact-wrapper .page-header .mode svg,
.page-wrapper.compact-wrapper .page-header .header-search svg use,
.page-wrapper.compact-wrapper .page-header .mode svg use {
  color: #ffffff !important;
  fill: #ffffff !important;
  stroke: #ffffff !important;
}
.page-wrapper.compact-wrapper .page-header .close-search {
  color: #ffffff !important;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  cursor: pointer;
  opacity: .9;
  flex: 0 0 auto;
}
.page-wrapper.compact-wrapper .page-header .close-search svg {
  width: 15px;
  height: 15px;
  stroke: #ffffff !important;
}
.page-wrapper.compact-wrapper .page-header .search-full input {
  color: #ffffff !important;
}
.page-wrapper.compact-wrapper .page-header .search-full input::placeholder {
  color: rgba(255,255,255,.8) !important;
}
.page-wrapper.compact-wrapper .page-header .search-full .Typeahead-menu {
  margin-top: 6px;
}
.page-wrapper.compact-wrapper .page-header .profile-dropdown,
.page-wrapper.compact-wrapper .page-header .more_lang {
  background: #ffffff !important;
  border: 1px solid rgba(23,100,168,.18) !important;
}
.page-wrapper.compact-wrapper .page-header .profile-dropdown a,
.page-wrapper.compact-wrapper .page-header .more_lang .lang {
  color: #333333 !important;
}
.page-wrapper.compact-wrapper .page-header .profile-dropdown a:hover,
.page-wrapper.compact-wrapper .page-header .more_lang .lang:hover {
  background: #f5f5f5 !important;
}
.page-wrapper.compact-wrapper .page-header,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper,
.page-wrapper.compact-wrapper .page-body-wrapper .page-body,
.page-wrapper.compact-wrapper .page-body-wrapper footer {
  transition: all .25s ease;
}
.page-wrapper.compact-wrapper .page-body-wrapper .page-body {
  margin-top: 58px !important;
  min-height: calc(100vh - 58px) !important;
  padding: 0 15px 0 15px !important;
}
.page-wrapper.compact-wrapper .page-body-wrapper .page-title {
  margin: 0 -15px 14px !important;
  padding: 10px 16px !important;
  min-height: 48px;
  display: flex;
  align-items: center;
  border-bottom: 1px solid rgba(23,100,168,.12);
}
.page-wrapper.compact-wrapper .page-body-wrapper .page-title .row {
  width: 100%;
  align-items: center;
}
.page-wrapper.compact-wrapper .page-body-wrapper .page-title .row h3 {
  font-size: 20px;
  line-height: 1.2;
  margin: 0;
}
.page-wrapper.compact-wrapper .page-body-wrapper .page-title .breadcrumb {
  justify-content: flex-end;
  margin: 0;
  min-height: 28px;
}
.page-wrapper.compact-wrapper .page-body-wrapper .page-title .breadcrumb .breadcrumb-item {
  display: inline-flex;
  align-items: center;
}
.page-wrapper.compact-wrapper .page-header .header-logo-wrapper .toggle-sidebar {
  position: static !important;
  top: auto !important;
  right: auto !important;
  display: grid;
  place-items: center;
  width: 34px;
  height: 34px;
  margin: 0 10px;
  border-radius: 8px;
  background: rgba(255,255,255,.14);
  border: 1px solid rgba(255,255,255,.22);
  cursor: pointer;
  transition: transform .2s ease, background-color .2s ease, box-shadow .2s ease;
  box-shadow: 0 4px 12px rgba(0,0,0,.12);
}
.page-wrapper.compact-wrapper .page-header .header-logo-wrapper .toggle-sidebar::before {
  content: none !important;
}
.page-wrapper.compact-wrapper .page-header .header-logo-wrapper {
  display: inline-flex;
  align-items: center;
  min-height: 58px;
}
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper .logo-wrapper .toggle-sidebar {
  position: static !important;
  top: auto !important;
  right: auto !important;
  margin-left: auto;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper .logo-wrapper .toggle-sidebar::before {
  content: none !important;
}
.page-wrapper.compact-wrapper .page-header .header-logo-wrapper .toggle-sidebar:hover {
  transform: translateY(-1px);
  background: rgba(255,255,255,.22);
  box-shadow: 0 6px 16px rgba(0,0,0,.18);
}
.page-wrapper.compact-wrapper .page-header .header-logo-wrapper .toggle-sidebar .sidebar-toggle {
  color: #fff !important;
  transition: transform .25s ease;
}
.page-wrapper.compact-wrapper .page-header.close_icon .header-logo-wrapper .toggle-sidebar .sidebar-toggle {
  transform: rotate(180deg);
}

.sidebar-wrapper[sidebar-layout] {
  background: var(--legacy-sidebar-bg) !important;
  border-right: 1px solid #e9ecef !important;
  box-shadow: 4px 0 18px rgba(17,24,39,.04);
}
.sidebar-wrapper[sidebar-layout] .logo-wrapper,
.sidebar-wrapper[sidebar-layout] .logo-icon-wrapper {
  background: var(--legacy-sidebar-bg) !important;
  border-bottom: 1px solid #eef1f4 !important;
  height: 58px !important;
  min-height: 58px !important;
  padding: 0 !important;
  display: flex;
  align-items: center;
}
.sidebar-wrapper[sidebar-layout] .logo-wrapper a,
.sidebar-wrapper[sidebar-layout] .logo-icon-wrapper a{
  display:flex;
  align-items:center;
  justify-content:center;
  height: 58px;
  min-height: 58px;
  width: 100%;
}
.sidebar-wrapper[sidebar-layout] .logo-wrapper img{
  height: 36px;
  max-height: 36px;
  width: auto;
  object-fit: contain;
}
.sidebar-wrapper[sidebar-layout] .logo-icon-wrapper img{
  height: 36px;
  max-height: 36px;
  width: auto;
  object-fit: contain;
}
.sidebar-wrapper[sidebar-layout] .sidebar-main {
  background: var(--legacy-sidebar-bg) !important;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links {
  background: var(--legacy-sidebar-bg) !important;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a {
  color: var(--legacy-sidebar-text) !important;
  background: transparent !important;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .according-menu i {
  color: #6c757d !important;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .dynamic-icon,
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .dynamic-icon::before {
  color: var(--legacy-sidebar-text) !important;
}
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list.active > a,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list .sidebar-link.active {
  background: var(--legacy-brand-blue) !important;
  color: var(--legacy-sidebar-active-text) !important;
}
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a > span,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list.active > a > span,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list > a.active > span {
  color: var(--legacy-sidebar-active-text) !important;
}
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a .dynamic-icon,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list.active > a .dynamic-icon,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a .dynamic-icon::before,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list.active > a .dynamic-icon::before,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a .according-menu i,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list.active > a .according-menu i {
  color: var(--legacy-sidebar-active-text) !important;
}
.sidebar-wrapper[sidebar-layout] .sidebar-submenu > li > a,
.sidebar-wrapper[sidebar-layout] .sidebar-submenu > li > a.li-a {
  color: var(--legacy-sidebar-text) !important;
}
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-submenu > li:hover > a,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-submenu > li.active > a,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-submenu > li > a.active {
  background: var(--legacy-brand-blue) !important;
  color: var(--legacy-sidebar-active-text) !important;
}
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-submenu > li:hover > a *,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-submenu > li.active > a *,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-submenu > li > a.active * {
  color: var(--legacy-sidebar-active-text) !important;
}
.sidebar-wrapper[sidebar-layout] .sidebar-main-title h6,
.sidebar-wrapper[sidebar-layout] .sidebar-main-title span,
.sidebar-wrapper[sidebar-layout] .nav-items h5,
.sidebar-wrapper[sidebar-layout] .nav-items h3 {
  color: #333 !important;
}
.sidebar-wrapper[sidebar-layout] .nav-items .balance-display,
.sidebar-wrapper[sidebar-layout] #tamaBalance {
  color: var(--legacy-brand-blue) !important;
  background: rgba(23,100,168,.10);
  border: 1px solid rgba(23,100,168,.20);
  border-radius: 10px;
  padding: 8px 10px;
  margin: 6px 12px 10px;
  font-size: 1.05rem;
  line-height: 1.2;
  display: inline-block;
  min-width: 140px;
}
.sidebar-wrapper[sidebar-layout] .nav-items .balance-label {
  color: #5b6777 !important;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .3px;
  margin: 0 0 6px;
  text-transform: uppercase;
}

/* ===== Sidebar modern refresh ===== */
:root {
  --v2-sidebar-surface: #ffffff;
  --v2-sidebar-border: #e8edf3;
  --v2-sidebar-hover: #f3f8ff;
  --v2-sidebar-active-start: #1f6db1;
  --v2-sidebar-active-end: #15558f;
  --v2-sidebar-muted: #6b7685;
}
.sidebar-wrapper[sidebar-layout] {
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%) !important;
  border-right: 1px solid var(--v2-sidebar-border) !important;
  box-shadow: 8px 0 24px rgba(15, 23, 42, 0.06);
}
.sidebar-wrapper[sidebar-layout] .sidebar-main {
  padding: 10px 10px 14px;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links {
  padding: 4px 0;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list {
  margin-bottom: 6px;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a {
  min-height: 40px;
  border-radius: 12px;
  padding: 8px 10px !important;
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
  border: 1px solid transparent;
  transition: background-color .2s ease, border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .v2-sidebar-icon-frame {
  width: 36px;
  height: 36px;
  min-width: 36px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .dynamic-icon,
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .dynamic-icon::before {
  width: 18px;
  min-width: 18px;
  text-align: center;
  font-size: 15px;
  color: var(--legacy-brand-blue) !important;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .v2-sidebar-label {
  flex: 1 1 auto;
  min-width: 0;
  line-height: 1.18;
  white-space: normal;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .v2-sidebar-menu-badge,
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .v2-sidebar-child-count {
  flex: 0 0 auto;
  margin-left: auto;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a > span {
  font-weight: 600;
  font-size: 13px;
  letter-spacing: .1px;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .according-menu i {
  color: var(--v2-sidebar-muted) !important;
  font-size: 13px;
}
.sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .according-menu {
  margin-left: auto;
  width: 18px;
  min-width: 18px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a {
  background: linear-gradient(135deg, var(--v2-sidebar-active-start), var(--v2-sidebar-active-end)) !important;
  border-color: rgba(255, 255, 255, .24) !important;
  box-shadow: 0 8px 16px rgba(21, 85, 143, .20);
  transform: translateX(2px);
}
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a > span,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a .dynamic-icon,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a .dynamic-icon::before,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a .according-menu i {
  color: #fff !important;
}
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list.active > a,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list .sidebar-link.active {
  background: linear-gradient(135deg, var(--v2-sidebar-active-start), var(--v2-sidebar-active-end)) !important;
  box-shadow: 0 8px 16px rgba(21, 85, 143, .26);
  border-color: rgba(255, 255, 255, .24) !important;
}
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list.active > a .dynamic-icon,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list.active > a .dynamic-icon::before,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list.active > a .according-menu i,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list.active > a > span,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list .sidebar-link.active .dynamic-icon,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list .sidebar-link.active .dynamic-icon::before,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list .sidebar-link.active .according-menu i,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list .sidebar-link.active > span {
  color: #fff !important;
}
.sidebar-wrapper[sidebar-layout] .sidebar-submenu {
  margin: 6px 0 0;
  padding: 6px;
  border-radius: 12px;
  border: 1px solid #dfebf7;
  background: #f7fbff !important;
}
.sidebar-wrapper[sidebar-layout] .sidebar-submenu > li > a,
.sidebar-wrapper[sidebar-layout] .sidebar-submenu > li > a.li-a {
  border-radius: 8px;
  min-height: 36px;
  display: flex;
  align-items: center;
  padding: 8px 10px !important;
  font-size: 12.5px;
  font-weight: 600;
}
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-submenu > li:hover > a,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-submenu > li.active > a,
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-submenu > li > a.active {
  background: linear-gradient(135deg, #2b7fc8, #1764a8) !important;
}
.sidebar-wrapper[sidebar-layout] .sidebar-main-title {
  background: linear-gradient(180deg, #ffffff, #f3f8ff);
  border: 1px solid #e0ebf7;
  border-radius: 14px;
  padding: 12px 10px !important;
  margin-bottom: 12px;
}
.sidebar-wrapper[sidebar-layout] .sidebar-main-title .user-avatar {
  width: 54px;
  height: 54px;
  object-fit: cover;
  border: 2px solid rgba(23,100,168,.22);
}
.sidebar-wrapper[sidebar-layout] .sidebar-main-title .user-name {
  color: #1f2937 !important;
  font-size: 14px;
  margin-bottom: 2px;
  line-height: 1.2;
}
.sidebar-wrapper[sidebar-layout] .sidebar-main-title .user-group {
  color: #64748b !important;
  font-weight: 600;
  letter-spacing: .2px;
}
.sidebar-wrapper[sidebar-layout] .sidebar-main-title:hover {
  border-color: #cfe1f5;
  box-shadow: 0 8px 16px rgba(21,85,143,.10);
}
.sidebar-wrapper[sidebar-layout] .sidebar-main-title:hover .user-name {
  color: #0f172a !important;
}
.sidebar-wrapper[sidebar-layout] .sidebar-main-title:hover .user-group {
  color: #334155 !important;
}
.sidebar-wrapper[sidebar-layout] .nav-items {
  margin-bottom: 12px;
}
.page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon .sidebar-links .sidebar-list > a {
  justify-content: center;
  padding-left: 10px !important;
  padding-right: 10px !important;
}

body.dark-only .sidebar-wrapper[sidebar-layout],
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout],
html.dark .sidebar-wrapper[sidebar-layout] {
  background: linear-gradient(180deg, #1d2430 0%, #181e29 100%) !important;
  border-right-color: #2d3748 !important;
  box-shadow: 8px 0 24px rgba(0, 0, 0, 0.35);
}
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-main-title,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-main-title,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-main-title {
  background: linear-gradient(180deg, #252e3c, #202937);
  border-color: #334155;
}
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-main-title .user-name,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-main-title .user-name,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-main-title .user-name {
  color: #f1f5f9 !important;
}
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-main-title .user-group,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-main-title .user-group,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-main-title .user-group {
  color: #93a4ba !important;
}
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-main-title .user-avatar,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-main-title .user-avatar,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-main-title .user-avatar {
  border-color: rgba(142,197,255,.34);
}
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-main-title:hover,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-main-title:hover,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-main-title:hover {
  background: linear-gradient(180deg, #2a3444, #233042);
  border-color: #466083;
  box-shadow: 0 10px 18px rgba(0,0,0,.35);
}
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-main-title:hover .user-name,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-main-title:hover .user-name,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-main-title:hover .user-name {
  color: #ffffff !important;
}
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-main-title:hover .user-group,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-main-title:hover .user-group,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-main-title:hover .user-group {
  color: #c7d6ea !important;
}
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a {
  border-color: transparent !important;
}
body.dark-only .sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a,
html.dark .sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) .sidebar-links .sidebar-list:hover > a {
  background: linear-gradient(135deg, var(--v2-sidebar-active-start), var(--v2-sidebar-active-end)) !important;
  border-color: rgba(255, 255, 255, .22) !important;
  box-shadow: 0 8px 16px rgba(21, 85, 143, .30);
}
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-submenu,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-submenu,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-submenu {
  background: #212a37 !important;
  border-color: #32445c;
}
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .dynamic-icon,
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .dynamic-icon::before,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .dynamic-icon,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .dynamic-icon::before,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .dynamic-icon,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .dynamic-icon::before {
  color: #8ec5ff !important;
}
.page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon {
  box-shadow: 2px 0 14px rgba(17,24,39,.08);
}
.page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon:hover {
  box-shadow: 6px 0 22px rgba(17,24,39,.12);
}
.page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon .logo-wrapper {
  border-bottom-color: rgba(23,100,168,.12) !important;
  height: 58px !important;
  min-height: 58px !important;
}
.page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon .logo-icon-wrapper {
  border-bottom-color: rgba(23,100,168,.12) !important;
  height: 58px !important;
  min-height: 58px !important;
}
.page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon .logo-icon-wrapper img {
  transition: none;
}
.page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon:hover .logo-icon-wrapper img {
  transform: none;
}

/* ===== Desktop collapsed sidebar UX ===== */
@media (min-width: 992px) {
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper {
    transition: width .28s ease, box-shadow .28s ease !important;
    overflow: hidden;
  }
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper .logo-wrapper,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper .logo-icon-wrapper,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper .sidebar-links .sidebar-list > a > span,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper .sidebar-links .sidebar-list > a .according-menu,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper .sidebar-main-title,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper .nav-items {
    transition: opacity .2s ease, transform .2s ease, max-height .2s ease;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon {
    width: 88px !important;
  }
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon .logo-wrapper {
    opacity: 0;
    transform: scale(.92);
    max-height: 0;
    pointer-events: none;
  }
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon .logo-icon-wrapper {
    opacity: 1;
    transform: scale(1);
    max-height: 58px;
    height: 58px;
    pointer-events: auto;
  }
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon .sidebar-links .sidebar-list > a > span,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon .sidebar-links .sidebar-list > a .according-menu,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon .sidebar-main-title,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon .nav-items {
    opacity: 0;
    transform: translateX(-10px);
    pointer-events: none;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon:hover {
    width: 280px !important;
  }
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon:hover .logo-wrapper {
    opacity: 1;
    transform: scale(1);
    max-height: 58px;
    height: 58px;
    pointer-events: auto;
  }
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon:hover .logo-wrapper img {
    height: 42px;
    max-height: 42px;
  }
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon:hover .logo-icon-wrapper {
    opacity: 0;
    transform: scale(.9);
    max-height: 0;
    pointer-events: none;
  }
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon:hover .sidebar-links .sidebar-list > a > span,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon:hover .sidebar-links .sidebar-list > a .according-menu,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon:hover .sidebar-main-title,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon:hover .nav-items {
    opacity: 1;
    transform: translateX(0);
    pointer-events: auto;
  }
}

/* ===== Dark mode: keep legacy blue accents, restore dark surfaces ===== */
body.dark-only .page-wrapper.compact-wrapper .page-header,
body.dark-only .page-wrapper.compact-wrapper .page-header .header-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .header-wrapper,
html.dark .page-wrapper.compact-wrapper .page-header,
html.dark .page-wrapper.compact-wrapper .page-header .header-wrapper {
  background: linear-gradient(180deg, #1b2330 0%, #161d28 100%) !important;
  border-bottom: 1px solid #2b3648 !important;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.28);
}
body.dark-only .page-wrapper.compact-wrapper .page-header .nav-menus > li > span,
body.dark-only .page-wrapper.compact-wrapper .page-header .nav-menus > li > a,
body.dark-only .page-wrapper.compact-wrapper .page-header .nav-menus > li > div,
body.dark-only .page-wrapper.compact-wrapper .page-header .translate_wrapper .lang .lang-txt,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .nav-menus > li > span,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .nav-menus > li > a,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .nav-menus > li > div,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .translate_wrapper .lang .lang-txt,
html.dark .page-wrapper.compact-wrapper .page-header .nav-menus > li > span,
html.dark .page-wrapper.compact-wrapper .page-header .nav-menus > li > a,
html.dark .page-wrapper.compact-wrapper .page-header .nav-menus > li > div,
html.dark .page-wrapper.compact-wrapper .page-header .translate_wrapper .lang .lang-txt {
  color: #e5e7eb !important;
}
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .page-title,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .page-title,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .page-title {
  background: #1f232b !important;
  border-bottom-color: #2f3642 !important;
}

body.dark-only .sidebar-wrapper[sidebar-layout],
body.dark-only .sidebar-wrapper[sidebar-layout] .logo-wrapper,
body.dark-only .sidebar-wrapper[sidebar-layout] .logo-icon-wrapper,
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-main,
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-links,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout],
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .logo-wrapper,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .logo-icon-wrapper,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-main,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-links,
html.dark .sidebar-wrapper[sidebar-layout],
html.dark .sidebar-wrapper[sidebar-layout] .logo-wrapper,
html.dark .sidebar-wrapper[sidebar-layout] .logo-icon-wrapper,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-main,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-links {
  background: #1f232b !important;
  border-color: #2f3642 !important;
}

body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a,
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-submenu > li > a,
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-submenu > li > a.li-a,
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-main-title h6,
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-main-title span,
body.dark-only .sidebar-wrapper[sidebar-layout] .nav-items h5,
body.dark-only .sidebar-wrapper[sidebar-layout] .nav-items h3,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-submenu > li > a,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-submenu > li > a.li-a,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-main-title h6,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-main-title span,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .nav-items h5,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .nav-items h3,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-submenu > li > a,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-submenu > li > a.li-a,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-main-title h6,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-main-title span,
html.dark .sidebar-wrapper[sidebar-layout] .nav-items h5,
html.dark .sidebar-wrapper[sidebar-layout] .nav-items h3 {
  color: #e5e7eb !important;
}
body.dark-only .sidebar-wrapper[sidebar-layout] .nav-items .balance-display,
body.dark-only .sidebar-wrapper[sidebar-layout] #tamaBalance,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .nav-items .balance-display,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] #tamaBalance,
html.dark .sidebar-wrapper[sidebar-layout] .nav-items .balance-display,
html.dark .sidebar-wrapper[sidebar-layout] #tamaBalance {
  color: #8ec5ff !important;
  background: rgba(142,197,255,.14);
  border-color: rgba(142,197,255,.28);
}
body.dark-only .sidebar-wrapper[sidebar-layout] .nav-items .balance-label,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .nav-items .balance-label,
html.dark .sidebar-wrapper[sidebar-layout] .nav-items .balance-label {
  color: #9db0c8 !important;
}

body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .according-menu i,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .according-menu i,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-links .sidebar-list > a .according-menu i {
  color: #cbd5e1 !important;
}
body.dark-only .sidebar-wrapper[sidebar-layout] .logo-wrapper img,
body.dark-only .sidebar-wrapper[sidebar-layout] .logo-icon-wrapper img,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .logo-wrapper img,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .logo-icon-wrapper img,
html.dark .sidebar-wrapper[sidebar-layout] .logo-wrapper img,
html.dark .sidebar-wrapper[sidebar-layout] .logo-icon-wrapper img {
  filter: brightness(1.08) contrast(1.02);
}

body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-submenu,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-submenu,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-submenu {
  background: #2a313d !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header .profile-dropdown,
body.dark-only .page-wrapper.compact-wrapper .page-header .more_lang,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .profile-dropdown,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .more_lang,
html.dark .page-wrapper.compact-wrapper .page-header .profile-dropdown,
html.dark .page-wrapper.compact-wrapper .page-header .more_lang {
  background: #1f232b !important;
  border-color: #2f3642 !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header .profile-dropdown a,
body.dark-only .page-wrapper.compact-wrapper .page-header .more_lang .lang,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .profile-dropdown a,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .more_lang .lang,
html.dark .page-wrapper.compact-wrapper .page-header .profile-dropdown a,
html.dark .page-wrapper.compact-wrapper .page-header .more_lang .lang {
  color: #e5e7eb !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header .profile-dropdown a:hover,
body.dark-only .page-wrapper.compact-wrapper .page-header .more_lang .lang:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .profile-dropdown a:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .more_lang .lang:hover,
html.dark .page-wrapper.compact-wrapper .page-header .profile-dropdown a:hover,
html.dark .page-wrapper.compact-wrapper .page-header .more_lang .lang:hover {
  background: #2a313d !important;
}
body.dark-only .page-wrapper.compact-wrapper .page-header .header-search,
body.dark-only .page-wrapper.compact-wrapper .page-header .mode,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .header-search,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .mode,
html.dark .page-wrapper.compact-wrapper .page-header .header-search,
html.dark .page-wrapper.compact-wrapper .page-header .mode {
  background: rgba(255,255,255,.06) !important;
  border: 1px solid rgba(148,163,184,.28);
}
body.dark-only .page-wrapper.compact-wrapper .page-header .header-search:hover,
body.dark-only .page-wrapper.compact-wrapper .page-header .mode:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .header-search:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .mode:hover,
html.dark .page-wrapper.compact-wrapper .page-header .header-search:hover,
html.dark .page-wrapper.compact-wrapper .page-header .mode:hover {
  background: rgba(59,130,246,.24) !important;
}
body.dark-only .page-wrapper.compact-wrapper .page-header .header-search svg,
body.dark-only .page-wrapper.compact-wrapper .page-header .mode svg,
body.dark-only .page-wrapper.compact-wrapper .page-header .header-search svg use,
body.dark-only .page-wrapper.compact-wrapper .page-header .mode svg use,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .header-search svg,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .mode svg,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .header-search svg use,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .mode svg use,
html.dark .page-wrapper.compact-wrapper .page-header .header-search svg,
html.dark .page-wrapper.compact-wrapper .page-header .mode svg,
html.dark .page-wrapper.compact-wrapper .page-header .header-search svg use,
html.dark .page-wrapper.compact-wrapper .page-header .mode svg use {
  color: #ffffff !important;
  fill: #ffffff !important;
  stroke: #ffffff !important;
}
body.dark-only .page-wrapper.compact-wrapper .page-header .search-full .u-posRelative,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .search-full .u-posRelative,
html.dark .page-wrapper.compact-wrapper .page-header .search-full .u-posRelative {
  background: rgba(255,255,255,.08) !important;
  border-color: rgba(148,163,184,.24) !important;
}
body.dark-only .page-wrapper.compact-wrapper .page-header .search-full .Typeahead-spinner,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .search-full .Typeahead-spinner,
html.dark .page-wrapper.compact-wrapper .page-header .search-full .Typeahead-spinner,
body.dark-only .page-wrapper.compact-wrapper .page-header .search-full .close-search,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .search-full .close-search,
html.dark .page-wrapper.compact-wrapper .page-header .search-full .close-search {
  color: #ffffff !important;
}
body.dark-only .page-wrapper.compact-wrapper .page-header .header-logo-wrapper .toggle-sidebar,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header .header-logo-wrapper .toggle-sidebar,
html.dark .page-wrapper.compact-wrapper .page-header .header-logo-wrapper .toggle-sidebar {
  background: rgba(255,255,255,.08);
  border-color: rgba(148,163,184,.28);
  box-shadow: 0 4px 12px rgba(0,0,0,.28);
}
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.close_icon {
  box-shadow: 2px 0 16px rgba(0,0,0,.25);
}

/* === LIGHT (default) === */
.sidebar-wrapper[sidebar-layout] .sidebar-link i.dynamic-icon,
.sidebar-wrapper[sidebar-layout] .sidebar-link i.dynamic-icon::before {
  color: #000 !important;
  opacity: 1 !important;
}

/* === DARK (support several dark hooks your layout might use) === */
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-link i.dynamic-icon,
body.dark-only .sidebar-wrapper[sidebar-layout] .sidebar-link i.dynamic-icon::before,
.page-wrapper.compact-wrapper.dark-sidebar .sidebar-wrapper[sidebar-layout] .sidebar-link i.dynamic-icon,
.page-wrapper.compact-wrapper.dark-sidebar .sidebar-wrapper[sidebar-layout] .sidebar-link i.dynamic-icon::before,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-link i.dynamic-icon,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout] .sidebar-link i.dynamic-icon::before,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-link i.dynamic-icon,
html.dark .sidebar-wrapper[sidebar-layout] .sidebar-link i.dynamic-icon::before {
  color: #fff !important;
  opacity: 1 !important;
}
/* Hide text inside DataTables buttons on small devices */
@media (max-width: 768px) {
  .dt-buttons .btn .btn-text {
    display: none;
  }
  .btn .btn-text {
    display: none; /* hide text on mobile */
  }
}
/* ---- Make Select2 look like Bootstrap form controls ---- */
.select2-container .select2-selection--single,
.select2-container .select2-selection--multiple{
  min-height: calc(2.5rem + 2px);      /* ~form-control height */
  border: 1px solid #ced4da;
  border-radius: .375rem;               /* same radius */
  padding: .375rem .75rem;              /* same padding */
  box-shadow: none;
}

/* remove inner paddings so outer padding rules */
.select2-container .select2-selection__rendered{ padding: 0; }

/* Multi: hide chips and show compact summary */
.select2-selection--multiple .select2-selection__rendered li{ display:none; }
.select2-selection--multiple .select2-selection__rendered::before{
  content: attr(data-summary);
  line-height: 1.4;
}

/* Show muted color when empty */
.select2-selection__rendered.s2-empty::before{ color:#6c757d; }

/* Multi: keep search field tight */
.select2-selection--multiple .select2-search__field{
  margin: 0; padding: 0; height: 1px; line-height: 1px; border: 0; /* visually hidden */
}

/* Add a caret to multi so it looks like other selects */
.select2-container .select2-selection--multiple{ position: relative; }
.select2-container .select2-selection--multiple::after{
  content: "▾";                          /* caret */
  position: absolute; right: .65rem; top: 50%;
  transform: translateY(-50%); color:#6c757d; pointer-events:none;
}

/* Results scrolling & sticky toolbar (from earlier) */
.select2-container .select2-results__options{ max-height: 280px; overflow-y:auto; }
.select2-results__options .select2-actions{
  position: sticky; top:0; z-index:2; display:flex; gap:.5rem; align-items:center;
  padding:.5rem .75rem; background:#fff; border-bottom:1px solid #e5e7eb;
}
.select2-actions .s2-btn{
  border:1px solid #d1d5db; background:#f9fafb; padding:.5rem .75rem;
  font-size:.875rem; line-height:1.25rem; border-radius:.375rem; cursor:pointer;
}
.select2-actions .s2-btn:hover{ background:#f3f4f6; }
.select2-actions .s2-btn--outline{ background:#fff; }

@media (max-width:480px){
  .select2-actions{ flex-wrap:wrap; }
  .select2-actions .s2-btn{ flex:1 1 48%; }
}

/* Mobile: stack right-aligned action button rows */
@media (max-width: 767.98px) {
  .page-wrapper.compact-wrapper .page-body-wrapper .page-body {
    margin-top: 58px !important;
  }
  .page-wrapper.compact-wrapper .page-body-wrapper .page-title {
    margin: 0 -15px 10px !important;
    padding: 8px 12px !important;
    min-height: 42px;
  }
  .page-wrapper.compact-wrapper .page-body-wrapper .page-title .breadcrumb {
    justify-content: flex-start;
  }
  .sidebar-wrapper[sidebar-layout] .logo-wrapper a,
  .sidebar-wrapper[sidebar-layout] .logo-icon-wrapper {
    display: none !important;
  }
  .sidebar-wrapper[sidebar-layout] .logo-wrapper {
    min-height: 44px;
    border-bottom: 0 !important;
  }
  .page-body .d-flex.justify-content-end.gap-2 {
    flex-direction: column;
    align-items: stretch;
  }
  .page-body .d-flex.justify-content-end.gap-2 .btn,
  .page-body .d-flex.justify-content-end.gap-2 a.btn {
    width: 100%;
  }
}




/* ===== V2 premium sidebar final override ===== */
:root {
  --v2-sidebar-open-width: 280px;
  --v2-sidebar-closed-width: 84px;
  --v2-nav-ink: #0f172a;
  --v2-nav-muted: #64748b;
  --v2-nav-line: #dbe7f3;
  --v2-nav-primary: #1764a8;
  --v2-nav-blue: #1764a8;
  --v2-nav-blue-dark: #0f4f85;
  --v2-nav-shadow: 10px 0 24px rgba(15, 23, 42, .08);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  height: 100vh !important;
  width: calc(var(--v2-sidebar-open-width) + 1px) !important;
  background: radial-gradient(circle at 28px 86px, rgba(23, 100, 168, .12), transparent 26px), radial-gradient(circle at 240px 170px, rgba(23, 100, 168, .14), transparent 42px), linear-gradient(180deg, #ffffff 0%, #f7fbff 50%, #eef7ff 100%) !important;
  border-right: 1px solid rgba(219, 231, 243, .95) !important;
  box-shadow: var(--v2-nav-shadow) !important;
  z-index: 51 !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] > div {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-wrapper,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-icon-wrapper {
  height: var(--v2-header-height) !important;
  min-height: var(--v2-header-height) !important;
  padding: 0 18px !important;
  margin: 0 !important;
  background: rgba(255, 255, 255, .78) !important;
  border-bottom: 1px solid rgba(219, 231, 243, .9) !important;
  backdrop-filter: blur(16px);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-wrapper a,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-icon-wrapper a {
  display: flex !important;
  align-items: center !important;
  justify-content: flex-start;
  height: var(--v2-header-height) !important;
  min-height: var(--v2-header-height) !important;
  width: 100%;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-wrapper img {
  height: 50px !important;
  max-height: 50px !important;
  width: auto;
  object-fit: contain;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-icon-wrapper img {
  height: 42px !important;
  max-height: 42px !important;
  width: auto;
  object-fit: contain;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .back-btn {
  width: 34px;
  height: 34px;
  border: 0;
  border-radius: 12px;
  display: inline-grid;
  place-items: center;
  color: var(--v2-nav-blue);
  background: rgba(23, 100, 168, .10);
  box-shadow: inset 0 0 0 1px rgba(23, 100, 168, .12);
  transition: background-color .2s ease, color .2s ease, transform .2s ease;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .back-btn:hover {
  color: #ffffff;
  background: var(--v2-nav-blue);
  transform: translateX(-2px);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-main {
  flex: 1 1 auto;
  min-height: 0;
  padding: 0 !important;
  overflow: hidden;
  background: transparent !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] #sidebar-menu,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .simplebar-wrapper,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .simplebar-mask,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .simplebar-content-wrapper { height: 100%; }

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .simplebar-content-wrapper,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] #sidebar-menu {
  overflow-y: auto !important;
  overflow-x: hidden !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-links {
  min-height: 100%;
  padding: 14px 14px 24px !important;
  background: transparent !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .simplebar-content-wrapper::-webkit-scrollbar,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] #sidebar-menu::-webkit-scrollbar { width: 7px; }

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .simplebar-content-wrapper::-webkit-scrollbar-thumb,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] #sidebar-menu::-webkit-scrollbar-thumb {
  background: rgba(100, 116, 139, .28);
  border-radius: 999px;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-card {
  position: relative;
  overflow: hidden;
  border-radius: 22px;
  padding: 16px 14px !important;
  margin: 0 0 12px !important;
  text-align: left !important;
  background: linear-gradient(135deg, rgba(23, 100, 168, .13), rgba(23, 100, 168, .08)), #ffffff !important;
  border: 1px solid rgba(219, 231, 243, .95);
  box-shadow: 0 18px 36px rgba(15, 23, 42, .08);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-card::after {
  content: "";
  position: absolute;
  right: -22px;
  top: -28px;
  width: 92px;
  height: 92px;
  border-radius: 999px;
  background: rgba(23, 100, 168, .16);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-avatar-wrap {
  position: relative;
  display: inline-flex;
  margin-bottom: 10px;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-card .user-avatar {
  width: 58px;
  height: 58px;
  border: 3px solid #ffffff;
  box-shadow: 0 12px 24px rgba(15, 23, 42, .16) !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-online-dot {
  position: absolute;
  right: 2px;
  bottom: 4px;
  width: 13px;
  height: 13px;
  border-radius: 999px;
  background: var(--v2-nav-primary);
  border: 2px solid #ffffff;
  box-shadow: 0 0 0 4px rgba(23, 100, 168, .12);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-copy,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-profile-link { position: relative; z-index: 1; }

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-card .user-name {
  color: var(--v2-nav-ink) !important;
  font-size: 15px;
  font-weight: 800;
  letter-spacing: 0;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-card .user-group {
  display: inline-flex;
  max-width: 100%;
  color: var(--v2-nav-blue) !important;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-profile-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 10px;
  color: var(--v2-nav-primary);
  font-size: 12px;
  font-weight: 800;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-profile-link::after {
  content: "";
  width: 14px;
  height: 1px;
  background: currentColor;
  transition: width .2s ease;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-profile-link:hover::after { width: 22px; }

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-balance-card {
  margin: 0 0 14px !important;
  padding: 13px 14px !important;
  border-radius: 18px;
  text-align: left !important;
  background: linear-gradient(135deg, var(--v2-nav-blue), var(--v2-nav-blue-dark));
  box-shadow: 0 16px 30px rgba(23, 100, 168, .24);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-balance-card .balance-label {
  display: block;
  color: rgba(255, 255, 255, .74) !important;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: .09em;
  margin-bottom: 4px;
  text-transform: uppercase;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-balance-card .balance-display,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-balance-card #tamaBalance {
  display: block;
  min-width: 0;
  margin: 0;
  padding: 0;
  color: #ffffff !important;
  background: transparent;
  border: 0;
  font-size: 19px;
  line-height: 1.25;
}

.page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-balance-card,
.page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-balance-card {
  display: none !important;
  height: 0 !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  overflow: hidden !important;
  opacity: 0 !important;
  pointer-events: none !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-section-title {
  padding: 10px 6px 8px;
  color: var(--v2-nav-muted);
  font-size: 11px;
  font-weight: 900;
  letter-spacing: .12em;
  text-transform: uppercase;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list {
  position: relative;
  margin: 0 0 7px !important;
  animation: v2SidebarItemIn .28s ease both;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list > a.sidebar-link {
  position: relative;
  min-height: 46px;
  padding: 8px 10px !important;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
  max-width: 100%;
  color: var(--v2-nav-ink) !important;
  background: rgba(255, 255, 255, .62) !important;
  border: 1px solid rgba(219, 231, 243, .72);
  box-shadow: 0 8px 18px rgba(15, 23, 42, .035);
  transform: translateX(0);
  transition: background .2s ease, border-color .2s ease, box-shadow .2s ease, transform .2s ease, color .2s ease;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list > a.sidebar-link::before {
  content: "";
  position: absolute;
  left: -14px;
  width: 4px;
  height: 0;
  border-radius: 0 999px 999px 0;
  background: var(--v2-nav-primary);
  transition: height .2s ease;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:hover > a.sidebar-link {
  color: var(--v2-nav-ink) !important;
  background: #ffffff !important;
  border-color: rgba(23, 100, 168, .28) !important;
  box-shadow: 0 14px 28px rgba(15, 23, 42, .08);
  transform: translateX(3px);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list.active > a.sidebar-link,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list > a.sidebar-link.active {
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--v2-nav-primary), var(--v2-nav-blue-dark)) !important;
  border-color: rgba(255, 255, 255, .36) !important;
  box-shadow: 0 16px 32px rgba(23, 100, 168, .28);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list.active > a.sidebar-link::before,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list > a.sidebar-link.active::before { height: 28px; }

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-icon-frame {
  width: 32px;
  height: 32px;
  min-width: 32px;
  flex: 0 0 32px;
  border-radius: 12px;
  display: inline-grid;
  place-items: center;
  overflow: hidden;
  background: rgba(23, 100, 168, .10);
  color: var(--v2-nav-blue);
  box-shadow: inset 0 0 0 1px rgba(23, 100, 168, .08);
  transition: background .2s ease, color .2s ease, transform .2s ease;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .dynamic-icon,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .dynamic-icon::before {
  width: auto !important;
  min-width: 0 !important;
  margin: 0 !important;
  color: inherit !important;
  font-size: 15px;
  line-height: 1;
  text-align: center;
  transform-origin: center;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:hover .v2-sidebar-icon-frame {
  background: rgba(23, 100, 168, .13);
  color: var(--v2-nav-primary);
  transform: scale(1.04);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list.active .v2-sidebar-icon-frame,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-link.active .v2-sidebar-icon-frame {
  background: rgba(255, 255, 255, .2);
  color: #ffffff;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .28);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-label {
  flex: 1 1 0;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: inherit !important;
  font-size: 13.5px;
  font-weight: 800;
  letter-spacing: 0;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .according-menu { margin-left: 2px; color: inherit; opacity: .7; }
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .according-menu i { color: inherit !important; }

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-menu-badge {
  flex: 0 0 auto;
  margin-left: 6px;
  padding: 3px 7px;
  border-radius: 999px;
  color: var(--v2-nav-blue);
  background: rgba(23, 100, 168, .12);
  font-size: 10px;
  font-weight: 900;
  letter-spacing: .06em;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list.active .v2-sidebar-menu-badge,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-link.active .v2-sidebar-menu-badge {
  color: #ffffff;
  background: rgba(255, 255, 255, .18);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-menu-badge--alert {
  color: #b42318;
  background: rgba(244, 63, 94, .12);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu {
  position: relative;
  margin: 8px 0 2px 18px !important;
  padding: 8px 8px 8px 14px !important;
  border-radius: 16px;
  border: 1px solid rgba(219, 231, 243, .82);
  background: rgba(255, 255, 255, .66) !important;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, .72);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu::before {
  content: "";
  position: absolute;
  left: 8px;
  top: 12px;
  bottom: 12px;
  width: 1px;
  background: rgba(23, 100, 168, .22);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li { position: relative; }

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.li-a {
  min-height: 34px;
  padding: 7px 9px 7px 14px !important;
  border-radius: 11px;
  color: var(--v2-nav-muted) !important;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  font-weight: 800;
  background: transparent !important;
  transition: background .18s ease, color .18s ease, transform .18s ease;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a::before {
  content: "";
  width: 6px;
  height: 6px;
  border-radius: 999px;
  background: rgba(100, 116, 139, .38);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:hover > a,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active {
  color: var(--theme-primary, var(--v2-nav-blue)) !important;
  background: rgba(var(--theme-primary-rgb, 23, 100, 168), .10) !important;
  transform: translateX(2px);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a::before,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active::before {
  background: var(--theme-sidebar-active, var(--v2-nav-primary));
  box-shadow: 0 0 0 4px rgba(var(--theme-sidebar-active-rgb, 23, 100, 168), .12);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active {
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--theme-sidebar-active, var(--v2-nav-primary)), var(--theme-primary, var(--v2-nav-blue))) !important;
  border-color: rgba(var(--theme-sidebar-active-rgb, 23, 100, 168), .30) !important;
  box-shadow: 0 8px 18px rgba(var(--theme-sidebar-active-rgb, 23, 100, 168), .24) !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a span,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active span,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a i,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active i,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a i::before,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active i::before {
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-empty {
  padding: 14px;
  border-radius: 16px;
  color: var(--v2-nav-muted);
  background: rgba(255, 255, 255, .7);
  border: 1px dashed rgba(100, 116, 139, .28);
  font-size: 13px;
  font-weight: 700;
}

@keyframes v2SidebarItemIn {
  from { opacity: 0; transform: translateX(-8px); }
  to { opacity: 1; transform: translateX(0); }
}

@media (min-width: 992px) {
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar] {
    transition: width .28s ease, box-shadow .28s ease, transform .28s ease !important;
  }

  .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .back-btn {
    display: none !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon { width: calc(var(--v2-sidebar-closed-width) + 1px) !important; }
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover { width: calc(var(--v2-sidebar-closed-width) + 1px) !important; }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .logo-wrapper,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-user-card,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-balance-card,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-section-title,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-label,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-menu-badge,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .according-menu,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .sidebar-submenu {
    opacity: 0;
    pointer-events: none;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .logo-icon-wrapper {
    opacity: 1;
    max-height: var(--v2-header-height);
    pointer-events: auto;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .logo-icon-wrapper a {
    justify-content: center !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .sidebar-list > a.sidebar-link {
    justify-content: center;
    gap: 0;
    padding-left: 7px !important;
    padding-right: 7px !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-balance-card,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-balance-card {
    display: none !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-label,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-menu-badge,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .according-menu {
    width: 0 !important;
    min-width: 0 !important;
    max-width: 0 !important;
    flex: 0 0 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-icon-frame {
    display: inline-grid !important;
    width: 32px !important;
    min-width: 32px !important;
    max-width: 32px !important;
    flex: 0 0 32px !important;
    margin: 0 !important;
    padding: 0 !important;
    opacity: 1 !important;
    transform: none !important;
    pointer-events: auto !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .dynamic-icon {
    transform: none !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover) .sidebar-submenu {
    display: none !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-wrapper,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-user-card,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-balance-card,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-section-title,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-label,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-menu-badge,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .according-menu,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .sidebar-submenu {
    opacity: 0;
    pointer-events: none;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-wrapper {
    display: flex !important;
    opacity: 1;
    max-height: var(--v2-header-height);
    pointer-events: auto;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-wrapper a {
    justify-content: center !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-wrapper img {
    height: 42px !important;
    max-height: 42px !important;
    max-width: calc(var(--v2-sidebar-closed-width) - 14px);
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-icon-wrapper {
    opacity: 0;
    max-height: 0;
    pointer-events: none;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-icon-wrapper a {
    justify-content: center !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .sidebar-list > a.sidebar-link {
    justify-content: center;
    gap: 0;
    padding-left: 7px !important;
    padding-right: 7px !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-label,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-menu-badge,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .according-menu {
    width: 0 !important;
    min-width: 0 !important;
    max-width: 0 !important;
    flex: 0 0 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-icon-frame {
    display: inline-grid !important;
    width: 32px !important;
    min-width: 32px !important;
    max-width: 32px !important;
    flex: 0 0 32px !important;
    margin: 0 !important;
    padding: 0 !important;
    opacity: 1 !important;
    transform: none !important;
    pointer-events: auto !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .dynamic-icon {
    transform: none !important;
  }
}

@media (max-width: 991.98px) {
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar] {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    z-index: 1052;
    width: min(88vw, 320px) !important;
    height: 100vh !important;
    transform: translateX(0);
    border-radius: 0 24px 24px 0;
    transition: transform .28s ease, box-shadow .28s ease !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon {
    transform: translateX(-108%);
    box-shadow: none !important;
  }

  .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-wrapper,
  .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-wrapper a { display: flex !important; }
  .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-icon-wrapper { display: none !important; }
  .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .back-btn { display: inline-grid !important; }

  body .bg-overlay.active,
  body .bg-overlay {
    background: rgba(15, 23, 42, .38) !important;
    backdrop-filter: blur(5px);
  }
}

@media (max-width: 575.98px) {
  .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-links { padding: 12px 12px 22px !important; }
  .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-card { padding: 14px 12px !important; }
}

body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar],
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar],
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] {
  --v2-nav-ink: #eef6ff;
  --v2-nav-muted: #9fb2ca;
  background: radial-gradient(circle at 32px 92px, rgba(59, 130, 246, .18), transparent 30px), radial-gradient(circle at 240px 170px, rgba(59, 130, 246, .18), transparent 46px), linear-gradient(180deg, #111827 0%, #172033 100%) !important;
  border-right-color: rgba(49, 68, 93, .95) !important;
}

body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-wrapper,
body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-icon-wrapper,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-wrapper,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-icon-wrapper,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-wrapper,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .logo-icon-wrapper {
  background: rgba(17, 24, 39, .82) !important;
  border-bottom-color: rgba(49, 68, 93, .82) !important;
}

body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-card,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-card,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-card,
body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list > a.sidebar-link,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list > a.sidebar-link,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list > a.sidebar-link,
body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu {
  background: rgba(15, 23, 42, .62) !important;
  border-color: rgba(49, 68, 93, .82) !important;
}

body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:hover > a.sidebar-link,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:hover > a.sidebar-link,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:hover > a.sidebar-link { background: rgba(30, 41, 59, .92) !important; }

body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-card .user-name,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-card .user-name,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-user-card .user-name { color: #ffffff !important; }

body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-profile-link,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-profile-link,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-profile-link,
body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:hover > a,
body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a,
body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:hover > a,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:hover > a,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active { color: #93c5fd !important; }

body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-icon-frame,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-icon-frame,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .v2-sidebar-icon-frame {
  color: #9cc8ff;
  background: rgba(59, 130, 246, .14);
  box-shadow: inset 0 0 0 1px rgba(147, 197, 253, .14);
}

/* V2 sidebar contrast tuning: hover stays tinted, active stays solid. */
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] {
  --v2-nav-hover-ink: #0f4f85;
  --v2-nav-hover-icon: #1764a8;
  --v2-nav-hover-bg: linear-gradient(135deg, rgba(23, 100, 168, .16), rgba(23, 100, 168, .08)), rgba(255, 255, 255, .9);
  --v2-nav-hover-border: rgba(23, 100, 168, .34);
  --v2-nav-hover-shadow: 0 16px 30px rgba(15, 79, 133, .12);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:not(.active):hover > a.sidebar-link:not(.active) {
  color: var(--v2-nav-hover-ink) !important;
  background: var(--v2-nav-hover-bg) !important;
  border-color: var(--v2-nav-hover-border) !important;
  box-shadow: var(--v2-nav-hover-shadow);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:not(.active):hover > a.sidebar-link:not(.active) > span,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:not(.active):hover > a.sidebar-link:not(.active) .v2-sidebar-label,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:not(.active):hover > a.sidebar-link:not(.active) .according-menu,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:not(.active):hover > a.sidebar-link:not(.active) .according-menu i,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:not(.active):hover > a.sidebar-link:not(.active) .dynamic-icon {
  color: var(--v2-nav-hover-ink) !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:not(.active):hover > a.sidebar-link:not(.active) .dynamic-icon::before {
  color: var(--v2-nav-hover-ink) !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:not(.active):hover > a.sidebar-link:not(.active) .v2-sidebar-icon-frame {
  color: var(--v2-nav-hover-icon) !important;
  background: rgba(23, 100, 168, .16) !important;
  box-shadow: inset 0 0 0 1px rgba(23, 100, 168, .18);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:not(.active):hover > a.sidebar-link:not(.active) .v2-sidebar-menu-badge {
  color: var(--v2-nav-blue) !important;
  background: rgba(23, 100, 168, .14) !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list.active:hover > a.sidebar-link,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:hover > a.sidebar-link.active {
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--v2-nav-primary), var(--v2-nav-blue-dark)) !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list.active:hover > a.sidebar-link > span,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list.active:hover > a.sidebar-link .dynamic-icon,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list.active:hover > a.sidebar-link .according-menu i,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:hover > a.sidebar-link.active > span,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:hover > a.sidebar-link.active .dynamic-icon,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:hover > a.sidebar-link.active .according-menu i {
  color: #ffffff !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list.active:hover > a.sidebar-link .dynamic-icon::before,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-list:hover > a.sidebar-link.active .dynamic-icon::before {
  color: #ffffff !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a:not(.active),
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a.li-a:not(.active) {
  color: var(--theme-primary, var(--v2-nav-blue)) !important;
  background: rgba(var(--theme-primary-rgb, 23, 100, 168), .10) !important;
  box-shadow: inset 0 0 0 1px rgba(var(--theme-primary-rgb, 23, 100, 168), .14);
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a:not(.active) span,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a.li-a:not(.active) span {
  color: var(--theme-primary, var(--v2-nav-blue)) !important;
}

.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a span,
.sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active span {
  color: #ffffff !important;
}

body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar],
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar],
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] {
  --v2-nav-hover-ink: #dbeafe;
  --v2-nav-hover-icon: #93c5fd;
  --v2-nav-hover-bg: linear-gradient(135deg, rgba(59, 130, 246, .20), rgba(23, 100, 168, .14)), rgba(30, 41, 59, .94);
  --v2-nav-hover-border: rgba(59, 130, 246, .38);
  --v2-nav-hover-shadow: 0 18px 34px rgba(0, 0, 0, .28);
}

body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a:not(.active),
body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a.li-a:not(.active),
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a:not(.active),
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a.li-a:not(.active),
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a:not(.active),
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a.li-a:not(.active) {
  color: #dbeafe !important;
  background: rgba(59, 130, 246, .16) !important;
  box-shadow: inset 0 0 0 1px rgba(59, 130, 246, .20);
}

body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a:not(.active) span,
body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a.li-a:not(.active) span,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a:not(.active) span,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a.li-a:not(.active) span,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a:not(.active) span,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li:not(.active):hover > a.li-a:not(.active) span {
  color: #dbeafe !important;
}

body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a span,
body.dark-only .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active span,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a span,
[data-bs-theme="dark"] .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active span,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li.active > a span,
html.dark .sidebar-wrapper[sidebar-layout][data-v2-sidebar] .sidebar-submenu > li > a.active span {
  color: #dbeafe !important;
}

/* ===== V2 premium header final override ===== */
:root {
  --v2-header-height: 72px;
  --v2-header-primary: #1764a8;
  --v2-header-primary-dark: #0f4f85;
  --v2-header-blue: #1764a8;
  --v2-header-ink: #0f172a;
  --v2-header-muted: #64748b;
  --v2-header-line: rgba(226, 232, 240, .9);
  --v2-header-glass: rgba(255, 255, 255, .82);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] {
  min-height: var(--v2-header-height) !important;
  background: linear-gradient(135deg, rgba(23, 100, 168, .13), rgba(23, 100, 168, .10)), #f8fafc !important;
  border-bottom: 1px solid var(--v2-header-line) !important;
  box-shadow: 0 18px 42px rgba(15, 23, 42, .08) !important;
  z-index: 50 !important;
  overflow: visible !important;
  backdrop-filter: blur(18px);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell {
  min-height: var(--v2-header-height) !important;
  display: flex !important;
  flex-wrap: nowrap !important;
  padding: 10px 18px !important;
  align-items: center !important;
  gap: 14px !important;
  overflow: visible !important;
  background: transparent !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .page-body {
  margin-top: var(--v2-header-height) !important;
  min-height: calc(100vh - var(--v2-header-height)) !important;
}

@media (min-width: 992px) {
  .page-wrapper.compact-wrapper .page-header[data-v2-header] {
    margin-left: calc(var(--v2-layout-sidebar-width) + var(--v2-bs-content-gutter, 1.5rem)) !important;
    width: calc(100% - var(--v2-layout-sidebar-width) - var(--v2-bs-content-gutter, 1.5rem)) !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header].close_icon {
    margin-left: calc(86px + var(--v2-bs-content-gutter, 1.5rem)) !important;
    width: calc(100% - 86px - var(--v2-bs-content-gutter, 1.5rem)) !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar] ~ .page-body,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar] ~ footer {
    margin-left: calc(var(--v2-layout-sidebar-width) + var(--v2-bs-content-gutter, 1.5rem)) !important;
    width: calc(100% - var(--v2-layout-sidebar-width) - var(--v2-bs-content-gutter, 1.5rem)) !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon ~ .page-body,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon ~ footer {
    margin-left: calc(86px + var(--v2-bs-content-gutter, 1.5rem)) !important;
    width: calc(100% - 86px - var(--v2-bs-content-gutter, 1.5rem)) !important;
  }
}

@media (max-width: 991.98px) {
  .page-wrapper.compact-wrapper .page-header[data-v2-header] {
    left: 0 !important;
    right: 0 !important;
    margin-left: 0 !important;
    width: 100% !important;
    max-width: 100vw !important;
    transform: none !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell {
    margin: 0 !important;
    max-width: 100vw !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar] ~ .page-body,
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar] ~ footer {
    margin-left: 0 !important;
  }
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand {
  width: auto !important;
  min-width: 0 !important;
  flex: 1 1 auto !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 12px !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card {
  width: auto !important;
  min-width: 0 !important;
  max-width: 100% !important;
  min-height: 44px !important;
  padding: 0 !important;
  flex: 1 1 auto !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: flex-start !important;
  justify-content: center !important;
  gap: 3px !important;
  border-radius: 0 !important;
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
  overflow: visible !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card::before {
  content: none !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title {
  width: 100% !important;
  min-width: 0 !important;
  flex: 0 1 auto !important;
  display: flex !important;
  align-items: center !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-main {
  display: flex !important;
  min-width: 0 !important;
  max-width: 100% !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-main::before {
  content: none !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-heading {
  margin: 0 !important;
  max-width: 100% !important;
  color: #0f172a !important;
  font-size: 21px !important;
  font-weight: 900 !important;
  line-height: 1.08 !important;
  letter-spacing: 0 !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-nav {
  width: 100% !important;
  min-width: 0 !important;
  flex: 0 0 auto !important;
  display: flex !important;
  align-items: center !important;
  opacity: .72 !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list {
  min-width: 0 !important;
  min-height: 18px !important;
  margin: 0 !important;
  padding: 0 !important;
  display: flex !important;
  align-items: center !important;
  flex-wrap: nowrap !important;
  gap: 4px !important;
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-item {
  min-width: 0 !important;
  display: inline-flex !important;
  align-items: center !important;
  color: #64748b !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  line-height: 1.1 !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item + .breadcrumb-item::before {
  content: "/" !important;
  padding: 0 1px 0 0 !important;
  color: #94a3b8 !important;
  font-size: 11px !important;
  font-weight: 700 !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current {
  min-width: 0 !important;
  max-width: 180px !important;
  min-height: 18px !important;
  padding: 0 !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 4px !important;
  border-radius: 0 !important;
  color: #64748b !important;
  background: transparent !important;
  box-shadow: none !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link:hover {
  color: #1d4ed8 !important;
  background: transparent !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item.active .v2-breadcrumb-current,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-item.active .v2-breadcrumb-current {
  color: #475569 !important;
  background: transparent !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-actions {
  position: relative !important;
  top: auto !important;
  right: auto !important;
  bottom: auto !important;
  left: auto !important;
  transform: none !important;
  flex: 0 0 auto !important;
  width: auto !important;
  min-width: 0 !important;
  max-width: none !important;
  margin-left: auto !important;
  display: flex !important;
  align-items: center !important;
  justify-content: flex-end !important;
  z-index: 2 !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .nav-menus {
  width: auto !important;
  display: flex !important;
  align-items: center !important;
  justify-content: flex-end !important;
  flex-wrap: nowrap !important;
  gap: 12px !important;
  margin: 0 !important;
  padding: 0 !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .nav-menus > li {
  flex: 0 0 auto !important;
  height: auto !important;
  min-width: 0 !important;
  margin: 0 !important;
  list-style: none !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcuts-item {
  display: inline-flex !important;
  align-items: center !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcuts {
  min-width: 0 !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  padding: 0 !important;
  margin: 0 !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut {
  min-width: 0 !important;
  min-height: 42px !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  padding: 6px 11px 6px 7px !important;
  border-radius: 14px !important;
  border: 1px solid rgba(15, 23, 42, .10) !important;
  background: rgba(255, 255, 255, .74) !important;
  color: #0f172a !important;
  text-decoration: none !important;
  font-size: 12px !important;
  line-height: 1 !important;
  font-weight: 850 !important;
  white-space: nowrap !important;
  box-shadow: 0 8px 18px rgba(15, 23, 42, .06) !important;
  transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease, background-color .16s ease !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut:hover,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut.active {
  transform: translateY(-1px) !important;
  border-color: rgba(15, 23, 42, .16) !important;
  box-shadow: 0 10px 22px rgba(15, 23, 42, .09) !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut-icon {
  width: 30px !important;
  height: 30px !important;
  flex: 0 0 30px !important;
  border-radius: 10px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-size: 14px !important;
  line-height: 1 !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut-label {
  display: inline-block !important;
  min-width: 0 !important;
  max-width: 96px !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card {
  background: transparent !important;
  border-color: transparent !important;
  box-shadow: none !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-heading,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-heading,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-heading {
  color: #f8fafc !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current {
  color: #94a3b8 !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item.active .v2-breadcrumb-current,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-item.active .v2-breadcrumb-current,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item.active .v2-breadcrumb-current,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-item.active .v2-breadcrumb-current,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item.active .v2-breadcrumb-current,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-item.active .v2-breadcrumb-current {
  color: #cbd5e1 !important;
  background: transparent !important;
  box-shadow: none !important;
}

@media (max-width: 1399.98px) {
  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card {
    max-width: 100% !important;
  }
}

@media (max-width: 1199.98px) {
  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-nav {
    display: none !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card {
    max-width: 100% !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle {
    min-width: 118px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option {
    min-width: 50px !important;
    padding: 7px 8px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-actions .nav-menus {
    gap: 8px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcuts {
    gap: 6px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut {
    min-height: 40px !important;
    padding: 5px !important;
    border-radius: 13px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-service-shortcut-label {
    display: none !important;
  }
}

@media (max-width: 991.98px) {
  :root { --v2-header-height: 64px; }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell {
    gap: 10px !important;
    padding: 8px 12px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand {
    flex: 1 1 auto !important;
    width: auto !important;
    min-width: 0 !important;
    gap: 8px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card {
    flex: 1 1 auto !important;
    max-width: none !important;
    min-width: 0 !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-heading {
    font-size: 18px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-actions {
    flex: 0 0 auto !important;
    min-width: 0 !important;
    margin-left: 8px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-actions .nav-menus {
    gap: 7px !important;
  }
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-header-icon-button {
  min-width: 44px;
  height: 44px;
  padding: 0 13px;
  border: 0;
  border-radius: 16px;
  display: inline-flex !important;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: var(--v2-header-ink) !important;
  background: rgba(255, 255, 255, .76) !important;
  box-shadow: inset 0 0 0 1px rgba(226, 232, 240, .88), 0 10px 24px rgba(15, 23, 42, .06);
  transition: transform .2s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button:hover,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-header-icon-button:hover {
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--v2-header-primary), var(--v2-header-primary-dark)) !important;
  box-shadow: 0 14px 28px rgba(23, 100, 168, .22);
  transform: translateY(-1px);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button svg,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button svg use,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button i {
  width: 18px;
  height: 18px;
  color: currentColor !important;
  fill: currentColor !important;
  stroke: currentColor !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .header-logo-wrapper .toggle-sidebar.v2-header-icon-button {
  width: 44px !important;
  min-width: 44px !important;
  height: 44px !important;
  margin: 0 !important;
  padding: 0 !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: var(--v2-header-ink) !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .header-logo-wrapper .toggle-sidebar.v2-header-icon-button .sidebar-toggle,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .header-logo-wrapper .toggle-sidebar.v2-header-icon-button svg,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .header-logo-wrapper .toggle-sidebar.v2-header-icon-button svg use,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .header-logo-wrapper .toggle-sidebar.v2-header-icon-button svg * {
  color: currentColor !important;
  stroke: currentColor !important;
  fill: none !important;
  opacity: 1 !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle {
  width: auto;
  min-width: 144px !important;
  height: 42px !important;
  padding: 5px 7px !important;
  gap: 7px !important;
  border-radius: 16px !important;
  justify-content: space-between;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover {
  color: var(--v2-header-ink) !important;
  background: rgba(255, 255, 255, .76) !important;
  box-shadow: inset 0 0 0 1px rgba(226, 232, 240, .88), 0 12px 26px rgba(15, 23, 42, .08);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 7px !important;
  min-width: 62px !important;
  padding: 7px 10px !important;
  border-radius: 12px !important;
  color: var(--v2-header-muted);
  font-size: 11px;
  font-weight: 900;
  line-height: 1 !important;
  white-space: nowrap !important;
  transition: background .2s ease, color .2s ease, box-shadow .2s ease;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle .v2-theme-icon {
  width: 14px !important;
  height: 14px !important;
  font-size: 14px !important;
  line-height: 1 !important;
  margin: 0 !important;
  flex: 0 0 auto;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle .v2-theme-option--light {
  color: #ffffff;
  background: linear-gradient(135deg, var(--v2-header-primary), var(--v2-header-primary-dark));
  box-shadow: 0 8px 18px rgba(23, 100, 168, .20);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--light {
  color: var(--v2-header-muted);
  background: transparent;
  box-shadow: none;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--dark {
  color: #ffffff;
  background: linear-gradient(135deg, var(--v2-header-primary), var(--v2-header-primary-dark));
  box-shadow: 0 8px 18px rgba(23, 100, 168, .20);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-action-label {
  color: inherit !important;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: .02em;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select {
  position: relative;
  display: inline-flex;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current {
  height: 44px;
  min-width: 92px;
  padding: 0 12px;
  border: 0;
  border-radius: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  color: var(--v2-header-ink) !important;
  background: rgba(255, 255, 255, .78) !important;
  box-shadow: inset 0 0 0 1px rgba(226, 232, 240, .9), 0 10px 24px rgba(15, 23, 42, .06);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current .lang {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-icon {
  width: 15px;
  height: 15px;
  font-size: 14px;
  color: currentColor !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current .flag-icon,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .flag-icon {
  width: 19px;
  height: 14px;
  border-radius: 4px;
  box-shadow: 0 0 0 1px rgba(15, 23, 42, .10);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current .lang-txt {
  color: inherit !important;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: .05em;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current .fa-angle-down {
  color: var(--v2-header-muted) !important;
  font-size: 12px;
  transition: transform .18s ease;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:hover .v2-language-current,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:focus-within .v2-language-current,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select.is-open .v2-language-current {
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--v2-header-blue), #0f4f85) !important;
  box-shadow: 0 14px 28px rgba(23, 100, 168, .24);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:hover .fa-angle-down,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:focus-within .fa-angle-down,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select.is-open .fa-angle-down {
  color: #ffffff !important;
  transform: rotate(180deg);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .more_lang.v2-language-menu {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  z-index: 1080;
  min-width: 176px;
  padding: 8px;
  border-radius: 18px;
  display: block !important;
  opacity: 0;
  visibility: hidden;
  transform: translateY(8px) scale(.98);
  pointer-events: none;
  background: rgba(255, 255, 255, .96) !important;
  border: 1px solid rgba(226, 232, 240, .92) !important;
  box-shadow: 0 22px 48px rgba(15, 23, 42, .16);
  transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:hover .v2-language-menu,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:focus-within .v2-language-menu,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select.is-open .v2-language-menu {
  opacity: 1;
  visibility: visible;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang {
  min-height: 40px;
  padding: 9px 10px;
  border-radius: 13px;
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--v2-header-ink) !important;
  font-size: 13px;
  font-weight: 800;
  background: transparent !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-code {
  width: 30px;
  height: 24px;
  border-radius: 9px;
  display: inline-grid;
  place-items: center;
  color: var(--v2-header-blue);
  background: rgba(23, 100, 168, .10);
  font-size: 10px;
  font-weight: 900;
  letter-spacing: .05em;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang:hover,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang.selected {
  color: var(--v2-header-blue) !important;
  background: rgba(23, 100, 168, .10) !important;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang .fa-check {
  margin-left: auto;
  color: var(--v2-header-primary) !important;
  font-size: 12px;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang:hover .v2-language-code,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang.selected .v2-language-code {
  color: var(--v2-header-blue);
  background: rgba(23, 100, 168, .14);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger {
  min-height: 48px;
  padding: 5px 8px 5px 6px;
  border: 0;
  border-radius: 18px;
  gap: 10px;
  color: var(--v2-header-ink) !important;
  background: rgba(255, 255, 255, .78) !important;
  box-shadow: inset 0 0 0 1px rgba(226, 232, 240, .9), 0 12px 26px rgba(15, 23, 42, .07);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-avatar {
  width: 36px;
  height: 36px;
  border-radius: 14px;
  display: inline-grid;
  place-items: center;
  color: #ffffff;
  background: linear-gradient(135deg, var(--v2-header-primary), var(--v2-header-blue));
  font-size: 14px;
  font-weight: 900;
  box-shadow: 0 10px 20px rgba(23, 100, 168, .22);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-copy {
  min-width: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  line-height: 1.08;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-copy span {
  max-width: 140px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: var(--v2-header-ink) !important;
  font-size: 13px;
  font-weight: 900;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-copy small {
  max-width: 140px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: var(--v2-header-muted) !important;
  font-size: 11px;
  font-weight: 800;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger .fa-angle-down {
  color: var(--v2-header-muted) !important;
  font-size: 12px;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown,
.page-wrapper.compact-wrapper .page-header[data-v2-header] .profile-dropdown.v2-profile-dropdown {
  min-width: 190px;
  padding: 8px;
  border-radius: 18px;
  background: rgba(255, 255, 255, .96) !important;
  border: 1px solid rgba(226, 232, 240, .92) !important;
  box-shadow: 0 22px 48px rgba(15, 23, 42, .16);
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown a {
  min-height: 39px;
  padding: 9px 10px;
  border-radius: 13px;
  display: flex;
  align-items: center;
  gap: 9px;
  color: var(--v2-header-ink) !important;
  font-size: 13px;
  font-weight: 800;
}

.page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown a:hover {
  color: var(--v2-header-blue) !important;
  background: rgba(23, 100, 168, .10) !important;
}

html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] {
  --v2-header-primary: #2563eb;
  --v2-header-primary-dark: #1d4ed8;
  --v2-header-blue: #2563eb;
  --v2-header-ink: #0f172a;
  --v2-header-muted: #64748b;
  --v2-header-line: rgba(203, 213, 225, .82);
  --v2-header-glass: rgba(255, 255, 255, .84);
  color: var(--v2-header-ink) !important;
  background: linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .92)) !important;
}

html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-heading {
  color: var(--v2-header-ink) !important;
}

html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-copy small,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger .fa-angle-down,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current .fa-angle-down {
  color: var(--v2-header-muted) !important;
}

html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-header-icon-button,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger {
  color: var(--v2-header-ink) !important;
  background: rgba(255, 255, 255, .84) !important;
  box-shadow: inset 0 0 0 1px rgba(203, 213, 225, .86), 0 10px 24px rgba(15, 23, 42, .06) !important;
}

html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button:hover,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-header-icon-button:hover,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:hover .v2-language-current,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:focus-within .v2-language-current,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select.is-open .v2-language-current {
  color: var(--v2-header-ink) !important;
  background: rgba(255, 255, 255, .94) !important;
  box-shadow: inset 0 0 0 1px rgba(147, 197, 253, .72), 0 12px 26px rgba(37, 99, 235, .10) !important;
}

html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-copy span,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current .lang-txt {
  color: var(--v2-header-ink) !important;
}

html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-avatar,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--light,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--dark {
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--v2-header-primary), var(--v2-header-primary-dark)) !important;
  box-shadow: 0 8px 18px rgba(37, 99, 235, .24) !important;
}

html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--dark,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--light {
  color: var(--v2-header-muted) !important;
  background: transparent !important;
  box-shadow: none !important;
}

html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang:hover,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang.selected,
html:not(.dark):not([data-bs-theme="dark"]) body:not(.dark-only):not([data-bs-theme="dark"]) .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown a:hover {
  color: var(--v2-header-primary) !important;
}

@media (max-width: 575.98px) {
  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell {
    padding: 7px 10px !important;
    gap: 7px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand {
    gap: 7px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-heading {
    font-size: 16px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
  .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-header-icon-button,
  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current {
    width: 38px !important;
    min-width: 38px !important;
    height: 38px !important;
    border-radius: 13px !important;
    padding-left: 9px !important;
    padding-right: 9px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle {
    width: 126px !important;
    min-width: 126px !important;
    height: 38px !important;
    padding: 3px 5px !important;
    gap: 4px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option {
    width: 56px !important;
    min-width: 56px !important;
    height: 30px !important;
    padding: 0 6px !important;
    gap: 4px !important;
    font-size: 10px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option span {
    display: inline !important;
  }
  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current .lang-txt { display: none; }
  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current .fa-angle-down { display: none; }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .nav-menus { gap: 5px !important; }
  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu { right: -12px; }
}

@media (max-width: 380px) {
  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell {
    padding-left: 8px !important;
    padding-right: 8px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-heading {
    font-size: 15px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current {
    width: 36px !important;
    min-width: 36px !important;
    height: 36px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle {
    width: 118px !important;
    min-width: 118px !important;
    height: 36px !important;
  }

  .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option {
    width: 52px !important;
    min-width: 52px !important;
    height: 28px !important;
    padding: 0 5px !important;
    font-size: 9px !important;
  }
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header],
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header],
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] {
  --v2-header-primary: #3b82f6;
  --v2-header-primary-dark: #2563eb;
  --v2-header-blue: #60a5fa;
  --v2-header-ink: #f8fafc;
  --v2-header-muted: #a8b6c9;
  --v2-header-line: rgba(51, 65, 85, .86);
  --v2-header-glass: rgba(15, 23, 42, .72);
  background: linear-gradient(180deg, #111827 0%, #0f172a 100%) !important;
  border-bottom-color: var(--v2-header-line) !important;
  box-shadow: 0 18px 42px rgba(0, 0, 0, .34) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-header-icon-button,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-header-icon-button,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-header-icon-button,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger {
  color: #f8fafc !important;
  background: rgba(15, 23, 42, .72) !important;
  box-shadow: inset 0 0 0 1px rgba(148, 163, 184, .18), 0 14px 28px rgba(0, 0, 0, .22);
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover {
  color: #f8fafc !important;
  background: rgba(15, 23, 42, .72) !important;
  box-shadow: inset 0 0 0 1px rgba(148, 163, 184, .18), 0 14px 28px rgba(0, 0, 0, .22);
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--dark,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--light,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--dark,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--light,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--dark,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--light {
  color: var(--v2-header-muted) !important;
  background: transparent !important;
  box-shadow: none !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--light,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--dark,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--light,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--dark,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--light,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--dark {
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--v2-header-primary), var(--v2-header-primary-dark)) !important;
  box-shadow: 0 8px 18px rgba(37, 99, 235, .28) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown {
  background: rgba(15, 23, 42, .98) !important;
  border-color: rgba(51, 65, 85, .95) !important;
  box-shadow: 0 24px 52px rgba(0, 0, 0, .42);
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown a,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown a,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown a {
  color: #e5edf7 !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-code,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-code,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-code {
  color: #9cc8ff;
  background: rgba(59, 130, 246, .16);
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang:hover,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang.selected,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown a:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang.selected,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown a:hover,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang:hover,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang.selected,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-dropdown a:hover {
  color: #93c5fd !important;
  background: rgba(59, 130, 246, .16) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang:hover .v2-language-code,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang.selected .v2-language-code,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang:hover .v2-language-code,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang.selected .v2-language-code,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang:hover .v2-language-code,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu .lang.selected .v2-language-code {
  color: #93c5fd;
  background: rgba(59, 130, 246, .18);
}

/* Final V2 layout polish for consistent light and dark shells */
:root {
  --v2-layout-sidebar-width: 260px;
  --v2-layout-sidebar-collapsed: 86px;
  --v2-layout-header-height: 68px;
  --v2-shell-primary: #2563eb;
  --v2-shell-primary-dark: #1d4ed8;
  --v2-shell-success: #2563eb;
  --v2-shell-shadow: 0 18px 42px rgba(15, 23, 42, .10);
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] {
  width: var(--v2-layout-sidebar-width) !important;
  background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%) !important;
  border-right: 1px solid rgba(148, 163, 184, .24) !important;
  box-shadow: 16px 0 36px rgba(15, 23, 42, .06) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper {
  height: var(--v2-layout-header-height) !important;
  min-height: var(--v2-layout-header-height) !important;
  padding: 0 18px !important;
  background: rgba(255, 255, 255, .82) !important;
  border-bottom: 1px solid rgba(148, 163, 184, .20) !important;
  display: flex !important;
  align-items: center !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper a {
  display: inline-flex !important;
  align-items: center !important;
  min-width: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper img {
  width: auto !important;
  max-width: 132px !important;
  max-height: 44px !important;
  object-fit: contain !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper {
  justify-content: center !important;
  padding: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar]:not(.close_icon) .logo-icon-wrapper {
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper img {
  width: 58px !important;
  max-width: 58px !important;
  max-height: 44px !important;
  object-fit: contain !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main {
  height: calc(100vh - var(--v2-layout-header-height)) !important;
  padding: 12px 8px 18px !important;
  overflow: hidden !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links {
  height: 100% !important;
  max-height: 100% !important;
  padding: 6px 4px 24px !important;
  overflow-y: auto !important;
  scrollbar-width: thin;
  scrollbar-color: rgba(37, 99, 235, .28) transparent;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links::-webkit-scrollbar {
  width: 6px;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links::-webkit-scrollbar-track {
  background: transparent;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links::-webkit-scrollbar-thumb {
  background: rgba(37, 99, 235, .24);
  border-radius: 999px;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list {
  margin: 4px 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link {
  min-height: 46px !important;
  height: 46px !important;
  padding: 0 12px !important;
  border-radius: 12px !important;
  display: flex !important;
  align-items: center !important;
  gap: 10px !important;
  color: #334155 !important;
  border: 1px solid transparent !important;
  background: transparent !important;
  transition: background .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame {
  width: 34px !important;
  height: 34px !important;
  min-width: 34px !important;
  border-radius: 11px !important;
  display: grid !important;
  place-items: center !important;
  color: #2563eb !important;
  background: rgba(37, 99, 235, .10) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame i,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame svg {
  margin: 0 !important;
  width: 16px !important;
  min-width: 16px !important;
  height: 16px !important;
  color: currentColor !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-label {
  flex: 1 1 auto !important;
  min-width: 0 !important;
  color: inherit !important;
  font-size: 13px !important;
  font-weight: 700 !important;
  line-height: 1.2 !important;
  letter-spacing: 0 !important;
  white-space: normal !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge {
  height: 22px !important;
  min-width: 34px !important;
  padding: 0 8px !important;
  border-radius: 999px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-size: 11px !important;
  line-height: 1 !important;
  letter-spacing: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link:hover {
  color: #1d4ed8 !important;
  background: rgba(37, 99, 235, .08) !important;
  border-color: rgba(37, 99, 235, .18) !important;
  transform: translateX(2px) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link.active,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > .sidebar-link {
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--v2-shell-primary) 0%, var(--v2-shell-primary-dark) 100%) !important;
  border-color: rgba(255, 255, 255, .24) !important;
  box-shadow: 0 14px 28px rgba(37, 99, 235, .26) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link.active .v2-sidebar-icon-frame,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > .sidebar-link .v2-sidebar-icon-frame {
  color: #ffffff !important;
  background: rgba(255, 255, 255, .18) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link.active *,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > .sidebar-link * {
  color: inherit !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card {
  border-radius: 16px !important;
  border: 1px solid rgba(148, 163, 184, .20) !important;
  box-shadow: 0 14px 30px rgba(15, 23, 42, .07) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon {
  width: var(--v2-layout-sidebar-collapsed) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon .logo-wrapper {
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon .logo-icon-wrapper {
  display: flex !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover {
  width: var(--v2-layout-sidebar-width) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-wrapper {
  display: flex !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-icon-wrapper {
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-balance-card,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-balance-card {
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon .sidebar-link {
  justify-content: center !important;
  padding: 0 10px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-label,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-menu-badge,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-section-title,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-user-card {
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .sidebar-link {
  justify-content: flex-start !important;
  padding: 0 12px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-label,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-menu-badge,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-section-title,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-user-card {
  display: flex !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .page-body {
  margin-left: var(--v2-layout-sidebar-width) !important;
  margin-top: var(--v2-layout-header-height) !important;
  min-height: calc(100vh - var(--v2-layout-header-height)) !important;
  padding: 0 !important;
  background: linear-gradient(180deg, #f4f8fb 0%, #eef6ff 100%) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon ~ .page-body {
  margin-left: var(--v2-layout-sidebar-collapsed) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .page-title {
  margin: 0 !important;
  padding: 12px 24px !important;
  min-height: 54px !important;
  background: rgba(255, 255, 255, .64) !important;
  border-bottom: 1px solid rgba(148, 163, 184, .18) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar],
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar],
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] {
  background: linear-gradient(180deg, #111827 0%, #0f172a 100%) !important;
  border-right-color: rgba(148, 163, 184, .18) !important;
  box-shadow: 18px 0 38px rgba(0, 0, 0, .22) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper {
  background: rgba(15, 23, 42, .86) !important;
  border-bottom-color: rgba(148, 163, 184, .16) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link {
  color: #cbd5e1 !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame {
  color: #93c5fd !important;
  background: rgba(37, 99, 235, .16) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link:hover,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link:hover {
  color: #ffffff !important;
  background: rgba(30, 41, 59, .88) !important;
  border-color: rgba(148, 163, 184, .22) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card {
  background: rgba(15, 23, 42, .62) !important;
  border-color: rgba(148, 163, 184, .18) !important;
  box-shadow: none !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .page-body,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .page-body,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .page-body {
  background: linear-gradient(180deg, #0b1220 0%, #111827 100%) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .page-title,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .page-title,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .page-title {
  background: rgba(17, 24, 39, .86) !important;
  border-bottom-color: rgba(148, 163, 184, .14) !important;
}

/* Final sidebar collapsed/peek corrections */
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] {
  transition: width .18s ease, box-shadow .18s ease, background .18s ease !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card {
  display: none !important;
  height: 0 !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  border: 0 !important;
  box-shadow: none !important;
  overflow: hidden !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title {
  min-height: 20px !important;
  margin: 10px 0 6px !important;
  padding: 0 12px !important;
  display: flex !important;
  align-items: center !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links {
  padding-top: 8px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-track.simplebar-vertical {
  width: 5px !important;
  right: 2px !important;
  opacity: .36 !important;
  transition: opacity .16s ease, width .16s ease !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-scrollbar::before {
  background: rgba(37, 99, 235, .30) !important;
  border-radius: 999px !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-scrollbar::before,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-scrollbar::before,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-scrollbar::before {
  background: rgba(147, 197, 253, .26) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon {
  overflow: visible !important;
  box-shadow: 10px 0 28px rgba(15, 23, 42, .08) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .simplebar-track.simplebar-vertical {
  opacity: 0 !important;
  width: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) #sidebar-menu,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .simplebar-content-wrapper,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .sidebar-links {
  scrollbar-width: none !important;
  -ms-overflow-style: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) #sidebar-menu::-webkit-scrollbar,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .simplebar-content-wrapper::-webkit-scrollbar,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .sidebar-links::-webkit-scrollbar {
  width: 0 !important;
  height: 0 !important;
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon .logo-icon-wrapper {
  width: var(--v2-layout-sidebar-collapsed) !important;
  justify-content: center !important;
  align-items: center !important;
  padding: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon .logo-icon-wrapper img {
  width: 64px !important;
  max-width: 64px !important;
  max-height: 42px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded {
  width: var(--v2-layout-sidebar-width) !important;
  z-index: 80 !important;
  box-shadow: 24px 0 54px rgba(15, 23, 42, .16) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-wrapper,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .logo-wrapper {
  display: flex !important;
  max-height: var(--v2-layout-header-height) !important;
  opacity: 1 !important;
  pointer-events: auto !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-wrapper a,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .logo-wrapper a {
  justify-content: flex-start !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-wrapper img,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .logo-wrapper img {
  width: auto !important;
  max-width: 132px !important;
  max-height: 44px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-icon-wrapper,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .logo-icon-wrapper {
  display: none !important;
  max-height: 0 !important;
  opacity: 0 !important;
  pointer-events: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .sidebar-link,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-link {
  justify-content: flex-start !important;
  gap: 10px !important;
  padding: 0 12px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-icon-frame,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-icon-frame {
  width: 34px !important;
  min-width: 34px !important;
  max-width: 34px !important;
  flex: 0 0 34px !important;
  opacity: 1 !important;
  pointer-events: auto !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-label,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-section-title,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-label,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-section-title {
  display: flex !important;
  width: auto !important;
  min-width: 0 !important;
  max-width: none !important;
  flex: 1 1 auto !important;
  opacity: 1 !important;
  pointer-events: auto !important;
  overflow: visible !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-menu-badge,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .according-menu,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-menu-badge,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .according-menu {
  display: inline-flex !important;
  width: auto !important;
  min-width: 34px !important;
  max-width: none !important;
  flex: 0 0 auto !important;
  margin: 0 !important;
  padding: 0 8px !important;
  opacity: 1 !important;
  pointer-events: auto !important;
  overflow: visible !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-user-card,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-balance-card,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-user-card,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-balance-card {
  display: none !important;
}

@media (min-width: 992px) {
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded {
    width: var(--v2-layout-sidebar-width) !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .sidebar-list > a.sidebar-link,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-list > a.sidebar-link {
    width: 100% !important;
    max-width: calc(var(--v2-layout-sidebar-width) - 8px) !important;
    justify-content: flex-start !important;
    gap: 10px !important;
    padding-left: 12px !important;
    padding-right: 12px !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .sidebar-main,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover #sidebar-menu,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .simplebar-wrapper,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .simplebar-mask,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .simplebar-offset,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .simplebar-content-wrapper,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .simplebar-content,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .sidebar-links,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .sidebar-list,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-main,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded #sidebar-menu,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .simplebar-wrapper,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .simplebar-mask,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .simplebar-offset,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .simplebar-content-wrapper,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .simplebar-content,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-links,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-list {
    width: 100% !important;
    max-width: none !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-label,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .according-menu,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-label,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .according-menu {
    width: auto !important;
    min-width: 0 !important;
    max-width: none !important;
    flex: 1 1 auto !important;
    margin: 0 !important;
    padding: 0 !important;
    opacity: 1 !important;
    pointer-events: auto !important;
    overflow: visible !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-menu-badge,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-menu-badge {
    width: auto !important;
    min-width: 34px !important;
    max-width: none !important;
    flex: 0 0 auto !important;
    margin: 0 !important;
    padding: 0 8px !important;
    opacity: 1 !important;
    pointer-events: auto !important;
    overflow: visible !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-icon-frame,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-icon-frame {
    width: 34px !important;
    min-width: 34px !important;
    max-width: 34px !important;
    flex: 0 0 34px !important;
    opacity: 1 !important;
    pointer-events: auto !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-wrapper,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .logo-wrapper {
    display: flex !important;
    opacity: 1 !important;
    max-height: var(--v2-layout-header-height) !important;
    pointer-events: auto !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-wrapper a,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .logo-wrapper a {
    justify-content: flex-start !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-wrapper img,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .logo-wrapper img {
    max-width: 132px !important;
    max-height: 44px !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .logo-icon-wrapper,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .logo-icon-wrapper {
    display: none !important;
    opacity: 0 !important;
    max-height: 0 !important;
    pointer-events: none !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-user-card,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-balance-card,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-user-card,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-balance-card {
    display: none !important;
    opacity: 0 !important;
    pointer-events: none !important;
  }
}

/* V2 breadcrumb shell */
.page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] {
  position: relative !important;
  isolation: isolate;
  overflow: hidden;
  margin: 12px 0 12px !important;
  padding: 10px 18px !important;
  min-height: 52px !important;
  display: block !important;
  background: #ffffff !important;
  border: 1px solid rgba(148, 163, 184, .20) !important;
  border-radius: 14px !important;
  box-shadow: 0 8px 22px rgba(15, 23, 42, .04) !important;
  backdrop-filter: blur(14px);
}

.page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb]::before {
  content: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb]::after {
  content: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] .row {
  position: relative;
  z-index: 1;
  width: 100% !important;
  min-height: 34px !important;
  align-items: center !important;
  margin: 0 !important;
  row-gap: 10px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] .v2-breadcrumb-title-col,
.page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] .v2-breadcrumb-trail-col {
  display: flex !important;
  align-items: center !important;
  min-height: 34px !important;
  padding-top: 0 !important;
  padding-bottom: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] .v2-breadcrumb-trail-col {
  justify-content: flex-end !important;
}

.v2-page-title-main {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.v2-page-title-main::before {
  content: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] .v2-page-title-heading {
  margin: 0 !important;
  color: #111827 !important;
  font-size: 22px !important;
  font-weight: 800 !important;
  line-height: 1.15 !important;
  letter-spacing: 0 !important;
  overflow-wrap: anywhere;
}

.v2-breadcrumb-nav {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  min-width: 0;
  width: 100%;
}

.v2-breadcrumb {
  display: flex !important;
  align-items: center !important;
  justify-content: flex-end !important;
  flex-wrap: wrap !important;
  gap: 6px !important;
  width: auto;
  max-width: 100%;
  min-height: 36px;
  margin: 0 !important;
  padding: 4px !important;
  border-radius: 13px !important;
  background: #f8fafc !important;
  border: 1px solid rgba(203, 213, 225, .85) !important;
  box-shadow: none !important;
}

.v2-breadcrumb .breadcrumb-item,
.v2-breadcrumb .v2-breadcrumb-item {
  display: inline-flex !important;
  align-items: center !important;
  min-width: 0;
  max-width: 240px;
  padding-left: 0 !important;
  color: #64748b !important;
  font-size: 13px !important;
  font-weight: 700 !important;
  line-height: 1 !important;
}

.v2-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
  content: "/" !important;
  padding: 0 1px 0 0 !important;
  color: #94a3b8 !important;
  line-height: 1 !important;
  font-size: 13px !important;
  font-weight: 700 !important;
}

.v2-breadcrumb-link,
.v2-breadcrumb-current {
  display: inline-flex !important;
  align-items: center !important;
  gap: 7px !important;
  min-width: 0;
  max-width: 100%;
  min-height: 28px;
  padding: 0 10px !important;
  border-radius: 10px !important;
  color: #475569 !important;
  font-weight: 700 !important;
  letter-spacing: 0;
  text-decoration: none !important;
  white-space: nowrap;
  overflow: hidden;
  transition: background-color .18s ease, color .18s ease, box-shadow .18s ease;
}

.v2-breadcrumb-link span,
.v2-breadcrumb-current span {
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
}

.v2-breadcrumb-icon {
  width: 14px;
  min-width: 14px;
  color: currentColor !important;
  font-size: 13px;
  line-height: 1;
}

.v2-breadcrumb-link:hover,
.v2-breadcrumb-link:focus {
  color: #1d4ed8 !important;
  background: #eff6ff !important;
  box-shadow: none !important;
  outline: none !important;
}

.v2-breadcrumb .breadcrumb-item.active .v2-breadcrumb-current,
.v2-breadcrumb .v2-breadcrumb-item.active .v2-breadcrumb-current {
  color: #0f172a !important;
  background: #ffffff !important;
  box-shadow: 0 4px 12px rgba(15, 23, 42, .06) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb],
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb],
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] {
  background: rgba(17, 24, 39, .92) !important;
  border-color: rgba(148, 163, 184, .16) !important;
  box-shadow: 0 12px 30px rgba(0, 0, 0, .18) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] .v2-page-title-heading,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] .v2-page-title-heading,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] .v2-page-title-heading {
  color: #f8fafc !important;
}

body.dark-only .v2-breadcrumb,
[data-bs-theme="dark"] .v2-breadcrumb,
html.dark .v2-breadcrumb {
  background: rgba(15, 23, 42, .72) !important;
  border-color: rgba(96, 165, 250, .24) !important;
  box-shadow: none !important;
}

body.dark-only .v2-breadcrumb .breadcrumb-item,
body.dark-only .v2-breadcrumb .v2-breadcrumb-item,
body.dark-only .v2-breadcrumb-link,
body.dark-only .v2-breadcrumb-current,
[data-bs-theme="dark"] .v2-breadcrumb .breadcrumb-item,
[data-bs-theme="dark"] .v2-breadcrumb .v2-breadcrumb-item,
[data-bs-theme="dark"] .v2-breadcrumb-link,
[data-bs-theme="dark"] .v2-breadcrumb-current,
html.dark .v2-breadcrumb .breadcrumb-item,
html.dark .v2-breadcrumb .v2-breadcrumb-item,
html.dark .v2-breadcrumb-link,
html.dark .v2-breadcrumb-current {
  color: #cbd5e1 !important;
}

body.dark-only .v2-breadcrumb .breadcrumb-item + .breadcrumb-item::before,
[data-bs-theme="dark"] .v2-breadcrumb .breadcrumb-item + .breadcrumb-item::before,
html.dark .v2-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
  color: #64748b !important;
}

body.dark-only .v2-breadcrumb-link:hover,
body.dark-only .v2-breadcrumb-link:focus,
[data-bs-theme="dark"] .v2-breadcrumb-link:hover,
[data-bs-theme="dark"] .v2-breadcrumb-link:focus,
html.dark .v2-breadcrumb-link:hover,
html.dark .v2-breadcrumb-link:focus {
  color: #ffffff !important;
  background: rgba(37, 99, 235, .22) !important;
}

body.dark-only .v2-breadcrumb .breadcrumb-item.active .v2-breadcrumb-current,
body.dark-only .v2-breadcrumb .v2-breadcrumb-item.active .v2-breadcrumb-current,
[data-bs-theme="dark"] .v2-breadcrumb .breadcrumb-item.active .v2-breadcrumb-current,
[data-bs-theme="dark"] .v2-breadcrumb .v2-breadcrumb-item.active .v2-breadcrumb-current,
html.dark .v2-breadcrumb .breadcrumb-item.active .v2-breadcrumb-current,
html.dark .v2-breadcrumb .v2-breadcrumb-item.active .v2-breadcrumb-current {
  color: #ffffff !important;
  background: rgba(37, 99, 235, .28) !important;
  box-shadow: none !important;
}

@media (max-width: 767.98px) {
  .page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] {
    margin: 10px 0 10px !important;
    padding: 10px 12px !important;
    border-radius: 12px !important;
    min-height: 0 !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] .v2-breadcrumb-title-col,
  .page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] .v2-breadcrumb-trail-col {
    justify-content: flex-start !important;
  }

  .v2-breadcrumb-nav,
  .v2-breadcrumb {
    justify-content: flex-start !important;
  }

  .v2-breadcrumb {
    width: auto;
    max-width: 100%;
    border-radius: 12px !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper .page-title[data-v2-breadcrumb] .v2-page-title-heading {
    font-size: 20px !important;
  }

  .v2-breadcrumb .breadcrumb-item,
  .v2-breadcrumb .v2-breadcrumb-item {
    max-width: 100%;
  }

  .v2-breadcrumb-link,
  .v2-breadcrumb-current {
    max-width: calc(100vw - 88px);
  }
}

@media (max-width: 420px) {
  .v2-breadcrumb {
    gap: 4px !important;
    padding: 4px !important;
  }

  .v2-breadcrumb-link,
  .v2-breadcrumb-current {
    min-height: 30px;
    padding: 0 9px !important;
    font-size: 12px !important;
  }
}

/* V2 sidebar final spacing correction */
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] {
  overflow: hidden !important;
  border-right: 1px solid rgba(148, 163, 184, .22) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper {
  min-height: 64px !important;
  padding: 0 22px !important;
  border-bottom: 1px solid rgba(148, 163, 184, .16) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] #sidebar-menu,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links {
  overflow-x: hidden !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] #sidebar-menu,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-content {
  padding-top: 0 !important;
  margin-top: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links {
  padding: 10px 10px 28px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main-title:not(.v2-sidebar-user-card) {
  display: none !important;
  margin: 0 !important;
  padding: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title {
  display: none !important;
  margin: 0 !important;
  padding: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card {
  width: 100% !important;
  margin: 0 0 10px !important;
  padding: 10px 12px !important;
  display: flex !important;
  align-items: center !important;
  gap: 10px !important;
  text-align: left !important;
  border-radius: 16px !important;
  background: #ffffff !important;
  border: 1px solid rgba(203, 213, 225, .72) !important;
  box-shadow: 0 8px 20px rgba(15, 23, 42, .04) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-avatar-wrap {
  width: 38px !important;
  height: 38px !important;
  min-width: 38px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  position: relative !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-avatar {
  width: 38px !important;
  height: 38px !important;
  object-fit: cover !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-online-dot {
  width: 9px !important;
  height: 9px !important;
  right: 1px !important;
  bottom: 1px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-copy {
  flex: 1 1 auto !important;
  min-width: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-name {
  margin: 0 !important;
  color: #0f172a !important;
  font-size: 14px !important;
  font-weight: 800 !important;
  line-height: 1.15 !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  white-space: nowrap !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-group {
  display: block !important;
  margin-top: 2px !important;
  color: #64748b !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  line-height: 1.2 !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  white-space: nowrap !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-profile-link {
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card {
  width: 100% !important;
  margin: -2px 0 10px !important;
  padding: 8px 12px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 10px !important;
  border-radius: 14px !important;
  background: #f8fafc !important;
  border: 1px solid rgba(203, 213, 225, .65) !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card .balance-label {
  color: #64748b !important;
  font-size: 11px !important;
  font-weight: 800 !important;
  text-transform: uppercase !important;
  letter-spacing: .08em !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card .balance-display,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card #tamaBalance {
  color: #0f172a !important;
  font-size: 13px !important;
  font-weight: 900 !important;
  line-height: 1.1 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list {
  width: 100% !important;
  margin: 0 0 6px !important;
  padding: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list::before,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list::after,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link::before,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link::after {
  display: none !important;
  content: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link {
  width: 100% !important;
  max-width: 100% !important;
  min-height: 48px !important;
  margin: 0 !important;
  padding: 0 10px !important;
  display: flex !important;
  align-items: center !important;
  gap: 10px !important;
  border-radius: 15px !important;
  box-sizing: border-box !important;
  overflow: hidden !important;
  color: #334155 !important;
  background: rgba(255, 255, 255, .34) !important;
  border: 1px solid transparent !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active {
  position: relative !important;
  color: #0f172a !important;
  background: #ffffff !important;
  border-color: rgba(37, 99, 235, .18) !important;
  box-shadow: 0 10px 24px rgba(15, 23, 42, .08) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active::after {
  content: "" !important;
  display: block !important;
  position: absolute !important;
  left: 0 !important;
  top: 10px !important;
  bottom: 10px !important;
  width: 4px !important;
  border-radius: 0 999px 999px 0 !important;
  background: #2f5bea !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame {
  width: 36px !important;
  min-width: 36px !important;
  height: 36px !important;
  border-radius: 12px !important;
  color: #2563eb !important;
  background: #e8f1ff !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame {
  color: #ffffff !important;
  background: #2f5bea !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-label {
  flex: 1 1 auto !important;
  min-width: 0 !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  white-space: nowrap !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .according-menu {
  width: 14px !important;
  min-width: 14px !important;
  height: 16px !important;
  margin-left: 2px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: #94a3b8 !important;
  background: transparent !important;
  border: 0 !important;
  font-size: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .according-menu i {
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .according-menu::before {
  content: "" !important;
  width: 7px;
  height: 7px;
  border-right: 2px solid currentColor;
  border-bottom: 2px solid currentColor;
  transform: rotate(-45deg);
  transition: transform .18s ease;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link[aria-expanded="true"] .according-menu::before {
  transform: rotate(45deg);
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .according-menu {
  color: #64748b !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge {
  margin-left: auto !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count {
  min-width: 24px !important;
  height: 24px !important;
  margin-left: auto !important;
  padding: 0 7px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 999px !important;
  color: #475569 !important;
  background: #eef4ff !important;
  border: 1px solid rgba(148, 163, 184, .34) !important;
  font-size: 12px !important;
  font-weight: 800 !important;
  line-height: 1 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-track.simplebar-vertical {
  width: 4px !important;
  right: 5px !important;
  top: 14px !important;
  bottom: 14px !important;
  border-radius: 999px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-scrollbar::before {
  left: 0 !important;
  right: 0 !important;
  top: 0 !important;
  bottom: 0 !important;
  border-radius: 999px !important;
  background: rgba(100, 116, 139, .28) !important;
  opacity: 1 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu {
  width: 100% !important;
  max-width: 100% !important;
  margin: 4px 0 10px !important;
  padding: 2px 0 2px 8px !important;
  box-sizing: border-box !important;
  overflow: visible !important;
  border-radius: 0 !important;
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu::before,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu::after,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li::before,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li::after,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a::before,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a::after {
  display: none !important;
  content: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li {
  width: 100% !important;
  margin: 0 !important;
  padding: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li + li {
  margin-top: 2px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a {
  width: 100% !important;
  max-width: 100% !important;
  min-height: 38px !important;
  padding: 6px 8px !important;
  display: flex !important;
  align-items: center !important;
  gap: 9px !important;
  border-radius: 12px !important;
  color: #64748b !important;
  font-size: 13px !important;
  font-weight: 750 !important;
  line-height: 1.2 !important;
  text-decoration: none !important;
  box-sizing: border-box !important;
  overflow: visible !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a > span:not(.v2-sidebar-child-icon-frame):not(.v2-sidebar-menu-badge) {
  flex: 1 1 auto !important;
  min-width: 0 !important;
  overflow: visible !important;
  text-overflow: clip !important;
  white-space: normal !important;
  word-break: normal !important;
  overflow-wrap: anywhere !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame {
  width: 28px !important;
  min-width: 28px !important;
  height: 28px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 9px !important;
  color: #2563eb !important;
  background: #e8f1ff !important;
  font-size: 11px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame i {
  color: currentColor !important;
  font-size: 11px !important;
  line-height: 1 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active {
  color: #0f172a !important;
  background: #ffffff !important;
  box-shadow: 0 8px 18px rgba(15, 23, 42, .055) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active .v2-sidebar-child-icon-frame {
  color: #ffffff !important;
  background: #2f5bea !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu {
  background: rgba(15, 23, 42, .62) !important;
  border-color: rgba(148, 163, 184, .16) !important;
}

/* V2 sidebar compact redesign override */
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links {
  padding: 10px 9px 28px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list {
  margin-bottom: 5px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link {
  position: relative !important;
  min-height: 44px !important;
  padding: 6px 9px !important;
  gap: 9px !important;
  border-radius: 13px !important;
  color: #26364d !important;
  background: transparent !important;
  border: 1px solid transparent !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover {
  background: rgba(255, 255, 255, .62) !important;
  border-color: rgba(203, 213, 225, .55) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active {
  color: #071326 !important;
  background: #ffffff !important;
  border-color: rgba(47, 91, 234, .26) !important;
  box-shadow: 0 8px 20px rgba(15, 23, 42, .07) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active::after {
  content: "" !important;
  display: block !important;
  position: absolute !important;
  left: 0 !important;
  top: 9px !important;
  bottom: 9px !important;
  width: 3px !important;
  border-radius: 0 999px 999px 0 !important;
  background: #2f5bea !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame {
  width: 32px !important;
  min-width: 32px !important;
  height: 32px !important;
  border-radius: 10px !important;
  color: #1f6db3 !important;
  background: #e8f1ff !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame {
  color: #ffffff !important;
  background: #2f5bea !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame i {
  font-size: 15px !important;
  line-height: 1 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-label {
  color: inherit !important;
  font-size: 14px !important;
  font-weight: 800 !important;
  line-height: 1.15 !important;
  letter-spacing: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count {
  min-width: 22px !important;
  height: 22px !important;
  margin-left: auto !important;
  padding: 0 7px !important;
  color: #475569 !important;
  background: #eef4ff !important;
  border: 1px solid rgba(148, 163, 184, .36) !important;
  font-size: 11px !important;
  font-weight: 800 !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .according-menu {
  position: static !important;
  inset: auto !important;
  transform: none !important;
  width: 12px !important;
  min-width: 12px !important;
  height: 18px !important;
  margin: 0 1px 0 0 !important;
  padding: 0 !important;
  color: #94a3b8 !important;
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .according-menu i {
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .according-menu::before {
  width: 6px !important;
  height: 6px !important;
  border-right: 2px solid currentColor !important;
  border-bottom: 2px solid currentColor !important;
  transform: rotate(-45deg) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link[aria-expanded="true"] .according-menu::before {
  transform: rotate(45deg) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu {
  margin: 3px 0 8px !important;
  padding: 0 0 0 41px !important;
  overflow: visible !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li {
  margin: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li + li {
  margin-top: 2px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a {
  min-height: 32px !important;
  padding: 5px 8px !important;
  gap: 8px !important;
  border-radius: 10px !important;
  color: #687386 !important;
  background: transparent !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame {
  width: 24px !important;
  min-width: 24px !important;
  height: 24px !important;
  border-radius: 8px !important;
  color: #2563eb !important;
  background: #e8f1ff !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame i {
  font-size: 10px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a > span:not(.v2-sidebar-child-icon-frame):not(.v2-sidebar-menu-badge) {
  color: inherit !important;
  font-size: 13px !important;
  font-weight: 750 !important;
  line-height: 1.18 !important;
  white-space: normal !important;
  overflow: visible !important;
  text-overflow: clip !important;
  overflow-wrap: anywhere !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active {
  color: #0f172a !important;
  background: rgba(255, 255, 255, .72) !important;
  box-shadow: none !important;
}

/* V2 submenu hierarchy correction */
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu {
  position: relative !important;
  width: 100% !important;
  max-width: 100% !important;
  margin: 4px 0 9px !important;
  padding: 3px 0 3px 46px !important;
  overflow: visible !important;
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu::before {
  content: "" !important;
  display: block !important;
  position: absolute !important;
  left: 25px !important;
  top: 2px !important;
  bottom: 6px !important;
  width: 1px !important;
  border-radius: 999px !important;
  background: rgba(148, 163, 184, .42) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li {
  position: relative !important;
  width: 100% !important;
  margin: 0 !important;
  padding: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li + li {
  margin-top: 3px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li::before {
  content: "" !important;
  display: block !important;
  position: absolute !important;
  left: -21px !important;
  top: 17px !important;
  width: 13px !important;
  height: 1px !important;
  background: rgba(148, 163, 184, .42) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li::after {
  content: none !important;
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a {
  width: 100% !important;
  min-height: 34px !important;
  padding: 5px 8px !important;
  display: grid !important;
  grid-template-columns: 22px minmax(0, 1fr) auto !important;
  column-gap: 8px !important;
  align-items: center !important;
  border-radius: 10px !important;
  color: #667085 !important;
  background: transparent !important;
  border: 1px solid transparent !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a::before,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a::after {
  content: none !important;
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame {
  width: 22px !important;
  min-width: 22px !important;
  height: 22px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 7px !important;
  color: #2563eb !important;
  background: #eaf3ff !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame i {
  color: currentColor !important;
  font-size: 10px !important;
  line-height: 1 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a > span:not(.v2-sidebar-child-icon-frame):not(.v2-sidebar-menu-badge) {
  min-width: 0 !important;
  color: inherit !important;
  font-size: 12.5px !important;
  font-weight: 750 !important;
  line-height: 1.22 !important;
  white-space: normal !important;
  overflow: visible !important;
  text-overflow: clip !important;
  overflow-wrap: break-word !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active {
  color: #0f172a !important;
  background: #ffffff !important;
  border-color: rgba(203, 213, 225, .70) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active .v2-sidebar-child-icon-frame {
  color: #ffffff !important;
  background: #2f5bea !important;
}

/* V2 submenu final correction: compact readable children */
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu {
  margin: 3px 0 8px !important;
  padding: 2px 4px 4px 42px !important;
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu::before,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu::after,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li::before,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li::after {
  content: none !important;
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li {
  margin: 0 !important;
  padding: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li + li {
  margin-top: 3px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a {
  min-height: 30px !important;
  padding: 4px 6px !important;
  display: flex !important;
  align-items: center !important;
  gap: 7px !important;
  border-radius: 9px !important;
  color: #667085 !important;
  background: transparent !important;
  border: 1px solid transparent !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame {
  width: 20px !important;
  min-width: 20px !important;
  height: 20px !important;
  border-radius: 7px !important;
  color: #2563eb !important;
  background: rgba(232, 241, 255, .9) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame i {
  font-size: 9.5px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a > span:not(.v2-sidebar-child-icon-frame):not(.v2-sidebar-menu-badge) {
  flex: 1 1 auto !important;
  min-width: 0 !important;
  color: inherit !important;
  font-size: 12px !important;
  font-weight: 760 !important;
  line-height: 1.15 !important;
  letter-spacing: -.01em !important;
  white-space: nowrap !important;
  overflow: visible !important;
  text-overflow: clip !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active {
  color: #0f172a !important;
  background: rgba(255, 255, 255, .78) !important;
  border-color: rgba(203, 213, 225, .65) !important;
}
/* V2 sidebar premium redesign final layer */
.page-wrapper.compact-wrapper {
  --v2-layout-sidebar-width: 282px;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] {
  width: var(--v2-layout-sidebar-width) !important;
  background:
    linear-gradient(180deg, rgba(248, 251, 255, .98), rgba(235, 244, 255, .94)) !important;
  border-right: 1px solid rgba(148, 163, 184, .24) !important;
  box-shadow: 10px 0 24px rgba(15, 23, 42, .05) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper {
  min-height: 72px !important;
  padding: 0 24px !important;
  background: rgba(255, 255, 255, .72) !important;
  border-bottom: 1px solid rgba(148, 163, 184, .18) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper img {
  max-width: 148px !important;
  max-height: 48px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links {
  padding: 14px 12px 32px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card {
  margin: 0 0 12px !important;
  padding: 11px 12px !important;
  display: grid !important;
  grid-template-columns: 42px minmax(0, 1fr) !important;
  column-gap: 11px !important;
  align-items: center !important;
  border-radius: 18px !important;
  background: rgba(255, 255, 255, .84) !important;
  border: 1px solid rgba(203, 213, 225, .76) !important;
  box-shadow: 0 12px 26px rgba(15, 23, 42, .06) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-avatar-wrap,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-avatar {
  width: 42px !important;
  height: 42px !important;
  min-width: 42px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-copy {
  min-width: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-name {
  margin: 0 !important;
  color: #0f172a !important;
  font-size: 14px !important;
  font-weight: 850 !important;
  line-height: 1.15 !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-group {
  margin-top: 3px !important;
  color: #64748b !important;
  font-size: 11px !important;
  font-weight: 750 !important;
  line-height: 1.15 !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card {
  margin: 0 0 12px !important;
  padding: 10px 12px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 12px !important;
  border-radius: 16px !important;
  background: #eef6ff !important;
  border: 1px solid rgba(191, 219, 254, .92) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list {
  margin: 0 0 7px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link {
  position: relative !important;
  width: 100% !important;
  min-height: 50px !important;
  padding: 7px 10px !important;
  display: grid !important;
  grid-template-columns: 38px minmax(0, 1fr) auto auto !important;
  column-gap: 10px !important;
  align-items: center !important;
  border-radius: 16px !important;
  color: #26364d !important;
  background: rgba(255, 255, 255, .28) !important;
  border: 1px solid transparent !important;
  box-shadow: none !important;
  box-sizing: border-box !important;
  overflow: hidden !important;
  transition: background-color .16s ease, border-color .16s ease, box-shadow .16s ease, transform .16s ease;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover {
  background: rgba(255, 255, 255, .76) !important;
  border-color: rgba(203, 213, 225, .72) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link {
  color: #081225 !important;
  background: #ffffff !important;
  border-color: rgba(47, 91, 234, .30) !important;
  box-shadow: 0 12px 26px rgba(15, 23, 42, .08) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active::after,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link::after {
  content: "" !important;
  display: block !important;
  position: absolute !important;
  left: 0 !important;
  top: 11px !important;
  bottom: 11px !important;
  width: 4px !important;
  border-radius: 0 999px 999px 0 !important;
  background: #2f5bea !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame {
  width: 38px !important;
  min-width: 38px !important;
  height: 38px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 13px !important;
  color: #1764a8 !important;
  background: #e6f1ff !important;
  border: 1px solid rgba(191, 219, 254, .86) !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame i {
  color: currentColor !important;
  font-size: 15px !important;
  line-height: 1 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame {
  color: #ffffff !important;
  background: #2f5bea !important;
  border-color: #2f5bea !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-label {
  min-width: 0 !important;
  color: inherit !important;
  font-size: 14px !important;
  font-weight: 850 !important;
  line-height: 1.15 !important;
  letter-spacing: -.01em !important;
  white-space: normal !important;
  overflow: visible !important;
  text-overflow: clip !important;
  overflow-wrap: break-word !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge {
  min-width: 24px !important;
  height: 24px !important;
  margin: 0 !important;
  padding: 0 7px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 999px !important;
  color: #475569 !important;
  background: #eef4ff !important;
  border: 1px solid rgba(148, 163, 184, .36) !important;
  font-size: 11px !important;
  font-weight: 850 !important;
  line-height: 1 !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .according-menu {
  position: static !important;
  width: 14px !important;
  min-width: 14px !important;
  height: 18px !important;
  margin: 0 !important;
  padding: 0 !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: #94a3b8 !important;
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
  font-size: 0 !important;
  transform: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .according-menu i {
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .according-menu::before {
  content: "" !important;
  width: 7px !important;
  height: 7px !important;
  border-right: 2px solid currentColor !important;
  border-bottom: 2px solid currentColor !important;
  transform: rotate(-45deg) !important;
  transition: transform .16s ease !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-link[aria-expanded="true"] .according-menu::before {
  transform: rotate(45deg) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu {
  width: 100% !important;
  max-width: 100% !important;
  margin: 7px 0 12px !important;
  padding: 6px 8px 6px 48px !important;
  background: transparent !important;
  border: 0 !important;
  border-radius: 0 !important;
  box-shadow: none !important;
  overflow: visible !important;
  box-sizing: border-box !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu::before,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu::after,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li::before,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li::after,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a::before,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a::after {
  content: none !important;
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li {
  width: 100% !important;
  margin: 0 !important;
  padding: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li + li {
  margin-top: 4px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a {
  width: 100% !important;
  min-height: 36px !important;
  padding: 6px 8px !important;
  display: grid !important;
  grid-template-columns: 24px minmax(0, 1fr) auto !important;
  column-gap: 8px !important;
  align-items: center !important;
  border-radius: 12px !important;
  color: #667085 !important;
  background: transparent !important;
  border: 1px solid transparent !important;
  box-shadow: none !important;
  box-sizing: border-box !important;
  overflow: visible !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame {
  width: 24px !important;
  min-width: 24px !important;
  height: 24px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 8px !important;
  color: #2563eb !important;
  background: rgba(232, 241, 255, .95) !important;
  border: 1px solid rgba(191, 219, 254, .65) !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame i {
  color: currentColor !important;
  font-size: 10px !important;
  line-height: 1 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a > span:not(.v2-sidebar-child-icon-frame):not(.v2-sidebar-menu-badge) {
  min-width: 0 !important;
  color: inherit !important;
  font-size: 13px !important;
  font-weight: 800 !important;
  line-height: 1.22 !important;
  letter-spacing: -.01em !important;
  white-space: normal !important;
  overflow: visible !important;
  text-overflow: clip !important;
  overflow-wrap: break-word !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active {
  color: #0f172a !important;
  background: rgba(255, 255, 255, .82) !important;
  border-color: rgba(203, 213, 225, .74) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active .v2-sidebar-child-icon-frame {
  color: #ffffff !important;
  background: #2f5bea !important;
  border-color: #2f5bea !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-track.simplebar-vertical {
  width: 5px !important;
  right: 5px !important;
  top: 12px !important;
  bottom: 12px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-scrollbar::before {
  background: rgba(100, 116, 139, .26) !important;
  border-radius: 999px !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar],
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar],
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] {
  background:
    linear-gradient(180deg, rgba(15, 23, 42, .98), rgba(17, 24, 39, .96)) !important;
  border-right-color: rgba(148, 163, 184, .18) !important;
  box-shadow: 16px 0 38px rgba(0, 0, 0, .22) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper {
  background: rgba(15, 23, 42, .72) !important;
  border-bottom-color: rgba(148, 163, 184, .14) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card {
  background: rgba(30, 41, 59, .72) !important;
  border-color: rgba(148, 163, 184, .18) !important;
  box-shadow: none !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-name,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-name,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-name {
  color: #f8fafc !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-group,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-group,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-group {
  color: #94a3b8 !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link {
  color: #cbd5e1 !important;
  background: transparent !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover {
  background: rgba(30, 41, 59, .72) !important;
  border-color: rgba(148, 163, 184, .14) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link {
  color: #ffffff !important;
  background: rgba(37, 99, 235, .24) !important;
  border-color: rgba(96, 165, 250, .34) !important;
  box-shadow: none !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame {
  color: #93c5fd !important;
  background: rgba(30, 64, 175, .22) !important;
  border-color: rgba(96, 165, 250, .18) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge {
  color: #dbeafe !important;
  background: rgba(30, 64, 175, .24) !important;
  border-color: rgba(96, 165, 250, .24) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a {
  color: #aab6c8 !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active {
  color: #ffffff !important;
  background: rgba(30, 41, 59, .76) !important;
  border-color: rgba(148, 163, 184, .16) !important;
}

@media (min-width: 992px) {
  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) {
    width: 86px !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .sidebar-links {
    padding-left: 12px !important;
    padding-right: 12px !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .sidebar-list > a.sidebar-link {
    grid-template-columns: 38px !important;
    justify-content: center !important;
    padding-left: 12px !important;
    padding-right: 12px !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .v2-sidebar-label,
  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .v2-sidebar-child-count,
  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .v2-sidebar-menu-badge,
  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .according-menu,
  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .sidebar-submenu,
  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .v2-sidebar-user-card,
  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(:hover):not(.is-hover-expanded) .v2-sidebar-balance-card {
    display: none !important;
  }
}

@media (max-width: 991.98px) {
  .page-wrapper.compact-wrapper {
    --v2-layout-sidebar-width: min(300px, 88vw);
  }
}
/* V2 submenu children same as parent rows */
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu {
  margin: 6px 0 10px !important;
  padding: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li {
  margin: 0 0 7px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li:last-child {
  margin-bottom: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a {
  position: relative !important;
  width: 100% !important;
  min-height: 50px !important;
  padding: 7px 10px !important;
  display: grid !important;
  grid-template-columns: 38px minmax(0, 1fr) auto !important;
  column-gap: 10px !important;
  align-items: center !important;
  border-radius: 16px !important;
  color: #26364d !important;
  background: rgba(255, 255, 255, .28) !important;
  border: 1px solid transparent !important;
  box-shadow: none !important;
  box-sizing: border-box !important;
  overflow: hidden !important;
  transition: background-color .16s ease, border-color .16s ease, box-shadow .16s ease;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover {
  color: #26364d !important;
  background: rgba(255, 255, 255, .76) !important;
  border-color: rgba(203, 213, 225, .72) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active {
  color: #081225 !important;
  background: #ffffff !important;
  border-color: rgba(47, 91, 234, .30) !important;
  box-shadow: 0 12px 26px rgba(15, 23, 42, .08) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active::after {
  content: "" !important;
  display: block !important;
  position: absolute !important;
  left: 0 !important;
  top: 11px !important;
  bottom: 11px !important;
  width: 4px !important;
  border-radius: 0 999px 999px 0 !important;
  background: #2f5bea !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame {
  width: 38px !important;
  min-width: 38px !important;
  height: 38px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 13px !important;
  color: #1764a8 !important;
  background: #e6f1ff !important;
  border: 1px solid rgba(191, 219, 254, .86) !important;
  box-shadow: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame i {
  color: currentColor !important;
  font-size: 15px !important;
  line-height: 1 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active .v2-sidebar-child-icon-frame {
  color: #ffffff !important;
  background: #2f5bea !important;
  border-color: #2f5bea !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a > span:not(.v2-sidebar-child-icon-frame):not(.v2-sidebar-menu-badge) {
  min-width: 0 !important;
  color: inherit !important;
  font-size: 14px !important;
  font-weight: 850 !important;
  line-height: 1.15 !important;
  letter-spacing: -.01em !important;
  white-space: normal !important;
  overflow: visible !important;
  text-overflow: clip !important;
  overflow-wrap: break-word !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a {
  color: #cbd5e1 !important;
  background: transparent !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover {
  color: #ffffff !important;
  background: rgba(30, 41, 59, .72) !important;
  border-color: rgba(148, 163, 184, .14) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active {
  color: #ffffff !important;
  background: rgba(37, 99, 235, .24) !important;
  border-color: rgba(96, 165, 250, .34) !important;
  box-shadow: none !important;
}
/* V2 sidebar active state uses theme color; hover stays unchanged */
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active {
  color: #ffffff !important;
  background: #2f5bea !important;
  border-color: #2f5bea !important;
  box-shadow: 0 12px 26px rgba(47, 91, 234, .24) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active::after,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link::after,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active::after {
  content: none !important;
  display: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active .v2-sidebar-child-icon-frame {
  color: #ffffff !important;
  background: rgba(255, 255, 255, .16) !important;
  border-color: rgba(255, 255, 255, .24) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-child-count,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-child-count,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-menu-badge,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-menu-badge,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a.active .v2-sidebar-menu-badge {
  color: #ffffff !important;
  background: rgba(255, 255, 255, .16) !important;
  border-color: rgba(255, 255, 255, .24) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .according-menu,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .according-menu {
  color: rgba(255, 255, 255, .86) !important;
}
/* V2 sidebar motion layer */
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list {
  animation: v2SidebarItemIn .28s ease both;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list:nth-of-type(2) { animation-delay: .02s; }
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list:nth-of-type(3) { animation-delay: .04s; }
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list:nth-of-type(4) { animation-delay: .06s; }
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list:nth-of-type(5) { animation-delay: .08s; }
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list:nth-of-type(6) { animation-delay: .10s; }
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list:nth-of-type(7) { animation-delay: .12s; }

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .according-menu::before {
  transition:
    transform .18s ease,
    background-color .18s ease,
    border-color .18s ease,
    color .18s ease,
    box-shadow .18s ease,
    opacity .18s ease !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover {
  transform: translateX(2px) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover .v2-sidebar-icon-frame,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:hover .v2-sidebar-child-icon-frame {
  transform: scale(1.04) rotate(-2deg) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:active,
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li a.li-a:active {
  transform: translateX(1px) scale(.99) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu {
  transform-origin: top center !important;
  animation: v2SidebarSubmenuIn .22s ease both;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li {
  animation: v2SidebarChildIn .22s ease both;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li:nth-child(2) { animation-delay: .025s; }
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li:nth-child(3) { animation-delay: .05s; }
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li:nth-child(4) { animation-delay: .075s; }
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li:nth-child(5) { animation-delay: .10s; }
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu li:nth-child(6) { animation-delay: .125s; }

@keyframes v2SidebarItemIn {
  from {
    opacity: 0;
    transform: translateX(-8px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes v2SidebarSubmenuIn {
  from {
    opacity: 0;
    transform: translateY(-4px) scaleY(.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scaleY(1);
  }
}

@keyframes v2SidebarChildIn {
  from {
    opacity: 0;
    transform: translateX(-5px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@media (prefers-reduced-motion: reduce) {
  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] *,
  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] *::before,
  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] *::after {
    animation: none !important;
    transition: none !important;
  }
}
/* V2 collapsed hover submenu fix */
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-section-title,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-section-title {
  display: none !important;
  width: 0 !important;
  height: 0 !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  opacity: 0 !important;
  overflow: hidden !important;
  pointer-events: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded {
  width: var(--v2-layout-sidebar-width) !important;
  background: linear-gradient(180deg, rgba(248, 251, 255, .98), rgba(235, 244, 255, .96)) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .sidebar-submenu,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-submenu {
  width: 100% !important;
  max-width: 100% !important;
  padding: 0 !important;
  margin: 6px 0 10px !important;
  overflow: visible !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .sidebar-submenu li a.li-a,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-submenu li a.li-a {
  width: 100% !important;
  min-height: 50px !important;
  padding: 7px 10px !important;
  display: grid !important;
  grid-template-columns: 38px minmax(0, 1fr) auto !important;
  column-gap: 10px !important;
  align-items: center !important;
  border-radius: 16px !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .sidebar-submenu li a.li-a > span:not(.v2-sidebar-child-icon-frame):not(.v2-sidebar-menu-badge),
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-submenu li a.li-a > span:not(.v2-sidebar-child-icon-frame):not(.v2-sidebar-menu-badge) {
  display: block !important;
  width: auto !important;
  min-width: 0 !important;
  max-width: none !important;
  height: auto !important;
  opacity: 1 !important;
  visibility: visible !important;
  color: inherit !important;
  font-size: 14px !important;
  font-weight: 850 !important;
  line-height: 1.15 !important;
  white-space: normal !important;
  overflow: visible !important;
  text-overflow: clip !important;
  pointer-events: auto !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover .v2-sidebar-child-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-child-icon-frame {
  width: 38px !important;
  min-width: 38px !important;
  max-width: 38px !important;
  height: 38px !important;
  opacity: 1 !important;
  visibility: visible !important;
  pointer-events: auto !important;
}

html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover,
html body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded,
html [data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover,
html [data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:hover,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded {
  background: linear-gradient(180deg, rgba(15, 23, 42, .98), rgba(17, 24, 39, .96)) !important;
}
/* V2 sidebar toggle state fix: no half-expanded sidebar on plain hover */
@media (min-width: 992px) {
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded),
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) {
    width: 86px !important;
    min-width: 86px !important;
    max-width: 86px !important;
    overflow: hidden !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .sidebar-links,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .sidebar-links {
    padding: 12px !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .sidebar-list > a.sidebar-link,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .sidebar-list > a.sidebar-link {
    width: 100% !important;
    min-height: 48px !important;
    padding: 6px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0 !important;
    grid-template-columns: none !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .v2-sidebar-label,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .v2-sidebar-child-count,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .v2-sidebar-menu-badge,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .according-menu,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .sidebar-submenu,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .v2-sidebar-user-card,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .v2-sidebar-balance-card,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .v2-sidebar-label,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .v2-sidebar-child-count,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .v2-sidebar-menu-badge,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .according-menu,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .sidebar-submenu,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .v2-sidebar-user-card,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .v2-sidebar-balance-card {
    display: none !important;
    width: 0 !important;
    min-width: 0 !important;
    max-width: 0 !important;
    height: 0 !important;
    min-height: 0 !important;
    max-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    opacity: 0 !important;
    visibility: hidden !important;
    overflow: hidden !important;
    pointer-events: none !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .v2-sidebar-icon-frame,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .v2-sidebar-icon-frame {
    width: 38px !important;
    min-width: 38px !important;
    max-width: 38px !important;
    height: 38px !important;
    opacity: 1 !important;
    visibility: visible !important;
  }
}
/* V2 hover-expanded collapsed sidebar must show open submenus */
@media (min-width: 992px) {
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-link[aria-expanded="true"] + .sidebar-submenu,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-submenu.is-open,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover.is-hover-expanded .sidebar-link[aria-expanded="true"] + .sidebar-submenu,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover.is-hover-expanded .sidebar-submenu.is-open {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    margin: 6px 0 10px !important;
    padding: 0 !important;
    opacity: 1 !important;
    visibility: visible !important;
    overflow: visible !important;
    pointer-events: auto !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-link[aria-expanded="true"] + .sidebar-submenu li,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-submenu.is-open li {
    display: block !important;
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    margin: 0 0 7px !important;
    padding: 0 !important;
    opacity: 1 !important;
    visibility: visible !important;
    overflow: visible !important;
    pointer-events: auto !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-link[aria-expanded="true"] + .sidebar-submenu li a.li-a,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-submenu.is-open li a.li-a {
    display: grid !important;
    grid-template-columns: 38px minmax(0, 1fr) auto !important;
    width: 100% !important;
    min-height: 50px !important;
    height: auto !important;
    max-height: none !important;
    padding: 7px 10px !important;
    opacity: 1 !important;
    visibility: visible !important;
    overflow: visible !important;
    pointer-events: auto !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-link[aria-expanded="true"] + .sidebar-submenu li a.li-a > span:not(.v2-sidebar-child-icon-frame):not(.v2-sidebar-menu-badge),
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .sidebar-submenu.is-open li a.li-a > span:not(.v2-sidebar-child-icon-frame):not(.v2-sidebar-menu-badge) {
    display: block !important;
    width: auto !important;
    min-width: 0 !important;
    height: auto !important;
    max-height: none !important;
    opacity: 1 !important;
    visibility: visible !important;
    color: inherit !important;
    overflow: visible !important;
    pointer-events: auto !important;
  }
}
/* V2 layout gap between sidebar and content */
.page-wrapper.compact-wrapper {
  --v2-sidebar-content-gap: 18px;
}

@media (min-width: 992px) {
  html body .page-wrapper.compact-wrapper .page-body-wrapper .page-body,
  html body .page-wrapper.compact-wrapper .page-body-wrapper footer {
    margin-left: calc(var(--v2-layout-sidebar-width) + var(--v2-sidebar-content-gap)) !important;
    width: calc(100% - var(--v2-layout-sidebar-width) - var(--v2-sidebar-content-gap)) !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon ~ .page-body,
  html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon ~ footer {
    margin-left: calc(86px + var(--v2-sidebar-content-gap)) !important;
    width: calc(100% - 86px - var(--v2-sidebar-content-gap)) !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded ~ .page-body,
  html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded ~ footer {
    margin-left: calc(86px + var(--v2-sidebar-content-gap)) !important;
    width: calc(100% - 86px - var(--v2-sidebar-content-gap)) !important;
  }
}
/* V2 sidebar logo center + theme animation */
.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper {
  position: relative !important;
  min-height: 86px !important;
  padding: 14px 18px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  overflow: hidden !important;
  background:
    radial-gradient(circle at 50% 0%, rgba(47, 91, 234, .10), transparent 44%),
    rgba(255, 255, 255, .76) !important;
  border-bottom: 1px solid rgba(148, 163, 184, .18) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper::before {
  content: "" !important;
  position: absolute !important;
  inset: 10px 14px !important;
  border-radius: 22px !important;
  background: linear-gradient(135deg, rgba(255, 255, 255, .92), rgba(239, 246, 255, .72)) !important;
  border: 1px solid rgba(191, 219, 254, .72) !important;
  box-shadow: 0 14px 28px rgba(15, 23, 42, .055) !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper::after {
  content: "" !important;
  position: absolute !important;
  top: 12px !important;
  bottom: 12px !important;
  left: -45% !important;
  width: 42% !important;
  transform: skewX(-18deg) !important;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .72), transparent) !important;
  animation: v2SidebarLogoShine 4.8s ease-in-out infinite !important;
  pointer-events: none !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper a {
  position: relative !important;
  z-index: 1 !important;
  width: 100% !important;
  min-height: 58px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper img {
  width: auto !important;
  max-width: 156px !important;
  max-height: 54px !important;
  object-fit: contain !important;
  filter: drop-shadow(0 7px 12px rgba(23, 100, 168, .16)) !important;
  animation: v2SidebarLogoFloat 5.6s ease-in-out infinite !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn {
  position: absolute !important;
  right: 10px !important;
  top: 10px !important;
  z-index: 2 !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper {
  background:
    radial-gradient(circle at 50% 0%, rgba(96, 165, 250, .16), transparent 46%),
    rgba(15, 23, 42, .78) !important;
  border-bottom-color: rgba(148, 163, 184, .14) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper::before,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper::before,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper::before {
  background: linear-gradient(135deg, rgba(30, 41, 59, .86), rgba(15, 23, 42, .72)) !important;
  border-color: rgba(96, 165, 250, .20) !important;
  box-shadow: 0 14px 30px rgba(0, 0, 0, .24) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper::after,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper::after,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper::after {
  background: linear-gradient(90deg, transparent, rgba(147, 197, 253, .20), transparent) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper img,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper img,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper img {
  filter: drop-shadow(0 8px 16px rgba(96, 165, 250, .22)) !important;
}

@media (min-width: 992px) {
  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .logo-wrapper {
    min-height: 78px !important;
    padding: 12px !important;
  }

  .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .logo-wrapper::before {
    inset: 12px !important;
    border-radius: 18px !important;
  }
}

@keyframes v2SidebarLogoFloat {
  0%, 100% {
    transform: translateY(0) scale(1);
  }
  50% {
    transform: translateY(-2px) scale(1.015);
  }
}

@keyframes v2SidebarLogoShine {
  0%, 58% {
    left: -45%;
    opacity: 0;
  }
  68% {
    opacity: .8;
  }
  88%, 100% {
    left: 108%;
    opacity: 0;
  }
}
/* V2 sidebar/content gap aligned to Bootstrap gutter */
.page-wrapper.compact-wrapper {
  --v2-bs-content-gutter: 0.5rem;
  --v2-sidebar-content-gap: var(--v2-bs-content-gutter);
}

.page-wrapper.compact-wrapper .page-body-wrapper .page-body > .container-fluid {
  --bs-gutter-x: var(--v2-bs-content-gutter);
}

@media (min-width: 992px) {
  html body .page-wrapper.compact-wrapper .page-body-wrapper .page-body,
  html body .page-wrapper.compact-wrapper .page-body-wrapper footer {
    margin-left: calc(var(--v2-layout-sidebar-width) + var(--v2-bs-content-gutter)) !important;
    width: calc(100% - var(--v2-layout-sidebar-width) - var(--v2-bs-content-gutter)) !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon ~ .page-body,
  html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon ~ footer {
    margin-left: calc(86px + var(--v2-bs-content-gutter)) !important;
    width: calc(100% - 86px - var(--v2-bs-content-gutter)) !important;
  }
}
/* V2 content spacing after header breadcrumb */
.page-wrapper.compact-wrapper .page-body-wrapper .page-body {
  padding-top: 18px !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .page-body > .container-fluid {
  margin-top: 0 !important;
  padding-top: 0 !important;
}

.page-wrapper.compact-wrapper .page-body-wrapper .page-body > .container-fluid:first-child {
  margin-top: 0 !important;
}

@media (max-width: 767.98px) {
  .page-wrapper.compact-wrapper .page-body-wrapper .page-body {
    padding-top: 14px !important;
  }
}

/* ===== Legacy sidebar empty-area finish ===== */
.sidebar-wrapper[sidebar-layout]:not([data-v2-sidebar]) #simple-bar{
  min-height: calc(100vh - 118px);
  display: flex;
  flex-direction: column;
}
/* ===== V2 sidebar lighter grouping and scroll polish ===== */
.page-wrapper.compact-wrapper{
  --v2-sidebar-logo-height: 86px;
  --v2-sidebar-scroll-bottom: 14px;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar]{
  height: 100vh !important;
  height: 100dvh !important;
  overflow: hidden !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper{
  height: var(--v2-sidebar-logo-height) !important;
  min-height: var(--v2-sidebar-logo-height) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main{
  height: calc(100vh - var(--v2-sidebar-logo-height)) !important;
  height: calc(100dvh - var(--v2-sidebar-logo-height)) !important;
  max-height: calc(100vh - var(--v2-sidebar-logo-height)) !important;
  max-height: calc(100dvh - var(--v2-sidebar-logo-height)) !important;
  overflow-y: auto !important;
  overflow-x: hidden !important;
  padding: 12px 10px 0 !important;
  scrollbar-width: thin;
  scrollbar-color: rgba(100, 116, 139, .32) transparent;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar{
  width: 6px;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-track{
  background: transparent;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-thumb{
  background: rgba(100, 116, 139, .28);
  border-radius: 999px;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] #simple-bar{
  height: auto !important;
  max-height: none !important;
  min-height: auto !important;
  padding: 6px 8px var(--v2-sidebar-scroll-bottom) !important;
  display: block !important;
  overflow: visible !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar]:not(.close_icon) .v2-sidebar-section-title,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-section-title{
  display: flex !important;
  width: auto !important;
  height: auto !important;
  min-height: 0 !important;
  margin: 18px 6px 7px !important;
  padding: 0 !important;
  opacity: 1 !important;
  overflow: visible !important;
  pointer-events: auto !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title:first-of-type{
  margin-top: 4px !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title span{
  color: #7a8ba5 !important;
  font-size: 10px !important;
  font-weight: 800 !important;
  letter-spacing: .08em !important;
  line-height: 1 !important;
  text-transform: uppercase !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list{
  margin: 4px 0 !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link{
  min-height: 42px !important;
  border-radius: 12px !important;
  padding: 7px 9px !important;
  column-gap: 9px !important;
  color: #203454 !important;
  background: transparent !important;
  border-color: transparent !important;
  box-shadow: none !important;
  transform: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover{
  color: var(--theme-primary, #0f4fa5) !important;
  background: rgba(var(--theme-primary-rgb, 15, 79, 165), .08) !important;
  border-color: rgba(var(--theme-primary-rgb, 15, 79, 165), .14) !important;
  box-shadow: none !important;
  transform: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link{
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--theme-sidebar-active, #2f6df6), var(--theme-primary, #0f4fa5)) !important;
  border-color: rgba(var(--theme-sidebar-active-rgb, 47, 109, 246), .30) !important;
  box-shadow: inset 3px 0 0 var(--theme-sidebar-active, #2f6df6) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link *{
  color: inherit !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame{
  width: 30px !important;
  min-width: 30px !important;
  max-width: 30px !important;
  height: 30px !important;
  border-radius: 9px !important;
  color: var(--theme-primary, #1c61b7) !important;
  background: rgba(var(--theme-primary-rgb, 28, 97, 183), .10) !important;
  border: 1px solid rgba(var(--theme-primary-rgb, 28, 97, 183), .18) !important;
  box-shadow: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame i,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame i,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame .dynamic-icon{
  font-size: 13px !important;
  line-height: 1 !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame{
  color: #ffffff !important;
  background: var(--theme-sidebar-active, #155ec3) !important;
  border-color: var(--theme-sidebar-active, #155ec3) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count{
  min-width: 0 !important;
  height: 18px !important;
  padding: 2px 6px !important;
  border-radius: 999px !important;
  color: var(--theme-primary, #2563eb) !important;
  background: rgba(var(--theme-primary-rgb, 37, 99, 235), .10) !important;
  border: 1px solid rgba(var(--theme-primary-rgb, 37, 99, 235), .18) !important;
  font-size: 9.5px !important;
  font-weight: 800 !important;
  letter-spacing: .02em !important;
  line-height: 12px !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-menu-badge,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-menu-badge,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-child-count,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-child-count{
  color: #ffffff !important;
  background: rgba(255, 255, 255, .18) !important;
  border-color: rgba(255, 255, 255, .28) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link{
  display: grid !important;
  grid-template-columns: 30px minmax(0, 1fr) auto !important;
  align-items: center !important;
  column-gap: 8px !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-label{
  min-width: 0 !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  white-space: nowrap !important;
  overflow-wrap: normal !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-trailing{
  min-width: 0 !important;
  max-width: 68px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: flex-end !important;
  justify-self: end !important;
  gap: 4px !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-trailing:empty{
  display: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-trailing .v2-sidebar-menu-badge,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-trailing .v2-sidebar-child-count{
  flex: 0 1 auto !important;
  max-width: 36px !important;
  margin: 0 !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  white-space: nowrap !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-trailing .according-menu{
  width: 12px !important;
  min-width: 12px !important;
  max-width: 12px !important;
  margin: 0 !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  flex: 0 0 12px !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link,
html.dark .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link{
  color: #ffffff !important;
  background: rgba(var(--theme-sidebar-active-rgb, 37, 99, 235), .18) !important;
  border-color: rgba(var(--theme-sidebar-active-rgb, 96, 165, 250), .24) !important;
  box-shadow: inset 3px 0 0 var(--theme-sidebar-active, #60a5fa) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-user-card,
html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .v2-sidebar-user-card,
html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .v2-sidebar-user-card,
html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-user-card{
  width: 100% !important;
  min-height: 64px !important;
  height: auto !important;
  margin: 0 0 12px !important;
  padding: 11px 12px !important;
  display: grid !important;
  grid-template-columns: 42px minmax(0, 1fr) !important;
  column-gap: 11px !important;
  align-items: center !important;
  opacity: 1 !important;
  transform: none !important;
  pointer-events: auto !important;
  overflow: visible !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card,
html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon .v2-sidebar-balance-card,
html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .v2-sidebar-balance-card,
html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .v2-sidebar-balance-card,
html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded .v2-sidebar-balance-card{
  width: 100% !important;
  min-height: 42px !important;
  height: auto !important;
  margin: 0 0 12px !important;
  padding: 9px 12px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 10px !important;
  opacity: 1 !important;
  transform: none !important;
  pointer-events: auto !important;
  overflow: visible !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-avatar-wrap,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-avatar{
  width: 42px !important;
  height: 42px !important;
  min-width: 42px !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-copy{
  min-width: 0 !important;
  display: block !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar],
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar],
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar]{
  background: #101827 !important;
  border-color: #22324a !important;
  box-shadow: 8px 0 24px rgba(0, 0, 0, .32) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links{
  background: #101827 !important;
  border-color: #22324a !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper{
  box-shadow: inset 0 -1px 0 rgba(148, 163, 184, .16) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card{
  background: rgba(15, 23, 42, .68) !important;
  border-color: rgba(var(--theme-primary-rgb, 96, 165, 250), .20) !important;
  box-shadow: none !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .user-name,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .balance-display,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .user-name,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .balance-display,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .user-name,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .balance-display{
  color: #f8fbff !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .user-group,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .balance-label,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .user-group,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .balance-label,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .user-group,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .balance-label{
  color: #9fb1ca !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title span,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title span,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title span{
  color: #90a4c3 !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link{
  color: #d7e2f2 !important;
  background: transparent !important;
  border-color: transparent !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus{
  color: #ffffff !important;
  background: rgba(var(--theme-sidebar-active-rgb, 47, 125, 255), .14) !important;
  border-color: rgba(var(--theme-sidebar-active-rgb, 96, 165, 250), .22) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame{
  color: var(--theme-primary, #93c5fd) !important;
  background: rgba(var(--theme-primary-rgb, 47, 125, 255), .14) !important;
  border-color: rgba(var(--theme-primary-rgb, 147, 197, 253), .22) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame{
  color: #ffffff !important;
  background: var(--theme-sidebar-active, #60a5fa) !important;
  border-color: var(--theme-sidebar-active, #60a5fa) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .dynamic-icon,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame i,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame i,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .dynamic-icon,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame i,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame i,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .dynamic-icon,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame i,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame i{
  color: inherit !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge{
  color: var(--theme-primary, #bfdbfe) !important;
  background: rgba(var(--theme-primary-rgb, 96, 165, 250), .14) !important;
  border-color: rgba(var(--theme-primary-rgb, 147, 197, 253), .20) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge--alert,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge--alert,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge--alert{
  color: #fecaca !important;
  background: rgba(239, 68, 68, .16) !important;
  border-color: rgba(248, 113, 113, .28) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu{
  background: rgba(15, 23, 42, .58) !important;
  border-color: #263a56 !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a{
  color: #c9d7eb !important;
  background: transparent !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:hover,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.active,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.active,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:hover,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.active{
  color: #ffffff !important;
  background: rgba(var(--theme-sidebar-active-rgb, 47, 125, 255), .16) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .back-btn,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .back-btn,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .back-btn{
  color: #d7e2f2 !important;
  background: rgba(15, 23, 42, .72) !important;
  border-color: rgba(var(--theme-primary-rgb, 96, 165, 250), .22) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] #sidebar-menu,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] #simple-bar,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-wrapper,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-mask,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-offset,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-content,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-content-wrapper{
  overflow: hidden !important;
  overflow-y: hidden !important;
  overflow-x: hidden !important;
  scrollbar-width: none !important;
  -ms-overflow-style: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] #sidebar-menu::-webkit-scrollbar,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links::-webkit-scrollbar,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] #simple-bar::-webkit-scrollbar,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-content-wrapper::-webkit-scrollbar{
  width: 0 !important;
  height: 0 !important;
  display: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-track,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-scrollbar{
  width: 0 !important;
  height: 0 !important;
  opacity: 0 !important;
  display: none !important;
  pointer-events: none !important;
}

@media (min-width: 992px){
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:not(.is-hover-expanded) .v2-sidebar-section-title,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon:hover:not(.is-hover-expanded) .v2-sidebar-section-title{
    display: none !important;
    width: 0 !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    opacity: 0 !important;
    overflow: hidden !important;
  }
}

/* Final V2 mobile shell reset: shared sidebar rules above can otherwise leave
   collapsed-sidebar width reserved on pages like dashboard-v2, bus, and profile. */
@media (max-width: 991.98px){
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header],
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header].close_icon{
    left: 0 !important;
    right: 0 !important;
    margin-left: 0 !important;
    width: 100% !important;
    max-width: 100vw !important;
    transform: none !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper .page-body,
  html body .page-wrapper.compact-wrapper .page-body-wrapper footer,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar] ~ .page-body,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar] ~ footer,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon ~ .page-body,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon ~ footer{
    margin-left: 0 !important;
    width: 100% !important;
    max-width: 100vw !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper .page-body > .container-fluid{
    width: 100% !important;
    max-width: 100vw !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
  }
}

/* Final V2 sidebar theme contract: keep the same markup usable in light and dark. */
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar],
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links{
  background: #f8fbff !important;
  color: #0f172a !important;
  border-color: rgba(191, 219, 254, .86) !important;
}

body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar]{
  box-shadow: 12px 0 30px rgba(15, 23, 42, .10) !important;
}

body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper{
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%) !important;
  box-shadow: inset 0 -1px 0 rgba(var(--theme-primary-rgb, 191, 219, 254), .18) !important;
}

body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card{
  background: rgba(255, 255, 255, .86) !important;
  border: 1px solid rgba(var(--theme-primary-rgb, 191, 219, 254), .18) !important;
  box-shadow: 0 10px 24px rgba(var(--theme-primary-rgb, 37, 99, 235), .08) !important;
}

body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .user-name,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .balance-display,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] #tamaBalance,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-label,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link{
  color: #17223b !important;
}

body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .user-group,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .balance-label,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title span{
  color: #64748b !important;
}

body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame{
  color: var(--theme-primary, #1764a8) !important;
  background: rgba(var(--theme-primary-rgb, 23, 100, 168), .10) !important;
  border-color: rgba(var(--theme-primary-rgb, 23, 100, 168), .20) !important;
}

body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link{
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--theme-sidebar-active, #2f7dff), var(--theme-primary, #1764a8)) !important;
  border-color: rgba(var(--theme-sidebar-active-rgb, 47, 125, 255), .30) !important;
  box-shadow: inset 3px 0 0 var(--theme-sidebar-active, #2f7dff) !important;
}

body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover .v2-sidebar-icon-frame,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus .v2-sidebar-icon-frame,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame{
  color: #ffffff !important;
  background: var(--theme-sidebar-active, #2f7dff) !important;
  border-color: var(--theme-sidebar-active, #2f7dff) !important;
}

body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count{
  color: var(--theme-primary, #1764a8) !important;
  background: rgba(var(--theme-primary-rgb, 23, 100, 168), .10) !important;
  border-color: rgba(var(--theme-primary-rgb, 23, 100, 168), .20) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar],
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-icon-wrapper,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links{
  background: #101827 !important;
  color: #d7e2f2 !important;
  border-color: #22324a !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar]{
  box-shadow: 8px 0 24px rgba(0, 0, 0, .32) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper{
  background: linear-gradient(180deg, #111b2b 0%, #101827 100%) !important;
  box-shadow: inset 0 -1px 0 rgba(148, 163, 184, .16) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card{
  background: rgba(15, 23, 42, .68) !important;
  border: 1px solid rgba(var(--theme-primary-rgb, 96, 165, 250), .20) !important;
  box-shadow: none !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .user-name,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .balance-display,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] #tamaBalance,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-label,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link{
  color: #f8fbff !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .user-group,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .balance-label,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title span{
  color: #9fb1ca !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-icon-frame,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-icon-frame{
  color: var(--theme-primary, #93c5fd) !important;
  background: rgba(var(--theme-primary-rgb, 47, 125, 255), .14) !important;
  border-color: rgba(var(--theme-primary-rgb, 147, 197, 253), .22) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link{
  color: #ffffff !important;
  background: rgba(var(--theme-sidebar-active-rgb, 47, 125, 255), .14) !important;
  border-color: rgba(var(--theme-sidebar-active-rgb, 96, 165, 250), .22) !important;
  box-shadow: inset 3px 0 0 var(--theme-sidebar-active, #60a5fa) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover .v2-sidebar-icon-frame,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus .v2-sidebar-icon-frame,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame{
  color: #ffffff !important;
  background: var(--theme-sidebar-active, #60a5fa) !important;
  border-color: var(--theme-sidebar-active, #60a5fa) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-menu-badge,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-child-count{
  color: var(--theme-primary, #bfdbfe) !important;
  background: rgba(var(--theme-primary-rgb, 96, 165, 250), .14) !important;
  border-color: rgba(var(--theme-primary-rgb, 147, 197, 253), .20) !important;
}

body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-balance-card{
  display: flex !important;
  opacity: 1 !important;
  transform: none !important;
  pointer-events: auto !important;
}

body.light .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card{
  display: grid !important;
}

/* Final V2 sidebar slim scroll: menu scrolls, page/header layout does not move. */
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main{
  height: calc(100vh - var(--v2-sidebar-logo-height, 86px)) !important;
  height: calc(100dvh - var(--v2-sidebar-logo-height, 86px)) !important;
  max-height: calc(100vh - var(--v2-sidebar-logo-height, 86px)) !important;
  max-height: calc(100dvh - var(--v2-sidebar-logo-height, 86px)) !important;
  overflow-y: auto !important;
  overflow-x: hidden !important;
  scrollbar-width: thin !important;
  scrollbar-color: rgba(var(--theme-primary-rgb, 47, 125, 255), .34) transparent !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] #sidebar-menu,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] #simple-bar,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .simplebar-content{
  height: auto !important;
  max-height: none !important;
  overflow: visible !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar{
  width: 4px !important;
  height: 4px !important;
  display: block !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-track{
  background: transparent !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-thumb{
  background: rgba(var(--theme-primary-rgb, 47, 125, 255), .30) !important;
  border-radius: 999px !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main:hover::-webkit-scrollbar-thumb{
  background: rgba(var(--theme-primary-rgb, 47, 125, 255), .48) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main{
  scrollbar-color: rgba(var(--theme-primary-rgb, 147, 197, 253), .34) transparent !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-thumb,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-thumb,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-thumb{
  background: rgba(var(--theme-primary-rgb, 147, 197, 253), .28) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main:hover::-webkit-scrollbar-thumb,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main:hover::-webkit-scrollbar-thumb,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main:hover::-webkit-scrollbar-thumb{
  background: rgba(var(--theme-primary-rgb, 147, 197, 253), .46) !important;
}

/* Final V2 sidebar bug fixes: clean profile card and subtle menu-only scroll. */
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card{
  position: relative !important;
  isolation: isolate !important;
  overflow: hidden !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card::after{
  content: none !important;
  display: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-avatar-wrap{
  width: 40px !important;
  height: 40px !important;
  min-width: 40px !important;
  margin: 0 !important;
  display: inline-grid !important;
  place-items: center !important;
  overflow: hidden !important;
  border-radius: 999px !important;
  background: #ffffff !important;
  border: 1px solid rgba(var(--theme-primary-rgb, 47, 125, 255), .24) !important;
  box-shadow: 0 8px 18px rgba(var(--theme-primary-rgb, 37, 99, 235), .12) !important;
  z-index: 1 !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-card .user-avatar{
  width: 100% !important;
  height: 100% !important;
  min-width: 0 !important;
  display: block !important;
  object-fit: cover !important;
  border: 0 !important;
  border-radius: 999px !important;
  box-shadow: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-online-dot{
  right: 1px !important;
  bottom: 1px !important;
  z-index: 2 !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-avatar-wrap,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-avatar-wrap,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-avatar-wrap{
  background: #0f172a !important;
  border-color: rgba(var(--theme-primary-rgb, 147, 197, 253), .28) !important;
  box-shadow: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main{
  padding-right: 8px !important;
  padding-bottom: 18px !important;
  scrollbar-width: thin !important;
  scrollbar-color: rgba(100, 116, 139, .20) transparent !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-links{
  padding-bottom: 20px !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar{
  width: 3px !important;
  height: 3px !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-button{
  width: 0 !important;
  height: 0 !important;
  display: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-track{
  background: transparent !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-thumb{
  background: rgba(100, 116, 139, .20) !important;
  border-radius: 999px !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main:hover::-webkit-scrollbar-thumb{
  background: rgba(var(--theme-primary-rgb, 47, 125, 255), .36) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main{
  scrollbar-color: rgba(var(--theme-primary-rgb, 147, 197, 253), .22) transparent !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-thumb,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-thumb,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main::-webkit-scrollbar-thumb{
  background: rgba(var(--theme-primary-rgb, 147, 197, 253), .20) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main:hover::-webkit-scrollbar-thumb,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main:hover::-webkit-scrollbar-thumb,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-main:hover::-webkit-scrollbar-thumb{
  background: rgba(var(--theme-primary-rgb, 147, 197, 253), .38) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-user-copy{
  display: flex !important;
  min-width: 0 !important;
  flex-direction: column !important;
  align-items: flex-start !important;
  gap: 2px !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance{
  width: 100% !important;
  margin-top: 7px !important;
  padding: 6px 8px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 8px !important;
  border-radius: 10px !important;
  background: rgba(239, 246, 255, .88) !important;
  border: 1px solid rgba(191, 219, 254, .86) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance .balance-label{
  margin: 0 !important;
  color: #64748b !important;
  font-size: 10px !important;
  font-weight: 800 !important;
  line-height: 1 !important;
  letter-spacing: .08em !important;
  text-transform: uppercase !important;
  white-space: nowrap !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance .balance-display,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance #tamaBalance{
  min-width: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  color: #0f172a !important;
  background: transparent !important;
  border: 0 !important;
  font-size: 13px !important;
  font-weight: 900 !important;
  line-height: 1 !important;
  white-space: nowrap !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance{
  background: rgba(15, 23, 42, .74) !important;
  border-color: rgba(var(--theme-primary-rgb, 147, 197, 253), .18) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance .balance-label,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance .balance-label,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance .balance-label{
  color: #9fb1ca !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance .balance-display,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance #tamaBalance,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance .balance-display,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance #tamaBalance,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance .balance-display,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-inline-balance #tamaBalance{
  color: #f8fbff !important;
}

/* Final V2 sidebar menu parity: flat old-style menu and white icons only on hover/active. */
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .v2-sidebar-section-title{
  display: none !important;
  width: 0 !important;
  height: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  overflow: hidden !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover .v2-sidebar-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus .v2-sidebar-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame{
  color: #ffffff !important;
  background: var(--theme-sidebar-active, #2f7dff) !important;
  border-color: var(--theme-sidebar-active, #2f7dff) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover .v2-sidebar-icon-frame i,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus .v2-sidebar-icon-frame i,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame i,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame i,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover .v2-sidebar-icon-frame i::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus .v2-sidebar-icon-frame i::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame i::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame i::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover .v2-sidebar-icon-frame .dynamic-icon,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus .v2-sidebar-icon-frame .dynamic-icon,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame .dynamic-icon,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame .dynamic-icon,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover .v2-sidebar-icon-frame .dynamic-icon::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus .v2-sidebar-icon-frame .dynamic-icon::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame .dynamic-icon::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame .dynamic-icon::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover .v2-sidebar-icon-frame svg,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus .v2-sidebar-icon-frame svg,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame svg,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame svg{
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
  fill: currentColor !important;
  stroke: currentColor !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:hover .v2-sidebar-icon-frame svg *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link:focus .v2-sidebar-icon-frame svg *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list > a.sidebar-link.active .v2-sidebar-icon-frame svg *,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-list.active > a.sidebar-link .v2-sidebar-icon-frame svg *{
  color: #ffffff !important;
  fill: currentColor !important;
  stroke: currentColor !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:hover .v2-sidebar-child-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:focus .v2-sidebar-child-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.active .v2-sidebar-child-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li.active > a .v2-sidebar-child-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:hover .v2-sidebar-child-icon-frame i,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:focus .v2-sidebar-child-icon-frame i,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.active .v2-sidebar-child-icon-frame i,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li.active > a .v2-sidebar-child-icon-frame i,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:hover .v2-sidebar-child-icon-frame i::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:focus .v2-sidebar-child-icon-frame i::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.active .v2-sidebar-child-icon-frame i::before,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li.active > a .v2-sidebar-child-icon-frame i::before{
  color: #ffffff !important;
  -webkit-text-fill-color: #ffffff !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:hover .v2-sidebar-child-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a:focus .v2-sidebar-child-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li > a.active .v2-sidebar-child-icon-frame,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .sidebar-submenu > li.active > a .v2-sidebar-child-icon-frame{
  background: var(--theme-sidebar-active, #2f7dff) !important;
  border-color: var(--theme-sidebar-active, #2f7dff) !important;
}

/* Final V2 header cleanup: no header tooltips, click-only language toggle. */
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] [data-v2-no-tooltip="true"]{
  cursor: pointer !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current{
  transition: background .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-current .fa-angle-down{
  transition: transform .18s ease, color .18s ease !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-menu,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .more_lang.v2-language-menu{
  display: block !important;
  opacity: 0 !important;
  visibility: hidden !important;
  transform: translateY(10px) scale(.98) !important;
  pointer-events: none !important;
  transition: opacity .18s ease, transform .18s ease, visibility 0s linear .18s !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:not(.is-open):hover .v2-language-current,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:not(.is-open):focus-within .v2-language-current{
  color: var(--v2-header-ink) !important;
  background: rgba(255, 255, 255, .78) !important;
  box-shadow: inset 0 0 0 1px rgba(226, 232, 240, .9), 0 10px 24px rgba(15, 23, 42, .06) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:not(.is-open):hover .fa-angle-down,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:not(.is-open):focus-within .fa-angle-down{
  color: var(--v2-header-muted) !important;
  transform: rotate(0deg) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select.is-open .v2-language-current{
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--v2-header-blue), #0f4f85) !important;
  box-shadow: 0 14px 28px rgba(23, 100, 168, .24) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select.is-open .fa-angle-down{
  color: #ffffff !important;
  transform: rotate(180deg) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select.is-open .v2-language-menu,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select.is-open .more_lang.v2-language-menu{
  opacity: 1 !important;
  visibility: visible !important;
  transform: translateY(0) scale(1) !important;
  pointer-events: auto !important;
  transition: opacity .18s ease, transform .18s ease, visibility 0s linear 0s !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:not(.is-open):hover .v2-language-current,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:not(.is-open):focus-within .v2-language-current,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:not(.is-open):hover .v2-language-current,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:not(.is-open):focus-within .v2-language-current,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:not(.is-open):hover .v2-language-current,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-select:not(.is-open):focus-within .v2-language-current{
  color: #f8fafc !important;
  background: rgba(15, 23, 42, .72) !important;
  box-shadow: inset 0 0 0 1px rgba(148, 163, 184, .18), 0 14px 28px rgba(0, 0, 0, .22) !important;
}

/* Final V2 header toggles: icon-only theme and segmented EN/FR language. */
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle{
  width: auto !important;
  height: 42px !important;
  min-height: 42px !important;
  padding: 5px !important;
  border: 0 !important;
  border-radius: 16px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 5px !important;
  color: var(--v2-header-ink) !important;
  background: rgba(255, 255, 255, .78) !important;
  box-shadow: inset 0 0 0 1px rgba(226, 232, 240, .9), 0 10px 24px rgba(15, 23, 42, .06) !important;
  transition: background .18s ease, box-shadow .18s ease, transform .18s ease !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle{
  min-width: 88px !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle{
  min-width: 104px !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle.is-loading{
  opacity: .78 !important;
  pointer-events: none !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:focus-visible,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:hover,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:focus-within{
  background: rgba(255, 255, 255, .92) !important;
  box-shadow: inset 0 0 0 1px rgba(191, 219, 254, .95), 0 14px 30px rgba(15, 23, 42, .09) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option{
  width: 36px !important;
  min-width: 36px !important;
  height: 32px !important;
  min-height: 32px !important;
  padding: 0 !important;
  border: 0 !important;
  border-radius: 12px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  flex: 0 0 auto !important;
  color: var(--v2-header-muted) !important;
  background: transparent !important;
  box-shadow: none !important;
  line-height: 1 !important;
  text-decoration: none !important;
  transition: background .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option{
  width: 46px !important;
  min-width: 46px !important;
  color: var(--v2-header-muted) !important;
  font-size: 12px !important;
  font-weight: 900 !important;
  letter-spacing: .04em !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle .v2-theme-icon{
  width: 15px !important;
  height: 15px !important;
  font-size: 15px !important;
  line-height: 1 !important;
  margin: 0 !important;
  color: currentColor !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option > .visually-hidden{
  position: absolute !important;
  width: 1px !important;
  height: 1px !important;
  padding: 0 !important;
  margin: -1px !important;
  overflow: hidden !important;
  clip: rect(0, 0, 0, 0) !important;
  white-space: nowrap !important;
  border: 0 !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option:hover,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option:hover,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option:focus-visible{
  color: var(--v2-header-primary) !important;
  background: rgba(23, 100, 168, .10) !important;
  outline: 0 !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--light,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--dark,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option.active,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option.selected{
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--v2-header-primary), var(--v2-header-primary-dark)) !important;
  box-shadow: 0 8px 18px rgba(23, 100, 168, .22) !important;
}

html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--dark,
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--light{
  color: var(--v2-header-muted) !important;
  background: transparent !important;
  box-shadow: none !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle{
  color: #f8fafc !important;
  background: rgba(15, 23, 42, .72) !important;
  box-shadow: inset 0 0 0 1px rgba(148, 163, 184, .18), 0 14px 28px rgba(0, 0, 0, .22) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:focus-visible,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:hover,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:focus-within,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:focus-visible,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:focus-within,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:focus-visible,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:hover,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:focus-within{
  background: rgba(15, 23, 42, .86) !important;
  box-shadow: inset 0 0 0 1px rgba(96, 165, 250, .34), 0 16px 32px rgba(0, 0, 0, .28) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option{
  color: #a8b6c9 !important;
  background: transparent !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option:hover,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option:hover,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option:focus-visible,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option:focus-visible,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option:hover,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option:hover,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option:focus-visible{
  color: #bfdbfe !important;
  background: rgba(59, 130, 246, .16) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--light,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--dark,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option.active,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option.selected,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--light,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--dark,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option.active,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option.selected,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--light,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--dark,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option.active,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option.selected{
  color: #ffffff !important;
  background: linear-gradient(135deg, var(--v2-header-primary), var(--v2-header-primary-dark)) !important;
  box-shadow: 0 8px 18px rgba(37, 99, 235, .30) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--dark,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--light,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--dark,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--light,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--dark,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--light{
  color: #a8b6c9 !important;
  background: transparent !important;
  box-shadow: none !important;
}

@media (max-width: 575px){
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle{
    height: 38px !important;
    min-height: 38px !important;
    padding: 4px !important;
    border-radius: 14px !important;
    gap: 4px !important;
  }

  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle{
    width: 38px !important;
    min-width: 38px !important;
  }

  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle{
    width: 46px !important;
    min-width: 46px !important;
  }

  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option,
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option{
    height: 30px !important;
    min-height: 30px !important;
    border-radius: 11px !important;
  }

  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option{
    width: 30px !important;
    min-width: 30px !important;
  }

  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option{
    width: 38px !important;
    min-width: 38px !important;
    font-size: 11px !important;
  }

  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--dark,
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--light,
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option:not(.active):not(.selected){
    display: none !important;
  }

  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle:not(.active) .v2-theme-option--light,
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-toggle.active .v2-theme-option--dark,
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option.active,
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option.selected{
    display: inline-flex !important;
  }
}

@media (max-width: 380px){
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle{
    width: 36px !important;
    min-width: 36px !important;
  }

  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle{
    width: 44px !important;
    min-width: 44px !important;
  }

  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-theme-option{
    width: 28px !important;
    min-width: 28px !important;
  }

  html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle-option{
    width: 36px !important;
    min-width: 36px !important;
  }
}

/* Final mobile sidebar close control placement. */
html body .page-wrapper.compact-wrapper .page-header[data-v2-header] .tooltip,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .tooltip{
  display: none !important;
  opacity: 0 !important;
  visibility: hidden !important;
  pointer-events: none !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn{
  width: 36px !important;
  height: 36px !important;
  min-width: 36px !important;
  min-height: 36px !important;
  padding: 0 !important;
  border: 0 !important;
  border-radius: 14px !important;
  display: inline-grid !important;
  place-items: center !important;
  position: absolute !important;
  right: 16px !important;
  top: 50% !important;
  z-index: 5 !important;
  color: var(--v2-nav-blue, #1764a8) !important;
  background: rgba(255, 255, 255, .86) !important;
  box-shadow: inset 0 0 0 1px rgba(23, 100, 168, .18), 0 10px 22px rgba(15, 23, 42, .10) !important;
  transform: translateY(-50%) !important;
  transition: background .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn:hover,
html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn:focus-visible{
  color: #ffffff !important;
  background: var(--v2-nav-blue, #1764a8) !important;
  box-shadow: 0 12px 26px rgba(23, 100, 168, .24) !important;
  outline: 0 !important;
  transform: translateY(-50%) scale(1.03) !important;
}

html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn i{
  margin: 0 !important;
  font-size: 18px !important;
  line-height: 1 !important;
  color: currentColor !important;
}

@media (max-width: 991.98px){
  html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper{
    min-height: 88px !important;
    padding: 12px 58px 12px 18px !important;
    overflow: hidden !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper::before{
    inset: 10px 16px !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper a{
    min-height: 62px !important;
    justify-content: center !important;
    pointer-events: auto !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper img{
    max-width: min(172px, calc(100vw - 160px)) !important;
    max-height: 52px !important;
  }
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn{
  color: #bfdbfe !important;
  background: rgba(15, 23, 42, .82) !important;
  box-shadow: inset 0 0 0 1px rgba(147, 197, 253, .24), 0 12px 26px rgba(0, 0, 0, .26) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn:hover,
body.dark-only .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn:focus-visible,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn:focus-visible,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn:hover,
html.dark body .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper[data-v2-sidebar] .logo-wrapper .back-btn:focus-visible{
  color: #ffffff !important;
  background: var(--theme-primary, #2563eb) !important;
}

/* Final V2 shell alignment: remove desktop gap between sidebar, header, and page body. */
html body .page-wrapper.compact-wrapper{
  --v2-bs-content-gutter: 0px !important;
  --v2-sidebar-content-gap: 0px !important;
}

@media (min-width: 992px){
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header],
  html body .page-wrapper.compact-wrapper .page-header[data-v2-header]:not(.close_icon){
    left: 0 !important;
    margin-left: var(--v2-layout-sidebar-width) !important;
    width: calc(100% - var(--v2-layout-sidebar-width)) !important;
    max-width: calc(100% - var(--v2-layout-sidebar-width)) !important;
  }

  html body .page-wrapper.compact-wrapper .page-header[data-v2-header].close_icon{
    left: 0 !important;
    margin-left: var(--v2-layout-sidebar-collapsed, 86px) !important;
    width: calc(100% - var(--v2-layout-sidebar-collapsed, 86px)) !important;
    max-width: calc(100% - var(--v2-layout-sidebar-collapsed, 86px)) !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar] ~ .page-body,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar] ~ footer{
    margin-left: var(--v2-layout-sidebar-width) !important;
    width: calc(100% - var(--v2-layout-sidebar-width)) !important;
    max-width: calc(100% - var(--v2-layout-sidebar-width)) !important;
  }

  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon ~ .page-body,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon ~ footer,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded ~ .page-body,
  html body .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper[data-v2-sidebar].close_icon.is-hover-expanded ~ footer{
    margin-left: var(--v2-layout-sidebar-collapsed, 86px) !important;
    width: calc(100% - var(--v2-layout-sidebar-collapsed, 86px)) !important;
    max-width: calc(100% - var(--v2-layout-sidebar-collapsed, 86px)) !important;
  }
}

/* Final V2 dark header color match: keep the header on the same navy shell as the sidebar. */
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header],
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header],
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header],
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header]{
  --v2-header-primary: #3b82f6;
  --v2-header-primary-dark: #2563eb;
  --v2-header-blue: #60a5fa;
  --v2-header-ink: #f8fbff;
  --v2-header-muted: #9fb1ca;
  --v2-header-line: #22324a;
  --v2-header-glass: rgba(15, 23, 42, .76);
  background: linear-gradient(180deg, #111b2b 0%, #101827 100%) !important;
  border-bottom-color: #22324a !important;
  box-shadow: inset 0 -1px 0 rgba(148, 163, 184, .08), 0 12px 28px rgba(0, 0, 0, .22) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .header-wrapper,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-area,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .header-wrapper,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-area,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .header-wrapper,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-area,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .header-wrapper,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-shell,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-brand,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-area,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-card{
  background: transparent !important;
  border-color: transparent !important;
  box-shadow: none !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-heading,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-heading,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-heading,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-title .v2-page-title-heading{
  color: #f8fbff !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .breadcrumb-item,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-link,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-breadcrumb-list .v2-breadcrumb-current{
  color: #9fb1ca !important;
  background: transparent !important;
  box-shadow: none !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger{
  color: #edf5ff !important;
  background: rgba(15, 23, 42, .74) !important;
  border-color: rgba(147, 197, 253, .18) !important;
  box-shadow: inset 0 0 0 1px rgba(147, 197, 253, .12), 0 10px 22px rgba(0, 0, 0, .18) !important;
}

body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button:hover,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:focus-visible,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:hover,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:focus-within,
body.dark-only .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:focus-visible,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:hover,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:focus-within,
[data-bs-theme="dark"] .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger:hover,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button:hover,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:focus-visible,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:hover,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:focus-within,
html.dark body .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger:hover,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-header-icon-button:hover,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:hover,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .mode.v2-theme-toggle:focus-visible,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:hover,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-language-toggle:focus-within,
html.dark .page-wrapper.compact-wrapper .page-header[data-v2-header] .v2-profile-trigger:hover{
  color: #ffffff !important;
  background: rgba(30, 41, 59, .82) !important;
  border-color: rgba(96, 165, 250, .32) !important;
  box-shadow: inset 0 0 0 1px rgba(96, 165, 250, .18), 0 12px 26px rgba(0, 0, 0, .24) !important;
}
</style>
