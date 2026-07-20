@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "TamaTopup",'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/intel/css/prism.css?v=4') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/intel/css/demo.css?v=4') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/intel/css/intlTelInput.css?v=4') }}" rel="stylesheet">
    <link href="{{ secure_asset('css/topup.css') }}" rel="stylesheet">
    <div id="loadergif" data-text="Chargement, veuillez patienter"></div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
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
                                                <span class="pull-left">{{ trans('common.click_here') }}</span>
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
                                                <span class="pull-left">{{ trans('common.click_here') }}</span>
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
                            <div id="show_providers" @if($page_title == 'TamaTopup') style="display:none;" @endif>
                                <div class="col-md-12 design-process-section" id="process-tab">
                                    <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane active" id="content">
                                            <div class="design-process-content">
                                                <div class="row">
                                                    <div class="col-md-0">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <a href="Javascript:void(0);"  id="provider_toupup">
                                                            <div class="panel panel-default cc-panel">
                                                                <div class="panel-body">
                                                                    <img src="{{ secure_asset('images/Lyca.jpg') }}" alt="" class="img-responsive center-block" style="padding: 12px;" />
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    {{--<div class="col-md-3">--}}
                                                    {{--<a href="Javascript:void(0);" onclick="fetchCallingProducts('FR','33','FR','L0FR','Libon','France')">--}}
                                                    {{--<div class="panel panel-default cc-panel">--}}
                                                    {{--<div class="panel-body">--}}
                                                    {{--<img src="https://imagerepo.ding.com/logo/L0.svg" alt="" class="img-responsive center-block" style="padding: 12px;" />--}}
                                                    {{--</div>--}}
                                                    {{--</div>--}}
                                                    {{--</a>--}}
                                                    {{--</div>--}}
                                                    <div class="col-md-3">
                                                        <a href="Javascript:void(0);"onclick="fetchCallingProducts('FR','33','FR','W2FR','World Talk Pins France','France')">
                                                            <div class="panel panel-default cc-panel">
                                                                <div class="panel-body">
                                                                    <img src="https://imagerepo.ding.com/logo/W2.svg" alt="" class="img-responsive center-block" style="padding: 30px;" />
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    {{--<div class="col-md-3">--}}
                                                    {{--<a href="Javascript:void(0);"onclick="fetchCallingProducts('FR','33','FR','H1FR','Talk Home France','France')">--}}
                                                    {{--<div class="panel panel-default cc-panel">--}}
                                                    {{--<div class="panel-body">--}}
                                                    {{--<img src="https://imagerepo.ding.com/logo/H1.svg" alt="" class="img-responsive center-block" style="padding: 30px;" />--}}
                                                    {{--</div>--}}
                                                    {{--</div>--}}
                                                    {{--</a>--}}
                                                    {{--</div>--}}
                                                    {{--<div class="col-md-3">--}}
                                                    {{--<a href="Javascript:void(0);"onclick="fetchCallingProducts('GB','44','GB','WCGB','White Calling EUR','United Kingdom')">--}}
                                                    {{--<div class="panel panel-default cc-panel">--}}
                                                    {{--<div class="panel-body">--}}
                                                    {{--<img src="https://imagerepo.ding.com/logo/WC.svg" alt="" class="img-responsive center-block" style="padding: 30px;" />--}}
                                                    {{--</div>--}}
                                                    {{--</div>--}}
                                                    {{--</a>--}}
                                                    {{--</div>--}}
                                                    <div class="col-md-0">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="show_form" @if($page_title == 'TamaTopupFrance') style="display:none;" @endif>
                                <div class="col-md-12 design-process-section" id="process-tab">
                                    <form id="frmTamaTopup" action="{{ secure_url('tama-topup/plans') }}" method="GET" class="form-horizontal">
                                        <div class="tab-content">
                                            <div role="tabpanel" class="tab-pane active" id="content">
                                                @include('service.tama-topup.tabs',['current' => 'search'])
                                                <h4 class="text-center">{{ trans('tamatopup.header_text') }}</h4>
                                                <div class="row">
                                                    <div class="col-md-4"></div>
                                                    <div class="col-md-4 m-t-20">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <input class="form-control" id="mobile" name="mobile"
                                                                       type="tel"
                                                                       tabindex="1" autofocus="true" >
                                                            </div>
                                                            <span class="error help-block hide"
                                                                  id="span_mobile">{{ trans('users.error_mobile_no') }}</span>
                                                            <input type="hidden" id="countryCode" name="countryCode">
                                                            <input type="hidden" id="countryIso" name="countryIso">
                                                        </div>
                                                        <div class="col-md-12 text-center">
                                                            <div class="form-group">
                                                                <button class="btn btn-primary" style="
    background-color: #00427f;" id="btnGetPlans" type="submit"><i
                                                                            class="fa fa-search-plus"></i>&nbsp;{{ trans('common.filter_lbl_search') }}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="form-group" id="loadingProducts" style="display: none">
                                <div class="col-md-3"></div>
                                <div class="col-md-6">
                                    <div class="load">
                                        <div class="gear one">
                                            <svg id="blue" viewbox="0 0 100 100" fill="#94DDFF">
                                                <path d="M97.6,55.7V44.3l-13.6-2.9c-0.8-3.3-2.1-6.4-3.9-9.3l7.6-11.7l-8-8L67.9,20c-2.9-1.7-6-3.1-9.3-3.9L55.7,2.4H44.3l-2.9,13.6      c-3.3,0.8-6.4,2.1-9.3,3.9l-11.7-7.6l-8,8L20,32.1c-1.7,2.9-3.1,6-3.9,9.3L2.4,44.3v11.4l13.6,2.9c0.8,3.3,2.1,6.4,3.9,9.3      l-7.6,11.7l8,8L32.1,80c2.9,1.7,6,3.1,9.3,3.9l2.9,13.6h11.4l2.9-13.6c3.3-0.8,6.4-2.1,9.3-3.9l11.7,7.6l8-8L80,67.9      c1.7-2.9,3.1-6,3.9-9.3L97.6,55.7z M50,65.6c-8.7,0-15.6-7-15.6-15.6s7-15.6,15.6-15.6s15.6,7,15.6,15.6S58.7,65.6,50,65.6z"></path>
                                            </svg>
                                        </div>
                                        <div class="gear two">
                                            <svg id="pink" viewbox="0 0 100 100" fill="#FB8BB9">
                                                <path d="M97.6,55.7V44.3l-13.6-2.9c-0.8-3.3-2.1-6.4-3.9-9.3l7.6-11.7l-8-8L67.9,20c-2.9-1.7-6-3.1-9.3-3.9L55.7,2.4H44.3l-2.9,13.6      c-3.3,0.8-6.4,2.1-9.3,3.9l-11.7-7.6l-8,8L20,32.1c-1.7,2.9-3.1,6-3.9,9.3L2.4,44.3v11.4l13.6,2.9c0.8,3.3,2.1,6.4,3.9,9.3      l-7.6,11.7l8,8L32.1,80c2.9,1.7,6,3.1,9.3,3.9l2.9,13.6h11.4l2.9-13.6c3.3-0.8,6.4-2.1,9.3-3.9l11.7,7.6l8-8L80,67.9      c1.7-2.9,3.1-6,3.9-9.3L97.6,55.7z M50,65.6c-8.7,0-15.6-7-15.6-15.6s7-15.6,15.6-15.6s15.6,7,15.6,15.6S58.7,65.6,50,65.6z"></path>
                                            </svg>
                                        </div>
                                        <div class="gear three">
                                            <svg id="yellow" viewbox="0 0 100 100" fill="#FFCD5C">
                                                <path d="M97.6,55.7V44.3l-13.6-2.9c-0.8-3.3-2.1-6.4-3.9-9.3l7.6-11.7l-8-8L67.9,20c-2.9-1.7-6-3.1-9.3-3.9L55.7,2.4H44.3l-2.9,13.6      c-3.3,0.8-6.4,2.1-9.3,3.9l-11.7-7.6l-8,8L20,32.1c-1.7,2.9-3.1,6-3.9,9.3L2.4,44.3v11.4l13.6,2.9c0.8,3.3,2.1,6.4,3.9,9.3      l-7.6,11.7l8,8L32.1,80c2.9,1.7,6,3.1,9.3,3.9l2.9,13.6h11.4l2.9-13.6c3.3-0.8,6.4-2.1,9.3-3.9l11.7,7.6l8-8L80,67.9      c1.7-2.9,3.1-6,3.9-9.3L97.6,55.7z M50,65.6c-8.7,0-15.6-7-15.6-15.6s7-15.6,15.6-15.6s15.6,7,15.6,15.6S58.7,65.6,50,65.6z"></path>
                                            </svg>
                                        </div>
                                        <div class="lil-circle"></div>
                                        <svg class="blur-circle">
                                            <filter id="blur">
                                                <fegaussianblur in="SourceGraphic" stddeviation="13"></fegaussianblur>
                                            </filter>
                                            <circle cx="70" cy="70" r="66" fill="transparent" stroke="white" stroke-width="40" filter="url(#blur)"></circle>
                                        </svg>
                                    </div>
                                    <div class="text">{{ trans('common.processing') }}...</div>
                                </div>
                                <div class="col-md-3"></div>
                            </div>
                            <div class="col-md-12 div-grid-products">
                                <div class="col-md-2"></div>
                                <div class="col-md-8">
                                    <ul id="gridproductLists" class="grid-product-lists">

                                    </ul>
                                </div>
                                <div class="col-md-2"></div>
                            </div>
                            <form id="frmDingTopup" class="form-horizontal">
                                <input type="hidden" name="SkuCode" id="SkuCode">
                                <input type="hidden" name="AccountNumber" id="33000000000">
                                <input type="hidden" name="SendValue" id="SendValue">
                                <input type="hidden" name="SendCurrencyIso" id="SendCurrencyIso">
                                <input type="hidden" name="_hid_commission_rate" id="_hid_commission_rate">
                                <input type="hidden" name="_hid_country" id="_hid_provider_country">
                                <input type="hidden" name="_hid_operator" id="_hid_provider_name">
                                <input type="hidden" name="_hid_euro_amount" id="_hid_euro_amount">
                                <input type="hidden" name="_hid_euro_amount_formatted" id="_hid_euro_amount_formatted">
                                <input type="hidden" name="_hid_dest_amount" id="_hid_dest_amount">
                                <input type="hidden" name="_hid_dest_amount_formatted" id="_hid_dest_amount_formatted">
                                <input type="hidden" name="UatNumber" id="UatNumber">
                                <input type="hidden" name="SendValueOriginal" id="SendValueOriginal">
                                <input type="hidden" name="ProviderCode" id="ProviderCode">
                                <div class="form-group hide" id="summaryDiv">
                                    <label class="control-label col-md-4">&nbsp;</label>
                                    <div class="col-md-2" style="margin-top: 50px">
                                        <a href="javascript:void(0);" id="btnSubmit"  class="btn btn-primary">Acheter</a>
                                    </div>
                                    <div class="col-md-6" style="margin-top: 50px">
                                        <button class="btn btn-primary" id="reviewOrderBtn" type="button"><i class="fa fa-search-plus"></i>&nbsp;Detail de la carte
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" class="hide"></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="flag" value="{{$select_flag}}">
    </div>
    <script src="{{ secure_asset('vendor/intel/js/intlTelInput-jquery.min.js?v=4') }}" type="text/javascript"></script>
    <script src="{{ secure_asset('vendor/intel/js/prism.js?v=4') }}" type="text/javascript"></script>
    <script>
        var api_base_url = "{{ secure_url('') }}";
        var will_be_received = "{{ trans('tamatopup.will_be_received') }}";
        var between_trans = "{{ trans('tamatopup.between') }}";
    </script>
    <script>
        $(document).ready(function () {
            //show hide with loader
            $("#provider_toupup").click(function () {
                scrollingElement = (document.scrollingElement || document.body)
                $(scrollingElement).animate({
                    scrollTop: document.body.scrollHeight
                }, 500);
                $("#loadingProducts").toggle();
                $("#show_form").show();
                $("#gridproductLists").addClass('hide');
                $("#summaryDiv").addClass('hide');
                $("#loadingProducts").toggle();
            });
            //review calling card
            $("#reviewOrderBtn").click(function () {
                var countryCode, phone_no, country, operator, euro_amount, euro_amount_formatted, dest_amount,dest_amount_formatted, query, sendAmount, sendCurrencyIso, commission_rate, skuCode, UatNumber,SendValueOriginal;
                countryCode = $("#_hid_provider_country").val();
                phone_no = $("#mobile").val();
                country = $("#_hid_provider_country").val();
                operator = $("#_hid_provider_name").val();
                euro_amount = $("#_hid_euro_amount").val();
                euro_amount_formatted = $("#_hid_euro_amount_formatted").val();
                dest_amount = $("#_hid_dest_amount").val();
                dest_amount_formatted = $("#_hid_dest_amount_formatted").val();
                sendAmount = $("#SendValue").val();
                sendCurrencyIso = $("#SendCurrencyIso").val();
                commission_rate = $("#_hid_commission_rate").val();
                skuCode = $("#SkuCode").val();
                UatNumber = $("#UatNumber").val();
                SendValueOriginal = $("#SendValueOriginal").val();
                query = "countryCode=" + countryCode + "&mobile=" + phone_no + "&provider_country=" + country + "&operator=" + operator + "&euro_amount=" + euro_amount + "&euro_amount_formatted=" + euro_amount_formatted + "&dest_amount=" + dest_amount + "&dest_amount_formatted=" + dest_amount_formatted + "&SendAmount=" + sendAmount + "&SendCurrencyIso=" + sendCurrencyIso + "&commissionRate=" + commission_rate + "&skuCode=" + skuCode + "&UatNumber=" + UatNumber + "&SendValueOriginal=" + SendValueOriginal;
                // console.log(query);
                if (countryCode != "" && phone_no != "" && country != "" && operator != "" && euro_amount != "" && euro_amount_formatted != "" && dest_amount != "" && dest_amount_formatted != "" && sendAmount != "" && sendCurrencyIso != "" && commission_rate != "" && skuCode != "" && UatNumber != "" && SendValueOriginal != "") {
                    AppModal("{{ secure_url('tama-topup-france/review?') }}" + query, "{{ trans('tamatopup.order_summary') }}");
                } else {
                    $.alert({
                        title: '{{ trans('common.info') }}',
                        content: "{{ trans('common.lbl_please_choose') }}"
                    });
                }
            });
            //submit form confirm calling card and print
            $("#btnSubmit").click(function () {
                $( "#loadergif" ).addClass( "loadergif loadergif-default is-active" );
                $.ajax({
                    url: '{{ secure_url('tama-topup-france/confirm') }}',
                    data: $('#frmDingTopup').serialize(),
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if(response.success == 4)
                        {
                            window.location.href = response.redirect;
                        }
                        else
                        {
                            $.alert({
                                title: '{{ trans('common.info') }}',
                                content: response.message
                            });
                            window.location.href = response.redirect;
                        }
                    },
                    error: function (data) {
                        $("#loadingProducts").toggle();
                        $.alert({
                            title: '{{ trans('common.info') }}',
                            content: response.message
                        });
                        window.location.href = response.redirect;
                    }
                });
            });
            $("#btnGetPlans").click(function () {
                $(this).html("<i class='fa fa-circle-notch fa-spin'></i>&nbsp;{{ trans('common.processing') }}...").attr('disabled', 'disabled');
                $("#frmTamaTopup").submit();
            });

            var telInput = $("#mobile"),
                errorMsg = $("#span_mobile");
            // initialise plugin
            telInput.intlTelInput({
                nationalMode: true,
                utilsScript: "{{ secure_asset('vendor/intel/js/utils.js?v=4') }}"
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
                        $("#countryIso").val(countryData.iso2);
                        $("#btnGetPlan").removeClass('disabled');
                        $("#btnGetPlans").removeClass('disabled');
                        $("#countryCode").val(countryData.dialCode);
                        errorMsg.addClass("hide");
                    } else {
                        telInput.parents(".form-group").addClass("has-error").removeClass("");
                        errorMsg.removeClass("hide");
                        $("#btnGetPlan").addClass('disabled');
                        $("#btnGetPlans").addClass('disabled');
                        $("#countryIso").val('');
                        $("#countryCode").val('');
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

        });
    </script>
    <script src="{{ secure_asset('js/topup.js') }}?v={{ rand(10,99) }}" type="text/javascript"></script>
@endsection