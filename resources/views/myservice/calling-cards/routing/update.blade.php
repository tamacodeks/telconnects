<link href="{{ secure_asset('vendor/select-picker/css/bootstrap-select.min.css') }}" rel="stylesheet">
<div class="row">
    <div class="col-md-9">
        <form  class="form-horizontal" id="frmService" action="{{ secure_url('service_provider/update') }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $row['id'] }}">
            <div class="form-group">
                <label class="control-label col-md-4" for="country_id">{{ trans('service.primary') }}</label>
                <div class="col-md-8">
                    <select class="form-control" name="service_provider" id="service_provider" data-live-search="true" >
                        @if( $row['primary'] == 'Aleda')
                            <option value="Aleda" selected>Aleda</option>
                            <option value="Bimedia">Bimedia</option>
                        @else
                            <option value="Bimedia" selected>Bimedia</option>
                            <option value="Aleda" >Aleda</option>
                        @endif
                    </select>
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
</div>
<script>
    $(document).ready(function () {
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