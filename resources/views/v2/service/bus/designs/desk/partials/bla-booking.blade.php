@php
    $blaReservation = is_array($blaBooking) ? ($blaBooking['data']['booking'] ?? null) : null;
    $bookingPassengers = $blaReservation['passengers'] ?? [];
    $blaPassengers  = $blaReservation['ui_passengers'] ?? $bookingPassengers;
    $outboundSegs   = $blaReservation['outbound_booking_tariff_segments'] ?? [];
    $inboundSegs    = $blaReservation['inbound_booking_tariff_segments'] ?? [];
    $blaSegments    = array_merge($outboundSegs, $inboundSegs);
    $firstSeg       = $blaSegments[0] ?? null;
    $lastSeg        = !empty($blaSegments) ? $blaSegments[count($blaSegments) - 1] : $firstSeg;
    $checkoutPrice  = isset($blaPrice) && $blaPrice !== '' ? $blaPrice : ($blaReservation['total_price'] ?? '');
    $providerPrice  = $blaReservation['total_price'] ?? $checkoutPrice;
    $passengerCount = count($blaPassengers);
    $expiryTs       = (int) ($bookingExpiresAt ?? 0);
    $isExpired      = !empty($bookingExpired);
    $isRoundTrip    = !empty($inboundSegs);
    $storedJourneyDetails = $blaBooking['journey_details'] ?? [];
    $journeyDetailsList = isset($storedJourneyDetails[0]) && is_array($storedJourneyDetails[0])
        ? $storedJourneyDetails
        : (!empty($storedJourneyDetails) ? [$storedJourneyDetails] : []);
@endphp

@if ($blaReservation && $firstSeg)
<section class="bco bco--bla bco--desk" data-booking-expires-at="{{ $expiryTs }}" data-booking-expired="{{ $isExpired ? 1 : 0 }}">

    {{-- ── Top bar ──────────────────────────────────────────────────────────── --}}
    <div class="bco-topbar">
        <div class="bco-topbar-brand bco-topbar-brand--bla">
            <img src="{{ secure_asset('images/search-bla.png') }}" alt="BlaBlaBus" class="bco-topbar-logo">
            <span>{{ __('bus.checkout.bla_badge') }}</span>
        </div>
        <div class="bco-topbar-title">
            <h2>{{ __('bus.checkout.title') }}</h2>
            <p>{{ __('bus.checkout.bla_intro') }}</p>
        </div>
        <div class="bco-timer-wrap">
            <div class="bco-timer">
                <span class="bco-timer-label">{{ __('bus.checkout.timer_label') }}</span>
                <strong class="bco-timer-value" data-booking-countdown>{{ __('bus.checkout.timer_loading') }}</strong>
            </div>
        </div>
    </div>

    {{-- ── Journey strip ────────────────────────────────────────────────────── --}}
    <div class="bco-journey">
        @if ($isRoundTrip)
            <div class="bco-journey-leg">
                <span class="bco-journey-label bco-journey-label--out">{{ __('bus.checkout.departure') }}</span>
                <div class="bco-journey-route">
                    <strong>{{ $outboundSegs[0]['departure_station']['name'] ?? '—' }}</strong>
                    <i class="fas fa-arrow-right bco-journey-arrow"></i>
                    <strong>{{ $outboundSegs[count($outboundSegs)-1]['arrival_station']['name'] ?? '—' }}</strong>
                </div>
                @php $od = $outboundSegs[0]['booking_journey_segments'][0]['departure_date_time'] ?? null; @endphp
                @if ($od)<small class="bco-journey-time">{{ date('D d M · H:i', strtotime($od)) }}</small>@endif
            </div>
            <div class="bco-journey-sep"><i class="fas fa-exchange-alt"></i></div>
            <div class="bco-journey-leg">
                <span class="bco-journey-label bco-journey-label--ret">{{ __('bus.checkout.return') }}</span>
                <div class="bco-journey-route">
                    <strong>{{ $inboundSegs[0]['departure_station']['name'] ?? '—' }}</strong>
                    <i class="fas fa-arrow-right bco-journey-arrow"></i>
                    <strong>{{ $inboundSegs[count($inboundSegs)-1]['arrival_station']['name'] ?? '—' }}</strong>
                </div>
                @php $id = $inboundSegs[0]['booking_journey_segments'][0]['departure_date_time'] ?? null; @endphp
                @if ($id)<small class="bco-journey-time">{{ date('D d M · H:i', strtotime($id)) }}</small>@endif
            </div>
        @else
            <div class="bco-journey-leg">
                <span class="bco-journey-label bco-journey-label--out">{{ __('bus.checkout.departure') }}</span>
                <div class="bco-journey-route">
                    <strong>{{ $firstSeg['departure_station']['name'] }}</strong>
                    <i class="fas fa-arrow-right bco-journey-arrow"></i>
                    <strong>{{ $lastSeg['arrival_station']['name'] }}</strong>
                </div>
                @php $dt = $firstSeg['booking_journey_segments'][0]['departure_date_time'] ?? null; @endphp
                @if ($dt)<small class="bco-journey-time">{{ date('D d M · H:i', strtotime($dt)) }}</small>@endif
            </div>
        @endif
        <div class="bco-journey-total">
            <span>Total</span>
            <strong>€ {{ number_format((float) $blaPrice, 2) }}</strong>
        </div>
    </div>

    <form action="{{ route('bus.v2.confirm.bla') }}" method="POST" class="js-bus-v2-ajax-form bco-form">
        @csrf
        <input type="hidden" name="booking_number"          value="{{ $blaReservation['booking_number'] }}">
        <input type="hidden" name="booking_id"              value="{{ $blaReservation['booking_id'] }}">
        <input type="hidden" name="sales_channel_code"      value="{{ $blaReservation['sales_channel_code'] }}">
        <input type="hidden" name="departure_time"          value="{{ $firstSeg['booking_journey_segments'][0]['departure_date_time'] }}">
        <input type="hidden" name="arrival_time"            value="{{ $lastSeg['booking_journey_segments'][0]['arrival_date_time'] }}">
        <input type="hidden" name="from_name"               value="{{ $firstSeg['departure_station']['name'] }}">
        <input type="hidden" name="to_name"                 value="{{ $lastSeg['arrival_station']['name'] }}">
        <input type="hidden" name="price"                   value="{{ $providerPrice }}">
        <input type="hidden" name="total_price"             value="{{ $checkoutPrice }}">
        <input type="hidden" name="currency"                value="{{ $blaReservation['currency'] }}">
        <input type="hidden" name="total_vat"               value="{{ $blaReservation['total_vat'] }}">
        <input type="hidden" name="total_price_paid"        value="{{ $blaReservation['total_price_paid'] }}">
        <input type="hidden" name="total_price_to_be_paid"  value="{{ $blaReservation['total_price_to_be_paid'] }}">

        @foreach ($bookingPassengers as $p)
            <input type="hidden" name="passenger_id[]"              value="{{ $p['id'] }}">
            <input type="hidden" name="passenger_type[]"            value="{{ $p['type'] }}">
            <input type="hidden" name="passenger_disability_type[]" value="{{ $p['disability_type'] }}">
            <input type="hidden" name="passenger_ref_id[]"          value="{{ $p['ref_id'] }}">
            <input type="hidden" name="passenger_uuid[]"            value="{{ $p['uuid'] }}">
        @endforeach

        @foreach ($blaSegments as $seg)
            <input type="hidden" name="segment_id[]"                value="{{ $seg['id'] }}">
            <input type="hidden" name="segment_departure_station[]" value="{{ $seg['departure_station']['name'] }}">
            <input type="hidden" name="segment_arrival_station[]"   value="{{ $seg['arrival_station']['name'] }}">
            <input type="hidden" name="segment_service_name[]"      value="{{ $seg['validity_service'] }}">
            <input type="hidden" name="segment_departure_time[]"    value="{{ $seg['booking_journey_segments'][0]['departure_date_time'] }}">
            <input type="hidden" name="segment_arrival_time[]"      value="{{ $seg['booking_journey_segments'][0]['arrival_date_time'] }}">
        @endforeach

        {{-- Desktop: sidebar LEFT, forms RIGHT --}}
        <div class="bco-layout bco-layout--desk">

            {{-- Sidebar --}}
            <aside class="bco-sidebar">
                <div class="bco-summary bco-summary--bla">
                    <div class="bco-summary-head">
                        <div class="bco-summary-badge-wrap">
                            <span class="bco-summary-brand-badge bco-summary-brand-badge--bla">{{ __('bus.checkout.bla_badge') }}</span>
                        </div>
                        <div class="bco-summary-timer-inline">
                            <span>{{ __('bus.checkout.timer_label') }}</span>
                            <strong data-booking-countdown>{{ __('bus.checkout.timer_loading') }}</strong>
                        </div>
                    </div>
                    <div class="bco-summary-route">
                        <div class="bco-summary-city">{{ $firstSeg['departure_station']['name'] }}</div>
                        <div class="bco-summary-route-icon bco-summary-route-icon--bla"><i class="fas fa-arrow-right"></i></div>
                        <div class="bco-summary-city">{{ $lastSeg['arrival_station']['name'] }}</div>
                    </div>
                    <ul class="bco-summary-list">
                        @foreach ($blaSegments as $seg)
                            <li>
                                <i class="fas fa-bus"></i>
                                <span>{{ $seg['departure_station']['name'] }} → {{ $seg['arrival_station']['name'] }}</span>
                                <small>{{ date('H:i', strtotime($seg['booking_journey_segments'][0]['departure_date_time'])) }}</small>
                            </li>
                        @endforeach
                        <li><i class="fas fa-route"></i><span>{{ __('bus.checkout.segments_count', ['count' => count($blaSegments)]) }}</span></li>
                        <li><i class="fas fa-users"></i><span>{{ __('bus.checkout.passengers_count', ['count' => $passengerCount]) }}</span></li>
                        <li><i class="fas fa-sim-card"></i><span>{{ __('bus.checkout.phone_locked_fr') }}</span></li>
                    </ul>
                    @include('v2.service.bus.partials.checkout-journey-details-toggle', ['journeyDetailsList' => $journeyDetailsList])
                    <div class="bco-trust-row">
                        <span><i class="fas fa-shield-alt"></i> {{ __('bus.checkout.secure_checkout') }}</span>
                        <span><i class="fas fa-lock"></i> {{ __('bus.checkout.encrypted_booking') }}</span>
                    </div>
                    <div class="bco-total bco-total--bla">
                        <div>
                            <span class="bco-total-label">{{ __('bus.checkout.total_to_charge') }}</span>
                            <strong class="bco-total-amount">€ {{ number_format((float) $blaPrice, 2) }}</strong>
                        </div>
                        <span class="bco-total-shield bco-total-shield--bla"><i class="fas fa-shield-alt"></i></span>
                    </div>
                    @if ($passengerCount > 1)
                        <p class="bco-summary-note" data-passenger-step-summary-note
                            data-note-pending="{{ __('bus.checkout.summary_note_pending') }}"
                            data-note-ready="{{ __('bus.checkout.summary_note_ready') }}">
                            {{ __('bus.checkout.summary_note_pending') }}
                        </p>
                    @endif
                    <button type="submit"
                        class="bco-submit bco-submit--bla bus-v2-submit"
                        data-passenger-submit
                        data-loading-text="{{ __('bus.checkout.issuing_ticket') }}"
                        @if ($passengerCount > 1) disabled @endif>
                        <i class="fas fa-ticket-alt"></i>
                        <span>{{ __('bus.checkout.issue_ticket') }}</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </aside>

            {{-- Passenger forms --}}
            <div class="bco-forms" @if ($passengerCount > 1) data-passenger-step-list data-step-count="{{ $passengerCount }}" data-active-step="0" @endif>

                @if ($passengerCount > 1)
                    <div class="bco-stepper">
                        <div class="bco-stepper-head">
                            <div>
                                <span class="bco-stepper-eyebrow">{{ __('bus.checkout.step_title') }}</span>
                                <strong class="bco-stepper-label" data-passenger-step-label
                                    data-template="{{ __('bus.checkout.step_label', ['current' => '__CURRENT__', 'total' => '__TOTAL__']) }}">
                                    {{ __('bus.checkout.step_label', ['current' => 1, 'total' => $passengerCount]) }}
                                </strong>
                            </div>
                            <div class="bco-stepper-dots" role="tablist">
                                @foreach ($blaPassengers as $dk => $dp)
                                    @php $dAdult = ($dp['type'] ?? 'A') === 'A'; @endphp
                                    <button type="button" class="bco-step-dot{{ $dk === 0 ? ' is-active' : '' }}"
                                        data-passenger-step-target="{{ $dk }}"
                                        title="{{ __('bus.checkout.passenger_number', ['number' => $dk + 1]) }}">
                                        <span>{{ $dk + 1 }}</span>
                                        <small>{{ $dAdult ? __('bus.checkout.adult') : __('bus.checkout.child') }}</small>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @foreach ($blaPassengers as $key => $passenger)
                    @php
                        $isAdult    = ($passenger['type'] ?? 'A') === 'A';
                        $issuingFld = 'deskBlaIssuing' . $key;
                        $selCit     = old('citizenship.' . $key);
                        $selIssuing = old('identification_issuing_country.' . $key);
                        $bdVal      = old('birthdate.' . $key);
                        $bdObj      = null;
                        if ($bdVal) {
                            $bdObj = \DateTime::createFromFormat('Y-m-d', $bdVal) ?: \DateTime::createFromFormat('d.m.Y', $bdVal);
                        }
                        $bdVal      = $bdObj ? $bdObj->format('d.m.Y') : '';
                        $expVal     = old('identification_expiry_date.' . $key);
                        $expObj     = null;
                        if ($expVal) {
                            $expObj = \DateTime::createFromFormat('Y-m-d', $expVal) ?: \DateTime::createFromFormat('d.m.Y', $expVal);
                        }
                        $expVal     = $expObj ? $expObj->format('d.m.Y') : '';
                        if (!$selIssuing && $selCit) {
                            $selIssuing = optional($countries->firstWhere('iso', $selCit))->iso3;
                        }
                        $phoneHid   = 'deskBlaPhone' . $key;
                        $phoneVal   = old('phone_number.' . $key, '');
                        $pfx        = 'deskBlaPax' . $key;
                    @endphp

                    <article class="bco-pax-card{{ $passengerCount > 1 && $key === 0 ? ' is-active' : '' }}"
                        @if ($passengerCount > 1) data-passenger-step-card data-passenger-step-index="{{ $key }}" @endif>

                        <div class="bco-pax-head">
                            <div class="bco-pax-num">
                                <span class="bco-pax-badge bco-pax-badge--bla">{{ $key + 1 }}</span>
                                <div>
                                    <h3>{{ __('bus.checkout.passenger_number', ['number' => $key + 1]) }}</h3>
                                    <p>{{ __('bus.checkout.bla_passenger_note') }}</p>
                                </div>
                            </div>
                            <span class="bco-type-chip bco-type-chip--{{ $isAdult ? 'adult' : 'child' }}">
                                <i class="fas fa-{{ $isAdult ? 'user' : 'child' }}"></i>
                                {{ $isAdult ? __('bus.checkout.adult') : __('bus.checkout.child') }}
                            </span>
                        </div>

                        @include('v2.service.bus.designs.desk.partials.passenger-form-fields', [
                            'prefix'                 => 'deskBla',
                            'key'                    => $key,
                            'passenger'              => $passenger,
                            'isAdult'                => $isAdult,
                            'countries'              => $countries,
                            'issuingField'           => $issuingFld,
                            'selectedIssuingCountry' => $selIssuing,
                            'birthdateValue'         => $bdVal,
                            'expiryValue'            => $expVal,
                            'phoneHiddenField'       => $phoneHid,
                            'phoneValue'             => $phoneVal,
                        ])
                    </article>
                @endforeach

                @if ($passengerCount > 1)
                    <div class="bco-step-actions">
                        <button type="button" class="bco-btn bco-btn--ghost" data-passenger-step-prev disabled>
                            <i class="fas fa-arrow-left"></i> {{ __('bus.checkout.previous_step') }}
                        </button>
                        <p class="bco-step-note" data-passenger-step-note
                            data-note-pending="{{ __('bus.checkout.step_note_pending') }}"
                            data-note-ready="{{ __('bus.checkout.step_note_ready') }}">
                            {{ __('bus.checkout.step_note_pending') }}
                        </p>
                        <button type="button" class="bco-btn bco-btn--next" data-passenger-step-next>
                            {{ __('bus.checkout.next_step') }} <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </form>
</section>
@endif
