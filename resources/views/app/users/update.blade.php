@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('users.lbl_users'),'url'=> secure_url('users'),'active' => 'no'],
        ['name' => trans('common.btn_update')." ".$row['username'],'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/intl-input/css/intlTelInput.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <form class="form-horizontal" id="frmUser" action="{{ secure_url('user/update') }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $row['id'] }}">
            <div class="row">
                <div class="col-md-12 m-b-10">
                    <div class="pull-left">
                        @if($row['id'] != '')
                            <span class="text-muted help-block">{{ trans('users.last_modified') }} {{ $row['updated_at'] == '' ? $row['created_at'] : $row['updated_at'] }}</span>
                        @endif
                    </div>
                    <div class="pull-right">
                        <button type="submit" id="btnSubmit" class="btn btn-primary"><i
                                    class="fa fa-save"></i>&nbsp;{{ trans('common.btn_save') }}</button>
                        <a href="{{ secure_url('users') }}" class="btn btn-warning"><i
                                    class="fa fa-times"></i>&nbsp;{{ trans('common.btn_cancel') }}</a>
                    </div>
                </div>
            </div>
            <div class="row profile">
                <div class="col-md-3">
                    <div class="profile-sidebar">
                        <!-- SIDEBAR USERPIC -->
                        <div class="profile-userpic m-b-10">
                            <img src="{{ secure_asset($row['user_image']) }}" id="img_holder" class="img-responsive" alt="">
                            <div class="col-md-2"></div>
                            <div class="col-md-8 m-t-5">
                                <input type="file" class="form-control" name="image" id="image">
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                        <!-- END SIDEBAR USERPIC -->

                        <!-- SIDEBAR BUTTONS -->
                        <div class="profile-userbuttons" style="margin-top: 50px">
                            <div class="checkbox">
                                <label><input name="status" type="checkbox"
                                              value="1" @if($row['status'] == 1) checked @endif>{{ trans('users.lbl_user_access') }} {{ trans('common.lbl_enabled') }}</label>
                            </div>

                        </div>
                        <!-- END SIDEBAR BUTTONS -->
                        <!-- SIDEBAR MENU -->
                        <div class="profile-usermenu" style="margin-top: 50px">
                            <ul class="nav">
                                <li class="active">
                                    <a data-toggle="tab" href="#home">
                                        <i class="fa fa-home"></i>
                                        {{ trans('users.lbl_user_info') }} </a>
                                </li>
                                <li class="onlyRetailers">
                                    <a data-toggle="tab" href="#user_balance">
                                        <i class="fa fa-money-bill-alt"></i>
                                        {{ trans('users.lbl_user_balance') }}</a>
                                </li>
                                <li class="onlyRetailers">
                                    <a data-toggle="tab" href="#commission">
                                        <i class="fa fa-tasks"></i>
                                        {{ trans('users.lbl_user_commission_setup') }} & {{ trans('users.lbl_user_access') }}</a>
                                </li>
                                @if(auth()->user()->group_id == '3')
                                    <li>
                                        <a data-toggle="tab" href="#payments">
                                            <i class="fa fa-tasks"></i>Payment Limit</a>
                                    </li>
                                @endif
                                <li class="onlyMasterRetailers">
                                    <a data-toggle="tab" href="#webhook">
                                        <i class="fa fa-globe"></i>
                                        WebHook Information</a>
                                </li>
                            </ul>
                        </div>
                        <!-- END MENU -->
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="profile-content" id="login-loader">
                        <div class="tab-content">
                            <div id="home" class="tab-pane fade in active">
                                <div class="m-t-10">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-4"
                                                       for="group_id">{{ trans('users.lbl_user_group') }}</label>
                                                <div class="col-md-8">
                                                    <select tabindex="1" class="form-control" name="group_id"
                                                            id="group_id">
                                                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                        @foreach($user_groups as $user_group)
                                                                <option value="{{ $user_group->id }}" @if($row['group_id'] == $user_group->id) selected @endif>{{ $user_group->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group hide" id="managerRetailer">
                                                <label class="control-label col-md-5"
                                                       for="parent_id">{{ trans('users.lbl_user_mgr') }}</label>
                                                <div class="col-md-7">
                                                    <select tabindex="2" class="form-control" name="parent_id"
                                                            id="parent_id">
                                                        <option value="">{{ trans('users.none') }}</option>
                                                        @foreach($parent_manager as $value)
                                                            <option value="{{ $value->id }}"
                                                                    @if($row['parent_id'] == $value->id) selected @endif>{{ $value->username }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-4"
                                                       for="username">{{ trans('users.lbl_user_name') }}</label>
                                                <div class="col-md-8">
                                                    <input tabindex="3" class="form-control" type="text" name="username"
                                                           id="username" value="{{ $row['username'] }}">
                                                    <div id="usernameerror"></div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-4"
                                                       for="first_name">{{ trans('users.lbl_user_fname') }}</label>
                                                <div class="col-md-8">
                                                    <input tabindex="4" class="form-control" type="text"
                                                           name="first_name" id="first_name"
                                                           value="{{ $row['first_name'] }}">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-4"
                                                       for="last_name">{{ trans('users.lbl_user_lname') }}</label>
                                                <div class="col-md-8">
                                                    <input tabindex="5" class="form-control" type="text"
                                                           name="last_name" id="last_name"
                                                           value="{{ $row['last_name'] }}">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-4"
                                                       for="email">{{ trans('users.lbl_user_email') }}</label>
                                                <div class="col-md-8">
                                                    <input tabindex="5" class="form-control" type="text" name="email"
                                                           id="email" value="{{ $row['email'] }}">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-4"
                                                       for="mobile">{{ trans('users.lbl_mobile_no') }}</label>
                                                <div class="col-md-8">
                                                    <input tabindex="5" class="form-control" type="text" name="mobile"
                                                           id="mobile" value="+{{$row['mobile'] }}">
                                                    <span id="error-msg"
                                                          class="text-danger help-block  hide">{{ trans('users.error_mobile_no') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group hide onlyRetailers">
                                                <label class="control-label col-md-4">{{ trans('users.lbl_user_api_access') }}</label>
                                                <div class="col-md-8">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="is_api_user" id="is_api_user" type="checkbox" @if($row['is_api_user'] == 1) checked @endif value="1">{{ trans('common.lbl_enabled') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group hide" id="can_process_order_div">
                                                <label for="can_process_order" class="control-label col-md-4">{{ trans('users.process_order') }}</label>
                                                <div class="col-md-8">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="can_process_order" type="checkbox" @if($row['can_process_order'] == 1) checked @endif value="1">{{ trans('common.lbl_yes') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-4"
                                                       for="country_id">{{ trans('users.lbl_user_country') }}</label>
                                                <div class="col-md-8">
                                                    <select class="form-control" name="country_id" id="country_id">
                                                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                        @foreach($countries as $country)
                                                            <option value="{{ $country->id }}"
                                                                    @if($row['country_id'] == $country->id) selected @endif>{{ $country->nice_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-4"
                                                       for="currency">{{ trans('users.lbl_user_currency') }}</label>
                                                <div class="col-md-8">
                                                    <select class="form-control" name="currency" id="currency">
                                                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                        @foreach($countries as $country)
                                                            <option class="{{ $country->id }}_curr hide"
                                                                    value="{{ $country->currency }}"
                                                                    @if($row['currency'] == $country->currency) selected @endif>{{ $country->currency }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-4"
                                                       for="timezone">{{ trans('users.lbl_user_timezone') }}</label>
                                                <div class="col-md-8">
                                                    <select class="form-control" name="timezone" id="timezone">
                                                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                        @foreach($countries as $country)
                                                            <option class="{{ $country->id }}_tz hide"
                                                                    value="{{ $country->timezone }}"
                                                                    @if($row['timezone'] == $country->timezone) selected @endif>{{ $country->timezone }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-4" for="address">{{ trans('users.lbl_user_address') }}</label>
                                                <div class="col-md-8">
                                                    <textarea class="form-control" name="address" id="address">{{ $row['address'] }}</textarea>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-4" for="password">{{ trans('users.lbl_password') }}</label>
                                                <div class="col-md-8">
                                                    <div class="input-group">
                                                        <input class="form-control" type="password" name="password" id="password">
                                                        <span class="input-group-addon">
                                                            <i id="toggleEye" onclick="showPass()" class="fa fa-eye-slash"></i>
                                                          </span>
                                                    </div>
                                                    @if($row['id'] != '')
                                                        <span class="help-block text-muted">{{ trans('users.password_help_block') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-4" for="confirm_password">{{ trans('users.lbl_confirm_password') }}</label>
                                                <div class="col-md-8">
                                                    <input class="form-control" type="password" name="confirm_password" id="confirm_password">
                                                </div>
                                            </div>
                                            @if(auth()->user()->group_id  == 2 || auth()->user()->group_id == 3)
                                                <div class="form-group">
                                                    <label class="control-label col-md-4" for="authentication_method">{{ trans('common.authentication_method') }}</label>
                                                    <div class="col-md-8">
                                                        <select class="form-control" name="authentication_method" id="authentication_method">
                                                            <option value="0" @if($row['method'] == 0) selected @endif>{{ trans('common.method_0') }}</option>
                                                            <option value="1" @if($row['method'] == 1) selected @endif>{{ trans('common.method_1') }}</option>
                                                            <option value="2" @if($row['method'] == 2) selected @endif>{{ trans('common.method_2') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-4" for="active_device_limit">Allowed active devices</label>
                                                    <div class="col-md-8">
                                                        <select class="form-control" name="active_device_limit" id="active_device_limit">
                                                            <option value="1" @if((int) $row['max_active_sessions'] === 1) selected @endif>1 device</option>
                                                            <option value="2" @if((int) $row['max_active_sessions'] === 2) selected @endif>2 devices - 2FA required</option>
                                                        </select>
                                                        <span class="help-block text-muted">Two active devices forces authenticator 2FA before the second device is accepted.</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="user_balance" class="tab-pane">
                                <div class="m-t-10 onlyRetailers">
                                    <div class="row">
                                        <fieldset class="fieldset-border">
                                            <legend class="legend-border">{{ trans('users.lbl_user_credit_limit') }}</legend>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label col-md-6" for="credit_limit">{{ trans('users.lbl_user_transaction_current_credit_limit') }}</label>
                                                    <div class="col-md-6">
                                                        <p class="text-danger" style="font-size: 22px">{{ \app\Library\AppHelper::get_credit_limit($row['id']) }}</p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-4" for="credit_limit">{{ trans('common.mr_tbl_credit_limit') }}</label>
                                                    <div class="col-md-8">
                                                        <input class="form-control money-input" type="text" name="credit_limit" id="credit_limit">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label col-md-6" for="amount">{{ trans('users.lbl_user_transaction_current_balance') }}</label>
                                                    <div class="col-md-6">
                                                        <p class="text-danger" style="font-size: 22px">{{ \app\Library\AppHelper::getBalance($row['id'],$row['currency'],true) }}</p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-4" for="amount">{{ trans('common.payment_frm_amount') }}</label>
                                                    <div class="col-md-8">
                                                        <input class="form-control money-input" type="text" name="amount" id="amount" value="">
                                                    </div>
                                                </div>
                                                @if(auth()->user()->group_id == 3)
                                                    <input type="hidden" id="same_amount_manager" name="same_amount_manager"value ="1">
                                                @endif
                                                <div class="form-group">
                                                    <label class="control-label col-md-4" for="description">{{ trans('common.lbl_desc') }}</label>
                                                    <div class="col-md-8">
                                                        <textarea class="form-control" name="description" id="description"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                        @if($row['group_id'] == 4 || $row['group_id'] == '')
                                            <fieldset class="fieldset-border">
                                                <legend class="legend-border">{{ trans('users.lbl_tbl_user_limit') }}</legend>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label col-md-4" for="credit_limit">{{ trans('users.lbl_tbl_user_limit') }}</label>
                                                        <div class="col-md-8">
                                                            <input class="form-control money-input" type="text" name="daily_limit" id="daily_limit">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-6" for="credit_limit">{{ trans('users.lbl_user_daily_current_credit_limit') }}</label>
                                                        <div class="col-md-6">
                                                            <p class="text-danger" style="font-size: 22px">{{ \app\Library\AppHelper::get_daily_limit($row['id']) }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label col-md-6" for="amount">{{ trans('users.lbl_remaining_limit') }}</label>
                                                        <div class="col-md-6">
                                                            <p class="text-danger" style="font-size: 22px">{{ \app\Library\AppHelper::get_remaning_limit_balance($row['id']) }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        @endif
                                        <fieldset class="fieldset-border">
                                            <legend class="legend-border">{{ trans('users.lbl_tbl_user_balance') }}</legend>
                                            <div class="col-md-12">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead>
                                                        <tr>
                                                            <td>{{ trans('common.mr_tbl_sl') }}</td>
                                                            <td>{{ trans('common.payment_frm_paid_on') }}</td>
                                                            <td>{{ trans('common.payment_tbl_paid_amount') }}</td>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php($sl=1)
                                                        @if(isset($row['payment_history']) && !empty($row['payment_history']))
                                                            @foreach($row['payment_history'] as $payment)
                                                                <tr>
                                                                    <td>{{ $sl }}</td>
                                                                    <td>{{ $payment['date'] }}</td>
                                                                    <td>{{ $payment['amount'] }}</td>
                                                                </tr>
                                                                @php($sl++)
                                                            @endforeach
                                                            <tr>
                                                                <td colspan="3" class="text-center"><a href="{{ secure_url('/payments?user='.$row['username']) }}" class="btn btn-primary" target="_blank">{{ trans('common.view_all') }}&nbsp;<i class="fa fa-external-link-alt"></i></a> </td>
                                                            </tr>
                                                        @else
                                                            <tr>
                                                                <td class="text-center" colspan="3">{{ trans('common.search_no_results') }}</td>
                                                            </tr>
                                                        @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                            <div id="commission" class="tab-pane">
                                <div class="m-t-10 onlyRetailers">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped  table-hover" id="tamaCommission">
                                            <thead>
                                            <tr>
                                                <th>{{ trans('users.lbl_user_commission_service') }}</th>
                                                <th class="hide">{{ trans('users.lbl_user_service_status') }}</th>
                                                <th>{{ trans('users.current_commission') }}</th>
                                                <th class="hide">{{ trans('users.new_commission') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(isset($services) && !empty($services))
                                                @foreach($services as $service)
                                                    <?php
                                                    $commission_amount = \app\Library\DBHelper::getCommission(auth()->user()->id,$service->id);
                                                    ?>
                                                    @if($service->status == 1 && \app\Library\AppHelper::skip_service_as_menu(str_slug($service->name,'-')))
                                                        <tr>
                                                            <td>{{ $service->name }}</td>
                                                            <td class="text-center hide">
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="service_{{ $service->id }}" type="checkbox" value="1" @if(\app\Library\AppHelper::user_access($service->id,$row['id']) == 1) checked @endif>{{ trans('common.lbl_enabled') }}</label>
                                                                </div>
                                                            </td>
                                                            @if(auth()->user()->group_id == 1)
                                                                <td class="text-center">{{ \app\Library\DBHelper::getAppCommission($service->id) }}%</td>
                                                            @else
                                                                <td class="text-center">{{ \app\Library\DBHelper::getCommission($row['id'],$service->id) }}%
                                                                    @if($row['id'] != '' && $service->id == 2 && auth()->user()->group_id == 2)
                                                                        <a data-toggle="modal" data-target="#commision{{$service->id}}" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>

                                                                        <div id="commision{{$service->id}}" class="modal fade" role="dialog">
                                                                            <div class="modal-dialog">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                                        <h4 class="modal-title">{{(trans('common.lbl_edit'))}} Commission</h4>
                                                                                    </div>
                                                                                    <div class="modal-body col-md-12">
                                                                                        <div class="form-group row">
                                                                                            <label class="control-label col-md-4" for="web_hook_url">Manager Commission</label>
                                                                                            <div class="col-md-8">
                                                                                                <input type="text" class="form-control" name="m_commission" id="m_commstions" value="{{ \app\Library\DBHelper::getCommission($row['id'],$service->id) }}">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group row">
                                                                                            <label class="control-labezl col-md-4" for="web_hook_url">Retailer Commission</label>
                                                                                            <div class="col-md-8">
                                                                                                <input type="text" class="form-control" name="r_commission" id="r_commstions" value="{{ \app\Library\DBHelper::getCommission($row['child_id'],$service->id) }}">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="modal-footer">
                                                                                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo e(trans('common.btn_close')); ?></button>
                                                                                    </div>
                                                                                </div>

                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                            @endif
                                                            <td  class="hide">
                                                                @if(auth()->user()->group_id == 1)
                                                                @else
                                                                    <input type="text" class="form-control money-input" name="service_commission_{{ $service->id }}" data-rule-range="1,{{ $commission_amount }}" data-msg-range="{{ $commission_amount != '' ? trans('users.enter_commission').' '.$commission_amount.trans('users.percent_commission') : "" }}" >
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @else

                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered  table-striped table-hover">
                                            <tbody>
                                            <tr>
                                                <td>{{ trans('service.service_calling_cards') }} {{ trans('myservice.rate_table') }}</td>
                                                <td>
                                                    <select class="form-control" name="rate_group_id" id="rate_group_id">
                                                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                        @if(isset($rate_table_groups))
                                                            @foreach($rate_table_groups as $rate_table_group)             <option value="{{ $rate_table_group->id }}" @if($row['rate_group_id'] == $rate_table_group->id) selected @endif>{{ $rate_table_group->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </td>
                                            </tr>
                                            @if($row['group_id'] != 2)
                                                <tr>
                                                    <td>{{ trans('service.can_print_again') }}</td>
                                                    <td>
                                                        <label class="checkbox-inline"><input type="checkbox" name="pin_print_again" @if($row['pin_print_again'] == 1) checked @endif>{{ trans('common.lbl_yes') }}</label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>{{ trans('service.ip_address_config') }}</td>
                                                    <td>
                                                        <label class="checkbox-inline"><input type="checkbox" name="enable_ip" @if($row['enable_ip'] == 1) checked @endif>{{ trans('common.lbl_yes') }}</label>
                                                    </td>
                                                </tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id="payments" class="tab-pane">
                                <div class="m-t-10 onlyRetailers">
                                    <div class="row">
                                        <fieldset class="fieldset-border">
                                            <legend class="legend-border">Payments Limits</legend>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label col-md-4" for="daily">Daily Limit</label>
                                                    <div class="col-md-4">
                                                        <input class="form-control money-input" type="text" name="daily" id="daily" value="{{ $row['daily'] }}">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-4" for="weekly">Weekly Limit</label>
                                                    <div class="col-md-4">
                                                        <input class="form-control money-input" type="text" name="weekly" id="weekly" value="{{ $row['weekly'] }}">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-4" for="monthly">Monthly Limit</label>
                                                    <div class="col-md-4">
                                                        <input class="form-control money-input" type="text" name="monthly" id="monthly" value="{{ $row['monthly'] }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                            <div id="webhook" class="tab-pane onlyMasterRetailers">
                                <div class="m-t-10 onlyMasterRetailers">
                                    <div class="row">
                                        <fieldset class="fieldset-border">
                                            <legend class="legend-border">Web Hook Information</legend>
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4" for="web_hook_url">API URL</label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="web_hook_url" id="web_hook_url" value="{{ $row['web_hook_url'] }}">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4" for="web_hook_uri">API URI End point</label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="web_hook_uri" id="web_hook_uri" value="{{ $row['web_hook_uri'] }}">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4" for="web_hook_token">Access Token</label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="web_hook_token" id="web_hook_token" value="{{ $row['web_hook_token'] }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script src="{{ secure_asset('vendor/intl-input/js/intlTelInput.js') }}?v={{ filemtime(public_path('vendor/intl-input/js/intlTelInput.js')) }}" type="text/javascript"></script>
    <script>
        function showPass() {
            var x = document.getElementById("password");
            if (x.type === "password") {
                x.type = "text";
                $("#toggleEye").removeClass('fa fa-eye-slash');
                $("#toggleEye").addClass('fa fa-eye');
            } else {
                x.type = "password";
                $("#toggleEye").removeClass('fa fa-eye');
                $("#toggleEye").addClass('fa fa-eye-slash');
            }
        }
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#img_holder').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $(document).ready(function () {
            $("#group_id").change(function () {
                if($(this).val() == 1){
                    $(".onlyRetailers").addClass('hide');
                    $("#can_process_order_div").removeClass('hide');
                    $('#is_api_user').prop('checked', false);
                    $("#managerRetailer").addClass('hide');
                }else if($(this).val() == 2){
                    $(".onlyRetailers").removeClass('hide');
                    $("#can_process_order_div").addClass('hide');
                    $('#is_api_user').prop('checked', false);
                    $("#managerRetailer").addClass('hide');
                }else if($(this).val() == 3){
                    $(".onlyRetailers").removeClass('hide');
                    $("#can_process_order_div").addClass('hide');
                    $("#managerRetailer").addClass('hide');
                    $('#is_api_user').prop('checked', false);
                    $("#managerRetailer").addClass('hide');
                }else if($(this).val() == 4){
                    $(".onlyRetailers").removeClass('hide');
                    $("#can_process_order_div").addClass('hide');
                    $("#managerRetailer").removeClass('hide');
                    $('#is_api_user').prop('checked', false);
                    $("#managerRetailer").addClass('hide');
                }else if($(this).val() == 5){
                    $(".onlyRetailers").removeClass('hide');
                    $("#can_process_order_div").addClass('hide');
                    $("#managerRetailer").addClass('hide');
                    $('#is_api_user').prop('checked', true);
                    $("#tamaCommission").addClass('hide');
                    $("#managerRetailer").removeClass('hide');
                }else if($(this).val() == 6){
                    $(".onlyRetailers").addClass('hide');
                    $("#can_process_order_div").removeClass('hide');
                    $('#is_api_user').prop('checked', false);
                    $("#managerRetailer").removeClass('hide');
                }else{
                    $(".onlyRetailers").addClass('hide');
                    $("#can_process_order_div").addClass('hide');
                    $("#managerRetailer").addClass('hide');
                    $("#parent_id").val('');
                    $('#is_api_user').prop('checked', false);
                    $("#managerRetailer").removeClass('hide');
                }

            });
            $("#group_id").change();

            function syncActiveDeviceAuthMethod() {
                if ($("#active_device_limit").val() === "2") {
                    $("#authentication_method").val("2");
                }
            }

            $("#active_device_limit").on("change", syncActiveDeviceAuthMethod);
            $("#authentication_method").on("change", syncActiveDeviceAuthMethod);
            syncActiveDeviceAuthMethod();

            $("#image").change(function () {
                readURL(this);
            });

            var telInput = $("#mobile"),
                telInputEl = telInput.get(0),
                errorMsg = $("#error-msg"),
                iti = null;

            // initialise plugin (supports both old jQuery wrapper and newer vanilla API)
            if (typeof telInput.intlTelInput === "function") {
                telInput.intlTelInput({
                    initialCountry: "fr",
                    nationalMode: true,
                    formatOnDisplay: true,
                    utilsScript: "{{ secure_asset('vendor/intl-input/js/utils.js') }}"
                });
            } else if (window.intlTelInput && telInputEl) {
                iti = window.intlTelInput(telInputEl, {
                    initialCountry: "fr",
                    nationalMode: true,
                    formatOnDisplay: true,
                    loadUtils: function () {
                        return import("{{ secure_asset('vendor/intl-input/js/utils.js') }}");
                    }
                });
            }

            function itiIsValidNumber() {
                if (iti) { return iti.isValidNumber(); }
                if (typeof telInput.intlTelInput === "function") { return telInput.intlTelInput("isValidNumber"); }
                return false;
            }

            function itiGetNumber() {
                if (iti) { return iti.getNumber(); }
                if (typeof telInput.intlTelInput === "function") { return telInput.intlTelInput("getNumber"); }
                return telInput.val();
            }

            function itiGetSelectedCountryData() {
                if (iti) { return iti.getSelectedCountryData() || {}; }
                if (typeof telInput.intlTelInput === "function") { return telInput.intlTelInput("getSelectedCountryData") || {}; }
                return {};
            }

            var reset = function () {
                telInput.removeClass("error");
                errorMsg.addClass("hide");
            };

            // on blur: validate
            telInput.blur(function () {
                reset();
                if ($.trim(telInput.val())) {
                    if (itiIsValidNumber()) {
                        telInput.parents('.form-group').removeClass('has-error');
                        var intlNumber = itiGetNumber(); // get full number eg +17024181234
                        var countryData = itiGetSelectedCountryData(); // get country data as obj
                        var countryCode = countryData.dialCode; // get the actual code eg 1 for US
                        countryCode = "+" + countryCode; // convert 1 to +1

                        var newNo = intlNumber.replace(countryCode, countryCode );
                        console.log('mobile ',countryData);
                        telInput.val(newNo);
                        $("#btnSubmit").removeAttr('disabled');
                    } else {
                        telInput.addClass("error");
                        telInput.parents('.form-group').addClass('has-error');
                        errorMsg.removeClass("hide");
                        $("#btnSubmit").attr('disabled','disabled');
                    }
                }
            });

            // on keyup / change flag: reset
            telInput.on('keyup change paste input', function (e) {
                reset();
                var code = (e.keyCode || e.which);
                // skip arrow keys
                if (code == 37 || code == 38 || code == 39 || code == 40 || code == 8) {
                    return;
                }
                // if first character is 0 filter it off
                var num = $(this).val();
                if (num.length === '') {
                    $(this).val('+');
                }
            });
            @if($row['mobile'] != '')
            setTimeout(function () {
                telInput.val(itiGetNumber())
            }, 1000);
            @endif
            telInput.change();

            $("#country_id").change(function () {
                var currency = $(this).find(':selected').val() + "_curr";
                var timezone = $(this).find(':selected').val() + "_tz";
                $("#currency > option").each(function () {
                    if ($(this).hasClass(currency)) {
                        $(this).removeClass('hide');
                        $(this).attr('selected','selected');
                    } else {
                        $(this).addClass('hide');
                    }
                });
                $("#timezone > option").each(function () {
                    if ($(this).hasClass(timezone)) {
                        $(this).removeClass('hide');
                        $(this).attr('selected','selected');
                    } else {
                        $(this).addClass('hide');
                    }
                });
            });
            $("#country_id").change();

            $('#frmUser').validate({
                // rules & options,
                rules: {
                    group_id: "required",
                    @if($row['id'] == '')
                    password: "required",
                    @endif
                    username : 'required',
                    first_name : 'required',
                    last_name : 'required',
                    // email : 'required',
                    mobile : 'required',
                    country_id : 'required',
                },
                errorElement: "span",
                errorPlacement: function (error, element) {
                    // Add the `help-block` class to the error element
                    error.addClass("help-block");

                    if (element.prop("type") === "checkbox") {
                        error.insertAfter(element.parents("checkbox"));
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).parents(".form-group").addClass("has-error").removeClass("has-success");
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).parents(".form-group").addClass("").removeClass("has-error");
                },
                submitHandler: function (form) {
                    $.confirm({
                        title: '{{ trans('common.btn_save') }}',
                        content: '{{ trans('common.lbl_ask_proceed_form') }}',
                        buttons: {
                            "{{ trans('common.btn_save') }}": function () {
                                $("#login-loader").LoadingOverlay("show");

                                $("#btnSubmit").html("<i class='fa fa-refresh fa-spin'></i>&nbsp;{{ trans('common.btn_save_changes') }}...").attr('disabled', 'disabled');
                                form.submit();
                            },
                            "{{ strtolower(trans('common.btn_cancel')) }}": function () {

                            }
                        }
                    });
                }
            });
            $('#username').on('change', function (e) {
                var username=this.value;
                $.ajax({
                    url : "{{ secure_url('check_username') }}",
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    type : "GET",
                    data: {username:username},
                    dataType: "json",
                    success : function(data)
                    {
                        if(data.success == false){
                            $('#username').addClass("error").focus();
                            $('#username').attr('style', "border-radius: 5px; border:#a94442 1px solid;box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);");
                            $("#btnSubmit").attr('disabled','disabled');
                            $("#usernameerror").show().html('<span style="color:#a94442";>' + data.message + '</span>');
                        }
                        if(data.success == true) {
                            $("#btnSubmit").removeAttr('disabled');
                            $('#username').removeAttr('style');
                            $("#usernameerror").hide();
                        }
                        if('#username' =='') {
                            $("#btnSubmit").removeAttr('disabled');
                            $('#username').removeAttr('style');
                            $("#usernameerror").hide();
                        }
                    }
                });
            });
        });
    </script>
@endsection
