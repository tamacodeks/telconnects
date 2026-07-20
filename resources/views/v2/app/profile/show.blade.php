@extends('v2.layout.simple.master')

@section('body_class', 'profile-v2-active')

@php
  $profileUser = auth()->user();
  $profileLocale = session('locale', 'en') === 'fr' ? 'fr' : 'en';
  app()->setLocale($profileLocale);
  $profileText = trans('profile_v2');

  $page_title = $profileText['title'];
  $profileName = trim((string) (optional($profileUser)->username ?: optional($profileUser)->name ?: 'User'));
  $profileFullName = trim((string) (trim((string) optional($profileUser)->first_name . ' ' . (string) optional($profileUser)->last_name) ?: $profileName));
  $profileInitial = strtoupper(substr($profileName, 0, 1));
  $profileGroupId = (int) optional($profileUser)->group_id;
  $profileGroup = optional(optional($profileUser)->group)->name ?: optional(\App\Models\UserGroup::find($profileGroupId))->name ?: 'Member';
  $profileImage = isset($user_image) ? (string) $user_image : 'images/avatar.png';
  $profileFallbackImageUrl = asset('images/avatar.png');
  $profileImageUrl = trim($profileImage) !== ''
    ? (preg_match('/^https?:\/\//i', $profileImage) ? $profileImage : asset(ltrim($profileImage, '/')))
    : $profileFallbackImageUrl;
  $profileReadonlyContact = $profileGroupId === 4;
  $profileIsActive = (int) optional($profileUser)->status === 1;
  $profileTwoFactorEnabled = (int) optional($profileUser)->enable_2fa === 1;
  $profileCurrency = optional($profileUser)->currency ?: 'EUR';

  $formatProfileDate = function ($value, $format = 'd M Y') {
    if (empty($value)) {
      return '-';
    }

    try {
      return \Illuminate\Support\Carbon::parse($value)->format($format);
    } catch (\Exception $e) {
      return (string) $value;
    }
  };

  $profileCreatedAt = $formatProfileDate(optional($profileUser)->created_at, 'd M Y');
  $profileLastActivity = $formatProfileDate(optional($profileUser)->last_activity ?: optional($profileUser)->updated_at, 'd M Y, h:i A');
  $profileUpdatedAt = $formatProfileDate(optional($profileUser)->updated_at, 'd M Y, h:i A');

  $toProfileNumber = function ($value, $fallback = 0.0) {
    if ($value === null || $value === '') {
      return $fallback;
    }

    if (is_numeric($value)) {
      return (float) $value;
    }

    $normalized = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', strip_tags((string) $value)));
    return is_numeric($normalized) ? (float) $normalized : $fallback;
  };

  $formatProfileMoney = function ($amount, $emptyText = null) use ($profileCurrency, $profileText, $toProfileNumber) {
    if ($amount === null || $amount === '') {
      return $emptyText ?: $profileText['not_set'];
    }

    try {
      return \app\Library\AppHelper::formatAmount($profileCurrency, $toProfileNumber($amount, 0));
    } catch (\Exception $e) {
      return number_format($toProfileNumber($amount, 0), 2, '.', ',') . ' ' . $profileCurrency;
    }
  };

  $formatProfilePercent = function ($amount) use ($profileText, $toProfileNumber) {
    if ($amount === null || $amount === '') {
      return $profileText['not_configured'];
    }

    $value = $toProfileNumber($amount, null);
    if ($value === null) {
      return $profileText['not_configured'];
    }

    $formatted = number_format($value, 2, '.', '');
    return rtrim(rtrim($formatted, '0'), '.') . '%';
  };

  $isValidCommission = function ($value) use ($toProfileNumber) {
    if ($value === null || $value === '') {
      return false;
    }

    $number = $toProfileNumber($value, null);
    return $number !== null && $number >= 0 && $number <= 100;
  };

  $profileBalanceAmount = 0.0;
  $profileCreditAmount = null;
  $profileDailyLimitAmount = null;
  $profileTodaySpendAmount = 0.0;
  $profileRemainingAmount = null;

  if (in_array($profileGroupId, [3, 4], true)) {
    $profileBalanceAmount = $toProfileNumber(\app\Library\AppHelper::getBalance($profileUser->id, $profileCurrency, false), 0);
    $creditLimitRow = \App\Models\CreditLimit::where('user_id', $profileUser->id)->orderBy('id', 'DESC')->first();
    $profileCreditAmount = optional($creditLimitRow)->credit_limit;
  } elseif ($profileGroupId === 2) {
    $profileBalanceAmount = $toProfileNumber(\app\Library\AppHelper::getAdminBalance(false), 0);
    $profileCreditAmount = $toProfileNumber(\app\Library\AppHelper::getAdminBalance(false, true), null);
  }

  if ($profileGroupId === 4) {
    $dailyLimitRow = \App\Models\DailyLimit::where('user_id', $profileUser->id)->orderBy('id', 'DESC')->first();
    $profileDailyLimitAmount = optional($dailyLimitRow)->daily_limit;

    if ($profileDailyLimitAmount !== null && $profileDailyLimitAmount !== '') {
      $todayStart = \Illuminate\Support\Carbon::today()->startOfDay();
      $todayEnd = \Illuminate\Support\Carbon::today()->endOfDay();
      $profileTodaySpendAmount = (float) \App\Models\Transaction::where('user_id', $profileUser->id)
        ->where('type', 'debit')
        ->where(function ($query) use ($todayStart, $todayEnd) {
          $query->whereBetween('created_at', [$todayStart, $todayEnd])
            ->orWhereBetween('date', [$todayStart, $todayEnd]);
        })
        ->sum('amount');

      $profileRemainingAmount = max(0, $toProfileNumber($profileDailyLimitAmount, 0) - $profileTodaySpendAmount);
    }
  }

  $balanceValue = $formatProfileMoney($profileBalanceAmount);
  $creditValue = $profileCreditAmount === null || $profileCreditAmount === ''
    ? $profileText['not_set']
    : $formatProfileMoney($profileCreditAmount);
  $dailyValue = $profileDailyLimitAmount === null || $profileDailyLimitAmount === ''
    ? $profileText['not_set']
    : $formatProfileMoney($profileDailyLimitAmount);
  $spentTodayValue = $formatProfileMoney($profileTodaySpendAmount);
  $remainingValue = $profileRemainingAmount === null
    ? $profileText['not_set']
    : $formatProfileMoney($profileRemainingAmount);

  $moneyTone = function ($amount, $fallback = 'blue') use ($toProfileNumber) {
    return $toProfileNumber($amount, 0) < 0 ? 'danger' : $fallback;
  };

  $profileStats = [
    [
      'label' => $profileText['current_balance'],
      'value' => $balanceValue,
      'caption' => $moneyTone($profileBalanceAmount) === 'danger' ? $profileText['negative_balance'] : $profileText['available_balance'],
      'tone' => $moneyTone($profileBalanceAmount, 'green'),
      'icon' => 'fa-wallet',
    ],
    [
      'label' => $profileText['credit_limit'],
      'value' => $creditValue,
      'caption' => $profileText['credit_caption'],
      'tone' => 'blue',
      'icon' => 'fa-credit-card',
    ],
    [
      'label' => $profileText['daily_limit'],
      'value' => $dailyValue,
      'caption' => $profileDailyLimitAmount === null || $profileDailyLimitAmount === '' ? $profileText['not_set'] : $profileText['daily_caption'],
      'tone' => 'orange',
      'icon' => 'fa-calendar-plus',
    ],
    [
      'label' => $profileText['remaining_today'],
      'value' => $remainingValue,
      'caption' => $profileRemainingAmount === null ? $profileText['not_set'] : $profileText['used_today'] . ': ' . $spentTodayValue,
      'tone' => $moneyTone($profileRemainingAmount, 'green'),
      'icon' => 'fa-briefcase',
    ],
  ];

  $profileIsRetailer = $profileGroupId === 4;
  $profileCanEditCommission = in_array($profileGroupId, [1, 2, 3], true);
  $profileCommissionRows = [];

  foreach (($services ?? []) as $service) {
    $serviceEnabled = \app\Library\AppHelper::user_access($service->id, $profileUser->id) == 1;

    if ($profileIsRetailer && !$serviceEnabled) {
      continue;
    }

    $userCommission = \App\Models\Commission::where('user_id', $profileUser->id)->where('service_id', $service->id)->first();
    $appCommission = \App\Models\AppCommission::where('service_id', $service->id)->first();
    $commissionValue = null;
    $commissionSource = $profileText['not_configured'];
    $commissionUpdatedAt = optional($service)->updated_at ?: optional($service)->created_at;

    if ($isValidCommission(optional($userCommission)->commission)) {
      $commissionValue = optional($userCommission)->commission;
      $commissionSource = $profileText['custom_rate'];
      $commissionUpdatedAt = optional($userCommission)->updated_at ?: optional($userCommission)->created_at ?: $commissionUpdatedAt;
    } else {
      $fallbackFields = $profileGroupId === 2
        ? ['commission', 'user_def_commission']
        : ($profileGroupId === 3 ? ['mgr_def_com', 'user_def_commission', 'commission'] : ['retailer_def_com', 'user_def_commission', 'commission']);

      foreach ($fallbackFields as $fallbackField) {
        if ($isValidCommission(optional($appCommission)->{$fallbackField})) {
          $commissionValue = optional($appCommission)->{$fallbackField};
          $commissionSource = $profileText['default_rate'];
          $commissionUpdatedAt = optional($appCommission)->updated_at ?: optional($appCommission)->created_at ?: $commissionUpdatedAt;
          break;
        }
      }
    }

    $profileCommissionRows[] = [
      'service' => $service,
      'enabled' => $serviceEnabled,
      'commission' => $formatProfilePercent($commissionValue),
      'commission_is_configured' => $commissionValue !== null,
      'source' => $commissionSource,
      'updated_at' => $formatProfileDate($commissionUpdatedAt, 'd M Y, h:i A'),
    ];
  }

  $profileCommissionEditUrl = in_array($profileGroupId, [1, 2], true) ? url('service-commissions') : url('service-access');
  $profileEmailVerified = !empty(optional($profileUser)->email_verified_at);
  $profilePhoneVerified = !empty(optional($profileUser)->mobile_verified_at) || !empty(optional($profileUser)->phone_verified_at);
  $profileDetailGroups = [
    [
      'title' => $profileText['account_details'],
      'icon' => 'fa-id-card',
      'items' => [
        ['label' => $profileText['username'], 'value' => $profileName],
        ['label' => $profileText['account_type'], 'value' => $profileGroup],
        ['label' => $profileText['currency'], 'value' => $profileCurrency],
      ],
    ],
    [
      'title' => $profileText['security_details'],
      'icon' => 'fa-shield-alt',
      'items' => [
        [
          'label' => $profileText['two_factor'],
          'value' => $profileTwoFactorEnabled ? $profileText['enabled'] : $profileText['disabled'],
          'tone' => $profileTwoFactorEnabled ? 'success' : 'warning',
        ],
        [
          'label' => $profileText['email_verified'],
          'value' => $profileEmailVerified ? $profileText['verified'] : $profileText['not_set'],
          'tone' => $profileEmailVerified ? 'success' : 'muted',
        ],
        [
          'label' => $profileText['phone_verified'],
          'value' => $profilePhoneVerified ? $profileText['verified'] : $profileText['not_set'],
          'tone' => $profilePhoneVerified ? 'success' : 'muted',
        ],
        [
          'label' => $profileText['login_protection'],
          'value' => $profileTwoFactorEnabled ? $profileText['secure'] : $profileText['needs_setup'],
          'tone' => $profileTwoFactorEnabled ? 'success' : 'warning',
          'action' => !$profileTwoFactorEnabled ? ['url' => route('enable-2fa'), 'label' => $profileText['setup_2fa']] : null,
        ],
      ],
    ],
    [
      'title' => $profileText['limit_details'],
      'icon' => 'fa-wallet',
      'items' => [
        ['label' => $profileText['wallet_balance'], 'value' => $balanceValue, 'tone' => $moneyTone($profileBalanceAmount) === 'danger' ? 'danger' : null],
        ['label' => $profileText['credit_limit'], 'value' => $creditValue],
        ['label' => $profileText['daily_limit'], 'value' => $dailyValue],
        ['label' => $profileText['today_spend'], 'value' => $spentTodayValue],
        ['label' => $profileText['remaining_today'], 'value' => $remainingValue],
      ],
    ],
    [
      'title' => $profileText['activity_details'],
      'icon' => 'fa-history',
      'items' => [
        ['label' => $profileText['account_created'], 'value' => $profileCreatedAt],
        ['label' => $profileText['last_login'], 'value' => $profileLastActivity],
        ['label' => $profileText['profile_updated'], 'value' => $profileUpdatedAt],
        [
          'label' => $profileText['mobile'],
          'value' => $profileReadonlyContact ? $profileText['managed_contact'] : $profileText['editable_contact'],
          'tone' => $profileReadonlyContact ? 'muted' : 'success',
        ],
      ],
    ],
  ];
@endphp

@include('v2.layout.simple.breadcrumb', ['data' => [
  ['name' => $profileText['breadcrumb'], 'url' => '', 'active' => 'yes']
]])

@section('style')
<link href="{{ asset('vendor/intl-input/css/intlTelInput.css') }}?v={{ filemtime(public_path('vendor/intl-input/css/intlTelInput.css')) }}" rel="stylesheet">
<style>
  :root {
    --iti-path-flags-1x: url('{{ asset('vendor/intl-input/img/flags.png') }}?v={{ filemtime(public_path('vendor/intl-input/img/flags.png')) }}');
    --iti-path-flags-2x: url('{{ asset('vendor/intl-input/img/flags@2x.png') }}?v={{ filemtime(public_path('vendor/intl-input/img/flags@2x.png')) }}');
  }
</style>
<link rel="stylesheet" href="{{ asset('css/v2/profile-v2.css') }}?v={{ filemtime(public_path('css/v2/profile-v2.css')) }}">
@endsection

@section('content')
<div class="container-fluid profile-v2-page">
  @if($errors->any())
    <div class="profile-v2-alert profile-v2-alert-warning">
      <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <section class="profile-v2-account-card">
    <div class="profile-v2-account-main">
      <div class="profile-v2-avatar-box">
        <img src="{{ $profileImageUrl }}" data-fallback-src="{{ $profileFallbackImageUrl }}" alt="" id="profileV2HeroImage">
        <span class="profile-v2-avatar-fallback">{{ $profileInitial }}</span>
        <label class="profile-v2-avatar-camera" for="profileV2Image" aria-label="{{ $profileText['change_image'] }}">
          <i class="fa fa-camera" aria-hidden="true"></i>
        </label>
      </div>
      <div class="profile-v2-account-title">
        <span>{{ $profileText['account'] }}</span>
        <h1>{{ $profileFullName }}</h1>
        <strong>{{ $profileGroup }} / {{ $profileName }}</strong>
      </div>
    </div>

    <div class="profile-v2-account-meta">
      <div class="profile-v2-meta-item">
        <span>{{ $profileText['status'] }}</span>
        <strong class="profile-v2-pill {{ $profileIsActive ? 'is-success' : 'is-muted' }}">{{ $profileIsActive ? $profileText['active'] : $profileText['inactive'] }}</strong>
      </div>
      <div class="profile-v2-meta-item">
        <span><i class="fa fa-calendar-alt" aria-hidden="true"></i>{{ $profileText['created'] }}</span>
        <strong>{{ $profileCreatedAt }}</strong>
      </div>
      <div class="profile-v2-meta-item">
        <span><i class="fa fa-clock" aria-hidden="true"></i>{{ $profileText['last_login'] }}</span>
        <strong>{{ $profileLastActivity }}</strong>
      </div>
      <div class="profile-v2-meta-item">
        <span><i class="fa fa-shield-alt" aria-hidden="true"></i>{{ $profileText['security'] }}</span>
        <strong class="profile-v2-security-meta">
          {{ $profileTwoFactorEnabled ? '2FA ' . $profileText['enabled'] : '2FA ' . $profileText['disabled'] }}
          @if(!$profileTwoFactorEnabled)
            <a href="{{ route('enable-2fa') }}">{{ $profileText['setup_2fa'] }}</a>
          @endif
        </strong>
      </div>
    </div>
  </section>

  <div class="profile-v2-stats">
    @foreach($profileStats as $stat)
      <section class="profile-v2-stat profile-v2-stat-{{ $stat['tone'] }}">
        <span class="profile-v2-stat-icon"><i class="fa {{ $stat['icon'] }}" aria-hidden="true"></i></span>
        <div>
          <span>{{ $stat['label'] }}</span>
          <strong>{{ $stat['value'] }}</strong>
          <small>{{ $stat['caption'] }}</small>
        </div>
      </section>
    @endforeach
  </div>

  <div class="profile-v2-main-grid">
    <div class="profile-v2-work-column">
      <section class="profile-v2-panel profile-v2-edit-panel">
      <div class="profile-v2-panel-head">
        <span class="profile-v2-panel-icon profile-v2-panel-icon-blue"><i class="fa fa-user-edit" aria-hidden="true"></i></span>
        <div>
          <h2>{{ $profileText['edit_profile'] }}</h2>
          <p>{{ $profileText['edit_caption'] }}</p>
        </div>
      </div>

      <form class="profile-v2-form" action="{{ url('user/edit/profile') }}" method="POST" enctype="multipart/form-data" id="profileV2Form" novalidate>
        {{ csrf_field() }}

        <div class="profile-v2-form-layout">
          <div class="profile-v2-image-field">
            <label>{{ $profileText['profile_image'] }}</label>
            <div class="profile-v2-image-preview">
              <img src="{{ $profileImageUrl }}" data-fallback-src="{{ $profileFallbackImageUrl }}" alt="" id="profileV2FormImage">
              <span>{{ $profileInitial }}</span>
            </div>
            <input class="profile-v2-file-input" type="file" name="image" id="profileV2Image" accept="image/*">
            <label class="profile-v2-upload-btn" for="profileV2Image">
              <i class="fa fa-upload" aria-hidden="true"></i>
              <span>{{ $profileText['change_image'] }}</span>
            </label>
            <small>{{ $profileText['image_help'] }}</small>
          </div>

          <div class="profile-v2-fields">
            <div class="profile-v2-form-row">
              <label class="profile-v2-field" for="profileV2FirstName">
                <span>{{ $profileText['first_name'] }}</span>
                <input type="text" name="first_name" id="profileV2FirstName" value="{{ old('first_name', $profileUser->first_name) }}">
              </label>
              <label class="profile-v2-field" for="profileV2LastName">
                <span>{{ $profileText['last_name'] }}</span>
                <input type="text" name="last_name" id="profileV2LastName" value="{{ old('last_name', $profileUser->last_name) }}">
              </label>
            </div>

            <div class="profile-v2-form-row">
              <label class="profile-v2-field {{ $profileReadonlyContact ? 'is-readonly' : '' }}" for="profileV2Email">
                <span class="profile-v2-field-label">
                  <span>{{ $profileText['email'] }}</span>
                  @if($profileReadonlyContact)
                    <em><i class="fa fa-lock" aria-hidden="true"></i>{{ $profileText['contact_locked'] }}</em>
                  @endif
                </span>
                <input type="email" name="email" id="profileV2Email" value="{{ old('email', $profileUser->email) }}" @if($profileReadonlyContact) readonly @endif required>
                @if($profileReadonlyContact)
                  <small class="profile-v2-help">{{ $profileText['contact_locked_hint'] }}</small>
                @endif
                @if($errors->has('email'))
                  <small class="profile-v2-error">{{ $errors->first('email') }}</small>
                @endif
              </label>
              <label class="profile-v2-field {{ $profileReadonlyContact ? 'is-readonly' : '' }}" for="profileV2Mobile">
                <span class="profile-v2-field-label">
                  <span>{{ $profileText['mobile'] }}</span>
                  @if($profileReadonlyContact)
                    <em><i class="fa fa-lock" aria-hidden="true"></i>{{ $profileText['contact_locked'] }}</em>
                  @endif
                </span>
                <input type="tel" name="mobile" id="profileV2Mobile" value="{{ old('mobile', '+' . $profileUser->mobile) }}" @if($profileReadonlyContact) readonly @endif required>
                <input type="hidden" id="profileV2CountryCode" value="">
                <input type="hidden" id="profileV2CountryIso" value="">
                @if($profileReadonlyContact)
                  <small class="profile-v2-help">{{ $profileText['contact_locked_hint'] }}</small>
                @endif
                <small id="profileV2MobileError" class="profile-v2-error d-none">{{ trans('users.error_mobile_no') }}</small>
                @if($errors->has('mobile'))
                  <small class="profile-v2-error">{{ $errors->first('mobile') }}</small>
                @endif
              </label>
            </div>

            <div class="profile-v2-form-row">
              <label class="profile-v2-field" for="profileV2Password">
                <span>{{ $profileText['new_password'] }}</span>
                <span class="profile-v2-password-wrap">
                  <input type="password" name="password" id="profileV2Password" value="" placeholder="{{ $profileText['password_placeholder'] }}" autocomplete="new-password">
                  <button type="button" class="profile-v2-password-toggle" data-target="profileV2Password" aria-label="{{ $profileText['new_password'] }}">
                    <i class="fa fa-eye-slash" aria-hidden="true"></i>
                  </button>
                </span>
              </label>
              <label class="profile-v2-field" for="profileV2PasswordConfirm">
                <span>{{ $profileText['confirm_password'] }}</span>
                <span class="profile-v2-password-wrap">
                  <input type="password" name="password_confirmation" id="profileV2PasswordConfirm" value="" placeholder="{{ $profileText['confirm_placeholder'] }}" autocomplete="new-password">
                  <button type="button" class="profile-v2-password-toggle" data-target="profileV2PasswordConfirm" aria-label="{{ $profileText['confirm_password'] }}">
                    <i class="fa fa-eye-slash" aria-hidden="true"></i>
                  </button>
                </span>
              </label>
            </div>

            <div class="profile-v2-form-note" id="profileV2PasswordNote">
              <i class="fa fa-info-circle" aria-hidden="true"></i>
              <span>{{ $profileText['password_help'] }}</span>
            </div>
            <div class="profile-v2-password-strength" id="profileV2PasswordStrength" aria-live="polite">
              <span><i></i></span>
              <strong>{{ $profileText['password_requirements'] }}</strong>
            </div>

            <div class="profile-v2-actions">
              <button type="reset" class="profile-v2-btn profile-v2-btn-ghost">{{ $profileText['cancel'] }}</button>
              <button type="submit" class="profile-v2-btn profile-v2-btn-primary" id="profileV2Submit">
                <i class="fa fa-lock" aria-hidden="true"></i>
                <span>{{ $profileText['save'] }}</span>
              </button>
            </div>
          </div>
        </div>
      </form>
      </section>

      <section class="profile-v2-panel profile-v2-commission-panel">
    <div class="profile-v2-commission-head">
      <div class="profile-v2-panel-head">
        <span class="profile-v2-panel-icon profile-v2-panel-icon-orange"><i class="fa fa-percent" aria-hidden="true"></i></span>
        <div>
          <h2>{{ $profileText['commissions'] }}</h2>
          <p>{{ $profileText['commission_caption'] }}</p>
        </div>
      </div>
      <a class="profile-v2-refresh-btn" href="{{ url()->current() }}">
        <i class="fa fa-sync-alt" aria-hidden="true"></i>
        <span>{{ $profileText['refresh'] }}</span>
      </a>
    </div>

    <div class="profile-v2-table-wrap">
      <table class="profile-v2-table">
        <thead>
          <tr>
            <th>{{ $profileText['service'] }}</th>
            <th>{{ $profileText['service_status'] }}</th>
            <th>{{ $profileText['commission'] }}</th>
            <th>{{ $profileText['commission_source'] }}</th>
            <th>{{ $profileText['last_updated'] }}</th>
            @if($profileCanEditCommission)
              <th>{{ $profileText['action'] }}</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @forelse($profileCommissionRows as $row)
            @php
              $service = $row['service'];
            @endphp
            <tr>
              <td>
                <span class="profile-v2-service-cell">
                  <strong>{{ $service->name }}</strong>
                  <small>ID {{ $service->id }}</small>
                </span>
              </td>
              <td>
                <span class="profile-v2-status {{ $row['enabled'] ? 'is-enabled' : 'is-disabled' }}">
                  {{ $row['enabled'] ? $profileText['enabled'] : $profileText['disabled'] }}
                </span>
              </td>
              <td>
                <span class="profile-v2-commission-value {{ $row['commission_is_configured'] ? '' : 'is-muted' }}">
                  {{ $row['commission'] }}
                </span>
              </td>
              <td><span class="profile-v2-source-pill">{{ $row['source'] }}</span></td>
              <td>{{ $row['updated_at'] }}</td>
              @if($profileCanEditCommission)
                <td>
                  <a class="profile-v2-table-action" href="{{ $profileCommissionEditUrl }}">
                    <i class="fa fa-pencil-alt" aria-hidden="true"></i>
                    <span>{{ $profileText['manage'] }}</span>
                  </a>
                </td>
              @endif
            </tr>
          @empty
            <tr>
              <td colspan="{{ 5 + ($profileCanEditCommission ? 1 : 0) }}">
                <div class="profile-v2-empty-state">
                  <i class="fa fa-percent" aria-hidden="true"></i>
                  <strong>{{ $profileText['no_services'] }}</strong>
                  <span>{{ $profileText['no_services_caption'] }}</span>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
      </section>
    </div>

    <section class="profile-v2-panel profile-v2-details-panel">
      <div class="profile-v2-panel-head">
        <span class="profile-v2-panel-icon profile-v2-panel-icon-green"><i class="fa fa-layer-group" aria-hidden="true"></i></span>
        <div>
          <h2>{{ $profileText['details'] }}</h2>
          <p>{{ $profileText['details_caption'] }}</p>
        </div>
      </div>

      <div class="profile-v2-detail-grid">
        @foreach($profileDetailGroups as $group)
          <article class="profile-v2-detail-card">
            <header>
              <span><i class="fa {{ $group['icon'] }}" aria-hidden="true"></i></span>
              <h3>{{ $group['title'] }}</h3>
            </header>
            <dl>
              @foreach($group['items'] as $item)
                <div>
                  <dt>{{ $item['label'] }}</dt>
                  <dd class="{{ !empty($item['tone']) ? 'profile-v2-detail-' . $item['tone'] : '' }}">
                    @if(!empty($item['tone']) && in_array($item['tone'], ['success', 'muted', 'warning', 'danger'], true))
                      <span class="profile-v2-pill is-{{ $item['tone'] === 'warning' ? 'warning' : ($item['tone'] === 'danger' ? 'danger' : $item['tone']) }}">
                        {{ $item['value'] }}
                      </span>
                    @else
                      {{ $item['value'] }}
                    @endif
                    @if(!empty($item['action']))
                      <a class="profile-v2-inline-action" href="{{ $item['action']['url'] }}">{{ $item['action']['label'] }}</a>
                    @endif
                  </dd>
                </div>
              @endforeach
            </dl>
          </article>
        @endforeach
      </div>
    </section>
  </div>
</div>
@endsection

@section('script')
<script src="{{ asset('vendor/intl-input/js/intlTelInput.js') }}?v={{ filemtime(public_path('vendor/intl-input/js/intlTelInput.js')) }}" type="text/javascript"></script>
<script>
(function ($) {
  $(function () {
    var imageInput = document.getElementById('profileV2Image');
    var heroPreview = document.getElementById('profileV2HeroImage');
    var formPreview = document.getElementById('profileV2FormImage');
    var form = document.getElementById('profileV2Form');
    var password = document.getElementById('profileV2Password');
    var confirmPassword = document.getElementById('profileV2PasswordConfirm');
    var passwordNote = document.getElementById('profileV2PasswordNote');
    var passwordStrength = document.getElementById('profileV2PasswordStrength');
    var $mobile = $('#profileV2Mobile');
    var $error = $('#profileV2MobileError');
    var $submit = $('#profileV2Submit');
    var profileV2Iti = null;
    var profileV2UtilsReady = false;
    var profileV2UtilsVersion = @json(filemtime(public_path('vendor/intl-input/js/utils.js')));

    function profileV2GetUtils() {
      return window.intlTelInput && window.intlTelInput.utils ? window.intlTelInput.utils : null;
    }

    function profileV2LoadUtils() {
      var utilsUrl = @json(asset('vendor/intl-input/js/utils.js')) + '?v=' + encodeURIComponent(profileV2UtilsVersion);
      return import(utilsUrl).then(function (module) {
        profileV2UtilsReady = true;
        return module;
      });
    }

    function profileV2EnsureLeadingPlus() {
      if (!$mobile.length || !$mobile.val()) return;
      var current = $mobile.val();
      if (current.charAt(0) !== '+') {
        $mobile.val('+' + current.replace(/^\+*/, ''));
      }
    }

    function profileV2UpdateCountryFields() {
      if (!profileV2Iti) return;
      var countryData = profileV2Iti.getSelectedCountryData() || {};
      $('#profileV2CountryIso').val(countryData.iso2 || '');
      $('#profileV2CountryCode').val(countryData.dialCode || '');
    }

    function profileV2ResetMobileState() {
      $mobile.removeClass('is-invalid');
      $error.addClass('d-none');
      $submit.prop('disabled', false);
    }

    function profileV2ValidateMobile(showError) {
      var utils = profileV2GetUtils();
      if (!profileV2Iti || !utils || !$mobile.length) {
        return true;
      }

      if (!$.trim($mobile.val())) {
        profileV2ResetMobileState();
        return true;
      }

      if (profileV2Iti.isValidNumber()) {
        profileV2UpdateCountryFields();
        profileV2ResetMobileState();
        return true;
      }

      if (showError) {
        $mobile.addClass('is-invalid');
        $error.removeClass('d-none');
        $submit.prop('disabled', true);
      }

      return false;
    }

    function profileV2SetSubmitNumber() {
      var utils = profileV2GetUtils();
      if (!profileV2Iti || !utils || !$.trim($mobile.val())) return;
      var normalized = profileV2Iti.getNumber(utils.numberFormat.E164);
      if (normalized) {
        $mobile.val(normalized);
      }
    }

    function profileV2SetImageEmpty(image) {
      if (!image) return;
      image.classList.add('is-empty');
      image.removeAttribute('src');
    }

    function profileV2BindImageFallback(image) {
      if (!image) return;

      image.addEventListener('error', function () {
        var fallbackSrc = image.getAttribute('data-fallback-src');
        if (fallbackSrc && image.getAttribute('src') !== fallbackSrc) {
          image.setAttribute('src', fallbackSrc);
          return;
        }

        profileV2SetImageEmpty(image);
      });

      if (image.complete && image.naturalWidth === 0) {
        profileV2SetImageEmpty(image);
      }
    }

    profileV2BindImageFallback(heroPreview);
    profileV2BindImageFallback(formPreview);

    if (imageInput) {
      imageInput.addEventListener('change', function () {
        var file = imageInput.files && imageInput.files[0];
        if (!file) return;

        var reader = new FileReader();
        reader.onload = function (event) {
          if (heroPreview) {
            heroPreview.classList.remove('is-empty');
            heroPreview.setAttribute('src', event.target.result);
          }

          if (formPreview) {
            formPreview.classList.remove('is-empty');
            formPreview.setAttribute('src', event.target.result);
          }
        };
        reader.readAsDataURL(file);
      });
    }

    $('.profile-v2-password-toggle').on('click', function () {
      var target = document.getElementById($(this).data('target'));
      if (!target) return;

      target.type = target.type === 'password' ? 'text' : 'password';
      $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    function profileV2PasswordScore(value) {
      var score = 0;
      if (!value) return score;
      if (value.length >= 8) score += 1;
      if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score += 1;
      if (/\d/.test(value)) score += 1;
      if (/[^A-Za-z0-9]/.test(value)) score += 1;
      return score;
    }

    function profileV2PasswordMeetsMinimum(value) {
      return !value || (value.length >= 8 && /[A-Za-z]/.test(value) && /\d/.test(value));
    }

    function profileV2SetPasswordMessage(type, message) {
      if (!passwordNote) return;
      passwordNote.classList.remove('is-error', 'is-success');

      if (type) {
        passwordNote.classList.add(type);
      }

      passwordNote.querySelector('span').textContent = message;
    }

    function profileV2UpdatePasswordStrength() {
      if (!password || !passwordStrength) return;

      var value = password.value;
      var score = profileV2PasswordScore(value);
      passwordStrength.classList.remove('is-empty', 'is-weak', 'is-good', 'is-strong');

      if (!value) {
        passwordStrength.classList.add('is-empty');
        passwordStrength.querySelector('strong').textContent = @json($profileText['password_requirements']);
        profileV2SetPasswordMessage('', @json($profileText['password_help']));
        return;
      }

      if (!profileV2PasswordMeetsMinimum(value)) {
        passwordStrength.classList.add('is-weak');
        passwordStrength.querySelector('strong').textContent = @json($profileText['password_weak']);
        profileV2SetPasswordMessage('is-error', @json($profileText['password_requirements']));
        return;
      }

      if (score >= 4) {
        passwordStrength.classList.add('is-strong');
        passwordStrength.querySelector('strong').textContent = @json($profileText['password_strong']);
      } else {
        passwordStrength.classList.add('is-good');
        passwordStrength.querySelector('strong').textContent = @json($profileText['password_good']);
      }

      if (confirmPassword && confirmPassword.value && value !== confirmPassword.value) {
        profileV2SetPasswordMessage('is-error', @json($profileText['password_mismatch']));
      } else {
        profileV2SetPasswordMessage('is-success', @json($profileText['password_ready']));
      }
    }

    if (password) {
      password.addEventListener('input', profileV2UpdatePasswordStrength);
    }

    if (confirmPassword) {
      confirmPassword.addEventListener('input', profileV2UpdatePasswordStrength);
    }

    if (form && password && confirmPassword && passwordNote) {
      form.addEventListener('submit', function (event) {
        if (password.value && !profileV2PasswordMeetsMinimum(password.value)) {
          event.preventDefault();
          profileV2SetPasswordMessage('is-error', @json($profileText['password_requirements']));
          password.focus();
          return;
        }

        if (password.value && confirmPassword.value && password.value !== confirmPassword.value) {
          event.preventDefault();
          profileV2SetPasswordMessage('is-error', @json($profileText['password_mismatch']));
          confirmPassword.focus();
        }
      });
    }

    if ($mobile.length && window.intlTelInput && !$mobile.prop('readonly')) {
      profileV2Iti = window.intlTelInput($mobile[0], {
        initialCountry: '',
        separateDialCode: false,
        autoPlaceholder: 'off',
        nationalMode: false,
        formatOnDisplay: false,
        loadUtils: profileV2LoadUtils
      });

      $mobile.on('countrychange', function () {
        profileV2UpdateCountryFields();
        profileV2ValidateMobile(false);
      });

      $mobile.on('change keyup paste input focus', function (event) {
        var code = event.keyCode || event.which;
        if (code === 37 || code === 38 || code === 39 || code === 40) return;

        profileV2ResetMobileState();
        profileV2EnsureLeadingPlus();

        var current = $mobile.val() || '';
        var cleaned = '+' + current.replace(/[^\d]/g, '');
        if (cleaned !== current) {
          $mobile.val(cleaned);
        }
      });

      $mobile.on('blur', function () {
        if (profileV2ValidateMobile(true)) {
          profileV2SetSubmitNumber();
        }
      });

      if (form) {
        form.addEventListener('submit', function (event) {
          if (!profileV2ValidateMobile(true)) {
            event.preventDefault();
            $mobile.focus();
            return;
          }

          profileV2SetSubmitNumber();
        });
      }

      profileV2EnsureLeadingPlus();
      profileV2UpdateCountryFields();

      if (profileV2Iti.promise && typeof profileV2Iti.promise.then === 'function') {
        profileV2Iti.promise.then(function () {
          if (profileV2UtilsReady) {
            profileV2UpdateCountryFields();
            profileV2ValidateMobile(false);
          }
        });
      }
    }
  });
})(jQuery);
</script>
@endsection
