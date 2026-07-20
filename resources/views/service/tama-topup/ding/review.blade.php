<form id="frmReviewTopup" action="{{ secure_url('tama-topup/ding/confirm/topup') }}" method="POST">
    @csrf
    <table class="table table-bordered">
        <tbody>
            <tr>
                <td>{{ trans('tamatopup.phone_number') }}</td>
                <td>{{ $phone_no or "" }}</td>
            </tr>
            <tr>
                <td>{{ trans('tamatopup.country') }}</td>
                <td>{{ $country or "" }}</td>
            </tr>
            <tr>
                <td>{{ trans('tamatopup.operator') }}</td>
                <td>{{ $operator or "" }}</td>
            </tr>
            <tr>
                <td>{{ trans('tamatopup.amount') }}</td>
                <td>{{ $euro_amount or "" }}</td>
            </tr>
            <tr>
                <td>{{ trans('tamatopup.phone_will_receive') }}</td>
                <td>{{ $dest_amount or "" }}</td>
            </tr>
            <tr>
                <td>{{ trans('tamatopup.total_amount_to_paid') }}</td>
                <td>{{ $euro_amount or "" }}</td>
            </tr>
        </tbody>
    </table>
    <input type="hidden" name="AccountNumber" value="{{ $phone_no or "" }}">
    <input type="hidden" name="countryCode" value="{{ $countryCode or "" }}">
    <input type="hidden" name="SkuCode" value="{{ $skuCode or "" }}">
    <input type="hidden" name="SendValue" value="{{ $SendValue or "" }}">
    <input type="hidden" name="SendCurrencyIso" value="{{ $SendCurrencyIso or "" }}">
    <input type="hidden" name="commissionRate" value="{{ $commissionRate or "" }}">
    <input type="hidden" name="_hid_country" value="{{ $country or "" }}">
    <input type="hidden" name="_hid_operator" value="{{ $operator or "" }}">
    <input type="hidden" name="_hid_euro_amount_formatted" value="{{ $euro_amount or "" }}">
    <input type="hidden" name="_hid_dest_amount_formatted" value="{{ $dest_amount or "" }}">
    <input type="hidden" name="_hid_euro_amount" value="{{ $_hid_euro_amount or "" }}">
    <input type="hidden" name="_hid_dest_amount" value="{{ $_hid_dest_amount or "" }}">
    <input type="hidden" name="UatNumber" value="{{ $UatNumber or "" }}">
    <input type="hidden" name="SendValueOriginal" value="{{ $SendValueOriginal or "" }}">
    <div class="text-center">
        <span class="text-muted">{{ trans('tamatopup.any_local_taxes_text') }}</span>
        <br>
        <br>
        <button type="submit" id="btnSubmit" onclick="this.form.submit();this.disabled=true;"  class="btn btn-primary">{{ trans('service.tamatopup_btn_confirm_topup') }}</button>
    </div>
</form>
<script>
    $(document).ready(function () {
        $("#btnSubmit").click(function () {
            $("#frmReviewTopup").LoadingOverlay('show');
            $("#frmReviewTopup").LoadingOverlay("text", "veuillez patienter et ne pas fermer le navigateur ni actualiser la page");
        });
    });
</script>