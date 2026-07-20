@extends('v2.layout.simple.master')

@section('title', __('bus.page_title'))

@section('style')
    @php
        $busV2ReservationAssetVersion = 'reservation-coach-v4';
        $busV2CssVersion = (@filemtime(public_path('css/bus-v2.css')) ?: time()) . '-' . $busV2ReservationAssetVersion;
        $busV2DeskCssVersion = (@filemtime(public_path('css/bus-v2-desk.css')) ?: time()) . '-' . $busV2ReservationAssetVersion;
        $busV2JsVersion = (@filemtime(public_path('js/bus-v2.js')) ?: time()) . '-' . $busV2ReservationAssetVersion;
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.6.0/build/css/intlTelInput.css">
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('assets/css/vendors/select2.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('css/bus-v2.css') }}?v={{ $busV2CssVersion }}" rel="stylesheet">
    <link href="{{ secure_asset('css/bus-v2-desk.css') }}?v={{ $busV2DeskCssVersion }}" rel="stylesheet">
    <link href="{{ secure_asset('css/bco.css') }}?v={{ @filemtime(public_path('css/bco.css')) ?: time() }}" rel="stylesheet">
@endsection

@include('v2.layout.simple.breadcrumb', ['data' => [
    ['name' => __('bus.page_title'), 'url' => '', 'active' => 'yes']
]])

@section('content')
    @php
        $flixLogo = secure_asset('images/search-flix.png');
        $blaLogo = secure_asset('images/search-bla.png');
    @endphp
    <div class="container-fluid">
        <div class="bus-v2-page bus-v2-desk">
            <div class="bus-v2-desk-shell">
                <section class="bus-v2-desk-hero">
                    <div class="bus-v2-desk-hero-main">
                        <span class="bus-v2-desk-kicker">
                            <i class="fas fa-bus-simple"></i>
                            {{ __('bus.page_title') }}
                        </span>
                        <h1>{{ __('bus.search.submit') }}</h1>
                        <p>{{ __('bus.search.note') }}</p>
                    </div>
                    <div class="bus-v2-desk-hero-board" aria-label="{{ __('bus.search.supported_by') }}">
                        <div class="bus-v2-provider-card is-flix">
                            <img src="{{ $flixLogo }}" alt="FlixBus" class="bus-v2-provider-logo">
                        </div>
                        <div class="bus-v2-provider-card is-bla">
                            <img src="{{ $blaLogo }}" alt="BlaBlaBus" class="bus-v2-provider-logo">
                        </div>
                    </div>
                </section>

                @include('v2.service.bus.designs.desk.partials.search')

                <div id="busV2State" class="bus-v2-state bus-v2-desk-state">
                    @include('v2.service.bus.designs.desk.partials.state')
                </div>
            </div>
        </div>
    </div>

    <div id="busV2ExpiryModal" class="bus-v2-modal bus-v2-desk-modal" aria-hidden="true">
        <div class="bus-v2-modal-backdrop"></div>
        <div class="bus-v2-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="busV2ExpiryTitle">
            <div class="bus-v2-modal-icon">
                <i class="fas fa-clock"></i>
            </div>
            <h3 id="busV2ExpiryTitle">{{ __('bus.modal.session_expired_title') }}</h3>
            <p>{{ __('bus.modal.session_expired_description') }}</p>
            <div class="bus-v2-modal-actions">
                <button type="button" class="bus-v2-button bus-v2-button--primary" data-bus-v2-restart-booking>
                    {{ __('bus.modal.restart_booking') }}
                </button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        window.busV2Config = {
            csrfToken: @json(csrf_token()),
            locale: @json(app()->getLocale()),
            cities: @json($cities),
            routes: {
                index: @json(route('bus.v2')),
                restart: @json(route('bus.v2', ['restart' => 1])),
                search: @json(route('bus.v2.search')),
                reset: @json(route('bus.v2.reset')),
                transactions: @json(secure_url('transactions-v2'))
            },
            defaults: {
                date: @json(date('Y-m-d'))
            },
            booking: {
                durationSeconds: 600
            },
            design: "desk",
            i18n: {
                passengers: {
                    adultOne: @json(__('bus.passenger.adult_one', ['count' => '__COUNT__'])),
                    adultOther: @json(__('bus.passenger.adult_other', ['count' => '__COUNT__'])),
                    childOne: @json(__('bus.passenger.child_one', ['count' => '__COUNT__'])),
                    childOther: @json(__('bus.passenger.child_other', ['count' => '__COUNT__'])),
                    total: @json(__('bus.passenger.total', ['count' => '__COUNT__']))
                },
                search: {
                    autocompleteHint: @json(__('bus.search.autocomplete_hint'))
                },
                errors: {
                    origin: @json(__('bus.validation.origin_required')),
                    destination: @json(__('bus.validation.destination_required')),
                    differentDestination: @json(__('bus.validation.different_destination')),
                    departure: @json(__('bus.validation.departure_required')),
                    returnDate: @json(__('bus.validation.return_required')),
                    returnAfterDeparture: @json(__('bus.validation.return_after_departure')),
                    passenger: @json(__('bus.validation.passenger_required'))
                },
                messages: {
                    processing: @json(__('bus.messages.processing')),
                    creatingReservation: @json(__('bus.messages.creating_reservation')),
                    creatingReservationDescription: @json(__('bus.messages.creating_reservation_description')),
                    genericError: @json(__('bus.messages.generic_error')),
                    resetFailed: @json(__('bus.messages.reset_failed')),
                    sessionExpired: @json(__('bus.messages.session_expired'))
                },
                checkout: {
                    timerLabel: @json(__('bus.checkout.timer_label')),
                    timerLoading: @json(__('bus.checkout.timer_loading')),
                    timerExpired: @json(__('bus.checkout.timer_expired'))
                },
                modal: {
                    title: @json(__('bus.modal.session_expired_title')),
                    description: @json(__('bus.modal.session_expired_description')),
                    restart: @json(__('bus.modal.restart_booking')),
                    reload: @json(__('bus.modal.reload_page'))
                },
                checkoutValidation: {
                    firstName: @json(__('bus.validation.first_name_required')),
                    lastName: @json(__('bus.validation.last_name_required')),
                    birthdate: @json(__('bus.validation.birthdate_required')),
                    gender: @json(__('bus.validation.gender_required')),
                    email: @json(__('bus.validation.email_required')),
                    phone: @json(__('bus.validation.phone_required')),
                    phoneInvalid: @json(__('bus.validation.valid_phone')),
                    citizenship: @json(__('bus.validation.citizenship_required')),
                    passportNumber: @json(__('bus.validation.passport_number_required')),
                    passportExpiry: @json(__('bus.validation.passport_expiry_required')),
                    passportExpiryFuture: @json(__('bus.validation.passport_expiry_future')),
                    visa: @json(__('bus.validation.visa_required')),
                    completeCurrent: @json(__('bus.validation.complete_current_passenger'))
                },
                datepicker: @json(trans('bus.datepicker'))
            },
            datepicker: {
                travelFormat: "yy-mm-dd",
                documentFormat: "dd.mm.yy"
            }
        };
    </script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.6.0/build/js/intlTelInput.min.js"></script>
    <script src="{{ secure_asset('vendor/date-picker/jquery-ui.js') }}"></script>
    <script src="{{ secure_asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ secure_asset('js/bus-v2.js') }}?v={{ $busV2JsVersion }}"></script>
@endsection
