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
    $firstOutboundSeg = $outboundSegs[0] ?? null;
    $lastOutboundSeg  = !empty($outboundSegs) ? $outboundSegs[count($outboundSegs) - 1] : $firstOutboundSeg;
    $firstInboundSeg  = $inboundSegs[0] ?? null;
    $lastInboundSeg   = !empty($inboundSegs) ? $inboundSegs[count($inboundSegs) - 1] : $firstInboundSeg;
    $segmentDepartureTime = function ($segment) {
        return $segment['booking_journey_segments'][0]['departure_date_time'] ?? '';
    };
    $segmentArrivalTime = function ($segment) {
        return $segment['booking_journey_segments'][0]['arrival_date_time'] ?? '';
    };
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
<section class="bco bco--normal bco--bla" data-booking-expires-at="{{ $expiryTs }}" data-booking-expired="{{ $isExpired ? 1 : 0 }}">

    {{-- ── Top bar ──────────────────────────────────────────────────────────── --}}
    <div class="bco-topbar">
        <div class="bco-topbar-brand bco-topbar-brand--bla">
            <img src="{{ secure_asset('images/search-bla.png') }}" alt="BlaBlaBus" class="bco-topbar-logo">
            <span>{{ __('bus.checkout.bla_badge') }}</span>
        </div>
        <div class="bco-topbar-title">
            <h2>{{ __('bus.checkout.title') }}</h2>
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
                    <strong>{{ $firstOutboundSeg['departure_station']['name'] ?? '—' }}</strong>
                    <i class="fas fa-arrow-right bco-journey-arrow"></i>
                    <strong>{{ $lastOutboundSeg['arrival_station']['name'] ?? '—' }}</strong>
                </div>
                @php $od = $segmentDepartureTime($firstOutboundSeg); @endphp
                @if ($od)<small class="bco-journey-time">{{ date('D d M · H:i', strtotime($od)) }}</small>@endif
            </div>
            <div class="bco-journey-sep"><i class="fas fa-exchange-alt"></i></div>
            <div class="bco-journey-leg">
                <span class="bco-journey-label bco-journey-label--ret">{{ __('bus.checkout.return') }}</span>
                <div class="bco-journey-route">
                    <strong>{{ $firstInboundSeg['departure_station']['name'] ?? '—' }}</strong>
                    <i class="fas fa-arrow-right bco-journey-arrow"></i>
                    <strong>{{ $lastInboundSeg['arrival_station']['name'] ?? '—' }}</strong>
                </div>
                @php $id = $segmentDepartureTime($firstInboundSeg); @endphp
                @if ($id)<small class="bco-journey-time">{{ date('D d M · H:i', strtotime($id)) }}</small>@endif
            </div>
        @else
            <div class="bco-journey-leg">
                <span class="bco-journey-label bco-journey-label--out">{{ __('bus.checkout.departure') }}</span>
                <div class="bco-journey-route">
                    <strong>{{ $firstSeg['departure_station']['name'] ?? '—' }}</strong>
                    <i class="fas fa-arrow-right bco-journey-arrow"></i>
                    <strong>{{ $lastSeg['arrival_station']['name'] ?? '—' }}</strong>
                </div>
                @php $dt = $segmentDepartureTime($firstSeg); @endphp
                @if ($dt)<small class="bco-journey-time">{{ date('D d M · H:i', strtotime($dt)) }}</small>@endif
            </div>
        @endif
        <div class="bco-journey-total">
            <span>Total</span>
            <strong>€{{ number_format((float) $blaPrice, 2) }}</strong>
        </div>
    </div>

    <form action="{{ route('bus.v2.confirm.bla') }}" method="POST" class="js-bus-v2-ajax-form bco-form">
        @csrf
        {{-- ── Hidden booking fields ────────────────────────────────────────── --}}
        <input type="hidden" name="booking_number"          value="{{ $blaReservation['booking_number'] ?? '' }}">
        <input type="hidden" name="booking_id"              value="{{ $blaReservation['booking_id'] ?? '' }}">
        <input type="hidden" name="sales_channel_code"      value="{{ $blaReservation['sales_channel_code'] ?? '' }}">
        <input type="hidden" name="departure_time"          value="{{ $segmentDepartureTime($firstSeg) }}">
        <input type="hidden" name="arrival_time"            value="{{ $segmentArrivalTime($lastSeg) }}">
        <input type="hidden" name="from_name"               value="{{ $firstSeg['departure_station']['name'] ?? '' }}">
        <input type="hidden" name="to_name"                 value="{{ $lastSeg['arrival_station']['name'] ?? '' }}">
        <input type="hidden" name="price"                   value="{{ $providerPrice }}">
        <input type="hidden" name="total_price"             value="{{ $checkoutPrice }}">
        <input type="hidden" name="currency"                value="{{ $blaReservation['currency'] ?? '' }}">
        <input type="hidden" name="total_vat"               value="{{ $blaReservation['total_vat'] ?? '' }}">
        <input type="hidden" name="total_price_paid"        value="{{ $blaReservation['total_price_paid'] ?? '' }}">
        <input type="hidden" name="total_price_to_be_paid"  value="{{ $blaReservation['total_price_to_be_paid'] ?? '' }}">

        @foreach ($bookingPassengers as $p)
            <input type="hidden" name="passenger_id[]"              value="{{ $p['id'] ?? '' }}">
            <input type="hidden" name="passenger_type[]"            value="{{ $p['type'] ?? '' }}">
            <input type="hidden" name="passenger_disability_type[]" value="{{ $p['disability_type'] ?? '' }}">
            <input type="hidden" name="passenger_ref_id[]"          value="{{ $p['ref_id'] ?? '' }}">
            <input type="hidden" name="passenger_uuid[]"            value="{{ $p['uuid'] ?? '' }}">
        @endforeach

        @foreach ($blaSegments as $seg)
            <input type="hidden" name="segment_id[]"               value="{{ $seg['id'] ?? '' }}">
            <input type="hidden" name="segment_departure_station[]" value="{{ $seg['departure_station']['name'] ?? '' }}">
            <input type="hidden" name="segment_arrival_station[]"   value="{{ $seg['arrival_station']['name'] ?? '' }}">
            <input type="hidden" name="segment_service_name[]"      value="{{ $seg['validity_service'] ?? '' }}">
            <input type="hidden" name="segment_departure_time[]"    value="{{ $segmentDepartureTime($seg) }}">
            <input type="hidden" name="segment_arrival_time[]"      value="{{ $segmentArrivalTime($seg) }}">
        @endforeach

        <div class="bco-layout">

            {{-- ── Left: passenger forms ───────────────────────────────────── --}}
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
                        $isAdult     = ($passenger['type'] ?? 'A') === 'A';
                        $issuingFld  = 'blaIssuing' . $key;
                        $selCit      = old('citizenship.' . $key);
                        $selIssuing  = old('identification_issuing_country.' . $key);
                        $bdVal       = old('birthdate.' . $key);
                        $bdObj       = null;
                        if ($bdVal) {
                            $bdObj = \DateTime::createFromFormat('Y-m-d', $bdVal) ?: \DateTime::createFromFormat('d.m.Y', $bdVal);
                        }
                        $bdVal       = $bdObj ? $bdObj->format('d.m.Y') : '';
                        $expVal      = old('identification_expiry_date.' . $key);
                        $expObj      = null;
                        if ($expVal) {
                            $expObj = \DateTime::createFromFormat('Y-m-d', $expVal) ?: \DateTime::createFromFormat('d.m.Y', $expVal);
                        }
                        $expVal      = $expObj ? $expObj->format('d.m.Y') : '';
                        if (!$selIssuing && $selCit) {
                            $selIssuing = optional($countries->firstWhere('iso', $selCit))->iso3;
                        }
                        $phoneHid    = 'blaPhone' . $key;
                        $phoneVal    = old('phone_number.' . $key, '');
                        $pfx         = 'blaPax' . $key;
                    @endphp

                    <article class="bco-pax-card{{ $passengerCount > 1 && $key === 0 ? ' is-active' : '' }}"
                        @if ($passengerCount > 1) data-passenger-step-card data-passenger-step-index="{{ $key }}" @endif>

                        {{-- card header --}}
                        <div class="bco-pax-head">
                            <div class="bco-pax-num">
                                <span class="bco-pax-badge bco-pax-badge--bla">{{ $key + 1 }}</span>
                                <div>
                                    <h3>{{ __('bus.checkout.passenger_number', ['number' => $key + 1]) }}</h3>
                                </div>
                            </div>
                            <span class="bco-type-chip bco-type-chip--{{ $isAdult ? 'adult' : 'child' }}">
                                <i class="fas fa-{{ $isAdult ? 'user' : 'child' }}"></i>
                                {{ $isAdult ? __('bus.checkout.adult') : __('bus.checkout.child') }}
                            </span>
                        </div>

                        {{-- Personal details --}}
                        <div class="bco-section">
                            <div class="bco-section-head">
                                <span class="bco-section-icon bco-section-icon--bla"><i class="fas fa-user"></i></span>
                                <h4>{{ __('bus.checkout.passenger_details') }}</h4>
                            </div>
                            <div class="bco-grid">
                                <div class="bco-field">
                                    <label class="bco-label" for="{{ $pfx }}Fn">{{ __('bus.checkout.first_name') }}</label>
                                    <input type="text" id="{{ $pfx }}Fn" class="bco-input" name="firstname[]"
                                        value="{{ old('firstname.' . $key, $passenger['first_name'] ?? '') }}"
                                        data-error-key="firstname.{{ $key }}" autocomplete="given-name"
                                        placeholder="{{ __('bus.checkout.first_name') }}"
                                        aria-describedby="{{ $pfx }}FnErr" aria-invalid="false">
                                    <span id="{{ $pfx }}FnErr" class="bco-field-err bus-v2-field-error" data-error-for="firstname.{{ $key }}" aria-live="polite"></span>
                                </div>
                                <div class="bco-field">
                                    <label class="bco-label" for="{{ $pfx }}Ln">{{ __('bus.checkout.last_name') }}</label>
                                    <input type="text" id="{{ $pfx }}Ln" class="bco-input" name="lastname[]"
                                        value="{{ old('lastname.' . $key, $passenger['last_name'] ?? '') }}"
                                        data-error-key="lastname.{{ $key }}" autocomplete="family-name"
                                        placeholder="{{ __('bus.checkout.last_name') }}"
                                        aria-describedby="{{ $pfx }}LnErr" aria-invalid="false">
                                    <span id="{{ $pfx }}LnErr" class="bco-field-err bus-v2-field-error" data-error-for="lastname.{{ $key }}" aria-live="polite"></span>
                                </div>
                                <div class="bco-field">
                                    <label class="bco-label" for="{{ $pfx }}Bd">{{ __('bus.checkout.date_of_birth') }}</label>
                                    <div class="bco-input-icon-wrap">
                                        <i class="fas fa-calendar-alt bco-input-icon"></i>
                                        <input type="text" id="{{ $pfx }}Bd" class="bco-input bco-input--icon bus-v2-birthdate js-bus-v2-datepicker"
                                            data-passenger-kind="{{ $isAdult ? 'adult' : 'child' }}" data-datepicker-kind="birthdate"
                                            name="birthdate[]" value="{{ $bdVal }}" autocomplete="bday" readonly
                                            data-error-key="birthdate.{{ $key }}" placeholder="DD.MM.YYYY"
                                            aria-describedby="{{ $pfx }}BdErr" aria-invalid="false">
                                    </div>
                                    <span id="{{ $pfx }}BdErr" class="bco-field-err bus-v2-field-error" data-error-for="birthdate.{{ $key }}" aria-live="polite"></span>
                                </div>
                                <div class="bco-field">
                                    <label class="bco-label" for="{{ $pfx }}Ge">{{ __('bus.checkout.gender') }}</label>
                                    <div class="bco-select-wrap">
                                        <select id="{{ $pfx }}Ge" class="bco-select" name="gender[]"
                                            data-error-key="gender.{{ $key }}"
                                            aria-describedby="{{ $pfx }}GeErr" aria-invalid="false">
                                            <option value="">{{ __('bus.checkout.select_option') }}</option>
                                            <option value="male"   {{ old('gender.' . $key) === 'male'   ? 'selected' : '' }}>{{ __('bus.gender.male') }}</option>
                                            <option value="female" {{ old('gender.' . $key) === 'female' ? 'selected' : '' }}>{{ __('bus.gender.female') }}</option>
                                        </select>
                                        <i class="fas fa-chevron-down bco-select-caret"></i>
                                    </div>
                                    <span id="{{ $pfx }}GeErr" class="bco-field-err bus-v2-field-error" data-error-for="gender.{{ $key }}" aria-live="polite"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Contact details --}}
                        <div class="bco-section">
                            <div class="bco-section-head">
                                <span class="bco-section-icon bco-section-icon--bla"><i class="fas fa-envelope"></i></span>
                                <h4>{{ __('bus.checkout.contact_details') }}</h4>
                            </div>
                            <div class="bco-grid">
                                <div class="bco-field">
                                    <label class="bco-label" for="{{ $pfx }}Em">{{ __('bus.checkout.email') }}</label>
                                    <div class="bco-input-icon-wrap">
                                        <i class="fas fa-envelope bco-input-icon"></i>
                                        <input type="email" id="{{ $pfx }}Em" class="bco-input bco-input--icon" name="email[]"
                                            value="{{ old('email.' . $key, $passenger['email'] ?? '') }}"
                                            data-error-key="email.{{ $key }}" autocomplete="email"
                                            placeholder="name@example.com"
                                            aria-describedby="{{ $pfx }}EmErr" aria-invalid="false">
                                    </div>
                                    <span id="{{ $pfx }}EmErr" class="bco-field-err bus-v2-field-error" data-error-for="email.{{ $key }}" aria-live="polite"></span>
                                </div>
                                <div class="bco-field bco-field--phone">
                                    <label class="bco-label" for="{{ $pfx }}Ph">{{ __('bus.checkout.phone_number') }}</label>
                                    <input type="tel" id="{{ $pfx }}Ph" class="bco-input bus-v2-phone-input"
                                        value="" inputmode="tel" autocomplete="tel-national" placeholder="06 12 34 56 78"
                                        data-phone-visible data-phone-hidden="#{{ $phoneHid }}"
                                        data-error-key="phone_number.{{ $key }}"
                                        aria-describedby="{{ $pfx }}PhErr" aria-invalid="false">
                                    <input type="hidden" id="{{ $phoneHid }}" name="phone_number[]" value="{{ $phoneVal }}" data-phone-hidden>
                                    <span id="{{ $pfx }}PhErr" class="bco-field-err bus-v2-field-error" data-error-for="phone_number.{{ $key }}" aria-live="polite"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Travel document --}}
                        <div class="bco-section">
                            <div class="bco-section-head">
                                <span class="bco-section-icon bco-section-icon--bla"><i class="fas fa-passport"></i></span>
                                <h4>{{ __('bus.checkout.travel_document') }}</h4>
                            </div>
                            <div class="bco-grid">
                                <div class="bco-field">
                                    <label class="bco-label" for="{{ $pfx }}Ci">{{ __('bus.checkout.citizenship') }}</label>
                                    <div class="bco-select-wrap">
                                        <select id="{{ $pfx }}Ci" class="bco-select bus-v2-citizenship" name="citizenship[]"
                                            data-target="{{ $issuingFld }}"
                                            data-error-key="citizenship.{{ $key }}"
                                            aria-describedby="{{ $pfx }}CiErr" aria-invalid="false">
                                            <option value="">{{ __('bus.checkout.select_option') }}</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->iso }}" data-iso3="{{ $country->iso3 }}"
                                                    {{ $selCit === $country->iso ? 'selected' : '' }}>{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down bco-select-caret"></i>
                                    </div>
                                    <span id="{{ $pfx }}CiErr" class="bco-field-err bus-v2-field-error" data-error-for="citizenship.{{ $key }}" aria-live="polite"></span>
                                </div>
                                <div class="bco-field">
                                    <label class="bco-label" for="{{ $pfx }}Pp">{{ __('bus.checkout.passport_number') }}</label>
                                    <input type="text" id="{{ $pfx }}Pp" class="bco-input" name="identification_number[]"
                                        value="{{ old('identification_number.' . $key) }}"
                                        data-error-key="identification_number.{{ $key }}" autocomplete="off"
                                        placeholder="{{ __('bus.checkout.passport_number') }}"
                                        aria-describedby="{{ $pfx }}PpErr" aria-invalid="false">
                                    <span id="{{ $pfx }}PpErr" class="bco-field-err bus-v2-field-error" data-error-for="identification_number.{{ $key }}" aria-live="polite"></span>
                                </div>
                                <div class="bco-field">
                                    <label class="bco-label" for="{{ $pfx }}Pe">{{ __('bus.checkout.passport_expiry') }}</label>
                                    <div class="bco-input-icon-wrap">
                                        <i class="fas fa-calendar-check bco-input-icon"></i>
                                        <input type="text" id="{{ $pfx }}Pe" class="bco-input bco-input--icon bus-v2-expirydate js-bus-v2-datepicker"
                                            data-datepicker-kind="expiry" name="identification_expiry_date[]" value="{{ $expVal }}"
                                            autocomplete="off" readonly
                                            data-error-key="identification_expiry_date.{{ $key }}" placeholder="DD.MM.YYYY"
                                            aria-describedby="{{ $pfx }}PeErr" aria-invalid="false">
                                    </div>
                                    <span id="{{ $pfx }}PeErr" class="bco-field-err bus-v2-field-error" data-error-for="identification_expiry_date.{{ $key }}" aria-live="polite"></span>
                                </div>
                                <div class="bco-field">
                                    <label class="bco-label" for="{{ $pfx }}Vi">{{ __('bus.checkout.visa_or_permit') }}</label>
                                    <div class="bco-select-wrap">
                                        <select id="{{ $pfx }}Vi" class="bco-select" name="visa_permit_type[]"
                                            data-error-key="visa_permit_type.{{ $key }}"
                                            aria-describedby="{{ $pfx }}ViErr" aria-invalid="false">
                                            <option value="">{{ __('bus.checkout.select_option') }}</option>
                                            <option value="single_or_double_entry_visa"    {{ old('visa_permit_type.' . $key) === 'single_or_double_entry_visa'    ? 'selected' : '' }}>{{ __('bus.visa_permit.single_or_double_entry_visa') }}</option>
                                            <option value="multiple_entry_visa"            {{ old('visa_permit_type.' . $key) === 'multiple_entry_visa'            ? 'selected' : '' }}>{{ __('bus.visa_permit.multiple_entry_visa') }}</option>
                                            <option value="eu_citizenship"                 {{ old('visa_permit_type.' . $key) === 'eu_citizenship'                 ? 'selected' : '' }}>{{ __('bus.visa_permit.eu_citizenship') }}</option>
                                            <option value="eu_residence_permit"            {{ old('visa_permit_type.' . $key) === 'eu_residence_permit'            ? 'selected' : '' }}>{{ __('bus.visa_permit.eu_residence_permit') }}</option>
                                            <option value="eu_family_with_residence_card"  {{ old('visa_permit_type.' . $key) === 'eu_family_with_residence_card'  ? 'selected' : '' }}>{{ __('bus.visa_permit.eu_family_with_residence_card') }}</option>
                                            <option value="local_border_permit"            {{ old('visa_permit_type.' . $key) === 'local_border_permit'            ? 'selected' : '' }}>{{ __('bus.visa_permit.local_border_permit') }}</option>
                                            <option value="long_stay_visa"                 {{ old('visa_permit_type.' . $key) === 'long_stay_visa'                 ? 'selected' : '' }}>{{ __('bus.visa_permit.long_stay_visa') }}</option>
                                            <option value="diplomat_or_high_ranking_official" {{ old('visa_permit_type.' . $key) === 'diplomat_or_high_ranking_official' ? 'selected' : '' }}>{{ __('bus.visa_permit.diplomat_or_high_ranking_official') }}</option>
                                            <option value="refugee_or_person_in_need"      {{ old('visa_permit_type.' . $key) === 'refugee_or_person_in_need'      ? 'selected' : '' }}>{{ __('bus.visa_permit.refugee_or_person_in_need') }}</option>
                                        </select>
                                        <i class="fas fa-chevron-down bco-select-caret"></i>
                                    </div>
                                    <span id="{{ $pfx }}ViErr" class="bco-field-err bus-v2-field-error" data-error-for="visa_permit_type.{{ $key }}" aria-live="polite"></span>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="identification_type[]" value="international_passport">
                        <input type="hidden" id="{{ $issuingFld }}" name="identification_issuing_country[]" value="{{ $selIssuing }}">
                    </article>
                @endforeach

                @if ($passengerCount > 1)
                    <div class="bco-step-actions bco-step-actions--compact">
                        <button type="button" class="bco-btn bco-btn--ghost" data-passenger-step-prev disabled>
                            <i class="fas fa-arrow-left"></i> {{ __('bus.checkout.previous_step') }}
                        </button>
                        <button type="button" class="bco-btn bco-btn--next" data-passenger-step-next>
                            {{ __('bus.checkout.next_step') }} <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                @endif
            </div>

            {{-- ── Right: order summary ─────────────────────────────────────── --}}
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
                        <div class="bco-summary-city">{{ $firstSeg['departure_station']['name'] ?? '' }}</div>
                        <div class="bco-summary-route-icon bco-summary-route-icon--bla"><i class="fas fa-arrow-right"></i></div>
                        <div class="bco-summary-city">{{ $lastSeg['arrival_station']['name'] ?? '' }}</div>
                    </div>

                    <ul class="bco-summary-list">
                        @foreach ($blaSegments as $seg)
                            <li>
                                <i class="fas fa-bus"></i>
                                <span>{{ $seg['departure_station']['name'] ?? '' }} → {{ $seg['arrival_station']['name'] ?? '' }}</span>
                                @php $summaryDeparture = $segmentDepartureTime($seg); @endphp
                                @if ($summaryDeparture !== '')<small>{{ date('H:i', strtotime($summaryDeparture)) }}</small>@endif
                            </li>
                        @endforeach
                        <li><i class="fas fa-route"></i><span>{{ __('bus.checkout.segments_count', ['count' => count($blaSegments)]) }}</span></li>
                        <li><i class="fas fa-users"></i><span>{{ __('bus.checkout.passengers_count', ['count' => $passengerCount]) }}</span></li>
                    </ul>

                    @include('v2.service.bus.partials.checkout-journey-details-toggle', ['journeyDetailsList' => $journeyDetailsList])

                    <div class="bco-total bco-total--bla">
                        <div>
                            <span class="bco-total-label">{{ __('bus.checkout.total_to_charge') }}</span>
                            <strong class="bco-total-amount">€{{ number_format((float) $blaPrice, 2) }}</strong>
                        </div>
                        <span class="bco-total-shield"><i class="fas fa-shield-alt"></i></span>
                    </div>

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
        </div>
    </form>
</section>
@endif
