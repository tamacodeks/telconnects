@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
         ['name' => "Calling cards",'url'=> secure_url('calling-cards'),'active' => 'no'],
         ['name' => $page_title,'url'=> '','active' => 'yes']
    ]])
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row m-t-10">
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
                        <div class="row m-t-20">
                            <div class="col-md-1"></div>
                            <div class="col-md-10">
                                <div class="row">
                                    @foreach($cards->chunk(4) as $chunk)
                                        @foreach($chunk as $item)
                                            <?php
                                            $src_img = $item->getMedia('telecom_providers_cards')->first();
                                            $img = !empty($src_img)
                                                ? secure_asset(optional($src_img)->getUrl())
                                                : secure_asset('images/no_image.png');
                                            $decipher = new \app\Library\SecurityHelper();
                                            $enc_id = $decipher->encrypt($item->id);


                                            if($item->is_card == '1'){
                                                $link = 'mycallingcards/'.$enc_id;
                                            }else{
                                                $link = $cardlink.'/'.$enc_id;
                                            }
                                            ?>

                                            <div class="col-md-3">

                                                <a href="{{ secure_url($link) }}">
                                                    <div class="plans-panel">
                                                        <div class="container-img">
                                                            <img src="{{ $img  }}" alt="{{ $item->name }}"
                                                                 class="image-img"/>
                                                            <div class="middle-img">
                                                                <h4>{{ $item->name }} {{ \app\Library\AppHelper::formatAmount("EUR",$item->face_value) }}</h4>
                                                                <div class="text-img">
                                                                    <p>
                                                                        {{ $item->description }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{--<div class="info-card">--}}
                                                    {{--<div class="front">--}}
                                                    {{--<img src="{{ $img  }}" alt="{{ $item->name }}" class="img-responsive center-block card-image" />--}}
                                                    {{--</div>--}}
                                                    {{--<div class="back">--}}
                                                    {{--<div class="panel panel-cards">--}}
                                                    {{--<div class="panel-heading">--}}
                                                    {{--<h3 class="panel-title">{{ $item->name }} {{ \app\Library\AppHelper::formatAmount("EUR",$item->face_value) }}</h3>--}}
                                                    {{--</div>--}}
                                                    {{--<div class="panel-body">--}}
                                                    {{--{{ $item->description }}--}}
                                                    {{--</div>--}}
                                                    {{--</div>--}}
                                                    {{--</div>--}}
                                                    {{--</div>--}}
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
@endsection