@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "TamaTopup",'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/intl-input/css/intlTelInput.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('css/topup.css') }}" rel="stylesheet">
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
                            <div class="col-md-12 design-process-section" id="process-tab">
                                @include('service.tama-topup.tabs',['current' => 'choose'])
                            </div>
                        </div>
                        <div class="row m-b-20" id="spinnerRow">
                            <div class="col-md-2"></div>
                            <div class="col-md-8 m-b-20">
                                <form id="frmDingTopup" class="form-horizontal">
                                    <div class="form-group m-b-30">
                                        <label class="control-label col-md-4"
                                               for="operator">{{ trans('tamatopup.mobile') }}</label>
                                        <div class="col-md-8">
                                            <h4>+{{ $mobile_number }}
                                                <a href="{{ secure_url('tama-topup') }}"
                                                   style="font-size: 14px;margin-left: 20px;">{{ trans('tamatopup.change_number') }}</a></h4>
                                            {{--<div class="input-group">--}}
                                            <input class="form-control" id="mobile" name="mobile" type="hidden"
                                                   tabindex="1" autofocus="true" value="+{{ $mobile_number }}">
                                            {{--<span class="input-group-addon" id="btnGetProviders"><i--}}
                                            {{--class="fa fa-search-plus"></i></span>--}}
                                            {{--</div>--}}
                                            <span class="error help-block hide"
                                                  id="span_mobile">{{ trans('users.error_mobile_no') }}</span>
                                            <input type="hidden" id="countryCode" value="{{ $countryCode }}"
                                                   name="countryCode">
                                            <input type="hidden" id="countryIso" value="{{ $countryIso }}"
                                                   name="countryIso">
                                        </div>
                                    </div>

                                    <div class="form-group hide" id="detectAnime">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-6">
                                            <img src="{{ secure_asset('images/detectProviderSpinner.gif') }}"
                                                 class="center-block img-responsive">
                                            <br>
                                            <span class="center-block text-center">{{ trans('tamatopup.detecting_operator') }}...</span>
                                        </div>
                                        <div class="col-md-3"></div>
                                    </div>
                                    <div class="form-group hide" id="gridProviderDiv">
                                        <label class="control-label col-md-4"
                                               for="operator">{{ trans('tamatopup.select_operator') }}</label>
                                        <div class="col-md-8">
                                            <ul class="provider-list" id="gridProviderList">
                                            </ul>
                                            <input type="hidden" name="_hid_provider_name" id="_hid_provider_name">
                                            <input type="hidden" name="_hid_provider_country"
                                                   id="_hid_provider_country">
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
                                                        <fegaussianblur in="SourceGraphic"
                                                                        stddeviation="13"></fegaussianblur>
                                                    </filter>
                                                    <circle cx="70" cy="70" r="66" fill="transparent" stroke="white"
                                                            stroke-width="40" filter="url(#blur)"></circle>
                                                </svg>
                                            </div>
                                            <div class="text">{{ trans('common.processing') }}...</div>
                                        </div>
                                        <div class="col-md-3"></div>
                                    </div>
                                    <div class="form-group hide div-list-products" style="margin-top: 50px">
                                        <label class="control-label col-md-4"
                                               for="operator">{{ trans('tamatopup.select_amount') }}
                                        </label>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <input type="text" onkeyup="filterPriceList()" class="form-control"
                                                       placeholder="Search" id="productSearch"/>
                                                <div class="input-group-btn">
                                                    <button class="btn btn-default" type="button">
                                                        <span class="glyphicon glyphicon-search"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 div-list-products">
                                        <ul id="productLists" class="product-lists">
                                        </ul>
                                    </div>
                                    <div class="col-md-12 div-grid-products">
                                        <div class="col-md-4"></div>
                                        <div class="col-md-8">
                                            <ul id="gridproductLists" class="grid-product-lists">

                                            </ul>
                                        </div>
                                    </div>
                                    <input type="hidden" name="SkuId" id="SkuId">
                                    <input type="hidden" name="FaceValue" id="FaceValue">
                                    <input type="hidden" name="LocalCurrency" id="LocalCurrency">
                                    <input type="hidden" name="OriginalValue" id="OriginalValue">
                                    <input type="hidden" name="Countryi" id="Countryi">
                                    <div class="form-group hide" id="divRangeAmount">
                                        <label class="control-label col-md-4">{{ trans('tamatopup.enter_amount') }}
                                        </label>
                                        <div class="col-md-8">
                                            <div id="betweenDenomination"
                                                 style="margin-bottom: 5px;font-weight: 600;"></div>
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <img src="{{ secure_asset('images/coins-icon.png') }}"
                                                         style="width: 20px;opacity: .5;">
                                                </span>
                                                <input id="range_amount" type="text" class="form-control" min="1"
                                                       max="1" name="range_amount" placeholder="">
                                                <input type="hidden" name="SendValue" id="SendValue">
                                                <input type="hidden" name="SendValueOriginal"
                                                       id="SendValueOriginal">
                                                <input type="hidden" name="CurrencyIso" id="CurrencyIso">
                                                <input type="hidden" name="countryid" id="countryid">
                                                <input type="hidden" name="SkuCode" id="SkuCode">
                                                <input type="hidden" name="exchange_rate" id="exchange_rate">
                                                <input type="hidden" name="percentage" id="percentage">
                                                <input type="hidden" name="denomination" id="denomination">
                                                <input type="hidden" name="local_amount" id="local_amount">
                                            </div>
                                            <div style="margin-top: 5px;display: none" id="amountReceivedDiv"><span
                                                        id="amountReceived"
                                                        style="font-size: 16px;font-weight: 600;"></span> {{ trans('tamatopup.will_be_received') }} <span id="ifTaxApplicable"
                                                                                                                                                          style="display: none;">Inc. <span
                                                            id="taxName"></span></span></div>
                                        </div>
                                    </div>
                                    <div class="form-group hide" id="summaryDiv">
                                        <label class="control-label col-md-4">&nbsp;</label>
                                        <div class="col-md-8" style="margin-top: 50px">
                                            <button class="btn btn-primary" id="reviewOrderBtn" type="button"><i class="fa fa-check-circle"></i>&nbsp;{{ trans("tamatopup.review_order_summary") }}
                                            </button>
                                        </div>
                                    </div>
                                    <button type="submit" class="hide"></button>
                                </form>
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ secure_asset('vendor/intl-input/js/intlTelInput.js') }}" type="text/javascript"></script>
    <script>
        var api_base_url = "{{ secure_url('') }}";
        var will_be_received = "{{ trans('tamatopup.will_be_received') }}";
        var between_trans = "{{ trans('tamatopup.between') }}";
    </script>
    <script>
        $(document).ready(function () {
            setTimeout(function () {
                // $("#mobile").focus();
                $("#frmDingTopup").submit();
            },1500)

            var validator = $( "#frmDingTopup" ).validate({
                rules: {
                    range_amount: {
                        required: true,
                        number: true,
                        max: function() {
                            return parseFloat($("#range_amount").attr('max'));
                        },
                        min: function() {
                            return parseFloat($("#range_amount").attr('min'));
                        },
                    }
                },
                highlight: function (element) {
                    var id_attr = "#" + $(element).attr("id") + "1";
                    if($(element).is(":focus")) $(element).closest('.input-group').addClass('has-error');
                    $("#reviewOrderBtn").attr('disabled','disabled');
                    $("#amountReceivedDiv").hide();
                    $("#amountReceived").html('');
                },
                unhighlight: function (element) {
                    var id_attr = "#" + $(element).attr("id") + "1";
                    $(element).closest('.input-group').removeClass('has-error');
                    $("#reviewOrderBtn").removeAttr('disabled');
                    $("#amountReceivedDiv").hide();
                    $("#amountReceived").html('');
                },

                errorPlacement: function (error, element) {
                    if($(element).is(":focus")) error.insertAfter(element.parent());
                }
            });
            $('#range_amount').keypress(function(event) {
                if(event.which < 46
                    || event.which > 59) {
                    event.preventDefault();
                } // prevent if not number/dot

                if(event.which == 46
                    && $(this).val().indexOf('.') != -1) {
                    event.preventDefault();
                } // prevent if already dot
            });

            $("#frmDingTopup").submit(function (e) {
                console.log('submitted');
                e.preventDefault();
                // validator.resetForm();
                toggleAnime('show');
                toggleGridProviders('hide');
                var mobileNumber, countryCode, countryIso;
                mobileNumber = $("#mobile").val();
                countryCode = $("#countryCode").val();
                countryIso = $("#countryIso").val();
                getProvidersprepay(api_base_url+"/tama-topup/fetchprepay/providers?accountNumber="+mobileNumber+"&countryCode="+countryCode+"&countryIsos="+countryIso);
            });
            $("#btnGetProviders").click(function () {
                toggleAnime('show');
                toggleGridProviders('hide');
                var mobileNumber, countryCode, countryIso;
                mobileNumber = $("#mobile").val();
                countryCode = $("#countryCode").val();
                countryIso = $("#countryIso").val();
                getProvidersprepay(api_base_url+"/tama-topup/fetchprepay/providers?accountNumber="+mobileNumber+"&countryCode="+countryCode+"&countryIsos="+countryIso);
            });

            $("#range_amount").blur(function () {
                // validator.resetForm();
                var min, max, currentVal;
                min = parseFloat($(this).attr('min'));
                max = parseFloat($(this).attr('max'));
                currentVal = parseFloat($(this).val());
                console.log(currentVal);
                if($(this).val() != "" && currentVal >= min && currentVal <= max){
                    $("#reviewOrderBtn").attr('disabled', 'disabled');
                    $(this).addClass('loading');
                    var exchange_rate = $("#exchange_rate").val();
                    var percentage = $("#percentage").val();
                    var range_amount = $("#range_amount").val();
                    var CurrencyIso = $("#CurrencyIso").val();
                    var per = (range_amount/100)*percentage;
                    var actual_amount = (range_amount - per).toFixed(2);;
                    var local = (actual_amount * exchange_rate).toFixed(2);
                    $("#SendValue").val(range_amount);
                    $("#SendValueOriginal").val(actual_amount);
                    $("#denomination").val("denomination");
                    $("#amountReceivedDiv").show();
                    $("#amountReceived").html(" "+CurrencyIso+" "+local);
                    $("#local_amount").val(local);
                    $("#ifTaxApplicable").show();
                    setTimeout(function () {
                        $("#range_amount").removeClass('loading');
                        $("#reviewOrderBtn").removeAttr('disabled');
                    },1000);
                }
            });


            $("#reviewOrderBtn").click(function () {
                var phone_no, sendAmount, skuCode , country, operator, denomination;
                denomination = $("#denomination").val();
                sendAmount = $("#FaceValue").val();
                sendValueOriginal = $("#OriginalValue").val();
                local_currency = $("#LocalCurrency").val();
                skuCode = $("#SkuId").val();
                country = $("#Countryi").val();
                phone_no = $("#mobile").val();
                operator = $("#_hid_provider_name").val();
                countryCode = $("#countryCode").val();
                if(denomination === 'denomination')
                {
                    sendAmount = $("#SendValue").val();
                    sendValueOriginal = $("#SendValueOriginal").val();
                    skuCode = $("#SkuCode").val();
                    local_currency = $("#local_amount").val();
                    country = $("#countryid").val();
                }

                query = "&mobile=" + phone_no + "&SendAmount=" + sendAmount +  "&sendValueOriginal=" + sendValueOriginal +  "&skuCode=" + skuCode +  "&country=" + country +  "&operator=" + operator  +  "&local_currency=" + local_currency+  "&countryCode=" + countryCode;
                console.log(query);
                if (phone_no != "" && sendAmount != "" && skuCode != "") {
                    AppModal("{{ secure_url('tama-topup/prepay-review?') }}" + query, "{{ trans('tamatopup.order_summary') }}");
                } else {
                    $.alert({
                        title: '{{ trans('common.info') }}',
                        content: "{{ trans('common.lbl_please_choose') }}"
                    });
                }
            });
        });
    </script>
    <script src="{{ secure_asset('js/topup.js') }}" type="text/javascript"></script>
@endsection