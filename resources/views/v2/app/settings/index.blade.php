@extends('v2.layout.simple.master')

@section('css')
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/v2/app-settings-v2.css') }}">
@endsection

@include('v2.layout.simple.breadcrumb', ['data' => [
  ['name' => 'Application Settings', 'url' => '', 'active' => 'yes']
]])

@section('content')
@php
  $languages = [
    ['folder' => 'en', 'name' => 'English'],
    ['folder' => 'fr', 'name' => 'French'],
  ];
  $logoFile = $settings['APP_LOGO'] ?: 'logo_inverse.png';
  $logoExists = file_exists(public_path('images/' . $logoFile));
  $logoUrl = asset('images/' . ($logoExists ? $logoFile : 'logo.png'));
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

  <div class="app-settings-v2-hero">
    <div class="app-settings-v2-title-wrap">
      <span class="app-settings-v2-emblem"><i class="fa fa-cog" aria-hidden="true"></i></span>
      <div>
        <h1 class="app-settings-v2-title">Application Control Center</h1>
        <p class="app-settings-v2-subtitle">Manage branding, localization, records, notifications, limits, and integration credentials from one V2 workspace.</p>
      </div>
    </div>
    <div class="app-settings-v2-hero-actions">
      <span class="app-settings-v2-dirty" id="settings-dirty-badge">
        <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
        Unsaved changes
      </span>
      <a href="{{ url('clear') }}" class="app-settings-v2-btn soft">
        <i class="fa fa-refresh" aria-hidden="true"></i>
        <span>Clear config</span>
      </a>
    </div>
  </div>

  <form class="app-settings-v2-form" id="app-settings-v2-form" action="{{ route('app-settings.v2.save') }}" method="POST" enctype="multipart/form-data" novalidate>
    {{ csrf_field() }}

    <div class="app-settings-v2-grid">
      <section class="app-settings-v2-card app-settings-v2-card-wide">
        <div class="app-settings-v2-card-head">
          <span class="app-settings-v2-card-icon blue"><i class="fa fa-building" aria-hidden="true"></i></span>
          <div>
            <h2>Brand and workspace</h2>
            <p>Visible name, logo, language, currency, and timezone defaults.</p>
          </div>
        </div>

        <div class="app-settings-v2-brand">
          <div class="app-settings-v2-logo-box">
            <img src="{{ $logoUrl }}" alt="Current application logo" id="app-logo-preview">
          </div>
          <div class="app-settings-v2-field grow">
            <label for="app_logo">Application logo</label>
            <input type="file" name="app_logo" id="app_logo" accept="image/*">
            <small>PNG, JPG, GIF, or WEBP. Current saved file: {{ $logoFile }}</small>
            <span class="app-settings-v2-error" data-error-for="app_logo"></span>
          </div>
        </div>

        <div class="app-settings-v2-fields two">
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

      <section class="app-settings-v2-card">
        <div class="app-settings-v2-card-head">
          <span class="app-settings-v2-card-icon green"><i class="fa fa-toggle-on" aria-hidden="true"></i></span>
          <div>
            <h2>Feature switches</h2>
            <p>Enable optional platform services.</p>
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

      <section class="app-settings-v2-card">
        <div class="app-settings-v2-card-head">
          <span class="app-settings-v2-card-icon purple"><i class="fa fa-list-ol" aria-hidden="true"></i></span>
          <div>
            <h2>Record defaults</h2>
            <p>Pagination, ordering, and record calculation mode.</p>
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

      <section class="app-settings-v2-card">
        <div class="app-settings-v2-card-head">
          <span class="app-settings-v2-card-icon orange"><i class="fa fa-hashtag" aria-hidden="true"></i></span>
          <div>
            <h2>Prefixes and limits</h2>
            <p>Operational codes and upload/banner limits.</p>
          </div>
        </div>
        <div class="app-settings-v2-fields two">
          <div class="app-settings-v2-field">
            <label for="order_prefix">Order prefix</label>
            <input type="text" name="order_prefix" id="order_prefix" value="{{ $settings['ORDER_PREFIX'] }}" maxlength="50">
          </div>
          <div class="app-settings-v2-field">
            <label for="transaction_prefix">Transaction prefix</label>
            <input type="text" name="transaction_prefix" id="transaction_prefix" value="{{ $settings['TRANSACTION_PREFIX'] }}" maxlength="50">
          </div>
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
      </section>

      <section class="app-settings-v2-card app-settings-v2-card-wide">
        <div class="app-settings-v2-card-head">
          <span class="app-settings-v2-card-icon cyan"><i class="fa fa-plug" aria-hidden="true"></i></span>
          <div>
            <h2>Integration and notifications</h2>
            <p>API endpoint, token, payment recipients, bus format, and provider codes.</p>
          </div>
        </div>
        <div class="app-settings-v2-fields two">
          <div class="app-settings-v2-field">
            <label for="payment_emails">Payment emails</label>
            <textarea name="payment_emails" id="payment_emails" rows="4">{{ $settings['PAYMENT_EMAILS'] }}</textarea>
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
          <div class="app-settings-v2-field full">
            <label for="authorization">Authorization</label>
            <textarea name="authorization" id="authorization" rows="3" maxlength="1000">{{ $settings['AUTHORIZATION'] }}</textarea>
            <span class="app-settings-v2-error" data-error-for="authorization"></span>
          </div>
        </div>
      </section>
    </div>

    <div class="app-settings-v2-sticky-actions">
      <div>
        <strong>Application Settings</strong>
        <span id="settings-save-state">Ready to save changes.</span>
      </div>
      <button type="submit" class="app-settings-v2-btn primary" id="settings-save-btn">
        <i class="fa fa-save" aria-hidden="true"></i>
        <span>Save settings</span>
      </button>
    </div>
  </form>
</div>

@section('script')
<script src="{{ asset('js/v2/app-settings-v2.js') }}"></script>
@endsection
