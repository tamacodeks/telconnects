@extends('layout.app')
@section('content')
    @include('layout.breadcrumb', ['data' => [
        ['name' => 'TamaTopup Confirm Attempts', 'url' => '', 'active' => 'yes'],
    ]])

    <link href="{{ asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/datatables.css') }}" rel="stylesheet">

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{ $page_title }}</h3>
                    </div>
                    <div class="panel-body">
                        <form method="GET" id="search-form" class="form-inline" role="form">
                            <div class="form-group">
                                <label for="from_date">{{ trans('common.filter_lbl_from') }}</label>
                                <input type="text" class="form-control date" name="from_date" id="from_date">
                            </div>
                            <div class="form-group">
                                <label for="to_date">{{ trans('common.filter_lbl_to') }}</label>
                                <input type="text" class="form-control date" name="to_date" id="to_date">
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" name="status" id="status">
                                    <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                    <option value="PROCESSING">PROCESSING</option>
                                    <option value="API_REQUEST_STARTED">API_REQUEST_STARTED</option>
                                    <option value="API_RESPONSE_RECEIVED">API_RESPONSE_RECEIVED</option>
                                    <option value="SUCCESS">SUCCESS</option>
                                    <option value="BLOCKED_DUPLICATE">BLOCKED_DUPLICATE</option>
                                    <option value="SECURITY_BLOCKED">SECURITY_BLOCKED</option>
                                    <option value="UNKNOWN">UNKNOWN</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="provider">Provider</label>
                                <select class="form-control" name="provider" id="provider">
                                    <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                    <option value="ding">Ding</option>
                                    <option value="reloadly">Reloadly</option>
                                    <option value="transfer">TransferTo</option>
                                    <option value="tellus">Tellus</option>
                                    <option value="prepay">Prepay</option>
                                    <option value="legacy">Legacy</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="layer">Layer</label>
                                <select class="form-control" name="layer" id="layer">
                                    <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                    <option value="frontend">Frontend</option>
                                    <option value="api">API</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="query">{{ trans('myservice.btn_search') }}</label>
                                <input type="text" class="form-control" name="query" id="query" placeholder="mobile, request, user, IP">
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i>&nbsp;{{ trans('myservice.btn_search') }}</button>
                        </form>
                    </div>
                </div>

                <div class="panel" style="margin-top: -20px">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="attempts-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Layer</th>
                                    <th>Provider</th>
                                    <th>User</th>
                                    <th>Mobile</th>
                                    <th>Amount</th>
                                    <th>Request</th>
                                    <th>Order</th>
                                    <th>IP</th>
                                </tr>
                                </thead>
                            </table>
                        </div>

                        <script id="details-template" type="text/x-handlebars-template">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                <tr><td>Request ID</td><td>@{{ request_id }}</td></tr>
                                <tr><td>Lock Key</td><td>@{{ lock_key }}</td></tr>
                                <tr><td>Internal Reference</td><td>@{{ internal_reference }}</td></tr>
                                <tr><td>Provider Reference</td><td>@{{ provider_reference }}</td></tr>
                                <tr><td>Transaction ID</td><td>@{{ transaction_id }}</td></tr>
                                <tr><td>Locked At</td><td>@{{ locked_at }}</td></tr>
                                <tr><td>Completed At</td><td>@{{ completed_at }}</td></tr>
                                <tr><td>Referer</td><td>@{{ referer }}</td></tr>
                                <tr><td>Origin</td><td>@{{ origin }}</td></tr>
                                <tr><td>User Agent</td><td>@{{ user_agent }}</td></tr>
                                <tr><td>Error</td><td>@{{ error_message }}</td></tr>
                                <tr><td>Metadata</td><td><pre style="white-space: pre-wrap">@{{ metadata }}</pre></td></tr>
                                </tbody>
                            </table>
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ asset('vendor/datatables/app.js') }}"></script>
    <script src="{{ asset('vendor/date-picker/jquery-ui.js') }}"></script>
    <script src="{{ asset('vendor/common/handlebars-v4.0.11.js') }}"></script>
    <script>
        $(function () {
            $('.date').datepicker({
                showButtonPanel: true,
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd',
                showAnim: 'slideDown'
            });
        });

        $(document).ready(function () {
            var template = Handlebars.compile($('#details-template').html());
            var oTable = $('#attempts-table').DataTable({
                autoWidth: false,
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url('topup-confirm-attempts/fetch') }}',
                    data: function (d) {
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.status = $('#status').val();
                        d.provider = $('#provider').val();
                        d.layer = $('#layer').val();
                        d.query = $('#query').val();
                    }
                },
                columns: [
                    {className: 'details-control', orderable: false, searchable: false, data: null, defaultContent: ''},
                    {data: 'id', name: 'id'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'status_label', name: 'status', orderable: false, searchable: false},
                    {data: 'layer', name: 'layer'},
                    {data: 'provider', name: 'provider'},
                    {data: 'username', name: 'username'},
                    {data: 'mobile', name: 'mobile'},
                    {data: 'amount', name: 'amount'},
                    {data: 'request_short', name: 'request_id', orderable: false},
                    {data: 'order_link', name: 'order_id', orderable: false, searchable: false},
                    {data: 'ip', name: 'ip'}
                ],
                dom: 'Bfrtip',
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10 {{ trans('users.records') }}', '25 {{ trans('users.records') }}', '50 {{ trans('users.records') }}', '{{ trans('users.show_all') }}']
                ],
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
                aaSorting: [[2, 'DESC']]
            });

            $('#search-form').on('submit', function (event) {
                event.preventDefault();
                oTable.draw();
            });

            $('#attempts-table tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = oTable.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(template(row.data())).show();
                    tr.addClass('shown');
                }
            });
        });
    </script>
@endsection
