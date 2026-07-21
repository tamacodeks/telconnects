@extends('v2.layout.simple.master')

@section('style')
    <link href="{{ asset('vendor/intl-input/css/intlTelInput.css') }}?v={{ @filemtime(public_path('vendor/intl-input/css/intlTelInput.css')) ?: time() }}" rel="stylesheet">
    <style>
        :root {
            --iti-path-flags-1x: url('{{ asset('vendor/intl-input/img/flags.png') }}?v={{ @filemtime(public_path('vendor/intl-input/img/flags.png')) ?: time() }}');
            --iti-path-flags-2x: url('{{ asset('vendor/intl-input/img/flags@2x.png') }}?v={{ @filemtime(public_path('vendor/intl-input/img/flags@2x.png')) ?: time() }}');
        }
    </style>
    <link href="{{ asset('css/topup-v2.css') }}?v={{ @filemtime(public_path('css/topup-v2.css')) ?: time() }}" rel="stylesheet">
@endsection

@include('v2.layout.simple.breadcrumb', ['data' => [
    ['name' => 'TamaTopup V2', 'url' => '', 'active' => 'yes']
]])

@section('content')
    <div class="container-fluid">
        <div class="tama-v2-page">
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

            <div id="v2ReviewModal" class="modal fade" tabindex="-1" aria-labelledby="v2ReviewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="v2ReviewModalLabel"></h4>
                            <button type="button" class="tama-v2-modal-close" data-bs-dismiss="modal" aria-label="{{ trans('common.btn_close') }}">
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="modal-body" id="v2ReviewModalBody"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('vendor/common/loadingoverlay.min.js') }}?v={{ @filemtime(public_path('vendor/common/loadingoverlay.min.js')) ?: time() }}" type="text/javascript"></script>
    <script src="{{ asset('vendor/intl-input/js/intlTelInput.js') }}?v={{ @filemtime(public_path('vendor/intl-input/js/intlTelInput.js')) ?: time() }}" type="text/javascript"></script>
    <script>
        var api_base_url = "{{ url('') }}";
        var will_be_received = "{{ trans('tamatopup.will_be_received') }}";
        var between_trans = "{{ trans('tamatopup.between') }}";
        var v2CloseLabel = "{{ strtolower(trans('common.btn_close')) }}";
        var v2ServiceNotAvailable = "{{ trans('common.service_not_avail') }}";
        var v2IntlUtilsVersion = "{{ @filemtime(public_path('vendor/intl-input/js/utils.js')) ?: time() }}";
        var v2BlockInspect = {{ app()->environment('production') ? 'true' : 'false' }};
    </script>
    <script src="{{ asset('js/topup-v2.js') }}?v={{ @filemtime(public_path('js/topup-v2.js')) ?: time() }}"></script>
@endsection
