@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('myservice.pin_usage_history'),'url'=> secure_url('cc-pin-history'),'active' => 'no'],
        ['name' => trans('myservice.my_tickets'),'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="row m-t-10">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{ trans('myservice.my_tickets') }}</h3>
                    </div>
                    <div class="panel-body">
                        <form method="POST" id="search-form" class="form-inline" role="form">
                            <div class="form-group">
                                <label for="type">{{ trans('myservice.status') }}</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                    <option value="open">{{ trans('myservice.open') }}</option>
                                    <option value="closed">{{ trans('myservice.closed') }}</option>
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
                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i>&nbsp;{{ trans('myservice.btn_search') }}</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="pin-history-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ trans('myservice.lbl_card_name') }}</th>
                                    {{--<th>{{ trans('myservice.face_value') }}</th>--}}
                                    <th>{{ trans('sale.serial') }}</th>
                                    <th>{{ trans('sale.pin') }}</th>
                                    <th>{{ trans('myservice.to') }}</th>
                                    <th>{{ trans('common.type') }}</th>
                                    <th>{{ trans('myservice.status') }}</th>
                                    <th>{{ trans('common.created_at') }}</th>
                                    <th>{{ trans('common.trans_tbl_action') }}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <link href="{{ secure_asset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <script src="{{ secure_asset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/app.js') }}"></script>
    <script src="{{ secure_asset('vendor/date-picker/jquery-ui.js') }}"></script>
    <script>
        var request, oTable;
        $(document).ready(function () {
            $( function() {
                $( ".date" ).datepicker({
                    showButtonPanel: true,
                    changeMonth: true,
                    changeYear: true,
                    dateFormat : "yy-mm-dd",
                    showAnim : "slideDown"
                });
            } );
            oTable = $('#pin-history-table').DataTable({
                "autoWidth": false,
                pageLength: "-1",
                searching: true,
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "<span class='loader'></span>",
                    paginate: {
                        next: '{!!  trans('pagination.next') !!}',
                        previous: '{!! trans('pagination.previous') !!}'
                    }
                },
                serverSide: true,
                ajax: {
                    url: '{{ secure_url('tickets/fetch') }}',
                    data: function (d) {
                        d.type = $('#type').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                    }
                },
                columns: [
                    {
                        "className": '',
                        "orderable": false,
                        "searchable": false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {data: 'name', name: 'pin_histories.name'},
                    // {data: 'face_value', name: 'calling_card_pins.face_value'},
                    {data: 'serial', name: 'pin_histories.serial', orderable: false},
                    {data: 'pin', name: 'pin_histories.pin', orderable: false, searchable: false},
                    {data: 'to_user', name: 'to_user', orderable: false, searchable: false},
                    {data: 'issue_type', name: 'issue_type', orderable: false, searchable: false},
                    {data: 'status', name: 'status', searchable: false, orderable: false},
                    {data: 'created_at', name: 'tickets.created_at', searchable: false},
                    {data: 'action', name: 'action', searchable: false, orderable: false}
                ],
                dom: 'Bfrtip',
                // Configure the drop down options.
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10 {{ trans('users.records') }}', '25 {{ trans('users.records') }}', '50 {{ trans('users.records') }}', '{{ trans('users.show_all') }}']
                ],
                // Add to buttons the pageLength option.
                buttons: [
                    'pageLength',
                    {
                        extend: 'excel',
                        text: '<i class="fa fa-file-excel"></i>',
                        titleAttr: '{{ trans('common.download_as_excel') }}'
                    },
                    {
                        extend: 'reload',
                        text: '<i class="fa fa-sync"></i>',
                        titleAttr: '{{ trans('common.refresh') }}'
                    }
                ],
                aaSorting: [[8, 'DESC']]
            });

            $('#search-form').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
            });
            oTable.on('order.dt search.dt', function () {
                oTable.column(0, {search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();
        });
    </script>
@endsection
