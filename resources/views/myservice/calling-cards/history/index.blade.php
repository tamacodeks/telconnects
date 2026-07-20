@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('myservice.pin_usage_history'),'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 m-t-10">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('myservice.pin_usage_history') }}
                        <div class="pull-right">
                            @if(in_array(auth()->user()->group_id,[2,3]))
                                <a href="{{ secure_url('tickets/manage') }}" class="btn btn-primary btn-sm" style="margin-top: -5px"><i
                                            class="fa fa-cog"></i>&nbsp;Manage Tickets</a> &nbsp;
                            @endif
                            @if(auth()->user()->group_id != 2)
                                <a href="{{ secure_url('tickets') }}" class="btn btn-primary btn-sm" style="margin-top: -5px"><i
                                            class="fa fa-list-ol"></i>&nbsp;View My Tickets</a>
                            @endif
                        </div>
                    </div>
                    <div class="panel-body">
                        <form method="POST" id="search-form" class="form-inline" role="form">
                            <div class="form-group">
                                <select data-live-search="true" name="telecom_provider_id" id="telecom_provider_id" class="select-picker" multiple data-selected-text-format="count" data-select-all-text="{{ trans('common.lbl_select_all') }}" data-none-selected-text="{{ trans('myservice.lbl_card_name') }}" data-deselect-all-text="{{ trans('common.lbl_deselect_all') }}" data-actions-box="true">
                                    @if(isset($providers))
                                        @foreach($providers as $provider)
                                            <option value="{{ $provider->id }}">{{ $provider->name }} {{ \app\Library\AppHelper::formatAmount('EUR',$provider->face_value) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="text" placeholder="{{ trans('common.filter_lbl_from') }}" class="form-control date" name="from_date" id="from_date" >
                            </div>
                            <div class="form-group">
                                <input type="text" placeholder="{{ trans('common.filter_lbl_to') }}" class="form-control date" name="to_date" id="to_date" >
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i>&nbsp;{{ trans('myservice.btn_search') }}</button>
                        </form>
                        <div class="table-responsive m-t-20">
                            <table id="pin-history-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ trans('myservice.lbl_card_name') }}</th>
                                    <th>{{ trans('myservice.lbl_card_desc') }}</th>
                                    <th>{{ trans('common.transaction_serial') }}</th>
                                    <th>{{ trans('common.transaction_pin') }}</th>
                                    <th>{{ trans('myservice.printed_at') }}</th>
                                    <th>{{ trans('myservice.status') }}</th>
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
    <script src="{{ secure_asset('vendor/select-picker/js/bootstrap-select.js') }}"></script>
    <script>
        var request, oTable;
        function print_pin(pin_id) {
            var url = "{{ secure_url('cc-pin-history/print/') }}/" + pin_id;
            var link = $('<a href="' + url + '" />');
            link.attr('target', '_blank');
            window.open(link.attr('href'));
        }
        function print_pin_again_req(pin_id) {
            $('body').append("<span class='loader'></span>");
            // Abort any pending request
            if (request) {
                request.abort();
            }
            // Serialize the data in the form
            var serializedData = {pin_id: pin_id};

            // Fire off the request to /form.php
            request = $.ajax({
                url: "{{ secure_url('cc-pin-history/print_again/request') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: serializedData
            });

            // Callback handler that will be called on success
            request.done(function (response, textStatus, jqXHR) {
                // Log a message to the console
                console.log(response);
                if (response.data.status == 200) {
                    $.alert({
                        content: response.data.message,
                        buttons: {
                            "{{ trans('common.btn_close') }}": function () {
                                oTable.ajax.reload();
                            }
                        },
                        title: "{{ trans('common.info') }}",
                        type: "blue",
                        icon: "fa fa-info-circle",
                        theme: 'material'
                    });
                } else {
                    $.alert({
                        content: response.data.message,
                        buttons: {
                            "{{ trans('common.btn_close') }}": function () {

                            }
                        },
                        title: "{{ trans('common.info') }}",
                        type: "red",
                        icon: "fa fa-exclamation-circle",
                        theme: 'material'
                    });
                }
            });

            // Callback handler that will be called on failure
            request.fail(function (jqXHR, textStatus, errorThrown) {
                // Log the error to the console
                console.error(
                    "The following error occurred: " +
                    textStatus, errorThrown
                );
            });

            // Callback handler that will be called regardless
            // if the request failed or succeeded
            request.always(function () {
                // Reenable the inputs
                $(".loader").remove();
            });
        }
        $( function() {
            $( ".date" ).datepicker({
                showButtonPanel: true,
                changeMonth: true,
                changeYear: true,
                dateFormat : "yy-mm-dd",
                showAnim : "slideDown",
                closeText: '<i class="fa fa-times-circle"><i>&nbsp;{{ trans('common.btn_clear') }}',
                onClose: function () {
                    var event = arguments.callee.caller.caller.arguments[0];
                    // If "Clear" gets clicked, then really clear it
                    if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
                        $(this).val('');
                    }
                }
            });
        } );
        $(document).ready(function () {
            $(".select-picker").selectpicker();
            oTable = $('#pin-history-table').DataTable({
                "autoWidth": false,
                pageLength: "-1",
                searching: true,
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "{{ trans('common.processing') }}",
                    paginate: {
                        next: '{!!  trans('pagination.next') !!}', // or '→'
                        previous: '{!! trans('pagination.previous') !!}' // or '←'
                    }
                },
                serverSide: true,
                ajax: {
                    url: '{{ secure_url('cc-pin-history/fetch') }}',
                    data: function (d) {
                        d.telecom_provider_id = $('#telecom_provider_id').val();
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
                    {data: 'name', name: 'name'},
                    {data: 'description', name: 'description', orderable: false, searchable: false},
                    {data: 'serial', name: 'serial', orderable: false},
                    {data: 'pin', name: 'pin', orderable: false, searchable: false},
                    {data: 'date', name: 'date', searchable: false},
                    {data: 'status', name: 'status', searchable: false, orderable: false},
                    {data: 'action', name: 'action', searchable: false, orderable: false},
                ],
                dom: 'Bfrtip',
                // Configure the drop down options.
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10 {{ trans('users.records') }}', '25 {{ trans('users.records') }}', '50 {{ trans('users.records') }}', '{{ trans('users.show_all') }}']
                ],
                aaSorting: [[4, 'DESC']],
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