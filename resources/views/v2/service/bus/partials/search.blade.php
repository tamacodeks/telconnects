@php
    $searchData = $searchData ?? [];
    $adultCount = max(1, (int) ($searchData['adult'] ?? 1));
    $childCount = max(0, (int) ($searchData['children'] ?? 0));
    $tripType = $searchData['trip_type'] ?? 'one_way';
    $returnDate = $searchData['return_date'] ?? '';
    $flixLogo = secure_asset('images/search-flix.png');
    $blaLogo = secure_asset('images/search-bla.png');
    $passengerSummary = implode(', ', array_filter([
        trans_choice('bus.passenger.adult', $adultCount, ['count' => $adultCount]),
        $childCount > 0 ? trans_choice('bus.passenger.child', $childCount, ['count' => $childCount]) : null,
    ]));
    $departureRaw = $searchData['departure'] ?? date('Y-m-d');
    $departureDayName = date('l', strtotime($departureRaw));
    $returnDayName = $returnDate ? date('l', strtotime($returnDate)) : '';
@endphp

<form id="busV2SearchForm" action="{{ route('bus.v2.search') }}" method="POST" novalidate>
@csrf
<input type="hidden" id="busV2TripType" name="trip_type" value="{{ $tripType }}">
<div class="bus-v2-search-grid {{ $tripType === 'round_trip' ? 'is-round-trip' : 'is-one-way' }}" aria-hidden="true" style="display:none!important"></div>

<div class="bpr-card bpr-card--luxury {{ $tripType === 'round_trip' ? 'is-round-trip' : 'is-one-way' }}">
  <div class="bpr-header">
    <div class="bpr-header-intro">
      <div class="bpr-toggle {{ $tripType === 'round_trip' ? 'two-way' : '' }}" id="busV2TripToggle" role="tablist">
        <button type="button"
          class="bpr-toggle-btn js-bus-v2-trip-type {{ $tripType === 'one_way' ? 'is-active' : '' }}"
          data-value="one_way" role="tab" aria-selected="{{ $tripType === 'one_way' ? 'true' : 'false' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          {{ __('bus.trip_type.one_way') }}
        </button>
        <button type="button"
          class="bpr-toggle-btn js-bus-v2-trip-type {{ $tripType === 'round_trip' ? 'is-active' : '' }}"
          data-value="round_trip" role="tab" aria-selected="{{ $tripType === 'round_trip' ? 'true' : 'false' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16V4m0 0L3 8m4-4 4 4M17 8v12m0 0 4-4m-4 4-4-4"/></svg>
          {{ __('bus.trip_type.round_trip') }}
        </button>
      </div>
    </div>

    <div class="bpr-header-stack">
      <div class="bpr-partners">
        <div class="bpr-providers">
          <img src="{{ $flixLogo }}" alt="FlixBus"
               onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
          <div class="bpr-provider-fallback bpr-provider-fallback--flix">FLiXBUS</div>
          <span class="bpr-provider-plus">+</span>
          <img src="{{ $blaLogo }}" alt="BlaBlaCar Bus"
               onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
          <div class="bpr-provider-fallback bpr-provider-fallback--bla">BlaBlaCar Bus</div>
        </div>
      </div>
    </div>
  </div>

  <div class="bpr-route">
    <div class="bpr-route-card bpr-route-card--from" id="busV2FromField">
      <div class="bpr-route-content">
        <div class="bpr-route-badge bpr-detail-badge bpr-detail-badge--green" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-6-5.2-6-11a6 6 0 1 1 12 0c0 5.8-6 11-6 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
        </div>
        <div class="bpr-route-copy">
          <label class="bpr-loc-label" for="busV2From">{{ __('bus.search.from') }}</label>
          <input type="text"
            class="bpr-loc-input bus-v2-control"
            id="busV2From" name="cityFrom"
            value="{{ $searchData['from_name'] ?? '' }}"
            placeholder="{{ __('bus.search.origin_placeholder') }}"
            autocomplete="off" spellcheck="false">
          <span id="busV2FromError" class="bpr-field-err bus-v2-error-text"></span>
        </div>
      </div>

      <input type="hidden" id="busV2FromId" name="cityFromHid" value="{{ $searchData['from_id'] ?? '' }}">
      <input type="hidden" id="busV2GeoLatFrom" name="geolatfrom" value="{{ $searchData['geolatfrom'] ?? '' }}">
      <input type="hidden" id="busV2GeoLonFrom" name="geolonfrom" value="{{ $searchData['geolonfrom'] ?? '' }}">
    </div>

    <button type="button" id="busV2Swap" class="bpr-swap bus-v2-swap" title="{{ __('bus.search.swap_route') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M7 16V4m0 0L3 8m4-4 4 4M17 8v12m0 0 4-4m-4 4-4-4"/>
      </svg>
    </button>

    <div class="bpr-route-card bpr-route-card--to" id="busV2ToField">
      <div class="bpr-route-content">
        <div class="bpr-route-badge bpr-detail-badge bpr-detail-badge--blue" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-6-5.2-6-11a6 6 0 1 1 12 0c0 5.8-6 11-6 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
        </div>
        <div class="bpr-route-copy">
          <label class="bpr-loc-label bpr-loc-label--blue" for="busV2To">{{ __('bus.search.to') }}</label>
          <input type="text"
            class="bpr-loc-input bus-v2-control"
            id="busV2To" name="cityTo"
            value="{{ $searchData['to_name'] ?? '' }}"
            placeholder="{{ __('bus.search.destination_placeholder') }}"
            autocomplete="off" spellcheck="false">
          <span id="busV2ToError" class="bpr-field-err bus-v2-error-text"></span>
        </div>
      </div>

      <input type="hidden" id="busV2ToId" name="cityToHid" value="{{ $searchData['to_id'] ?? '' }}">
      <input type="hidden" id="busV2GeoLatTo" name="geolatto" value="{{ $searchData['geolatto'] ?? '' }}">
      <input type="hidden" id="busV2GeoLonTo" name="geolonto" value="{{ $searchData['geolonto'] ?? '' }}">
    </div>
  </div>

  <div class="bpr-details bpr-meta-grid">
    <div class="bpr-detail-card bpr-detail-card--date">
      <div class="bpr-detail-badge bpr-detail-badge--green">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      </div>
      <div class="bpr-detail-body">
        <span class="bpr-detail-label">{{ __('bus.search.departure') }}</span>
        <input class="bpr-detail-val js-bus-v2-datepicker"
          type="text"
          id="busV2DepartureDate" name="departureDate"
          value="{{ $departureRaw }}"
          data-datepicker-kind="travel"
          autocomplete="off" readonly>
        <span class="bpr-detail-sub" id="busV2DepartureDayName">{{ $departureDayName }}</span>
      </div>
      <span id="busV2DateError" class="bpr-field-err bus-v2-error-text"></span>
    </div>

    <div class="bpr-return" id="busV2ReturnField" @if($tripType !== 'round_trip') hidden @endif>
      <div class="bpr-return-card">
        <div class="bpr-detail-badge bpr-detail-badge--green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
        </div>
        <div class="bpr-detail-body">
          <span class="bpr-detail-label">{{ __('bus.search.return') }}</span>
          <input class="bpr-detail-val js-bus-v2-datepicker"
            type="text"
            id="busV2ReturnDate" name="returnDate"
            value="{{ $returnDate }}"
            data-datepicker-kind="travel"
            autocomplete="off" readonly>
          <span class="bpr-detail-sub" id="busV2ReturnDayName">{{ $returnDayName }}</span>
        </div>
        <span id="busV2ReturnDateError" class="bpr-field-err bus-v2-error-text"></span>
      </div>
    </div>

    <div class="bpr-detail-card bpr-detail-card--pax bus-v2-passenger-field" id="busV2PaxCard" aria-expanded="false">
      <div class="bpr-detail-badge bpr-detail-badge--blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="bpr-detail-body">
        <span class="bpr-detail-label bpr-detail-label--blue">{{ __('bus.search.passengers') }}</span>
        <div class="bpr-pax-trigger-row">
          <button type="button" class="bpr-detail-val bus-v2-passenger-trigger" data-passenger-toggle aria-expanded="false">
            <span id="busV2PassengersDisplay">{{ $passengerSummary }}</span>
          </button>
          <span class="bpr-chevron">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M6 9l6 6 6-6"/></svg>
          </span>
        </div>
      </div>
      <span id="busV2PassengersError" class="bpr-field-err bus-v2-error-text"></span>

      <div class="bpr-pax-panel bus-v2-passenger-panel" id="busV2PassengerPanel" hidden>
        <div class="bpr-pax-head">
          <span>{{ __('bus.search.passengers') }}</span>
          <span id="busV2PassengerCountBadge" class="bpr-pax-badge">{{ __('bus.passenger.total', ['count' => $adultCount + $childCount]) }}</span>
        </div>
        <div class="bpr-pax-row-item" id="busV2AdultRow">
          <div class="bpr-pax-info">
            <strong>{{ __('bus.search.adults') }}</strong>
            <small>{{ __('bus.search.adults_age') }}</small>
          </div>
          <div class="bpr-stepper">
            <button type="button" class="bpr-step js-bus-v2-stepper" data-target="#busV2Adult" data-step="-1" data-min="1">-</button>
            <span id="busV2AdultCount" class="bpr-step-num">{{ $adultCount }}</span>
            <button type="button" class="bpr-step js-bus-v2-stepper" data-target="#busV2Adult" data-step="1" data-max="9">+</button>
          </div>
        </div>
        <div class="bpr-pax-row-item" id="busV2ChildRow">
          <div class="bpr-pax-info">
            <strong>{{ __('bus.search.children') }}</strong>
            <small>{{ __('bus.search.children_age') }}</small>
          </div>
          <div class="bpr-stepper">
            <button type="button" class="bpr-step js-bus-v2-stepper" data-target="#busV2Child" data-step="-1" data-min="0">-</button>
            <span id="busV2ChildCount" class="bpr-step-num">{{ $childCount }}</span>
            <button type="button" class="bpr-step js-bus-v2-stepper" data-target="#busV2Child" data-step="1" data-max="9">+</button>
          </div>
        </div>
        <div class="bpr-pax-foot">
          <span id="busV2PassengerPanelSummary" class="bus-v2-passenger-panel-summary">{{ $passengerSummary }}</span>
          <button type="button" class="bpr-pax-done js-bus-v2-passenger-apply">{{ __('bus.search.done') }}</button>
        </div>
      </div>

      <input type="hidden" id="busV2Passengers" name="passengers" value="{{ $passengerSummary }}">
      <input type="hidden" id="busV2Adult" name="adult" value="{{ $adultCount }}">
      <input type="hidden" id="busV2Child" name="child" value="{{ $childCount }}">
    </div>
  </div>

  <div class="bpr-submit-wrap">
    <button type="submit" id="busV2SearchButton" class="bpr-submit"
      data-loading-text="{{ __('bus.search.searching') }}"
      data-loading-alt-text="{{ __('bus.search.finding_routes') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <span>{{ __('bus.search.submit') }}</span>
      <svg class="bpr-submit-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </button>
  </div>
</div>
</form>

<template id="busV2ResultsLoadingTemplate">
    @include('v2.service.bus.partials.loading-state')
</template>
