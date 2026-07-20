@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "Orders",'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{ trans('common.dashboard_view_orders') }}</h3>
                    </div>
                    <div class="panel-body">
                        <form method="POST" id="search-form" class="form-inline" role="form">
                            <div class="form-group">
                                <label for="service_id">{{ trans('common.filter_lbl_service') }}</label>
                                <select name="service_id" id="service_id" class="select-picker" multiple>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
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
                                <input type="text" class="form-control" placeholder="{{ trans('myservice.btn_search') }}" name="query" id="query" value="{{ old('query',request()->input('user')) }}">
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i>&nbsp;{{ trans('myservice.btn_search') }}</button>
                        </form>
                    </div>
                </div>
                <div class="panel" style="margin-top: -20px">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="orders-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>{{ trans('common.lbl_date') }}</th>
                                    {{--<th>{{ trans('common.order_tbl_retailer') }}</th>--}}
                                    <th>{{ trans('common.transaction_tbl_service') }}</th>
                                    <th>{{ trans('common.lbl_product') }}</th>
                                    <th>{{ trans('common.order_tbl_price') }}</th>
                                    <th>{{ trans('common.order_status') }}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <script id="details-template" type="text/x-handlebars-template">
                            <table class="table table-bordered table-striped">
                                <thead>
                                <tr>
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
            var oTable = $('#orders-table').DataTable({
                "autoWidth": false,
                searching: false,
//                "pageLength": "-1",
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "<span class='loader'></span>"
                },
                serverSide: true,
                ajax: {
                    url: '{{ secure_url('fetch/my/orders') }}',
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
                    {data: 'date', name: 'orders.date',searchable:false},
//                    {data: 'username', name: 'users.username',orderable:false},
                    {data: 'service_name', name: 'service_name',searchable:false,orderable:false},
                    {data: 'product_name', name: 'product_name',searchable:false,orderable:false},
                    {data: 'order_amount', name: 'order_amount',searchable:false,orderable:false},
                    {data: 'order_status_name', name: 'order_status_name',searchable:false,orderable:false}
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
                aaSorting: [[1, 'DESC']]
            });

            $('#search-form').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
            });

            // Add event listener for opening and closing details
            $('#orders-table tbody').on('click', 'td.details-control', function () {
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