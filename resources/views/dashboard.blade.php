@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => []])
    <style>
        .huge1 {
            font-weight: 800;
        }
        .carousel-caption {
            position: absolute;
            top:-30px;
            text-align: left;
        }
        .item-image {
            opacity: 0.8;
        }
        .family-logo{
            height: 58px;
        }
        .flix-bus-logo {
            height: 58px;
            width: 200px;
        }
        .france-logo{
            height: 99px;
            width: 212px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        @if(auth()->user()->group_id != 1)
                            <div class="col-md-6">
                                <div id="myCarousel" class="carousel slide" data-ride="carousel">
                                    <div class="carousel-inner">
                                        @forelse($banner as $key=>$banners)
                                            <div class="item @if($key == '0') active @endif">
                                                <img class="item-image" src="{{ secure_asset('images/'.$banners['banner']) }}" style="width:100%;">
                                                <div class="carousel-caption right-caption text-right">
                                                    <h3>{{ $banners['title'] }}</h3>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="item active">
                                                <img src="{{ secure_asset('images/banner/banner_default_image.png') }}" style="width:100%;">
                                            </div>
                                        @endforelse
                                    </div>

                                    <!-- Left and right controls -->
                                    <a class="left carousel-control" href="#myCarousel" data-slide="prev">
                                        <span class="glyphicon glyphicon-chevron-left"></span>
                                        <span class="sr-only">Previous</span>
                                    </a>
                                    <a class="right carousel-control" href="#myCarousel" data-slide="next">
                                        <span class="glyphicon glyphicon-chevron-right"></span>
                                        <span class="sr-only">Next</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                        @if(auth()->user()->group_id != 1)
                            <div class="row">
                                @if(in_array(auth()->user()->group_id,[2,3]))
                                    <div class="col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <div class="row">
                                                    <div class="col-xs-3">
                                                        <i class="fa fa-users fa-4x text-warning"></i>
                                                    </div>
                                                    <div class="col-xs-9 text-right">
                                                        <div class="huge">{{ isset($total_resellers) ? $total_resellers : 0 }}</div>
                                                        <div class="huge-next">{{ trans('common.dashboard_total_resellers') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="{{ secure_url('users') }}" class="a-footer">
                                                <div class="panel-footer dashboard-panel-footer">
                                                    <span class="pull-left">{{ trans('common.view_all') }}</span>
                                                    <span class="pull-right"><i
                                                                class="fa fa-arrow-circle-right"></i></span>
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
                                                        <i class="fa fa-list-ol fa-4x text-success"></i>
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
                                @endif

                                @if(in_array(auth()->user()->group_id,[4]))
                                    <div class="col-md-3">
                                        <a href="{{ secure_url('tama-topup') }}" style="color:#000;">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <div class="row">
                                                        <div class="col-xs-3">
                                                            <i class="fa fa-mobile-alt fa-4x text-warning"></i>
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
                                                            <i class="fa fa-credit-card fa-4x text-info"></i>
                                                        </div>
                                                        <div class="col-xs-9 text-right">
                                                            <div class="huge">&nbsp;</div>
                                                            <div class="huge-next">Carte recharge</div>
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
                                    <div class="col-md-3" style="display:none;">
                                        <a href="{{ secure_url('tama-topup-france') }}" style="color:#000;">
                                            <div class="panel panel-default">
                                                <div class="panel-heading" >
                                                    <div class="row">
                                                        <div class="col-xs-12 ">
                                                            <a href="{{ secure_url('tama-topup-france') }}" style="color:#000;">
                                                                <img class="france-logo" src="{{ secure_asset('images/calling_card.jpeg') }}">
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </a>
                                    </div>
                                    @if(\App\Models\Service::where('id', 6)->where('status', 1)->first())
                                        <div class="col-md-3">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <div class="row">
                                                        <div class="col-xs-3">
                                                            <a href="{{ secure_url('tama-family') }}">
                                                                <img class="family-logo" src="{{ secure_asset('images/family.png') }}">
                                                            </a>
                                                        </div>
                                                        <div class="col-xs-9 text-right">
                                                            <div class="huge"></div>
                                                            <div class="huge-next">Tama Family</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <a href="{{ secure_url('tama-family') }}"
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
                                    @endif
                                    @if(\App\Models\Service::where('id', 9)->where('status', 1)->first())
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
                                    @endif
                                    <div class="col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <div class="row">
                                                    <div class="col-xs-3">
                                                        <i class="fa fa-calendar-check fa-4x text-success"></i>
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
                                @endif
                                @if(in_array(auth()->user()->group_id,[2,3]))
                                    <div class="col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <div class="row">
                                                    <div class="col-xs-3">
                                                        <i class="fa fa-calendar-check fa-4x text-info"></i>
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
                                    <div class="col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <div class="row">
                                                    <div class="col-xs-3">
                                                        <i class="fa fa-history fa-4x text-danger"></i>
                                                    </div>
                                                    <div class="col-xs-9 text-right">
                                                        <div class="huge">{{ isset($total_transaction) ? $total_transaction : 0 }}</div>
                                                        <div class="huge-next">{{ trans('common.dashboard_total_trans') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="{{ secure_url('transactions') }}" class="a-footer">
                                                <div class="panel-footer dashboard-panel-footer">
                                                    <span class="pull-left">{{ trans('common.view_all') }}</span>
                                                    <span class="pull-right"><i
                                                                class="fa fa-arrow-circle-right"></i></span>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if(auth()->user()->group_id == 6)
                                    <div class="col-md-3">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <div class="row">
                                                    <div class="col-xs-3">
                                                        <i class="fa fa-history fa-4x"></i>
                                                    </div>
                                                    <div class="col-xs-9 text-right">
                                                        <div class="huge">{{ isset($orders_in_progress) ? $orders_in_progress : 0 }}</div>
                                                        <div class="huge-next">{{ trans('service.order_in_progress') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="#" class="a-footer">
                                                <div class="panel-footer dashboard-panel-footer">
                                                    <span class="pull-left">{{ trans('common.view_all') }}</span>
                                                    <span class="pull-right"><i
                                                                class="fa fa-arrow-circle-right"></i></span>
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
                                                        <i class="fa fa-check-circle fa-4x"></i>
                                                    </div>
                                                    <div class="col-xs-9 text-right">
                                                        <div class="huge">{{ isset($closed_orders) ? $closed_orders : 0 }}</div>
                                                        <div class="huge-next">{{ trans('service.closed_orders') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="#" class="a-footer">
                                                <div class="panel-footer dashboard-panel-footer">
                                                    <span class="pull-left">{{ trans('common.view_all') }}</span>
                                                    <span class="pull-right"><i
                                                                class="fa fa-arrow-circle-right"></i></span>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h3 class="panel-title"><i
                                                        class="fa fa-list-ol"></i>&nbsp;{{ trans('common.dashboard_last10_orders') }}
                                            </h3>
                                        </div>
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-striped table-bordered">
                                                    <thead>
                                                    <tr>
                                                        <th>{{ trans('common.lbl_date') }}</th>
                                                        <th>{{ trans('common.order_tbl_retailer') }}</th>
                                                        <th>{{ trans('common.order_tbl_service') }}</th>
                                                        <th>{{ trans('common.prod_name') }}</th>
                                                        <th>{{ trans('common.order_tbl_price') }}</th>
                                                        <th>{{ trans('common.order_status') }}</th>
                                                        @if(auth()->user()->group_id ==6)
                                                            <th>{{ trans('common.mr_tbl_action') }}</th>
                                                        @endif
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @if(isset($orders))
                                                        @foreach($orders as $order)
                                                            <?php
                                                            $order_item = \App\Models\OrderItem::find($order->order_item_id);
                                                            if ($order->service_id == 1) {
                                                                $product_name = optional(\App\Models\Product::find($order->product_id))->name;
                                                            } elseif ($order->service_id == 2 || $order->service_id == 7) {
                                                                $product_name = optional($order_item)->tt_operator;
                                                            } else {
                                                                $iso_code = optional(\App\User::find($order->user_id))->currency;
                                                                $price = $order->public_price == "0.00" ? $order->grand_total : $order->public_price;

                                                                $product_name = $order->service_name . ' ' . \app\Library\AppHelper::formatAmount($iso_code, $price);
                                                            }
                                                            $price = $order->public_price == "0.00" ? $order->grand_total : $order->public_price;
                                                            ?>
                                                            <tr>
                                                                <td>{{ $order->date }}</td>
                                                                <td>{{ $order->username }}</td>
                                                                <td>
                                                                    @if($order->service_name != 'Topup')
                                                                        @if($order->service_name == 'Tama Topup')
                                                                            TopUp
                                                                        @else
                                                                            {{ $order->service_name }}
                                                                        @endif
                                                                    @endif
                                                                </td>
                                                                <td>{{ $product_name }}</td>
                                                                <td>{{ $price }}</td>
                                                                <td>{{ $order->order_status_name }}</td>
                                                                @if(auth()->user()->group_id ==6)
                                                                    <td><a href="{{ secure_url('order/'.$order->id) }}"
                                                                           class="btn btn-primary btn-xs"><i
                                                                                    class="fa fa-edit"></i>&nbsp;{{ trans('common.lbl_view') }}
                                                                        </a></td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td></td>
                                                            <td colspan="4"
                                                                class="text-center">{{ trans('common.search_no_results') }}</td>
                                                            <td></td>
                                                        </tr>
                                                    @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
