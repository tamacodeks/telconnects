@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('common.telecom_provider_countries'),'url'=> '','active' => 'yes']
    ]])
    <div class="container-fluid">
        <div class="row">
        <div class="col-md-12" id="loader">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>{{ trans('common.telecom_provider_countries') }}</h4>
                        <div class="pull-right" style="margin-top: -35px">
                            <a onclick="AppModal(this.href,'{{ trans('common.btn_add') .' '.trans('common.order_tbl_service') }}');return false;" href="{{ secure_url('telecom-country/update') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;{{ trans('common.btn_add') }} </a>
                        </div>
                        <div>
                            <a href="{{ secure_url('processAll') }}" id="loader" class="btn btn-sm btn-primary">&nbsp;Fetch From TamaPanel</a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="service-config-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th>{{ trans('service.tp_country') }}</th>
                                    <th>{{ trans('myservice.status') }}</th>
                                    <th>{{ trans('common.created_at') }}</th>
                                    <th>{{ trans('common.updated_at') }}</th>
                                    <th>{{ trans('common.mr_tbl_action') }}</th>
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
    <script>
        $(document).ready(function () {
            var table = $('#service-config-table').DataTable({
                "autoWidth": false,
                "pageLength": "{{ PER_PAGE }}",
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "<span class='loader'></span>"
                },
                serverSide: true,
                ajax: '{{ secure_url('telecom-countries/fetch') }}',
                columns: [
                    {data: 'name', name: 'name',orderable : false},
                    {data: 'status', name: 'status',orderable : false,searchable: false},
                    {data: 'created_at', name: 'created_at',orderable : false,searchable: false},
                    {data: 'updated_at', name: 'updated_at',orderable : false,searchable: false},
                    {data: 'action', name: 'action',orderable : false,searchable: false}
                ],
                order: [[1, 'asc']]
            });
            $("#loader").click(function(e) {
                $("#loader").LoadingOverlay("show");
                $('body').append("<span class='loader'></span>");
            });
        });
    </script>
@endsection