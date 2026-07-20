@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => 'Refresh Popup Seen Users','url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <style>
        .refresh-popup-card {
            border: 1px solid #e6ecf2;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(22, 34, 51, 0.06);
            overflow: hidden;
            background: #fff;
        }
        .refresh-popup-card .panel-heading {
            background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
            border-bottom: 1px solid #e3edf9;
            padding: 14px 18px;
        }
        .refresh-popup-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .refresh-popup-toolbar .btn-group .btn {
            border-radius: 6px !important;
            margin-right: 6px;
        }
        .refresh-popup-meta {
            color: #5f6f82;
            font-size: 12px;
        }
        .refresh-popup-chip {
            display: inline-block;
            padding: 4px 10px;
            margin-right: 8px;
            border-radius: 14px;
            background: #f2f7ff;
            color: #2c5ea8;
            font-weight: 600;
            font-size: 12px;
        }
        #transactions-table thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e8eef5 !important;
            color: #3a4a5b;
            font-weight: 600;
            white-space: nowrap;
        }
        #transactions-table td {
            vertical-align: middle !important;
        }
        .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .2px;
        }
        .status-pill.success {
            background: #e8f7ee;
            color: #1f7a45;
        }
        .status-pill.warning {
            background: #fff4df;
            color: #a96700;
        }
        .status-pill.muted {
            background: #eef1f5;
            color: #617182;
        }
        .parent-name-cell {
            font-weight: 600;
            color: #334e68;
        }
        .user-name-cell {
            font-weight: 600;
            color: #1f3f5b;
        }
        .last-seen-cell {
            color: #586a7a;
            font-size: 12px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default refresh-popup-card">
                    <div class="panel-heading">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                            <div>
                                <h3 class="panel-title" style="margin:0;">
                                    <i class="fa fa-bell-slash" style="color:#3b82c4;"></i>
                                    Refresh Popup Pending Users
                                </h3>
                                <div class="refresh-popup-meta">Retailers who have not clicked TT V2 refresh popup and were active in the last 7 days.</div>
                            </div>
                            <div class="refresh-popup-meta">
                                <span class="refresh-popup-chip"><i class="fa fa-clock-o"></i> Last 7 days</span>
                                <span class="refresh-popup-chip"><i class="fa fa-filter"></i> Parent Active Only</span>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="refresh-popup-toolbar">
                            <div class="btn-group">
                                <button type="button" id="btn-refresh-popup-reload" class="btn btn-primary btn-sm">
                                    <i class="fa fa-sync"></i> Refresh Table
                                </button>
                                <button type="button" id="btn-refresh-popup-export" class="btn btn-success btn-sm">
                                    <i class="fa fa-file-excel"></i> Export Excel
                                </button>
                            </div>
                            <div>
                                <span class="refresh-popup-chip">Rows: <span id="refresh-popup-row-count">0</span></span>
                                <span class="refresh-popup-chip">Sort: Parent Name</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="transactions-table" class="table table-striped table-hover table-condensed">
                                <thead>
                                <tr>
                                    <th>{{ trans('common.order_tbl_sl') }}</th>
                                    <th>Parent Name</th>
                                    <th>Parent Status</th>
                                    <th>{{ trans('users.lbl_user_name') }}</th>
                                    <th>Popup Status</th>
                                    <th>{{ trans('users.last_activity') }}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <script id="details-template" type="text/x-handlebars-template">

                        </script>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <link href="{{ secure_asset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <script src="{{ secure_asset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ secure_asset('vendor/date-picker/jquery-ui.js') }}"></script>
    <script src="{{ secure_asset('vendor/select-picker/js/bootstrap-select.js') }}"></script>
    <script src="{{ secure_asset('vendor/common/handlebars-v4.0.11.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/app.js') }}"></script>
    <script>

        $(document).ready(function () {
            var oTable = $('#transactions-table').DataTable({
                "autoWidth": false,
                searching: true,
                "pageLength": 25,
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "{{ trans('common.processing') }}...<span class='loader'></span>"
                },
                serverSide: true,
                ajax: {
                    url: '{{ secure_url('fetch_refresh_popup_seen_users') }}',
                },
                columns: [
                    {
                        "className":      '',
                        "orderable":      false,
                        "searchable":     false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    {
                        data: 'parent_name',
                        name: 'parent_users.username',
                        render: function (data) {
                            return '<span class="parent-name-cell">' + (data || '-') + '</span>';
                        }
                    },
                    {
                        data: 'parent_status',
                        name: 'parent_users.status',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            if (data === 'Active') {
                                return '<span class="status-pill success">Active</span>';
                            }
                            return '<span class="status-pill muted">' + (data || 'Unknown') + '</span>';
                        }
                    },
                    {
                        data: 'username',
                        name: 'users.username',
                        render: function (data) {
                            return '<span class="user-name-cell">' + (data || '-') + '</span>';
                        }
                    },
                    {
                        data: 'status',
                        name: 'users.status',
                        orderable:false,
                        searchable:false,
                        render: function (data) {
                            if (data === 'Not Clicked') {
                                return '<span class="status-pill warning">Not Clicked</span>';
                            }
                            return '<span class="status-pill success">' + (data || '-') + '</span>';
                        }
                    },
                    {
                        data: 'last_activity',
                        name: 'users.last_activity',
                        orderable:false,
                        searchable:false,
                        render: function (data) {
                            return '<span class="last-seen-cell">' + (data || '-') + '</span>';
                        }
                    },
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
                aaSorting: [[1, 'ASC'], [3, 'ASC']],
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

            $('#btn-refresh-popup-reload').on('click', function () {
                oTable.ajax.reload(null, false);
            });

            $('#btn-refresh-popup-export').on('click', function () {
                oTable.button('.buttons-excel').trigger();
            });

            oTable.on('draw.dt', function () {
                $('#refresh-popup-row-count').text(oTable.rows({ filter: 'applied' }).count());
            });

            oTable.on('order.dt search.dt', function () {
                oTable.column(0, {search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();
        });
    </script>
@endsection
