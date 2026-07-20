<div class="tama-v2-step">
    <div class="tama-v2-mobile-card">
        <div class="tama-v2-mobile-input">
            <input class="form-control" id="mobile" name="mobile" type="tel" autofocus placeholder="+">
            <span class="tama-v2-error hide" id="span_mobile">{{ trans('topup_v2.error_mobile_no') }}</span>
            <input type="hidden" id="countryCode">
            <input type="hidden" id="countryIso">
        </div>
        <div class="tama-v2-mobile-actions">
            <button type="button" id="btnFetchProviders" class="btn btn-primary" disabled>
                <i class="fa fa-search-plus"></i>&nbsp;{{ trans('topup_v2.filter_lbl_search') }}
            </button>
            <a href="javascript:void(0);" id="changeNumberLink" class="tama-v2-change-link hide">
                {{ trans('topup_v2.change_number') }}
            </a>
        </div>
        <div id="mobileLoader" class="tama-v2-loader hide">
            <div class="tama-v2-loader-tower">
                <span></span><span></span><span></span>
            </div>
        </div>
        <div id="routeErrorMessage" class="tama-v2-route-error hide">
            {{ trans('topup_v2.service_not_avail') }}
        </div>
    </div>
</div>
