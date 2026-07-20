<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($page_title) ? $page_title : "Login" }}</title>
    <link href="{{ secure_asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('css/login.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ secure_asset('vendor/font-awesome/css/fontawesome-all.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('vendor/jquery-confirm/jquery-confirm.min.css') }}">
    <link href="{{ secure_asset('vendor/intl-input/css/intlTelInput.css') }}" rel="stylesheet">
    <script src="{{ secure_asset('vendor/jquery/jquery-3.3.1.js') }}"></script>
</head>
<body class="content">
<main role="main">
    <section class="webapp-auth ">
        <figure class="webapp-auth__figure">
            <img src="{{ secure_asset('images/logo.png') }}" alt="" style="width: 150px;height: auto;">
        </figure>
        <input type="hidden" id="flag" value="france">
        <section class="account-form-container">
            <h1 class="tama-login">{{ trans('login.lbl_two_step') }}</h1>
            <form class="box account-form" id="frmLogin" action="{{ secure_url('generate_otp') }}" method="POST">
                {{ csrf_field() }}
                <input type="hidden" class="form-control" name="username" value="{{$username}}">
                <input type="hidden" class="form-control" name="password" value="{{$password}}">
                <input type="hidden" class="form-control" name="lang" value="{{$lang}}">
                <div class="settings-form__field">
                    <label class="settings-form__field__label" for="username">{{ trans('login.lbl_mobile') }}</label>
                    <input class="settings-form__field__input" type="text" name="mobile" id="mobile" placeholder="{{ trans('login.lbl_mobile') }}" autofocus tabindex="1">
                </div>
                <div class="settings-form__field">
                    <label class="settings-form__field__label" for="password">{{ trans('login.lbl_email') }}</label>
                    <input id="email" type="text" class="settings-form__field__input" name="email" placeholder="{{ trans('login.lbl_email') }}" autofocus tabindex="1">
                </div>
                <div class="settings-form__field">
                    <label class="settings-form__field__label" for="password">{{ trans('login.lbl_ip_address') }}</label>
                    <input id="ip_address" type="text" class="settings-form__field__input" name="ip_address" value="{{$ip_address}}" readonly >
                </div>
                <button tabindex="5" type="submit" class="btn btn-danger btn-deep-purple">{{ trans('login.lbl_Update') }}</button>
            </form>
        </section>
    </section>
</main>
<script src="{{ secure_asset('vendor/jquery-validator/jquery.validate.min.js') }}"></script>
<script src="{{ secure_asset('vendor/common/loadingoverlay.min.js') }}"></script>
<script src="{{ secure_asset('vendor/jquery-confirm/jquery-confirm.min.js') }}"></script>
<script src="{{ secure_asset('vendor/intl-input/js/intlTelInput.js') }}" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        var telInput = $("#mobile"),
            errorMsg = $("#span_mobile");
        // initialise plugin
        telInput.intlTelInput({
            nationalMode: true,
            utilsScript: "{{ secure_asset('vendor/intl-input/js/utils.js') }}"
        });
        var reset = function () {
            telInput.removeClass("has-error");
            errorMsg.addClass("hide");
        };
        telInput.on('change keyup paste input focus blur', function (e) {
            var code = (e.keyCode || e.which);
            // skip arrow keys
            if (code == 37 || code == 38 || code == 39 || code == 40 || code == 8) {
                return;
            }
            if ($.trim(telInput.val())) {
                if (telInput.intlTelInput("isValidNumber")) {
                    telInput.parents(".form-group").addClass("").removeClass("has-error");
                    var intlNumber = telInput.intlTelInput("getNumber");
                    var countryData = telInput.intlTelInput("getSelectedCountryData");
                    telInput.val(intlNumber);
                    errorMsg.addClass("hide");
                } else {
                    telInput.parents(".form-group").addClass("has-error").removeClass("");
                    errorMsg.removeClass("hide");
                }
            }
            // if first character is 0 filter it off
            var num = $(this).val();
            var flag =$('#flag').val();
            if(flag == '')
            {
                if (num.length == '') {
                    $(this).val('+');
                }
            }
            else
            {
                if (num.length == '') {
                    $(this).val('+33');
                }
            }
        });
        // trigger a fake "change" event now, to trigger an initial sync
        telInput.change();
        @if(\Session::has('message'))
        $.alert({
            title: "{{ ucfirst(session('message_type'))  }}",
            content: '{{ session('message')  }}',
            buttons: {
                "{{ trans('common.btn_close') }}": function () {

                }
            },
            type : "{{ \app\Library\AppHelper::message_types(session('message_type'))  }}",
            autoClose: '{{ trans('common.btn_close') }}'
        });
        @endif

    });
</script>
</body>
</html>

