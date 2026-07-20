@if(!$isRetailerPriceList)
    <a href="{{ url('cc-price-list/groups') }}" class="ccpl-v2-btn ccpl-v2-btn-ghost">
        <i class="fa fa-layer-group" aria-hidden="true"></i>
        <span>{{ $priceListText['view_groups'] }}</span>
    </a>
    @if((int) auth()->user()->group_id !== 5)
        <a onclick="AppModal(this.href,'{{ trans('common.add_new') }}');return false;"
           href="{{ url('cc-price-list/groups/edit') }}"
           class="ccpl-v2-btn ccpl-v2-btn-primary">
            <i class="fa fa-plus" aria-hidden="true"></i>
            <span>{{ $priceListText['add_group'] }}</span>
        </a>
    @endif
@endif
