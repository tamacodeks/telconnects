@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
    ['name' => "Bus",'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('css/both-booking.css') }}" rel="stylesheet"/>
    <link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('css/topup.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@17/build/css/intlTelInput.css" />
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@17/build/js/intlTelInput.min.js"></script>
    @include('service.tama-bus.search')
    @include('service.tama-bus.bus')
    @include('service.tama-bus.booking')
    @include('service.tama-bus.bla_booking')
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="{{ secure_asset('vendor/date-picker/jquery-ui.js') }}"></script>
    <script src="{{ secure_asset('js/jquery.storageapi.min.js') }}"></script>

    <script>
        var api_base_url = "{{ secure_url('') }}";
        $("#cityFrom, #cityTo, #passengers").on('keyup change', function() {
            var isValid = $(this).val() !== "";
            var inputId = $(this).attr('id'); // Get the ID of the current input field

            if (isValid) {
                // Clear the corresponding error message
                $("#error" + inputId.charAt(0).toUpperCase() + inputId.slice(1)).text("");
            } else {
                // Set the corresponding error message
                $("#error" + inputId.charAt(0).toUpperCase() + inputId.slice(1)).text("This field is required.");
            }
        });
        function validateForm() {
            var isValid = true;
            // Clear any previous error messages
            $("#errorCityFrom").html("");
            $("#errorCityTo").html("");
            $("#errorDepartureDate").html("");
            $("#errorPassengers").html("");

            // Get the values from the form fields
            var cityFrom = $("#cityFrom").val().trim();
            var cityTo = $("#cityTo").val().trim();
            var departureDate = $("#departureDate").val().trim();
            var passengers = $("#passengers").val().trim();

            // Validate City From
            if (cityFrom === "") {
                $("#errorCityFrom").html(trans('flixbus.from')); // Multilingual string for error
                isValid = false;
            }

            // Validate City To
            if (cityTo === "") {
                $("#errorCityTo").html(trans('flixbus.to')); // Multilingual string for error
                isValid = false;
            }

            // Validate Departure Date
            if (departureDate === "") {
                $("#errorDepartureDate").html(trans('flixbus.select_departure_date')); // Multilingual string for error
                isValid = false;
            }

            // Validate Passengers
            if (passengers === "") {
                $("#errorPassengers").html(trans('flixbus.select_passengers')); // Multilingual string for error
                isValid = false;
            }

            // If validation fails, reset the search button and show an error message
            if (!isValid) {
                // Hide the animation if the form is not valid
                $("#detectAnime").addClass('hide');

                // Re-enable the search button
                var searchButton = $("#frmBusBookbtn");
                searchButton.prop("disabled", false);
                searchButton.html(trans('flixbus.search')); // Reset to original text
            }

            return isValid;
        }
        $("#frmBusBookbtn").click(function (e) {
            if (!validateForm()) {
                return false;
            }
            $("html, body").animate({ scrollTop: $(window).scrollTop() + 170 }, 500); // 500ms for smooth scrolling

            $(this).prop("disabled", true);
            $(this).html('<i class="fas fa-spinner fa-spin"></i> {{ trans('service.search_bus') }}');

            $("#detectAnime").removeClass('hide');
            $(".bus_details").addClass('hide');

            $("#frmBusBook").submit();
        });
        @if (session('bus_results'))
        $("html, body").animate({ scrollTop: $(window).scrollTop() + 360 }, 500); // 500ms for smooth scrolling
        @endif
        // Handling button disable and feedback on click
        $("#flixbus").click(function (e) {
            // Run validation first before submitting the form
            if (!validateForm()) {
                return false;
            }
            // If validation passed, disable button and show loading message
            $(this).prop("disabled", true);
            $(this).html('<i class="fas fa-spinner fa-spin"></i>Booking Bus');

            // Show loading animation or message
            $("#detectAnime").removeClass('hide');
            $(".bus_details").addClass('hide');

            // Submit the form after everything is set
            $("#formflixbus").submit();
        });
        $("#blabus").click(function (e) {
            // Run validation first before submitting the form
            if (!validateForm()) {
                return false;
            }
            // If validation passed, disable button and show loading message
            $(this).prop("disabled", true);
            $(this).html('<i class="fas fa-spinner fa-spin"></i>Booking Bus');

            // Show loading animation or message
            $("#detectAnime").removeClass('hide');
            $(".bus_details").addClass('hide');

            // Submit the form after everything is set
            $("#formblabus").submit();
        });
        $("#bookblabus").click(function (e) {
            e.preventDefault(); // Prevent form submission until validation passes

            // Check if all inputs are filled
            var isValid = true;
            $("#frmBusBookbla input").each(function () {
                if ($(this).val() === '') {
                    isValid = false;
                    $(this).addClass('input-error'); // Optionally, add error class to the input
                } else {
                    $(this).removeClass('input-error'); // Remove error class if input is valid
                }
            });

            // If the form is not valid, stop here
            if (!isValid) {
                alert('Please fill all required fields.');
                return;
            }

            // If validation passed, disable button and show loading message
            $(this).prop("disabled", true);
            $(this).html('<i class="fas fa-spinner fa-spin"></i> Booking Bus');

            // Show loading animation or message
            $("#detectAnime").removeClass('hide');
            $(".bus_details").addClass('hide');

            // Submit the form
            $("#frmBusBookbla").submit();
        });
        $("#bookflixbus").click(function (e) {
            e.preventDefault(); // Prevent form submission until validation passes

            // Check if all inputs are filled
            var isValid = true;
            $("#frmBusBookflix input").each(function () {
                if ($(this).val() === '') {
                    isValid = false;
                    $(this).addClass('input-error'); // Optionally, add error class to the input
                } else {
                    $(this).removeClass('input-error'); // Remove error class if input is valid
                }
            });

            // If the form is not valid, stop here
            if (!isValid) {
                alert('Please fill all required fields.');
                return;
            }

            // If validation passed, disable button and show loading message
            $(this).prop("disabled", true);
            $(this).html('<i class="fas fa-spinner fa-spin"></i> Booking Bus');

            // Show loading animation or message
            $("#detectAnime").removeClass('hide');
            $(".bus_details").addClass('hide');

            // Submit the form
            $("#frmBusBookflix").submit();
        });



        $("#sort_by").change(function (e) {
            e.preventDefault(); // Prevent default behavior if needed
            // Show the animation and hide bus details before form submission
            $("#detectAnime").removeClass('hide');
            $(".bus_details").addClass('hide');

            // Submit the form
            $('#sortForm').submit();
        });

        $("#sort_by_bus").change(function (e) {
            e.preventDefault(); // Prevent default behavior if needed
            // Show the animation and hide bus details before form submission
            $("#detectAnime").removeClass('hide');
            $(".bus_details").addClass('hide');

            // Submit the form
            $('#sortForm').submit();
        });

        $(function () {
            var availableCities = [
                    @forelse($cities as $city)
                {
                    value: "{{ $city['id'] }}",
                    label: "{{ $city['name'] }}",
                    latitude: "{{ $city['coordinates']['latitude'] }}",
                    longitude: "{{ $city['coordinates']['longitude'] }}"
                } @if(!$loop->last),@endif
                @empty
                @endforelse
            ];
            $("#cityFrom").autocomplete({
                minLength: 2,
                source: availableCities,
                select: function (event, ui) {
                    $("#cityFrom").val(ui.item.label);
                    $("#cityFromHid").val(ui.item.value);
                    $("#geolatfrom").val((ui.item.latitude));
                    $("#geolonfrom").val((ui.item.longitude));
                    return false;
                }
            });
            $("#cityTo").autocomplete({
                minLength: 2,
                source: availableCities,
                select: function (event, ui) {
                    $("#cityTo").val(ui.item.label);
                    $("#cityToHid").val(ui.item.value);
                    $("#geolatto").val(ui.item.latitude).toFixed;
                    $("#geolonto").val(ui.item.longitude).toFixed;
                    return false;
                }
            });
            $(".date").datepicker({
                changeYear: true,
                minDate: new Date(),
                maxDate: '3M',
                showButtonPanel: true,
                dateFormat: "yy-mm-dd",
                showAnim: "slideDown"
            });
        });

        $(document).ready(function () {
            $.localStorage.remove('adult');
            $.localStorage.remove('child');
            $.localStorage.remove('bikes');
        });

        $(document).on('click', function (e) {
            $('[data-toggle="popover"],[data-original-title]').each(function () {
                if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                    (($(this).popover('hide').data('bs.popover') || {}).inState || {}).click = false;
                }
            });
        });



        $(document).ready(function() {
            // Datepicker for Birthdate and Expiry Date
            $("[id^=birthdate], .expirydate").datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true
            });

            // Initialize Select2 for Citizenship and Visa Types
            $(".select2").select2({
                placeholder: 'Select an option',
                width: '100%'
            });

            // Input Mask for Phone Number
            $("[id^=phone]").mask('+0000000000000'); // Adjust mask as needed
        });

        $(function () {
            var $popover = $('.popover-markup>.trigger').popover({
                container: 'body',
                html: true,
                placement: 'bottom',
                content: function () {
                    return $(this).parent().find('.content').html();
                }
            });

            var passengers = [1, 0, 0]; // Set default value for adults to 1
            $('#adult').val(passengers[0]); // Ensure the adult input is set to 1

            // On popover click, update inputs with current passenger counts
            $('.popover-markup>.trigger').click(function (e) {
                e.stopPropagation();
                $(".popover-content input").each(function (i) {
                    $(this).val(passengers[i]);
                });
            });

            // Hide popover on outside click
            $(document).click(function (e) {
                if ($(e.target).is('.demise')) {
                    $('.popover-markup>.trigger').popover('hide');
                }
            });

            // Save passenger values on popover hide
            $popover.on('hide.bs.popover', function () {
                $(".popover-content input").each(function (i) {
                    passengers[i] = $(this).val();
                });
            });

            // Handle spinner increment/decrement
            $(document).on('click', '.number-spinner a', function () {
                var btn = $(this),
                    input = btn.closest('.number-spinner').find('input'),
                    oldValue = parseInt(input.val().trim()),
                    forSpinner = btn.attr('data-choser');
                var adults = 0, children = 0, bikes = 0, appendString = '';

                if (btn.attr('data-dir') === 'up') {
                    if (oldValue < input.attr('max')) {
                        oldValue++;
                    }
                } else {
                    if (oldValue > input.attr('min')) {
                        oldValue--;
                    }
                }
                input.val(oldValue);
                $.localStorage.set(forSpinner, oldValue);

                adults = $.localStorage.get('adult') || 1; // Default adult to 1
                children = $.localStorage.get('child') || 0;
                bikes = $.localStorage.get('bikes') || 0;

                if (adults) appendString += " Adults: " + adults + ", ";
                if (children) appendString += " Children: " + children + ", ";
                if (bikes) appendString += " Bike: " + bikes;

                $('#passengers').val(appendString);
                $("#adult").val(adults);
                $("#child").val(children);
                $("#bikes").val(bikes);
            });

            // Set default values on page load
            $(document).ready(function () {
                $.localStorage.set('adult', 1); // Set default adult value to 1
                $.localStorage.set('child', 0);
                $.localStorage.set('bikes', 0);

                $('#passengers').val(" Adults: 1");
            });
        });


        // Timer settings
        let time = 600; // 600 seconds (10 minutes)
        const timerElement = document.getElementById('timer');

        const countdown = setInterval(() => {
            // Calculate minutes and seconds
            const minutes = Math.floor(time / 60);
        const seconds = time % 60;

        // Update timer display
        timerElement.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

        // Check if time has expired
        if (time <= 0) {
            clearInterval(countdown);
            alert('Your session has expired.');
            window.location.href = '{{ secure_url("bus") }}'; // Redirect to an expiration page
        } else {
            // Decrement the time
            time--;
        }
        }, 1000);
    </script>
    <script>
        $(document).ready(function () {
            var $inputs = $(".phone-number");
            var $submitBtn = $("#bookflixbus");
            var dialCode = "+33";

            // Disable button initially
            $submitBtn.prop("disabled", true);

            const validateAllPhones = () => {
                let allValid = true;

                $inputs.each(function () {
                    const $input = $(this);
                    const iti = window.intlTelInputGlobals.getInstance(this);
                    const $errorMsg = $input.closest('.col-md-6').find(".phone-error");

                    if ($.trim($input.val())) {
                        if (iti.isValidNumber()) {
                            $errorMsg.hide();
                        } else {
                            $errorMsg.show();
                            allValid = false;
                        }
                    } else {
                        $errorMsg.hide(); // or show an error if empty is invalid
                        allValid = false;
                    }
                });

                $submitBtn.prop("disabled", !allValid);
            };

            $inputs.each(function () {
                const $input = $(this);
                const $errorMsg = $input.closest('.col-md-6').find(".phone-error");

                const iti = window.intlTelInput(this, {
                    initialCountry: "fr",
                    onlyCountries: ["fr"],
                    nationalMode: false,
                    separateDialCode: true,
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@17/build/js/utils.js",
                    autoPlaceholder: "aggressive"
                });

                const forcePrefix = () => {
                    let currentVal = $input.val();
                    if (!currentVal.startsWith(dialCode)) {
                        // Remove non-digit prefix and replace with +33
                        const digits = currentVal.replace(/[^0-9]/g, '');
                        $input.val(dialCode + digits.slice(dialCode.length));
                    }
                };

                const validatePhone = () => {
                    forcePrefix();
                    validateAllPhones();
                };

                // Prevent deleting or modifying prefix
                $input.on("keydown", function (e) {
                    if (
                        this.selectionStart < dialCode.length &&
                        (e.key === "Backspace" || e.key === "Delete" || e.key.length === 1)
                    ) {
                        e.preventDefault();
                    }
                });

                $input.on("input keyup paste focus blur", function () {
                    forcePrefix();
                    validatePhone();
                });

                // Force prefix on load
                forcePrefix();
            });
        });
    </script>


@endsection
