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
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{ trans('common.payments') }}</h3>
                        @if(auth()->user()->group_id != 4)
                            <div class="pull-right" style="margin-top: -23px;">
                                <a href="{{ secure_url('payment/add') }}" class="btn btn-theme btn-sm"><i class="fa fa-plus-circle"></i>&nbsp;{{ trans('common.payment_btn_add_payment') }}</a>
                            </div>
                        @endif
                    </div>
                    <div class="panel-body">
                        <form method="POST" id="search-form" class="form-inline" role="form">
                            @if(auth()->user()->group_id != 4)
                                <div class="form-group">
                                    <label for="retailer_id">{{ trans('common.order_tbl_retailer') }}</label>
                                    <select data-live-search="true" name="retailer_id" id="retailer_id" class="select-picker" multiple>
                                        @foreach($retailers as $retailer)
                                            <option value="{{ $retailer->id }}" @if(request()->get('user') == $retailer->username) selected @endif>{{ $retailer->username }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="form-group">
                                <label for="from_date">{{ trans('common.filter_lbl_from') }}</label>
                                <input type="text" class="form-control date" name="from_date" id="from_date" >
                            </div>
                            <div class="form-group">
                                <label for="to_date">{{ trans('common.filter_lbl_to') }}</label>
                                <input type="text" class="form-control date" name="to_date" id="to_date" >
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i>&nbsp;{{ trans('myservice.btn_search') }}</button>
                        </form>
                    </div>
                </div>
                <div class="panel" style="margin-top: -20px">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="payments-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>{{ trans('common.order_tbl_sl') }}</th>
                                    <th>{{ trans('common.lbl_intiated_date') }}</th>
                                    <th>{{ trans('common.lbl_updated_date') }}</th>
                                    <th>{{ trans('common.payment_tbl_cust_id') }}</th>
                                    <th>{{ trans('common.order_tbl_retailer') }}</th>
                                    <th>{{ trans('common.payment_tbl_paid_amount') }}</th>
                                    <th>{{ trans('common.payment_tbl_prev_bal') }}</th>
                                    <th>{{ trans('common.payment_tbl_cur_bal') }}</th>
                                    <th>{{ trans('common.payment_tbl_comment') }}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <script id="details-template" type="text/x-handlebars-template">
                            <table class="table table-bordered">
                                <tbody>

                                </tbody>
                            </table>
                        </script>
                    </div>
                </div>

            </div>
        </div>
    </div>
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
                    url: '{{ secure_url('fetch/payments') }}',
                    data: function (d) {
                        d.retailer_id = $('#retailer_id').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                    }
                },
                columns: [
                    {
                        "className":      '',
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
                    {data: 'payment_date', name: 'transactions.date'},
                    {data: 'cust_id', name: 'users.cust_id',searchable : false,orderable : false},
                    {data: 'username', name: 'users.username',searchable : false,orderable : false},
                    {data: 'amount', name: 'payments.amount',searchable : false,orderable : false},
                    {data: 'prev_bal', name: 'transactions.prev_bal',searchable : false,orderable : false},
                    {data: 'balance', name: 'transactions.balance',searchable : false,orderable : false},
                    { data:'prev_bal',
                        render: function(data, type, full) {
                            var ret = '';
                            if (!full.prev_bal) {
                                ret += '{{ trans('common.payment') }} '+ full.amount +'  € {{ trans('common.initiated') }} '+ full.username +' {{ trans('common.account_login') }}';
                            } else {
                                ret += full.description;
                            }
                            return ret;
                        }
                    }
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

            oTable.on('order.dt search.dt', function () {
                oTable.column(1, {search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();
            $('#search-form').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
            });

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
@endsection