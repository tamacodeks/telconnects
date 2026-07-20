@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
         ['name' => "Calling cards",'url'=> secure_url('calling-cards'),'active' => 'no'],
         ['name' => $card_name,'url'=> secure_url('calling-cards/'.$card_id),'active' => 'no'],
         ['name' => $page_title,'url'=> '','active' => 'yes'],
    ]])
    <style>
        @media screen and (max-width: 992px) {
            #print_card {
                display:none;
            }
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row" id="print_card">
                            <div class="col-md-3">
                                <a href="{{ secure_url('tama-topup') }}" style="color:#000;">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <div class="row">
                                                <div class="col-xs-3">
                                                    <i class="fa fa-mobile-alt fa-4x"></i>
                                                </div>
                                                <div class="col-xs-9 text-right">
                                                    <div class="huge">&nbsp;</div>
                                                    <div class="huge-next">Tranfert de credit</div>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="{{ secure_url('tama-topup') }}" class="a-footer">
                                            <div class="panel-footer dashboard-panel-footer">
                                                <span class="pull-left">Click here</span>
                                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                                <div class="clearfix"></div>
                                            </div>
                                        </a>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ secure_url('calling-cards') }}" style="color:#000;">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <div class="row">
                                                <div class="col-xs-3">
                                                    <i class="fa fa-credit-card fa-4x"></i>
                                                </div>
                                                <div class="col-xs-9 text-right">
                                                    <div class="huge">&nbsp;</div>
                                                    <div class="huge-next">Carte Recharge</div>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="{{ secure_url('calling-cards') }}" class="a-footer">
                                            <div class="panel-footer dashboard-panel-footer">
                                                <span class="pull-left">Click here</span>
                                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                                <div class="clearfix"></div>
                                            </div>
                                        </a>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <a href="{{ secure_url('bus-v2') }}">
                                                    <img class="flix-bus-logo" src="{{ secure_asset('images/logo-big.png') }}">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ secure_url('bus-v2') }}"
                                       class="a-footer">
                                        <div class="panel-footer dashboard-panel-footer">
                                            <span class="pull-left">{{ trans('common.click_here') }}</span>
                                            <span class="pull-right"><i
                                                        class="fa fa-arrow-circle-right"></i></span>
                                            <div class="clearfix"></div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" id="loader">
                                <fieldset class="fieldset-border">
                                    <legend class="legend-border">
                                        {{ $page_title }}
                                    </legend>
                                    <div class="col-md-4"></div>
                                    <div class="col-md-4">
                                        <div class="panel panel-default">
                                            <div class="panel-body">
                                                <button id="printMe" class="btn btn-theme center-block m-b-20"><i class="fa fa-print"></i>&nbsp;{{ trans('common.btn_print') }}</button>
                                                <button class="btn btn-theme center-block hide" id="btnPrintPinAgain"><i class="fa fa-redo"></i>&nbsp;{{ trans('myservice.btn_print_pin_again') }}</button>
                                                <div class="card-block" id="print-content">
                                                    <table style="width: 100%;max-width: 100%;">
                                                        <tr>
                                                            <td style="text-align:center;">
                                                                <?php
                                                                $tp_config =  \App\Models\TelecomProvider::find($provider->id);
                                                                //                                                                    dd($tp_config);
                                                                $src_img = $tp_config->getMedia('telecom_providers_cards')->first();
                                                                $img = !empty($src_img) ? optional($src_img)->getUrl('thumb') : secure_asset('images/card_image.png');
                                                                ?>
                                                                <img class="center-block" src="{{ secure_asset($img) }}" style="margin-bottom: 5px;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align:center;border-top: 1px dashed #322f32;">
                                                                <h1 id="cardName" style="
     margin-top: 5px;font-size: 1.4em;">{{ $cards['name'] }}</h1>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align:center;border-top: 1px dashed #322f32;">
                                                                <p id="cardDesc" style="
     margin-top: 5px;font-size: .9em;">
                                                                    {{ $cards['description'] }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align:center;border-top: 1px dashed #322f32;">
                                                                <h1 style="margin-top: 5px;font-size: 17px;"><span>{{ trans('myservice.code_secret') }}</span><br>
                                                                    <span id="cardPin" style="font-size: 1.4em;color: blue">XXXXXXXXX</span>
                                                                </h1>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align:center;border-top: 1px dashed #322f32;">
                                                                <h1 style="margin-top: 5px;font-size: 14px;">{{ $cards['access_number'] }}</h1>
                                                            </td>
                                                        </tr>
                                                        {{--@if(!empty($cards['validity']) || isset($aleda_service))--}}
                                                            {{--<tr>--}}
                                                                {{--<td style="border-top:1px dashed #322f32">--}}
                                                                    {{--<h1 style="margin-top: 5px;text-align:center;font-size: 14px;"><span id="cardValidity">{{ $card->validity }}</span></h1>--}}
                                                                {{--</td>--}}
                                                            {{--</tr>--}}
                                                        {{--@endif--}}
                                                        @if(!empty($card->comment_1))
                                                            <tr>
                                                                <td style="border-top:1px dashed #322f32">
                                                                    <p style="margin-top: 5px;text-align:center;font-size: 14px;">{{ $card->comment_1 }}</p>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                        @if(!empty($card->comment_2))
                                                            <tr>
                                                                <td style="border-top:1px dashed #322f32">
                                                                    <p style="margin-top: 5px;text-align:center;font-size: 14px;"><{{ $card->comment_2 }}</p>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                        <tr>
                                                            <td style="border-top: 1px dashed #322f32;">
                                                                <table style="width:100%">
                                                                    <tr>
                                                                        <td>
                                                                            {{ trans('sale.serial') }}
                                                                        </td>
                                                                        <td id="cardSerial" align="right">XXXXXXXXX</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            {{ trans('myservice.lbl_print_client') }}
                                                                        </td>
                                                                        <td align="right">{{ auth()->user()->username }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>{{ trans('common.lbl_date') }}</td>
                                                                        <td id="cardDate" align="right">XXXXXXXXX</td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align:center;">
                                                                <img style="margin-top: 20px;width: 200px;" src="{{ secure_asset('images/logo.png') }}" class="center-block"/>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4"></div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ secure_asset('vendor/common/loadingoverlay.min.js') }}" type="text/javascript"></script>
    <script>
        var isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
        if(isFirefox){

            var n = localStorage.getItem('on_load_counter');
            if (n === null) {
                n = 0;
            }
            n++;
            localStorage.setItem("on_load_counter", n);
            if(n > 1)
            {

                // if(get_time <= minus_time)
                // {
                location.reload(true);
                // }
            }
        }
        function print_pin() {
            var contents = $("#print-content").html();
            var frame1 = $('<iframe />');
            frame1[0].name = "frame1";
            frame1.css({ "position": "absolute", "top": "-1000000px" });
            $("body").append(frame1);
            var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
            frameDoc.document.open();
            //Create a new HTML document.
            frameDoc.document.write('<html><head><title>{{ $page_title }}</title>');
            frameDoc.document.write('</head><body>');
            //Append the external CSS file.
//                       frameDoc.document.write('<link href="style.css" rel="stylesheet" type="text/css" />');
            //Append the DIV contents.
            frameDoc.document.write(contents);
            frameDoc.document.write('</body></html>');
            frameDoc.document.close();
            setTimeout(function () {
                window.frames["frame1"].focus();
                window.frames["frame1"].print();
                frame1.remove();
            }, 500);
        }

        $(document).ready(function () {
            var request;
            $("#printMe").click(function () {
                    $.LoadingOverlay("show");
                    $('body').append("<span class='loader'></span>");
                    // Abort any pending request
                    if (request) {
                        request.abort();
                    }
                    // Serialize the data in the form
                    var serializedData = {
                        pin_id: "{{ $cards['ccp_id'] }}",
                        cus_id: "{{ auth()->user()->cust_id }}",
                        telecom_provider_id: "{{ $telecom_provider_id }}",
                        face_value: "{{ $face_value }}",
                    };

                    // Fire off the request to /form.php
                    request = $.ajax({
                        url: "{{ secure_url("print_mycallingcard") }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: serializedData
                    });

                    // Callback handler that will be called on success
                    request.done(function (response, textStatus, jqXHR) {
                        // Log a message to the console
                        console.log(response);
                        if (response.data.code == 200) {
                            $("#cardPin").html('').html(response.data.result.pin);
                            $("#cardSerial").html('').html(response.data.result.serial);
                            $("#cardDate").html('').html(response.data.result.time_printed);
                            $("#printMe").remove();
                            $("#btnPrintPinAgain").removeClass('hide');
                            @if(isset($aleda_service))
                            if (response.data.result.validity != '') {
                                $("#cardValidity").html(response.data.result.validity);
                            } else {
                                $("#cardValidity").addClass('hide');
                            }
                            @endif

                            $("#tamaBalance").html(response.data.result.remain_balance);
                            print_pin();
                        }
                        else if (response.data.code == 400) {
                            $.alert({
                                content: response.data.message,
                                buttons: {
                                    "{{ trans('common.btn_close') }}": function () {

                                    }
                                },
                                type: "red",
                                icon: "fa fa-exclamation-circle",
                                theme: 'material'
                            });
                            window.location = '{{ secure_url('calling-cards')}}';
                        } else {
                            $.alert({
                                content: response.data.message,
                                buttons: {
                                    "{{ trans('common.btn_close') }}": function () {

                                    }
                                },
                                type: "red",
                                icon: "fa fa-exclamation-circle",
                                theme: 'material'
                            });
                        }
                    });

                    // Callback handler that will be called on failure
                    request.fail(function (jqXHR, textStatus, errorThrown) {
                        // Log the error to the console
                        console.error(
                            "The following error occurred: " +
                            textStatus, errorThrown
                        );
                    });

                    // Callback handler that will be called regardless
                    // if the request failed or succeeded
                    request.always(function () {
                        // Reenable the inputs
                        $(".loader").remove();
                        $.LoadingOverlay("hide");
                    });
            });

            //btn print pin again
            $("#btnPrintPinAgain").click(function () {
                print_pin();
            });
        });

        document.addEventListener('contextmenu', event => event.preventDefault());
        document.onkeydown = function() {
            switch (event.keyCode) {
                case 116 : //F5 button
                    event.returnValue = false;
                    event.keyCode = 0;
                    return false;
                case 82 : //R button
                    if (event.ctrlKey) {
                        event.returnValue = false;
                        event.keyCode = 0;
                        return false;
                    }
            }
        }
    </script>

@endsection