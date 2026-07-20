@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('myservice.margin_report'),'url'=> '','active' => 'yes']
    ]
    ])
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{ trans('myservice.margin_report') }}</h3>
                    </div>
                    <div class="panel-body">
                        <form method="POST" id="search-form" class="form-inline" role="form">
                            <div class="form-group">
                                <select data-live-search="true" name="retailers[]" id="retailers" class="select-picker" multiple data-selected-text-format="count" data-select-all-text="{{ trans('common.lbl_select_all') }}" data-none-selected-text="{{ trans('myservice.lbl_choose_retailers') }}" data-deselect-all-text="{{ trans('common.lbl_deselect_all') }}" data-actions-box="true">
                                    @if(isset($retailers))
                                        @foreach($retailers as $retailer)
                                            <option value="{{ $retailer->id }}">{{ $retailer->username }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="text" placeholder="{{ trans('common.filter_lbl_from') }}" class="form-control date" name="from_date" id="from_date" value="{{ date("Y-m-d") }}" >
                            </div>
                            <div class="form-group">
                                <input type="text" placeholder="{{ trans('common.filter_lbl_to') }}" class="form-control date" name="to_date" id="to_date" value="{{ date("Y-m-d") }}" >
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i>&nbsp;{{ trans('myservice.btn_search') }}</button>
                        </form>
                    </div>
                </div>
                <div class="panel" style="margin-top: -20px">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="pin-usage-stats-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ trans('myservice.last_transaction_at') }}</th>
                                    <th>{{ trans('common.transaction_tbl_reseller') }}</th>
                                    <th>{{ trans('myservice.total_buying_price') }}</th>
                                    <th>{{ trans('myservice.total_sale_price') }}</th>
                                    <th>{{ trans('myservice.total_margin') }}</th>
                                    <th>{{ trans('common.mr_tbl_action') }}</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th class="text-left">{{ trans('common.lbl_total') }}</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </tfoot>
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
            setTimeout(function () {
                $(function () {
                    $('[data-toggle="popover"]').popover();
                })
            },2500);
            $(".select-picker").selectpicker();
            var oTable = $('#pin-usage-stats-table').DataTable({
                "autoWidth": false,
                searching: false,
                "pageLength": "-1",
                processing: "<span class='loader'></span>",
                language: {
                    "processing": "<span class='loader'></span>",
                    paginate: {
                        next: '{!!  trans('pagination.next') !!}', // or '→'
                        previous: '{!! trans('pagination.previous') !!}' // or '←'
                    }
                },
                serverSide: true,
                ajax: {
                    url: '{{ secure_url('cc/report/margins/fetch') }}',
                    data: function (d) {
                        d.retailers = $('#retailers').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
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
                    {data: 'last_transaction', name: 'last_transaction',searchable:false},
                    {data: 'username', name: 'users.username',searchable:false},
                    {data: 'buying_price', name: 'buying_price',searchable:false,orderable:false,className: "sum"},
                    {data: 'sale_price', name: 'sale_price',searchable:false,orderable:false,className: "sum"},
                    {data: 'margin', name: 'margin',searchable:false,orderable:false,className: "sum"},
                    {data: 'action', name: 'action',searchable:false,orderable:false}
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

            $('#search-form').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
            });

            oTable.on( 'order.dt search.dt', function () {
                oTable.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    cell.innerHTML = i+1;
                } );
            } ).draw();
        });
    </script>
@endsection