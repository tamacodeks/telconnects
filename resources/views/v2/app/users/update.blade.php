@extends('v2.layout.simple.master')

@php
    $isEdit = !empty($row['id']);
    $userName = trim($row['username'] ?? '');
    $page_title = $isEdit
        ? trans('users.btn_update_user') . ' ' . ($userName ?: trans('users.lbl_user'))
        : trans('users.btn_add_user');
    $usersTitle = trans('users.lbl_users');
    $panelSubtitle = trans('users.user_update_subtitle');
    $saveLabel = trans('users.btn_save');
    $cancelLabel = trans('users.btn_cancel');
    $chooseLabel = trans('users.lbl_please_choose');
    $proceedLabel = trans('users.confirm_save_message');
    $savingLabel = trans('users.btn_saving');
    $selectedCountry = old('country_id', $row['country_id'] ?? '');
    $selectedCurrency = old('currency', $row['currency'] ?? '');
    $selectedTimezone = old('timezone', $row['timezone'] ?? '');
    $selectedGroup = old('group_id', $row['group_id'] ?? '');
    $selectedParent = old('parent_id', $row['parent_id'] ?? '');
    $v2UsersCssVersion = @filemtime(public_path('assets/css/v2-users.css')) ?: time();
    $userInitial = strtoupper(substr($userName ?: ($row['first_name'] ?? trans('users.lbl_user')), 0, 1));
    $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: ($userName ?: trans('users.btn_add_user'));
    $statusText = (int) old('status', $row['status'] ?? 0) === 1 ? trans('users.active') : trans('users.inactive');
    $groupName = '';
    foreach ($user_groups as $user_group) {
        if ((string) $selectedGroup === (string) $user_group->id) {
            $groupName = $user_group->name;
            break;
        }
    }
    $pageSections = [
        ['id' => 'user-profile', 'eyebrow' => trans('users.section_01'), 'title' => trans('users.section_profile'), 'copy' => trans('users.section_profile_copy')],
        ['id' => 'user-security', 'eyebrow' => trans('users.section_02'), 'title' => trans('users.section_security'), 'copy' => trans('users.section_security_copy')],
        ['id' => 'user-limits', 'eyebrow' => trans('users.section_03'), 'title' => trans('users.section_limits'), 'copy' => trans('users.section_limits_copy')],
        ['id' => 'user-services', 'eyebrow' => trans('users.section_04'), 'title' => trans('users.section_services'), 'copy' => trans('users.section_services_copy')],
        ['id' => 'user-webhook', 'eyebrow' => trans('users.section_05'), 'title' => trans('users.section_webhook'), 'copy' => trans('users.section_webhook_copy')],
    ];
@endphp

@section('body_class', 'v2-users-page v2-history-themed-page v2-user-update-page')

@section('style')
    <link href="{{ secure_asset('vendor/intl-input/css/intlTelInput.css') }}" rel="stylesheet">
    <link href="{{ asset('css/v2/app-settings-v2.css') }}?v={{ @filemtime(public_path('css/v2/app-settings-v2.css')) ?: time() }}" rel="stylesheet">
    <link href="{{ asset('css/v2/profile-v2.css') }}?v={{ @filemtime(public_path('css/v2/profile-v2.css')) ?: time() }}" rel="stylesheet">
    <link href="{{ asset('assets/css/v2-users.css') }}?v={{ $v2UsersCssVersion }}&theme=auto" rel="stylesheet">
@endsection

@include('v2.layout.simple.breadcrumb', ['data' => [
    ['name' => $usersTitle, 'url'=> route('users.v2'), 'active' => 'no'],
    ['name' => $page_title, 'url'=> '', 'active' => 'yes'],
]])

@section('content')
    <div class="container-fluid app-settings-v2-page v2-users v2-user-form v2-user-update-form">
        <form class="app-settings-v2-form form-horizontal" id="frmUserV2" action="{{ route('user.update.v2.save') }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $row['id'] ?? '' }}">
            <input type="file" class="v2-user-file-input" name="image" id="image" accept="image/*">

            <div class="profile-v2-account-card v2-user-account-card">
                <div class="profile-v2-account-main">
                    <div class="profile-v2-avatar-box">
                        <img src="{{ secure_asset($row['user_image'] ?? 'images/avatar.png') }}" id="img_holder_hero" alt="">
                        <span class="profile-v2-avatar-fallback">{{ $userInitial }}</span>
                        <label class="profile-v2-avatar-camera" for="image" aria-label="{{ trans('users.lbl_image') }}">
                            <i class="fa fa-camera" aria-hidden="true"></i>
                        </label>
                    </div>
                    <div class="profile-v2-account-title">
                        <span>{{ $isEdit ? trans('users.user_account') : trans('users.new_user_account') }}</span>
                        <h1>{{ $fullName }}</h1>
                        <strong>{{ $groupName ?: trans('users.lbl_user_group') }} / {{ $userName ?: trans('users.lbl_user_name') }}</strong>
                    </div>
                </div>

                <div class="profile-v2-account-meta">
                    <div class="profile-v2-meta-item">
                        <span>{{ trans('users.lbl_user_access') }}</span>
                        <strong class="profile-v2-pill {{ $statusText === 'Active' ? 'is-success' : 'is-muted' }}">{{ $statusText }}</strong>
                    </div>
                    <div class="profile-v2-meta-item">
                        <span><i class="fa fa-clock" aria-hidden="true"></i>{{ $isEdit ? trans('users.last_modified') : trans('users.mode') }}</span>
                        <strong>{{ $isEdit ? (empty($row['updated_at']) ? ($row['created_at'] ?? '-') : $row['updated_at']) : trans('users.create') }}</strong>
                    </div>
                </div>
            </div>

            <div class="app-settings-v2-shell v2-user-settings-shell">
                <aside class="app-settings-v2-rail v2-user-form-rail" aria-label="{{ trans('users.user_map') }}">
                    <div class="app-settings-v2-rail-card">
                        <span class="app-settings-v2-rail-label">{{ trans('users.user_map') }}</span>
                        <h3>{{ $page_title }}</h3>
                        <p>{{ $panelSubtitle }}</p>

                        <div class="app-settings-v2-rail-meta">
                            <span><strong>{{ count($pageSections) }}</strong> {{ trans('users.sections') }}</span>
                            <span><strong>{{ $isEdit ? trans('users.edit_mode') : trans('users.new_mode') }}</strong> {{ trans('users.mode') }}</span>
                        </div>

                        <nav class="app-settings-v2-section-nav">
                            @foreach($pageSections as $pageSection)
                                <a class="app-settings-v2-section-link{{ $loop->first ? ' is-active' : '' }}" href="#{{ $pageSection['id'] }}">
                                    <span class="app-settings-v2-section-kicker">{{ $pageSection['eyebrow'] }}</span>
                                    <strong>{{ $pageSection['title'] }}</strong>
                                    <small>{{ $pageSection['copy'] }}</small>
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </aside>

                <div class="app-settings-v2-stack v2-user-form-main">
                                <section class="app-settings-v2-panel app-settings-v2-panel-wide v2-user-form-section is-active" id="user-profile">
                                    <h4>{{ trans('users.lbl_user_info') }}</h4>
                                    <div class="v2-user-field-grid">
                                        <label>
                                            <span>{{ trans('users.lbl_user_group') }}</span>
                                            <select class="form-control" name="group_id" id="group_id" required>
                                                <option value="">{{ $chooseLabel }}</option>
                                                @foreach($user_groups as $user_group)
                                                    <option value="{{ $user_group->id }}" @if((string) $selectedGroup === (string) $user_group->id) selected @endif>{{ $user_group->name }}</option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label id="managerRetailer">
                                            <span>{{ trans('users.lbl_user_mgr') }}</span>
                                            <select class="form-control" name="parent_id" id="parent_id">
                                                <option value="">{{ trans('users.none') }}</option>
                                                @foreach($parent_manager as $value)
                                                    <option value="{{ $value->id }}" @if((string) $selectedParent === (string) $value->id) selected @endif>{{ $value->username }}</option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label class="app-settings-v2-switch v2-user-check">
                                            <input type="hidden" name="status" value="0">
                                            <input name="status" type="checkbox" value="1" @if((int) old('status', $row['status'] ?? 0) === 1) checked @endif>
                                            <span></span>
                                            <strong>{{ trans('users.user_access_enabled') }}</strong>
                                        </label>

                                        @if(auth()->user()->group_id == 1)
                                            <label class="app-settings-v2-switch v2-user-check">
                                                <input type="hidden" name="v2_enabled" value="0">
                                                <input name="v2_enabled" id="v2_enabled" type="checkbox" value="1" @if((int) old('v2_enabled', $row['v2_enabled'] ?? 0) === 1) checked @endif>
                                                <span></span>
                                                <strong>{{ trans('users.v2_access') }}</strong>
                                            </label>
                                        @endif

                                        <label>
                                            <span>{{ trans('users.lbl_user_name') }}</span>
                                            <input class="form-control" type="text" name="username" id="username" value="{{ old('username', $row['username'] ?? '') }}" required>
                                            <span id="usernameerror"></span>
                                        </label>

                                        <label>
                                            <span>{{ trans('users.lbl_user_fname') }}</span>
                                            <input class="form-control" type="text" name="first_name" value="{{ old('first_name', $row['first_name'] ?? '') }}" required>
                                        </label>

                                        <label>
                                            <span>{{ trans('users.lbl_user_lname') }}</span>
                                            <input class="form-control" type="text" name="last_name" value="{{ old('last_name', $row['last_name'] ?? '') }}" required>
                                        </label>

                                        <label>
                                            <span>{{ trans('users.lbl_user_email') }}</span>
                                            <input class="form-control" type="text" name="email" value="{{ old('email', $row['email'] ?? '') }}">
                                        </label>

                                        <label>
                                            <span>{{ trans('users.lbl_mobile_no') }}</span>
                                            <input class="form-control" type="text" name="mobile" id="mobile" value="{{ old('mobile', !empty($row['mobile']) ? '+'.$row['mobile'] : '') }}" required>
                                            <span id="error-msg" class="text-danger help-block hide">{{ trans('users.error_mobile_no') }}</span>
                                        </label>

                                        <label>
                                            <span>{{ trans('users.lbl_user_country') }}</span>
                                            <select class="form-control" name="country_id" id="country_id" required>
                                                <option value="">{{ $chooseLabel }}</option>
                                                @foreach($countries as $country)
                                                    <option value="{{ $country->id }}" @if((string) $selectedCountry === (string) $country->id) selected @endif>{{ $country->nice_name }}</option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label>
                                            <span>{{ trans('users.lbl_user_currency') }}</span>
                                            <select class="form-control" name="currency" id="currency">
                                                <option value="">{{ $chooseLabel }}</option>
                                                @foreach($countries as $country)
                                                    <option class="{{ $country->id }}_curr hide" value="{{ $country->currency }}" @if((string) $selectedCurrency === (string) $country->currency) selected @endif>{{ $country->currency }}</option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label>
                                            <span>{{ trans('users.lbl_user_timezone') }}</span>
                                            <select class="form-control" name="timezone" id="timezone">
                                                <option value="">{{ $chooseLabel }}</option>
                                                @foreach($countries as $country)
                                                    <option class="{{ $country->id }}_tz hide" value="{{ $country->timezone }}" @if((string) $selectedTimezone === (string) $country->timezone) selected @endif>{{ $country->timezone }}</option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label class="v2-user-field-wide">
                                            <span>{{ trans('users.lbl_user_address') }}</span>
                                            <textarea class="form-control" name="address">{{ old('address', $row['address'] ?? '') }}</textarea>
                                        </label>
                                    </div>
                                </section>

                                <section class="app-settings-v2-panel app-settings-v2-panel-wide v2-user-form-section" id="user-security">
                                    <h4>{{ trans('users.authentication') }}</h4>
                                    <div class="v2-user-field-grid">
                                        <label>
                                            <span>{{ trans('users.lbl_password') }}</span>
                                            <input class="form-control" type="password" name="password" id="password" @if(!$isEdit) required @endif>
                                            @if($isEdit)
                                                <small class="help-block">{{ trans('users.password_help_block') }}</small>
                                            @endif
                                        </label>

                                        <label>
                                            <span>{{ trans('users.authentication_method') }}</span>
                                            <select class="form-control" name="authentication_method" id="authentication_method">
                                                <option value="0" @if((int) old('authentication_method', $row['method'] ?? 0) === 0) selected @endif>{{ trans('users.auth_none') }}</option>
                                                <option value="1" @if((int) old('authentication_method', $row['method'] ?? 0) === 1) selected @endif>{{ trans('users.auth_ip_otp') }}</option>
                                                <option value="2" @if((int) old('authentication_method', $row['method'] ?? 0) === 2) selected @endif>{{ trans('users.auth_totp') }}</option>
                                            </select>
                                        </label>

                                        <label>
                                            <span>{{ trans('users.allowed_active_devices') }}</span>
                                            <select class="form-control" name="active_device_limit" id="active_device_limit">
                                                <option value="1" @if((int) old('active_device_limit', $row['max_active_sessions'] ?? 1) === 1) selected @endif>1</option>
                                                <option value="2" @if((int) old('active_device_limit', $row['max_active_sessions'] ?? 1) === 2) selected @endif>2</option>
                                            </select>
                                        </label>

                                        <label class="app-settings-v2-switch v2-user-check">
                                            <input type="hidden" name="is_api_user" value="0">
                                            <input name="is_api_user" id="is_api_user" type="checkbox" value="1" @if((int) old('is_api_user', $row['is_api_user'] ?? 0) === 1) checked @endif>
                                            <span></span>
                                            <strong>{{ trans('users.lbl_user_api_access') }}</strong>
                                        </label>
                                    </div>
                                </section>

                                <section class="app-settings-v2-panel app-settings-v2-panel-wide v2-user-form-section" id="user-limits">
                                    <h4>{{ trans('users.lbl_user_balance') }}</h4>
                                    <div class="v2-user-field-grid">
                                        <label>
                                            <span>{{ trans('users.lbl_amount') }}</span>
                                            <input class="form-control money-input" type="text" name="amount" value="{{ old('amount') }}">
                                        </label>
                                        <label>
                                            <span>{{ trans('users.lbl_credit_limit') }}</span>
                                            <input class="form-control money-input" type="text" name="credit_limit" value="{{ old('credit_limit') }}">
                                        </label>
                                        <label>
                                            <span>{{ trans('users.lbl_tbl_user_limit') }}</span>
                                            <input class="form-control money-input" type="text" name="daily_limit" value="{{ old('daily_limit') }}">
                                        </label>
                                        <label class="v2-user-field-wide">
                                            <span>{{ trans('users.lbl_description') }}</span>
                                            <textarea class="form-control" name="description">{{ old('description') }}</textarea>
                                        </label>
                                    </div>
                                    <input type="hidden" id="same_amount_manager" name="same_amount_manager" value="1">
                                </section>

                                <section class="app-settings-v2-panel app-settings-v2-panel-wide v2-user-form-section" id="user-services">
                                    <h4>{{ trans('users.lbl_user_commission_setup') }} & {{ trans('users.lbl_user_access') }}</h4>
                                    <div class="v2-user-field-grid">
                                        <label>
                                            <span>{{ trans('users.calling_cards_rate_table') }}</span>
                                            <select class="form-control" name="rate_group_id" id="rate_group_id">
                                                <option value="">{{ $chooseLabel }}</option>
                                                @foreach($rate_table_groups as $rate_table_group)
                                                    <option value="{{ $rate_table_group->id }}" @if((string) old('rate_group_id', $row['rate_group_id'] ?? '') === (string) $rate_table_group->id) selected @endif>{{ $rate_table_group->name }}</option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label class="app-settings-v2-switch v2-user-check">
                                            <input type="hidden" name="pin_print_again" value="0">
                                            <input type="checkbox" name="pin_print_again" value="1" @if((int) old('pin_print_again', $row['pin_print_again'] ?? 0) === 1) checked @endif>
                                            <span></span>
                                            <strong>{{ trans('users.can_print_again') }}</strong>
                                        </label>

                                        <label class="app-settings-v2-switch v2-user-check">
                                            <input type="hidden" name="enable_ip" value="0">
                                            <input type="checkbox" name="enable_ip" value="1" @if((int) old('enable_ip', $row['enable_ip'] ?? 0) === 1) checked @endif>
                                            <span></span>
                                            <strong>{{ trans('users.ip_address_check') }}</strong>
                                        </label>
                                    </div>

                                    @if(in_array(auth()->user()->group_id, [2, 3]))
                                        <div class="v2-user-service-grid">
                                            @foreach($services as $service)
                                                @if($service->status == 1 && \app\Library\AppHelper::skip_service_as_menu(str_slug($service->name, '-')))
                                                    <label class="app-settings-v2-switch v2-user-check">
                                                        <input type="hidden" name="service_{{ $service->id }}" value="0">
                                                        <input name="service_{{ $service->id }}" type="checkbox" value="1" @if((int) old('service_'.$service->id, \app\Library\AppHelper::user_access($service->id, $row['id'] ?? 0)) === 1) checked @endif>
                                                        <span></span>
                                                        <strong>{{ $service->name }}</strong>
                                                    </label>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    @if(auth()->user()->group_id == 2 && $isEdit)
                                        <input type="hidden" name="m_commission" value="{{ old('m_commission', \app\Library\DBHelper::getCommission($row['id'], 2)) }}">
                                        <input type="hidden" name="r_commission" value="{{ old('r_commission', optional(\App\Models\Manager_commission::where('user_id', $row['id'])->where('service_id', 2)->first())->commission) }}">
                                    @endif
                                </section>

                                <section class="app-settings-v2-panel app-settings-v2-panel-wide v2-user-form-section" id="user-webhook">
                                    <h4>{{ trans('users.webhook_information') }}</h4>
                                    <div class="v2-user-field-grid">
                                        <label>
                                            <span>{{ trans('users.api_url') }}</span>
                                            <input type="text" class="form-control" name="web_hook_url" value="{{ old('web_hook_url', $row['web_hook_url'] ?? '') }}">
                                        </label>
                                        <label>
                                            <span>{{ trans('users.api_uri_endpoint') }}</span>
                                            <input type="text" class="form-control" name="web_hook_uri" value="{{ old('web_hook_uri', $row['web_hook_uri'] ?? '') }}">
                                        </label>
                                        <label>
                                            <span>{{ trans('users.access_token') }}</span>
                                            <input type="text" class="form-control" name="web_hook_token" value="{{ old('web_hook_token', $row['web_hook_token'] ?? '') }}">
                                        </label>
                                        <label>
                                            <span>{{ trans('users.daily_limit') }}</span>
                                            <input class="form-control money-input" type="text" name="daily" value="{{ old('daily', $row['daily'] ?? '') }}">
                                        </label>
                                        <label>
                                            <span>{{ trans('users.weekly_limit') }}</span>
                                            <input class="form-control money-input" type="text" name="weekly" value="{{ old('weekly', $row['weekly'] ?? '') }}">
                                        </label>
                                        <label>
                                            <span>{{ trans('users.monthly_limit') }}</span>
                                            <input class="form-control money-input" type="text" name="monthly" value="{{ old('monthly', $row['monthly'] ?? '') }}">
                                        </label>
                                    </div>
                                </section>
                </div>
            </div>

            <div class="app-settings-v2-actions v2-user-form-actions-bar">
                <div class="app-settings-v2-action-state">
                    <i class="fa fa-user-edit" aria-hidden="true"></i>
                    <span>{{ $isEdit ? $page_title : trans('users.create_user') }}</span>
                </div>
                <div class="app-settings-v2-action-buttons">
                    <a href="{{ route('users.v2') }}" class="app-settings-v2-btn secondary">
                        <i class="fa fa-times" aria-hidden="true"></i>
                        <span>{{ $cancelLabel }}</span>
                    </a>
                    <button type="submit" id="btnSubmit" class="app-settings-v2-btn primary">
                        <i class="fa fa-save" aria-hidden="true"></i>
                        <span>{{ $saveLabel }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script src="{{ secure_asset('vendor/intl-input/js/intlTelInput.js') }}?v={{ @filemtime(public_path('vendor/intl-input/js/intlTelInput.js')) ?: time() }}" type="text/javascript"></script>
    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#img_holder_hero').attr('src', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(function () {
            $("#image").on("change", function () {
                readURL(this);
            });

            var sectionLinks = $(".v2-user-form-rail .app-settings-v2-section-link");

            function setActiveSection(sectionId) {
                sectionLinks.removeClass("is-active")
                    .filter('[href="#' + sectionId + '"]')
                    .addClass("is-active");
                $(".v2-user-form-section").removeClass("is-active")
                    .filter("#" + sectionId)
                    .addClass("is-active");
            }

            sectionLinks.on("click", function (event) {
                event.preventDefault();
                var sectionId = String($(this).attr("href") || "").replace("#", "");
                if (sectionId) {
                    setActiveSection(sectionId);
                }
            });

            function syncGroupControls() {
                var group = String($("#group_id").val() || "");
                $("#managerRetailer").toggleClass("hide", ["4", "5", "6"].indexOf(group) === -1);
                if (group === "5") {
                    $("#is_api_user").prop("checked", true);
                }
            }

            $("#group_id").on("change", syncGroupControls);
            syncGroupControls();

            function syncActiveDeviceAuthMethod() {
                if ($("#active_device_limit").val() === "2") {
                    $("#authentication_method").val("2");
                }
            }

            $("#active_device_limit").on("change", syncActiveDeviceAuthMethod);
            $("#authentication_method").on("change", syncActiveDeviceAuthMethod);
            syncActiveDeviceAuthMethod();

            $("#country_id").on("change", function () {
                var currency = $(this).find(":selected").val() + "_curr";
                var timezone = $(this).find(":selected").val() + "_tz";
                $("#currency > option").each(function () {
                    $(this).toggleClass("hide", !$(this).hasClass(currency));
                    if ($(this).hasClass(currency)) {
                        $(this).prop("selected", true);
                    }
                });
                $("#timezone > option").each(function () {
                    $(this).toggleClass("hide", !$(this).hasClass(timezone));
                    if ($(this).hasClass(timezone)) {
                        $(this).prop("selected", true);
                    }
                });
            }).trigger("change");

            var telInput = $("#mobile");
            if (typeof telInput.intlTelInput === "function") {
                telInput.intlTelInput({
                    initialCountry: "fr",
                    nationalMode: true,
                    formatOnDisplay: true,
                    utilsScript: "{{ secure_asset('vendor/intl-input/js/utils.js') }}"
                });
            } else if (window.intlTelInput && telInput.get(0)) {
                window.intlTelInput(telInput.get(0), {
                    initialCountry: "fr",
                    nationalMode: true,
                    formatOnDisplay: true
                });
            }

            $("#frmUserV2").validate({
                rules: {
                    group_id: "required",
                    @if(!$isEdit)
                    password: "required",
                    @endif
                    username: "required",
                    first_name: "required",
                    last_name: "required",
                    mobile: "required",
                    country_id: "required"
                },
                errorElement: "span",
                errorPlacement: function (error, element) {
                    error.addClass("help-block");
                    error.insertAfter(element);
                },
                highlight: function (element) {
                    $(element).parents("label").addClass("has-error");
                },
                unhighlight: function (element) {
                    $(element).parents("label").removeClass("has-error");
                },
                submitHandler: function (form) {
                    var saveLabel = {!! json_encode($saveLabel) !!};
                    var cancelLabel = {!! json_encode(strtolower($cancelLabel)) !!};
                    var savingLabel = {!! json_encode($savingLabel) !!};
                    var confirmButtons = {};
                    confirmButtons[saveLabel] = function () {
                        $("#btnSubmit")
                            .html("<i class='fa fa-refresh fa-spin'></i>&nbsp;" + savingLabel + "...")
                            .attr("disabled", "disabled");
                        form.submit();
                    };
                    confirmButtons[cancelLabel] = function () {};

                    $.confirm({
                        title: saveLabel,
                        content: {!! json_encode($proceedLabel) !!},
                        buttons: confirmButtons
                    });
                }
            });

            $("#username").on("change", function () {
                $.ajax({
                    url: "{{ secure_url('check_username') }}",
                    headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
                    type: "GET",
                    data: {username: this.value},
                    dataType: "json",
                    success: function (data) {
                        if (data.success === false) {
                            $("#username").addClass("error").focus();
                            $("#btnSubmit").attr("disabled", "disabled");
                            $("#usernameerror").show().html('<span style="color:#a94442;">' + data.message + '</span>');
                            return;
                        }
                        $("#btnSubmit").removeAttr("disabled");
                        $("#username").removeClass("error");
                        $("#usernameerror").hide();
                    }
                });
            });
        });
    </script>
@endsection
