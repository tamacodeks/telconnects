@extends('layout.app')
@section('content')
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
                                <div class="table-responsive">
                                    <table id="users-table" class="table table-condensed">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>{{ trans('users.lbl_user_name') }}</th>
                                            <th>{{ trans('users.lbl_tbl_user_acc_type') }}</th>
                                            <th>{{ trans('login.lbl_ip_address') }}</th>
                                            <th>{{ trans('users.lbl_mobile_no') }}</th>
                                            <th>{{ trans('users.lbl_user_email') }}</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                                <script id="details-template" type="text/x-handlebars-template">
                                    <table class="table table-bordered table-striped">

                                    </table>
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <link href="{{ secure_asset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <script src="{{ secure_asset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/Buttons-1.5.1/js/buttons.bootstrap.min.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/Buttons-1.5.1/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/app.js') }}"></script>
    <script src="{{ secure_asset('vendor/common/handlebars-v4.0.11.js') }}"></script>
    <script>
        $(document).ready(function () {
            var template = Handlebars.compile($("#details-template").html());

            var table = $('#users-table').DataTable({
                "autoWidth": false,
                "pageLength": "-1",
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "{{ trans('common.processing') }}"
                },
                serverSide: true,
                ajax: '{{ secure_url('fetch/users_info') }}',
                columns: [
                    {
                        "className":      '',
                        "orderable":      false,
                        "searchable":     false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    {data: 'username', name: 'users.username'},
                    {data: 'name', name: 'user_groups.name'},
                    {data: 'ip_address', name: 'users.ip_address'},
                    {data: 'mobile', name: 'users.mobile'},
                    {data: 'email', name: 'users.email'}
                ],
                order: [[5, 'DESC']],
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
                            columns: [ 2,3,4,5,6 ]
                        }
                    },
                    {
                        extend:    'reload',
                        text:      '<i class="fa fa-sync"></i>',
                        titleAttr: '{{ trans('common.refresh') }}'
                    }
                ]
            });

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
        });
    </script>
@endsection