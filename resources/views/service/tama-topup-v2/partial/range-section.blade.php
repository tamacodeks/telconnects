<div id="rangeSection" class="tama-v2-section hide">
    <div class="tama-v2-range-card">
        <div class="tama-v2-range-header">
            <div id="betweenDenomination" class="tama-v2-range-info"></div>
            <div class="tama-v2-range-badge">
                <span id="rangeMin"></span>
                <span class="tama-v2-range-sep">-</span>
                <span id="rangeMax"></span>
            </div>
        </div>
        <div class="tama-v2-range-input">
            <span id="rangeCurrency" class="tama-v2-range-currency">&#8364;</span>
            <input id="range_amount" type="text" class="form-control" min="1" max="1" step="0.01" inputmode="decimal" autocomplete="off" name="range_amount" placeholder="">
        </div>
        <div id="rangeError" class="tama-v2-error hide"></div>
        <div id="amountReceivedDiv" class="tama-v2-received hide">
            <span id="amountReceived"></span>
            <span class="tama-v2-received-label">{{ trans('topup_v2.will_be_received') }}</span>
            <span id="ifTaxApplicable" class="hide">Inc. <span id="taxName"></span></span>
        </div>
    </div>
</div>
