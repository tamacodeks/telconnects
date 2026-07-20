@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "TamaTopup",'url'=> '','active' => 'yes']
    ]
    ])
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
                                                    <div class="huge-next">Carte recharge</div>
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
                                            <div class="col-xs-3">
                                                <i class="fa fa-list-ol fa-4x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <div class="huge">{{ isset($total_orders) ? $total_orders : 0 }}</div>
                                                <div class="huge-next">{{ trans('common.dashboard_total_orders') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ secure_url('orders') }}" class="a-footer">
                                        <div class="panel-footer dashboard-panel-footer">
                                            <span class="pull-left">{{ trans('common.view_all') }}</span>
                                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                            <div class="clearfix"></div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <div class="col-xs-3">
                                                <i class="fa fa-calendar-check fa-4x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <div class="huge">{{ isset($today_transaction) ? $today_transaction : 0 }}</div>
                                                <div class="huge-next">{{ trans('common.dashboard_total_today_trans') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ secure_url('transactions?from='.date('Y-m-d')."&to=".date("Y-m-d")) }}"
                                       class="a-footer">
                                        <div class="panel-footer dashboard-panel-footer">
                                            <span class="pull-left">{{ trans('common.view_all') }}</span>
                                            <span class="pull-right"><i
                                                        class="fa fa-arrow-circle-right"></i></span>
                                            <div class="clearfix"></div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-3">
                                <div class="wrapper-md">
                                    <div class="text-center">
                                        <button onclick="print_pin()" class="btn btn-primary"><i class="fa fa-print"></i>&nbsp;{{ trans('common.btn_print') }}</button>
                                    </div>
                                    <div id="tama-topup-receipt" class="tama-topup-receipt">
                                        <div id="receipt-data">
                                            <div>
                                                <div style="text-align:center;">
                                                    <img src="{{ secure_asset('images/logo.png') }}" style="width: 200px;height: auto;margin-bottom: 10px;margin-top: 10px;margin-left: -40px;">
                                                    <p style="text-align:center;"><strong>{{ APP_NAME }}</strong></div>
                                                <p style="font-family: monospace;">
                                                    {{ trans('common.lbl_date') }}: {{ $order->date }} <br>
                                                    {{ trans('service.tama_lbl_order_id') }}: {{ $order->id }}<br>
                                                </p>
                                                <div style="clear:both;"></div>
                                                <table style="font-size: 12.5px;font-family: monospace;" class="table  table-condensed">
                                                    <tbody>
                                                    @if($order->tt_mobile)
                                                        <tr>
                                                            <th style="width: 450px;text-align: left;">{{ trans('sale.mobile_number') }}</th>
                                                            <th style="text-align: left">{{ $order->tt_mobile }}</th>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <th style="width: 450px;text-align: left;">{{ trans('sale.pin') }}</th>
                                                            <th style="text-align: left">{{ $order->tama_pin }}</th>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <th style="width: 450px;text-align: left;">{{ trans('sale.topup_operator') }}</th>
                                                        <th style="text-align: left">{{ $order->tt_operator }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th style="width: 450px;text-align: left;">{{ trans('sale.order_amount_euro') }}</th>
                                                        <th style="text-align: left">{{ \app\Library\AppHelper::formatAmount("EUR",$order->tt_euro_amount)  }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th style="width: 450px; text-align: left;">
                                                            {{ trans('service.tamatopup_frm_dest_cur') }}
                                                        </th>

                                                        @if(!empty($order->tt_operator) && stripos($order->tt_operator, 'data') !== false)
                                                            <th style="text-align: left;">
                                                                {{ $order->tt_dest_currency }}
                                                            </th>
                                                        @else
                                                            <th style="text-align: left;">
                                                                {{ $order->tt_dest_amount }}&nbsp;{{ $order->tt_dest_currency }}
                                                            </th>
                                                        @endif
                                                    </tr>
                                                    <tr>
                                                        <th style="width: 450px;text-align: left;">Operator ID</th>
                                                        <th style="text-align: left">{{ $order->transfer_ref }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th style="width: 450px;text-align: left;">{{ trans('service.ms_status') }}</th>
                                                        <th style="text-align: left">{{ $order->status }}</th>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <div class="well well-sm" style="margin-top:10px;">
                                                    <div style="text-align: center;
    font-family: monospace;">Merci d'avoir utilisé {{ APP_NAME }}</div>
                                                </div>
                                            </div>
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function print_pin() {
            var contents = $("#tama-topup-receipt").html();
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

        });
    </script>
@endsection