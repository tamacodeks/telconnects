@extends('layout.app')
@section('content')
    <style>
        .auth-method-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            min-height: 24px;
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .auth-method-badge--none {
            background: #fee4e2;
            color: #b42318;
            border-color: #fecdca;
        }

        .auth-method-badge--otp {
            background: #dcfae6;
            color: #087443;
            border-color: #abefc6;
        }

        .auth-method-badge--totp {
            background: #fff1d6;
            color: #b54708;
            border-color: #fedf89;
        }
    </style>
    @include('layout.breadcrumb',['data' => [
        ['name' => "Users",'url'=> '','active' => 'yes']
    ]
    ])
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-right m-b-20">
                                    <a href="{{ secure_url('user/update') }}" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;{{ trans('users.btn_add_user') }}</a>
                                    @if(in_array(auth()->user()->group_id, [1, 2]))
                                    <button type="button" class="btn btn-danger" id="run-reset-corrections-today">
                                        <i class="fa fa-play"></i>&nbsp;Run Reset Corrections
                                    </button>
                                    @endif
                                    @if(in_array(auth()->user()->group_id, [1, 2]))
                                    <button type="button" class="btn btn-warning" id="reset-transaction-corrections" data-toggle="modal" data-target="#resetCorrectionsModal">
                                        <i class="fa fa-history"></i>&nbsp;Reset Corrections
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <br>
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="users-table" class="table table-condensed">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>{{ trans('common.order_tbl_sl') }}</th>
                                            <th>{{ trans('users.lbl_user_status') }}</th>
                                            <th>{{ trans('common.authentication_method') }}</th>
                                            <th>{{ trans('users.lbl_tbl_cust_id') }}</th>
                                            <th>{{ trans('users.lbl_user_name') }}</th>
                                            <th>{{ trans('users.lbl_tbl_user_acc_type') }}</th>
                                            {{--<th>{{ trans('users.lbl_tbl_user_rep') }}</th>--}}
                                            <th>{{ trans('users.lbl_tbl_user_balance') }}</th>
                                            <th>{{ trans('users.lbl_user_credit_limit') }}</th>
                                            <th>{{ trans('users.lbl_tbl_user_created_on') }}</th>
                                            <th>{{ trans('common.mr_tbl_action') }}</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                                <script id="details-template" type="text/x-handlebars-template">
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <td>{{ trans('users.last_activity') }}</td>
                                            <td>@{{ last_online_at }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ trans('users.lbl_tbl_user_credit') }}</td>
                                            <td>@{{ credit_limit }}</td>
                                        </tr>
                                    </table>
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(in_array(auth()->user()->group_id, [1, 2]))
    <div class="modal fade" id="resetCorrectionsModal" tabindex="-1" role="dialog" aria-labelledby="resetCorrectionsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('common.btn_close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="resetCorrectionsModalLabel">Reset Transaction Corrections</h4>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Date: <span id="reset-date-label"></span></p>
                    <div class="form-group">
                        <label for="reset-from">From (time)</label>
                        <input type="time" id="reset-from" class="form-control input-sm" />
                    </div>
                    <div class="form-group">
                        <label for="reset-to">To (time)</label>
                        <input type="time" id="reset-to" class="form-control input-sm" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.btn_close') }}</button>
                    <button type="button" class="btn btn-warning" id="reset-transaction-submit">
                        <i class="fa fa-history"></i>&nbsp;Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    <link href="{{ secure_asset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <script src="{{ secure_asset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/Buttons-1.5.1/js/buttons.bootstrap.min.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/Buttons-1.5.1/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/app.js') }}"></script>
    <script src="{{ secure_asset('vendor/common/handlebars-v4.0.11.js') }}"></script>
    <script>
        @if(auth()->user()->group_id == 2)
        function syncPriceLists(url,anim)
        {
            $("#users-table").LoadingOverlay('show');
            $(anim).html("<i class='fa fa-refresh fa-spin'></i>");
            $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                /**
                 * A function to be called if the request fails.
                 */
                error: function(jqXHR, textStatus, errorThrown) {
                    $("#users-table").LoadingOverlay('hide');
                    console.log(jqXHR);
                    $(anim).html("<i class='fa fa-sync-alt'></i>");
                },

                /**
                 * A function to be called if the request succeeds.
                 */
                success: function(data, textStatus, jqXHR) {
                    console.log(data);
                    $("#users-table").LoadingOverlay('hide');
                    $(anim).html("<i class='fa fa-sync-alt'></i>");
                    $.alert({
                        title: "Information",
                        content: data.message,
                        buttons: {
                            "{{ trans('common.btn_close') }}": function () {

                            }
                        },
                        backgroundDismiss: true, // this will just close the modal
                        theme: 'material',
                        animation: 'zoom',
                        closeAnimation: 'bottom',
                        escapeKey: '{{ trans('common.btn_close') }}',
                        type: 'success',
                        icon: 'fa fa-check-circle'
                    });
                }
            });
        }
        @endif
        function runResetCorrectionsToday() {
            if (!confirm('Run reset corrections for today?')) {
                return;
            }
            $("#users-table").LoadingOverlay('show');
            $.ajax({
                url: "{{ secure_url('users/run-reset-corrections-today') }}",
                type: "POST",
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error: function(jqXHR) {
                    $("#users-table").LoadingOverlay('hide');
                    var message = 'Unable to run reset corrections.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        message = jqXHR.responseJSON.message;
                    }
                    $.alert({
                        title: "Information",
                        content: message,
                        buttons: {
                            "{{ trans('common.btn_close') }}": function () {}
                        },
                        backgroundDismiss: true,
                        theme: 'material',
                        animation: 'zoom',
                        closeAnimation: 'bottom',
                        escapeKey: '{{ trans('common.btn_close') }}',
                        type: 'red',
                        icon: 'fa fa-exclamation-circle'
                    });
                },
                success: function(data) {
                    $("#users-table").LoadingOverlay('hide');
                    $.alert({
                        title: "Information",
                        content: (data.message || ''),
                        buttons: {
                            "{{ trans('common.btn_close') }}": function () {}
                        },
                        backgroundDismiss: true,
                        theme: 'material',
                        animation: 'zoom',
                        closeAnimation: 'bottom',
                        escapeKey: '{{ trans('common.btn_close') }}',
                        type: 'success',
                        icon: 'fa fa-check-circle'
                    });
                }
            });
        }
        function resetTransactionCorrections() {
            var fromVal = $("#reset-from").val();
            var toVal = $("#reset-to").val();
            if (!fromVal || !toVal) {
                $.alert({
                    title: "Information",
                    content: "Please select From and To date/time.",
                    buttons: {
                        "{{ trans('common.btn_close') }}": function () {}
                    },
                    backgroundDismiss: true,
                    theme: 'material',
                    animation: 'zoom',
                    closeAnimation: 'bottom',
                    escapeKey: '{{ trans('common.btn_close') }}',
                    type: 'red',
                    icon: 'fa fa-exclamation-circle'
                });
                return;
            }
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            var fromDateTime = yyyy + '-' + mm + '-' + dd + ' ' + fromVal + ':00';
            var toDateTime = yyyy + '-' + mm + '-' + dd + ' ' + toVal + ':00';
            $("#users-table").LoadingOverlay('show');
            $.ajax({
                url: "{{ secure_url('users/reset-transaction-corrections') }}",
                type: "POST",
                dataType: "json",
                data: {
                    from: fromDateTime,
                    to: toDateTime
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error: function(jqXHR) {
                    $("#users-table").LoadingOverlay('hide');
                    var message = 'Unable to reset corrections.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        message = jqXHR.responseJSON.message;
                    }
                    $.alert({
                        title: "Information",
                        content: message,
                        buttons: {
                            "{{ trans('common.btn_close') }}": function () {}
                        },
                        backgroundDismiss: true,
                        theme: 'material',
                        animation: 'zoom',
                        closeAnimation: 'bottom',
                        escapeKey: '{{ trans('common.btn_close') }}',
                        type: 'red',
                        icon: 'fa fa-exclamation-circle'
                    });
                },
                success: function(data) {
                    $("#users-table").LoadingOverlay('hide');
                    var rangeText = '';
                    if (data.from && data.to) {
                        rangeText = '<br>Range: ' + data.from + ' to ' + data.to;
                    }
                    $.alert({
                        title: "Information",
                        content: (data.message || '') + '<br>Total updated: ' + (data.rows_updated || 0) + rangeText,
                        buttons: {
                            "{{ trans('common.btn_close') }}": function () {}
                        },
                        backgroundDismiss: true,
                        theme: 'material',
                        animation: 'zoom',
                        closeAnimation: 'bottom',
                        escapeKey: '{{ trans('common.btn_close') }}',
                        type: 'success',
                        icon: 'fa fa-check-circle'
                    });
                    $("#resetCorrectionsModal").modal('hide');
                }
            });
        }
        $(document).ready(function () {
            $("#run-reset-corrections-today").on('click', function () {
                runResetCorrectionsToday();
            });

            setTimeout(function () {
                $("#wrapper").addClass('toggled');
            },1000);
            var template = Handlebars.compile($("#details-template").html());
            function renderAuthMethodBadge(row, type) {
                if (type === 'display' && row.auth_method) {
                    return row.auth_method;
                }

                var method = parseInt(row.method, 10);
                if (isNaN(method)) {
                    method = 0;
                }

                if (type !== 'display') {
                    return method;
                }

                var methodMap = {
                    1: {
                        label: '1 - IP OTP',
                        className: 'auth-method-badge auth-method-badge--otp',
                        title: 'OTP is required when the login IP changes'
                    },
                    2: {
                        label: '2 - 2FA',
                        className: 'auth-method-badge auth-method-badge--totp',
                        title: 'Authenticator 2FA is used when enabled and verified'
                    }
                };
                var methodData = methodMap[method] || {
                    label: '0 - No Auth',
                    className: 'auth-method-badge auth-method-badge--none',
                    title: 'No extra authentication step'
                };

                return '<span class="' + methodData.className + '" title="' + methodData.title + '">' + methodData.label + '</span>';
            }

            var table = $('#users-table').DataTable({
                "autoWidth": false,
                "pageLength": "-1",
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "{{ trans('common.processing') }}"
                },
                serverSide: true,
                ajax: '{{ secure_url('fetch/users') }}',
                columns: [
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "searchable":     false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    {
                        "className":      '',
                        "orderable":      false,
                        "searchable":     false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    {data: 'status_indicator', name: 'users.status_indicator',"orderable" : false,"searchable": false,"className" : "text-center"},
                    {
                        data: null,
                        name: 'users.method',
                        "orderable" : true,
                        "searchable": false,
                        "className" : "text-center",
                        render: function (data, type, row) {
                            return renderAuthMethodBadge(row || {}, type);
                        }
                    },
                    {data: 'cust_id', name: 'users.cust_id'},
                    {data: 'username', name: 'users.username'},
                    {data: 'name', name: 'user_groups.name'},
//                    {data: 'representative', name: 'users.representative',orderable : false,searchable: false},
                    {data: 'balance', name: 'users.balance',orderable : false,searchable: false},
                    {data: 'credit_limit', name: 'credit_limit',orderable : false,searchable: false},
                    {data: 'created_at', name: 'users.created_at'},
                    {data: 'action', name: 'users.action',orderable : false,searchable: false}
                ],
                order: [[7, 'DESC']],
                dom: 'Bfrtip',
                // Configure the drop down options.
                lengthMenu: [
                    [ 10, 25, 50, -1 ],
                    [ '10 {{ trans('users.records') }}', '25 {{ trans('users.records') }}', '50 {{ trans('users.records') }}', '{{ trans('users.show_all') }}' ]
                ],
                // Add to buttons the pageLength option.
                buttons: [
                    {
                        extend:    'pageLength',
                    },
                    {
                        extend:    'excel',
                        text:      '<i class="fa fa-file-excel"></i>',
                        titleAttr: '{{ trans('common.download_as_excel') }}',
                        exportOptions: {
                            columns: [ 2,3,4,5,6,7]
                        }
                    },
                    {
                        extend:    'reload',
                        text:      '<i class="fa fa-sync"></i>',
                        titleAttr: '{{ trans('common.refresh') }}'
                    }
                ]
            });

            table.on('order.dt search.dt', function () {
                table.column(1, {search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();
            // Add event listener for opening and closing details
            $('#users-table tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row( tr );

                if ( row.child.isShown() ) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    // Open this row
                    row.child( template(row.data()) ).show();
                    tr.addClass('shown');
                }
            });

            $("#reset-transaction-submit").on('click', function () {
                resetTransactionCorrections();
            });

            $('#resetCorrectionsModal').on('show.bs.modal', function () {
                var today = new Date();
                var yyyy = today.getFullYear();
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var dd = String(today.getDate()).padStart(2, '0');
                $("#reset-date-label").text(yyyy + '-' + mm + '-' + dd);
                $("#reset-from").val('00:00');
                $("#reset-to").val('23:59');
            });
        });
    </script>
@endsection


