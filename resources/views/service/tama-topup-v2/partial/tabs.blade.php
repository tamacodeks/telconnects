@php
    $steps = [
        ['key' => 'search', 'label' => trans('topup_v2.filter_lbl_search'), 'icon' => 'fa-search'],
        ['key' => 'choose', 'label' => trans('topup_v2.choose'), 'icon' => 'fa-th-large'],
        ['key' => 'amount', 'label' => trans('topup_v2.select_amount'), 'icon' => 'fa-solid fa-euro-sign'],
        ['key' => 'review', 'label' => trans('topup_v2.review'), 'icon' => 'fa-check-circle'],
        ['key' => 'print', 'label' => trans('topup_v2.btn_print'), 'icon' => 'fa-print'],
    ];
    $currentStep = isset($current) ? $current : 'search';
@endphp
<div class="row">
    <div class="col-md-12">
        <div class="tama-v2-tabs" role="tablist" aria-label="Topup steps">
            @foreach ($steps as $index => $step)
                @php
                    $isActive = $currentStep === $step['key'];
                    $isDone = array_search($currentStep, array_column($steps, 'key')) > $index;
                @endphp
                <div class="tama-v2-tab {{ $isActive ? 'is-active' : '' }} {{ $isDone ? 'is-done' : '' }}" data-step="{{ $step['key'] }}" aria-current="{{ $isActive ? 'step' : 'false' }}">
                    <div class="tama-v2-tab-badge">
                        <i class="fa {{ $step['icon'] }}"></i>
                    </div>
                    <div class="tama-v2-tab-label">{{ $step['label'] }}</div>
                    @if ($index < count($steps) - 1)
                        <div class="tama-v2-tab-connector" aria-hidden="true"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
