@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('common.payment_btn_add_limit'),'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body" id="progress-loader">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="active"><a href="#Add_limit" role="tab" data-toggle="tab">Add Limit</a></li>
                            <li><a href="#Delete_limit" role="tab" data-toggle="tab">Delete Limit</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="Add_limit">
                                <div class="row">
                                    <div class="col-md-3"></div>
                                    <div class="col-md-6">
                                        <form id="frmPayment" class="form-horizontal" action="{{ secure_url('limit/update') }}" method="POST">
                                            {{ csrf_field() }}
                                            <div class="form-group">
                                                <label class="control-label col-md-4" for="retailer_id">{{ trans('myservice.lbl_choose_retailers') }}</label>
                                                <div class="col-md-8">
                                                    <select class="select-picker" name="retailer_id" id="retailer_id"  data-none-results-text="{{ trans('common.no_result_matched') }}" title="{{ trans('common.lbl_please_choose') }}"  data-size="5" data-live-search="true" >
                                                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                        @foreach($retailers as $retailer)
                                                            <?php
                                                            $rt_balance = \app\Library\AppHelper::get_daily_limit($retailer->id,$retailer->currency,true);
                                                            $r_bal = \app\Library\AppHelper::get_remaning_limit_balance($retailer->id);
                                                            ?>
                                                            <option value="{{ $retailer->id }}" data-balance="{{ $rt_balance }}" data-remaining_bal="{{ $r_bal }}">{{ $retailer->username }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group hide" id="balanceDiv">
                                                <label class="control-label col-md-4" for="retailer_id">{{ trans('users.lbl_user_daily_current_credit_limit') }}</label>
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control" name="current_balance" id="current_balance" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group hide" id="r_bal">
                                                <label class="control-label col-md-4" for="retailer_id">{{ trans('users.lbl_remaining_limit') }}</label>
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control" name="remaining_bal" id="remaining_bal" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-4" for="amount">{{ trans('common.payment_btn_add_limit') }}</label>
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control money-input" name="amount" id="amount">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-4"></div>
                                                <div class="col-md-4">
                                                    <button type="submit" class="btn btn-primary" id="btnSubmit"><i class="fa fa-save"></i>&nbsp;{{ trans('common.update_limit') }}</button>
                                                </div>
                                                <div class="col-md-4"></div>
                                            </div>

                                        </form>
                                    </div>
                                    <div class="col-md-3"></div>
                                </div>
                            </div>
                            <div class="tab-pane" id="Delete_limit">
                                <div class="row">
                                    <div class="col-md-3"></div>
                                    <div class="col-md-6">
                                        <form id="frmPayment" class="form-horizontal" action="{{ secure_url('limit/delete') }}" method="POST">
                                            {{ csrf_field() }}
                                            <div class="form-group">
                                                <label class="control-label col-md-4" for="retailer_id">{{ trans('myservice.lbl_choose_retailers') }}</label>
                                                <div class="col-md-8">
                                                    <select class="select-picker" name="id_retailer" id="id_retailer"  data-none-results-text="{{ trans('common.no_result_matched') }}" title="{{ trans('common.lbl_please_choose') }}"  data-size="5" data-live-search="true" >
                                                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                        @foreach($retailers as $retailer)
                                                            <?php
                                                            $rt_balance = \app\Library\AppHelper::get_daily_limit($retailer->id,$retailer->currency,true);
                                                            $r_bal = \app\Library\AppHelper::get_remaning_limit_balance($retailer->id);
                                                            ?>
                                                            <option value="{{ $retailer->id }}">{{ $retailer->username }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-4"></div>
                                                <div class="col-md-4">
                                                    <button type="submit" class="btn btn-primary" id="btnSubmit"><i class="fa fa-save"></i>&nbsp;{{ trans('common.btn_delete') }}</button>
                                                </div>
                                                <div class="col-md-4"></div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ secure_asset('vendor/select-picker/js/bootstrap-select.js') }}"></script>
    <script>
        $(document).ready(function () {
            $(".select-picker").selectpicker();

            $("#retailer_id").change(function () {
                if($(this).val() != ''){
                    var balance = $(this).find(':selected').data('balance');
                    var remaining_bal = $(this).find(':selected').data('remaining_bal');
                    if(balance == '')
                    {
                        $("#balanceDiv").removeClass('hide');
                        $("#r_bal").removeClass('hide');
                        $("#remaining_bal").val(remaining_bal);
                        $("#current_balance").val('0.00');
                    }
                    else
                    {
                        $("#balanceDiv").removeClass('hide');
                        $("#r_bal").removeClass('hide');
                        $("#current_balance").val(balance);
                        $("#remaining_bal").val(remaining_bal);
                    }

                }else{
                    $("#balanceDiv").addClass('hide');
                    $("#r_bal").addClass('hide');
                    $("#current_balance").val('');
                    $("#remaining_bal").val(remaining_bal);
                }
            }) ;

            $('#frmPayment').validate({
                // rules & options,
                rules: {
                    retailer_id: "required",
                    amount : 'required'
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
                                $("#progress-loader").LoadingOverlay("show");
                                $("#btnSubmit").html("<i class='fa fa-spinner fa-pulse'></i>&nbsp;{{ trans('common.btn_save_changes') }}...").attr('disabled', 'disabled');
                                form.submit();
                            },
                            "{{ strtolower(trans('common.btn_cancel')) }}": function () {
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection