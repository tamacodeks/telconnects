@extends('v2.layout.simple.master')

@section('style')
    <link href="{{ secure_asset('vendor/intl-input/css/intlTelInput.css') }}?v={{ filemtime(public_path('vendor/intl-input/css/intlTelInput.css')) }}" rel="stylesheet">
    <link href="{{ secure_asset('css/topup-v2.css') }}?v={{ filemtime(public_path('css/topup-v2.css')) }}" rel="stylesheet">
@endsection

@section('content')
    @include('v2.layout.simple.breadcrumb',['data' => [
        ['name' => 'TamaTopup V2','url'=> secure_url('tama-topup-v2'),'active' => false],
        ['name' => trans('topup_v2.btn_print'),'url'=> '','active' => 'yes']
    ]])
    <div class="container-fluid tama-v2-receipt-page">
        @include('service.tama-topup-v2.partial.header-links')
        <div class="tama-v2-receipt-actions">
            <button onclick="print_pin()" class="btn btn-primary"><i class="fa fa-print"></i>&nbsp;{{ trans('topup_v2.btn_print') }}</button>
        </div>
        <div class="tama-v2-receipt-wrap">
            <div id="tama-topup-receipt" class="tama-v2-receipt-card">
                <div id="receipt-data">
                    <div class="tama-v2-receipt-header">
                        <img src="{{ secure_asset('images/logo.png') }}" class="tama-v2-receipt-logo" alt="{{ APP_NAME }}">
                        <div class="tama-v2-receipt-title">{{ APP_NAME }}</div>
                        <div class="tama-v2-receipt-meta">
                            <span>{{ trans('topup_v2.lbl_date') }}: {{ $order->date }}</span>
                            <span>{{ trans('topup_v2.order_id') }}: {{ $order->id }}</span>
                        </div>
                    </div>
                    <table class="table tama-v2-receipt-table">
                        <tbody>
                        @if($order->tt_mobile)
                            <tr>
                                <th>{{ trans('topup_v2.mobile_number') }}</th>
                                <td>{{ $order->tt_mobile }}</td>
                            </tr>
                        @else
                            <tr>
                                <th>{{ trans('topup_v2.pin') }}</th>
                                <td>{{ $order->tama_pin }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>{{ trans('topup_v2.topup_operator') }}</th>
                            <td>{{ $order->tt_operator }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('topup_v2.order_amount_euro') }}</th>
                            <td>{{ \app\Library\AppHelper::formatAmount('EUR',$order->tt_euro_amount) }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('topup_v2.dest_currency') }}</th>
                            @if(!empty($order->tt_operator) && stripos($order->tt_operator, 'data') !== false)
                                <td>{{ $order->tt_dest_currency }}</td>
                            @else
                                <td>{{ $order->tt_dest_amount }} {{ $order->tt_dest_currency }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>Operator ID</th>
                            <td>{{ $order->transfer_ref }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('topup_v2.status') }}</th>
                            <td>{{ $order->status }}</td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="tama-v2-receipt-footer">
                        <span>Thank you for using {{ APP_NAME }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function print_pin() {
            var contents = $("#tama-topup-receipt").html();
            var frame1 = $('<iframe />');
            frame1[0].name = "frame1";
            frame1.css({ "position": "absolute", "top": "-1000000px" });
            $("body").append(frame1);
            var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
            frameDoc.document.open();
            frameDoc.document.write('<html><head><title>{{ $page_title }}</title>');
            frameDoc.document.write('<link rel="stylesheet" href="{{ secure_asset('css/topup-v2.css') }}">');
            frameDoc.document.write('<style>body.tama-v2-receipt-print{font-family: "Barlow", "Segoe UI", Arial, sans-serif; padding:18px;} .tama-v2-receipt-card{margin:0 auto;}</style>');
            frameDoc.document.write('</head><body class="tama-v2-receipt-print">');
            frameDoc.document.write(contents);
            frameDoc.document.write('</body></html>');
            frameDoc.document.close();
            setTimeout(function () {
                window.frames["frame1"].focus();
                window.frames["frame1"].print();
                frame1.remove();
            }, 500);
        }
    </script>
@endsection
