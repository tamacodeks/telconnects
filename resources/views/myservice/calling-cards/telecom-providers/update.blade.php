<link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
<div class="row">
    <div class="col-md-9">
        <form  class="form-horizontal" id="frmService" action="{{ secure_url('telecom-provider/update') }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $row['id'] }}">
            <div class="form-group">
                <label class="control-label col-md-4" for="country_id">{{ trans('service.tp_country') }}</label>
                <div class="col-md-8">
                    <select class="form-control" name="country_id" id="country_id" data-live-search="true" >
                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                        @if(isset($countries))
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" @if( $country->id == $row['country_id']) selected @endif>{{ $country->nice_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <input type="hidden" name="name" id="name" value="{{ $row['name'] }}">
            <div class="form-group">
                <label class="control-label col-md-4" for="tp_config_id">{{ trans('service.name') }}</label>
                <div class="col-md-8">
                    <select class="form-control" name="tp_config_id" id="tp_config_id" data-live-search="true" >
                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                        @if(isset($telecom_providers_config))
                            @foreach($telecom_providers_config as $value)
                                <?php
                                $tp_config =  \App\Models\TelecomProviderConfig::find($value->id);
                                $src_img = $tp_config->getMedia('telecom_providers')->first();
                                $img_tp = !empty($src_img) ? optional($src_img)->getUrl('thumb') : secure_asset('images/no_image.png');
                                ?>
                                <option class="@if($value->id == $row['tp_config_id']) @else hide @endif" data-image="{{ $img_tp }}" data-country_id="{{ $value->country_id }}" value="{{ $value->id }}" @if($value->id == $row['tp_config_id']) selected @endif>{{ $value->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-4" for="description">{{ trans('common.lbl_desc') }}</label>
                <div class="col-md-8">
                    <textarea class="form-control" name="description" id="description">{{ $row['description'] }}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-4" for="face_value">{{ trans('myservice.face_value') }}</label>
                <div class="col-md-8">
                    <input type="text" class="money-input form-control" name="face_value" id="face_value" value="{{ $row['face_value'] }}">
                </div>
            </div>
            <div class="form-group">
                <label for="image" class="col-sm-4 control-label">{{ trans('myservice.image') }}</label>
                <div class="col-sm-8">
                    <?php
                    if($row['id'] != ''){
                        $tp_config =  \App\Models\TelecomProvider::find($row['id']);
                        $src_img = $tp_config->getMedia('telecom_providers_cards')->first();
                        $img = !empty($src_img) ? optional($src_img)->getUrl('thumb') : secure_asset('images/no_image.png');
                    }else{
                        $img = secure_asset('images/no_image.png');
                    }
                    ?>
                    <img src="{{ $img }}" id="img_holder" style="width: 150px">
                    <input type="file" name="image" class="form-control" id="image">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-4" for="status">{{ trans('service.edit_telecom_provider') }}</label>
                <div class="col-md-8">
                    <div class="checkbox">
                        <label><input name="bimedia_card" type="checkbox"
                                      value="1" @if($row['bimedia_card'] == 1) checked @endif>{{ trans('common.select_bimedia') }}</label>
                    </div>
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
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <button type="submit" id="btnSubmit" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;{{ trans("common.btn_save") }}</button>
                </div>
                <div class="col-md-4"></div>
            </div>
        </form>
    </div>
    <div class="col-md-3">
        <img src="{{ secure_asset('images/no_image.png') }}" id="provider_img" class="img-responsive">
    </div>
</div>
<script>
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#img_holder').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $(document).ready(function () {
        $('.money-input').autoNumeric({
            aSep: ''
        });
        $('.money-input').blur(function () {
            if($(this).val() != ''){
                var striped_val = Math.abs($(this).val());
                $(this).val(striped_val);
            }
        })

        $("#image").change(function () {
            readURL(this);
        });
        $("#country_id").change(function () {
            var current_val =$(this).val();
            if(current_val != ''){
                $("#tp_config_id > option").each(function() {
                    if($(this).data('country_id') == current_val){
                        $(this).removeClass('hide');
                    }else{
                        $(this).addClass('hide');
                    }
                });
                $("#tp_config_id").val('');
                $('#provider_img').attr('src',"{{ secure_asset('images/no_image.png') }}");
                $("#name").val();
            }
        });
        $("#tp_config_id").change(function () {
            var current_val =$(this);
            if(current_val.val() != ''){
                var selectedProviderImage = $("#tp_config_id option:selected").data('image');
                $('#provider_img').attr('src',selectedProviderImage);
                $("#name").val($("#tp_config_id option:selected").text());
            }
        });
        $("#country_id").change();
        @if($row['tp_config_id'] != '')
        $("#tp_config_id").val("{{ $row['tp_config_id']  }}");
        @endif
        $("#tp_config_id").change();
        $('#frmService').validate({
            // rules & options,
            rules: {
                country_id: "required",
                name: "required",
                tp_config_id: "required",
                face_value: "required"
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