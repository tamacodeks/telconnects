<button type="button"
        class="ccpl-v2-icon-btn"
        id="ccplV2Refresh"
        title="{{ $priceListText['refresh'] }}">
    <i class="fa fa-sync-alt" aria-hidden="true"></i>
</button>
@if(!$isRetailerPriceList)
    <button type="button" class="ccpl-v2-btn ccpl-v2-btn-ghost" id="ccplV2Export">
        <i class="fa fa-file-excel" aria-hidden="true"></i>
        <span>{{ $priceListText['export'] }}</span>
    </button>
@endif
