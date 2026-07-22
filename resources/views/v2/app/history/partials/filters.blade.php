@php
    $isPinHistoryPage = ($historyType ?? '') === 'pin-history';
    $isPaymentsPage = ($historyType ?? '') === 'payments';
    $isFailedTransactionsPage = ($historyType ?? '') === 'failed-transactions';
    $isTicketsPage = ($historyType ?? '') === 'tickets';
    $hideServiceFilter = $isPaymentsPage || $isFailedTransactionsPage || $isTicketsPage;
    $filterInputName = $isPinHistoryPage ? 'telecom_provider_id' : ($isPaymentsPage ? 'retailer_id' : 'service_id');
    $filterOptions = $isPaymentsPage ? ($retailers ?? collect()) : ($services ?? collect());
    $selectedServices = array_values(array_filter(array_map(function ($value) use ($isPinHistoryPage, $isPaymentsPage) {
        $value = (string) $value;

        if ($isPinHistoryPage || $isPaymentsPage) {
            return $value;
        }

        if ($value === '111') {
            return 'operator:blabla';
        }

        if ($value === '112') {
            return 'operator:flixbus';
        }

        if (strpos($value, 'service:') === 0 || strpos($value, 'operator:') === 0) {
            return $value;
        }

        return $value !== '' ? 'service:' . $value : '';
    }, (array) request()->input($filterInputName, []))));
    $isOrdersPage = ($historyType ?? '') === 'orders';
    $showQuickRanges = $showQuickRanges ?? false;
    $activeRange = $activeRange ?? '';
    $displayHistoryDate = function ($value) {
        $value = trim((string) $value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return date('d/m/Y', strtotime($value));
        }

        return $value;
    };
    $operatorFilters = [
        'operator:flixbus' => $historyText['filters']['flix_bus'],
        'operator:blabla' => $historyText['filters']['bla_bus'],
    ];
@endphp

<form method="GET" id="v2HistoryFilterForm" class="v2-history-toolbar" role="form">
    @unless($hideServiceFilter)
        <label class="v2-history-field v2-history-field-select" for="v2HistoryService">
            <span class="v2-history-field-heading">
                <span>
                    {{ $isPinHistoryPage ? ($historyText['filters']['card'] ?? 'Card') : $historyText['filters']['service'] }}
                    <span class="v2-history-select-count" id="v2HistoryServiceCount"></span>
                </span>
            </span>
            <select name="{{ $filterInputName }}[]"
                    id="v2HistoryService"
                    class="select2 v2-history-service-select"
                    data-width="100%"
                    data-container="body"
                    data-placeholder="{{ $historyText['filters']['all_services'] }}"
                    title="{{ $historyText['filters']['all_services'] }}"
                    multiple>
                @foreach($filterOptions as $service)
                    @php
                        $serviceId = (string) $service->id;
                        $serviceValue = $isPinHistoryPage ? $serviceId : 'service:' . $serviceId;
                        $serviceName = (string) $service->name;
                        $canShowService = true;

                        if ($isOrdersPage) {
                            $canShowService = $canShowService && \app\Library\AppHelper::user_access($service->id, auth()->user()->id);
                        }

                        if (!$isPinHistoryPage && in_array($serviceName, ['Topup', 'Flix Bus'], true)) {
                            $canShowService = false;
                        }

                        $serviceLabel = $serviceName === 'Tama Topup' ? 'TopUp' : $serviceName;
                        if ($isPinHistoryPage && isset($service->face_value)) {
                            $serviceLabel .= ' ' . \app\Library\AppHelper::formatAmount('EUR', $service->face_value);
                        }
                    @endphp
                    @if($canShowService)
                        <option value="{{ $serviceValue }}" {{ in_array($serviceValue, $selectedServices, true) ? 'selected' : '' }}>
                            {{ $serviceLabel }}
                        </option>
                    @endif
                @endforeach
                @if(!$isPinHistoryPage)
                    @foreach($operatorFilters as $operatorValue => $operatorLabel)
                        <option value="{{ $operatorValue }}" {{ in_array($operatorValue, $selectedServices, true) ? 'selected' : '' }}>
                            {{ $operatorLabel }}
                        </option>
                    @endforeach
                @endif
            </select>
            @if(($historyType ?? '') !== 'transactions')
                <span class="v2-history-select-actions">
                    <button type="button" data-v2-history-select="all">
                        {{ $historyText['filters']['select_all'] }}
                    </button>
                    <button type="button" data-v2-history-select="clear">
                        {{ $historyText['filters']['deselect_all'] }}
                    </button>
                </span>
            @endif
        </label>
    @endunless

    @if($isTicketsPage)
        <label class="v2-history-field" for="v2HistoryStatus">
            <span>{{ $historyText['filters']['status'] ?? 'Status' }}</span>
            <select name="type" id="v2HistoryStatus" class="v2-history-service-select">
                <option value="">{{ $historyText['filters']['all_statuses'] ?? 'All statuses' }}</option>
                <option value="open" {{ request()->input('type') === 'open' ? 'selected' : '' }}>{{ $historyText['filters']['open'] ?? 'Open' }}</option>
                <option value="closed" {{ request()->input('type') === 'closed' ? 'selected' : '' }}>{{ $historyText['filters']['closed'] ?? 'Closed' }}</option>
            </select>
        </label>
    @endif

    <label class="v2-history-field v2-history-field-date" for="v2HistoryFromDate">
        <span>{{ $historyText['filters']['from'] }}</span>
        <input type="hidden"
               name="from_date"
               id="v2HistoryFromDateIso"
               value="{{ $fromDate ?? '' }}">
        <i class="fa fa-calendar-alt v2-history-date-icon" aria-hidden="true"></i>
        <input type="text"
               class="form-control date v2-history-date"
               id="v2HistoryFromDate"
               value="{{ $displayHistoryDate($fromDate ?? '') }}"
               inputmode="numeric"
               placeholder="DD/MM/YYYY"
               data-iso-target="v2HistoryFromDateIso"
               autocomplete="off">
    </label>

    <label class="v2-history-field v2-history-field-date" for="v2HistoryToDate">
        <span>{{ $historyText['filters']['to'] }}</span>
        <input type="hidden"
               name="to_date"
               id="v2HistoryToDateIso"
               value="{{ $toDate ?? '' }}">
        <i class="fa fa-calendar-alt v2-history-date-icon" aria-hidden="true"></i>
        <input type="text"
               class="form-control date v2-history-date"
               id="v2HistoryToDate"
               value="{{ $displayHistoryDate($toDate ?? '') }}"
               inputmode="numeric"
               placeholder="DD/MM/YYYY"
               data-iso-target="v2HistoryToDateIso"
               autocomplete="off">
    </label>

    <label class="v2-history-field v2-history-field-search" for="v2HistorySearch">
        <span>{{ $historyText['filters']['search'] }}</span>
        <i class="fa fa-search" aria-hidden="true"></i>
        <input id="v2HistorySearch"
               type="search"
               name="query"
               value="{{ $searchValue ?? '' }}"
               placeholder="{{ $historyText['filters']['search_placeholder'] }}"
               autocomplete="off">
    </label>

    <button type="submit" class="v2-history-btn v2-history-btn-primary">
        <i class="fa fa-filter" aria-hidden="true"></i>
        <span>{{ $historyText['filters']['apply'] }}</span>
    </button>

    @if(in_array(($historyType ?? ''), ['orders', 'transactions', 'pin-history', 'payments', 'failed-transactions'], true))
        <button type="button" class="v2-history-btn v2-history-btn-outline v2-history-reset-btn" data-v2-history-reset>
            <i class="fa fa-undo" aria-hidden="true"></i>
            <span>{{ $historyText['filters']['reset'] ?? 'Reset filters' }}</span>
        </button>
    @endif

    <p class="v2-history-date-error" id="v2HistoryDateError" hidden>
        {{ $historyText['filters']['date_error'] ?? 'Select a valid date range.' }}
    </p>

    @if($showQuickRanges)
        <div class="v2-history-field v2-history-range-field">
            <span>{{ $historyText['filters']['quick_range'] }}</span>
            <div class="v2-history-range-buttons" data-active-range="{{ $activeRange }}">
                <button type="button"
                        class="v2-history-range-btn {{ $activeRange === 'today' ? 'is-active' : '' }}"
                        data-v2-history-range="today">
                    {{ $historyText['filters']['today'] }}
                </button>
                <button type="button"
                        class="v2-history-range-btn {{ $activeRange === '7d' ? 'is-active' : '' }}"
                        data-v2-history-range="7d">
                    {{ $historyText['filters']['last_7_days'] }}
                </button>
                <button type="button"
                        class="v2-history-range-btn {{ $activeRange === 'month' ? 'is-active' : '' }}"
                        data-v2-history-range="month">
                    {{ $historyText['filters']['this_month'] }}
                </button>
                <button type="button"
                        class="v2-history-range-btn {{ $activeRange === '30d' ? 'is-active' : '' }}"
                        data-v2-history-range="30d">
                    {{ $historyText['filters']['last_30_days'] }}
                </button>
                <button type="button"
                        class="v2-history-range-btn {{ $activeRange === 'all' ? 'is-active' : '' }}"
                        data-v2-history-range="all">
                    {{ $historyText['filters']['all_dates'] }}
                </button>
            </div>
        </div>
    @endif
</form>
