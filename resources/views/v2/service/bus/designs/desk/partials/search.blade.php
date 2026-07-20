@php
    $searchData      = $searchData ?? [];
    $adultCount      = max(1, (int) ($searchData['adult'] ?? 1));
    $childCount      = max(0, (int) ($searchData['children'] ?? 0));
    $tripType        = $searchData['trip_type'] ?? 'one_way';
    $returnDate      = $searchData['return_date'] ?? '';
    $flixLogo        = secure_asset('images/search-flix.png');
    $blaLogo         = secure_asset('images/search-bla.png');
    $passengerSummary = implode(', ', array_filter([
        trans_choice('bus.passenger.adult', $adultCount, ['count' => $adultCount]),
        $childCount > 0 ? trans_choice('bus.passenger.child', $childCount, ['count' => $childCount]) : null,
    ]));
@endphp

<section class="bsk bus-v2-search-card {{ $tripType === 'round_trip' ? 'is-round-trip' : 'is-one-way' }}">
    <div class="bsk-header">
        <div class="bsk-header-left">
            <div class="bsk-toggle" role="tablist" aria-label="{{ __('bus.trip_type.label') }}">
                <button type="button"
                    class="bsk-toggle-btn js-bus-v2-trip-type {{ $tripType === 'one_way' ? 'is-active' : '' }}"
                    data-value="one_way"
                    aria-pressed="{{ $tripType === 'one_way' ? 'true' : 'false' }}">
                    <i class="fas fa-arrow-right"></i> {{ __('bus.trip_type.one_way') }}
                </button>
                <button type="button"
                    class="bsk-toggle-btn js-bus-v2-trip-type {{ $tripType === 'round_trip' ? 'is-active' : '' }}"
                    data-value="round_trip"
                    aria-pressed="{{ $tripType === 'round_trip' ? 'true' : 'false' }}">
                    <i class="fas fa-right-left"></i> {{ __('bus.trip_type.round_trip') }}
                </button>
            </div>
        </div>
    </div>

    <form id="busV2SearchForm" action="{{ route('bus.v2.search') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" id="busV2TripType" name="trip_type" value="{{ $tripType }}">

        <div class="bsk-row bus-v2-search-grid bus-v2-desk-search-grid {{ $tripType === 'round_trip' ? 'is-round-trip' : 'is-one-way' }}">

            {{-- FROM --}}
            <div class="bsk-cell bsk-cell--route" id="busV2FromField">
                <label class="bsk-label" for="busV2From">
                    <i class="fas fa-location-dot bsk-icon bsk-icon--dep"></i>
                    {{ __('bus.search.from') }}
                </label>
                <input type="text"
                    class="bsk-input bus-v2-control"
                    id="busV2From" name="cityFrom"
                    value="{{ $searchData['from_name'] ?? '' }}"
                    placeholder="{{ __('bus.search.origin_placeholder') }}"
                    autocomplete="off">
                <input type="hidden" id="busV2FromId"    name="cityFromHid" value="{{ $searchData['from_id'] ?? '' }}">
                <input type="hidden" id="busV2GeoLatFrom" name="geolatfrom" value="{{ $searchData['geolatfrom'] ?? '' }}">
                <input type="hidden" id="busV2GeoLonFrom" name="geolonfrom" value="{{ $searchData['geolonfrom'] ?? '' }}">
                <span id="busV2FromError" class="bsk-err bus-v2-error-text"></span>
            </div>

            {{-- SWAP --}}
            <div class="bsk-swap-cell">
                <button type="button" id="busV2Swap" class="bsk-swap bus-v2-swap" title="{{ __('bus.search.swap_route') }}">
                    <i class="fas fa-arrow-right-arrow-left"></i>
                </button>
            </div>

            {{-- TO --}}
            <div class="bsk-cell bsk-cell--route" id="busV2ToField">
                <label class="bsk-label" for="busV2To">
                    <i class="fas fa-location-arrow bsk-icon bsk-icon--arr"></i>
                    {{ __('bus.search.to') }}
                </label>
                <input type="text"
                    class="bsk-input bus-v2-control"
                    id="busV2To" name="cityTo"
                    value="{{ $searchData['to_name'] ?? '' }}"
                    placeholder="{{ __('bus.search.destination_placeholder') }}"
                    autocomplete="off">
                <input type="hidden" id="busV2ToId"      name="cityToHid"  value="{{ $searchData['to_id'] ?? '' }}">
                <input type="hidden" id="busV2GeoLatTo"  name="geolatto"   value="{{ $searchData['geolatto'] ?? '' }}">
                <input type="hidden" id="busV2GeoLonTo"  name="geolonto"   value="{{ $searchData['geolonto'] ?? '' }}">
                <span id="busV2ToError" class="bsk-err bus-v2-error-text"></span>
            </div>

            {{-- DIVIDER --}}
            <div class="bsk-divider" aria-hidden="true"></div>

            {{-- DEPARTURE DATE --}}
            <div class="bsk-cell bsk-cell--date">
                <label class="bsk-label" for="busV2DepartureDate">
                    <i class="far fa-calendar bsk-icon"></i>
                    {{ __('bus.search.departure') }}
                </label>
                <input type="text"
                    class="bsk-input js-bus-v2-datepicker"
                    id="busV2DepartureDate" name="departureDate"
                    value="{{ $searchData['departure'] ?? date('Y-m-d') }}"
                    data-datepicker-kind="travel"
                    autocomplete="off" readonly
                    placeholder="DD / MM / YYYY">
                <span id="busV2DateError" class="bsk-err bus-v2-error-text"></span>
            </div>

            {{-- RETURN DATE --}}
            <div class="bsk-cell bsk-cell--date" id="busV2ReturnField" @if($tripType !== 'round_trip') hidden @endif>
                <label class="bsk-label" for="busV2ReturnDate">
                    <i class="far fa-calendar-check bsk-icon"></i>
                    {{ __('bus.search.return') }}
                </label>
                <input type="text"
                    class="bsk-input js-bus-v2-datepicker"
                    id="busV2ReturnDate" name="returnDate"
                    value="{{ $returnDate }}"
                    data-datepicker-kind="travel"
                    autocomplete="off" readonly
                    placeholder="DD / MM / YYYY">
                <span id="busV2ReturnDateError" class="bsk-err bus-v2-error-text"></span>
            </div>

            {{-- DIVIDER --}}
            <div class="bsk-divider" aria-hidden="true"></div>

            {{-- PASSENGERS --}}
            <div class="bsk-cell bsk-cell--pax bus-v2-passenger-field">
                <label class="bsk-label">
                    <i class="fas fa-users bsk-icon"></i>
                    {{ __('bus.search.passengers') }}
                </label>
                <button type="button" class="bsk-pax-trigger bus-v2-passenger-trigger" data-passenger-toggle aria-expanded="false">
                    <span id="busV2PassengersDisplay" class="bsk-pax-text">{{ $passengerSummary }}</span>
                    <i class="fas fa-chevron-down bsk-pax-caret"></i>
                </button>

                <div class="bus-v2-passenger-panel bsk-pax-panel" id="busV2PassengerPanel" hidden>
                    <div class="bus-v2-passenger-panel-head bsk-panel-head">
                        <strong>{{ __('bus.search.passengers') }}</strong>
                        <span id="busV2PassengerCountBadge">{{ __('bus.passenger.total', ['count' => $adultCount + $childCount]) }}</span>
                    </div>

                    <div class="bus-v2-passenger-stack bsk-pax-stack">
                        <div class="bus-v2-passenger-row bsk-pax-row is-adult" id="busV2AdultRow">
                            <div class="bsk-pax-info">
                                <strong>{{ __('bus.search.adults') }}</strong>
                                <span>{{ __('bus.search.adults_age') }}</span>
                            </div>
                            <div class="bus-v2-stepper bsk-stepper">
                                <button type="button" class="bsk-step-btn js-bus-v2-stepper" data-target="#busV2Adult" data-step="-1" data-min="1"><i class="fas fa-minus"></i></button>
                                <span id="busV2AdultCount" class="bus-v2-stepper-value bsk-step-val">{{ $adultCount }}</span>
                                <button type="button" class="bsk-step-btn js-bus-v2-stepper" data-target="#busV2Adult" data-step="1"  data-max="9"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>

                        <div class="bus-v2-passenger-row bsk-pax-row is-child {{ $childCount < 1 ? 'is-empty' : '' }}" id="busV2ChildRow">
                            <div class="bsk-pax-info">
                                <strong>{{ __('bus.search.children') }}</strong>
                                <span>{{ __('bus.search.children_age') }}</span>
                            </div>
                            <div class="bus-v2-stepper bsk-stepper">
                                <button type="button" class="bsk-step-btn js-bus-v2-stepper" data-target="#busV2Child" data-step="-1" data-min="0"><i class="fas fa-minus"></i></button>
                                <span id="busV2ChildCount" class="bus-v2-stepper-value bsk-step-val">{{ $childCount }}</span>
                                <button type="button" class="bsk-step-btn js-bus-v2-stepper" data-target="#busV2Child" data-step="1"  data-max="9"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="bus-v2-passenger-panel-foot bsk-panel-foot">
                        <span id="busV2PassengerPanelSummary" class="bus-v2-passenger-panel-summary">{{ $passengerSummary }}</span>
                        <button type="button" class="bsk-done js-bus-v2-passenger-apply">{{ __('bus.search.done') }}</button>
                    </div>
                </div>

                <input type="hidden" id="busV2Passengers" name="passengers" value="{{ $passengerSummary }}">
                <input type="hidden" id="busV2Adult"      name="adult"      value="{{ $adultCount }}">
                <input type="hidden" id="busV2Child"      name="child"      value="{{ $childCount }}">
                <span id="busV2PassengersError" class="bsk-err bus-v2-error-text"></span>
            </div>

            {{-- SEARCH BUTTON --}}
            <div class="bsk-submit-cell">
                <button type="submit" id="busV2SearchButton"
                    class="bsk-submit bus-v2-button bus-v2-button--primary"
                    data-loading-text="{{ __('bus.search.searching') }}"
                    data-loading-alt-text="{{ __('bus.search.finding_routes') }}">
                    <i class="fas fa-magnifying-glass"></i>
                    <span>{{ __('bus.search.submit') }}</span>
                </button>
            </div>

        </div>
    </form>
</section>

<template id="busV2ResultsLoadingTemplate">
    @include('v2.service.bus.designs.desk.partials.loading-state')
</template>
