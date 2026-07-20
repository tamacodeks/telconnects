@php
    $topupEncrypt = function ($value) {
        return \app\Library\SecurityHelper::randomEncDec('ec', (string) $value);
    };
@endphp
@if ($provider === 'ding')
<form id="frmReviewTopup" action="{{ secure_url('tama-topup-v2/ding/confirm') }}" method="POST">
    @csrf
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td>{{ trans('tamatopup.phone_number') }}</td>
            <td>{{ $phone_no or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.country') }}</td>
            <td>{{ $country or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.operator') }}</td>
            <td>{{ $operator or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.amount') }}</td>
            <td>{{ $euro_amount or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.phone_will_receive') }}</td>
            <td>{{ $dest_amount or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.total_amount_to_paid') }}</td>
            <td>{{ $euro_amount or '' }}</td>
        </tr>
        </tbody>
    </table>
    <input type="hidden" name="AccountNumber" value="{{ $topupEncrypt(isset($phone_no) ? $phone_no : '') }}">
    <input type="hidden" name="countryCode" value="{{ $topupEncrypt(isset($countryCode) ? $countryCode : '') }}">
    <input type="hidden" name="SkuCode" value="{{ $topupEncrypt(isset($skuCode) ? $skuCode : '') }}">
    <input type="hidden" name="SendValue" value="{{ $topupEncrypt(isset($SendValue) ? $SendValue : '') }}">
    <input type="hidden" name="SendCurrencyIso" value="{{ $topupEncrypt(isset($SendCurrencyIso) ? $SendCurrencyIso : '') }}">
    <input type="hidden" name="commissionRate" value="{{ $topupEncrypt(isset($commissionRate) ? $commissionRate : '') }}">
    <input type="hidden" name="_hid_country" value="{{ $topupEncrypt(isset($country) ? $country : '') }}">
    <input type="hidden" name="_hid_operator" value="{{ $topupEncrypt(isset($operator) ? $operator : '') }}">
    <input type="hidden" name="_hid_euro_amount_formatted" value="{{ $topupEncrypt(isset($euro_amount) ? $euro_amount : '') }}">
    <input type="hidden" name="_hid_dest_amount_formatted" value="{{ $topupEncrypt(isset($dest_amount) ? $dest_amount : '') }}">
    <input type="hidden" name="_hid_euro_amount" value="{{ $topupEncrypt(isset($_hid_euro_amount) ? $_hid_euro_amount : '') }}">
    <input type="hidden" name="_hid_dest_amount" value="{{ $topupEncrypt(isset($_hid_dest_amount) ? $_hid_dest_amount : '') }}">
    <input type="hidden" name="UatNumber" value="{{ $topupEncrypt(isset($UatNumber) ? $UatNumber : '') }}">
    <input type="hidden" name="SendValueOriginal" value="{{ $topupEncrypt(isset($SendValueOriginal) ? $SendValueOriginal : '') }}">
    <div class="text-center">
        <span class="text-muted">{{ trans('tamatopup.any_local_taxes_text') }}</span>
        <br><br>
        <button type="submit" id="btnSubmit" onclick="this.form.submit();this.disabled=true;" class="btn btn-primary">{{ trans('service.tamatopup_btn_confirm_topup') }}</button>
    </div>
</form>
@elseif ($provider === 'reloadly')
<form id="frmReviewTopup" action="{{ secure_url('tama-topup-v2/reloadly/confirm') }}" method="POST">
    @csrf
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td>{{ trans('tamatopup.phone_number') }}</td>
            <td>{{ $phone_no or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.country') }}</td>
            <td>{{ $country or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.operator') }}</td>
            <td>{{ $operator or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.amount') }}</td>
            <td>{{ $SendValue or '' }}</td>
        </tr>
        @if(!empty($productName))
            <tr>
                <td>Plan</td>
                <td>{{ $productName }}</td>
            </tr>
        @endif
        @if(($structure ?? '') !== 'RANGE' && !empty($sendValueOriginal) && (float) $sendValueOriginal !== (float) ($SendValue ?? 0))
            <tr>
                <td>Base Amount</td>
                <td>EUR {{ $sendValueOriginal }}</td>
            </tr>
        @endif
        <tr>
            <td>{{ trans('tamatopup.phone_will_receive') }}</td>
            <td>{{ $currency ?? '' }} {{ $dest_amount ?? '' }}</td>
        </tr>
        @if(!empty($description))
            <tr>
                <td>Details</td>
                <td>{{ $description }}</td>
            </tr>
        @endif
        <tr>
            <td>{{ trans('tamatopup.total_amount_to_paid') }}</td>
            <td>{{ $SendValue or '' }}</td>
        </tr>
        </tbody>
    </table>
    <input type="hidden" name="description" value="{{ $topupEncrypt(isset($description) ? $description : '') }}">
    <input type="hidden" name="AccountNumber" value="{{ $topupEncrypt(isset($phone_no) ? $phone_no : '') }}">
    <input type="hidden" name="SkuCode" value="{{ $topupEncrypt(isset($skuCode) ? $skuCode : '') }}">
    <input type="hidden" name="SendValue" value="{{ $topupEncrypt(isset($SendValue) ? $SendValue : '') }}">
    <input type="hidden" name="sendValueOriginal" value="{{ $topupEncrypt(isset($sendValueOriginal) ? $sendValueOriginal : '') }}">
    <input type="hidden" name="local_amt" value="{{ $topupEncrypt(isset($dest_amount) ? $dest_amount : '') }}">
    <input type="hidden" name="countryCode" value="{{ $topupEncrypt(isset($countryCode) ? $countryCode : '') }}">
    <input type="hidden" name="operator" value="{{ $topupEncrypt(isset($operator) ? $operator : '') }}">
    <input type="hidden" name="currency" value="{{ $topupEncrypt(isset($currency) ? $currency : '') }}">
    <input type="hidden" name="country" value="{{ $topupEncrypt(isset($country) ? $country : '') }}">
    <input type="hidden" name="ISO" value="{{ $topupEncrypt(isset($ISO) ? $ISO : '') }}">
    <div class="text-center">
        <span class="text-muted">{{ trans('tamatopup.any_local_taxes_text') }}</span>
        <br><br>
        <button type="submit" id="btnSubmit" onclick="this.form.submit();this.disabled=true;" class="btn btn-primary">{{ trans('service.tamatopup_btn_confirm_topup') }}</button>
    </div>
</form>
@elseif ($provider === 'tellus')
<form id="frmReviewTopup" action="{{ secure_url('tama-topup-v2/tellus/confirm') }}" method="POST">
    @csrf
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td>{{ trans('tamatopup.phone_number') }}</td>
            <td>{{ $phone_no or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.country') }}</td>
            <td>{{ $country or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.operator') }}</td>
            <td>{{ $operator or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.amount') }}</td>
            <td>{{ $SendValue or '' }}</td>
        </tr>
        @if(!empty($productName))
            <tr>
                <td>Plan</td>
                <td>{{ $productName }}</td>
            </tr>
        @endif
        @if(($structure ?? '') !== 'RANGE' && !empty($sendValueOriginal) && (float) $sendValueOriginal !== (float) ($SendValue ?? 0))
            <tr>
                <td>Base Amount</td>
                <td>EUR {{ $sendValueOriginal }}</td>
            </tr>
        @endif
        <tr>
            <td>{{ trans('tamatopup.phone_will_receive') }}</td>
            <td>{{ $currency ?? '' }} {{ $dest_amount ?? '' }}</td>
        </tr>
        @if(!empty($description))
            <tr>
                <td>Details</td>
                <td>{{ $description }}</td>
            </tr>
        @endif
        <tr>
            <td>{{ trans('tamatopup.total_amount_to_paid') }}</td>
            <td>{{ $SendValue or '' }}</td>
        </tr>
        </tbody>
    </table>
    <input type="hidden" name="description" value="{{ $topupEncrypt(isset($description) ? $description : '') }}">
    <input type="hidden" name="AccountNumber" value="{{ $topupEncrypt(isset($phone_no) ? $phone_no : '') }}">
    <input type="hidden" name="SkuCode" value="{{ $topupEncrypt(isset($skuCode) ? $skuCode : '') }}">
    <input type="hidden" name="SendValue" value="{{ $topupEncrypt(isset($SendValue) ? $SendValue : '') }}">
    <input type="hidden" name="sendValueOriginal" value="{{ $topupEncrypt(isset($sendValueOriginal) ? $sendValueOriginal : '') }}">
    <input type="hidden" name="local_amt" value="{{ $topupEncrypt(isset($dest_amount) ? $dest_amount : '') }}">
    <input type="hidden" name="countryCode" value="{{ $topupEncrypt(isset($countryCode) ? $countryCode : '') }}">
    <input type="hidden" name="operator" value="{{ $topupEncrypt(isset($operator) ? $operator : '') }}">
    <input type="hidden" name="currency" value="{{ $topupEncrypt(isset($currency) ? $currency : '') }}">
    <input type="hidden" name="country" value="{{ $topupEncrypt(isset($country) ? $country : '') }}">
    <input type="hidden" name="minSendValue" value="{{ $topupEncrypt(isset($minSendValue) ? $minSendValue : '') }}">
    <input type="hidden" name="maxSendValue" value="{{ $topupEncrypt(isset($maxSendValue) ? $maxSendValue : '') }}">
    <input type="hidden" name="priceValue" value="{{ $topupEncrypt(isset($priceValue) ? $priceValue : '') }}">
    <input type="hidden" name="priceValueMax" value="{{ $topupEncrypt(isset($priceValueMax) ? $priceValueMax : '') }}">
    <input type="hidden" name="localAmount" value="{{ $topupEncrypt(isset($localAmount) ? $localAmount : '') }}">
    <input type="hidden" name="localAmountMin" value="{{ $topupEncrypt(isset($localAmountMin) ? $localAmountMin : '') }}">
    <input type="hidden" name="localAmountMax" value="{{ $topupEncrypt(isset($localAmountMax) ? $localAmountMax : '') }}">
    <input type="hidden" name="product" value="{{ $topupEncrypt(isset($product) ? $product : '') }}">
    <input type="hidden" name="productId" value="{{ $topupEncrypt(isset($productId) ? $productId : '') }}">
    <input type="hidden" name="productName" value="{{ $topupEncrypt(isset($productName) ? $productName : '') }}">
    <input type="hidden" name="type" value="{{ $topupEncrypt(isset($type) ? $type : '') }}">
    <input type="hidden" name="structure" value="{{ $topupEncrypt(isset($structure) ? $structure : '') }}">
    <input type="hidden" name="infoMode" value="{{ $topupEncrypt(isset($infoMode) ? $infoMode : '') }}">
    <div class="text-center">
        <span class="text-muted">{{ trans('tamatopup.any_local_taxes_text') }}</span>
        <br><br>
        <button type="submit" id="btnSubmit" onclick="this.form.submit();this.disabled=true;" class="btn btn-primary">{{ trans('service.tamatopup_btn_confirm_topup') }}</button>
    </div>
</form>
@else
<form id="frmReviewTopup" action="{{ secure_url('tama-topup-v2/transfer/confirm') }}" method="POST">
    @csrf
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td>{{ trans('tamatopup.phone_number') }}</td>
            <td>{{ $phone_no or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.country') }}</td>
            <td>{{ $country or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.operator') }}</td>
            <td>{{ $operator_name or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.amount') }}</td>
            <td>{{ $name or '' }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.total_amount_to_paid') }}</td>
            <td>{{ $currency or '' }} {{ $SendValue or '' }}</td>
        </tr>
        </tbody>
    </table>
    <input type="hidden" name="mobile_number" value="{{ $topupEncrypt(isset($phone_no) ? $phone_no : '') }}">
    <input type="hidden" name="SkuCode" value="{{ $topupEncrypt(isset($skuCode) ? $skuCode : '') }}">
    <input type="hidden" name="SendValue" value="{{ $topupEncrypt(isset($SendValue) ? $SendValue : '') }}">
    <input type="hidden" name="sendValueOriginal" value="{{ $topupEncrypt(isset($sendValueOriginal) ? $sendValueOriginal : '') }}">
    <input type="hidden" name="local_amount" value="{{ $topupEncrypt(isset($dest_amount) ? $dest_amount : '') }}">
    <input type="hidden" name="country_code" value="{{ $topupEncrypt(isset($countryCode) ? $countryCode : '') }}">
    <input type="hidden" name="name" value="{{ $topupEncrypt(isset($name) ? $name : '') }}">
    <input type="hidden" name="country" value="{{ $topupEncrypt(isset($country) ? $country : '') }}">
    <input type="hidden" name="operator_id" value="{{ $topupEncrypt(isset($operator_id) ? $operator_id : '') }}">
    <input type="hidden" name="operator_name" value="{{ $topupEncrypt(isset($operator_name) ? $operator_name : '') }}">
    <input type="hidden" name="currency" value="{{ $topupEncrypt(isset($currency) ? $currency : '') }}">
    <input type="hidden" name="ISO" value="{{ $topupEncrypt(isset($ISO) ? $ISO : '') }}">
    <input type="hidden" name="sender_name" value="{{ $topupEncrypt(isset($sender_name) ? $sender_name : '') }}">
    <input type="hidden" name="sender_parent_name" value="{{ $topupEncrypt(isset($sender_parent_name) ? $sender_parent_name : '') }}">
    <div class="text-center">
        <span class="text-muted">{{ trans('tamatopup.any_local_taxes_text') }}</span>
        <br><br>
        <button type="submit" id="btnSubmit" onclick="this.form.submit();this.disabled=true;" class="btn btn-primary">{{ trans('service.tamatopup_btn_confirm_topup') }}</button>
    </div>
</form>
@endif
<script>
    $(document).ready(function () {
        $("#btnSubmit").click(function () {
            $("#frmReviewTopup").LoadingOverlay('show');
            $("#frmReviewTopup").LoadingOverlay('text', 'Please wait and do not refresh the page');
        });
    });
</script>
