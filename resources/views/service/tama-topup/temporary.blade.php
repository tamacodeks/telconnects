@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "TamaTopup",'url'=> secure_url('tama-topup'),'active' => 'no'],
        [
            'name' => "Denominations $mobile_number",'url'=> '','active' => 'yes'
        ]
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
                                <div class="form-group m-b-30">
                                    <label class="control-label col-md-4"
                                           for="operator">{{ trans('tamatopup.mobile') }}</label>
                                    <div class="col-md-8">
                                        <h4>+{{ $mobile_number }}
                                            <a href="{{ secure_url('tama-topup') }}"
                                               style="font-size: 14px;margin-left: 20px;">{{ trans('tamatopup.change_number') }}</a></h4>
                                    </div>
                                </div>

                                <div class="col-md-12 div-list-products">
                                    <ul id="productLists" class="product-lists">
                                        <li class="denomination">
                                            <a href="javascript:void(0);" >
                                                <div class="panel panel-default panel-data activatable-item">
                                                    <div class="data">
                                                        <div>
                                                            <h2>{{ trans('common.service_not_avail') }}</h2>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <!-- /ko -->
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection