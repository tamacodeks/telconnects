<div class="col-md-12">
    <form action="{{ $contactActionUrl ?? secure_url('cc-pin-history/contact') }}" class="form-horizontal" id="frmEnquiry" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="fwdStatus" value="{{ isset($ticket_fwd) ? "true" : "false" }}">
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label for="account" class="col-sm-4 control-label">Account</label>
                    <div class="col-sm-8">
                        <h5>{{ auth()->user()->username }}</h5>
                    </div>
                </div>
                <div class="form-group">
                    <label for="account" class="col-sm-4 control-label">To</label>
                    <div class="col-sm-8">
                        <h5>{{ optional(\App\User::find(auth()->user()->parent_id))->username }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="form-group">
                    <label for="account" class="col-sm-5 control-label">Card Name</label>
                    <div class="col-sm-7">
                        <h5>{{ $card->name }}</h5>
                    </div>
                </div>
                <div class="form-group">
                    <label for="account" class="col-sm-5 control-label">Face Value</label>
                    <div class="col-sm-7">
                        <h5>{{ $card->face_value }}</h5>
                    </div>
                </div>
                <div class="form-group">
                    <label for="account" class="col-sm-5 control-label">Serial</label>
                    <div class="col-sm-7">
                        <h5>{{ $card->serial }}</h5>
                    </div>
                </div>
                <div class="form-group">
                    <label for="account" class="col-sm-5 control-label">Pin</label>
                    <div class="col-sm-7">
                        <h5>{{ $card->pin }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <input type="hidden" name="pin_id" value="{{ $pin_id }}">
                <div class="form-group">
                    <label for="account" class="col-sm-4 control-label">Type</label>
                    <div class="col-sm-8">
                        <div class="radio">
                            <label><input type="radio" value="card_issue" name="type">Card Issue</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" value="topup_request" name="type">Topup Request</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" value="others" name="type">Others</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message" class="col-sm-4 control-label">Message</label>
                    <div class="col-sm-8">
                        <textarea class="form-control" name="message" id="message"></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message" class="col-sm-4 control-label">&nbsp;</label>
                    <div class="col-sm-8">
                        <button class="btn btn-primary" id="btnSubmitSend" type="submit"><i class="fa fa-paper-plane"></i>&nbsp;{{ trans('service.send') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
    $('#frmEnquiry').validate({
        // rules & options,
        rules: {
            type: "required",
            message: "required"
        },
        messages: {
            type: "{{ trans('myservice.err_type') }}",
            message: "{{ trans('myservice.err_message') }}"
        },
        errorElement: "div",
        errorPlacement: function (error, element) {
            // Add the `help-block` class to the error element
            error.addClass("help-block");

            if (element.prop("type") === "checkbox") {
                error.insertAfter(element.parents("checkbox"));
            }
            if (element.prop("type") === "radio") {
                error.insertAfter(element.parents("radio"));
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
            $("#frmEnquiry").LoadingOverlay("show");
            $("#btnSubmitSend").html("<i class='fa fa-spinner fa-pulse'></i>&nbsp;{{ trans('common.processing') }}").attr('disabled', 'disabled');
            form.submit();
        }
    });
</script>
