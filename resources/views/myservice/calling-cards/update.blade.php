@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('service.service_calling_cards'),'url'=> secure_url('cc/manage'),'active' => 'no'],
        ['name' => trans('common.btn_update').' '.$row['name'],'url'=> '','active' => 'yes']
    ]])
    <link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <script src="{{ secure_asset('vendor/select-picker/js/bootstrap-select.js') }}"></script>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            @if($row['id'] != '')
                                <i class="fa fa-edit"></i>&nbsp;{{ trans('common.btn_update').' '.$row['name'] }}
                            @else
                                <i class="fa fa-plus-circle"></i>&nbsp;{{ trans('myservice.btn_add_new_pin') }}
                            @endif
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="col-md-9">
                            <form class="form-horizontal" action="{{ secure_url('cc/update') }}" method="post" id="frmUpdate" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{ $row['id'] }}">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="telecom_provider_id" class="col-md-4 control-label">{{ trans('service.telecom_provider') }}</label>
                                    <div class="col-md-8">
                                        <div class="col-md-6">
                                            <select class="form-control" name="country_id" id="country_id">
                                                <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                @if(isset($countries))
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country->id }}" @if($row['country_id'] == $country->id) selected @endif>{{ $country->nice_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-control" name="telecom_provider_id" id="telecom_provider_id" disabled >
                                                <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                @if(isset($telecom_providers))
                                                    @foreach($telecom_providers as $provider)
                                                        <?php
                                                        $tp_config = \App\Models\TelecomProvider::find($provider->id);
                                                        $src_img = $tp_config->getMedia('telecom_providers_cards')->first();
                                                        $img = !empty($src_img) ? optional($src_img)->getUrl('thumb') : 'images/no_image.png';
                                                        ?>
                                                        @if($row['id'] != '')
                                                            <option class=" @if($row['telecom_provider_id'] != $provider->id) hide @endif" data-fv="{{ $provider->face_value }}" data-image="{{ secure_asset($img) }}" data-desc="{{ $provider->description }}" data-country="{{ $provider->country_id }}" value="{{ $provider->id }}"  @if($row['telecom_provider_id'] == $provider->id) selected @endif>{{ $provider->name." ".\app\Library\AppHelper::formatAmount('EUR',$provider->face_value) }}</option>
                                                        @else
                                                            <option class="" data-fv="{{ $provider->face_value }}" data-image="{{ secure_asset($img) }}" data-desc="{{ $provider->description }}" data-country="{{ $provider->country_id }}" value="{{ $provider->id }}"  @if($row['telecom_provider_id'] == $provider->id) selected @endif>{{ $provider->name." ".\app\Library\AppHelper::formatAmount('EUR',$provider->face_value) }}</option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="name" class="col-sm-4 control-label">{{ trans('myservice.lbl_card_name') }}</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="name" name="name" value="{{ $row['name'] }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="description" class="col-sm-4 control-label">{{ trans('myservice.lbl_card_desc') }}</label>
                                    <div class="col-sm-6">
                                        <textarea class="form-control" id="description" name="description">{{ $row['description'] }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="comment_1" class="col-sm-4 control-label">{{ trans('myservice.lbl_comment1') }}</label>
                                    <div class="col-sm-6">
                                        <textarea class="form-control" id="comment_1" name="comment_1">{{ $row['comment_1'] }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="comment_2" class="col-sm-4 control-label">{{ trans('myservice.lbl_comment2') }}</label>
                                    <div class="col-sm-6">
                                        <textarea class="form-control" id="comment_2" name="comment_2">{{ $row['comment_2'] }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="validity" class="col-sm-4 control-label">{{ trans('myservice.lbl_validity') }}</label>
                                    <div class="col-sm-6">
                                        <textarea class="form-control" id="validity" name="validity">{{ $row['validity'] }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="access_number" class="col-sm-4 control-label">{{ trans('myservice.access_number') }}</label>
                                    <div class="col-sm-6">
                                        <textarea class="form-control" id="access_number" name="access_number">{{ $row['access_number'] }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="buying_price" class="col-sm-4 control-label">{{ trans('myservice.buying_price') }}</label>
                                    <div class="col-sm-6">
                                        <input type="hidden" name="face_value" id="face_value" value="">
                                        <input type="text" class="money-input form-control" name="buying_price" id="buying_price" value="{{ $row['buying_price'] }}" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="buying_price" class="col-sm-4 control-label">{{ trans('myservice.bimedia_buying_price') }}</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="money-input form-control" name="buying_price1" id="buying_price1" value="{{ $row['buying_price1'] }}" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="status">{{ trans('myservice.lbl_status') }}</label>
                                    <div class="col-md-8">
                                        <div class="checkbox">
                                            <label><input name="status" type="checkbox"
                                                          value="1" @if($row['status'] == 1) checked @endif>{{ trans('common.lbl_enabled') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4" for="status">Activate Card First</label>
                                    <div class="col-md-8">
                                        <div class="checkbox">
                                            <label><input name="activate" type="checkbox"
                                                          value="1" @if($row['activate'] == 1) checked @endif>{{ trans('common.lbl_enabled') }}</label>
                                        </div>
                                    </div>
                                </div>
                                @if($row['id'] == '')
                                    <div class="form-group">
                                        <label for="excelFile" class="col-sm-4 control-label">{{ trans('myservice.lbl_choose_excel') }}</label>
                                        <div class="col-sm-6">
                                            <input type="file" class="form-control" name="excelFile" id="excelFile">
                                        </div>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label for="aleda_product_code" class="col-sm-4 control-label">{{ trans("myservice.aleda_product_code") }}</label>
                                    <div class="col-sm-6">
                                        <select  data-size="10" data-live-search="true" class="select-picker show-tick form-control" name="aleda_product_code" id="aleda_product_code">
                                            <option value="">{{ trans('common.lbl_please_choose')  }}</option>
                                            @foreach($product_codes as $product_code)
                                                @if($product_code['productType'] == "AS" || $product_code['productType'] == "ES")
                                                    <option value="{{ $product_code['Gencod'] }}" @if($row['aleda_product_code'] == $product_code['Gencod']) selected @endif  data-subtext="{{ $product_code['description'] }}">{{  \app\Library\AppHelper::makeKeyword($product_code['ticketModel']) }} {{ str_replace(".00",'',$product_code['value']) }}&euro;</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="bimedia_product_code" class="col-sm-4 control-label">{{ trans("myservice.bimedia_product_code") }}</label>
                                    <div class="col-sm-6">
                                        <select  data-size="10" data-live-search="true" class="select-picker show-tick form-control" name="bimedia_product_code" id="bimedia_product_code">
                                            <option value="">{{ trans('common.lbl_please_choose')  }}</option>
                                            @foreach($bimedia_code as $product_code)

                                                <option value="{{ $product_code['codeProduit'] }}" @if($row['bimedia_product_code'] == $product_code['codeProduit']) selected @endif  data-subtext="{{ $product_code['operateur']}}-{{ $product_code['nomProduit'] }}">{{  \app\Library\AppHelper::makeKeyword($product_code['operateur']) }} {{ str_replace(".00",'',$product_code['valeur']) }}&euro;</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-4"></div>
                                    <div class="col-md-6">
                                        <button type="submit" id="btnSubmit" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;{{ trans("common.btn_save") }}</button>
                                        <a href="{{ secure_url('cc/manage') }}" class="btn btn-warning"><i class="fa fa-times"></i>&nbsp;{{ trans('common.btn_cancel') }}</a>
                                    </div>
                                    <div class="col-md-2"></div>
                                </div>

                            </form>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <img src="{{ secure_asset('images/no_image.png') }}" class="img-responsive center-block" id="providerImg">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $(".select-picker").selectpicker();
            //country change
            $("#country_id").change(function () {
                var cur_val = $(this).val();
                if(cur_val != ''){
                    $("#telecom_provider_id").removeAttr('disabled');
                }else{
                    $("#telecom_provider_id").val('').attr('disabled','disabled');
                    $("#name").val('');
                    $("#description").val('');
                    $("#providerImg").attr('src',"{{ secure_asset('images/no_image.png') }}");
                    $("#face_value").val('');
                }
            });
            $("#country_id").change();
            //provider change
            $("#telecom_provider_id").change(function () {
                var cur_val = $(this);
                if(cur_val.val() != ''){
                    console.log(cur_val.val());
                    var name = $("#telecom_provider_id option:selected").text();
                    var desc = $("#telecom_provider_id option:selected").data('desc');
                    var image = $("#telecom_provider_id option:selected").data('image');
                    var face_value = $("#telecom_provider_id option:selected").data('fv');
                    $("#name").val(name);
                    $("#description").val(desc);
                    $("#providerImg").attr('src',image);
                    $("#face_value").val(face_value);
                }else{
                    $("#name").val('');
                    $("#description").val('');
                    $("#face_value").val('');
                    $("#providerImg").attr('src',"{{ secure_asset('images/no_image.png') }}");
                }
            });
            $("#telecom_provider_id").change();
            @if($row['id'] != '')
            $("#telecom_provider_id").val("{{ $row['telecom_provider_id'] }}");
            @endif
            $('#frmUpdate').validate({
                // rules & options,
                rules: {
                    country_id: "required",
                    provider_id: "required",
                    name: "required",
                    buying_price: {
                        required: true,
                        max: function() {
                            return parseInt($('#face_value').val());
                        }
                    },
                },
                errorElement: "span",
                errorPlacement: function (error, element) {
                    // Add the `help-block` class to the error element
                    error.addClass("help-block");

                    if (element.prop("type") === "checkbox") {
                        error.insertAfter(element.parents("checkbox"));
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).parents(".form-group").addClass("has-error").removeClass("has-success");
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).parents(".form-group").addClass("").removeClass("has-error");
                },
                submitHandler: function (form) {
                    $.confirm({
                        title: '{{ trans('common.btn_save') }}',
                        content: '{{ trans('common.lbl_ask_proceed_form') }}',
                        buttons: {
                            "{{ trans('common.btn_save') }}": function () {
                                $("#frmService").LoadingOverlay("show");
                                $("#btnSubmit").html("<i class='fa fa-spinner fa-pulse'></i>&nbsp;{{ trans('common.btn_save_changes') }}...").attr('disabled', 'disabled');
                                form.submit();
                            },
                            "{{ strtolower(trans('common.btn_cancel')) }}": function () {

                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection