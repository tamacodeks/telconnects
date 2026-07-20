@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('service.ms_manage_telecom_provider'),'url'=> '','active' => 'yes']
    ]])
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>{{ trans('service.ms_manage_telecom_provider') }}</h4>
                        <div class="pull-right" style="margin-top: -35px">
                            <a onclick="AppModal(this.href,'{{ trans('common.btn_add') .' '.trans('service.telecom_provider') }}');return false;" href="{{ secure_url('tp-config/update') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;{{ trans('common.btn_add') }} </a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="tp-config-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>{{ trans('service.tp_country') }}</th>
                                    <th>{{ trans('service.name') }}</th>
                                    <th>{{ trans('myservice.status') }}</th>
                                    <th>{{ trans('common.created_at') }}</th>
                                    <th>{{ trans('common.updated_at') }}</th>
                                    <th>{{ trans('common.mr_tbl_action') }}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <script id="details-template" type="text/x-handlebars-template">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <td>{{ trans('myservice.image') }}</td>
                                    <td><img src="@{{ image }}" class="img-thumbnail" style="width: 100px"></td>
                                </tr>
                            </table>
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <link href="{{ secure_asset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <script src="{{ secure_asset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/Buttons-1.5.1/js/buttons.bootstrap.min.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/Buttons-1.5.1/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/buttons.server-side.js') }}"></script>
    <script src="{{ secure_asset('vendor/common/handlebars-v4.0.11.js') }}"></script>
    <script>
        $(document).ready(function () {
            var template = Handlebars.compile($("#details-template").html());
            var table = $('#tp-config-table').DataTable({
                "autoWidth": false,
                "pageLength": "{{ PER_PAGE }}",
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "<span class='loader'></span>"
                },
                serverSide: true,
                ajax: '{{ secure_url('tp-config/fetch') }}',
                columns: [
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "searchable":     false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    {data: 'nice_name', name: 'countries.nice_name',orderable : false},
                    {data: 'name', name: 'name',orderable : false},
                    {data: 'status', name: 'status',orderable : false,searchable: false},
                    {data: 'created_at', name: 'created_at',orderable : false,searchable: false},
                    {data: 'updated_at', name: 'updated_at',orderable : false,searchable: false},
                    {data: 'action', name: 'action',orderable : false,searchable: false}
                ],
                order: [[1, 'asc']],
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
                        extend:    'reload',
                        text:      '<i class="fa fa-sync"></i>',
                        titleAttr: '{{ trans('common.refresh') }}'
                    }
                ]
            });

            // Add event listener for opening and closing details
            $('#tp-config-table tbody').on('click', 'td.details-control', function () {
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