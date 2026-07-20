@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "Settings",'url'=> '#','active' => 'no'],
        ['name' => "Application Settings",'url'=> '','active' => 'yes'],
    ]
    ])
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="pull-right m-b-20">
                        <a href="{{ secure_url('clear') }}" class="btn btn-primary">&nbsp;Clear Config</a>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <form class="form-horizontal" action="{{ secure_url('app-settings/save') }}" method="POST"
                                  enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="app_name"
                                               class="control-label col-md-4">{{ trans('settings.lbl_app_name') }}</label>
                                        <div class="col-md-8">
                                            <input type="text" name="app_name" id="app_name" value="{{ APP_NAME }}"
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="app_logo"
                                               class="control-label col-md-4">{{ trans('settings.lbl_app_logo') }}</label>
                                        <div class="col-md-8">
                                            @if(File::exists('images/'.APP_LOGO))
                                                <div style="background: #000">
                                                    <img src="{{ secure_asset('images/'.APP_LOGO) }}" class="img-responsive"
                                                         style="width: 50%">
                                                </div>
                                            @endif
                                            <input type="file" name="app_logo" id="app_logo" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="enable_multi_lang"
                                               class="control-label col-md-4">{{ trans('settings.lbl_multi_language') }}</label>
                                        <div class="col-md-8">
                                            <label class="radio-inline">
                                                <input type="checkbox" name="enable_multi_lang" value="1"
                                                       @if(ENABLE_MULTI_LANG==1 ) checked="checked" @endif />&nbsp;{{ trans('common.lbl_enabled') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="app_currency"
                                               class="control-label col-md-4">{{ trans('settings.lbl_def_currency') }}</label>
                                        <div class="col-md-8">
                                            <select class="form-control" name="app_currency" id="app_currency">
                                                @foreach($currencies as $key => $currency)
                                                    <option value="{{ $key }}"
                                                            @if(DEFAULT_CURRENCY == $key) selected @endif>{{ $currency }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="app_lang"
                                               class="control-label col-md-4">{{ trans('settings.lbl_def_language') }}</label>
                                        <div class="col-md-8">
                                            @php
                                                $language = [
                                                    ['folder' => 'en', 'name' => 'English'],
                                                    ['folder' => 'fr', 'name' => 'French'],
                                                    // Add more languages as needed
                                                ];
                                            @endphp

                                            <select class="form-control" name="app_lang" id="app_lang">
                                                @foreach($language as $lang)
                                                    <option value="{{ $lang['folder'] }}" @if(session('locale') == $lang['folder']) selected @endif>
                                                        {{ $lang['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="app_timezone"
                                               class="control-label col-md-4">{{ trans('settings.lbl_def_timezone') }}</label>
                                        <div class="col-md-8">
                                            <select class="form-control" name="app_timezone" id="app_timezone">
                                                @foreach($timezones as $key => $timezone)
                                                    <option value="{{ $timezone }}"
                                                            @if(DEFAULT_TIMEZONE == $timezone) selected @endif>{{ $timezone }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="order_prefix" class="col-sm-4 control-label">{{ trans('settings.lbl_order_prefix') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="order_prefix" name="order_prefix" value="{{ ORDER_PREFIX }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="transaction_prefix" class="col-sm-4 control-label">{{ trans('settings.lbl_trans_prefix') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="transaction_prefix" name="transaction_prefix" value="{{ TRANSACTION_PREFIX }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="transaction_prefix" class="col-sm-4 control-label">COMCOD</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="comcod" name="comcod" value="{{ COMCOD }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="transaction_prefix" class="col-sm-4 control-label">TPVCOD</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="tpvcod" name="tpvcod" value="{{ TPVCOD }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="transaction_prefix" class="col-sm-4 control-label">Authorization</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="authorization" name="authorization" value="{{ AUTHORIZATION }}">
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="per_page"
                                               class="control-label col-md-4">{{ trans('settings.lbl_per_page') }}</label>
                                        <div class="col-md-8">
                                            <select class="form-control" name="per_page" id="per_page">
                                                <option value="10" @if(PER_PAGE == 10) selected @endif>10</option>
                                                <option value="25" @if(PER_PAGE == 25) selected @endif>25</option>
                                                <option value="50" @if(PER_PAGE == 50) selected @endif>50</option>
                                                <option value="100" @if(PER_PAGE == 100) selected @endif>100</option>
                                                <option value="150" @if(PER_PAGE == 150) selected @endif>150</option>
                                                <option value="200" @if(PER_PAGE == 200) selected @endif>200</option>
                                                <option value="250" @if(PER_PAGE == 250) selected @endif>250</option>
                                                <option value="500" @if(PER_PAGE == 500) selected @endif>500</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="record_order"
                                               class="control-label col-md-4">{{ trans('settings.lbl_order_by') }}</label>
                                        <div class="col-md-8">
                                            <select class="form-control" name="record_order" id="record_order">
                                                <option value="ASC" @if(RECORD_ORDER_BY == "ASC") selected @endif>
                                                    Ascending Order
                                                </option>
                                                <option value="DESC" @if(RECORD_ORDER_BY == "DESC") selected @endif>
                                                    Descending Order
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="record_method"
                                               class="control-label col-md-4">{{ trans('settings.lbl_record_method') }}</label>
                                        <div class="col-md-8">
                                            <select class="form-control" name="record_method" id="record_method">
                                                <?php $record_methods = \app\Library\DBHelper::record_methods(); ?>
                                                @foreach($record_methods as $key => $value)
                                                    <option value="{{ $key }}" @if(DEFAULT_RECORD_METHOD == $key) selected @endif>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="enable_email"
                                               class="control-label col-md-4">{{ trans('settings.lbl_emails') }}</label>
                                        <div class="col-md-8">
                                            <label class="radio-inline">
                                                <input type="checkbox" name="enable_email" value="1"
                                                       @if(ENABLE_EMAIL == 1) checked="checked" @endif />&nbsp;{{ trans('common.lbl_enabled') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="enable_slack"
                                               class="control-label col-md-4">{{ trans('settings.lbl_enable_slack') }}</label>
                                        <div class="col-md-8">
                                            <label class="radio-inline">
                                                <input type="checkbox" name="enable_slack" value="1"
                                                       @if(ENABLE_SLACK == 1) checked="checked" @endif />&nbsp;{{ trans('common.lbl_enabled') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4" for="payment_emails">{{ trans('settings.lbl_payment_emails') }}</label>
                                        <div class="col-md-8">
                                            <textarea class="form-control" name="payment_emails" id="payment_emails">{{ PAYMENT_EMAILS }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4" for="api_token">{{ trans('settings.lbl_api_token') }}</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="api_token" id="api_token" value="{{ API_TOKEN }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4" for="api_end_point">{{ trans('settings.lbl_api_end_point') }}</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="api_end_point" id="api_end_point" value="{{ API_END_POINT }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4" for="bus_v2_design">Bus Design Format</label>
                                        <div class="col-md-8">
                                            <?php $busV2Design = defined('BUS_V2_DESIGN') ? BUS_V2_DESIGN : 'standard'; ?>
                                            <select class="form-control" name="bus_v2_design" id="bus_v2_design">
                                                <option value="standard" @if($busV2Design === 'standard') selected @endif>Design 1 - Current Bus</option>
                                                <option value="desk" @if($busV2Design === 'desk') selected @endif>Design 2 - Travel Desk</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4" for="admin_limit">Admin Limit</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" id="admin_limit" name="admin_limit" value="{{ ADMIN_LIMIT }}">
                                        </div>

                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4" for="manager_limit">Manager Limit</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" id="manager_limit" name="manager_limit" value="{{ MANAGER_LIMIT }}">
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <div class="col-md-4">

                                        </div>
                                        <div class="col-md-8 m-t-20">
                                            <button type="submit" class="btn btn-theme"><i
                                                        class="fa fa-save"></i>&nbsp;{{ trans('common.btn_save_changes') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {

        });

    </script>
@endsection
