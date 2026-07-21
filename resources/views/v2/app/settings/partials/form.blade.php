@php
  $normalizeHex = function ($value, $fallback = '#000000') {
    $value = strtoupper(trim((string) $value));

    return preg_match('/^#[0-9A-F]{6}$/', $value) ? $value : strtoupper($fallback);
  };

  $mixHex = function ($from, $to, $ratio = 0.5) use ($normalizeHex) {
    $from = ltrim($normalizeHex($from, '#000000'), '#');
    $to = ltrim($normalizeHex($to, '#FFFFFF'), '#');
    $ratio = max(0, min(1, (float) $ratio));
    $channels = [];

    foreach ([0, 2, 4] as $offset) {
      $fromChannel = hexdec(substr($from, $offset, 2));
      $toChannel = hexdec(substr($to, $offset, 2));
      $channels[] = sprintf('%02X', (int) round($fromChannel + (($toChannel - $fromChannel) * $ratio)));
    }

    return '#' . implode('', $channels);
  };

  $contrastHex = function ($hex) use ($normalizeHex) {
    $hex = ltrim($normalizeHex($hex, '#000000'), '#');
    $red = hexdec(substr($hex, 0, 2));
    $green = hexdec(substr($hex, 2, 2));
    $blue = hexdec(substr($hex, 4, 2));
    $luma = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

    return $luma > 156 ? '#111827' : '#FFFFFF';
  };

  $buildThemePreset = function ($label, $primary, $accent) use ($normalizeHex, $mixHex, $contrastHex) {
    $primary = $normalizeHex($primary, '#1764A8');
    $accent = $normalizeHex($accent, $mixHex($primary, '#FFFFFF', 0.28));
    $interactive = $mixHex($primary, '#000000', 0.18);
    $headerBackground = $mixHex($primary, '#FFFFFF', 0.98);
    $dashboardBackground = $mixHex($primary, '#FFFFFF', 0.955);
    $dashboardBorder = $mixHex($primary, '#FFFFFF', 0.82);

    return [
      'label' => $label,
      'colors' => [
        'theme_primary_color' => $primary,
        'theme_accent_color' => $accent,
        'theme_login_color' => $interactive,
        'theme_header_color' => $headerBackground,
        'theme_header_text_color' => $interactive,
        'theme_sidebar_color' => '#FFFFFF',
        'theme_sidebar_active_color' => $interactive,
        'theme_sidebar_text_color' => '#1F2937',
        'theme_button_color' => $interactive,
        'theme_button_text_color' => $contrastHex($interactive),
        'theme_dashboard_background_color' => $dashboardBackground,
        'theme_dashboard_card_color' => '#FFFFFF',
        'theme_dashboard_text_color' => '#1F2937',
        'theme_dashboard_muted_color' => '#6B7280',
        'theme_dashboard_border_color' => $dashboardBorder,
        'theme_dark_surface_color' => '#161311',
        'theme_dark_card_color' => '#221A16',
        'theme_dark_text_color' => '#F5F5F5',
        'theme_dark_muted_color' => '#A8A8A8',
        'theme_dark_border_color' => '#3A2A22',
      ],
    ];
  };

  $themePresets = [
    'royal_blue' => $buildThemePreset('Royal blue', '#00427F', '#7098BC'),
    'royal_indigo' => $buildThemePreset('Royal indigo', '#7098BC', '#00427F'),
    'royal_office' => $buildThemePreset('Royal office', '#83AE69', '#7098BC'),
    'royal_rose' => $buildThemePreset('Royal rose', '#D853A4', '#E58742'),
    'royal_commerce' => $buildThemePreset('Royal commerce', '#E58742', '#D853A4'),
    'royal_navy' => $buildThemePreset('Royal navy', '#00427F', '#83AE69'),
    'royal_steel' => $buildThemePreset('Royal steel', '#7098BC', '#E58742'),
    'royal_sage' => $buildThemePreset('Royal sage', '#83AE69', '#00427F'),
    'royal_berry' => $buildThemePreset('Royal berry', '#D853A4', '#00427F'),
    'royal_amber' => $buildThemePreset('Royal amber', '#E58742', '#83AE69'),
    'royal_executive' => $buildThemePreset('Royal executive', '#00427F', '#D853A4'),
    'royal_garden' => $buildThemePreset('Royal garden', '#83AE69', '#E58742'),
    'royal_harbor' => $buildThemePreset('Royal harbor', '#7098BC', '#00427F'),
  ];

  $themeFields = [
    ['name' => 'theme_primary_color', 'label' => 'Dashboard accent', 'setting' => 'THEME_PRIMARY_COLOR'],
    ['name' => 'theme_accent_color', 'label' => 'Secondary accent', 'setting' => 'THEME_ACCENT_COLOR'],
    ['name' => 'theme_login_color', 'label' => 'Login primary', 'setting' => 'THEME_LOGIN_COLOR'],
    ['name' => 'theme_header_color', 'label' => 'Header background', 'setting' => 'THEME_HEADER_COLOR'],
    ['name' => 'theme_header_text_color', 'label' => 'Header text', 'setting' => 'THEME_HEADER_TEXT_COLOR'],
    ['name' => 'theme_sidebar_color', 'label' => 'Sidebar background', 'setting' => 'THEME_SIDEBAR_COLOR'],
    ['name' => 'theme_sidebar_active_color', 'label' => 'Sidebar active', 'setting' => 'THEME_SIDEBAR_ACTIVE_COLOR'],
    ['name' => 'theme_sidebar_text_color', 'label' => 'Sidebar text', 'setting' => 'THEME_SIDEBAR_TEXT_COLOR'],
    ['name' => 'theme_button_color', 'label' => 'Button background', 'setting' => 'THEME_BUTTON_COLOR'],
    ['name' => 'theme_button_text_color', 'label' => 'Button text', 'setting' => 'THEME_BUTTON_TEXT_COLOR'],
    ['name' => 'theme_dashboard_background_color', 'label' => 'Light background', 'setting' => 'THEME_DASHBOARD_BACKGROUND_COLOR'],
    ['name' => 'theme_dashboard_card_color', 'label' => 'Light card', 'setting' => 'THEME_DASHBOARD_CARD_COLOR'],
    ['name' => 'theme_dashboard_text_color', 'label' => 'Light text', 'setting' => 'THEME_DASHBOARD_TEXT_COLOR'],
    ['name' => 'theme_dashboard_muted_color', 'label' => 'Light muted text', 'setting' => 'THEME_DASHBOARD_MUTED_COLOR'],
    ['name' => 'theme_dashboard_border_color', 'label' => 'Light border', 'setting' => 'THEME_DASHBOARD_BORDER_COLOR'],
    ['name' => 'theme_dark_surface_color', 'label' => 'Dark surface', 'setting' => 'THEME_DARK_SURFACE_COLOR'],
    ['name' => 'theme_dark_card_color', 'label' => 'Dark card', 'setting' => 'THEME_DARK_CARD_COLOR'],
    ['name' => 'theme_dark_text_color', 'label' => 'Dark text', 'setting' => 'THEME_DARK_TEXT_COLOR'],
    ['name' => 'theme_dark_muted_color', 'label' => 'Dark muted text', 'setting' => 'THEME_DARK_MUTED_COLOR'],
    ['name' => 'theme_dark_border_color', 'label' => 'Dark border', 'setting' => 'THEME_DARK_BORDER_COLOR'],
  ];

  $themeFieldLookup = [];
  foreach ($themeFields as $themeField) {
    $themeFieldLookup[$themeField['name']] = $themeField;
  }

  $themeFieldGroups = [
    [
      'title' => 'Shared brand and actions',
      'copy' => 'Core accents shared by light and dark mode for login, buttons, and primary actions.',
      'mode' => 'shared',
      'fields' => ['theme_primary_color', 'theme_accent_color', 'theme_login_color', 'theme_button_color', 'theme_button_text_color'],
    ],
    [
      'title' => 'Header and navigation',
      'copy' => 'Header and sidebar colors that control top-level navigation and menu emphasis.',
      'mode' => 'light',
      'fields' => ['theme_header_color', 'theme_header_text_color', 'theme_sidebar_color', 'theme_sidebar_active_color', 'theme_sidebar_text_color'],
    ],
    [
      'title' => 'Light workspace',
      'copy' => 'Background, cards, text, muted text, and borders used throughout light mode.',
      'mode' => 'light',
      'fields' => ['theme_dashboard_background_color', 'theme_dashboard_card_color', 'theme_dashboard_text_color', 'theme_dashboard_muted_color', 'theme_dashboard_border_color'],
    ],
    [
      'title' => 'Dark workspace',
      'copy' => 'Surface, card, text, muted text, and border values used throughout dark mode.',
      'mode' => 'dark',
      'fields' => ['theme_dark_surface_color', 'theme_dark_card_color', 'theme_dark_text_color', 'theme_dark_muted_color', 'theme_dark_border_color'],
    ],
  ];

  $pageSections = [
    ['id' => 'settings-general', 'eyebrow' => 'Section 01', 'title' => 'General', 'copy' => 'Brand, language, currency, and timezone.'],
    ['id' => 'settings-appearance', 'eyebrow' => 'Section 02', 'title' => 'Appearance', 'copy' => 'Theme presets and light-dark workspace colors.'],
    ['id' => 'settings-operations', 'eyebrow' => 'Section 03', 'title' => 'Operations', 'copy' => 'Switches, record behavior, and internal limits.'],
    ['id' => 'settings-integration', 'eyebrow' => 'Section 04', 'title' => 'Integration', 'copy' => 'API access, bus format, and credentials.'],
  ];
@endphp

<div class="container-fluid app-settings-v2-page"
  id="app-settings-v2"
  data-save-url="{{ route('app-settings.v2.save') }}"
  data-clear-url="{{ url('clear') }}"
  data-csrf="{{ csrf_token() }}">
  @if(session('message'))
    <div class="app-settings-v2-flash {{ session('message_type') }}">
      {!! session('message') !!}
    </div>
  @endif

  @if($errors->any())
    <div class="app-settings-v2-flash warning">
      {{ $errors->first() }}
    </div>
  @endif

  <form class="app-settings-v2-form" id="app-settings-v2-form" action="{{ route('app-settings.v2.save') }}" method="POST" enctype="multipart/form-data" novalidate>
    {{ csrf_field() }}

    <div class="app-settings-v2-shell">
      <aside class="app-settings-v2-rail" aria-label="Settings sections">
        <div class="app-settings-v2-rail-card">
          <span class="app-settings-v2-rail-label">Settings map</span>
          <h3>Application settings</h3>
          <p>Move through the configuration in focused blocks instead of scanning one long form.</p>

          <div class="app-settings-v2-rail-meta">
            <span><strong>{{ count($pageSections) }}</strong> sections</span>
            <span><strong>{{ count($themePresets) }}</strong> presets</span>
          </div>

          <nav class="app-settings-v2-section-nav">
            @foreach($pageSections as $pageSection)
              <a class="app-settings-v2-section-link" href="#{{ $pageSection['id'] }}">
                <span class="app-settings-v2-section-kicker">{{ $pageSection['eyebrow'] }}</span>
                <strong>{{ $pageSection['title'] }}</strong>
                <small>{{ $pageSection['copy'] }}</small>
              </a>
            @endforeach
          </nav>
        </div>
      </aside>

      <div class="app-settings-v2-stack">
        <section class="app-settings-v2-section" id="settings-general">
          <div class="app-settings-v2-section-head">
            <div>
              <span class="app-settings-v2-section-kicker">Section 01</span>
              <h3>General</h3>
              <p>Core branding, default language, timezone, and currency choices.</p>
            </div>
          </div>

          <section class="app-settings-v2-panel app-settings-v2-panel-wide">
            <div class="app-settings-v2-panel-head">
              <i class="fa fa-building" aria-hidden="true"></i>
              <div class="app-settings-v2-panel-title">
                <h4>Branding</h4>
                <p>Identity, defaults, and the public-facing application logo.</p>
              </div>
            </div>

            <div class="app-settings-v2-brand-row">
              <div class="app-settings-v2-logo-box">
                <img src="{{ $logoUrl }}" alt="Current application logo" id="app-logo-preview">
              </div>

              <div class="app-settings-v2-field">
                <label for="app_logo">Application logo</label>
                <input type="file" name="app_logo" id="app_logo" accept=".png,.jpg,.jpeg,.gif,.bmp,.webp,image/png,image/jpeg,image/gif,image/bmp,image/webp">
                <small>PNG, JPG, GIF, BMP, or WebP up to 2 MB. Current file: {{ $logoFile }}</small>
                <span class="app-settings-v2-error" data-error-for="app_logo"></span>
              </div>
            </div>

            <div class="app-settings-v2-fields app-settings-v2-fields-2">
              <div class="app-settings-v2-field">
                <label for="app_name">Application name <span>*</span></label>
                <input type="text" name="app_name" id="app_name" value="{{ $settings['APP_NAME'] }}" maxlength="120" required>
                <span class="app-settings-v2-error" data-error-for="app_name"></span>
              </div>

              <div class="app-settings-v2-field">
                <label for="app_currency">Default currency <span>*</span></label>
                <select name="app_currency" id="app_currency" required>
                  @foreach($currencies as $code => $currency)
                    <option value="{{ $code }}" {{ $settings['DEFAULT_CURRENCY'] == $code ? 'selected' : '' }}>{{ $currency }} ({{ $code }})</option>
                  @endforeach
                </select>
                <span class="app-settings-v2-error" data-error-for="app_currency"></span>
              </div>

              <div class="app-settings-v2-field">
                <label for="app_lang">Default language <span>*</span></label>
                <select name="app_lang" id="app_lang" required>
                  @foreach($languages as $language)
                    <option value="{{ $language['folder'] }}" {{ $settings['DEFAULT_LANG'] == $language['folder'] ? 'selected' : '' }}>{{ $language['name'] }}</option>
                  @endforeach
                </select>
                <span class="app-settings-v2-error" data-error-for="app_lang"></span>
              </div>

              <div class="app-settings-v2-field">
                <label for="app_timezone">Default timezone <span>*</span></label>
                <select name="app_timezone" id="app_timezone" required>
                  @foreach($timezones as $timezone)
                    <option value="{{ $timezone }}" {{ $settings['DEFAULT_TIMEZONE'] == $timezone ? 'selected' : '' }}>{{ $timezone }}</option>
                  @endforeach
                </select>
                <span class="app-settings-v2-error" data-error-for="app_timezone"></span>
              </div>
            </div>
          </section>

          <section class="app-settings-v2-panel app-settings-v2-panel-wide">
            <div class="app-settings-v2-panel-head">
              <i class="fa fa-commenting-o" aria-hidden="true"></i>
              <div class="app-settings-v2-panel-title">
                <h4>Dashboard welcome</h4>
                <p>Editable retailer welcome copy shown on the dashboard intro banner.</p>
              </div>
            </div>

            <div class="app-settings-v2-fields app-settings-v2-fields-2">
              <div class="app-settings-v2-field">
                <label for="dashboard_welcome_prefix">Welcome prefix</label>
                <input type="text" name="dashboard_welcome_prefix" id="dashboard_welcome_prefix" value="{{ $settings['DASHBOARD_WELCOME_PREFIX'] }}" maxlength="120" placeholder="Hello">
                <span class="app-settings-v2-error" data-error-for="dashboard_welcome_prefix"></span>
              </div>

              <div class="app-settings-v2-field">
                <label for="dashboard_welcome_subtitle">Welcome subtitle</label>
                <input type="text" name="dashboard_welcome_subtitle" id="dashboard_welcome_subtitle" value="{{ $settings['DASHBOARD_WELCOME_SUBTITLE'] }}" maxlength="255" placeholder="Welcome to your retailer workspace.">
                <span class="app-settings-v2-error" data-error-for="dashboard_welcome_subtitle"></span>
              </div>

              <div class="app-settings-v2-field">
                <label for="dashboard_feature_secure">Feature line 1</label>
                <textarea name="dashboard_feature_secure" id="dashboard_feature_secure" rows="2" maxlength="255" placeholder="Secure transactions">{{ $settings['DASHBOARD_FEATURE_SECURE'] }}</textarea>
                <span class="app-settings-v2-error" data-error-for="dashboard_feature_secure"></span>
              </div>

              <div class="app-settings-v2-field">
                <label for="dashboard_feature_instant">Feature line 2</label>
                <textarea name="dashboard_feature_instant" id="dashboard_feature_instant" rows="2" maxlength="255" placeholder="Instant access&#10;to services">{{ $settings['DASHBOARD_FEATURE_INSTANT'] }}</textarea>
                <span class="app-settings-v2-error" data-error-for="dashboard_feature_instant"></span>
              </div>

              <div class="app-settings-v2-field app-settings-v2-field-wide">
                <label for="dashboard_feature_support">Feature line 3</label>
                <textarea name="dashboard_feature_support" id="dashboard_feature_support" rows="2" maxlength="255" placeholder="Dedicated support&#10;24/7">{{ $settings['DASHBOARD_FEATURE_SUPPORT'] }}</textarea>
                <span class="app-settings-v2-error" data-error-for="dashboard_feature_support"></span>
              </div>
            </div>
          </section>
        </section>

        <section class="app-settings-v2-section" id="settings-appearance">
          <div class="app-settings-v2-section-head">
            <div>
              <span class="app-settings-v2-section-kicker">Section 02</span>
              <h3>Appearance</h3>
              <p>Preset palettes and grouped light-dark color controls for the entire V2 shell.</p>
            </div>
          </div>

          <section class="app-settings-v2-panel app-settings-v2-panel-wide">
            <div class="app-settings-v2-panel-head">
              <i class="fa fa-eyedropper" aria-hidden="true"></i>
              <div class="app-settings-v2-panel-title">
                <h4>Theme colors</h4>
                <p>Start from a preset, then fine-tune the palette by area instead of editing one continuous color wall.</p>
              </div>
            </div>

            <div class="app-settings-v2-theme-presets" aria-label="Theme presets">
              @foreach($themePresets as $presetKey => $preset)
                <button type="button"
                  class="app-settings-v2-preset"
                  data-theme-preset="{{ $presetKey }}"
                  data-theme-values='@json($preset['colors'])'>
                  <span class="app-settings-v2-preset-swatches" aria-hidden="true">
                    <span style="background: {{ $preset['colors']['theme_primary_color'] }}"></span>
                    <span style="background: {{ $preset['colors']['theme_sidebar_active_color'] }}"></span>
                    <span style="background: {{ $preset['colors']['theme_accent_color'] }}"></span>
                  </span>
                  <span>{{ $preset['label'] }}</span>
                </button>
              @endforeach
            </div>

            <div class="app-settings-v2-appearance-switch" role="tablist" aria-label="Appearance mode">
              <button type="button"
                class="app-settings-v2-appearance-tab is-active"
                id="appearance-light-tab"
                role="tab"
                aria-selected="true"
                aria-controls="appearance-theme-groups"
                data-appearance-mode="light">
                <i class="fa fa-sun-o" aria-hidden="true"></i>
                <span>Light mode</span>
              </button>
              <button type="button"
                class="app-settings-v2-appearance-tab"
                id="appearance-dark-tab"
                role="tab"
                aria-selected="false"
                aria-controls="appearance-theme-groups"
                data-appearance-mode="dark">
                <i class="fa fa-moon-o" aria-hidden="true"></i>
                <span>Dark mode</span>
              </button>
            </div>

            <div class="app-settings-v2-theme-groups" id="appearance-theme-groups">
              @foreach($themeFieldGroups as $themeGroup)
                <fieldset class="app-settings-v2-theme-group{{ $themeGroup['mode'] === 'dark' ? ' is-mode-hidden' : '' }}"
                  data-theme-mode="{{ $themeGroup['mode'] }}">
                  <legend>{{ $themeGroup['title'] }}</legend>
                  <p>{{ $themeGroup['copy'] }}</p>

                  <div class="app-settings-v2-color-grid app-settings-v2-color-grid-group">
                    @foreach($themeGroup['fields'] as $themeFieldName)
                      @php
                        $themeField = $themeFieldLookup[$themeFieldName];
                        $themeValue = strtoupper($settings[$themeField['setting']] ?? '#1764A8');
                      @endphp
                      <div class="app-settings-v2-field app-settings-v2-color-field">
                        <label for="{{ $themeField['name'] }}">{{ $themeField['label'] }} <span>*</span></label>
                        <div class="app-settings-v2-color-control">
                          <input
                            class="app-settings-v2-color-input"
                            type="color"
                            id="{{ $themeField['name'] }}"
                            name="{{ $themeField['name'] }}"
                            value="{{ $themeValue }}"
                            data-color-target="{{ $themeField['name'] }}_text">
                          <input
                            class="app-settings-v2-color-text"
                            type="text"
                            id="{{ $themeField['name'] }}_text"
                            value="{{ $themeValue }}"
                            maxlength="7"
                            inputmode="text"
                            data-color-source="{{ $themeField['name'] }}"
                            aria-label="{{ $themeField['label'] }} hex color">
                        </div>
                        <span class="app-settings-v2-error" data-error-for="{{ $themeField['name'] }}"></span>
                      </div>
                    @endforeach
                  </div>
                </fieldset>
              @endforeach
            </div>
          </section>
        </section>

        <section class="app-settings-v2-section" id="settings-operations">
          <div class="app-settings-v2-section-head">
            <div>
              <span class="app-settings-v2-section-kicker">Section 03</span>
              <h3>Operations</h3>
              <p>Feature switches, record defaults, and numbering rules used by daily workflows.</p>
            </div>
          </div>

          <div class="app-settings-v2-layout app-settings-v2-layout-operations">
            <section class="app-settings-v2-panel">
              <div class="app-settings-v2-panel-head">
                <i class="fa fa-toggle-on" aria-hidden="true"></i>
                <div class="app-settings-v2-panel-title">
                  <h4>Switches</h4>
                  <p>Enable or disable communication and language features.</p>
                </div>
              </div>

              <div class="app-settings-v2-switch-list">
                <label class="app-settings-v2-switch">
                  <input type="checkbox" name="enable_multi_lang" value="1" {{ (int) $settings['ENABLE_MULTI_LANG'] === 1 ? 'checked' : '' }}>
                  <span></span>
                  <strong>Multi language</strong>
                </label>

                <label class="app-settings-v2-switch">
                  <input type="checkbox" name="enable_email" value="1" {{ (int) $settings['ENABLE_EMAIL'] === 1 ? 'checked' : '' }}>
                  <span></span>
                  <strong>Email notifications</strong>
                </label>

                <label class="app-settings-v2-switch">
                  <input type="checkbox" name="enable_slack" value="1" {{ (int) $settings['ENABLE_SLACK'] === 1 ? 'checked' : '' }}>
                  <span></span>
                  <strong>Slack alerts</strong>
                </label>
              </div>
            </section>

            <section class="app-settings-v2-panel">
              <div class="app-settings-v2-panel-head">
                <i class="fa fa-list-ol" aria-hidden="true"></i>
                <div class="app-settings-v2-panel-title">
                  <h4>Records</h4>
                  <p>Default record sorting and list volume across the application.</p>
                </div>
              </div>

              <div class="app-settings-v2-fields">
                <div class="app-settings-v2-field">
                  <label for="per_page">Records per page <span>*</span></label>
                  <select name="per_page" id="per_page" required>
                    @foreach([10,25,50,100,150,200,250,500] as $size)
                      <option value="{{ $size }}" {{ (int) $settings['PER_PAGE'] === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                  </select>
                  <span class="app-settings-v2-error" data-error-for="per_page"></span>
                </div>

                <div class="app-settings-v2-field">
                  <label for="record_order">Default order <span>*</span></label>
                  <select name="record_order" id="record_order" required>
                    <option value="ASC" {{ $settings['RECORD_ORDER_BY'] === 'ASC' ? 'selected' : '' }}>Ascending</option>
                    <option value="DESC" {{ $settings['RECORD_ORDER_BY'] === 'DESC' ? 'selected' : '' }}>Descending</option>
                  </select>
                  <span class="app-settings-v2-error" data-error-for="record_order"></span>
                </div>

                <div class="app-settings-v2-field">
                  <label for="record_method">Record method <span>*</span></label>
                  <select name="record_method" id="record_method" required>
                    @foreach($recordMethods as $key => $method)
                      <option value="{{ $key }}" {{ $settings['DEFAULT_RECORD_METHOD'] == $key ? 'selected' : '' }}>{{ $method }}</option>
                    @endforeach
                  </select>
                  <span class="app-settings-v2-error" data-error-for="record_method"></span>
                </div>
              </div>
            </section>

            <section class="app-settings-v2-panel app-settings-v2-panel-wide">
              <div class="app-settings-v2-panel-head">
                <i class="fa fa-hashtag" aria-hidden="true"></i>
                <div class="app-settings-v2-panel-title">
                  <h4>Prefixes</h4>
                  <p>Identifiers and numeric limits used in order and transaction flows.</p>
                </div>
              </div>

              <div class="app-settings-v2-fields">
                <div class="app-settings-v2-field">
                  <label for="order_prefix">Order prefix</label>
                  <input type="text" name="order_prefix" id="order_prefix" value="{{ $settings['ORDER_PREFIX'] }}" maxlength="50">
                </div>

                <div class="app-settings-v2-field">
                  <label for="transaction_prefix">Transaction prefix</label>
                  <input type="text" name="transaction_prefix" id="transaction_prefix" value="{{ $settings['TRANSACTION_PREFIX'] }}" maxlength="50">
                </div>

                <div class="app-settings-v2-fields app-settings-v2-fields-2">
                  <div class="app-settings-v2-field">
                    <label for="admin_limit">Admin limit</label>
                    <input type="number" name="admin_limit" id="admin_limit" value="{{ $settings['ADMIN_LIMIT'] }}" min="0" max="999999">
                    <span class="app-settings-v2-error" data-error-for="admin_limit"></span>
                  </div>

                  <div class="app-settings-v2-field">
                    <label for="manager_limit">Manager limit</label>
                    <input type="number" name="manager_limit" id="manager_limit" value="{{ $settings['MANAGER_LIMIT'] }}" min="0" max="999999">
                    <span class="app-settings-v2-error" data-error-for="manager_limit"></span>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </section>

        <section class="app-settings-v2-section" id="settings-integration">
          <div class="app-settings-v2-section-head">
            <div>
              <span class="app-settings-v2-section-kicker">Section 04</span>
              <h3>Integration</h3>
              <p>Endpoints, tokens, design format, and gateway credentials used by external services.</p>
            </div>
          </div>

          <section class="app-settings-v2-panel app-settings-v2-panel-wide">
            <div class="app-settings-v2-panel-head">
              <i class="fa fa-plug" aria-hidden="true"></i>
              <div class="app-settings-v2-panel-title">
                <h4>Integration</h4>
                <p>API access and external bus configuration in one isolated block.</p>
              </div>
            </div>

            <div class="app-settings-v2-fields app-settings-v2-fields-2">
              <div class="app-settings-v2-field">
                <label for="payment_emails">Payment emails</label>
                <textarea name="payment_emails" id="payment_emails" rows="3">{{ $settings['PAYMENT_EMAILS'] }}</textarea>
                <span class="app-settings-v2-error" data-error-for="payment_emails"></span>
              </div>

              <div class="app-settings-v2-field">
                <label for="api_token">API token</label>
                <input type="text" name="api_token" id="api_token" value="{{ $settings['API_TOKEN'] }}" maxlength="500">
                <span class="app-settings-v2-error" data-error-for="api_token"></span>
              </div>

              <div class="app-settings-v2-field">
                <label for="api_end_point">API endpoint</label>
                <input type="text" name="api_end_point" id="api_end_point" value="{{ $settings['API_END_POINT'] }}" maxlength="500">
                <span class="app-settings-v2-error" data-error-for="api_end_point"></span>
              </div>

              <div class="app-settings-v2-field">
                <label for="bus_v2_design">Bus design format <span>*</span></label>
                <select name="bus_v2_design" id="bus_v2_design" required>
                  <option value="standard" {{ $settings['BUS_V2_DESIGN'] === 'standard' ? 'selected' : '' }}>Design 1 - Current Bus</option>
                  <option value="desk" {{ $settings['BUS_V2_DESIGN'] === 'desk' ? 'selected' : '' }}>Design 2 - Travel Desk</option>
                </select>
                <span class="app-settings-v2-error" data-error-for="bus_v2_design"></span>
              </div>

              <div class="app-settings-v2-field">
                <label for="comcod">COMCOD</label>
                <input type="text" name="comcod" id="comcod" value="{{ $settings['COMCOD'] }}" maxlength="155">
                <span class="app-settings-v2-error" data-error-for="comcod"></span>
              </div>

              <div class="app-settings-v2-field">
                <label for="tpvcod">TPVCOD</label>
                <input type="text" name="tpvcod" id="tpvcod" value="{{ $settings['TPVCOD'] }}" maxlength="155">
                <span class="app-settings-v2-error" data-error-for="tpvcod"></span>
              </div>

              <div class="app-settings-v2-field app-settings-v2-field-wide">
                <label for="authorization">Authorization</label>
                <textarea name="authorization" id="authorization" rows="3" maxlength="1000">{{ $settings['AUTHORIZATION'] }}</textarea>
                <span class="app-settings-v2-error" data-error-for="authorization"></span>
              </div>
            </div>
          </section>
        </section>
      </div>
    </div>

    <div class="app-settings-v2-actions">
      <div class="app-settings-v2-action-state">
        <span class="app-settings-v2-dirty" id="settings-dirty-badge">
          <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
          Unsaved changes
        </span>
        <span id="settings-save-state">Ready to save changes.</span>
      </div>

      <div class="app-settings-v2-action-buttons">
        <a href="{{ url('clear') }}" class="app-settings-v2-btn secondary">
          <i class="fa fa-refresh" aria-hidden="true"></i>
          <span>Clear config</span>
        </a>

        <button type="submit" class="app-settings-v2-btn primary" id="settings-save-btn">
          <i class="fa fa-save" aria-hidden="true"></i>
          <span>Save settings</span>
        </button>
      </div>
    </div>
  </form>
</div>
