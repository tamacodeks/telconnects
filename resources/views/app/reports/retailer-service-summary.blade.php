@extends('layout.app')
@section('content')
    <link href="{{ secure_asset('vendor/date-picker/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('vendor/datatables/datatables.css') }}" rel="stylesheet">

    <style>
        .service-report-page {
            background: #f5f7fa;
            color: #263238;
            font-family: "Inter", "Roboto", "Source Sans Pro", Arial, sans-serif;
            margin: -15px;
            min-height: calc(100vh - 70px);
            padding: 22px;
        }
        .service-report-ajax-content {
            position: relative;
        }
        .service-report-ajax-content.is-loading {
            opacity: .62;
            pointer-events: none;
        }
        .service-report-ajax-content.is-loading:after {
            background: rgba(245, 247, 250, .72);
            bottom: 0;
            content: '';
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            z-index: 30;
        }
        .report-shell {
            margin: 0 auto;
            max-width: 1480px;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 18px;
        }
        .report-breadcrumb {
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .report-title {
            font-size: 27px;
            font-weight: 700;
            line-height: 1.2;
            margin: 0;
        }
        .report-subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-top: 6px;
        }
        .report-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .045);
            margin-bottom: 18px;
        }
        .report-card-body {
            padding: 20px;
        }
        .filter-card {
            padding: 16px 18px;
        }
        .filter-toolbar {
            align-items: flex-end;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .filter-field {
            min-width: 170px;
        }
        .filter-field label {
            color: #6b7280;
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .date-input-wrap {
            position: relative;
        }
        .date-input-wrap .fa {
            color: #82b368;
            left: 11px;
            position: absolute;
            top: 10px;
        }
        .date-input-wrap .form-control {
            border-color: #dfe5ec;
            border-radius: 8px;
            box-shadow: none;
            height: 38px;
            padding-left: 33px;
        }
        .filter-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .btn-report-primary {
            background: #82b368;
            border-color: #82b368;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            height: 38px;
            padding: 8px 14px;
        }
        .btn-report-primary:hover,
        .btn-report-primary:focus {
            background: #6fa057;
            border-color: #6fa057;
            color: #fff;
        }
        .btn-report-light {
            background: #fff;
            border: 1px solid #dfe5ec;
            border-radius: 8px;
            color: #263238;
            font-weight: 600;
            height: 38px;
            padding: 8px 13px;
        }
        .quick-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 12px;
        }
        .quick-filter {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            color: #53606d;
            font-size: 12px;
            padding: 6px 11px;
        }
        .quick-filter:hover,
        .quick-filter:focus {
            background: #edf7ea;
            border-color: #cfe4c5;
            color: #477538;
        }
        .kpi-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .kpi-card {
            min-height: 128px;
        }
        .kpi-body {
            align-items: center;
            display: flex;
            gap: 15px;
            height: 100%;
            padding: 20px;
        }
        .kpi-icon {
            align-items: center;
            background: #edf7ea;
            border-radius: 10px;
            color: #5c9147;
            display: flex;
            flex: 0 0 48px;
            font-size: 20px;
            height: 48px;
            justify-content: center;
            width: 48px;
        }
        .kpi-label {
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .kpi-value {
            color: #263238;
            font-size: 29px;
            font-weight: 750;
            line-height: 1.1;
        }
        .kpi-note {
            color: #8a94a6;
            font-size: 12px;
            margin-top: 6px;
        }
        .section-heading {
            align-items: center;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }
        .section-title {
            font-size: 17px;
            font-weight: 700;
            margin: 0;
        }
        .section-caption {
            color: #6b7280;
            font-size: 12px;
        }
        .distribution-grid {
            align-items: center;
            display: grid;
            gap: 22px;
            grid-template-columns: minmax(0, 7fr) minmax(280px, 5fr);
        }
        .donut-wrap {
            align-items: center;
            display: flex;
            justify-content: center;
            min-height: 285px;
        }
        #retailerServiceDonutChart {
            height: 270px !important;
            max-width: 100%;
            width: 270px !important;
        }
        .empty-state {
            background: #f8fafc;
            border: 1px dashed #d8e0ea;
            border-radius: 10px;
            color: #6b7280;
            display: none;
            font-size: 14px;
            padding: 28px;
            text-align: center;
        }
        .service-breakdown {
            display: grid;
            gap: 10px;
        }
        .breakdown-item {
            border: 1px solid #eef1f5;
            border-radius: 9px;
            padding: 12px;
        }
        .breakdown-top {
            align-items: center;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
        }
        .service-name {
            align-items: center;
            display: inline-flex;
            font-weight: 700;
            gap: 8px;
        }
        .service-dot {
            border-radius: 999px;
            display: inline-block;
            height: 10px;
            width: 10px;
        }
        .breakdown-share {
            color: #263238;
            font-weight: 700;
        }
        .breakdown-meta {
            color: #6b7280;
            display: flex;
            flex-wrap: wrap;
            font-size: 12px;
            gap: 12px;
        }
        .service-card-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .service-performance-card {
            border-top: 4px solid #82b368;
        }
        .service-performance-body {
            padding: 18px;
        }
        .service-performance-head {
            align-items: center;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
        }
        .service-performance-title {
            font-size: 16px;
            font-weight: 700;
        }
        .soft-badge {
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 9px;
        }
        .badge-most {
            background: #edf7ea;
            color: #477538;
        }
        .badge-moderate {
            background: #eef4ff;
            color: #315f9f;
        }
        .badge-least {
            background: #fff7ed;
            color: #a75b12;
        }
        .service-main-metric {
            font-size: 21px;
            font-weight: 750;
            margin-bottom: 4px;
        }
        .service-sub-metric {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 12px;
        }
        .progress-modern {
            background: #eef1f5;
            border-radius: 999px;
            height: 8px;
            overflow: hidden;
        }
        .progress-modern-fill {
            border-radius: 999px;
            height: 100%;
            min-width: 2px;
        }
        .table-card .report-card-body {
            padding: 0;
        }
        .table-toolbar {
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 20px;
        }
        .table-toolbar-title {
            font-size: 17px;
            font-weight: 700;
            margin: 0;
        }
        .table-toolbar-caption {
            color: #6b7280;
            font-size: 12px;
            margin-top: 3px;
        }
        .table-responsive-modern {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: 0 20px 20px;
        }
        .mobile-breakdown-toolbar,
        .mobile-report-cards {
            display: none;
        }
        .mobile-search-wrap {
            position: relative;
        }
        .mobile-search-wrap .fa {
            color: #82b368;
            left: 13px;
            position: absolute;
            top: 13px;
        }
        .mobile-search-input {
            border: 1px solid #dfe5ec;
            border-radius: 9px;
            box-shadow: none;
            height: 42px;
            padding-left: 36px;
            width: 100%;
        }
        .mobile-breakdown-actions {
            display: grid;
            gap: 8px;
            grid-template-columns: 1fr 1fr;
            margin-top: 9px;
        }
        .mobile-admin-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            margin-bottom: 10px;
            padding: 14px;
        }
        .mobile-admin-head {
            align-items: flex-start;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }
        .mobile-admin-name {
            color: #263238;
            font-size: 15px;
            font-weight: 700;
        }
        .mobile-admin-role {
            color: #6b7280;
            font-size: 12px;
            margin-top: 2px;
        }
        .mobile-admin-totals {
            display: grid;
            gap: 10px;
            grid-template-columns: 1fr 1fr;
            margin-bottom: 12px;
        }
        .mobile-total-value {
            color: #263238;
            font-size: 15px;
            font-weight: 700;
        }
        .mobile-total-label {
            color: #6b7280;
            font-size: 11px;
            margin-top: 2px;
        }
        .mobile-service-list {
            border-top: 1px solid #edf0f3;
            padding-top: 8px;
        }
        .mobile-service-row {
            padding: 7px 0;
        }
        .mobile-service-top,
        .mobile-service-meta {
            align-items: center;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .mobile-service-title {
            align-items: center;
            color: #263238;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            gap: 7px;
        }
        .mobile-service-percent {
            color: #263238;
            font-size: 13px;
            font-weight: 700;
        }
        .mobile-service-meta {
            color: #6b7280;
            font-size: 12px;
            margin-top: 3px;
        }
        .mobile-stacked-usage {
            margin-top: 9px;
            min-width: 0;
        }
        #retailer-service-report-table {
            border-collapse: separate;
            border-spacing: 0 10px;
            min-width: 980px;
            width: 100% !important;
        }
        #retailer-service-report-table thead th {
            background: transparent;
            border: 0;
            color: #6b7280;
            font-size: 12px;
            font-weight: 700;
            padding: 8px 10px;
            text-transform: uppercase;
            vertical-align: middle;
        }
        #retailer-service-report-table tbody td {
            background: #fff;
            border-bottom: 1px solid #edf0f3;
            border-top: 1px solid #edf0f3;
            padding: 13px 10px;
            vertical-align: middle;
        }
        #retailer-service-report-table tbody td:first-child {
            border-left: 1px solid #edf0f3;
            border-radius: 9px 0 0 9px;
            font-weight: 700;
        }
        #retailer-service-report-table tbody td:last-child {
            border-radius: 0 9px 9px 0;
            border-right: 1px solid #edf0f3;
        }
        #retailer-service-report-table tfoot td {
            background: #f8fafc;
            border: 0;
            color: #263238;
            font-weight: 700;
            padding: 13px 10px;
        }
        .cell-primary {
            color: #263238;
            font-weight: 700;
        }
        .cell-muted {
            color: #6b7280;
            font-size: 12px;
            margin-top: 2px;
        }
        .service-cell-name {
            font-weight: 700;
            margin-bottom: 4px;
        }
        .service-cell-metrics {
            color: #6b7280;
            font-size: 12px;
            line-height: 1.45;
        }
        .stacked-usage {
            background: #eef1f5;
            border-radius: 999px;
            display: flex;
            height: 12px;
            margin-bottom: 8px;
            overflow: hidden;
            min-width: 180px;
        }
        .stacked-usage-segment {
            height: 100%;
            min-width: 2px;
        }
        .stacked-usage-labels {
            color: #6b7280;
            display: flex;
            flex-wrap: wrap;
            font-size: 11px;
            gap: 8px;
        }
        .dataTables_wrapper .dt-buttons {
            float: right;
            margin-bottom: 10px;
        }
        .dataTables_wrapper .dt-buttons .btn,
        .dataTables_wrapper .dt-buttons button {
            background: #fff !important;
            border: 1px solid #dfe5ec !important;
            border-radius: 8px !important;
            color: #263238 !important;
            font-weight: 600;
            margin-left: 6px;
            padding: 7px 11px !important;
        }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #dfe5ec;
            border-radius: 7px;
            box-shadow: none;
            padding: 6px 8px;
        }
        @media (max-width: 1199px) {
            .kpi-grid,
            .service-card-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .distribution-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 767px) {
            html,
            body {
                overflow-x: hidden;
            }
            .service-report-page *,
            .service-report-page *:before,
            .service-report-page *:after {
                box-sizing: border-box;
                max-width: 100%;
            }
            .service-report-page {
                margin: -15px;
                padding: 10px;
            }
            .report-header,
            .table-toolbar {
                display: block;
            }
            .report-header {
                margin-bottom: 10px;
            }
            .report-breadcrumb {
                font-size: 11px;
                margin-bottom: 5px;
            }
            .report-title {
                font-size: 21px;
            }
            .report-subtitle {
                font-size: 12px;
                line-height: 1.35;
                margin-top: 4px;
            }
            .report-card {
                border-radius: 9px;
                margin-bottom: 11px;
            }
            .filter-card {
                padding: 14px;
            }
            .filter-toolbar {
                display: block;
            }
            .filter-card:before {
                color: #263238;
                content: 'Date Range';
                display: block;
                font-size: 15px;
                font-weight: 700;
                margin-bottom: 10px;
            }
            .filter-field {
                margin-bottom: 9px;
                min-width: 0;
                width: 100%;
            }
            .filter-field label {
                font-size: 12px;
                margin-bottom: 4px;
            }
            .date-input-wrap .form-control {
                height: 40px;
            }
            .filter-actions {
                display: grid;
                gap: 8px;
                grid-template-columns: 2fr 1fr;
                margin-top: 2px;
            }
            .filter-actions .btn {
                height: 40px;
                margin-bottom: 0;
                padding-left: 8px;
                padding-right: 8px;
                width: 100%;
            }
            .quick-filters {
                display: block;
                margin-top: 10px;
                overflow-x: auto;
                padding-bottom: 2px;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            .quick-filter {
                display: inline-block;
                margin-right: 6px;
                padding: 6px 10px;
                white-space: nowrap;
            }
            .kpi-grid {
                gap: 10px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .service-card-grid {
                gap: 10px;
                grid-template-columns: 1fr;
            }
            .kpi-amount-card {
                grid-column: 1 / -1;
            }
            .kpi-card {
                min-height: 88px;
            }
            .kpi-body {
                gap: 10px;
                padding: 13px;
            }
            .kpi-icon {
                border-radius: 8px;
                flex-basis: 34px;
                font-size: 15px;
                height: 34px;
                width: 34px;
            }
            .kpi-label {
                font-size: 12px;
                margin-bottom: 3px;
            }
            .kpi-value {
                font-size: 23px;
            }
            .kpi-note {
                font-size: 11px;
                margin-top: 3px;
            }
            .report-card-body,
            .service-performance-body {
                padding: 14px;
            }
            .section-heading {
                display: block;
                margin-bottom: 10px;
            }
            .section-title {
                font-size: 16px;
            }
            .section-caption {
                font-size: 11px;
                margin-top: 4px;
            }
            .distribution-grid {
                gap: 10px;
            }
            .donut-wrap {
                min-height: 198px;
            }
            #retailerServiceDonutChart {
                height: 195px !important;
                width: 195px !important;
            }
            .service-breakdown {
                gap: 0;
            }
            .breakdown-item {
                border: 0;
                border-bottom: 1px solid #edf0f3;
                border-radius: 0;
                padding: 8px 0;
            }
            .breakdown-item:last-child {
                border-bottom: 0;
            }
            .breakdown-top {
                margin-bottom: 4px;
            }
            .service-name,
            .breakdown-share {
                font-size: 13px;
            }
            .breakdown-meta {
                font-size: 12px;
                gap: 8px;
            }
            .service-performance-head {
                margin-bottom: 8px;
            }
            .service-performance-title {
                font-size: 15px;
            }
            .soft-badge {
                font-size: 11px;
                padding: 4px 8px;
            }
            .service-main-metric {
                display: none;
            }
            .service-sub-metric {
                font-size: 12px;
                line-height: 1.35;
                margin-bottom: 9px;
            }
            .table-card .report-card-body {
                padding: 0;
            }
            .table-toolbar {
                border-bottom: 0;
                padding: 14px 14px 8px;
            }
            .table-toolbar-title {
                font-size: 16px;
            }
            .table-toolbar-caption,
            .table-toolbar .section-caption {
                display: none;
            }
            .mobile-breakdown-toolbar,
            .mobile-report-cards {
                display: block;
            }
            .mobile-breakdown-toolbar {
                padding: 0 14px 12px;
            }
            .table-responsive-modern {
                display: none;
            }
            .service-report-page .dataTables_wrapper {
                display: none;
            }
            .mobile-admin-card.is-hidden {
                display: none;
            }
            .empty-state {
                font-size: 12px;
                padding: 16px;
            }
        }
        @media (max-width: 379px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }
            .kpi-amount-card {
                grid-column: auto;
            }
        }
    </style>

    @php
        $serviceColorMap = [
            'TopUp' => '#2e7d32',
            'Calling Cards' => '#c0392b',
            'Bus' => '#f2c94c',
        ];
        $fallbackServiceColors = ['#f59e0b', '#0f766e', '#b83280', '#64748b'];
        $serviceOverview = [];
        $servicePieData = [];
        $mostUsedAmount = 0;
        $leastUsedAmount = null;

        foreach ($services as $index => $service) {
            $serviceTotal = $totals['services'][$service->id];
            $count = (int) $serviceTotal['count'];
            $amount = (float) $serviceTotal['amount'];
            $percentage = (float) $serviceTotal['percentage'];
            $color = isset($serviceColorMap[$service->report_name])
                ? $serviceColorMap[$service->report_name]
                : $fallbackServiceColors[$index % count($fallbackServiceColors)];

            $serviceOverview[$service->id] = [
                'count' => $count,
                'amount' => $amount,
                'percentage' => $percentage,
                'color' => $color,
            ];
            $servicePieData[] = [
                'value' => $amount,
                'amount' => $amount,
                'percentage' => $percentage,
                'color' => $color,
                'highlight' => $color,
                'label' => $service->report_name,
            ];

            if ($amount > $mostUsedAmount) {
                $mostUsedAmount = $amount;
            }

            if ($amount > 0 && ($leastUsedAmount === null || $amount < $leastUsedAmount)) {
                $leastUsedAmount = $amount;
            }
        }

        $reportData = [
            'servicePieData' => $servicePieData,
            'totalTransactions' => (int) $totals['transaction_count'],
            'totalAmount' => (float) $totals['transaction_amount'],
            'hasData' => (float) $totals['transaction_amount'] > 0,
        ];
    @endphp

    <div class="service-report-page">
        <div class="report-shell">
            <div class="report-header">
                <div>
                    <div class="report-breadcrumb">Dashboard &gt; Reports &gt; Service Reports</div>
                    <h1 class="report-title">{{ trans('reports.labels.service_report') }}</h1>
                    <div class="report-subtitle">Monitor transaction volume, transaction amount and service amount share.</div>
                </div>
            </div>

            <div class="service-report-ajax-content">
                <script type="application/json" id="retailerServiceReportData">{!! json_encode($reportData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>

                <div class="report-card filter-card">
                    <form method="GET" id="retailer-service-filter-form" action="{{ secure_url('reports/retailer-services') }}">
                        <div class="filter-toolbar">
                            <div class="filter-field">
                                <label for="from_date">{{ trans('reports.labels.from') }}</label>
                                <div class="date-input-wrap">
                                    <i class="fa fa-calendar"></i>
                                    <input type="text" class="form-control date" name="from_date" id="from_date" value="{{ $from_date }}">
                                </div>
                            </div>
                            <div class="filter-field">
                                <label for="to_date">{{ trans('reports.labels.to') }}</label>
                                <div class="date-input-wrap">
                                    <i class="fa fa-calendar"></i>
                                    <input type="text" class="form-control date" name="to_date" id="to_date" value="{{ $to_date }}">
                                </div>
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-report-primary">
                                    <i class="fa fa-filter"></i> Apply Filter
                                </button>
                                <button type="button" class="btn btn-report-light" id="retailer-service-reset-filter">
                                    <i class="fa fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                        <div class="quick-filters">
                            <button type="button" class="quick-filter" data-range="today">Today</button>
                            <button type="button" class="quick-filter" data-range="yesterday">Yesterday</button>
                            <button type="button" class="quick-filter" data-range="last_7_days">Last 7 Days</button>
                            <button type="button" class="quick-filter" data-range="this_month">This Month</button>
                            <button type="button" class="quick-filter" data-range="last_month">Last Month</button>
                        </div>
                    </form>
                </div>

                <div class="kpi-grid">
                    <div class="report-card kpi-card">
                        <div class="kpi-body">
                            <div class="kpi-icon"><i class="fa fa-users"></i></div>
                            <div>
                                <div class="kpi-label">{{ $child_user_label }}</div>
                                <div class="kpi-value">{{ number_format($child_user_count) }}</div>
                                <div class="kpi-note">Active report scope</div>
                            </div>
                        </div>
                    </div>
                    <div class="report-card kpi-card">
                        <div class="kpi-body">
                            <div class="kpi-icon"><i class="fa fa-exchange"></i></div>
                            <div>
                                <div class="kpi-label">{{ trans('reports.labels.transaction_count') }}</div>
                                <div class="kpi-value">{{ number_format($totals['transaction_count']) }}</div>
                                <div class="kpi-note">Successful transactions</div>
                            </div>
                        </div>
                    </div>
                    <div class="report-card kpi-card kpi-amount-card">
                        <div class="kpi-body">
                            <div class="kpi-icon"><i class="fa fa-credit-card"></i></div>
                            <div>
                                <div class="kpi-label">{{ trans('reports.labels.transaction_amount') }}</div>
                                <div class="kpi-value">&euro;{{ number_format($totals['transaction_amount'], 2) }}</div>
                                <div class="kpi-note">Total sale amount</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if((int) $totals['transaction_count'] === 0)
                    <div class="empty-state" style="display:block;margin-bottom:18px;">
                        No report data available for the selected period.
                    </div>
                @endif

                <div class="report-card">
                    <div class="report-card-body">
                        <div class="section-heading">
                            <div>
                                <h2 class="section-title">Service Amount Distribution</h2>
                                <div class="section-caption">Percentages are calculated from transaction amount.</div>
                            </div>
                            <div class="section-caption">{{ $from_date }} to {{ $to_date }}</div>
                        </div>
                        <div class="distribution-grid">
                            <div class="donut-wrap">
                                <canvas id="retailerServiceDonutChart" width="270" height="270"></canvas>
                                <div id="retailerServiceDonutEmpty" class="empty-state">No report data available for the selected period.</div>
                            </div>
                            <div class="service-breakdown">
                                @foreach($services as $service)
                                    @php $overview = $serviceOverview[$service->id]; @endphp
                                    <div class="breakdown-item">
                                        <div class="breakdown-top">
                                            <span class="service-name"><span class="service-dot" style="background: {{ $overview['color'] }}"></span>{{ $service->report_name }}</span>
                                            <span class="breakdown-share">{{ number_format($overview['percentage'], 2) }}%</span>
                                        </div>
                                        <div class="breakdown-meta">
                                            <span>{{ number_format($overview['count']) }} Transactions</span>
                                            <span>&euro;{{ number_format($overview['amount'], 2) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="service-card-grid">
                    @foreach($services as $service)
                        @php
                            $overview = $serviceOverview[$service->id];
                            $isMostUsed = $overview['amount'] > 0 && $overview['amount'] === $mostUsedAmount;
                            $isLeastUsed = $overview['amount'] > 0 && $overview['amount'] === $leastUsedAmount && !$isMostUsed;
                            $badgeClass = $isMostUsed ? 'badge-most' : ($isLeastUsed ? 'badge-least' : 'badge-moderate');
                            $badgeText = $isMostUsed ? 'Highest Amount' : ($isLeastUsed ? 'Lowest Amount' : 'Moderate');
                        @endphp
                        <div class="report-card service-performance-card" style="border-top-color: {{ $overview['color'] }}">
                            <div class="service-performance-body">
                                <div class="service-performance-head">
                                    <div class="service-performance-title">{{ $service->report_name }}</div>
                                    <span class="soft-badge {{ $badgeClass }}">{{ $badgeText }}</span>
                                </div>
                                <div class="service-main-metric">{{ number_format($overview['count']) }} Transactions</div>
                                <div class="service-sub-metric">&euro;{{ number_format($overview['amount'], 2) }} Amount &bull; {{ number_format($overview['percentage'], 2) }}% Amount Share</div>
                                <div class="progress-modern">
                                    <div class="progress-modern-fill" style="width: {{ min(100, $overview['percentage']) }}%; background: {{ $overview['color'] }}"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="report-card table-card">
                    <div class="report-card-body">
                        <div class="table-toolbar">
                            <div>
                                <h2 class="table-toolbar-title">{{ $child_user_singular_label }} Service Breakdown</h2>
                                <div class="table-toolbar-caption">Search, sort, export, and compare service amount share by account.</div>
                            </div>
                            <div class="section-caption">Excel Export and Refresh are available in the table toolbar.</div>
                        </div>
                        <div class="mobile-breakdown-toolbar">
                            <div class="mobile-search-wrap">
                                <i class="fa fa-search"></i>
                                <input type="text" class="mobile-search-input" id="mobileReportSearch" placeholder="Search admin...">
                            </div>
                            <div class="mobile-breakdown-actions">
                                <button type="button" class="btn btn-report-light" id="mobileExcelExport" @if((int) $totals['transaction_count'] === 0) disabled @endif>
                                    <i class="fa fa-file-excel-o"></i> Excel
                                </button>
                                <button type="button" class="btn btn-report-light" id="mobileReportRefresh">
                                    <i class="fa fa-refresh"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div class="mobile-report-cards">
                            @foreach($rows as $row)
                                <div class="mobile-admin-card" data-search="{{ strtolower($row['user']->username) }}">
                                    <div class="mobile-admin-head">
                                        <div>
                                            <div class="mobile-admin-name">{{ $row['user']->username }}</div>
                                            <div class="mobile-admin-role">{{ $child_user_singular_label }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-admin-totals">
                                        <div>
                                            <div class="mobile-total-value">{{ number_format($row['total_count']) }}</div>
                                            <div class="mobile-total-label">Transactions</div>
                                        </div>
                                        <div>
                                            <div class="mobile-total-value">&euro;{{ number_format($row['total_amount'], 2) }}</div>
                                            <div class="mobile-total-label">Amount</div>
                                        </div>
                                    </div>
                                    <div class="mobile-service-list">
                                        @foreach($services as $service)
                                            @php
                                                $serviceData = $row['services'][$service->id];
                                                $percentage = (float) $serviceData['percentage'];
                                                $color = $serviceOverview[$service->id]['color'];
                                            @endphp
                                            <div class="mobile-service-row">
                                                <div class="mobile-service-top">
                                                    <span class="mobile-service-title"><span class="service-dot" style="background: {{ $color }}"></span>{{ $service->report_name }}</span>
                                                    <span class="mobile-service-percent">{{ number_format($percentage, 2) }}%</span>
                                                </div>
                                                <div class="mobile-service-meta">
                                                    <span>{{ number_format($serviceData['count']) }} transactions</span>
                                                    <span>&euro;{{ number_format($serviceData['amount'], 2) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="stacked-usage mobile-stacked-usage">
                                            @foreach($services as $service)
                                                @php
                                                    $serviceData = $row['services'][$service->id];
                                                    $percentage = (float) $serviceData['percentage'];
                                                    $color = $serviceOverview[$service->id]['color'];
                                                @endphp
                                                @if($percentage > 0)
                                                    <span class="stacked-usage-segment" style="width: {{ min(100, $percentage) }}%; background: {{ $color }}"></span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="empty-state" id="mobileReportNoResults">No matching report data.</div>
                        </div>
                        <div class="table-responsive-modern">
                            <table id="retailer-service-report-table" class="table">
                                <thead>
                                <tr>
                                    <th>{{ $child_user_singular_label }}</th>
                                    <th>{{ trans('reports.labels.transaction_count') }}</th>
                                    <th>{{ trans('reports.labels.transaction_amount') }}</th>
                                    @foreach($services as $service)
                                        <th>{{ $service->report_name }}</th>
                                    @endforeach
                                    <th>Amount Breakdown</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($rows as $row)
                                    <tr>
                                        <td>
                                            <div class="cell-primary">{{ $row['user']->username }}</div>
                                            <div class="cell-muted">{{ $child_user_singular_label }}</div>
                                        </td>
                                        <td data-order="{{ $row['total_count'] }}">
                                            <div class="cell-primary">{{ number_format($row['total_count']) }}</div>
                                            <div class="cell-muted">Transactions</div>
                                        </td>
                                        <td data-order="{{ $row['total_amount'] }}">
                                            <div class="cell-primary">&euro;{{ number_format($row['total_amount'], 2) }}</div>
                                            <div class="cell-muted">Amount</div>
                                        </td>
                                        @foreach($services as $service)
                                            @php
                                                $serviceData = $row['services'][$service->id];
                                                $percentage = (float) $serviceData['percentage'];
                                                $count = (int) $serviceData['count'];
                                            @endphp
                                            <td data-order="{{ $serviceData['amount'] }}">
                                                <div class="service-cell-name">{{ $service->report_name }}</div>
                                                <div class="service-cell-metrics">
                                                    {{ number_format($count) }} transactions<br>
                                                    &euro;{{ number_format($serviceData['amount'], 2) }}<br>
                                                    {{ number_format($percentage, 2) }}% amount share
                                                </div>
                                            </td>
                                        @endforeach
                                        <td>
                                            <div class="stacked-usage">
                                                @foreach($services as $service)
                                                    @php
                                                        $serviceData = $row['services'][$service->id];
                                                        $percentage = (float) $serviceData['percentage'];
                                                        $color = $serviceOverview[$service->id]['color'];
                                                    @endphp
                                                    @if($percentage > 0)
                                                        <span class="stacked-usage-segment" style="width: {{ min(100, $percentage) }}%; background: {{ $color }}"></span>
                                                    @endif
                                                @endforeach
                                            </div>
                                            <div class="stacked-usage-labels">
                                                @foreach($services as $service)
                                                    @php $serviceData = $row['services'][$service->id]; @endphp
                                                    <span>{{ $service->report_name }} {{ number_format($serviceData['percentage'], 0) }}%</span>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td>{{ trans('reports.labels.total') }}</td>
                                    <td>{{ number_format($totals['transaction_count']) }}</td>
                                    <td>&euro;{{ number_format($totals['transaction_amount'], 2) }}</td>
                                    @foreach($services as $service)
                                        @php $serviceTotal = $totals['services'][$service->id]; @endphp
                                        <td data-order="{{ $serviceTotal['amount'] }}">
                                            {{ number_format($serviceTotal['count']) }} transactions<br>
                                            &euro;{{ number_format($serviceTotal['amount'], 2) }}<br>
                                            {{ number_format($serviceTotal['percentage'], 2) }}% amount share
                                        </td>
                                    @endforeach
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ secure_asset('vendor/date-picker/jquery-ui.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ secure_asset('vendor/datatables/app.js') }}"></script>
    <script src="{{ secure_asset('assets/js/chart/chartjs/chart.min.js') }}"></script>
    <script>
        $(function () {
            var amountLabelText = '{{ trans('reports.labels.amount') }}';
            var ajaxRequest = null;

            function formatDate(date) {
                var month = String(date.getMonth() + 1);
                var day = String(date.getDate());
                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;
                return date.getFullYear() + '-' + month + '-' + day;
            }

            function rangeDates(range) {
                var today = new Date();
                var from = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                var to = new Date(today.getFullYear(), today.getMonth(), today.getDate());

                if (range === 'yesterday') {
                    from.setDate(from.getDate() - 1);
                    to.setDate(to.getDate() - 1);
                } else if (range === 'last_7_days') {
                    from.setDate(from.getDate() - 6);
                } else if (range === 'this_month') {
                    from = new Date(today.getFullYear(), today.getMonth(), 1);
                } else if (range === 'last_month') {
                    from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    to = new Date(today.getFullYear(), today.getMonth(), 0);
                }

                return { from: formatDate(from), to: formatDate(to) };
            }

            function formatEuroText(amount) {
                var value = parseFloat(amount) || 0;
                return String.fromCharCode(8364) + value.toFixed(2);
            }

            function drawDonutCenter(chart, totalAmount) {
                if (!chart || !chart.chart) return;
                var ctx = chart.chart.ctx;
                var x = chart.chart.width / 2;
                var y = chart.chart.height / 2;

                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillStyle = '#263238';
                ctx.font = '700 24px Arial';
                ctx.fillText(formatEuroText(totalAmount), x, y - 6);
                ctx.fillStyle = '#6b7280';
                ctx.font = '600 12px Arial';
                ctx.fillText('Amount', x, y + 17);
                ctx.restore();
            }

            function readReportData() {
                var script = document.getElementById('retailerServiceReportData');
                if (!script) {
                    return { servicePieData: [], totalTransactions: 0, totalAmount: 0, hasData: false };
                }

                try {
                    return JSON.parse(script.text || script.textContent || '{}');
                } catch (exception) {
                    return { servicePieData: [], totalTransactions: 0, totalAmount: 0, hasData: false };
                }
            }

            function drawReportCharts(data) {
                var servicePieData = data.servicePieData || [];
                var totalAmount = parseFloat(data.totalAmount) || 0;
                var canvas = document.getElementById('retailerServiceDonutChart');

                if (canvas && window.Chart && totalAmount > 0) {
                    new Chart(canvas.getContext('2d')).Doughnut(servicePieData, {
                        animationSteps: 36,
                        responsive: true,
                        percentageInnerCutout: 68,
                        segmentShowStroke: true,
                        segmentStrokeColor: '#ffffff',
                        segmentStrokeWidth: 3,
                        tooltipTemplate: '<%if (label){%><%=label%>: <%}%>' + String.fromCharCode(8364) + '<%= value %> (' + amountLabelText + ')',
                        onAnimationComplete: function () {
                            drawDonutCenter(this, totalAmount);
                        }
                    });
                } else {
                    $('#retailerServiceDonutChart').hide();
                    $('#retailerServiceDonutEmpty').show();
                }
            }

            function initDateFilters() {
                $('.date').datepicker({
                    changeYear: true,
                    maxDate: new Date(),
                    showButtonPanel: true,
                    dateFormat: 'yy-mm-dd',
                    showAnim: 'slideDown'
                });
            }

            function destroyReportDataTable() {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable && $.fn.DataTable.isDataTable('#retailer-service-report-table')) {
                    $('#retailer-service-report-table').DataTable().destroy();
                }
            }

            function initReportDataTable() {
                var reportData = readReportData();
                var hasData = !!reportData.hasData;
                var buttons = [
                    'pageLength',
                    {
                        extend: 'reload',
                        text: '<i class="fa fa-refresh"></i> Refresh',
                        titleAttr: '{{ trans('common.refresh') }}'
                    }
                ];

                if (hasData) {
                    buttons.splice(1, 0, {
                        extend: 'excel',
                        text: '<i class="fa fa-file-excel-o"></i> Excel Export',
                        titleAttr: '{{ trans('common.download_as_excel') }}'
                    });
                }

                $('#retailer-service-report-table').DataTable({
                    autoWidth: false,
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    scrollX: true,
                    order: [[2, 'desc']],
                    dom: 'Bfrtip',
                    lengthMenu: [
                        [10, 25, 50, -1],
                        ['10 {{ trans('users.records') }}', '25 {{ trans('users.records') }}', '50 {{ trans('users.records') }}', '{{ trans('users.show_all') }}']
                    ],
                    buttons: buttons
                });
            }

            function filterMobileReportCards(query) {
                var normalized = String(query || '').toLowerCase();
                var visibleCount = 0;

                $('.mobile-admin-card').each(function () {
                    var $card = $(this);
                    var haystack = String($card.data('search') || '').toLowerCase();
                    var isVisible = !normalized || haystack.indexOf(normalized) !== -1;
                    $card.toggleClass('is-hidden', !isVisible);

                    if (isVisible) {
                        visibleCount++;
                    }
                });

                $('#mobileReportNoResults').toggle(visibleCount === 0);
            }

            function initRetailerServiceReport() {
                initDateFilters();
                drawReportCharts(readReportData());
                initReportDataTable();
                filterMobileReportCards($('#mobileReportSearch').val());
                $('[title]').tooltip && $('[title]').tooltip();
            }

            function setReportLoading(isLoading) {
                var $content = $('.service-report-ajax-content');
                $content.toggleClass('is-loading', isLoading);
                $content.find('button[type="submit"]').prop('disabled', isLoading);
            }

            function fetchReport($form) {
                var action = $form.attr('action');
                var query = $form.serialize();
                var requestUrl = action + (query ? '?' + query : '');

                if (ajaxRequest && ajaxRequest.readyState !== 4) {
                    ajaxRequest.abort();
                }

                setReportLoading(true);

                ajaxRequest = $.ajax({
                    url: action,
                    type: 'GET',
                    data: query,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).done(function (html) {
                    var parsed = $.parseHTML(html, document, true);
                    var $response = $('<div>').append(parsed);
                    var $newContent = $response.find('.service-report-ajax-content').first();

                    if (!$newContent.length) {
                        window.location.href = requestUrl;
                        return;
                    }

                    destroyReportDataTable();
                    $('.service-report-ajax-content').first().replaceWith($newContent);
                    window.history.pushState(null, '', requestUrl);
                    initRetailerServiceReport();
                }).fail(function (xhr, status) {
                    if (status !== 'abort') {
                        window.location.href = requestUrl;
                    }
                }).always(function () {
                    setReportLoading(false);
                });
            }

            $(document).on('submit', '#retailer-service-filter-form', function (event) {
                event.preventDefault();
                fetchReport($(this));
            });

            $(document).on('click', '.quick-filter', function () {
                var dates = rangeDates($(this).data('range'));
                var $form = $('#retailer-service-filter-form');
                $form.find('[name="from_date"]').val(dates.from);
                $form.find('[name="to_date"]').val(dates.to);
                fetchReport($form);
            });

            $(document).on('click', '#retailer-service-reset-filter', function () {
                var dates = rangeDates('today');
                var $form = $('#retailer-service-filter-form');
                $form.find('[name="from_date"]').val(dates.from);
                $form.find('[name="to_date"]').val(dates.to);
                fetchReport($form);
            });

            $(document).on('keyup change', '#mobileReportSearch', function () {
                filterMobileReportCards($(this).val());
            });

            $(document).on('click', '#mobileReportRefresh', function () {
                fetchReport($('#retailer-service-filter-form'));
            });

            $(document).on('click', '#mobileExcelExport', function () {
                var $excelButton = $('.buttons-excel').first();

                if ($excelButton.length) {
                    $excelButton.trigger('click');
                }
            });

            initRetailerServiceReport();
        });
    </script>
@endsection
