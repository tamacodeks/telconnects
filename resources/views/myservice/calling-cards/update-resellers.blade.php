<link href="{{ secure_asset('vendor/multi-select/css/multi-select.dist.css') }}" rel="stylesheet">
<form class="form-horizontal" id="frmService" action="{{ secure_url('cc/update/reseller-access') }}" method="POST"
      enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="hidden" name="id" value="{{ $cc_id }}">
    <div class="form-group">
        <label class="control-label col-md-4" for="retailers">{{ trans('myservice.lbl_choose_retailers') }}</label>
        <div class="col-md-8">
            <button type="button" class="btn btn-sm btn-primary"
                    id='select-all'>{{ trans('common.lbl_select_all') }}
            </button>
            <button type="button" class="btn btn-sm btn-danger" id='deselect-all'
                    style="margin: 10px;">{{ trans('common.lbl_deselect_all') }}
            </button>
            <select class="form-control" id="retailers" name="retailers[]" multiple>
                @if(isset($users))
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->username }}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <button type="submit" id="btnSubmit" class="btn btn-primary"><i
                        class="fa fa-save"></i>&nbsp;{{ trans("common.btn_save") }}</button>
        </div>
        <div class="col-md-4"></div>
    </div>
</form>
<script src="{{ secure_asset('vendor/multi-select/js/jquery.multi-select.js') }}" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        /**
         * App Register Configuration
         */
        $('#retailers').multiSelect({
            selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='{{ trans('users.lbl_user_name') }}'>",
            selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='{{ trans('users.lbl_user_name') }}'>",
            afterInit: function (ms) {
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    $selectionSearch = that.$selectionUl.prev(),
                    selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
                    selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                    .on('keydown', function (e) {
                        if (e.which === 40) {
                            that.$selectableUl.focus();
                            return false;
                        }
                    });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                    .on('keydown', function (e) {
                        if (e.which == 40) {
                            that.$selectionUl.focus();
                            return false;
                        }
                    });
            },
            afterSelect: function () {
                this.qs1.cache();
                this.qs2.cache();
            },
            afterDeselect: function () {
                this.qs1.cache();
                this.qs2.cache();
            },
            selectableFooter: "<div class='custom-header'>{{ trans('common.avail_retailers') }}</div>",
            selectionFooter: "<div class='custom-header'>{{ trans('myservice.lbl_selected_retailers')}}</div>"
        });
        $('#select-all').click(function () {
            $('#retailers').multiSelect('select_all');
            return false;
        });
        $('#deselect-all').click(function () {
            $('#retailers').multiSelect('deselect_all');
            return false;
        });
        @if(isset($row))
        $("#retailers").multiSelect('select', [@foreach ($row as $user) "{{ $user->user_id }}", @endforeach]);
        @endif

        $('#frmService').validate({
            // rules & options,
            rules: {},
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