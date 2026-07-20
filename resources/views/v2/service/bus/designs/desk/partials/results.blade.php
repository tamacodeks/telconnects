@php
    $tripType = $searchData['trip_type'] ?? 'one_way';
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
    $flixLogo = secure_asset('images/search-flix.png');
    $blaLogo = secure_asset('images/search-bla.png');
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

<section class="bus-v2-panel bus-v2-results-panel bus-v2-desk-results">
    <div class="bus-v2-desk-results-head">
        <div>
            <span class="bus-v2-results-kicker">{{ __('bus.results.live_availability') }}</span>
            <h2>{{ __('bus.results.title') }}</h2>
            <p>
                {{ __('bus.results.summary', [
                    'options' => trans_choice('bus.passenger.option', (int) $totalResults, ['count' => (int) $totalResults]),
                    'from' => $searchData['from_name'] ?? '',
                    'to' => $searchData['to_name'] ?? '',
                    'date' => $searchData['departure'] ?? '',
                ]) }}
            </p>
        </div>
        <div class="bus-v2-desk-results-score">
            <span>{{ trans_choice('bus.passenger.option', (int) $totalResults, ['count' => (int) $totalResults]) }}</span>
            <strong>{{ (int) $totalResults }}</strong>
        </div>
    </div>

    <form id="busV2SortForm" action="{{ route('bus.v2.search') }}" method="POST" class="js-bus-v2-ajax-form bus-v2-filter-shell bus-v2-desk-filter">
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

        <div class="bus-v2-desk-filter-main">
            <div class="bus-v2-filter-quick">
                <button type="button" class="bus-v2-filter-pill{{ $activeSort === '1' ? ' is-active' : '' }}" data-bus-v2-quick-sort="1">
                    <i class="fas fa-clock"></i>
                    {{ __('bus.results.quick_earliest') }}
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
                    <label for="busV2SortBy">{{ __('bus.results.sort_by') }}</label>
                    <select id="busV2SortBy" name="sort_by" class="bus-v2-select js-bus-v2-sort-select">
                        <option value="1" {{ $activeSort === '1' ? 'selected' : '' }}>{{ __('bus.results.sort_earliest') }}</option>
                        <option value="2" {{ $activeSort === '2' ? 'selected' : '' }}>{{ __('bus.results.sort_lowest_price') }}</option>
                        <option value="3" {{ $activeSort === '3' ? 'selected' : '' }}>{{ __('bus.results.sort_shortest') }}</option>
                    </select>
                </div>
                <div class="bus-v2-filter-group">
                    <label for="busV2SortCarrier">{{ __('bus.results.carrier') }}</label>
                    <select id="busV2SortCarrier" name="sort_by_bus" class="bus-v2-select js-bus-v2-sort-select">
                        <option value="">{{ __('bus.results.all_carriers') }}</option>
                        <option value="4" {{ ($searchData['sort_by_bus'] ?? '') === '4' ? 'selected' : '' }}>FlixBus</option>
                        <option value="5" {{ ($searchData['sort_by_bus'] ?? '') === '5' ? 'selected' : '' }}>BlaBlaBus</option>
                    </select>
                </div>
            </div>
        </div>
    </form>

    <div class="bus-v2-desk-trip-list">
        @foreach ($results as $bus)
            @php
                $busType = $bus['bus_type'] ?? '';
                $isBla = !empty($busType) && (stripos($busType, 'blabla') !== false || stripos($busType, 'comuto') !== false || $busType === 'Mixed');
                $seatCount = (int) ($bus['total_selected_seats'] ?? 1);
                $adultMix = (int) ($bus['adult'] ?? 0);
                $childMix = (int) ($bus['children'] ?? 0);
                $operatorLabel = $isBla ? ($busType === 'Mixed' ? __('bus.results.mixed_operator') : 'BlaBlaBus') : 'FlixBus';
                $reserveLabel = trans_choice('bus.results.reserve_seat', $seatCount, ['count' => $seatCount]);
                $passengerMixLabel = trans_choice('bus.passenger.adult', $adultMix, ['count' => $adultMix]) . ($childMix > 0 ? ' / ' . trans_choice('bus.passenger.child', $childMix, ['count' => $childMix]) : '');
                $availableSeats = (int) ($bus['available_seats'] ?? 0);
                $seatLeftLabel = $availableSeats > 0 ? trans_choice('bus.results.seats_left', $availableSeats, ['count' => $availableSeats]) : null;
                $legCount = max(1, count($bus['legs'] ?? []));
                $journeyMeta = $legCount === 1 ? __('bus.results.direct_trip') : trans_choice('bus.results.segment_label', $legCount, ['count' => $legCount]);
                $isAvailable = number_format($bus['total_price'] ?? 0, 2) !== '0.00';
                $amenities = $normalizeAmenities($bus['amenities'] ?? []);
                $durationLabel = ($bus['duration_hour'] ?? '0') . 'h ' . ($bus['duration_minutes'] ?? '0') . 'm';
                $deskAmenities = [
                    ['key' => 'e_ticket', 'label' => 'E-ticket', 'icon' => 'fas fa-ticket-alt'],
                    ['key' => 'assigned_seating', 'label' => 'Assigned seating', 'icon' => 'fas fa-chair'],
                ];
                $deskAmenityKeys = ['e_ticket' => true, 'assigned_seating' => true];
                foreach ($amenities as $amenity) {
                    $amenityKey = trim((string) ($amenity['key'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '_', $amenity['label'] ?? ''))));
                    $amenityLabel = trim((string) ($amenity['label'] ?? ''));
                    if ($amenityLabel === '' || isset($deskAmenityKeys[$amenityKey])) {
                        continue;
                    }
                    $deskAmenities[] = [
                        'key' => $amenityKey,
                        'label' => $amenityLabel,
                        'icon' => trim((string) ($amenity['icon'] ?? 'fas fa-check')),
                    ];
                    $deskAmenityKeys[$amenityKey] = true;
                }
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

            <article class="bus-v2-trip-card bus-v2-desk-trip bus-v2-desk-result-card{{ $isBla ? ' is-bla' : ' is-flix' }}{{ $isAvailable ? '' : ' is-disabled' }}">
                <div class="bus-v2-desk-result-main">
                    <div class="bus-v2-desk-trip-carrier bus-v2-desk-result-carrier">
                        <div class="bus-v2-desk-logo-card {{ $isBla ? 'is-bla' : 'is-flix' }}">
                            <img src="{{ $isBla ? $blaLogo : $flixLogo }}" alt="{{ $operatorLabel }}" class="bus-v2-carrier-logo">
                        </div>
                        <span class="bus-v2-desk-info-pill">
                            <i class="fas fa-user"></i>
                            {{ $passengerMixLabel }}
                        </span>
                        @if ($seatLeftLabel)
                            <span class="bus-v2-desk-info-pill bus-v2-desk-info-pill--seat">
                                <i class="fas fa-chair"></i>
                                {{ $seatLeftLabel }}
                            </span>
                        @endif
                    </div>

                    <div class="bus-v2-desk-trip-route bus-v2-desk-result-route">
                        <div class="bus-v2-desk-result-stop">
                            <span class="bus-v2-trip-stop-label">{{ __('bus.results.departure') }}</span>
                            <strong>{{ $bus['departure'] ?? '--:--' }}</strong>
                            <small>{{ $bus['from_name'] ?? '' }}</small>
                        </div>

                        <div class="bus-v2-trip-timeline bus-v2-desk-result-timeline">
                            <div class="bus-v2-desk-track" aria-hidden="true">
                                <span class="bus-v2-desk-track-dot"></span>
                                <span class="bus-v2-desk-track-line"></span>
                                <span class="bus-v2-desk-track-bus"><i class="fas fa-bus"></i></span>
                                <span class="bus-v2-desk-track-line"></span>
                                <span class="bus-v2-desk-track-dot"></span>
                            </div>
                            <strong>{{ $durationLabel }}</strong>
                            <small>{{ $journeyMeta }}</small>
                        </div>

                        <div class="bus-v2-desk-result-stop bus-v2-desk-result-stop--arrival">
                            <span class="bus-v2-trip-stop-label">{{ __('bus.results.arrival') }}</span>
                            <strong>{{ $bus['arrival'] ?? '--:--' }}</strong>
                            <small>{{ $bus['to_name'] ?? '' }}</small>
                        </div>
                    </div>

                    <div class="bus-v2-desk-trip-buy bus-v2-desk-result-buy">
                    <div class="bus-v2-trip-price bus-v2-desk-price">
                        <span>{{ __('bus.results.total_fare') }}</span>
                        <strong>&euro;{{ number_format($bus['total_price'] ?? 0, 2) }}</strong>
                        <small>Instant confirmation</small>
                    </div>

                    <div class="bus-v2-desk-result-actions">
                    @if (!$isAvailable)
                        <span class="bus-v2-chip">{{ __('bus.results.not_available') }}</span>
                    @elseif ($isBla)
                        <form action="{{ route('bus.v2.reserve.bla') }}" method="POST" class="js-bus-v2-ajax-form bus-v2-desk-reserve-form">
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
                            @foreach (($bus['legs'] ?? []) as $index => $leg)
                                <input type="hidden" name="legs[{{ $index }}][from_id]" value="{{ $leg['from_id'] }}">
                                <input type="hidden" name="legs[{{ $index }}][from_name]" value="{{ $leg['from_name'] }}">
                                <input type="hidden" name="legs[{{ $index }}][to_id]" value="{{ $leg['to_id'] }}">
                                <input type="hidden" name="legs[{{ $index }}][to_name]" value="{{ $leg['to_name'] }}">
                                <input type="hidden" name="legs[{{ $index }}][departure]" value="{{ $leg['departure'] }}">
                                <input type="hidden" name="legs[{{ $index }}][arrival]" value="{{ $leg['arrival'] }}">
                                <input type="hidden" name="legs[{{ $index }}][bus_id]" value="{{ $leg['bus_id'] }}">
                                <input type="hidden" name="legs[{{ $index }}][service_name]" value="{{ $leg['service_name'] }}">
                                <input type="hidden" name="legs[{{ $index }}][bus_uid]" value="{{ $leg['bus_uid'] }}">
                                <input type="hidden" name="legs[{{ $index }}][bus_type]" value="{{ $leg['bus_type'] }}">
                                <input type="hidden" name="legs[{{ $index }}][tariff_code]" value="{{ $leg['traffic_code'] }}">
                            @endforeach
                            <button type="submit" class="bus-v2-button bus-v2-button--secondary" data-loading-text="{{ __('bus.messages.processing') }}">{{ $reserveLabel }}</button>
                        </form>
                    @else
                        <form action="{{ route('bus.v2.reserve.flix') }}" method="POST" class="js-bus-v2-ajax-form bus-v2-desk-reserve-form">
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

                @include('v2.service.bus.designs.desk.partials.amenity-chips', [
                    'amenities' => $deskAmenities,
                    'amenityClass' => 'bus-v2-desk-trip-amenity-rail',
                    'amenityLimit' => 6,
                ])
            </article>
        @endforeach
    </div>
</section>
