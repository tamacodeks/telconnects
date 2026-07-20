<form id="frmUpdate" class="form-horizontal" action="{{ secure_url('cc/upload-pins') }}" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="hidden" name="id" value="{{ $row['id'] }}">
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
    @if(isset($show_bp) && $show_bp == "YES")
        <div class="form-group">
            <label for="buying_price" class="col-sm-4 control-label">{{ trans('myservice.buying_price') }}</label>
            <div class="col-sm-6">
                <input type="hidden" name="face_value" id="face_value" value="{{ $row['face_value'] }}">
                <input type="text" class="money-input form-control" name="buying_price" id="buying_price" value="{{ $row['buying_price'] }}" >
            </div>
        </div>
    @else
        <input type="hidden" name="face_value" id="face_value" value="{{ $row['face_value'] }}">
        <input type="hidden" class="form-control" name="buying_price" id="buying_price" value="{{ $row['buying_price'] }}" >
    @endif
    <div class="form-group">
        <label for="excelFile" class="col-sm-4 control-label">{{ trans('myservice.lbl_choose_excel') }}</label>
        <div class="col-sm-6">
            <input type="file" class="form-control" name="excelFile" id="excelFile">
        </div>
    </div>
    <div class="form-group m-t-20 m-b-20">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <button type="submit" id="btnSubmit" class="btn btn-primary"><i class="fa fa-upload"></i>&nbsp;{{ trans("myservice.btn_upload_pins") }}</button>
        </div>
        <div class="col-md-4"></div>
    </div>
</form>
<script>
    $(document).ready(function () {
        $('#frmUpdate').validate({
            // rules & options,
            rules: {
                excelFile: "required",
                @if(isset($show_bp) && $show_bp == "YES")
                buying_price: {
                    required: true,
                    max: function() {
                        return parseInt($('#face_value').val());
                    }
                }
                @endif
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
                            $("#frmUpdate").LoadingOverlay("show");
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