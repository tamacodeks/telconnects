@php
    $isTransactionsPage = ($historyType ?? '') === 'transactions';
    $isFailedTransactionsPage = ($historyType ?? '') === 'failed-transactions';
    $isFinancialTransactionPage = $isTransactionsPage || $isFailedTransactionsPage;
    $isPinHistoryPage = ($historyType ?? '') === 'pin-history';
    $isPaymentsPage = ($historyType ?? '') === 'payments';
    $canSeeCost = $canSeeCost ?? false;
@endphp

<div class="v2-history-table-wrap">
    <div id="v2HistoryMobileList" class="v2-history-mobile-list" aria-live="polite"></div>
    <div class="v2-history-loading-skeleton" aria-hidden="true">
        @for($i = 0; $i < 7; $i++)
            <span></span>
        @endfor
    </div>

    <table id="v2HistoryTable" class="table v2-history-table">
        <thead>
        <tr>
            @if($isPinHistoryPage)
                <th>{{ $historyText['columns']['number'] }}</th>
                <th>{{ $historyText['columns']['date'] }}</th>
                <th>{{ $historyText['columns']['card_name'] ?? 'Card name' }}</th>
                <th>{{ $historyText['columns']['card_description'] ?? 'Description' }}</th>
                <th>{{ $historyText['columns']['serial'] }}</th>
                <th>{{ $historyText['columns']['pin'] }}</th>
                <th>{{ $historyText['columns']['status'] }}</th>
                <th>{{ $historyText['columns']['action'] ?? 'Action' }}</th>
            @elseif($isPaymentsPage)
                <th>{{ $historyText['columns']['number'] }}</th>
                <th>{{ $historyText['columns']['date'] }}</th>
                <th>{{ $historyText['columns']['payment_date'] ?? 'Updated date' }}</th>
                <th>{{ $historyText['columns']['customer_id'] }}</th>
                <th>{{ $historyText['columns']['retailer'] }}</th>
                <th class="v2-history-number-head">{{ $historyText['columns']['amount'] ?? 'Amount' }}</th>
                <th class="v2-history-number-head">{{ $historyText['columns']['previous_balance'] ?? 'Previous balance' }}</th>
                <th class="v2-history-number-head">{{ $historyText['columns']['current_balance'] ?? 'Current balance' }}</th>
                <th>{{ $historyText['columns']['comment'] ?? 'Comment' }}</th>
                <th>{{ $historyText['columns']['received_by'] ?? 'Received by' }}</th>
            @else
                <th class="v2-history-details-head"></th>
                <th>{{ $historyText['columns']['number'] }}</th>
                <th>{{ $historyText['columns']['date'] }}</th>
                <th>{{ $historyText['columns']['retailer'] }}</th>
                <th>{{ $historyText['columns']['service'] }}</th>
                @if($isFinancialTransactionPage)
                    <th>{{ $historyText['columns']['transaction_id'] }}</th>
                @endif
                <th>{{ $historyText['columns']['product'] }}</th>
                @if($isFinancialTransactionPage)
                    <th class="v2-history-number-head">{{ $historyText['columns']['public_price'] }}</th>
                    @if($canSeeCost)
                        <th class="v2-history-number-head">{{ $historyText['columns']['buying_price'] }}</th>
                        <th class="v2-history-number-head">{{ $historyText['columns']['reseller_price'] }}</th>
                    @else
                        <th class="v2-history-number-head">{{ $historyText['columns']['price'] }}</th>
                    @endif
                    <th class="v2-history-number-head">{{ $historyText['columns']['sale_margin'] }}</th>
                @else
                    <th class="v2-history-number-head">{{ $historyText['columns']['price'] }}</th>
                @endif
                <th>{{ $historyText['columns']['status'] }}</th>
            @endif
        </tr>
        </thead>
        @if($isFinancialTransactionPage)
            <tfoot>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="v2-history-total-label">{{ $historyText['columns']['total'] ?? 'Total' }}</th>
                @if($canSeeCost)
                    <th></th>
                    <th></th>
                    <th></th>
                @else
                    <th></th>
                    <th></th>
                @endif
                <th></th>
                <th></th>
            </tr>
            </tfoot>
        @elseif($isPaymentsPage)
            <tfoot>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="v2-history-total-label">{{ $historyText['columns']['total'] ?? 'Total' }}</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            </tfoot>
        @endif
    </table>
</div>

@if(!$isPinHistoryPage && !$isPaymentsPage)
    @include('v2.app.history.partials.details-template', [
        'historyText' => $historyText,
        'historyType' => $historyType,
    ])
@endif
