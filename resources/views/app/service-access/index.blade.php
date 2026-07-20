@extends('layout.app')

@section('content')
    @include('layout.breadcrumb', [
        'data' => [['name' => trans('common.service_access'), 'url' => '', 'active' => 'yes']]
    ])

    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/datatables/datatables.css') }}" rel="stylesheet">

    <div class="container-fluid">

        {{-- Service Access Form --}}
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-default">
                    <div class="panel-body" id="progress-loader">
                        <form id="service-access-form" class="form-horizontal" method="POST" action="{{ secure_url('service-access/update') }}">
                            @csrf

                            {{-- Manager --}}
                            <div class="form-group">
                                <label class="control-label col-md-4" for="manager_id">{{ trans('common.select_manager') }}</label>
                                <div class="col-md-8">
                                    <select name="manager_id" id="manager_id" class="form-control select-picker" data-live-search="true">
                                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}">{{ $manager->username }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Retailer --}}
                            <div class="form-group">
                                <label class="control-label col-md-4" for="retailer_id">{{ trans('common.select_retailer') }}</label>
                                <div class="col-md-8">
                                    <select name="retailer_id" id="retailer_id" class="form-control select-picker" data-live-search="true">
                                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Services --}}
                            <div class="form-group hide" id="services-box">
                                <label class="control-label col-md-4">{{ trans('common.choose_services') }}</label>
                                <div class="col-md-8" id="services-list">
                                    {{-- Dynamic checkboxes --}}
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="form-group">
                                <div class="col-md-4"></div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-block" id="btnSubmit">
                                        <i class="fa fa-save"></i> {{ trans('common.btn_update') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Access Listing --}}
        <div class="row" style="margin-top: 30px;">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h4 class="text-center">{{ trans('users.lbl_user_service_status') }}</h4>
                        <div class="table-responsive">
                            <table id="service-access-table" class="table table-bordered table-condensed">
                                <thead>
                                <tr>
                                    <th>{{ trans('users.lbl_user_name') }}</th>
                                    <th>{{ trans('users.lbl_user_commission_service') }}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ secure_asset('vendor/select-picker/js/bootstrap-select.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/datatables.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('.select-picker').selectpicker();

            $('#manager_id').on('change', function () {
                const manager_id = $(this).val();
                $('#retailer_id').html('<option value="">{{ trans('common.loading') }}...</option>').selectpicker('refresh');
                if (manager_id) {
                    $.post('{{ secure_url('service-access/retailers') }}', {
                        manager_id: manager_id, _token: '{{ csrf_token() }}'
                    }, function (data) {
                        $('#retailer_id').empty().append('<option value="">{{ trans('common.lbl_please_choose') }}</option>');
                        $.each(data, function (i, retailer) {
                            $('#retailer_id').append('<option value="'+ retailer.id +'">'+ retailer.username +'</option>');
                        });
                        $('#retailer_id').selectpicker('refresh');
                    });
                }
            });

            $('#retailer_id').on('change', function () {
                const retailer_id = $(this).val();
                if (retailer_id) {
                    $.post('{{ secure_url('service-access/services') }}', {
                        retailer_id: retailer_id, _token: '{{ csrf_token() }}'
                    }, function (res) {
                        $('#services-box').removeClass('hide');
                        let html = '';
                        $.each(res.services, function (i, service) {
                            const checked = res.access[service.id] == 1 ? 'checked' : '';
                            html += `<div class="checkbox"><label><input type="checkbox" name="services[]" value="${service.id}" ${checked}> ${service.name}</label></div>`;
                        });
                        $('#services-list').html(html);
                    });
                } else {
                    $('#services-box').addClass('hide');
                    $('#services-list').empty();
                }
            });

            $('#service-access-form').on('submit', function () {
                $.confirm({
                    title: '{{ trans('common.btn_save') }}',
                    content: '{{ trans('common.lbl_ask_proceed_form') }}',
                    buttons: {
                        "{{ trans('common.btn_save') }}": function () {
                            $("#progress-loader").LoadingOverlay("show");
                            $("#btnSubmit").html("<i class='fa fa-spinner fa-pulse'></i> {{ trans('common.saving') }}...").attr('disabled', 'disabled');
                            $('#service-access-form')[0].submit();
                        },
                        "{{ trans('common.btn_cancel') }}": function () {}
                    }
                });
                return false;
            });

            $('#service-access-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ secure_url('service-access/list') }}',
                columns: [
                    { data: 'username', name: 'username' },
                    { data: 'services', name: 'services', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@endsection
