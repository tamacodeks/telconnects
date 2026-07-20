<form id="frmReviewTopup" action="{{ secure_url('tama-topup/transfer/confirm/topup') }}" method="POST">
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
            <td>{{ $operator_name or "" }}</td>
        </tr>
        <tr>
            <td>{{ trans('tamatopup.amount') }}</td>
            <td>{{ $name or "" }}</td>
        </tr>
        {{--<tr>--}}
        {{--<td>{{ trans('tamatopup.phone_will_receive') }}</td>--}}
        {{--<td> {{ $dest_amount or "" }}</td>a--}}
        {{--</tr>--}}
        <tr>
            <td>{{ trans('tamatopup.total_amount_to_paid') }}</td>
            <td>{{ $currency or "" }} {{ $SendValue or "" }}</td>
        </tr>
        </tbody>
    </table>
    <input type="hidden" name="mobile_number" value="{{ $phone_no or "" }}">
    <input type="hidden" name="SkuCode" value="{{ $skuCode or "" }}">
    <input type="hidden" name="SendValue" value="{{ $SendValue or "" }}">
    <input type="hidden" name="sendValueOriginal" value="{{ $sendValueOriginal or "" }}">
    <input type="hidden" name="local_amount" value="{{ $dest_amount or "" }}">
    <input type="hidden" name="country_code" value="{{ $countryCode or "" }}">
    <input type="hidden" name="name" value="{{ $name or "" }}">
    <input type="hidden" name="country" value="{{ $country or "" }}">
    <input type="hidden" name="operator_id" value="{{ $operator_id or "" }}">
    <input type="hidden" name="operator_name" value="{{ $operator_name or "" }}">
    <input type="hidden" name="currency" value="{{ $currency or "" }}">
    <input type="hidden" name="country" value="{{ $country or "" }}">
    <input type="hidden" name="ISO" value="{{ $ISO or "" }}">
    <input type="hidden" name="sender_name" value="{{ $sender_name or "" }}">
    <input type="hidden" name="sender_parent_name" value="{{ $sender_parent_name or "" }}">
    <div class="text-center">
        <span class="text-muted">{{ trans('tamatopup.any_local_taxes_text') }}</span>
        <br>
        <br>
        <button type="submit" id="btnSubmit" onclick="this.form.submit();this.disabled=true;" class="btn btn-primary">{{ trans('service.tamatopup_btn_confirm_topup') }}</button>
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