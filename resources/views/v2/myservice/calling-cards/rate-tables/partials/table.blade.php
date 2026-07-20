@if($isRetailerPriceList)
    <div class="ccpl-v2-retailer-list-shell">
        <div id="ccplV2RetailerList" class="ccpl-v2-retailer-grid" aria-live="polite"></div>
        <div class="ccpl-v2-retailer-footer">
            <span id="ccplV2RetailerStatus" class="ccpl-v2-retailer-status"></span>
            <div id="ccplV2RetailerPager" class="ccpl-v2-retailer-pager"></div>
        </div>
    </div>
@else
    <div class="ccpl-v2-table-wrap">
        <table id="ccplV2Table" class="table ccpl-v2-table">
            <thead>
            <tr>
                <th>{{ $priceListText['columns']['number'] }}</th>
                <th>{{ $priceListText['columns']['card'] }}</th>
                <th>{{ $priceListText['columns']['description'] }}</th>
                <th>{{ $priceListText['columns']['buying_price'] }}</th>
                <th>{{ $priceListText['columns']['sale_price'] }}</th>
                <th>{{ $priceListText['columns']['sale_margin'] }}</th>
                <th>{{ $priceListText['columns']['action'] }}</th>
            </tr>
            </thead>
        </table>
    </div>
@endif
