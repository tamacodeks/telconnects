@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "TamaTopup",'url'=> '','active' => 'yes']
    ]
    ])
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
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
                                                    <img src="{{ secure_asset('images/logo.png') }}">
                                                    <p style="text-align:center;"><strong>{{ APP_NAME }}</strong></div>
                                                <p style="font-family: monospace;">
                                                    {{ trans('common.lbl_date') }}: {{ $order->date }} <br>
                                                    {{ trans('service.tama_lbl_order_id') }}: {{ $order->id }}<br>
                                                </p>
                                                <div style="clear:both;"></div>
                                                <table style="font-size: 12.5px;font-family: monospace;" class="table  table-condensed">
                                                    <tbody>
                                                    <tr>
                                                        <th style="width: 450px;text-align: left;">{{ trans('service.tamaapp_app_id') }}</th>
                                                        <th style="text-align: left">{{ $order->app_mobile }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th style="width: 450px;text-align: left;">{{ trans('common.transaction_tbl_trans_id') }}</th>
                                                        <th style="text-align: left">{{ $order->txn_ref }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th style="width: 450px;text-align: left;">{{ trans('sale.order_amount_euro') }}</th>
                                                        <th style="text-align: left">{{ \app\Library\AppHelper::formatAmount("EUR",$order->public_price)  }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th style="width: 450px;text-align: left;">{{ trans('sale.amount_topup') }}</th>
                                                        <th style="text-align: left">{{ $order->app_amount_topup }}&nbsp;{{ $order->app_currency }}</th>
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