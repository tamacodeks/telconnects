@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "Send Tama Configuration",'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/multi-select/css/multi-select.css') }}" rel="stylesheet"/>
    <script src="{{ secure_asset('vendor/multi-select/js/jquery.multi-select.js') }}"></script>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('service.service_config_st') }}
                    </div>
                    <div class="panel-body">
                        <form class="form-horizontal"
                              action="{{ secure_url('config/send-tama/save') }}"
                              method="post">
                            {{ csrf_field() }}
                            <div class="form-group{{ $errors->has('config_data') ? ' has-error' : '' }}">
                                <label class="control-label col-md-4">&nbsp;</label>
                                <div class="col-md-8">
                                    <?php
                                    $currencies = \App\Models\Currency::all();
                                    ?>
                                    <select id='selected_country_data' name="selected_country_data[]"
                                            multiple='multiple'>
                                        @foreach($avail_countries as $cty=>$value)
                                            <option value='{{ $cty }}'>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('selected_country_data'))
                                        <span class="help-block">
                                                                    <strong>{{ $errors->first('selected_country_data') }}</strong>
                                                                </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-4"></div>
                                <div class="col-md-4">
                                    <button type="submit"
                                            class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;{{ trans('common.btn_save') }}</button>
                                </div>
                                <div class="col-md-4"></div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#selected_country_data').multiSelect({
                selectableHeader: "<div class='custom-header'>{{ \Lang::get('service.service_config_avc') }}</div>",
                selectionHeader: "<div class='custom-header'>{{ \Lang::get('service.service_config_sc') }}</div>",
                selectableFooter: "<div class='custom-header'>{{ \Lang::get('service.service_config_avc') }}</div>",
                selectionFooter: "<div class='custom-header'>{{ \Lang::get('service.service_config_sc') }}</div>"
            });
            @if(isset($chosen_cty['access_data']))
            $("#selected_country_data").multiSelect('select', [@foreach ($chosen_cty['access_data'] as $cty) "{{ $cty }}", @endforeach]);
            @endif

        });
    </script>
@endsection