<form method="{{ $isRetailerPriceList ? 'GET' : 'POST' }}" id="ccplV2FilterForm" class="ccpl-v2-toolbar" role="form">
    @if(!$isRetailerPriceList)
        <label class="ccpl-v2-field ccpl-v2-field-select" for="ccplV2RateGroup">
            <span>{{ $priceListText['choose_group'] }}</span>
            <select name="rate_table_group_id"
                    id="ccplV2RateGroup"
                    class="select-picker"
                    data-live-search="true">
                @forelse($rateGroups as $rateGroup)
                    <option value="{{ $rateGroup->id }}"
                            {{ (string) $defaultRateGroupId === (string) $rateGroup->id ? 'selected' : '' }}>
                        {{ $rateGroup->name }}
                    </option>
                @empty
                    <option value="">{{ $priceListText['no_group'] }}</option>
                @endforelse
            </select>
        </label>
        <button type="submit" class="ccpl-v2-btn ccpl-v2-btn-primary">
            <i class="fa fa-filter" aria-hidden="true"></i>
            <span>{{ $priceListText['filter'] }}</span>
        </button>
    @endif
    <label class="ccpl-v2-field ccpl-v2-field-search" for="ccplV2Search">
        <span>{{ $priceListText['search'] }}</span>
        <i class="fa fa-search" aria-hidden="true"></i>
        <input id="ccplV2Search"
               type="search"
               placeholder="{{ $priceListText['search'] }}"
               autocomplete="off">
    </label>
</form>
