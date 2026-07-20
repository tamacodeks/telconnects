@php
    $isTransactionsPage = ($historyType ?? '') === 'transactions';
    $isFailedTransactionsPage = ($historyType ?? '') === 'failed-transactions';
    $isFinancialTransactionPage = $isTransactionsPage || $isFailedTransactionsPage;
@endphp

<script id="v2HistoryDetailsTemplate" type="text/x-handlebars-template">
    <div class="v2-history-detail-card">
        <dl>
            @if($isFinancialTransactionPage)
                <div>
                    <dt>{{ $historyText['columns']['customer_id'] }}</dt>
                    <dd>@{{ cust_id }}</dd>
                </div>
            @endif
            <div>
                <dt>{{ $historyText['columns']['sender_name'] }}</dt>
                <dd>@{{ sender_first_name }}</dd>
            </div>
            <div>
                <dt>{{ $historyText['columns']['sender_number'] }}</dt>
                <dd>@{{ sender_mobile }}</dd>
            </div>
            <div>
                <dt>{{ $historyText['columns']['receiver_name'] }}</dt>
                <dd>@{{ receiver_first_name }}</dd>
            </div>
            <div>
                <dt>{{ $historyText['columns']['receiver_number'] }}</dt>
                <dd>@{{ mobile }}</dd>
            </div>
            <div>
                <dt>{{ $historyText['columns']['pin'] }}</dt>
                <dd>@{{ pin }}</dd>
            </div>
            <div>
                <dt>{{ $historyText['columns']['serial'] }}</dt>
                <dd>@{{ serial }}</dd>
            </div>
        </dl>
        @if(!$isFinancialTransactionPage)
            @{{#if print_receipt}}
                <div class="v2-history-detail-actions">
                    @{{{ print_receipt }}}
                </div>
            @{{/if}}
        @endif
    </div>
</script>
