@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('common.payments'),'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 m-t-20">
                <div class="panel" style="margin-top: -20px">
                    <div class="panel-body">
                        <div class="table-responsive">
                            @if(auth()->user()->group_id == 2)
                                <table id="payments-table" class="table table-condensed">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ trans('common.lbl_date') }}</th>
                                        <th>{{ trans('users.lbl_user_name') }}</th>
                                        <th>{{ trans('common.payment_tbl_paid_amount') }}</th>
                                        <th>{{ trans('common.payment_tbl_prev_bal') }}</th>
                                        <th>{{ trans('common.payment_tbl_cur_bal') }}</th>
                                        <th>{{ trans('common.payment_tbl_comment') }}</th>
                                        <th>{{ trans('common.order_tbl_receiver_name') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php($sl=1)
                                    @forelse($payments as $payment)
                                        <tr>
                                            <td>{{ $sl }}</td>
                                            <td>{{ $payment['date'] }}</td>
                                            <td>{{ $payment['user'] }}</td>
                                            <td>{{ $payment['amount'] }}</td>
                                            <td>{{ $payment['previous_balance'] }}</td>
                                            <td>{{ $payment['balance'] }}</td>
                                            <td>{{ $payment['description'] }}</td>
                                            <td>{{ $payment['received_by'] }}</td>
                                        </tr>
                                        @php($sl++)
                                    @empty
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td colspan="3" class="text-center">{{ trans('common.search_no_results') }}</td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            @else
                                <table id="payments-table" class="table table-condensed">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>{{ trans('common.order_tbl_sl') }}</th>
                                        <th>{{ trans('common.lbl_date') }}</th>
                                        <th>{{ trans('common.payment_tbl_cust_id') }}</th>
                                        <th>{{ trans('common.order_tbl_retailer') }}</th>
                                        <th>{{ trans('common.payment_tbl_paid_amount') }}</th>
                                        <th>{{ trans('common.payment_tbl_prev_bal') }}</th>
                                        <th>{{ trans('common.payment_tbl_cur_bal') }}</th>
                                    </tr>
                                    </thead>
                                </table>
                            @endif
                        </div>
                        <script id="details-template" type="text/x-handlebars-template">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                <tr>
                                    <td>{{ trans('common.payment_tbl_comment') }}</td>
                                    <td>@{{ description }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </script>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @if(auth()->user()->group_id == 2)

    @else
        <link href="{{ secure_asset('vendor/datatables/datatables.css') }}" rel="stylesheet">
        <script src="{{ secure_asset('vendor/datatables/datatables.js') }}"></script>
        <script src="{{ secure_asset('vendor/datatables/app.js') }}"></script>
        <script src="{{ secure_asset('vendor/date-picker/jquery-ui.js') }}"></script>
        <script src="{{ secure_asset('vendor/select-picker/js/bootstrap-select.js') }}"></script>
        <script src="{{ secure_asset('vendor/common/handlebars-v4.0.11.js') }}"></script>
        <script>
            $( function() {
                $( ".date" ).datepicker({
                    showButtonPanel: true,
                    changeMonth: true,
                    changeYear: true,
                    dateFormat : "yy-mm-dd",
                    showAnim : "slideDown"
                });
            } );
            $(document).ready(function () {
                var template = Handlebars.compile($("#details-template").html());
                $(".select-picker").selectpicker();
                var oTable = $('#payments-table').DataTable({
                    "autoWidth": false,
                    searching: false,
                    "pageLength": "{{ PER_PAGE }}",
                    processing: "<span class='loader'></span>",
                    language: {
                        "processing": "<span class='loader'></span>"
                    },
                    serverSide: true,
                    ajax: {
                        url: '{{ secure_url('fetch/my/payments') }}',
                        data: function (d) {
                            d.retailer_id = $('#retailer_id').val();
                            d.from_date = $('#from_date').val();
                            d.to_date = $('#to_date').val();
                        }
                    },
                    columns: [
                        {
                            "className":      'details-control',
                            orderable:      false,
                            searchable:     false,
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
                        {data: 'date', name: 'payments.date'},
                        {data: 'cust_id', name: 'users.cust_id',searchable : false,orderable : false},
                        {data: 'username', name: 'users.username',searchable : false,orderable : false},
                        {data: 'amount', name: 'payments.amount',searchable : false,orderable : false},
                        {data: 'prev_bal', name: 'transactions.prev_bal',searchable : false,orderable : false},
                        {data: 'balance', name: 'transactions.balance',searchable : false,orderable : false}
                    ],
                    dom: 'Bfrtip',
                    // Configure the drop down options.
                    lengthMenu: [
                        [ 10, 25, 50, -1 ],
                        [ '10 {{ trans('users.records') }}', '25 {{ trans('users.records') }}', '50 {{ trans('users.records') }}', '{{ trans('users.show_all') }}' ]
                    ],
                    aaSorting: [[2, 'DESC']],
                    // Add to buttons the pageLength option.
                    buttons: [
                        'pageLength',
                        {
                            extend:    'excel',
                            text:      '<i class="fa fa-file-excel"></i>',
                            titleAttr: '{{ trans('common.download_as_excel') }}'
                        },
                        {
                            extend:    'reload',
                            text:      '<i class="fa fa-sync"></i>',
                            titleAttr: '{{ trans('common.refresh') }}'
                        }
                    ]
                });

                $('#search-form').on('submit', function(e) {
                    oTable.draw();
                    e.preventDefault();
                });

                oTable.on('order.dt search.dt', function () {
                    oTable.column(1, {search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
                        cell.innerHTML = i + 1;
                    });
                }).draw();
                // Add event listener for opening and closing details
                $('#payments-table tbody').on('click', 'td.details-control', function () {
                    var tr = $(this).closest('tr');
                    var row = oTable.row( tr );

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
            });
        </script>
    @endif
@endsection