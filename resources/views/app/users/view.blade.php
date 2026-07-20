@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "Users",'url'=> secure_url('users'),'active' => 'no'],
        ['name' => "View ".$row->username,'url'=> '','active' => 'yes']
    ]
    ])
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 m-b-10">
                <div class="pull-left">
                    <a href="{{ secure_url('user/update/'.$row->id) }}" class="btn btn-default btn-sm"><i class="fa fa-edit"></i>&nbsp;{{ trans('common.lbl_edit') }}</a>
                </div>
                <div class="pull-right">

                </div>
            </div>
        </div>
        <div class="row profile">
            <div class="col-md-3">
                <div class="profile-sidebar">
                    <!-- SIDEBAR USERPIC -->
                    <div class="profile-userpic">
                        <img src="{{ secure_asset($user_image) }}" class="img-responsive" alt="">
                    </div>
                    <!-- END SIDEBAR USERPIC -->
                    <!-- SIDEBAR USER TITLE -->
                    <div class="profile-usertitle">
                        <div class="profile-usertitle-name">
                            {{ $row->username }}
                        </div>
                        <div class="profile-usertitle-job">
                            {{ $row->group->name }}
                        </div>
                    </div>
                    <!-- END SIDEBAR USER TITLE -->
                    <!-- SIDEBAR BUTTONS -->
                    <div class="profile-userbuttons">
                        <a href="{{ secure_url('user/impersonate/'.\app\Library\SecurityHelper::simpleEncDec('ec',$row->id)) }}" class="btn btn-primary btn-xs"><i class="fa fa-user-secret"></i>&nbsp;{{ trans('users.lbl_user_impersonate') }} {{ $row->username }}</a>
                    </div>
                    <!-- END SIDEBAR BUTTONS -->
                    <!-- SIDEBAR MENU -->
                    <div class="profile-usermenu">
                        <ul class="nav">
                            <li class="active">
                                <a data-toggle="tab" href="#home">
                                    <i class="fa fa-home"></i>
                                    {{ trans('users.lbl_user_info') }} </a>
                            </li>
                            @if(in_array($row->group_id,[3,4,5]))
                                <li>
                                    <a data-toggle="tab" href="#commission">
                                        <i class="fa fa-tasks"></i>
                                        {{ trans('users.lbl_user_commission_setup') }}</a>
                                </li>
                                <li>
                                    <a href="{{ secure_url('orders?user='.$row->username) }}" target="_blank">
                                        <i class="fa fa-list-alt"></i>
                                        {{ trans('common.dashboard_view_orders') }} </a>
                                </li>
                                <li>
                                    <a href="{{ secure_url('transactions?user='.$row->username) }}" target="_blank">
                                        <i class="fa fa-history"></i>
                                        {{ trans('common.dashboard_view_trans') }} </a>
                                </li>
                                <li>
                                    <a  data-toggle="tab" href="#payments">
                                        <i class="fa fa-money-bill-alt"></i>
                                        {{ trans('common.breadcrumb_payment_history') }} </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <!-- END MENU -->
                </div>
            </div>
            <div class="col-md-9">
                <div class="profile-content">
                    <div class="tab-content">
                        <div id="home" class="tab-pane fade in active">
                            <div class="m-t-10">
                                <div class="col-md-12">
                                    @if(in_array($row->group_id,[3,4,5]))
                                        <div class="col-md-4">
                                            <div class="panel panel-custom-test">
                                                <div class="panel-heading media">
                                                    <div class="media-left">
                                                        <div class="panel-icon fa fa-list-alt"></div>
                                                    </div>
                                                    <div class="media-body">
                                                        <div class="panel-title">{{ trans('common.dashboard_total_orders') }}</div>
                                                    </div>
                                                </div>
                                                <div class="panel-body">
                                                    <h2 class="text-center">{{ $row->orders_count }}</h2>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($row->group_id == 3)
                                        <div class="col-md-4">
                                            <div class="panel panel-custom-test">
                                                <div class="panel-heading media">
                                                    <div class="media-left">
                                                        <div class="panel-icon fa fa-users"></div>
                                                    </div>
                                                    <div class="media-body">
                                                        <div class="panel-title">{{ trans('common.dashboard_total_resellers') }}</div>
                                                    </div>
                                                </div>
                                                <div class="panel-body">
                                                    <h2 class="text-center">{{ $row->children_count }}</h2>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <tbody>
                                            @if(in_array($row->group_id,[3,4,5]))
                                                <tr>
                                                    <td>{{ trans('users.lbl_tbl_user_rep') }}</td>
                                                    <td>{{ optional(\App\User::find($row->parent_id))->username }}
                                                        {{--@if(optional(\App\User::find($row->parent_id))->username != '')--}}
                                                        {{--(<a target="_blank" href="{{ secure_url('user/view/'.$row->parent_id) }}" class="">{{ trans('common.lbl_view') }}</a>)--}}
                                                        {{--@endif--}}
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td>{{ trans('users.lbl_user_name') }}</td>
                                                <td>{{ $row->username }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ trans('users.lbl_user_fname') }}</td>
                                                <td>{{ $row->first_name }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ trans('users.lbl_user_lname') }}</td>
                                                <td>{{ $row->last_name }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ trans('users.lbl_user_email') }}</td>
                                                <td>{{ $row->email }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ trans('users.lbl_mobile_no') }}</td>
                                                <td>{{ $row->mobile }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ trans('login.lbl_ip_address') }}</td>
                                                <td>{{ $row->ip_address }}</td>
                                            </tr>
                                            @if(in_array($row->group_id,[4]))
                                                <tr>
                                                    <td>{{ trans('service.ip_address_config') }}</td>
                                                    @if($row->enable_ip == 1)
                                                        <td>Inactivated</td>
                                                    @else
                                                        <td>Activated</td>
                                                    @endif
                                                </tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <tbody>
                                            <tr>
                                                <td>{{ trans('users.lbl_user_status') }}</td>
                                                <td> {{ trans('users.lbl_user_access') }} {{ $row->status == 1 ? trans('common.lbl_enabled') : trans('common.lbl_disabled') }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ trans('users.lbl_user_is_api_user') }}</td>
                                                <td>{{ $row->is_api_user == 1 ? trans('common.lbl_yes') : trans('common.lbl_no') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>{{ trans('users.lbl_user_currency') }}</td>
                                                <td>{{ $row->currency }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ trans('users.lbl_user_timezone') }}</td>
                                                <td>{{ $row->timezone }}</td>
                                            </tr>
                                            @if(in_array($row->group_id,[3,4,5]))
                                                <tr>
                                                    <td>{{ trans('users.balance') }}</td>
                                                    <td>{{ \app\Library\AppHelper::getBalance($row->id,$row->currency,true) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ trans('users.lbl_user_credit_limit') }}</td>
                                                    <td>{{ \app\Library\DBHelper::getCreditLimit($row->id) }}</td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td>{{ trans('users.balance') }}</td>
                                                    <td>{{  \app\Library\AppHelper::getAdminBalance(true) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ trans('users.lbl_user_credit_limit') }}</td>
                                                    <td>{{ \app\Library\AppHelper::getAdminBalance(true,true) }}</td>
                                                </tr>
                                            @endif
                                            @if(in_array($row->group_id,[4]))
                                                <tr>
                                                    <td>{{ trans('users.lbl_tbl_user_limit') }}</td>
                                                    <td>{{ \app\Library\AppHelper::get_daily_limit($row['id']) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ trans('users.lbl_remaining_limit') }}</td>
                                                    <td>{{ \app\Library\AppHelper::get_remaning_limit_balance($row['id'],$row['currency'],true) }}</td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td>{{ trans('users.process_order') }}</td>
                                                    <td><span class="label @if($row->can_process_order ==1)label-primary @else label-warning @endif">@if($row->can_process_order ==1){{ trans('common.lbl_yes') }} @else  {{ trans('common.lbl_no') }}@endif</span> </td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td>{{ trans('login.lbl_last_ip_address') }}</td>
                                                <td>{{ $row->ip_address2 }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-12 m-b-15">
                                    @if($row->is_api_user == 1)
                                        <div class="input-group">
                                            <input type="text" class="form-control"
                                                   value="{{ $row->api_token }}" placeholder="Some path" id="api_token_id">
                                            <span class="input-group-btn">
      <button data-clipboard-target="#api_token_id" class="btn btn-default" type="button" id="copy-button"
              data-toggle="tooltip" data-placement="button"
              title="Copy to Clipboard">
        <i class="fa fa-copy"></i>
      </button>
    </span>
                                        </div>

                                    @endif
                                </div>
                            </div>
                        </div>
                        <div id="commission" class="tab-pane">
                            <div class="m-t-10">
                                <fieldset class="fieldset-border">
                                    <legend class="legend-border">Tama Services</legend>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                            <tr>
                                                <th class="text-center">{{ trans('users.lbl_user_commission_service') }}</th>
                                                <th class="text-center">{{ trans('users.lbl_user_service_status') }}</th>
                                                <th class="text-center">{{ trans('common.lbl_commission_rate') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($services as $service)
                                                @if($service->status == 1 && \app\Library\AppHelper::user_access($service->id,$row->id) == 1)
                                                    <tr>
                                                        <td>{{ $service->name }}</td>
                                                        <td class="text-center"><span class="label label-{{ \app\Library\AppHelper::user_access($service->id,$row->id) == 1 ? 'primary' : "danger"}}">{{ \app\Library\AppHelper::user_access($service->id,$row->id) == 1 ? 'Enabled' : "Disabled"}}</span></td>
                                                        <td class="text-center">{{ \app\Library\DBHelper::getCommission($row->id,$service->id) }}%</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </fieldset>
                                <fieldset class="fieldset-border">
                                    <legend class="legend-border">{{ trans('common.menu_my_services') }}</legend>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <tbody>
                                            <tr>
                                                <td>{{ trans('service.service_calling_cards') }} {{ trans('myservice.rate_table') }}</td>
                                                <td class="text-center">{{ isset($rate_table) ? $rate_table : "-" }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </fieldset>
                            </div>
                        </div>
                        <div id="payments" class="tab-pane">
                            <div class="m-t-10">
                                <div class="col-md-12">
                                    <a href="{{ secure_url('/') }}" class="btn btn-primary pull-right"><i class="fa fa-plus-circle"></i>&nbsp;Add New Payment</a>
                                </div>
                                <div class="col-md-12 m-t-5">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                            <tr>
                                                <th>{{ trans('common.mr_tbl_sl') }}</th>
                                                <th>{{ trans('common.payment_frm_paid_on') }}</th>
                                                <th>{{ trans('common.payment_frm_amount') }}</th>
                                                <th>{{ trans('common.lbl_desc') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @php($sl=1)
                                            @foreach($row->payment_history as $payment)
                                                <tr>
                                                    <td>{{ $sl }}</td>
                                                    <td>{{ $payment->date }}</td>
                                                    <td>{{ $payment->amount }}</td>
                                                    <td>{{ $payment->description }}</td>
                                                </tr>
                                                @php($sl++)
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.rawgit.com/zenorocha/clipboard.js/v2.0.0/dist/clipboard.min.js"></script>
    <script>
        $(function () {
            $('#copyBtn').tooltip({ trigger: 'click'});
        });
        $(document).ready(function () {
            new ClipboardJS('.btn');
        });
    </script>
@endsection