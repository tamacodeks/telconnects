<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Order;
use App\User;
use app\Library\AppHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $groupId = (int) optional($user)->group_id;

        $ui = [
            'show_banners' => $groupId !== 1,
            'show_balances' => false,
            'show_kpis' => true,
            'show_monthly_chart' => false,
            'show_global_range' => false,
            'show_top_ops' => false,
            'show_service_chart' => false,
            'show_topup_health' => false,
            'show_margin' => false,
            'show_latest_orders' => $groupId !== 1,
        ];

        // V1-style group 6 shows operational counters and recent orders; hide admin analytics widgets.
        if ($groupId === 6) {
            $ui['show_global_range'] = false;
            $ui['show_top_ops'] = false;
            $ui['show_service_chart'] = false;
            $ui['show_topup_health'] = false;
            $ui['show_margin'] = false;
            $ui['show_monthly_chart'] = false;
            $ui['show_balances'] = false;
        }

        // V1-style retailer dashboard (group 4) is action-oriented, not analytics-heavy.
        if ($groupId === 4) {
            $ui['show_banners'] = true;
            $ui['show_global_range'] = false;
            $ui['show_top_ops'] = false;
            $ui['show_service_chart'] = false;
            $ui['show_topup_health'] = false;
            $ui['show_margin'] = false;
            $ui['show_monthly_chart'] = false;
            $ui['show_balances'] = false;
        }

        // V1-style parent/master groups 2/3 focus on KPIs + latest orders.
        if (in_array($groupId, [2, 3], true)) {
            $ui['show_global_range'] = false;
            $ui['show_top_ops'] = false;
            $ui['show_service_chart'] = false;
            $ui['show_topup_health'] = false;
            $ui['show_margin'] = false;
            $ui['show_monthly_chart'] = false;
            $ui['show_balances'] = false;
        }

        return view('v2.layout.dashboard', [
            'page_title' => 'Dashboard',
            'ui' => $ui,
        ]);
    }

    public function summary(Request $request)
    {
        $user = auth()->user();
        $groupId = (int) optional($user)->group_id;
        $ttl = $groupId === 1 ? 90 : ($groupId === 4 ? 60 : 45);
        $cacheKey = $this->dashboardCacheKey('summary', [
            'date' => Carbon::today()->toDateString(),
            'default_record_method' => defined('DEFAULT_RECORD_METHOD') ? DEFAULT_RECORD_METHOD : 1,
            'dashboard_version' => 9,
        ]);

        $payload = Cache::remember($cacheKey, $ttl, function () use ($user, $groupId) {
            $today = Carbon::today();
            $todayOrdersCount = 0;
            $todayAmount = 0.0;
            $monthAmount = 0.0;

            if ($groupId !== 1 && $groupId !== 4) {
                $todayOrders = $this->scopedOrdersQuery(true)
                    ->whereBetween('orders.date', [$today->copy()->startOfDay(), $today->copy()->endOfDay()]);

                $todayOrdersCount = (int) (clone $todayOrders)->count();
                list($todayAmount, $monthAmount) = $this->dashboardTransactionAmounts($user, $today);
            }

            $payload = [
                'group_id' => $groupId,
                'total_resellers' => in_array($groupId, [2, 3], true) ? $this->v1StyleTotalResellersCount($user) : 0,
                'total_transaction' => $this->formatEuro($monthAmount),
                'total_orders_today' => $todayOrdersCount,
                'total_orders' => $todayOrdersCount,
                'today_transaction' => $this->formatEuro($todayAmount),
                'orders_in_progress' => $groupId === 6 ? $this->group6OrdersInProgressCount($user) : 0,
                'closed_orders' => $groupId === 6 ? $this->group6ClosedOrdersCount($user) : 0,
                'failed_callbacks_count' => null,
                'routes_count' => null,
                'root_metrics' => $groupId === 1 ? $this->rootDashboardMetrics() : [],
                'root_attention' => $groupId === 1 ? $this->rootAttentionItems() : [],
                'root_system_health' => $groupId === 1 ? $this->rootSystemHealth() : [],
                'root_operational' => [],
                'root_recent_activity' => $groupId === 1 ? $this->rootRecentActivity() : [],
                'banners' => $groupId !== 1 ? $this->bannerPayload($user) : [],
                'quick_actions' => $groupId === 4 ? $this->retailerQuickActions() : [],
            ];

            if ($groupId === 4) {
                $payload = array_merge($payload, $this->retailerDashboardSummary($user, $today));
            }

            return $payload;
        });

        return response()->json($this->withDashboardMeta($payload, $cacheKey, $ttl));
    }

    public function orders(Request $request)
    {
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:50',
            'page' => 'sometimes|integer|min:1|max:500',
        ]);

        $perPage = max(1, min(50, (int) $request->get('per_page', 10)));
        $page = max(1, (int) $request->get('page', 1));
        $offset = ($page - 1) * $perPage;

        $ttl = 30;
        $cacheKey = $this->dashboardCacheKey('orders', [
            'page' => $page,
            'per_page' => $perPage,
            'dashboard_version' => 2,
        ]);

        $payload = Cache::remember($cacheKey, $ttl, function () use ($offset, $perPage, $page) {
            $rows = $this->scopedOrdersQuery(true)
                ->select([
                    'orders.id',
                    'orders.date',
                    'users.username',
                    'services.name as service_name',
                    'orders.order_amount',
                    'order_status.name as status',
                    'order_items.tt_operator',
                ])
                ->orderBy('orders.id', 'desc')
                ->skip($offset)
                ->take($perPage)
                ->get();

            $data = $rows->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'date' => $this->safeDate($row->date),
                    'username' => (string) $row->username,
                    'tt_operator' => $row->tt_operator ? (string) $row->tt_operator : null,
                    'product_name' => $row->tt_operator ? (string) $row->tt_operator : null,
                    'service_name' => (string) $row->service_name,
                    'order_amount' => $this->formatEuro($row->order_amount),
                    'status' => (string) $row->status,
                ];
            })->values();

            return [
                'data' => $data,
                'page' => $page,
                'per_page' => $perPage,
            ];
        });

        return response()->json($this->withDashboardMeta($payload, $cacheKey, $ttl));
    }

    public function monthlyTransactions(Request $request)
    {
        $cacheKey = $this->dashboardCacheKey('monthly-transactions', [
            'range' => 'last_6_months',
            'month' => Carbon::now()->format('Y-m'),
        ]);

        $payload = Cache::remember($cacheKey, 300, function () {
            $now = Carbon::now();
            $start = $now->copy()->startOfMonth()->subMonths(5);
            $end = $now->copy()->endOfMonth();

            $rows = $this->scopedOrdersQuery(false)
                ->whereBetween('orders.date', [$start, $end])
                ->select([
                    DB::raw('YEAR(orders.date) as y'),
                    DB::raw('MONTH(orders.date) as m'),
                    DB::raw('SUM(orders.order_amount) as total_amount'),
                ])
                ->groupBy(DB::raw('YEAR(orders.date), MONTH(orders.date)'))
                ->orderBy(DB::raw('YEAR(orders.date)'))
                ->orderBy(DB::raw('MONTH(orders.date)'))
                ->get();

            $indexed = [];
            foreach ($rows as $row) {
                $key = sprintf('%04d-%02d', $row->y, $row->m);
                $indexed[$key] = (float) $row->total_amount;
            }

            $labels = [];
            $data = [];
            $cursor = $start->copy();
            while ($cursor <= $end) {
                $key = $cursor->format('Y-m');
                $labels[] = $cursor->format('M Y');
                $data[] = isset($indexed[$key]) ? round($indexed[$key], 2) : 0;
                $cursor->addMonth();
            }

            return [
                'labels' => $labels,
                'data' => $data,
            ];
        });

        return response()->json($this->withDashboardMeta($payload, $cacheKey, 300));
    }

    public function balances(Request $request)
    {
        // Placeholder structure to match v2 dashboard UI.
        return response()->json([
            'reloadly' => ['ok' => false, 'value' => null],
            'ding' => ['ok' => false, 'value' => null],
            'transfer' => ['ok' => false, 'value' => null],
        ]);
    }

    public function serviceMonthly(Request $request)
    {
        $this->validateDashboardRange($request);
        $rangeKey = $this->rangeCacheKey($request);
        $cacheKey = $this->dashboardCacheKey('service-monthly', ['range' => $rangeKey]);

        $payload = Cache::remember($cacheKey, 120, function () use ($request) {
            $query = $this->scopedOrdersQuery(true);
            $this->applyRange($query, $request);

            $items = $query
                ->select([
                    'services.name as label',
                    DB::raw('SUM(orders.order_amount) as total_amount'),
                ])
                ->groupBy('services.id', 'services.name')
                ->orderBy(DB::raw('SUM(orders.order_amount)'), 'desc')
                ->take(10)
                ->get()
                ->map(function ($row) {
                    return [
                        'label' => (string) $row->label,
                        'total_amount' => round((float) $row->total_amount, 2),
                    ];
                })
                ->values();

            return ['items' => $items];
        });

        return response()->json($this->withDashboardMeta($payload, $cacheKey, 120));
    }

    public function topupHealth(Request $request)
    {
        $this->validateDashboardRange($request);
        $rangeKey = $this->rangeCacheKey($request);
        $cacheKey = $this->dashboardCacheKey('topup-health', ['range' => $rangeKey]);

        $payload = Cache::remember($cacheKey, 120, function () use ($request) {
            $query = $this->scopedOrdersQuery(true);
            $this->applyRange($query, $request);

            $rows = $query
                ->select([
                    'order_status.name as status_name',
                    DB::raw('COUNT(*) as total_count'),
                ])
                ->groupBy('order_status.id', 'order_status.name')
                ->get();

            $success = 0;
            $failed = 0;

            foreach ($rows as $row) {
                $name = strtolower((string) $row->status_name);
                $count = (int) $row->total_count;

                if ($this->isSuccessStatus($name)) {
                    $success += $count;
                } elseif ($this->isFailedStatus($name)) {
                    $failed += $count;
                }
            }

            return [
                'success_count' => $success,
                'failed_count' => $failed,
            ];
        });

        return response()->json($this->withDashboardMeta($payload, $cacheKey, 120));
    }

    public function margins(Request $request)
    {
        $this->validateDashboardRange($request);
        $rangeKey = $this->rangeCacheKey($request);
        $cacheKey = $this->dashboardCacheKey('margins', ['range' => $rangeKey]);

        $payload = Cache::remember($cacheKey, 120, function () use ($request) {
            $query = $this->scopedOrdersQuery(true);
            $this->applyRange($query, $request);

            $rows = $query
                ->select([
                    DB::raw('DATE(orders.date) as d'),
                    DB::raw('SUM(COALESCE(orders.order_amount, 0)) as sale_total'),
                    DB::raw('SUM(COALESCE(orders.buying_price, 0)) as buy_total'),
                    DB::raw('SUM(COALESCE(orders.sale_margin, (COALESCE(orders.order_amount,0) - COALESCE(orders.buying_price,0)))) as margin_total'),
                ])
                ->groupBy(DB::raw('DATE(orders.date)'))
                ->orderBy(DB::raw('DATE(orders.date)'))
                ->get();

            $labels = [];
            $sale = [];
            $buy = [];
            $margin = [];

            foreach ($rows as $row) {
                $labels[] = (string) $row->d;
                $sale[] = round((float) $row->sale_total, 2);
                $buy[] = round((float) $row->buy_total, 2);
                $margin[] = round((float) $row->margin_total, 2);
            }

            return [
                'labels' => $labels,
                'series' => [
                    'margin' => $margin,
                    'buy' => $buy,
                    'sale' => $sale,
                ],
                'totals' => [
                    'margin' => round(array_sum($margin), 2),
                    'buy' => round(array_sum($buy), 2),
                    'sale' => round(array_sum($sale), 2),
                ],
            ];
        });

        return response()->json($this->withDashboardMeta($payload, $cacheKey, 120));
    }

    public function topSales(Request $request)
    {
        $this->validateDashboardRange($request);
        $rangeKey = $this->rangeCacheKey($request);
        $cacheKey = $this->dashboardCacheKey('top-sales', ['range' => $rangeKey]);

        $payload = Cache::remember($cacheKey, 120, function () use ($request) {
            $query = $this->scopedOrdersQuery(true);
            $this->applyRange($query, $request);

            $rows = $query
                ->whereNotNull('order_items.tt_operator')
                ->where('order_items.tt_operator', '!=', '')
                ->select([
                    'order_items.tt_operator as operator',
                    DB::raw('COUNT(*) as orders_count'),
                    DB::raw('SUM(COALESCE(orders.order_amount,0)) as total_sales'),
                ])
                ->groupBy('order_items.tt_operator')
                ->orderBy(DB::raw('SUM(COALESCE(orders.order_amount,0))'), 'desc')
                ->take(10)
                ->get()
                ->map(function ($row) {
                    return [
                        'operator' => (string) $row->operator,
                        'orders_count' => (int) $row->orders_count,
                        'total_sales' => round((float) $row->total_sales, 2),
                    ];
                })
                ->values();

            return [
                'top_sales_tt' => $rows,
            ];
        });

        return response()->json($this->withDashboardMeta($payload, $cacheKey, 120));
    }

    protected function scopedOrdersQuery($joinOrderItems = true)
    {
        $user = auth()->user();

        $query = Order::query()
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->join('order_status', 'order_status.id', '=', 'orders.order_status_id')
            ->join('services', 'services.id', '=', 'orders.service_id');

        if ($joinOrderItems) {
            $query->leftJoin('order_items', 'order_items.id', '=', 'orders.order_item_id');
        }

        $this->applyDefaultRecordMethod($query);

        $userIds = $this->scopedRetailerIds($user);
        $groupId = (int) $user->group_id;

        if ($groupId === 2 || $groupId === 3) {
            if (empty($userIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('users.id', $userIds)
                    ->where('orders.is_parent_order', '=', '1');
            }
        } elseif ($groupId === 4) {
            $query->where('users.id', '=', $user->id)
                ->where('orders.is_parent_order', '=', '0');
        } elseif ($groupId === 6) {
            $query->where('orders.service_id', '=', 1)
                ->where('orders.is_parent_order', '=', '0');
        } else {
            $query->where(function ($q) {
                $q->where('users.parent_id', '=', '0')
                    ->orWhereNull('users.parent_id');
            })->where('orders.is_parent_order', '=', '0');
        }

        return $query;
    }

    protected function applyDefaultRecordMethod($query)
    {
        $method = defined('DEFAULT_RECORD_METHOD') ? (int) DEFAULT_RECORD_METHOD : 1;
        switch ($method) {
            case 2:
                $query->whereMonth('orders.date', date('m'));
                break;
            case 3:
                $query->whereBetween('orders.date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 1:
            default:
                break;
        }
    }

    protected function scopedRetailerIds($user)
    {
        if (!$user) {
            return [];
        }

        $child = User::where('id', $user->id)->with('children')->first();
        if (!$child) {
            return [];
        }

        return $child->children->pluck('id')->flatten()->toArray();
    }

    protected function v1StyleTotalResellersCount($user)
    {
        if (!$user) {
            return 0;
        }

        $retailerIds = $this->scopedRetailerIds($user);
        $query = User::query()->select('id');

        if (in_array((int) $user->group_id, [2, 3], true)) {
            if (empty($retailerIds)) {
                return 0;
            }
            $query->whereIn('users.id', $retailerIds);
        } else {
            $query->where(function ($q) {
                $q->where('users.parent_id', '=', '0')
                    ->orWhereNull('users.parent_id');
            });
        }

        return (int) $query->where('status', 1)->count();
    }

    protected function v1StyleTransactionAmounts($user, Carbon $today)
    {
        if (!$user) {
            return [0.0, 0.0];
        }

        $retailerIds = $this->scopedRetailerIds($user);

        $base = Order::query()
            ->join('order_items', 'order_items.id', '=', 'orders.order_item_id')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->join('order_status', 'order_status.id', '=', 'orders.order_status_id')
            ->join('services', 'services.id', '=', 'orders.service_id');

        // Match v1 metric behavior (child users + parent orders) by default.
        if (!empty($retailerIds)) {
            $base->whereIn('users.id', $retailerIds);
        } else {
            $base->whereRaw('1 = 0');
        }
        $base->where('orders.is_parent_order', '=', '1');

        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();

        $todayQuery = (clone $base)->whereBetween('orders.date', [$todayStart, $todayEnd]);
        $monthQuery = (clone $base)
            ->whereYear('orders.date', $today->year)
            ->whereMonth('orders.date', $today->month);

        // v1 special case for retailer group
        if ((int) $user->group_id === 4) {
            $todayQuery = $this->retailerOrdersQuery($user, true)
                ->whereBetween('orders.date', [$todayStart, $todayEnd]);

            $monthQuery = $this->retailerOrdersQuery($user, true)
                ->whereYear('orders.date', $today->year)
                ->whereMonth('orders.date', $today->month);
        }

        return [
            (float) $todayQuery->sum('orders.order_amount'),
            (float) $monthQuery->sum('orders.order_amount'),
        ];
    }

    protected function retailerDashboardSummary($user, Carbon $today)
    {
        if (!$user) {
            return [];
        }

        $currency = $user->currency ?: 'EUR';
        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $orderSummary = $this->retailerOrderPeriodSummary($user, $todayStart, $todayEnd, $monthStart, $monthEnd);

        $creditLimit = $this->safeNumericHelper(function () use ($user) {
            return AppHelper::get_credit_limit($user->id);
        });

        $dailyLimit = $this->safeNumericHelper(function () use ($user) {
            return AppHelper::get_daily_limit($user->id);
        });

        $todayDebit = $this->todayDebitSpend($user, $todayStart, $todayEnd);
        $remainingLimit = $dailyLimit > 0 ? max(0, $dailyLimit - $todayDebit) : 0;
        $statusCounts = $this->retailerTodayStatusCounts($user, $todayStart, $todayEnd);

        return [
            'total_orders_today' => (int) $orderSummary['today_orders'],
            'total_orders' => (int) $orderSummary['today_orders'],
            'today_transaction' => $this->formatEuro($orderSummary['today_amount']),
            'total_transaction' => $this->formatEuro($orderSummary['month_amount']),
            'retailer_balance' => $this->retailerFormattedBalance($user),
            'retailer_credit_limit' => $this->formatUserAmount($currency, $creditLimit),
            'retailer_daily_limit' => $dailyLimit > 0 ? $this->formatUserAmount($currency, $dailyLimit) : 'Not set',
            'retailer_limit_used' => $dailyLimit > 0 ? $this->formatUserAmount($currency, $todayDebit) : $this->formatUserAmount($currency, 0),
            'retailer_remaining_limit' => $dailyLimit > 0 ? $this->formatUserAmount($currency, $remainingLimit) : 'Not set',
            'retailer_today_success' => $statusCounts['success'],
            'retailer_today_pending' => $statusCounts['pending'],
            'retailer_today_failed' => $statusCounts['failed'],
            'retailer_pending_orders' => $this->retailerPendingOrdersCount($user),
            'retailer_today_orders' => (int) $orderSummary['today_orders'],
            'retailer_month_orders' => (int) $orderSummary['month_orders'],
            'retailer_last_order' => $this->retailerLastOrder($user),
        ];
    }

    protected function retailerOrdersQuery($user, $joinOrderItems = true)
    {
        $query = Order::query()
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->join('order_status', 'order_status.id', '=', 'orders.order_status_id')
            ->join('services', 'services.id', '=', 'orders.service_id')
            ->where('users.id', '=', $user->id)
            ->where('orders.is_parent_order', '=', '0');

        if ($joinOrderItems) {
            $query->leftJoin('order_items', 'order_items.id', '=', 'orders.order_item_id');
        }

        return $query;
    }

    protected function retailerOrderPeriodSummary($user, Carbon $todayStart, Carbon $todayEnd, Carbon $monthStart, Carbon $monthEnd)
    {
        $empty = [
            'today_orders' => 0,
            'today_amount' => 0.0,
            'month_orders' => 0,
            'month_amount' => 0.0,
        ];

        try {
            $row = Order::query()
                ->where('orders.user_id', '=', $user->id)
                ->where('orders.is_parent_order', '=', '0')
                ->whereBetween('orders.date', [$monthStart, $monthEnd])
                ->selectRaw('COUNT(*) as month_orders')
                ->selectRaw('COALESCE(SUM(orders.order_amount), 0) as month_amount')
                ->selectRaw(
                    'COALESCE(SUM(CASE WHEN orders.date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as today_orders',
                    [$todayStart->toDateTimeString(), $todayEnd->toDateTimeString()]
                )
                ->selectRaw(
                    'COALESCE(SUM(CASE WHEN orders.date BETWEEN ? AND ? THEN orders.order_amount ELSE 0 END), 0) as today_amount',
                    [$todayStart->toDateTimeString(), $todayEnd->toDateTimeString()]
                )
                ->first();
        } catch (\Throwable $e) {
            return $empty;
        }

        if (!$row) {
            return $empty;
        }

        return [
            'today_orders' => (int) $row->today_orders,
            'today_amount' => (float) $row->today_amount,
            'month_orders' => (int) $row->month_orders,
            'month_amount' => (float) $row->month_amount,
        ];
    }

    protected function retailerTodayStatusCounts($user, Carbon $from, Carbon $to)
    {
        $counts = [
            'success' => 0,
            'pending' => 0,
            'failed' => 0,
        ];

        $rows = Order::query()
            ->join('order_status', 'order_status.id', '=', 'orders.order_status_id')
            ->where('orders.user_id', '=', $user->id)
            ->where('orders.is_parent_order', '=', '0')
            ->whereBetween('orders.date', [$from, $to])
            ->select([
                'order_status.name as status_name',
                DB::raw('COUNT(*) as total_count'),
            ])
            ->groupBy('order_status.id', 'order_status.name')
            ->get();

        foreach ($rows as $row) {
            $name = strtolower((string) $row->status_name);
            $count = (int) $row->total_count;

            if ($this->isSuccessStatus($name)) {
                $counts['success'] += $count;
            } elseif ($this->isFailedStatus($name)) {
                $counts['failed'] += $count;
            } else {
                $counts['pending'] += $count;
            }
        }

        return $counts;
    }

    protected function retailerPendingOrdersCount($user)
    {
        try {
            $rows = $this->retailerOrdersQuery($user, false)
                ->select([
                    'order_status.name as status_name',
                    DB::raw('COUNT(*) as total_count'),
                ])
                ->groupBy('order_status.id', 'order_status.name')
                ->get();
        } catch (\Throwable $e) {
            return 0;
        }

        $pending = 0;

        foreach ($rows as $row) {
            $name = strtolower((string) $row->status_name);

            if ($this->isSuccessStatus($name) || $this->isFailedStatus($name)) {
                continue;
            }

            $pending += (int) $row->total_count;
        }

        return $pending;
    }

    protected function retailerLastOrder($user)
    {
        $row = $this->retailerOrdersQuery($user, true)
            ->select([
                'orders.id',
                'orders.date',
                'orders.order_amount',
                'services.name as service_name',
                'order_status.name as status',
                'order_items.tt_operator',
            ])
            ->orderBy('orders.id', 'desc')
            ->first();

        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row->id,
            'date' => $this->safeDate($row->date),
            'service_name' => (string) $row->service_name,
            'status' => (string) $row->status,
            'product_name' => $row->tt_operator ? (string) $row->tt_operator : (string) $row->service_name,
            'order_amount' => $this->formatEuro($row->order_amount),
        ];
    }

    protected function todayDebitSpend($user, Carbon $from, Carbon $to)
    {
        try {
            return (float) DB::table('transactions')
                ->where('user_id', $user->id)
                ->where('type', 'debit')
                ->whereBetween('created_at', [$from, $to])
                ->sum('amount');
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    protected function retailerFormattedBalance($user)
    {
        try {
            return AppHelper::getBalance($user->id, $user->currency ?: 'EUR', true);
        } catch (\Throwable $e) {
            return $this->formatUserAmount($user->currency ?: 'EUR', 0);
        }
    }

    protected function safeNumericHelper(\Closure $callback)
    {
        try {
            $value = $callback();
        } catch (\Throwable $e) {
            return 0.0;
        }

        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) $value;
    }

    protected function formatUserAmount($currency, $amount)
    {
        try {
            return AppHelper::formatAmount($currency ?: 'EUR', (float) $amount);
        } catch (\Throwable $e) {
            return ($currency ?: 'EUR') . ' ' . number_format((float) $amount, 2, '.', ',');
        }
    }

    protected function retailerQuickActions()
    {
        return [
            [
                'key' => 'topup',
                'label' => 'Topup',
                'detail' => 'Mobile topup',
                'url' => url('tama-topup-v2'),
                'icon' => 'fa fa-mobile-alt',
                'tone' => 'amber',
            ],
            [
                'label' => 'Calling Cards',
                'detail' => 'Bimedia cards',
                'url' => url('calling-cards-v2'),
                'icon' => 'fa fa-credit-card',
                'tone' => 'blue',
            ],
            [
                'label' => 'Bus Tickets',
                'detail' => 'FlixBus and BlaBlaBus',
                'url' => url('bus'),
                'icon' => 'fa fa-bus',
                'tone' => 'green',
            ],
        ];
    }

    protected function dashboardTransactionAmounts($user, Carbon $today)
    {
        if ((int) optional($user)->group_id !== 1) {
            return $this->v1StyleTransactionAmounts($user, $today);
        }

        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();

        $todayQuery = $this->scopedOrdersQuery(false)
            ->whereBetween('orders.date', [$todayStart, $todayEnd]);

        $monthQuery = $this->scopedOrdersQuery(false)
            ->whereYear('orders.date', $today->year)
            ->whereMonth('orders.date', $today->month);

        return [
            (float) $todayQuery->sum('orders.order_amount'),
            (float) $monthQuery->sum('orders.order_amount'),
        ];
    }

    protected function group6OrdersInProgressCount($user)
    {
        if ((int) optional($user)->group_id !== 6) {
            return 0;
        }

        return (int) Order::whereIn('order_status_id', [1, 2, 3, 4])->count();
    }

    protected function group6ClosedOrdersCount($user)
    {
        if ((int) optional($user)->group_id !== 6) {
            return 0;
        }

        return (int) Order::whereIn('order_status_id', [5])->count();
    }

    protected function applyRange($query, Request $request)
    {
        list($from, $to) = $this->resolveRange($request);
        $query->whereBetween('orders.date', [$from, $to]);
    }

    protected function resolveRange(Request $request)
    {
        $today = Carbon::today();
        $range = strtolower((string) $request->get('range', 'today'));

        $fromInput = $this->parseDateInput($request->get('from'));
        $toInput = $this->parseDateInput($request->get('to'));

        if ($fromInput || $toInput) {
            $from = $fromInput ?: $toInput;
            $to = $toInput ?: $fromInput;

            if ($from->gt($to)) {
                $tmp = $from;
                $from = $to;
                $to = $tmp;
            }

            return [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
        }

        switch ($range) {
            case 'last_week':
                $from = $today->copy()->subWeek()->startOfWeek();
                $to = $today->copy()->subWeek()->endOfWeek();
                break;
            case 'last_month':
                $from = $today->copy()->subMonth()->startOfMonth();
                $to = $today->copy()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $from = $today->copy()->subMonths(2)->startOfMonth();
                $to = $today->copy()->endOfDay();
                break;
            case 'last_6_months':
                $from = $today->copy()->subMonths(5)->startOfMonth();
                $to = $today->copy()->endOfDay();
                break;
            case 'today':
            default:
                $from = $today->copy()->startOfDay();
                $to = $today->copy()->endOfDay();
                break;
        }

        return [$from, $to];
    }

    protected function parseDateInput($value)
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function bannerPayload($user)
    {
        if (!$user) {
            return [];
        }

        $query = Banner::query()->whereDate('to_date', '>=', date('Y-m-d'));

        if (in_array((int) $user->group_id, [1, 2], true)) {
            $query->where('user_id', $user->id);
        } else {
            $parent = User::where('id', $user->parent_id)->first();
            $ids = array_filter([
                $parent ? $parent->parent_id : null,
                $user->parent_id,
            ]);
            if (empty($ids)) {
                return [];
            }
            $query->whereIn('user_id', $ids);
        }

        return $query->get()->map(function ($banner) {
            return [
                'id' => (int) $banner->id,
                'banner' => isset($banner->banner) ? (string) $banner->banner : null,
                'title' => isset($banner->title) ? (string) $banner->title : '',
            ];
        })->values()->all();
    }

    protected function rootDashboardMetrics()
    {
        $activeServices = $this->safeTableCount('services', ['status' => 1]);
        $disabledServices = $this->safeTableCount('services', ['status' => 0]);
        $rootMenuItems = $this->safeTableCount('menus', [
            'group_id' => 1,
            'position' => 'sidebar',
            'status' => 1,
        ]);

        return [
            'root_admins' => (int) User::where('group_id', 1)->where('status', 1)->count(),
            'active_users' => (int) User::where('group_id', '!=', 1)->where('status', 1)->count(),
            'total_users' => (int) User::where('group_id', '!=', 1)->count(),
            'inactive_users' => (int) User::where('group_id', '!=', 1)->where('status', '!=', 1)->count(),
            'user_groups' => $this->safeTableCount('user_groups'),
            'menu_items' => $rootMenuItems,
            'menu_issues' => $this->rootMenuIssueCount(),
            'active_services' => $activeServices,
            'disabled_services' => $disabledServices,
            'telecom_countries' => $this->safeTableCount('telecom_countries'),
            'telecom_providers' => $this->safeTableCount('telecom_providers'),
        ];
    }

    protected function rootAttentionItems()
    {
        $items = [
            [
                'label' => 'Inactive Users',
                'value' => (int) User::where('group_id', '!=', 1)->where('status', '!=', 1)->count(),
                'url' => url('users'),
                'icon' => 'fa fa-user-times',
            ],
            [
                'label' => 'Disabled Services',
                'value' => $this->safeTableCount('services', ['status' => 0]),
                'url' => url('services'),
                'icon' => 'fa fa-ban',
            ],
            [
                'label' => 'Open Tickets',
                'value' => $this->rootOpenTicketsCount(),
                'url' => url('tickets/manage'),
                'icon' => 'fa fa-ticket',
            ],
            [
                'label' => 'Warnings Today',
                'value' => $this->rootWarningsTodayCount(),
                'url' => url('activities'),
                'icon' => 'fa fa-info-circle',
            ],
            [
                'label' => 'Menu Issues',
                'value' => $this->rootMenuIssueCount(),
                'url' => url('menus?template=1'),
                'icon' => 'fa fa-sitemap',
            ],
            [
                'label' => 'Empty Providers',
                'value' => $this->safeTableCount('telecom_providers') > 0 ? 0 : 1,
                'url' => url('myservice'),
                'icon' => 'fa fa-plug',
            ],
        ];

        return array_map(function ($item) {
            $item['level'] = ((int) $item['value']) > 0 ? 'warning' : 'ok';
            return $item;
        }, $items);
    }

    protected function rootSystemHealth()
    {
        return [
            $this->healthCheck('Database', 'fa fa-database', function () {
                DB::select('select 1');
                return 'Connected';
            }),
            $this->healthCheck('Cache', 'fa fa-bolt', function () {
                cache()->put('root_dashboard_health', 'ok', 1);
                return cache()->get('root_dashboard_health') === 'ok' ? 'Writable' : 'Read failed';
            }),
            $this->healthCheck('Redis', 'fa fa-server', function () {
                $connection = config('cache.default') === 'redis' || config('queue.default') === 'redis';
                if (!$connection) {
                    return 'Not primary';
                }

                cache()->put('root_dashboard_redis_health', 'ok', 1);
                return 'Connected';
            }),
            [
                'label' => 'Queue',
                'icon' => 'fa fa-random',
                'value' => strtoupper((string) config('queue.default', 'sync')),
                'level' => config('queue.default') === 'sync' ? 'warning' : 'ok',
                'detail' => config('queue.default') === 'sync' ? 'Sync mode' : 'Worker mode',
            ],
        ];
    }

    protected function rootOperationalSnapshot()
    {
        $rootAdmins = (int) User::where('group_id', 1)->where('status', 1)->count();
        $activeUsers = (int) User::where('group_id', '!=', 1)->where('status', 1)->count();
        $inactiveUsers = (int) User::where('group_id', '!=', 1)->where('status', '!=', 1)->count();
        $userGroups = $this->safeTableCount('user_groups');
        $activeServices = $this->safeTableCount('services', ['status' => 1]);
        $disabledServices = $this->safeTableCount('services', ['status' => 0]);
        $providers = $this->safeTableCount('telecom_providers');
        $countries = $this->safeTableCount('telecom_countries');
        $warningsToday = $this->rootWarningsTodayCount();
        $menuIssues = $this->rootMenuIssueCount();
        $activeRootMenus = $this->safeTableCount('menus', ['group_id' => 1, 'position' => 'sidebar', 'status' => 1]);

        return [
            [
                'label' => 'Root Admins',
                'value' => $rootAdmins . ' active',
                'detail' => 'group 1 control users',
                'icon' => 'fa fa-shield',
                'level' => $rootAdmins > 0 ? 'ok' : 'warning',
            ],
            [
                'label' => 'User Groups',
                'value' => $userGroups . ' groups',
                'detail' => 'permission structure',
                'icon' => 'fa fa-users',
                'level' => $userGroups > 0 ? 'ok' : 'warning',
            ],
            [
                'label' => 'User Base',
                'value' => $activeUsers . ' active',
                'detail' => $inactiveUsers . ' inactive users',
                'icon' => 'fa fa-users',
                'level' => $inactiveUsers > 0 ? 'warning' : 'ok',
            ],
            [
                'label' => 'Service Catalog',
                'value' => $activeServices . ' active',
                'detail' => $disabledServices . ' disabled services',
                'icon' => 'fa fa-gift',
                'level' => $disabledServices > 0 ? 'warning' : 'ok',
            ],
            [
                'label' => 'Telecom Config',
                'value' => $providers . ' providers',
                'detail' => $countries . ' countries configured',
                'icon' => 'fa fa-server',
                'level' => $providers > 0 && $countries > 0 ? 'ok' : 'warning',
            ],
            [
                'label' => 'Audit Monitor',
                'value' => $warningsToday . ' warnings',
                'detail' => 'logged today',
                'icon' => 'fa fa-info-circle',
                'level' => $warningsToday > 0 ? 'warning' : 'ok',
            ],
            [
                'label' => 'Menu Integrity',
                'value' => $menuIssues . ' issues',
                'detail' => $activeRootMenus . ' active Root items',
                'icon' => 'fa fa-sitemap',
                'level' => $menuIssues > 0 ? 'warning' : 'ok',
            ],
        ];
    }

    protected function rootRecentActivity()
    {
        try {
            return DB::table('logs')
                ->leftJoin('users', 'users.id', '=', 'logs.user_id')
                ->select([
                    'logs.type',
                    'logs.title',
                    'logs.uri',
                    'logs.created_at',
                    'users.username',
                ])
                ->orderBy('logs.id', 'desc')
                ->take(8)
                ->get()
                ->map(function ($row) {
                    return [
                        'type' => (string) $row->type,
                        'title' => (string) $row->title,
                        'user' => $row->username ? (string) $row->username : 'System',
                        'uri' => (string) $row->uri,
                        'time' => $this->safeDate($row->created_at),
                        'level' => in_array(strtolower((string) $row->type), ['warning', 'danger', 'error'], true) ? 'warning' : 'ok',
                    ];
                })
                ->values()
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function healthCheck($label, $icon, \Closure $callback)
    {
        try {
            $detail = (string) $callback();

            return [
                'label' => $label,
                'icon' => $icon,
                'value' => 'Online',
                'level' => 'ok',
                'detail' => $detail,
            ];
        } catch (\Throwable $e) {
            return [
                'label' => $label,
                'icon' => $icon,
                'value' => 'Issue',
                'level' => 'danger',
                'detail' => $e->getMessage(),
            ];
        }
    }

    protected function rootFailedOrdersTodayCount()
    {
        try {
            $today = Carbon::today();

            return (int) $this->scopedOrdersQuery(true)
                ->whereBetween('orders.date', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
                ->where(function ($q) {
                    $q->whereRaw('LOWER(order_status.name) LIKE ?', ['%fail%'])
                        ->orWhereRaw('LOWER(order_status.name) LIKE ?', ['%cancel%'])
                        ->orWhereRaw('LOWER(order_status.name) LIKE ?', ['%reject%'])
                        ->orWhereRaw('LOWER(order_status.name) LIKE ?', ['%error%'])
                        ->orWhereRaw('LOWER(order_status.name) LIKE ?', ['%declined%']);
                })
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function rootOpenTicketsCount()
    {
        try {
            return (int) DB::table('tickets')
                ->where(function ($q) {
                    $q->where('status', '!=', '1')->orWhereNull('status');
                })
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function rootWarningsTodayCount()
    {
        try {
            return (int) DB::table('logs')
                ->whereDate('created_at', Carbon::today()->toDateString())
                ->where(function ($q) {
                    $q->where('type', 'warning')
                        ->orWhere('type', 'danger')
                        ->orWhere('type', 'error');
                })
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function rootMenuIssueCount()
    {
        try {
            $inactive = (int) DB::table('menus')
                ->where('group_id', 1)
                ->where('position', 'sidebar')
                ->where('status', '!=', 1)
                ->count();

            $emptyParents = (int) DB::table('menus as parents')
                ->leftJoin('menus as children', function ($join) {
                    $join->on('children.parent_id', '=', 'parents.id')
                        ->where('children.status', '=', 1);
                })
                ->where('parents.group_id', 1)
                ->where('parents.position', 'sidebar')
                ->where('parents.status', 1)
                ->where('parents.parent_id', 0)
                ->whereNotIn('parents.url', ['dashboard', 'activities', 'redis-manager'])
                ->whereNull('children.id')
                ->count();

            return $inactive + $emptyParents;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function safeTableCount($table, array $where = [])
    {
        try {
            $query = DB::table($table);
            foreach ($where as $column => $value) {
                $query->where($column, $value);
            }

            return (int) $query->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function validateDashboardRange(Request $request)
    {
        $request->validate([
            'range' => 'sometimes|in:today,last_week,last_month,last_3_months,last_6_months,custom',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = $request->get('from');
        $to = $request->get('to');
        if ($from && $to && Carbon::parse($from)->gt(Carbon::parse($to))) {
            throw ValidationException::withMessages([
                'to' => ['The to date must be after or equal to from date.'],
            ]);
        }
    }

    protected function rangeCacheKey(Request $request)
    {
        return implode(':', [
            strtolower((string) $request->get('range', 'today')),
            (string) $request->get('from', ''),
            (string) $request->get('to', ''),
        ]);
    }

    protected function dashboardCacheKey($segment, array $parts = [])
    {
        $user = auth()->user();
        $base = [
            'dashboard_v2',
            (string) $segment,
            'user_' . (int) optional($user)->id,
            'group_' . (int) optional($user)->group_id,
        ];

        foreach ($parts as $key => $value) {
            $base[] = $key . '_' . (is_scalar($value) ? (string) $value : md5(json_encode($value)));
        }

        return preg_replace('/[^A-Za-z0-9:_\-.]/', '_', implode(':', $base));
    }

    protected function withDashboardMeta(array $payload, $cacheKey, $ttl)
    {
        $payload['_meta'] = [
            'cache_key' => $cacheKey,
            'ttl' => (int) $ttl,
            'signature' => md5(json_encode($payload)),
            'served_at' => Carbon::now()->toIso8601String(),
        ];

        return $payload;
    }

    protected function formatEuro($amount)
    {
        $value = (float) $amount;
        return number_format($value, 2, '.', ',');
    }

    protected function safeDate($value)
    {
        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    protected function isSuccessStatus($statusName)
    {
        return strpos($statusName, 'success') !== false
            || strpos($statusName, 'completed') !== false
            || strpos($statusName, 'complete') !== false
            || strpos($statusName, 'closed') !== false;
    }

    protected function isFailedStatus($statusName)
    {
        return strpos($statusName, 'fail') !== false
            || strpos($statusName, 'cancel') !== false
            || strpos($statusName, 'reject') !== false
            || strpos($statusName, 'error') !== false;
    }
}
