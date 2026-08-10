@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "Log Viewer",'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{ trans('common.log_viewer') }}</h3>
                        <div class="pull-right">
                            <a class="btn btn-warning btn-sm" style="margin-top: -40px" href="{{ secure_url('topup-confirm-attempts') }}"><i class="fa fa-shield"></i>&nbsp;Topup Attempts</a>
                            <a class="btn btn-primary btn-sm" style="margin-top: -40px" href="{{ secure_url("log-viewer") }}"><i class="fa fa-book"></i>&nbsp;System Log Viewer</a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="col-md-10">
                            <form method="POST" id="search-form" class="form-inline" role="form">
                                <div class="form-group">
                                    <label for="from_date">{{ trans('common.filter_lbl_from') }}</label>
                                    <input type="text" class="form-control date" name="from_date" id="from_date" >
                                </div>
                                <div class="form-group">
                                    <label for="to_date">{{ trans('common.filter_lbl_to') }}</label>
                                    <input type="text" class="form-control date" name="to_date" id="to_date" >
                                </div>
                                <div class="form-group">
                                    <label for="type">{{ trans('common.type') }}</label>
                                    <select class="form-control" name="type" id="type">
                                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                        <option value="success">Success</option>
                                        <option value="info">Info</option>
                                        <option value="warning">Warning</option>
                                        <option value="error">Error</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="query">{{ trans('myservice.btn_search') }}</label>
                                    <input type="text" class="form-control" name="query" id="query" value="{{ old('query',request()->input('user')) }}">
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i>&nbsp;{{ trans('myservice.btn_search') }}</button>
                            </form>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ secure_url('clear/logs') }}" onclick="AppConfirmDelete(this.href,'{{ trans('service.confirm') }}','Do you want remove all logs?');return false;" class="btn btn-danger"><i class="fa fa-trash-alt"></i>&nbsp;{{ trans('common.clear_all_logs') }}</a>
                        </div>
                    </div>
                </div>
                <div class="panel" style="margin-top: -20px">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="logs-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>{{ trans('common.lbl_date') }}</th>
                                    <th>{{ trans('common.type') }}</th>
                                    <th>Channel</th>
                                    <th>{{ trans('users.lbl_user_name') }}</th>
                                    <th>{{ trans('common.title') }}</th>
                                    <th>Uri</th>
                                    <th>IP</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <script id="details-template" type="text/x-handlebars-template">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                <tr>
                                    <td>{{ trans('common.lbl_desc') }}</td>
                                    <td>@{{ description }}</td>
                                </tr>
                                <tr>
                                    <td>Request Info</td>
                                    <td id="jsonBeautify">@{{ request_info }}</td>
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
    <script src="{{ secure_asset('vendor/date-picker/jquery-ui.js') }}"></script>
    <script src="{{ secure_asset('vendor/select-picker/js/bootstrap-select.js') }}"></script><script src="{{ secure_asset('vendor/datatables/Buttons-1.5.1/js/buttons.bootstrap.min.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/app.js') }}"></script>
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
            var oTable = $('#logs-table').DataTable({
                "autoWidth": false,
                searching: false,
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "<span class='loader'></span>"
                },
                serverSide: true,
                ajax: {
                    url: '{{ secure_url('fetch/logs') }}',
                    data: function (d) {
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.query = $('#query').val();
                        d.type = $('#type').val();
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
                    {data: 'date', name: 'date'},
                    {data: 'log_type', name: 'log_type',className : "text-center", orderable :false, searchable : false},
                    {data: 'channel', name: 'channel',className : "text-center", orderable :false, searchable : false},
                    {data: 'username', name: 'users.username', orderable :false, searchable : false},
                    {data: 'title', name: 'title', orderable :false, searchable : false},
                    {data: 'uri', name: 'uri', orderable :false, searchable : false},
                    {data: 'ip', name: 'ip', orderable :false, searchable : false}
                ],
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
                        titleAttr: '{{ trans('common.download_as_excel') }}'
                    },
                    {
                        extend:    'reload',
                        text:      '<i class="fa fa-sync"></i>',
                        titleAttr: '{{ trans('common.refresh') }}'
                    }
                ],
                aaSorting: [[1, 'DESC']],
                drawCallback: function () {
                    $('[data-toggle="popover"]').popover();
                }
            });

            $('#search-form').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
            });

            // Add event listener for opening and closing details
            $('#logs-table tbody').on('click', 'td.details-control', function () {
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
