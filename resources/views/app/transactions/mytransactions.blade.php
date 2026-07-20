@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('common.breadcrumb_trans_history'),'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{ trans('common.dashboard_view_trans') }}</h3>
                    </div>
                    <div class="panel-body">
                        <form method="POST" id="search-form" class="form-inline" role="form">
                            <div class="form-group">
                                <label for="service_id">{{ trans('common.filter_lbl_service') }}</label>
                                <select name="service_id" id="service_id" class="select-picker" multiple>
                                    @foreach($services as $service)
                                        @if($service->name != 'Topup')
                                            <option value="{{ $service->id }}">
                                                @if($service->name == 'Tama Topup')
                                                    TopUp
                                                @else
                                                    {{ $service->name }}
                                                @endif
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="from_date">{{ trans('common.filter_lbl_from') }}</label>
                                <input type="text" class="form-control date" name="from_date" id="from_date" >
                            </div>
                            <div class="form-group">
                                <label for="to_date">{{ trans('common.filter_lbl_to') }}</label>
                                <input type="text" class="form-control date" name="to_date" id="to_date" >
                            </div>
                            <div class="form-group">
                                <label for="query">{{ trans('myservice.btn_search') }}</label>
                                <input type="text" class="form-control" name="query" id="query" value="{{ old('query',request()->input('user')) }}">
                            </div>

                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i>&nbsp;{{ trans('myservice.btn_search') }}</button>
                        </form>
                    </div>
                </div>
                <div class="panel" style="margin-top: -20px">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="transactions-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>{{ trans('common.lbl_date') }}</th>
                                    {{--<th>{{ trans('common.order_tbl_retailer') }}</th>--}}
                                    <th>{{ trans('common.transaction_tbl_service') }}</th>
                                    <th>{{ trans('common.transaction_tbl_trans_id') }}</th>
                                    <th>{{ trans('common.lbl_product') }}</th>
                                    <th>{{ trans('common.transaction_tbl_pub_price') }}</th>
                                    @if(in_array(auth()->user()->group_id,[1,2,3]))
                                        <th>{{ trans('common.transaction_tbl_res_price') }}</th>
                                    @else
                                        <th>{{ trans('myservice.sale_price') }}</th>
                                    @endif
                                    <th>{{ trans('common.transaction_tbl_sale_margin') }}</th>
                                    <th>{{ trans('common.order_status') }}</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th colspan="3"></th>
                                    <th class="text-left">{{ trans('common.lbl_total') }}:</th>
                                    @if(in_array(auth()->user()->group_id,[1,2,3]))
                                        <th></th>
                                    @else

                                    @endif
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <script id="details-template" type="text/x-handlebars-template">
                            <table class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>{{ trans('common.transaction_tbl_cust_id') }}</th>
                                    <th>{{ trans('common.order_tbl_sender_name') }}</th>
                                    <th>{{ trans('common.order_tbl_sender_number') }}</th>
                                    <th>{{ trans('common.order_tbl_receiver_name') }}</th>
                                    <th>{{ trans('common.order_tbl_receiver_number') }}</th>
                                    <th>{{ trans('common.transaction_pin') }}</th>
                                    <th>{{ trans('common.transaction_serial') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>@{{  cust_id }}</td>
                                    <td>@{{  sender_first_name }}</td>
                                    <td>@{{  sender_mobile }}</td>
                                    <td>@{{  receiver_first_name }}</td>
                                    <td>@{{  mobile }}</td>
                                    <td>@{{  tama_pin }}</td>
                                    <td>@{{  tama_serial }}</td>
                                </tr>
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
            var oTable = $('#transactions-table').DataTable({
                "autoWidth": false,
                searching: false,
//                "pageLength": "-1",
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "{{ trans('common.processing') }}...<span class='loader'></span>"
                },
                serverSide: true,
                ajax: {
                    url: '{{ url('fetch/my/transactions') }}',
                    data: function (d) {
                        d.service_id = $('#service_id').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.query = $('#query').val();
                    }
                },
                columns: [
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "searchable":     false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    {data: 'date', name: 'orders.date'},
//                    {data: 'username', name: 'users.username'},
                    {data: 'service_name', name: 'service_name',orderable:false,searchable:false},
                    {data: 'txn_id', name: 'txn_id',orderable:false},
                    {data: 'product_name', name: 'product_name',orderable:false,searchable:false},
                    {data: 'public_price', name: 'public_price',orderable:false,searchable:false, className: "sum" },
                    {data: 'order_amount', name: 'order_amount',orderable:false,searchable:false, className: "sum" },
                    {data: 'sale_margin', name: 'sale_margin',orderable:false,searchable:false,className: "sum"},
                    {data: 'order_status_name', name: 'order_status_name',orderable:false,searchable:false}
                ],
                dom: 'Bfrtip',
                // Configure the drop down options.
                lengthMenu: [
                    [ 10, 25, 50, -1 ],
                    [ '10 {{ trans('users.records') }}', '25 {{ trans('users.records') }}', '50 {{ trans('users.records') }}', '{{ trans('users.show_all') }}' ]
                ],
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
                ],
                aaSorting: [[1, 'DESC']],
                "footerCallback": function(row, data, start, end, display) {
                    var api = this.api();

                    api.columns('.sum', { page: 'current' }).every(function () {
                        var sum = this
                            .data()
                            .reduce(function (a, b) {
                                var x = parseFloat(a) || 0;
                                var y = parseFloat(b) || 0;
                                return x + y;
                            }, 0);
//                        console.log(sum); //alert(sum);
                        $(this.footer()).html(sum.toFixed(2));
                    });
                }
            });

            $('#search-form').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
            });

            // Add event listener for opening and closing details
            $('#transactions-table tbody').on('click', 'td.details-control', function () {
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

            setTimeout(function () {
                $("#wrapper").addClass('toggled');
            },1000)


        });
    </script>
@endsection