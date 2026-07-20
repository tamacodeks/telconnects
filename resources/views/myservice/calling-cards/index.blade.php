@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('service.service_calling_cards'),'url'=> '','active' => 'yes']
    ]])
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>{{ trans('service.service_calling_cards') }}</h4>
                        <div class="pull-right" style="margin-top: -35px">
                            <a href="{{ secure_url('cc/report/upload-statistics') }}" class="btn btn-sm btn-primary"><i class="fa fa-history"></i>&nbsp;{{ trans('myservice.upload_statistics') }} </a>
                            <a href="{{ secure_url('cc/update') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;{{ trans('myservice.upload_new_pin') }} </a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="calling-cards-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>{{ trans('service.tp_country') }}</th>
                                    <th>{{ trans('myservice.provider_name') }}</th>
                                    <th>{{ trans('myservice.description') }}</th>
                                    <th>{{ trans('myservice.access_number') }}</th>
                                    <th>{{ trans('myservice.buying_price') }}</th>
                                    <th>{{ trans('myservice.bimedia_buying_price') }}</th>
                                    <th>{{ trans('myservice.status') }}</th>
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
                                <tr>
                                    <td>{{ trans('myservice.face_value') }}</td>
                                    <td>@{{ face_value }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans('common.created_at') }}</td>
                                    <td>@{{ created_at }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans('common.updated_at') }}</td>
                                    <td>@{{ updated_at }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans('myservice.aleda_product_code') }}</td>
                                    <td>@{{ aleda }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans('myservice.bimedia_product_code') }}</td>
                                    <td>@{{ bimedia }}</td>
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
    <script src="{{ secure_asset('vendor/datatables/app.js') }}"></script>
    <script src="{{ secure_asset('vendor/common/handlebars-v4.0.11.js') }}"></script>
    <script>
        $(function () {
            $('[data-toggle="popover"]').popover();
        })
        $(document).ready(function () {
            var template = Handlebars.compile($("#details-template").html());
            var table = $('#calling-cards-table').DataTable({
                "autoWidth": false,
                "pageLength": "-1",
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "<span class='loader'></span>"
                },
                serverSide: true,
                ajax: '{{ secure_url('cc/fetch') }}',
                columns: [
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "searchable":     false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    {data: 'country_name', name: 'country_name',orderable : false,searchable: false},
                    {data: 'name', name: 'name',orderable : false},
                    {data: 'description', name: 'description',orderable : false,searchable: false},
                    {data: 'access_number', name: 'access_number',orderable : false,searchable: false},
                    {data: 'buying_price', name: 'buying_price',orderable : false,searchable: false},
                    {data: 'buying_price1', name: 'buying_price1',orderable : false,searchable: false},
                    {data: 'status', name: 'status',orderable : false,searchable: false},
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
                        extend:    'excel',
                        text:      '<i class="fa fa-file-excel"></i>',
                        titleAttr: '{{ trans('common.download_as_excel') }}'
                    },
                    {
                        extend:    'reload',
                        text:      '<i class="fa fa-sync-alt"></i>',
                        titleAttr: '{{ trans('common.refresh') }}'
                    }
                ],
                drawCallback: function () {
                    $('[data-toggle="popover"]').popover();
                }
            });

            // Add event listener for opening and closing details
            $('#calling-cards-table tbody').on('click', 'td.details-control', function () {
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