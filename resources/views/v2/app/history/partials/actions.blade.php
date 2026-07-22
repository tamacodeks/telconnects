<label class="v2-history-page-size" for="v2HistoryPageSize">
    <span>{{ $historyText['actions']['page_size'] ?? 'Rows' }}</span>
    <select id="v2HistoryPageSize" aria-label="{{ $historyText['actions']['page_size'] ?? 'Rows' }}">
        <option value="10">10 {{ $historyText['actions']['records'] ?? 'records' }}</option>
        <option value="25">25 {{ $historyText['actions']['records'] ?? 'records' }}</option>
        <option value="50">50 {{ $historyText['actions']['records'] ?? 'records' }}</option>
        <option value="-1">{{ $historyText['actions']['show_all'] ?? 'Show all' }}</option>
    </select>
</label>
@if(($historyType ?? '') === 'pin-history')
    <a href="{{ url('tickets-v2') }}"
       class="v2-history-btn v2-history-btn-outline">
        <i class="fa fa-ticket-alt" aria-hidden="true"></i>
        <span>{{ trans('myservice.my_tickets') === 'myservice.my_tickets' ? 'Tickets' : trans('myservice.my_tickets') }}</span>
    </a>
@endif
<button type="button"
        class="v2-history-btn v2-history-btn-soft"
        id="v2HistoryRefresh"
        title="{{ $historyText['actions']['refresh'] }}">
    <i class="fa fa-sync-alt" aria-hidden="true"></i>
    <span>{{ $historyText['actions']['refresh'] }}</span>
</button>
<button type="button"
        class="v2-history-btn v2-history-btn-outline"
        id="v2HistoryExport">
    <i class="fa fa-file-excel" aria-hidden="true"></i>
    <span>{{ $historyText['actions']['export'] }}</span>
</button>
