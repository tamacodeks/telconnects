@php
    $tripType = $searchData['trip_type'] ?? 'one_way';
    $isRoundTrip = $tripType === 'round_trip';
    $adultCount = max(1, (int) ($searchData['adult'] ?? 1));
    $childCount = max(0, (int) ($searchData['children'] ?? 0));
    $passengerSummary = implode(', ', array_filter([
        trans_choice('bus.passenger.adult', $adultCount, ['count' => $adultCount]),
        $childCount > 0 ? trans_choice('bus.passenger.child', $childCount, ['count' => $childCount]) : null,
    ]));
    $activeSort = (string) ($searchData['sort_by'] ?? '1');
    if ($activeSort === '') {
        $activeSort = '1';
    }

    $resultsCountLabel = trans_choice('bus.passenger.option', (int) $totalResults, ['count' => (int) $totalResults]);
    $lowestFare = null;
    $highestFare = null;
    $fastestMinutes = null;
    $carrierSet = [];
    foreach ($results as $trip) {
        $price = round((float) ($trip['total_price'] ?? 0), 2);
        $minutes = ((int) ($trip['duration_hour'] ?? 0) * 60) + (int) ($trip['duration_minutes'] ?? 0);
        $tripBusType = $trip['bus_type'] ?? '';
        $carrierSet[
            (!empty($tripBusType) && (stripos($tripBusType, 'blabla') !== false || stripos($tripBusType, 'comuto') !== false || $tripBusType === 'Mixed'))
                ? 'BlaBlaBus'
                : 'FlixBus'
        ] = true;

        if ($price > 0) {
            $lowestFare = $lowestFare === null ? $price : min($lowestFare, $price);
            $highestFare = $highestFare === null ? $price : max($highestFare, $price);
        }

        if ($minutes > 0) {
            $fastestMinutes = $fastestMinutes === null ? $minutes : min($fastestMinutes, $minutes);
        }
    }
    $formatDuration = function ($minutes) {
        $hours = (int) floor($minutes / 60);
        $mins = (int) ($minutes % 60);
        if ($hours <= 0) {
            return $mins . 'm';
        }
        if ($mins <= 0) {
            return $hours . 'h';
        }
        return $hours . 'h ' . $mins . 'm';
    };

    $lowestFareLabel = $lowestFare !== null ? '€ ' . number_format($lowestFare, 2) : __('bus.results.not_available');
    $fareWindowLabel = ($lowestFare !== null && $highestFare !== null)
        ? '€ ' . number_format($lowestFare, 2) . ' - € ' . number_format($highestFare, 2)
        : $lowestFareLabel;
    $fastestTripLabel = $fastestMinutes !== null ? $formatDuration($fastestMinutes) : '--';
    $routeFromLabel = trim((string) ($searchData['from_name'] ?? ''));
    $routeToLabel = trim((string) ($searchData['to_name'] ?? ''));
    $routeLabel = trim($routeFromLabel . ' - ' . $routeToLabel);
    $resultsSummaryLabel = $resultsCountLabel . ' on ' . ($searchData['departure'] ?? '');

    $normalizeAmenities = function ($items) {
        $map = [
            'wifi' => ['key' => 'wifi', 'label' => 'Free Wi-Fi', 'icon' => 'fas fa-wifi'],
            'power_sockets' => ['key' => 'outlets', 'label' => 'Outlets', 'icon' => 'fas fa-plug'],
            'power_socket' => ['key' => 'outlets', 'label' => 'Outlets', 'icon' => 'fas fa-plug'],
            'power_outlets' => ['key' => 'outlets', 'label' => 'Outlets', 'icon' => 'fas fa-plug'],
            'power_outlet' => ['key' => 'outlets', 'label' => 'Outlets', 'icon' => 'fas fa-plug'],
            'usb' => ['key' => 'outlets', 'label' => 'Outlets', 'icon' => 'fas fa-plug'],
            'air_conditioning' => ['key' => 'ac', 'label' => 'Air conditioning', 'icon' => 'fas fa-snowflake'],
            'ac' => ['key' => 'ac', 'label' => 'Air conditioning', 'icon' => 'fas fa-snowflake'],
            'toilet' => ['key' => 'toilet', 'label' => 'Toilet', 'icon' => 'fas fa-restroom'],
            'bike_slot' => ['key' => 'bike', 'label' => 'Bike slot', 'icon' => 'fas fa-bicycle'],
            'bike' => ['key' => 'bike', 'label' => 'Bike slot', 'icon' => 'fas fa-bicycle'],
            'family_package' => ['key' => 'family', 'label' => 'Family friendly', 'icon' => 'fas fa-child'],
            'snacks' => ['key' => 'snacks', 'label' => 'Snacks & drinks', 'icon' => 'fas fa-utensils'],
            'entertainment' => ['key' => 'entertain', 'label' => 'Entertainment', 'icon' => 'fas fa-tv'],
            'accessible' => ['key' => 'access', 'label' => 'Accessible', 'icon' => 'fas fa-wheelchair'],
            'wheelchair' => ['key' => 'access', 'label' => 'Accessible', 'icon' => 'fas fa-wheelchair'],
            'wc' => ['key' => 'toilet', 'label' => 'Toilet', 'icon' => 'fas fa-restroom'],
            'prop_wifi' => ['key' => 'wifi', 'label' => 'Free Wi-Fi', 'icon' => 'fas fa-wifi'],
            'prop_power_socket' => ['key' => 'outlets', 'label' => 'Outlets', 'icon' => 'fas fa-plug'],
            'prop_power_outlet' => ['key' => 'outlets', 'label' => 'Outlets', 'icon' => 'fas fa-plug'],
            'prop_usb' => ['key' => 'outlets', 'label' => 'USB ports', 'icon' => 'fas fa-plug'],
            'prop_outletandusb' => ['key' => 'outlets', 'label' => 'Plug sockets and USB', 'icon' => 'fas fa-plug'],
            'prop_ac' => ['key' => 'ac', 'label' => 'Air conditioning', 'icon' => 'fas fa-snowflake'],
            'prop_air_conditioning' => ['key' => 'ac', 'label' => 'Air conditioning', 'icon' => 'fas fa-snowflake'],
            'prop_onboardac' => ['key' => 'ac', 'label' => 'Air conditioning', 'icon' => 'fas fa-snowflake'],
            'prop_toilet' => ['key' => 'toilet', 'label' => 'Toilet', 'icon' => 'fas fa-restroom'],
            'prop_onboardwc' => ['key' => 'toilet', 'label' => 'Toilet', 'icon' => 'fas fa-restroom'],
            'prop_bike' => ['key' => 'bike', 'label' => 'Bike slot', 'icon' => 'fas fa-bicycle'],
            'prop_snacks' => ['key' => 'snacks', 'label' => 'Snacks & drinks', 'icon' => 'fas fa-utensils'],
            'prop_entertainment' => ['key' => 'entertain', 'label' => 'Entertainment', 'icon' => 'fas fa-tv'],
            'prop_accessible' => ['key' => 'access', 'label' => 'Accessible', 'icon' => 'fas fa-wheelchair'],
            'prop_wheelchair' => ['key' => 'access', 'label' => 'Accessible', 'icon' => 'fas fa-wheelchair'],
            'prop_luggage' => ['key' => 'luggage', 'label' => 'Luggage', 'icon' => 'fas fa-suitcase'],
            'prop_luggage_rack' => ['key' => 'luggage', 'label' => 'Luggage rack', 'icon' => 'fas fa-suitcase'],
            'prop_oneholdluggage' => ['key' => 'hold_luggage', 'label' => 'Hold luggage', 'icon' => 'fas fa-suitcase'],
            'prop_twohandluggages' => ['key' => 'hand_luggage', 'label' => 'Hand luggage', 'icon' => 'fas fa-suitcase'],
            'prop_confortseats' => ['key' => 'seats', 'label' => 'Reclining seats', 'icon' => 'fas fa-chair'],
            'prop_comfortseats' => ['key' => 'seats', 'label' => 'Reclining seats', 'icon' => 'fas fa-chair'],
            'prop_assigned_seating' => ['key' => 'assigned_seating', 'label' => 'Assigned seating', 'icon' => 'fas fa-chair'],
            'prop_e_ticket' => ['key' => 'e_ticket', 'label' => 'E-ticket', 'icon' => 'fas fa-ticket-alt'],
            'hold_luggage' => ['key' => 'hold_luggage', 'label' => 'Hold luggage', 'icon' => 'fas fa-suitcase'],
            'hand_luggage' => ['key' => 'hand_luggage', 'label' => 'Hand luggage', 'icon' => 'fas fa-suitcase'],
            'assigned_seating' => ['key' => 'assigned_seating', 'label' => 'Assigned seating', 'icon' => 'fas fa-chair'],
            'e_ticket' => ['key' => 'e_ticket', 'label' => 'E-ticket', 'icon' => 'fas fa-ticket-alt'],
        ];
        $flatten = null;
        $flatten = function ($values) use (&$flatten) {
            $codes = [];
            foreach ((array) $values as $value) {
                if (is_array($value)) {
                    if (!empty($value['label'])) {
                        $codes[] = $value;
                    }
                    foreach (['code', 'key', 'name'] as $field) {
                        if (isset($value[$field]) && is_scalar($value[$field])) {
                            $codes[] = $value[$field];
                        }
                    }
                    $codes = array_merge($codes, $flatten($value));
                } elseif (is_scalar($value)) {
                    $codes[] = $value;
                }
            }
            return $codes;
        };
        $amenities = [];
        $seen = [];
        foreach ($flatten($items) as $item) {
            if (is_array($item) && !empty($item['label'])) {
                $key = (string) ($item['key'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '_', $item['label'])));
                if (empty($seen[$key])) {
                    $amenities[] = $item;
                    $seen[$key] = true;
                }
                continue;
            }
            $code = strtolower(trim((string) $item));
            if (isset($map[$code]) && empty($seen[$map[$code]['key']])) {
                $amenities[] = $map[$code];
                $seen[$map[$code]['key']] = true;
            }
        }
        return $amenities;
    };
@endphp

<section class="bus-v2-panel bus-v2-results-panel bus-v2-results-panel--premium">
    <form id="busV2SortForm" action="{{ route('bus.v2.search') }}" method="POST" class="js-bus-v2-ajax-form bus-v2-filter-shell bus-v2-filter-shell--premium">
        @csrf
        <input type="hidden" name="trip_type" value="{{ $tripType }}">
        <input type="hidden" name="cityFrom" value="{{ $searchData['from_name'] ?? '' }}">
        <input type="hidden" name="cityTo" value="{{ $searchData['to_name'] ?? '' }}">
        <input type="hidden" name="geolatfrom" value="{{ $searchData['geolatfrom'] ?? '' }}">
        <input type="hidden" name="geolonfrom" value="{{ $searchData['geolonfrom'] ?? '' }}">
        <input type="hidden" name="cityFromHid" value="{{ $searchData['from_id'] ?? '' }}">
        <input type="hidden" name="cityToHid" value="{{ $searchData['to_id'] ?? '' }}">
        <input type="hidden" name="geolatto" value="{{ $searchData['geolatto'] ?? '' }}">
        <input type="hidden" name="geolonto" value="{{ $searchData['geolonto'] ?? '' }}">
        <input type="hidden" name="departureDate" value="{{ $searchData['departure'] ?? '' }}">
        <input type="hidden" name="returnDate" value="{{ $searchData['return_date'] ?? '' }}">
        <input type="hidden" name="passengers" value="{{ $passengerSummary }}">
        <input type="hidden" name="adult" value="{{ $searchData['adult'] ?? 1 }}">
        <input type="hidden" name="child" value="{{ $searchData['children'] ?? 0 }}">
        <input type="hidden" name="bus_v2_sort_only" value="1">
        <input type="hidden" id="busV2SortBy" name="sort_by" value="{{ $activeSort }}">
        <input type="hidden" id="busV2SortCarrier" name="sort_by_bus" value="{{ $searchData['sort_by_bus'] ?? '' }}">

        <div class="bus-v2-results-hero bus-v2-results-hero--merged bus-v2-results-hero--reference">
            <div class="bus-v2-results-hero-main">
                <div class="bus-v2-results-hero-copy">
                    <div class="bus-v2-results-step-row">
                        <span class="bus-v2-results-step-pill">Step 1 of 1</span>
                        <span class="bus-v2-results-step-pill bus-v2-results-step-pill--live">{{ __('bus.results.live_availability') }}</span>
                    </div>
                    <h2>{{ __('bus.results.title') }}</h2>
                    <div class="bus-v2-results-route-hero">
                        <strong>{{ $routeLabel }}</strong>
                        <span>{{ $resultsSummaryLabel }}</span>
                    </div>

                    <div class="bus-v2-results-meta-row bus-v2-results-meta-row--hero">
                        <span class="bus-v2-results-meta-chip bus-v2-results-meta-chip--accent">
                            <i class="fas fa-ticket-alt" aria-hidden="true"></i>
                            {{ $resultsCountLabel }}
                        </span>
                        <span class="bus-v2-results-meta-chip">
                            <i class="far fa-calendar-alt" aria-hidden="true"></i>
                            {{ $searchData['departure'] ?? '' }}
                        </span>
                        <span class="bus-v2-results-meta-chip">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            {{ $passengerSummary }}
                        </span>
                    </div>
                </div>

                <div class="bus-v2-results-route-visual" aria-label="{{ trim($routeFromLabel . ' to ' . $routeToLabel) }}">
                    <span class="bus-v2-results-route-end bus-v2-results-route-end--from">
                        <small>{{ __('bus.search.from') }}</small>
                        <strong>{{ $routeFromLabel }}</strong>
                    </span>
                    <span class="bus-v2-results-route-dot"></span>
                    <span class="bus-v2-results-route-line"></span>
                    <span class="bus-v2-results-route-bus"><i class="fas fa-bus"></i></span>
                    <span class="bus-v2-results-route-line"></span>
                    <span class="bus-v2-results-route-dot"></span>
                    <span class="bus-v2-results-route-end bus-v2-results-route-end--to">
                        <small>{{ __('bus.search.to') }}</small>
                        <strong>{{ $routeToLabel }}</strong>
                    </span>
                </div>
            </div>

            <aside class="bus-v2-results-summary-panel">
                <div class="bus-v2-results-summary-grid">
                    <div class="bus-v2-results-summary-item">
                        <span>Lowest fare today</span>
                        <strong>{{ $lowestFareLabel }}</strong>
                    </div>
                    <div class="bus-v2-results-summary-item">
                        <span>Fastest trip</span>
                        <strong>{{ $fastestTripLabel }}</strong>
                    </div>
                    <div class="bus-v2-results-summary-item bus-v2-results-summary-item--wide">
                        <span>Fare window</span>
                        <strong>{{ $fareWindowLabel }}</strong>
                    </div>
                </div>
            </aside>

            <div class="bus-v2-filter-bar bus-v2-filter-bar--results bus-v2-filter-bar--premium">
                <div class="bus-v2-filter-quick">
                    <button type="button" class="bus-v2-filter-pill{{ $activeSort === '1' ? ' is-active' : '' }}" data-bus-v2-quick-sort="1">
                        <i class="fas fa-star"></i>
                        Recommended
                    </button>
                    <button type="button" class="bus-v2-filter-pill{{ $activeSort === '2' ? ' is-active' : '' }}" data-bus-v2-quick-sort="2">
                        <i class="fas fa-tag"></i>
                        {{ __('bus.results.quick_cheapest') }}
                    </button>
                    <button type="button" class="bus-v2-filter-pill{{ $activeSort === '3' ? ' is-active' : '' }}" data-bus-v2-quick-sort="3">
                        <i class="fas fa-bolt"></i>
                        {{ __('bus.results.quick_fastest') }}
                    </button>
                </div>

                <div class="bus-v2-filter-controls">
                    <div class="bus-v2-filter-group">
                        <label>{{ __('bus.results.carrier') }}</label>
                        <div class="bus-v2-filter-quick bus-v2-filter-quick--carrier">
                            <button type="button" class="bus-v2-filter-pill{{ ($searchData['sort_by_bus'] ?? '') === '' ? ' is-active' : '' }}" data-bus-v2-quick-carrier="">
                                <i class="fas fa-layer-group"></i>
                                {{ __('bus.results.all_carriers') }}
                            </button>
                            <button type="button" class="bus-v2-filter-pill{{ ($searchData['sort_by_bus'] ?? '') === '4' ? ' is-active' : '' }}" data-bus-v2-quick-carrier="4">
                                <i class="fas fa-bus"></i>
                                FlixBus
                            </button>
                            <button type="button" class="bus-v2-filter-pill{{ ($searchData['sort_by_bus'] ?? '') === '5' ? ' is-active' : '' }}" data-bus-v2-quick-carrier="5">
                                <i class="fas fa-bus-alt"></i>
                                BlaBlaBus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="bus-v2-result-section-heading">
        <div class="bus-v2-result-section-title">
            <span class="bus-v2-result-section-icon"><i class="fas fa-bus"></i></span>
            <div>
                <h3>{{ __('bus.results.departure') }}</h3>
                <p>{{ $searchData['from_name'] ?? '' }} &rarr; {{ $searchData['to_name'] ?? '' }} &middot; {{ $searchData['departure'] ?? '' }}</p>
            </div>
        </div>
        <span class="bus-v2-result-section-count">{{ $resultsCountLabel }}</span>
    </div>

    <div class="bus-v2-result-list bus-v2-result-list--premium">
        @foreach ($results as $index => $bus)
            @php
                $busType = $bus['bus_type'] ?? '';
                $isBla = !empty($busType) && (stripos($busType, 'blabla') !== false || stripos($busType, 'comuto') !== false || $busType === 'Mixed');
                $seatCount = (int) ($bus['total_selected_seats'] ?? 1);
                $operatorLabel = $isBla ? ($busType === 'Mixed' ? __('bus.results.mixed_operator') : 'BlaBlaBus') : 'FlixBus';
                $reserveLabel = trans_choice('bus.results.reserve_seat', $seatCount, ['count' => $seatCount]);
                $operatorLogo = $busType === 'Mixed' ? null : secure_asset($isBla ? 'images/search-bla.png' : 'images/search-flix.png');
                $legCount = max(1, count($bus['legs'] ?? []));
                $journeyMeta = $legCount === 1
                    ? __('bus.results.direct_trip')
                    : trans_choice('bus.results.segment_label', $legCount, ['count' => $legCount]);
                $isAvailable = round((float) ($bus['total_price'] ?? 0), 2) > 0;
                $tripPrice = round((float) ($bus['total_price'] ?? 0), 2);
                $tripMinutes = ((int) ($bus['duration_hour'] ?? 0) * 60) + (int) ($bus['duration_minutes'] ?? 0);
                $isCheapest = $lowestFare !== null && $tripPrice === $lowestFare;
                $isFastest = $fastestMinutes !== null && $tripMinutes === $fastestMinutes;
                $isBest = $index === 0;
                $amenities = $normalizeAmenities($bus['amenities'] ?? []);
                $journeyDetailsPayload = base64_encode(json_encode([
                    'from_name' => $bus['from_name'] ?? '',
                    'to_name' => $bus['to_name'] ?? '',
                    'departure' => $bus['departure'] ?? '',
                    'arrival' => $bus['arrival'] ?? '',
                    'bus_type' => $bus['bus_type'] ?? '',
                    'duration_hour' => $bus['duration_hour'] ?? 0,
                    'duration_minutes' => $bus['duration_minutes'] ?? 0,
                    'travel_date' => $bus['travel_date'] ?? ($searchData['departure'] ?? ''),
                    'amenities' => $amenities,
                    'stops' => is_array($bus['stops'] ?? null) ? $bus['stops'] : [],
                    'legs' => is_array($bus['legs'] ?? null) ? $bus['legs'] : [],
                ]));
            @endphp

            <article class="bus-v2-trip-card bus-v2-trip-card--premium{{ $isBla ? ' is-bla' : ' is-flix' }}{{ $isAvailable ? '' : ' is-disabled' }}" style="--bus-v2-stagger: {{ min($index, 12) * 55 }}ms">
                <div class="bus-v2-trip-card-top">
                    <div class="bus-v2-trip-badges">
                        @if ($isBest)
                            <span class="bus-v2-trip-chip bus-v2-trip-chip--best">Best</span>
                        @endif
                        @if ($isCheapest)
                            <span class="bus-v2-trip-chip bus-v2-trip-chip--best">Cheapest</span>
                        @endif
                        @if ($isFastest)
                            <span class="bus-v2-trip-chip bus-v2-trip-chip--speed">Fastest</span>
                        @endif
                        @if (!empty($bus['available_seats']))
                            <span class="bus-v2-trip-chip bus-v2-trip-chip--seat">
                                <i class="fas fa-chair"></i>
                                {{ trans_choice('bus.results.seats_left', (int) $bus['available_seats'], ['count' => (int) $bus['available_seats']]) }}
                            </span>
                        @endif
                        <span class="bus-v2-trip-chip bus-v2-trip-chip--pax bus-v2-mobile-only">
                            <i class="fas fa-users"></i>
                            {{ $passengerSummary }}
                        </span>
                    </div>

                    <div class="bus-v2-trip-status">
                        <span class="bus-v2-trip-status-pulse"></span>
                        Live fare
                    </div>
                </div>

                <div class="bus-v2-trip-card-body">
                    <div class="bus-v2-trip-main">
                        <div class="bus-v2-trip-stop">
                            <span class="bus-v2-trip-stop-label">{{ __('bus.results.departure') }}</span>
                            <strong>{{ $bus['departure'] ?? '--:--' }}</strong>
                            <span>{{ $bus['from_name'] ?? '' }}</span>
                        </div>

                        <div class="bus-v2-trip-timeline">
                            <div class="bus-v2-trip-track" aria-hidden="true">
                                <span class="bus-v2-trip-track-dot"></span>
                                <span class="bus-v2-trip-track-line"></span>
                                <span class="bus-v2-trip-track-bus"><i class="fas fa-bus"></i></span>
                                <span class="bus-v2-trip-track-line"></span>
                                <span class="bus-v2-trip-track-dot"></span>
                            </div>
                            <strong>{{ $bus['duration_hour'] ?? '0' }}h {{ $bus['duration_minutes'] ?? '0' }}m</strong>
                            <small>{{ $journeyMeta }}</small>
                        </div>

                        <div class="bus-v2-trip-stop bus-v2-trip-stop--arrival">
                            <span class="bus-v2-trip-stop-label">{{ __('bus.results.arrival') }}</span>
                            <strong>{{ $bus['arrival'] ?? '--:--' }}</strong>
                            <span>{{ $bus['to_name'] ?? '' }}</span>
                        </div>

                        <div class="bus-v2-route-operator-card {{ $isBla ? 'is-bla' : 'is-flix' }}">
                            <span class="bus-v2-route-operator-logo">
                                @if ($operatorLogo)
                                    <img src="{{ $operatorLogo }}" alt="{{ $operatorLabel }}">
                                @else
                                    <span class="bus-v2-carrier-badge-fallback">{{ strtoupper(substr($operatorLabel, 0, 1)) }}</span>
                                @endif
                            </span>
                            <span>{{ $operatorLabel }}</span>
                        </div>
                    </div>

                    <div class="bus-v2-trip-side">
                        @if (!empty($bus['available_seats']))
                            <div class="bus-v2-trip-side-seat">
                                <i class="fas fa-chair" aria-hidden="true"></i>
                                <span>{{ trans_choice('bus.results.seats_left', (int) $bus['available_seats'], ['count' => (int) $bus['available_seats']]) }}</span>
                            </div>
                        @endif

                        <div class="bus-v2-trip-price">
                            <span>{{ __('bus.results.total_fare') }}</span>
                            <strong>€{{ number_format($bus['total_price'] ?? 0, 2) }}</strong>
                            <small>Instant confirmation</small>
                        </div>

                        <div class="bus-v2-trip-action">
                            <button
                                type="button"
                                class="bus-v2-button bus-v2-button--ghost bus-v2-result-details-btn"
                                data-rt-view-details
                                data-rt-direction="outbound"
                                data-rt-trip="{{ $journeyDetailsPayload }}">
                                <i class="fas fa-list-ul"></i>
                                Voir les détails
                            </button>

                            @if (!$isAvailable)
                                <span class="bus-v2-chip">{{ __('bus.results.not_available') }}</span>
                            @elseif ($isBla)
                                <form action="{{ route('bus.v2.reserve.bla') }}" method="POST" class="js-bus-v2-ajax-form">
                                    @csrf
                                    <input type="hidden" name="from_id" value="{{ $bus['from_id'] }}">
                                    <input type="hidden" name="from_name" value="{{ $bus['from_name'] }}">
                                    <input type="hidden" name="to_id" value="{{ $bus['to_id'] }}">
                                    <input type="hidden" name="to_name" value="{{ $bus['to_name'] }}">
                                    <input type="hidden" name="bus_type" value="{{ $bus['bus_type'] }}">
                                    <input type="hidden" name="adult" value="{{ $bus['adult'] }}">
                                    <input type="hidden" name="children" value="{{ $bus['children'] }}">
                                    <input type="hidden" name="bikes" value="{{ $bus['bikes'] }}">
                                    <input type="hidden" name="currency" value="{{ $bus['currency'] }}">
                                    <input type="hidden" name="departure" value="{{ $bus['departure'] }}">
                                    <input type="hidden" name="arrival" value="{{ $bus['arrival'] }}">
                                    <input type="hidden" name="total_selected_seats" value="{{ $bus['total_selected_seats'] }}">
                                    <input type="hidden" name="total_price" value="{{ $bus['total_price'] }}">
                                    <input type="hidden" name="available_seats" value="{{ $bus['available_seats'] }}">
                                    <input type="hidden" name="discount" value="{{ $bus['discount'] }}">
                                    <input type="hidden" name="duration_hour" value="{{ $bus['duration_hour'] }}">
                                    <input type="hidden" name="duration_minutes" value="{{ $bus['duration_minutes'] }}">
                                    <input type="hidden" name="ref_id" value="{{ $bus['ref_id'] }}">
                                    <input type="hidden" name="passenger_id" value="{{ $bus['passenger_id'] }}">
                                    <input type="hidden" name="validity_end_date" value="{{ $bus['validity_end_date'] }}">
                                    <input type="hidden" name="product_code" value="{{ $bus['product_code'] }}">
                                    <input type="hidden" name="journey_details" value="{{ $journeyDetailsPayload }}">
                                    @foreach ($bus['legs'] as $legIndex => $leg)
                                        <input type="hidden" name="legs[{{ $legIndex }}][from_id]" value="{{ $leg['from_id'] }}">
                                        <input type="hidden" name="legs[{{ $legIndex }}][from_name]" value="{{ $leg['from_name'] }}">
                                        <input type="hidden" name="legs[{{ $legIndex }}][to_id]" value="{{ $leg['to_id'] }}">
                                        <input type="hidden" name="legs[{{ $legIndex }}][to_name]" value="{{ $leg['to_name'] }}">
                                        <input type="hidden" name="legs[{{ $legIndex }}][departure]" value="{{ $leg['departure'] }}">
                                        <input type="hidden" name="legs[{{ $legIndex }}][arrival]" value="{{ $leg['arrival'] }}">
                                        <input type="hidden" name="legs[{{ $legIndex }}][bus_id]" value="{{ $leg['bus_id'] }}">
                                        <input type="hidden" name="legs[{{ $legIndex }}][service_name]" value="{{ $leg['service_name'] }}">
                                        <input type="hidden" name="legs[{{ $legIndex }}][bus_uid]" value="{{ $leg['bus_uid'] }}">
                                        <input type="hidden" name="legs[{{ $legIndex }}][bus_type]" value="{{ $leg['bus_type'] }}">
                                        <input type="hidden" name="legs[{{ $legIndex }}][tariff_code]" value="{{ $leg['traffic_code'] }}">
                                    @endforeach
                                    <button type="submit" class="bus-v2-button bus-v2-button--secondary" data-loading-text="{{ __('bus.messages.processing') }}">{{ $reserveLabel }}</button>
                                </form>
                            @else
                                <form action="{{ route('bus.v2.reserve.flix') }}" method="POST" class="js-bus-v2-ajax-form">
                                    @csrf
                                    <input type="hidden" name="trip_uid" value="{{ $bus['bus_uid'] }}">
                                    <input type="hidden" name="adult" value="{{ $bus['adult'] }}">
                                    <input type="hidden" name="children" value="{{ $bus['children'] }}">
                                    <input type="hidden" name="bikes" value="{{ $bus['bikes'] }}">
                                    <input type="hidden" name="currency" value="{{ $bus['currency'] }}">
                                    <input type="hidden" name="total_price" value="{{ $bus['total_price'] }}">
                                    <input type="hidden" name="journey_details" value="{{ $journeyDetailsPayload }}">
                                    <button type="submit" class="bus-v2-button bus-v2-button--primary" data-loading-text="{{ __('bus.messages.processing') }}">{{ $reserveLabel }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                @include('v2.service.bus.partials.amenity-chips', [
                    'amenities' => $amenities,
                    'amenityLimit' => 6,
                ])
            </article>
        @endforeach
    </div>

    <div class="bus-v2-trip-modal" data-rt-trip-modal hidden>
        <div class="bus-v2-trip-modal-backdrop" data-rt-modal-close></div>
        <div class="bus-v2-trip-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="busV2TripModalTitle">
            <button type="button" class="bus-v2-trip-modal-close" data-rt-modal-close aria-label="Close details">
                <i class="fas fa-times"></i>
            </button>
            <div class="bus-v2-trip-modal-head">
                <span class="bus-v2-results-kicker" data-rt-modal-direction>Trip details</span>
                <h3 id="busV2TripModalTitle" data-rt-modal-title>Route details</h3>
                <p data-rt-modal-subtitle></p>
            </div>
            <div class="bus-v2-trip-modal-body" data-rt-modal-body></div>
        </div>
    </div>
</section>

