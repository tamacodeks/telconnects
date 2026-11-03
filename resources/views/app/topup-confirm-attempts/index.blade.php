@extends('layout.app')
@section('content')
    @include('layout.breadcrumb', ['data' => [
        ['name' => $page_title, 'url' => '', 'active' => 'yes'],
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
                                <input type="text" class="form-control date" name="from_date" id="from_date" value="{{ $from_date }}">
                            </div>
                            <div class="form-group">
                                <label for="to_date">{{ trans('common.filter_lbl_to') }}</label>
                                <input type="text" class="form-control date" name="to_date" id="to_date" value="{{ $to_date }}">
                            </div>
                            <div class="form-group">
                                <label for="status">{{ trans('topup_v2.confirm_attempts.status') }}</label>
                                <select class="form-control" name="status" id="status">
                                    <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                    <option value="PROCESSING">{{ trans('topup_v2.confirm_attempts.statuses.processing') }}</option>
                                    <option value="API_REQUEST_STARTED">{{ trans('topup_v2.confirm_attempts.statuses.api_request_started') }}</option>
                                    <option value="API_RESPONSE_RECEIVED">{{ trans('topup_v2.confirm_attempts.statuses.api_response_received') }}</option>
                                    <option value="SUCCESS">{{ trans('topup_v2.confirm_attempts.statuses.success') }}</option>
                                    <option value="BLOCKED_DUPLICATE">{{ trans('topup_v2.confirm_attempts.statuses.blocked_duplicate') }}</option>
                                    <option value="TOO_MANY_ATTEMPTS">{{ trans('topup_v2.confirm_attempts.statuses.too_many_attempts') }}</option>
                                    <option value="SECURITY_BLOCKED">{{ trans('topup_v2.confirm_attempts.statuses.security_blocked') }}</option>
                                    <option value="UNKNOWN">{{ trans('topup_v2.confirm_attempts.statuses.unknown') }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="provider">{{ trans('topup_v2.confirm_attempts.provider') }}</label>
                                <select class="form-control" name="provider" id="provider">
                                    <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                    <option value="ding">{{ trans('topup_v2.confirm_attempts.providers.ding') }}</option>
                                    <option value="reloadly">{{ trans('topup_v2.confirm_attempts.providers.reloadly') }}</option>
                                    <option value="transfer">{{ trans('topup_v2.confirm_attempts.providers.transfer') }}</option>
                                    <option value="tellus">{{ trans('topup_v2.confirm_attempts.providers.tellus') }}</option>
                                    <option value="prepay">{{ trans('topup_v2.confirm_attempts.providers.prepay') }}</option>
                                    <option value="legacy">{{ trans('topup_v2.confirm_attempts.providers.legacy') }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="layer">{{ trans('topup_v2.confirm_attempts.layer') }}</label>
                                <select class="form-control" name="layer" id="layer">
                                    <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                    <option value="frontend">{{ trans('topup_v2.confirm_attempts.frontend') }}</option>
                                    <option value="api">{{ trans('topup_v2.confirm_attempts.api') }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="query">{{ trans('myservice.btn_search') }}</label>
                                <input type="text" class="form-control" name="query" id="query" placeholder="{{ trans('topup_v2.confirm_attempts.query_placeholder') }}">
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
                                    <th>{{ trans('topup_v2.lbl_date') }}</th>
                                    <th>{{ trans('topup_v2.confirm_attempts.status') }}</th>
                                    <th>{{ trans('topup_v2.confirm_attempts.layer') }}</th>
                                    <th>{{ trans('topup_v2.confirm_attempts.provider') }}</th>
                                    <th>{{ trans('topup_v2.confirm_attempts.user') }}</th>
                                    <th>{{ trans('topup_v2.confirm_attempts.mobile') }}</th>
                                    <th>{{ trans('topup_v2.amount') }}</th>
                                    <th>{{ trans('topup_v2.confirm_attempts.request') }}</th>
                                    <th>{{ trans('topup_v2.confirm_attempts.order') }}</th>
                                    <th>{{ trans('topup_v2.confirm_attempts.ip') }}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>

                        <script id="details-template" type="text/x-handlebars-template">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                <tr><td>{{ trans('topup_v2.confirm_attempts.request_id') }}</td><td>@{{ request_id }}</td></tr>
                                <tr><td>{{ trans('topup_v2.confirm_attempts.lock_key') }}</td><td>@{{ lock_key }}</td></tr>
                                <tr><td>{{ trans('topup_v2.confirm_attempts.internal_reference') }}</td><td>@{{ internal_reference }}</td></tr>
                                <tr><td>{{ trans('topup_v2.confirm_attempts.provider_reference') }}</td><td>@{{ provider_reference }}</td></tr>
                                <tr><td>{{ trans('topup_v2.confirm_attempts.transaction_id') }}</td><td>@{{ transaction_id }}</td></tr>
                                <tr><td>{{ trans('topup_v2.confirm_attempts.locked_at') }}</td><td>@{{ locked_at }}</td></tr>
                                <tr><td>{{ trans('topup_v2.confirm_attempts.completed_at') }}</td><td>@{{ completed_at }}</td></tr>
                                <tr><td>{{ trans('topup_v2.confirm_attempts.referer') }}</td><td>@{{ referer }}</td></tr>
                                <tr><td>{{ trans('topup_v2.confirm_attempts.origin') }}</td><td>@{{ origin }}</td></tr>
                                <tr><td>{{ trans('topup_v2.confirm_attempts.user_agent') }}</td><td>@{{ user_agent }}</td></tr>
                                <tr><td>{{ trans('topup_v2.confirm_attempts.error') }}</td><td>@{{ error_message }}</td></tr>
                                <tr><td>{{ trans('topup_v2.confirm_attempts.metadata') }}</td><td><pre style="white-space: pre-wrap">@{{ metadata }}</pre></td></tr>
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
                    [10, 25, 50],
                    ['10 {{ trans('users.records') }}', '25 {{ trans('users.records') }}', '50 {{ trans('users.records') }}']
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
