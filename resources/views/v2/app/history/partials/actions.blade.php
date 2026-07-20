<label class="v2-history-page-size" for="v2HistoryPageSize">
    <span>{{ $historyText['actions']['page_size'] ?? 'Rows' }}</span>
    <select id="v2HistoryPageSize" aria-label="{{ $historyText['actions']['page_size'] ?? 'Rows' }}">
        <option value="10">10 {{ $historyText['actions']['records'] ?? 'records' }}</option>
        <option value="25">25 {{ $historyText['actions']['records'] ?? 'records' }}</option>
        <option value="50">50 {{ $historyText['actions']['records'] ?? 'records' }}</option>
        <option value="-1">{{ $historyText['actions']['show_all'] ?? 'Show all' }}</option>
    </select>
</label>
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
