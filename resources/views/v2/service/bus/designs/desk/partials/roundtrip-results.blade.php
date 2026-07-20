@php
    $tripType = $searchData['trip_type'] ?? 'round_trip';
    $adultCount = max(1, (int) ($searchData['adult'] ?? 1));
    $childCount = max(0, (int) ($searchData['children'] ?? 0));
    $passengerSummary = implode(', ', array_filter([
        trans_choice('bus.passenger.adult', $adultCount, ['count' => $adultCount]),
        $childCount > 0 ? trans_choice('bus.passenger.child', $childCount, ['count' => $childCount]) : null,
    ]));
    $outboundResults = array_values($results['outbound'] ?? []);
    $inboundResults  = array_values($results['inbound']  ?? []);
    $outboundCount = count($outboundResults);
    $inboundCount = count($inboundResults);
    $formatLegDate = function ($value) {
        $timestamp = strtotime((string) $value);
        return $timestamp ? date('D, d M Y', $timestamp) : (string) $value;
    };

    $flixLogo = secure_asset('images/search-flix.png');
    $blaLogo  = secure_asset('images/search-bla.png');

    $providerRoutes = [
        'flix' => route('bus.v2.reserve.flix'),
        'bla'  => route('bus.v2.reserve.bla'),
    ];
    $providerLabels = ['flix' => 'FlixBus', 'bla' => 'BlaBlaBus'];
    $providerBtnClass = ['flix' => 'bus-v2-button--primary', 'bla' => 'bus-v2-button--secondary'];

    $getProvider = function ($trip) {
        $t = $trip['bus_type'] ?? '';
        return (!empty($t) && (stripos($t, 'blabla') !== false || stripos($t, 'comuto') !== false || $t === 'Mixed'))
            ? 'bla' : 'flix';
    };

    $normalizeAmenities = function ($items) {
        $map = [
            'wifi' => ['key' => 'wifi', 'label' => 'Free Wi-Fi', 'icon' => 'fas fa-wifi'],
            'power_sockets' => ['key' => 'outlets', 'label' => 'Outlets', 'icon' => 'fas fa-plug'],
            'power_socket' => ['key' => 'outlets', 'label' => 'Outlets', 'icon' => 'fas fa-plug'],
            'power_outlets' => ['key' => 'outlets', 'label' => 'Outlets', 'icon' => 'fas fa-plug'],
            'power_outlet' => ['key' => 'outlets', 'label' => 'Outlets', 'icon' => 'fas fa-plug'],
            'usb' => ['key' => 'outlets', 'label' => 'USB ports', 'icon' => 'fas fa-plug'],
            'air_conditioning' => ['key' => 'ac', 'label' => 'Air conditioning', 'icon' => 'fas fa-snowflake'],
            'ac' => ['key' => 'ac', 'label' => 'Air conditioning', 'icon' => 'fas fa-snowflake'],
            'toilet' => ['key' => 'toilet', 'label' => 'Toilet', 'icon' => 'fas fa-restroom'],
            'wc' => ['key' => 'toilet', 'label' => 'Toilet', 'icon' => 'fas fa-restroom'],
            'bike_slot' => ['key' => 'bike', 'label' => 'Bike slot', 'icon' => 'fas fa-bicycle'],
            'bike' => ['key' => 'bike', 'label' => 'Bike slot', 'icon' => 'fas fa-bicycle'],
            'family_package' => ['key' => 'family', 'label' => 'Family friendly', 'icon' => 'fas fa-child'],
            'snacks' => ['key' => 'snacks', 'label' => 'Snacks & drinks', 'icon' => 'fas fa-utensils'],
            'entertainment' => ['key' => 'entertain', 'label' => 'Entertainment', 'icon' => 'fas fa-tv'],
            'accessible' => ['key' => 'access', 'label' => 'Accessible', 'icon' => 'fas fa-wheelchair'],
            'wheelchair' => ['key' => 'access', 'label' => 'Accessible', 'icon' => 'fas fa-wheelchair'],
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

    $groupedInbound = ['flix' => [], 'bla' => []];
    foreach ($inboundResults as $trip) {
        $groupedInbound[$getProvider($trip)][] = $trip;
    }

    $formatTrip = function ($trip) { return base64_encode(json_encode($trip)); };

    $cardMeta = function ($bus) use ($normalizeAmenities) {
        $adultMix  = (int) ($bus['adult']    ?? 0);
        $childMix  = (int) ($bus['children'] ?? 0);
        $busType   = $bus['bus_type'] ?? '';
        $isBla     = !empty($busType) && (stripos($busType, 'blabla') !== false || stripos($busType, 'comuto') !== false || $busType === 'Mixed');
        $legCount  = max(1, count($bus['legs'] ?? []));
        $availableSeats = (int) ($bus['available_seats'] ?? 0);
        $amenities = $normalizeAmenities($bus['amenities'] ?? []);
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
        return [
            'isBla'      => $isBla,
            'operator'   => $isBla ? ($busType === 'Mixed' ? __('bus.results.mixed_operator') : 'BlaBlaBus') : 'FlixBus',
            'logo'       => $isBla ? secure_asset('images/search-bla.png') : secure_asset('images/search-flix.png'),
            'pax'        => trans_choice('bus.passenger.adult', $adultMix, ['count' => $adultMix])
                             . ($childMix > 0 ? ' / ' . trans_choice('bus.passenger.child', $childMix, ['count' => $childMix]) : ''),
            'seatLabel'  => $availableSeats > 0 ? trans_choice('bus.results.seats_left', $availableSeats, ['count' => $availableSeats]) : null,
            'journey'    => $legCount === 1 ? __('bus.results.direct_trip') : trans_choice('bus.results.segment_label', $legCount, ['count' => $legCount]),
            'available'  => number_format($bus['total_price'] ?? 0, 2) !== '0.00',
            'window'     => trim(($bus['departure'] ?? '--:--') . ' – ' . ($bus['arrival'] ?? '--:--')),
            'route'      => trim(($bus['from_name'] ?? '') . ' → ' . ($bus['to_name'] ?? '')),
            'duration'   => (int) ($bus['duration_hour'] ?? 0) . 'h ' . (int) ($bus['duration_minutes'] ?? 0) . 'm',
            'price'      => '€ ' . number_format((float) ($bus['total_price'] ?? 0), 2),
            'amenities'  => $deskAmenities,
        ];
    };
@endphp

<section class="bus-v2-panel bus-v2-desk-results bus-v2-roundtrip-results bus-v2-rt-split bus-v2-rt-stepflow is-choosing-departure" data-bus-v2-roundtrip-flow>

    {{-- ── Header ────────────────────────────────────────────────────────── --}}
    <div class="bus-v2-desk-results-head">
        <div>
            <span class="bus-v2-results-kicker">{{ __('bus.results.live_availability') }}</span>
            <h2>{{ __('bus.results.title') }}</h2>
            <p>{{ __('bus.results.summary', [
                'options' => trans_choice('bus.passenger.option', (int) $totalResults, ['count' => (int) $totalResults]),
                'from'    => $searchData['from_name'] ?? '',
                'to'      => $searchData['to_name']   ?? '',
                'date'    => $searchData['departure']  ?? '',
            ]) }}</p>
            <div class="bus-v2-results-meta-row" style="margin-top:14px">
                <span class="bus-v2-results-meta-chip"><i class="far fa-calendar-alt"></i> {{ $searchData['departure'] ?? '' }}</span>
                <span class="bus-v2-results-meta-chip"><i class="fas fa-calendar-check"></i> {{ $searchData['return_date'] ?? '' }}</span>
                <span class="bus-v2-results-meta-chip"><i class="fas fa-users"></i> {{ $passengerSummary }}</span>
            </div>
        </div>
        <div class="bus-v2-desk-results-score">
            <span>Round Trip</span>
            <strong>{{ (int) $totalResults }}</strong>
        </div>
    </div>

    {{-- ── Selection recap bar ─────────────────────────────────────────────── --}}
    <div class="bus-v2-rt-selection-bar" data-rt-selection-bar hidden>
        <div class="bus-v2-rt-selection-leg" data-rt-sel-outbound>
            <span class="bus-v2-rt-selection-label"><i class="fas fa-plane-departure"></i> Departure</span>
            <strong data-rt-recap-window>—</strong>
            <small data-rt-recap-route></small>
        </div>
        <div class="bus-v2-rt-selection-arrow"><i class="fas fa-exchange-alt"></i></div>
        <div class="bus-v2-rt-selection-leg bus-v2-rt-selection-leg--pending" data-rt-sel-return>
            <span class="bus-v2-rt-selection-label"><i class="fas fa-plane-arrival"></i> Return</span>
            <strong>Choose a return trip →</strong>
        </div>
        <button type="button" class="bus-v2-button bus-v2-button--ghost bus-v2-rt-change-departure" data-rt-change-departure>
            <i class="fas fa-arrow-left"></i>
            Change departure
        </button>
    </div>

    {{-- ── Two-column body ──────────────────────────────────────────────────── --}}
    <div class="bus-v2-rt-split-body">

        {{-- LEFT: Outbound ──────────────────────────────────────────────────── --}}
        <div class="bus-v2-rt-split-col bus-v2-rt-split-col--outbound">
            <div class="bus-v2-rt-split-col-head bus-v2-rt-leg-head bus-v2-rt-leg-head--outbound">
                <div class="bus-v2-rt-leg-head-main">
                    <span class="bus-v2-rt-leg-icon"><i class="fas fa-arrow-right"></i></span>
                    <div class="bus-v2-rt-leg-copy">
                        <span class="bus-v2-rt-leg-kicker">Step 1</span>
                        <h3>Departure</h3>
                        <div class="bus-v2-rt-leg-route">
                            <strong>{{ $searchData['from_name'] ?? '' }}</strong>
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            <strong>{{ $searchData['to_name'] ?? '' }}</strong>
                        </div>
                    </div>
                </div>
                <div class="bus-v2-rt-leg-meta">
                    <span class="bus-v2-rt-leg-date">
                        <i class="far fa-calendar-alt" aria-hidden="true"></i>
                        {{ $formatLegDate($searchData['departure'] ?? '') }}
                    </span>
                    <span class="bus-v2-rt-split-count bus-v2-rt-leg-count">
                        <strong>{{ $outboundCount }}</strong>
                        <span>{{ $outboundCount === 1 ? 'trip' : 'trips' }}</span>
                    </span>
                </div>
            </div>

            <div class="bus-v2-desk-trip-list bus-v2-rt-split-list">
                @forelse ($outboundResults as $bus)
                    @php
                        $m        = $cardMeta($bus);
                        $provider = $m['isBla'] ? 'bla' : 'flix';
                        $hasReturn = !empty($groupedInbound[$provider]);
                        $selectable = $m['available'] && $hasReturn;
                    @endphp

                    <article class="bus-v2-trip-card bus-v2-desk-trip bus-v2-desk-result-card {{ $m['isBla'] ? 'is-bla' : 'is-flix' }}{{ $selectable ? '' : ' is-disabled' }} bus-v2-rt-outbound-card"
                        data-rt-outbound-card
                        data-provider="{{ $provider }}"
                        data-trip-value="{{ $formatTrip($bus) }}"
                        data-trip-window="{{ $m['window'] }}"
                        data-trip-route="{{ $m['route'] }}"
                        data-trip-duration="{{ $m['duration'] }}"
                        data-trip-price="{{ $m['price'] }}"
                    >
                        <div class="bus-v2-desk-result-main">
                            <div class="bus-v2-desk-trip-carrier bus-v2-desk-result-carrier">
                                <div class="bus-v2-desk-logo-card {{ $m['isBla'] ? 'is-bla' : 'is-flix' }}">
                                    <img src="{{ $m['logo'] }}" alt="{{ $m['operator'] }}" class="bus-v2-carrier-logo">
                                </div>
                                <span class="bus-v2-desk-info-pill">
                                    <i class="fas fa-user"></i>
                                    {{ $m['pax'] }}
                                </span>
                                @if ($m['seatLabel'])
                                    <span class="bus-v2-desk-info-pill bus-v2-desk-info-pill--seat">
                                        <i class="fas fa-chair"></i>
                                        {{ $m['seatLabel'] }}
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
                                    <strong>{{ $m['duration'] }}</strong>
                                    <small>{{ $m['journey'] }}</small>
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
                                <div class="bus-v2-desk-result-actions bus-v2-rt-action-stack">
                                    @if (!$selectable)
                                        <span class="bus-v2-chip">{{ !$m['available'] ? __('bus.results.not_available') : 'No matching return' }}</span>
                                    @else
                                        <button type="button"
                                            class="bus-v2-button {{ $m['isBla'] ? 'bus-v2-button--secondary' : 'bus-v2-button--primary' }} bus-v2-rt-select-btn"
                                            data-rt-select-outbound>
                                            <i class="fas fa-check bus-v2-rt-select-btn-check" hidden></i>
                                            <span class="bus-v2-rt-select-btn-label">Select departure</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @include('v2.service.bus.designs.desk.partials.amenity-chips', [
                            'amenities' => $m['amenities'],
                            'amenityClass' => 'bus-v2-desk-trip-amenity-rail',
                            'amenityLimit' => 6,
                        ])
                    </article>
                @empty
                    <div class="bus-v2-empty-copy">{{ __('bus.results.not_available') }}</div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT: Return trips --}}
        <div class="bus-v2-rt-split-col bus-v2-rt-split-col--return">
            <div class="bus-v2-rt-split-col-head bus-v2-rt-leg-head bus-v2-rt-leg-head--return">
                <div class="bus-v2-rt-leg-head-main">
                    <span class="bus-v2-rt-leg-icon"><i class="fas fa-arrow-left"></i></span>
                    <div class="bus-v2-rt-leg-copy">
                        <span class="bus-v2-rt-leg-kicker">Step 2</span>
                        <h3>Return</h3>
                        <div class="bus-v2-rt-leg-route">
                            <strong>{{ $searchData['to_name'] ?? '' }}</strong>
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            <strong>{{ $searchData['from_name'] ?? '' }}</strong>
                        </div>
                    </div>
                </div>
                <div class="bus-v2-rt-leg-meta">
                    <span class="bus-v2-rt-leg-date">
                        <i class="far fa-calendar-alt" aria-hidden="true"></i>
                        {{ $formatLegDate($searchData['return_date'] ?? '') }}
                    </span>
                    <span class="bus-v2-rt-split-count bus-v2-rt-leg-count">
                        <strong>{{ $inboundCount }}</strong>
                        <span>{{ $inboundCount === 1 ? 'trip' : 'trips' }}</span>
                    </span>
                </div>
            </div>

            <div class="bus-v2-rt-return-placeholder" data-rt-return-placeholder>
                <div class="bus-v2-rt-return-placeholder-inner">
                    <span class="bus-v2-rt-placeholder-icon"><i class="fas fa-arrow-circle-left"></i></span>
                    <strong>Select a departure first</strong>
                    <p>Choose your outbound trip to see available return journeys</p>
                </div>
            </div>

            @foreach (['flix', 'bla'] as $provKey)
                <div data-rt-returns-for="{{ $provKey }}" hidden>
                    <form
                        action="{{ $providerRoutes[$provKey] }}"
                        method="POST"
                        class="js-bus-v2-ajax-form"
                        data-rt-form
                    >
                        @csrf
                        <input type="hidden" name="trip_type" value="{{ $tripType }}">
                        <input type="hidden" name="outbound_trip" data-rt-outbound-input value="">

                        <div class="bus-v2-desk-trip-list bus-v2-rt-split-list">
                            @forelse ($groupedInbound[$provKey] as $bus)
                                @php $m2 = $cardMeta($bus); @endphp

                                <label class="bus-v2-trip-card bus-v2-desk-trip bus-v2-desk-result-card bus-v2-rt-return-card {{ $m2['isBla'] ? 'is-bla' : 'is-flix' }}{{ $m2['available'] ? '' : ' is-disabled' }}"
                                    data-trip-value="{{ $formatTrip($bus) }}">
                                    <input
                                        type="radio"
                                        name="return_trip"
                                        value="{{ $formatTrip($bus) }}"
                                        class="bus-v2-rt-return-radio"
                                        data-rt-return-radio
                                        {{ $m2['available'] ? '' : 'disabled' }}
                                    >

                                    <div class="bus-v2-desk-result-main">
                                        <div class="bus-v2-desk-trip-carrier bus-v2-desk-result-carrier">
                                            <div class="bus-v2-desk-logo-card {{ $m2['isBla'] ? 'is-bla' : 'is-flix' }}">
                                                <img src="{{ $m2['logo'] }}" alt="{{ $m2['operator'] }}" class="bus-v2-carrier-logo">
                                            </div>
                                            <span class="bus-v2-desk-info-pill">
                                                <i class="fas fa-user"></i>
                                                {{ $m2['pax'] }}
                                            </span>
                                            @if ($m2['seatLabel'])
                                                <span class="bus-v2-desk-info-pill bus-v2-desk-info-pill--seat">
                                                    <i class="fas fa-chair"></i>
                                                    {{ $m2['seatLabel'] }}
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
                                                <strong>{{ $m2['duration'] }}</strong>
                                                <small>{{ $m2['journey'] }}</small>
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
                                            <div class="bus-v2-desk-result-actions bus-v2-trip-action bus-v2-rt-return-action">
                                                <button
                                                    type="button"
                                                    class="bus-v2-button {{ $providerBtnClass[$provKey] }} bus-v2-rt-return-select-btn"
                                                    data-rt-select-return
                                                    {{ $m2['available'] ? '' : 'disabled' }}>
                                                    <i class="fas fa-check bus-v2-rt-return-select-check" hidden></i>
                                                    <span class="bus-v2-rt-return-select-label">Select return</span>
                                                </button>
                                                <button
                                                    type="submit"
                                                    class="bus-v2-button {{ $providerBtnClass[$provKey] }} bus-v2-rt-card-continue"
                                                    data-loading-text="{{ __('bus.messages.processing') }}"
                                                    data-rt-card-submit
                                                    disabled>
                                                    Continue to Passengers
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @include('v2.service.bus.designs.desk.partials.amenity-chips', [
                                        'amenities' => $m2['amenities'],
                                        'amenityClass' => 'bus-v2-desk-trip-amenity-rail',
                                        'amenityLimit' => 6,
                                    ])
                                </label>
                            @empty
                                <div class="bus-v2-empty-copy">No return trips with {{ $providerLabels[$provKey] }}</div>
                            @endforelse
                        </div>

                    </form>
                </div>
            @endforeach
        </div>

    </div>{{-- /bus-v2-rt-split-body --}}

</section>
