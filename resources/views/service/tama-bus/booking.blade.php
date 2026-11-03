@if (session('booking'))
    @php
        $booking = session('booking');
         $total_price = session('price');
        $reservation = $booking[0]; // Access the first (and only) reservation in the array
       // Access the first (and only) passenger in the array
    $countries = \App\Models\Country::all();
    @endphp
    <style>
        .card {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            /*padding: 10px;*/
            /*font-size: 16px;*/
            border-radius: 5px;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
        }

        .section-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .section-subtitle {
            font-size: 18px;
            font-weight: bold;
            color: #d9534f;
            margin-top: 20px;
        }

        .booking-button {
            background-color: #28a745;
            font-size: 18px;
            padding: 12px;
            border-radius: 5px;
            font-weight: bold;
        }

        .booking-button:hover {
            background-color: #218838;
            transition: background-color 0.3s ease-in-out;
        }

        .cart-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .total-price {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }

        .timer-countdown {
            font-size: 20px;
            font-weight: bold;
            color: #ff6f00;
        }

        .custom-divider {
            border: 1px solid #ddd;
        }

        .dep-time {
            font-size: 22px;
            color: #007bff;
        }

    </style>
    <div class="container-fluid bus_details">
        <div class="row" id="detectBooking">
            <form id="frmBusBookflix" class="form-horizontal" action="{{ secure_url('bus/confirm') }}" method="POST">
                @csrf
                <div class="col-md-12">
                    <div id="hidden">
                        <input type="hidden" class="form-control" id="reservation_token" name="reservation_token" value="{{ $reservation['reservation_token'] }}" readonly>
                        <input type="hidden" class="form-control" id="reservation_id" name="reservation_id" value="{{ $reservation['reservation_id'] }}" readonly>
                        <input type="hidden" class="form-control" id="donation" name="donation" value="{{ $reservation['donation'] }}" readonly>
                        <input type="hidden" class="form-control" id="departure_time" name="departure_time" value="{{ $reservation['departure_time'] }}" readonly>
                        <input type="hidden" class="form-control" id="from_name" name="from_name" value="{{ $reservation['from_name'] }}" readonly>
                        <input type="hidden" class="form-control" id="to_name" name="to_name" value="{{ $reservation['to_name'] }}" readonly>
                        <input type="hidden" class="form-control" id="price" name="price" value="{{ $total_price }}" readonly>
                        <input type="hidden" class="form-control" id="total_price" name="total_price" value="{{ $reservation['price']  }}" readonly>
                    </div>
                    <!-- Passengers & Contact Details Section -->
                    <div class="col-md-8 col-xs-12">
                        @foreach ($reservation['passenger_details'] as $key => $passenger)
                            <div class="booking-shopping-cart card">
                                <div class="booking-shopping-cart card">
                                    <h3 class="section-title">{{ trans('service.passenger') }} #{{ $passenger['type'] == 'adult' ? 'ADULT' : 'CHILD' }} {{ $key + 1 }}</h3>
                                    <h5 class="section-subtitle">{{ trans('service.passenger_details') }}</h5>
                                    <hr>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="firstname">{{ trans('service.first_name') }}</label>
                                                <input type="text" class="form-control" id="firstname" name="firstname[]" value="{{ $passenger['first_name'] ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="lastname">{{ trans('service.lastname') }}</label>
                                                <input type="text" class="form-control" id="lastname" name="lastname[]" value="{{ $passenger['last_name'] ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="birthdate">{{ trans('service.dob') }}</label>
                                                <input type="text" class="form-control" id="birthdate{{ $key }}" name="birthdate[]" value="">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="gender">{{ trans('service.gender') }}</label>
                                                <select class="form-control" name="gender[]">
                                                    <option>{{ trans('service.select_option') }}</option>
                                                    <option value="male">{{ trans('service.gender_male') }}</option>
                                                    <option value="female">{{ trans('service.gender_female') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <h5 class="section-subtitle">{{ trans('service.contact_details') }}</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="email">{{ trans('service.email') }}</label>
                                            <input type="email" class="form-control" id="email" name="email[]" value="{{ $passenger['email'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="phone">{{ trans('service.phone') }}</label>
                                            <input type="text" class="form-control phone-number" id="phone" name="phone_number[]" value="+33" maxlength="12">
                                            <span class="phone-error" style="color: red; display: none;">{{ trans('users.error_mobile_no') }}.</span>
                                        </div>
                                    </div>
                                    <h5 class="section-subtitle">{{ trans('service.passport_proof') }}</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="citizenship">{{ trans('service.citizenship') }}</label>
                                            <select class="form-control select2" id="citizenship{{ $key }}" name="citizenship[]">
                                                <option>{{ trans('service.select_option') }}</option>
                                                @foreach($countries as $country)
                                                    <option value="{{ $country->iso }}" data-iso3="{{ $country->iso3 }}">
                                                        {{ $country->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Hidden field to hold the iso3 value -->
                                        <input type="hidden" id="identification_issuing_country{{ $key }}" name="identification_issuing_country" />


                                        <div class="col-md-6">
                                            <label for="identification_number">{{ trans('service.passport_no') }}</label>
                                            <input type="text" class="form-control" id="identification_number" name="identification_number[]">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="identification_expiry_date">{{ trans('service.passport_expire') }}</label>
                                            <input type="text" class="form-control" id="identification_expiry_date{{ $key }}" name="identification_expiry_date[]" value="">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="visa_permit_type">{{ trans('service.visa_permit') }}</label>
                                            <select class="form-control" name="visa_permit_type[]" id="visa_permit_type{{ $key }}">
                                                <option>{{ trans('service.select_option') }}</option>
                                                <option value="single_or_double_entry_visa">{{ trans('service.single_or_double_entry_visa') }}</option>
                                                <option value="multiple_entry_visa">{{ trans('service.multiple_entry_visa') }}</option>
                                                <option value="eu_citizenship">{{ trans('service.eu_citizenship') }}</option>
                                                <option value="eu_residence_permit">{{ trans('service.eu_residence_permit') }}</option>
                                                <option value="eu_family_with_residence_card">{{ trans('service.eu_family_with_residence_card') }}</option>
                                                <option value="local_border_permit">{{ trans('service.local_border_permit') }}</option>
                                                <option value="long_stay_visa">{{ trans('service.long_stay_visa') }}</option>
                                                <option value="diplomat_or_high_ranking_official">{{ trans('service.diplomat_or_high_ranking_official') }}</option>
                                                <option value="refugee_or_person_in_need">{{ trans('service.refugee_or_person_in_need') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" class="form-control" id="identification_type" name="identification_type[]" value="international_passport">
                                    <input type="hidden" class="form-control" id="ticket_id" name="ticket_id[]" value="{{ $passenger['ticket_id'] }}">
                                    <input type="hidden" class="form-control" id="reference_id" name="reference_id[]" value="{{ $passenger['reference_id'] }}">
                                    <input type="hidden" class="form-control" id="trip_type" name="trip_type[]" value="{{ $passenger['trip_type'] }}">
                                    <input type="hidden" id="product_type" name="product_type[]" value="{{ $passenger['product_type'] }}" readonly>
                                    <input type="hidden" id="passenger_no" name="passenger_no[]" value="{{ $passenger['passenger_no'] }}" readonly>
                                </div>
                            </div>
                            <script>
                                $(document).ready(function() {
                                    $("#birthdate{{ $key }}").datepicker({
                                        dateFormat: "dd.mm.yy",
                                        changeMonth: true,
                                        changeYear: true,
                                        maxDate: '-14y',
                                        yearRange: "1900:{{ date('Y') }}"
                                    });
                                    $("#identification_expiry_date{{ $key }}").datepicker({
                                        changeMonth: true,
                                        dateFormat: "dd.mm.yy",
                                        changeYear: true,
                                        minDate: '0',
                                    });
                                    // Fix the change event for the dynamically generated dropdown
                                    $('#citizenship{{ $key }}').change(function() {
                                        // Get the selected option's data-iso3 attribute using the dynamic ID
                                        var iso3 = $('#citizenship{{ $key }} option:selected').attr('data-iso3');
                                        // Set the hidden input field value to the iso3 value
                                        $('#identification_issuing_country{{ $key }}').val(iso3);
                                    });
                                });

                            </script>
                            <script>
                                $(document).ready(function() {
                                    $(document).on('keydown', function(event) {
                                        // Check if the Shift or Ctrl key is pressed
                                        if (event.ctrlKey) {
                                            event.preventDefault();  // Disable Shift and Ctrl keys
                                            return false;  // Stops further processing of the event
                                        }
                                    });
                                });
                                $(document).ready(function() {
                                    const phoneInput = $('#phone');

                                    // Ensure the cursor is always placed after the '+33'
                                    phoneInput.on('focus', function() {
                                        const input = this;
                                        setTimeout(function() {
                                            input.setSelectionRange(3, 3); // Set cursor after '+33'
                                        }, 0);
                                    });

                                    // Prevent user from deleting the '+33' and allow only numbers
                                    phoneInput.on('keydown', function(event) {
                                        const allowedKeys = ['Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete'];
                                        const isNumberKey = event.key >= '0' && event.key <= '9';
                                        const selectionStart = this.selectionStart;

                                        // Prevent deleting or editing '+33'
                                        if (selectionStart < 3 && (event.key === 'Backspace' || event.key === 'Delete')) {
                                            event.preventDefault();
                                        }

                                        // Allow navigation keys and prevent non-numeric input
                                        if (!isNumberKey && !allowedKeys.includes(event.key)) {
                                            event.preventDefault();
                                        }
                                    });

                                    // Prevent non-numeric characters
                                    phoneInput.on('input', function() {
                                        const input = this;
                                        if (input.value.indexOf('+33') !== 0) {
                                            input.value = '+33';
                                        }
                                        // Replace any non-numeric characters after +33
                                        input.value = '+33' + input.value.slice(3).replace(/\D/g, '');
                                    });
                                });
                            </script>
                        @endforeach
                    </div>
                    <div class="col-md-4 col-xs-12" id="booking-cart">
                        <div class="booking-shopping-cart card shadow-lg p-4">
                            <h3 class="cart-title mb-4 d-flex justify-content-between align-items-center">
                                <span>{{ trans('service.your_order') }}</span>
                                <div class="dep-time text-primary d-flex align-items-center">
                                    <i class="fas fa-clock"></i>
                                    <span id="dep_time">{{ date('H:i', strtotime($reservation['departure_time'])) }}</span>
                                </div>
                            </h3>

                            <hr class="custom-divider">
                            <div class="row align-items-center">

                                <div class="col-md-12 col-xs-12 mt-3">
                                    <h5><i class="fas fa-map-marker-alt text-danger"></i>
                                        {{ $reservation['from_name'] }}
                                    </h5>
                                    <h5><i class="fas fa-map-marker-alt text-success"></i>
                                        {{ $reservation['to_name'] }}
                                    </h5>
                                </div>
                            </div>
                            <hr class="custom-divider mt-4 mb-4">
                            <h3 class="total-price text-center">
                                <span>€</span> {{ $total_price }}
                            </h3>
                            <p class="text-center text-warning">{{ trans('service.reserved') }}<span id="timer" class="timer-countdown">10:00</span> {{ trans('service.minutes') }}.</p>
                            <button type="submit"  id="bookflixbus" class="btn btn-primary booking-button" id="frmBusBoo1kbtn">{{ trans('service.confirm_your_order') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
