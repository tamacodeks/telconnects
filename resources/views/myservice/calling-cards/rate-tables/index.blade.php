@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('myservice.lbl_rate_tables'),'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12" id="loader">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{ trans('myservice.lbl_rate_tables') }}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="col-md-7">
                            <form method="POST" id="search-form" class="form-inline" role="form">
                                <div class="form-group">
                                    <label for="rate_table_group_id">{{ trans('myservice.lbl_choose_rate_group') }}</label>
                                    <select name="rate_table_group_id" id="rate_table_group_id" class="select-picker">
                                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                        @foreach($rate_groups as $rate_group)
                                            <option value="{{ $rate_group->id }}">{{ $rate_group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i>&nbsp;{{ trans('myservice.btn_search') }}</button>
                            </form>
                        </div>
                        @if(auth()->user()->group_id != 5)
                            <div class="col-md-5">
                                <a href="{{ secure_url('cc-price-list/groups') }}" class="btn btn-primary"><i class="fa fa-list-ol"></i> {{ trans('myservice.lbl_view_all_price_lists') }}</a>
                                <a onclick="AppModal(this.href,'{{ trans('common.add_new') }}');return false;"  href="{{ secure_url('cc-price-list/groups/edit') }}" class="btn btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('myservice.btn_add_price_list') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="panel" style="margin-top: -20px">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="rate-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ trans('myservice.lbl_card_name') }}</th>
                                    <th>{{ trans('common.lbl_desc') }}</th>
                                    <th>{{ trans('myservice.buying_price') }}</th>
                                    <th>{{ trans('myservice.sale_price') }}</th>
                                    <th>{{ trans('common.transaction_tbl_sale_margin') }}</th>
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
    <script src="{{ secure_asset('vendor/datatables/app.js') }}"></script>
    <script src="{{ secure_asset('vendor/select-picker/js/bootstrap-select.js') }}"></script>
    <script>
        $(function () {
            $('[data-toggle="popover"]').popover();
        })
        // Variable to hold request
        var request;
        function updateSalePrice(rate_table_id,sale_price) {
            $("#loader").LoadingOverlay("show");
            $('body').append("<span class='loader'></span>");
            // Abort any pending request
            if (request) {
                request.abort();
            }
            // Serialize the data in the form
            var serializedData = { rate_table_id: rate_table_id, sale_price: sale_price };

            // Fire off the request to /form.php
            request = $.ajax({
                url: "{{ secure_url('cc-price-lists/update') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: serializedData
            });

            // Callback handler that will be called on success
            request.done(function (response, textStatus, jqXHR){
                // Log a message to the console
                console.log(response);
                if(response.data.code == 400){
                    $.alert({
                        content: response.data.message,
                        buttons: {
                            "{{ trans('common.btn_close') }}": function () {

                            }
                        },
                        type : "warning",
                        autoClose: '{{ trans('common.btn_close') }}|5000'
                    });
                }else{
                    $.alert({
                        content: response.data.message,
                        buttons: {
                            "{{ trans('common.btn_close') }}": function () {
                                setTimeout(function () {
                                    document.getElementById("sp_"+rate_table_id).focus();
                                },1000)
                            }
                        },
                        type : "success",
                        autoClose: '{{ trans('common.btn_close') }}|5000'
                    });
                    $("#sp_"+rate_table_id).val(response.data.result.sale_price);
                    $("#sm_"+rate_table_id).html(response.data.result.sale_margin);
                }
            });

            // Callback handler that will be called on failure
            request.fail(function (jqXHR, textStatus, errorThrown){
                // Log the error to the console
                console.error(
                    "The following error occurred: "+
                    textStatus, errorThrown
                );
            });

            // Callback handler that will be called regardless
            // if the request failed or succeeded
            request.always(function () {
                // Reenable the inputs
                $("#loader").LoadingOverlay("hide");
                $(".loader").remove();
            });
        }

        function validateSalePrice(element) {
            var current = $(element);
            var id = $(element).attr('id');
            var btn_to_disable = id.replace('sp_',"btn_");
            if(parseFloat(current.data('max')) < parseFloat(current.val())){
                $('#'+btn_to_disable).attr('disabled','disabled');
                $(element).parents(".form-group").addClass("has-error");
                var message = "<span class='help-block error'>{{ trans('myservice.amount_must_message') }}"+current.data('max')+"</span>";
                $(element).next("span").remove();
                $(message).insertAfter(element);
            }else{
                $('#'+btn_to_disable).removeAttr('disabled');
                $(element).parents(".form-group").removeClass("has-error");
                $(element).next("span").remove();
            }
        }
        function isNumberKey(evt,id)
        {
            try{
                var charCode = (evt.which) ? evt.which : event.keyCode;

                if(charCode==46){
                    var txt=document.getElementById(id).value;
                    if(!(txt.indexOf(".") > -1)){

                        return true;
                    }
                }
                if (charCode > 31 && (charCode < 48 || charCode > 57) )
                    return false;

                return true;
            }catch(w){
                alert(w);
            }
        }
        $(document).ready(function () {
            $('.sp_val').keypress(function(event) {
                if(event.which == 8 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46)
                    return true;

                else if((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57))
                    event.preventDefault();

            });
            $(".select-picker").selectpicker();
            var oTable = $('#rate-table').DataTable({
                "autoWidth": false,
                searching: true,
                "pageLength": "-1",
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "{{ trans('common.processing') }}"
                },
                serverSide: true,
                ajax: {
                    url: '{{ secure_url('cc-price-lists/fetch') }}',
                    data: function (d) {
                        d.rate_table_group_id = $('#rate_table_group_id').val();
                    }
                },
                columns: [
                    {
                        "className":      '',
                        "orderable":      false,
                        "searchable":     false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    {data: 'name', name: 'calling_cards.name'},
                    {data: 'description', name: 'calling_cards.description',searchable:false,orderable:false},
                    {data: 'buying_price', name: 'buying_price',searchable:false,orderable:false},
                    {data: 'sale_price', name: 'sale_price',searchable:false,orderable:false},
                    {data: 'sale_margin', name: 'sale_margin',searchable:false,orderable:false},
                    {data: 'action', name: 'action',searchable:false,orderable:false},
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
                drawCallback: function () {
                    $('[data-toggle="popover"]').popover();
                }
            });
            oTable.on('order.dt search.dt', function () {
                oTable.column(0, {search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();
            $('#search-form').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
            });

        });
    </script>
@endsection