@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "TamaTopup",'url'=> secure_url('tama-topup'),'active' => 'no'],
        ['name' => $page_title,'url'=> '','active' => 'yes'],
    ]
    ])
    <style>
        .pselected {
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#4dff6600', endColorstr='#4dff6600', GradientType=0);
            background: #00427f;
            color: #FFf;
        }
        .pselected >.box >.card-block > .card-text > h4.ret_user{
            color: #fff;
        }
    </style>
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
                            <div class="col-md-12">
                                @include('service.tama-topup.tabs',['current' => 'choose'])
                            </div>
                            <div class="col-md-2"></div>
                            <div class="col-md-8 m-b-20">
                                <div class="row">
                                    <div class="col-md-12 col-xs-12">
                                        <div class="media">
                                            <a href="#" class="pull-left">
                                                <img src="https://fm.transfer-to.com/logo_operator/logo-{{ $plan['operator_id'] }}-1.png" class="media-object" alt="{{ $plan['operator'] }}">
                                            </a>
                                            <div class="media-body">
                                                <h1 class="media-heading">{{ $plan['operator']  }}</h1>
                                                <h4>{{ $plan['country'] }} - ({{ $plan['mobile_number'] }})
                                                    <a href="{{ secure_url('tama-topup') }}" style="font-size: 14px;margin-left: 20px;">Change number?</a></h4>
                                            </div>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                        $euro = $plan['retail_price_list'];
                                        $local_price = $plan['product_list'];
                                        $products = collect(array_combine($euro, $local_price));
                                        //                                                    dd($products->chunk(4));
                                        ?>
                                        <div class="row m-t-20">
                                            @foreach ($products->chunk(4) as $chunk)
                                                @foreach ($chunk as $euro=>$dest)
                                                    <div class="col-md-3  m-t-10">
                                                        <a style="text-decoration: none;"
                                                           href="javascript:void(0)" class="choose_plan">
                                                            <input class="euro_cur" type="hidden"
                                                                   value="{{ $euro }}">
                                                            <input class="dest_cur" type="hidden"
                                                                   value="{{ $dest }}">
                                                            <div class="card tmp-card plans-card">
                                                                <div class="box">
                                                                    <div class="card-block">
                                                                        <h5 class="card-title">
                                                                            {{ \app\Library\AppHelper::formatAmount('EUR',$euro) }}
                                                                        </h5>
                                                                        <div class="card-text">
                                                                            <h4 class="ret_user">{{ $dest.' '.$plan['destination_currency'] }}</h4>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            @endforeach
                                            <div class="col-md-12 m-t-20">
                                                <div class="row">
                                                    <div class="col-md-4"></div>
                                                    <div class="col-md-4"></div>
                                                    <div class="col-md-4 hide" id="selectgo">
                                                        <form class="form-horizontal" method="post"
                                                              id="frmTopup"
                                                              action="{{ secure_url('tama-topup/confirm/topup') }}">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" name="country_code"
                                                                   value="{{ isset($country_code) ? $country_code : "" }}">
                                                            <input type="hidden" name="mobile_number"
                                                                   value="{{ $plan['mobile_number'] }}">
                                                            <input type="hidden" name="mobile_operator"
                                                                   value="{{ $plan['operator'] }}">
                                                            <input type="hidden" name="euro_amount" id="euro_amount" value="">
                                                            <input type="hidden" name="local_amount" id="local_amount" value="">
                                                            <input type="hidden" name="dest_currency" id="dest_currency" value="{{ $plan['destination_currency'] }}">
                                                            <input type="hidden" name="country_name" id="country_name" value="{{ $plan['country'] }}">
                                                            <div class="pull-right">
                                                                <button type="submit" id="submitBtn"
                                                                        class="btn btn-theme" href="#">
                                                                    {{ trans('service.tamatopup_btn_confirm_topup') }}
                                                                    &nbsp;<span
                                                                            class="fa fa-chevron-circle-right"></span>
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="myModal" tabindex="-1" data-backdrop="static"  role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">{{ trans('service.tamatopup_confirm_topup_modal_title') }}</h5>
                    <button type="button" class="close closeModal" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <td>{{ trans('service.tamatopup_lbl_mn') }}</td>
                            <td>{{ $plan['mobile_number'] }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('service.tamatopup_lbl_mo') }}</td>
                            <td>{{ $plan['operator'] }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('service.tamatopup_lbl_ea') }}</td>
                            <td id="eamount">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ trans('service.tamatopup_lbl_la') }}</td>
                            <td id="lamount">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <label class="hide">
                        <input class="minimal-red" type="checkbox" id="cust_sms" name="d_cust">&nbsp;{{ trans('service.tamatopup_confirm_send_sms') }}</label>

                    <div class="hide" id="myHideDiv">
                        <form class="form-horizontal">
                            <div class="card-block chat" id="chat-box">
                                <div class="form-group row">
                                    <label for="inputMobile" class="col-sm-4 control-label">{{ trans('service.tamatopup_lbl_sn') }}</label>

                                    <div class="col-sm-8">
                                        <input type="tel" name="senderTmpNumber" id="senderTmpNumber"
                                               class="form-control masked_input"
                                               placeholder="Sender number with country code"
                                               value="">
                                        <span class="help-block hide" id="span_mobile_no">{{ trans('users.error_mobile_no') }}</span>
                                        <p class="help-block">{{ trans('service.tamatopup_lbl_opt_smsr') }}</p>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputMobile" class="col-sm-4 control-label">{{ trans('service.tamatopup_lbl_opt_sms') }}</label>

                                    <div class="col-sm-8">
                                        <textarea class="form-control" id="senderTmpMsg" placeholder="Enter your message..."></textarea>
                                        <p class="help-block">{{ trans('service.tamatopup_lbl_opt_sms_max_count') }}</p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default closeModal" id="closeModal">{{ trans('common.btn_close') }}</button>
                    <button type="button" id="btnXchange" class="btn btn-theme">{{ trans('service.tamatopup_btn_topup') }}</button>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ secure_asset('vendor/intl-input/js/intlTelInput.js') }}" type="text/javascript"></script>
    <script src="{{ secure_asset('vendor/common/loadingoverlay.min.js') }}" type="text/javascript"></script>
    <script>
        $(document).ready(function () {

            $(".closeModal").click(function () {
                $("#myModal").modal("hide");
                $("#btnXchange").html('{{ trans('service.tamatopup_btn_confirm_topup') }}');
                $("#submitBtn").html('{{ trans('service.tamatopup_btn_confirm_topup') }} <span class="fa fa-chevron-circle-right"></span>');
            });
            //submit order
            $("#submitBtn").click(function (event) {
                $(this).html('<i class="fa fa-spinner fa-pulse"></i> {{ trans('common.processing') }}...');
                event.preventDefault();
                $("#myModal").modal("show");
            });

            $(".choose_plan").click(function (e) {
                e.preventDefault();
                $(".choose_plan").find('div.tmp-card.pselected').removeClass('pselected');
                $(this).find('div.tmp-card').addClass('pselected');
                var nval = $(this).find('h4.ret_user').html();
                var lval = $(this).find('input.dest_cur').val();
                var eval = $(this).find('input.euro_cur').val();
//                console.log('nval',nval);
//                console.log('lval',lval);
//                console.log('eval',eval);
                $("#local_amount").val(lval);
                $("#euro_amount").val(eval);
                $("#eamount").html(nval);
                $("#lamount").html($("#local_amount").val() + " " + $("#dest_currency").val());
//            console.log('selected value=> ',nval);
                $("#up-value").val(nval);
                //proceed to payment transaction
                $("#selectgo").removeClass('hide').show('100');
                var target = $("#selectgo");
                if (target.length) {
                    e.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top
                    }, 2000);
                }
            });

            $("#btnXchange").click(function () {
                $(this).html('<i class="fa fa-spinner fa-pulse"></i> {{ trans('common.processing') }}...');
                $("#frmTopup").submit();
                $.LoadingOverlay("show");
            });

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
            // on blur: validate
            telInput.blur(function () {
                reset();
                if ($.trim(telInput.val())) {
                    if (telInput.intlTelInput("isValidNumber")) {
                        telInput.parents(".form-group").addClass("").removeClass("has-error");
                        var intlNumber = telInput.intlTelInput("getNumber");
                        var countryData = telInput.intlTelInput("getSelectedCountryData");
                        var countryCode = countryData.dialCode;
                        countryCode = "+" + countryCode;
                        var newNo = intlNumber.replace(countryCode, "(" + countryCode + ")");
                        telInput.val(newNo);
                        $("#btnSubmit").removeAttr('disabled');
                        $("#areaCode").val(countryData.dialCode);
                    } else {
                        telInput.parents(".form-group").addClass("has-error").removeClass("");
                        errorMsg.removeClass("hide");
                        $("#btnSubmit").attr('disabled', 'disabled');
                    }
                }
            });
            telInput.on('change keyup paste input focus', function (e) {
                var code = (e.keyCode || e.which);
                // skip arrow keys
                if (code == 37 || code == 38 || code == 39 || code == 40 || code == 8) {
                    return;
                }

                var intlNumber = telInput.intlTelInput("getNumber");
                var countryData = telInput.intlTelInput("getSelectedCountryData");
                var countryCode = countryData.dialCode;
                countryCode = "+" + countryCode;
                var newNo = intlNumber.replace("(" + countryCode + ")", countryCode);
                telInput.val(newNo);
                // if first character is 0 filter it off
                var num = $(this).val();
                if (num.length == '') {
                    $(this).val('+');
                }
            });
            // trigger a fake "change" event now, to trigger an initial sync
            telInput.change();
        });
    </script>
@endsection