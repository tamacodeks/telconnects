@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
         ['name' => "Calling card",'url'=> '','active' => 'yes']
    ]])
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
                            <div class="col-md-1"></div>
                            <div class="col-md-10">
                                <div class="row">
                                    @foreach($telecom_providers->chunk(4) as $chunk)
                                        @foreach($chunk as $item)
                                            <?php
                                            $tp_config =  \App\Models\TelecomProviderConfig::find($item->id);
                                            $src_img = $tp_config->getMedia('telecom_providers')->first();
                                            $img = !empty($src_img) ? secure_asset(optional($src_img)->getUrl('thumb')) : secure_asset('images/no_image.png');
                                            $decipher = new \app\Library\SecurityHelper();
                                            $enc_id = $decipher->encrypt($item->id);
                                            ?>
                                            <div class="col-md-2">
                                                <a href="{{ secure_url('calling-cards/'.$enc_id) }}" >
                                                    <div class="panel panel-default cc-panel">
                                                        <div class="panel-body">
                                                            <img src="{{ $img  }}" alt="{{ $item->name }}" class="img-responsive center-block" />
                                                        </div>
                                                        {{--<div class="panel-footer">--}}
                                                        {{--<a href="{{ secure_url('calling-cards/'.$enc_id) }}" style="text-decoration: none;color: #000;font-size: 18px">--}}
                                                        {{--{{ $item->name }}</a>--}}
                                                        {{--</div>--}}
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
        if(isFirefox){
            setTimeout(function(){
                localStorage.removeItem('on_load_counter');
                localStorage.removeItem('print_card');
            }, 2000);
        }
    </script>
@endsection