@extends('v2.layout.simple.master')

@section('title', __('bus.page_title'))

@section('style')
    @php
        $busV2ReservationAssetVersion = 'reservation-coach-v4';
        $busV2CssVersion = (@filemtime(public_path('css/bus-v2.css')) ?: time()) . '-' . $busV2ReservationAssetVersion;
        $busV2JsVersion = (@filemtime(public_path('js/bus-v2.js')) ?: time()) . '-' . $busV2ReservationAssetVersion;
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.6.0/build/css/intlTelInput.css">
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('assets/css/vendors/select2.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('css/bus-v2.css') }}?v={{ $busV2CssVersion }}" rel="stylesheet">
    <link href="{{ secure_asset('css/bco.css') }}?v={{ @filemtime(public_path('css/bco.css')) ?: time() }}" rel="stylesheet">
@endsection

@include('v2.layout.simple.breadcrumb', ['data' => [
    ['name' => __('bus.page_title'), 'url' => '', 'active' => 'yes']
]])

@section('content')
    <div class="container-fluid">
        <div class="bus-v2-page bus-v2-platform-page">
            <div class="bus-v2-shell">
                @include('v2.service.bus.partials.search')

                <div id="busV2State" class="bus-v2-state">
                    @include('v2.service.bus.partials.state')
                </div>
            </div>
        </div>
    </div>

    <div id="busV2ExpiryModal" class="bus-v2-modal" aria-hidden="true">
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
                tripStops: @json(route('bus.v2.trip.stops')),
                transactions: @json(secure_url('transactions-v2'))
            },
            defaults: {
                date: @json(date('Y-m-d'))
            },
            booking: {
                durationSeconds: 600
            },
            design: "standard",
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
    <script>
    (function () {
        var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        function updateDayName(inputId, spanId) {
            var el   = document.getElementById(inputId);
            var span = document.getElementById(spanId);
            if (!span || !el) return;
            var val = el.value;
            if (val) {
                var d = new Date(val + 'T00:00:00');
                span.textContent = isNaN(d) ? '' : days[d.getDay()];
            } else {
                span.textContent = '';
            }
        }

        /* day name updates on datepicker change */
        document.addEventListener('change', function (e) {
            if (!e.target) return;
            if (e.target.id === 'busV2DepartureDate') updateDayName('busV2DepartureDate', 'busV2DepartureDayName');
            if (e.target.id === 'busV2ReturnDate')    updateDayName('busV2ReturnDate',    'busV2ReturnDayName');
        });

        /* trip toggle pill — sync .two-way class on container */
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.js-bus-v2-trip-type');
            if (!btn) return;
            var toggle = document.getElementById('busV2TripToggle');
            if (!toggle) return;
            toggle.classList.toggle('two-way', btn.dataset.value === 'round_trip');
        });

        /* swap button: spin icon + fade route cards */
        document.addEventListener('click', function (e) {
            var swapBtn = document.getElementById('busV2Swap');
            if (!swapBtn || !swapBtn.contains(e.target)) return;

            /* spin */
            swapBtn.classList.remove('spinning');
            void swapBtn.offsetWidth;
            swapBtn.classList.add('spinning');
            setTimeout(function () { swapBtn.classList.remove('spinning'); }, 450);

            /* fade cards out then back in */
            var fromCard = document.getElementById('busV2FromField');
            var toCard   = document.getElementById('busV2ToField');
            if (fromCard && toCard) {
                [fromCard, toCard].forEach(function (c) {
                    c.style.transition = 'opacity 0.15s, transform 0.15s';
                    c.style.opacity    = '0.4';
                    c.style.transform  = 'translateY(4px) scale(0.97)';
                });
                setTimeout(function () {
                    [fromCard, toCard].forEach(function (c) {
                        c.style.opacity   = '1';
                        c.style.transform = '';
                    });
                    setTimeout(function () {
                        [fromCard, toCard].forEach(function (c) {
                            c.style.transition = '';
                        });
                    }, 300);
                }, 200);
            }
        });

        /* pax card chevron aria-expanded sync */
        document.addEventListener('click', function (e) {
            var paxCard = document.getElementById('busV2PaxCard');
            if (!paxCard) return;
            var panel = document.getElementById('busV2PassengerPanel');
            if (!panel) return;
            /* give bus-v2.js time to toggle [hidden] first */
            setTimeout(function () {
                var isOpen = !panel.hasAttribute('hidden');
                paxCard.setAttribute('aria-expanded', String(isOpen));
            }, 10);
        });
    })();
    </script>
@endsection
