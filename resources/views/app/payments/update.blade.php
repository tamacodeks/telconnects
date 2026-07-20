@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('common.payments'),'url'=> secure_url('payments'),'active' => 'no'],
        ['name' => trans('common.payment_btn_add_payment'),'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body" id="progress-loader">
                        <div class="row">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <form id="frmPayment" class="form-horizontal" action="{{ secure_url('payment/update') }}" method="POST">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label class="control-label col-md-4" for="retailer_id">{{ trans('myservice.lbl_choose_retailers') }}</label>
                                        <div class="col-md-8">
                                            <select class="select-picker" name="retailer_id" id="retailer_id" data-live-search="true">
                                                <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                @foreach($retailers as $retailer)
                                                    <option value="{{ $retailer->id }}" data-balance="{{ $retailer->balance }}" data-user_id="{{ $retailer->id }}">{{ $retailer->username }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group hide" id="balanceDiv">
                                        <label class="control-label col-md-4" for="retailer_id">{{ trans('users.lbl_user_transaction_current_balance') }}</label>
                                        <div class="col-md-8">
                                            <p style="font-size: 22px;color: #ff0000;" id="current_balance"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4" for="amount">{{ trans('common.payment_frm_amount') }}</label>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control money-input" name="amount" id="amount">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4" for="description">{{ trans('common.lbl_desc') }}</label>
                                        <div class="col-md-6">
                                            <textarea class="form-control" name="description" id="description"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-4"></div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-primary" id="btnSubmit"><i class="fa fa-save"></i>&nbsp;{{ trans('common.update_payment') }}</button>
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
            <div class="col-md-12 m-t-5">
                <div class="panel">
                    <div class="panel-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="active"><a href="#Intiated_Payment" role="tab" data-toggle="tab">Intiated Payment</a></li>
                            <li ><a href="#Completed_Payment" role="tab" data-toggle="tab">Completed Payment</a></li>

                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="Intiated_Payment">
                                <div class="table-responsive">
                                    <table id="payments-table" class="table table-condensed">
                                        <thead>
                                        <tr>
                                            <th>{{ trans('common.order_tbl_sl') }}</th>
                                            <th>{{ trans('common.lbl_date') }}</th>
                                            <th>{{ trans('common.payment_lbl_reseller') }}</th>
                                            <th>{{ trans('common.payment_tbl_comment') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $sl=1;
                                        ?>
                                        @forelse($intiated_payment as $payment)
                                            <tr>
                                                <td>{{ $sl }}</td>
                                                <td>{{ $payment->date }}</td>
                                                <td>{{ $payment->username }}</td>
                                                <td>{{ trans('common.payment') }} {{ $payment->amount }}  €  {{ trans('common.initiated') }} {{ $payment->username }} {{ trans('common.account_login') }}</td>
                                            </tr>
                                            @php($sl++)
                                        @empty
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td colspan="3" class="text-center">{{ trans('common.payment_no_payments') }}</td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="Completed_Payment">
                                <div class="table-responsive">
                                    <table id="payments-table" class="table table-condensed">
                                        <thead>
                                        <tr>
                                            <th>{{ trans('common.order_tbl_sl') }}</th>
                                            <th>{{ trans('common.lbl_date') }}</th>
                                            <th>{{ trans('common.payment_lbl_reseller') }}</th>
                                            <th>{{ trans('common.payment_tbl_paid_amount') }}</th>
                                            <th>{{ trans('common.payment_tbl_prev_bal') }}</th>
                                            <th>{{ trans('common.payment_tbl_cur_bal') }}</th>
                                            <th>{{ trans('common.order_tbl_receiver_name') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $sl=1;
                                        ?>
                                        @forelse($payments as $payment)
                                            <tr>
                                                <td>{{ $sl }}</td>
                                                <td>{{ $payment->date }}</td>
                                                <td>{{ $payment->username }}</td>
                                                <td>{{ $payment->amount }}</td>
                                                <td>{{ $payment->prev_bal }}</td>
                                                <td>{{ $payment->balance }}</td>
                                                <td>{{ optional(\App\User::find($payment->received_by))->username }}</td>
                                            </tr>
                                            @php($sl++)
                                        @empty
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td colspan="2" class="text-center">{{ trans('common.payment_no_payments') }}</td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        @endforelse
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
    <link href="{{ secure_asset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <script src="{{ secure_asset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/app.js') }}"></script>
    <script src="{{ secure_asset('vendor/select-picker/js/bootstrap-select.js') }}"></script>
    <script>
        $(document).ready(function () {
            $(".select-picker").selectpicker();
            $("#retailer_id").change(function () {
                if($(this).val() != ''){
                    var balance = $(this).find(':selected').data('balance');
                    var user_id = $(this).find(':selected').data('user_id');
                    $("#balanceDiv").removeClass('hide');
                    $("#current_balance").html(balance);
                    // $("option",this).each(function () {
                    //     var tmp_user_id = $(this).data('user_id');
                    //     $(".user_tr_"+tmp_user_id).addClass('hide');
                    // });
                    // $(".user_tr_"+user_id).removeClass('hide');
                }else{
                    $("#balanceDiv").addClass('hide');
                    $("#current_balance").html('');
                    // $("option",this).each(function () {
                    //     var tmp_user_id = $(this).data('user_id');
                    //     $(".user_tr_"+tmp_user_id).addClass('hide');
                    // });
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