@php
    $reservation    = is_array($flixBooking)
        ? (!empty($flixBooking[0]) ? $flixBooking[0] : $flixBooking)
        : null;
    $bookingPassengerDetails = $reservation['passenger_details'] ?? [];
    $passengerDetails = $reservation['ui_passenger_details'] ?? $bookingPassengerDetails;
    $tripSummaries  = $reservation['trips'] ?? [];
    $checkoutPrice = isset($flixPrice) && $flixPrice !== '' ? $flixPrice : ($reservation['price'] ?? ($reservation['total_price'] ?? ''));
    $providerTotalPrice = $reservation['price'] ?? ($reservation['total_price'] ?? $checkoutPrice);
    if ($providerTotalPrice === '') {
        $providerTotalPrice = $checkoutPrice;
    }
    if (empty($tripSummaries) && $reservation) {
        $tripSummaries = [[
            'from_name'      => $reservation['from_name'] ?? null,
            'to_name'        => $reservation['to_name'] ?? null,
            'departure_time' => $reservation['departure_time'] ?? null,
        ]];
    }
    $primaryTrip    = $tripSummaries[0] ?? [];
    $lastTrip       = !empty($tripSummaries) ? $tripSummaries[count($tripSummaries) - 1] : $primaryTrip;
    $passengerCount = count($passengerDetails);
    $expiryTs       = (int) ($bookingExpiresAt ?? 0);
    $isExpired      = !empty($bookingExpired);
    $storedJourneyDetails = $reservation['journey_details'] ?? [];
    $journeyDetailsList = isset($storedJourneyDetails[0]) && is_array($storedJourneyDetails[0])
        ? $storedJourneyDetails
        : (!empty($storedJourneyDetails) ? [$storedJourneyDetails] : []);
@endphp

@if ($reservation)
<section class="bco bco--flix bco--desk" data-booking-expires-at="{{ $expiryTs }}" data-booking-expired="{{ $isExpired ? 1 : 0 }}">

    {{-- ── Top bar ──────────────────────────────────────────────────────────── --}}
    <div class="bco-topbar">
        <div class="bco-topbar-brand bco-topbar-brand--flix">
            <img src="{{ secure_asset('images/search-flix.png') }}" alt="FlixBus" class="bco-topbar-logo">
            <span>{{ __('bus.checkout.flix_badge') }}</span>
        </div>
        <div class="bco-topbar-title">
            <h2>{{ __('bus.checkout.title') }}</h2>
            <p>{{ __('bus.checkout.flix_intro') }}</p>
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
        @foreach ($tripSummaries as $idx => $leg)
            @if ($idx > 0)
                <div class="bco-journey-sep"><i class="fas fa-exchange-alt"></i></div>
            @endif
            <div class="bco-journey-leg">
                <span class="bco-journey-label {{ $idx === 0 ? 'bco-journey-label--out' : 'bco-journey-label--ret' }}">
                    {{ $idx === 0 ? __('bus.checkout.departure') : __('bus.checkout.return') }}
                </span>
                <div class="bco-journey-route">
                    <strong>{{ $leg['from_name'] ?? '—' }}</strong>
                    <i class="fas fa-arrow-right bco-journey-arrow"></i>
                    <strong>{{ $leg['to_name'] ?? '—' }}</strong>
                </div>
                @if (!empty($leg['departure_time']))
                    <small class="bco-journey-time">{{ date('D d M · H:i', strtotime($leg['departure_time'])) }}</small>
                @endif
            </div>
        @endforeach
        <div class="bco-journey-total">
            <span>Total</span>
            <strong>€ {{ number_format((float) $flixPrice, 2) }}</strong>
        </div>
    </div>

    <form action="{{ route('bus.v2.confirm') }}" method="POST" class="js-bus-v2-ajax-form bco-form">
        @csrf
        <input type="hidden" name="reservation_token" value="{{ $reservation['reservation_token'] ?? '' }}">
        <input type="hidden" name="reservation_id"    value="{{ $reservation['reservation_id'] ?? '' }}">
        <input type="hidden" name="departure_time"    value="{{ $primaryTrip['departure_time'] ?? ($reservation['departure_time'] ?? '') }}">
        <input type="hidden" name="from_name"         value="{{ $primaryTrip['from_name'] ?? ($reservation['from_name'] ?? '') }}">
        <input type="hidden" name="to_name"           value="{{ $lastTrip['to_name'] ?? ($reservation['to_name'] ?? '') }}">
        <input type="hidden" name="price"             value="{{ $checkoutPrice }}">
        <input type="hidden" name="total_price"       value="{{ $providerTotalPrice }}">
        @foreach (($reservation['ticket_ids'] ?? []) as $ticketId)
            <input type="hidden" name="ticket_ids[]" value="{{ $ticketId }}">
        @endforeach

        {{-- Desktop: sidebar LEFT, forms RIGHT --}}
        <div class="bco-layout bco-layout--desk">

            {{-- Sidebar --}}
            <aside class="bco-sidebar">
                <div class="bco-summary bco-summary--flix">
                    <div class="bco-summary-head">
                        <span class="bco-summary-brand-badge bco-summary-brand-badge--flix">{{ __('bus.checkout.flix_badge') }}</span>
                        <div class="bco-summary-timer-inline">
                            <span>{{ __('bus.checkout.timer_label') }}</span>
                            <strong data-booking-countdown>{{ __('bus.checkout.timer_loading') }}</strong>
                        </div>
                    </div>
                    <div class="bco-summary-route">
                        <div class="bco-summary-city">{{ $primaryTrip['from_name'] ?? ($reservation['from_name'] ?? '') }}</div>
                        <div class="bco-summary-route-icon bco-summary-route-icon--flix"><i class="fas fa-arrow-right"></i></div>
                        <div class="bco-summary-city">{{ $lastTrip['to_name'] ?? ($reservation['to_name'] ?? '') }}</div>
                    </div>
                    <ul class="bco-summary-list">
                        @foreach ($tripSummaries as $t)
                            <li>
                                <i class="fas fa-bus"></i>
                                <span>{{ ($t['from_name'] ?? '') }} → {{ ($t['to_name'] ?? '') }}</span>
                                @if (!empty($t['departure_time']))<small>{{ date('H:i', strtotime($t['departure_time'])) }}</small>@endif
                            </li>
                        @endforeach
                        <li><i class="fas fa-users"></i><span>{{ __('bus.checkout.passengers_count', ['count' => $passengerCount]) }}</span></li>
                        <li><i class="fas fa-sim-card"></i><span>{{ __('bus.checkout.phone_locked_fr') }}</span></li>
                        <li><i class="fas fa-envelope"></i><span>{{ __('bus.checkout.email_required') }}</span></li>
                    </ul>
                    @include('v2.service.bus.partials.checkout-journey-details-toggle', ['journeyDetailsList' => $journeyDetailsList])
                    <div class="bco-trust-row">
                        <span><i class="fas fa-shield-alt"></i> {{ __('bus.checkout.secure_checkout') }}</span>
                        <span><i class="fas fa-lock"></i> {{ __('bus.checkout.encrypted_booking') }}</span>
                    </div>
                    <div class="bco-total bco-total--flix">
                        <div>
                            <span class="bco-total-label">{{ __('bus.checkout.total_to_charge') }}</span>
                            <strong class="bco-total-amount">€ {{ number_format((float) $flixPrice, 2) }}</strong>
                        </div>
                        <span class="bco-total-shield"><i class="fas fa-shield-alt"></i></span>
                    </div>
                    @if ($passengerCount > 1)
                        <p class="bco-summary-note" data-passenger-step-summary-note
                            data-note-pending="{{ __('bus.checkout.summary_note_pending') }}"
                            data-note-ready="{{ __('bus.checkout.summary_note_ready') }}">
                            {{ __('bus.checkout.summary_note_pending') }}
                        </p>
                    @endif
                    <button type="submit"
                        class="bco-submit bco-submit--flix bus-v2-submit"
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
                                @foreach ($passengerDetails as $dk => $dp)
                                    @php $dAdult = strtolower($dp['type'] ?? '') === 'adult'; @endphp
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

                @foreach ($passengerDetails as $key => $passenger)
                    @php
                        $isAdult    = strtolower($passenger['type'] ?? '') === 'adult';
                        $issuingFld = 'deskFlixIssuing' . $key;
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
                        $phoneHid   = 'deskFlixPhone' . $key;
                        $phoneVal   = old('phone_number.' . $key, '');
                        $pfx        = 'deskFlixPax' . $key;
                    @endphp

                    <article class="bco-pax-card{{ $passengerCount > 1 && $key === 0 ? ' is-active' : '' }}"
                        @if ($passengerCount > 1) data-passenger-step-card data-passenger-step-index="{{ $key }}" @endif>

                        <div class="bco-pax-head">
                            <div class="bco-pax-num">
                                <span class="bco-pax-badge bco-pax-badge--flix">{{ $key + 1 }}</span>
                                <div>
                                    <h3>{{ __('bus.checkout.passenger_number', ['number' => $key + 1]) }}</h3>
                                    <p>{{ __('bus.checkout.passenger_note') }}</p>
                                </div>
                            </div>
                            <span class="bco-type-chip bco-type-chip--{{ $isAdult ? 'adult' : 'child' }}">
                                <i class="fas fa-{{ $isAdult ? 'user' : 'child' }}"></i>
                                {{ $isAdult ? __('bus.checkout.adult') : __('bus.checkout.child') }}
                            </span>
                        </div>

                        @include('v2.service.bus.designs.desk.partials.passenger-form-fields', [
                            'prefix'               => 'deskFlix',
                            'key'                  => $key,
                            'passenger'            => $passenger,
                            'isAdult'              => $isAdult,
                            'countries'            => $countries,
                            'issuingField'         => $issuingFld,
                            'selectedIssuingCountry' => $selIssuing,
                            'birthdateValue'       => $bdVal,
                            'expiryValue'          => $expVal,
                            'phoneHiddenField'     => $phoneHid,
                            'phoneValue'           => $phoneVal,
                        ])

                        <input type="hidden" name="reference_id[]"   value="{{ $passenger['reference_id'] }}">
                        <input type="hidden" name="product_type[]"   value="{{ $passenger['product_type'] }}">
                        <input type="hidden" name="identification_type[]" value="international_passport">
                        <input type="hidden" id="{{ $issuingFld }}"  name="identification_issuing_country[]" value="{{ $selIssuing }}">
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
