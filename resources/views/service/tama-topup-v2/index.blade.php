@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => 'TamaTopup V2','url'=> '','active' => 'yes']
    ]])
    <link href="{{ secure_asset('vendor/intl-input/css/intlTelInput.css') }}?v={{ filemtime(public_path('vendor/intl-input/css/intlTelInput.css')) }}" rel="stylesheet">
    <style>
        :root {
            --iti-path-flags-1x: url('{{ secure_asset('vendor/intl-input/img/flags.png') }}?v={{ filemtime(public_path('vendor/intl-input/img/flags.png')) }}');
            --iti-path-flags-2x: url('{{ secure_asset('vendor/intl-input/img/flags@2x.png') }}?v={{ filemtime(public_path('vendor/intl-input/img/flags@2x.png')) }}');
        }
    </style>
    <link href="{{ secure_asset('css/topup-v2.css') }}?v={{ filemtime(public_path('css/topup-v2.css')) }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        @include('service.tama-topup-v2.partial.header-links')
                        <div class="tama-v2">
                            @include('service.tama-topup-v2.partial.tabs')
                            @include('service.tama-topup-v2.partial.mobile-step')
                            @include('service.tama-topup-v2.partial.reloadly-mode')
                            @include('service.tama-topup-v2.partial.provider-section')
                            @include('service.tama-topup-v2.partial.transfer-type')
                            @include('service.tama-topup-v2.partial.product-section')
                            @include('service.tama-topup-v2.partial.range-section')
                            @include('service.tama-topup-v2.partial.summary-section')
                        </div>

                        @include('service.tama-topup-v2.partial.hidden-fields')

                        <div id="v2ReviewModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('common.btn_close') }}">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <h4 class="modal-title" id="v2ReviewModalLabel"></h4>
                                    </div>
                                    <div class="modal-body" id="v2ReviewModalBody"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ secure_asset('vendor/intl-input/js/intlTelInput.js') }}?v={{ filemtime(public_path('vendor/intl-input/js/intlTelInput.js')) }}" type="text/javascript"></script>
    <script>
        var api_base_url = "{{ secure_url('') }}";
        var will_be_received = "{{ trans('tamatopup.will_be_received') }}";
        var between_trans = "{{ trans('tamatopup.between') }}";
        var v2CloseLabel = "{{ strtolower(trans('common.btn_close')) }}";
        var v2ServiceNotAvailable = "{{ trans('common.service_not_avail') }}";
        var v2IntlUtilsVersion = "{{ filemtime(public_path('vendor/intl-input/js/utils.js')) }}";
        var v2BlockInspect = {{ app()->environment('production') ? 'true' : 'false' }};
    </script>
    <script src="{{ secure_asset('js/topup-v2.js') }}?v={{ filemtime(public_path('js/topup-v2.js')) }}"></script>
@endsection
