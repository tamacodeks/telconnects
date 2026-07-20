<?php

namespace App\Http\Controllers\V2;

use app\Library\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PinHistory;
use App\Models\Product;
use App\Models\Service;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderHistoryController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        list($fromDate, $toDate, $activeRange) = $this->dateRangeFromRequest($request);

        return view('v2.app.orders.index', [
            'page_title' => trans('v2_history.orders.page_title'),
            'services' => Service::select('id', 'name')->orderBy('name')->get(),
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'active_range' => $activeRange,
        ]);
    }

    public function data(Request $request)
    {
        $serviceFilters = $this->normalizeHistoryServiceFilters($request);
        $query = $this->ordersQuery();

        $this->applyDateFilter($query, $request);

        return DataTables::of($query)
            ->addColumn('product_name', function ($order) {
                if ($order->service_id == 1) {
                    return optional(Product::find($order->product_id))->name;
                }

                if ($order->service_id == 2 || $order->service_id == 7) {
                    return $order->tt_operator;
                }

                if ($order->service_id == 5) {
                    return $order->service_name . ' ' . AppHelper::formatAmount($order->app_currency, $order->app_amount_topup);
                }

                $isoCode = optional(User::find($order->user_id))->currency;
                $price = $order->public_price == '0.00' ? $order->grand_total : $order->public_price;

                return $order->service_name . ' ' . AppHelper::formatAmount($isoCode, $price);
            })
            ->addColumn('order_amount', function ($order) {
                return $order->public_price;
            })
            ->addColumn('pin', function ($order) {
                if ($order->service_id == 7) {
                    $pinHistory = PinHistory::where('used_by', $order->user_id)->where('date', $order->date)->first();
                    return optional($pinHistory)->pin;
                }

                return $order->tama_pin;
            })
            ->addColumn('serial', function ($order) {
                if ($order->service_id == 7) {
                    $pinHistory = PinHistory::where('used_by', $order->user_id)->where('date', $order->date)->first();
                    return optional($pinHistory)->serial;
                }

                return $order->tama_serial;
            })
            ->addColumn('mobile', function ($order) {
                if ($order->service_id == 1) {
                    return $order->receiver_mobile;
                }

                if ($order->service_id == 2) {
                    return $order->tt_mobile;
                }

                return $order->app_mobile;
            })
            ->addColumn('print_receipt', function ($order) {
                if ($order->service_id == 2) {
                    return "<a target='_blank' href='" . secure_url('tama-topup/print/receipt/' . $order->id) . "' class='btn btn-primary btn-xs'><i class='fa fa-print'></i>&nbsp;" . trans('common.btn_print') . '</a>';
                }

                return '';
            })
            ->filter(function ($query) use ($request, $serviceFilters) {
                $this->applySearchFilter($query, $request);
                $this->applyServiceFilter($query, $serviceFilters);
            })
            ->rawColumns(['print_receipt'])
            ->make(true);
    }

    private function ordersQuery()
    {
        $query = Order::join('users', 'users.id', 'orders.user_id')
            ->join('order_status', 'order_status.id', 'orders.order_status_id')
            ->join('services', 'services.id', 'orders.service_id')
            ->select([
                'orders.date',
                'orders.id',
                'orders.user_id',
                'orders.is_parent_order',
                'orders.sur_charge',
                'orders.order_item_id',
                'users.username',
                'services.id as service_id',
                'services.name as service_name',
                'orders.public_price',
                'orders.grand_total',
                'order_items.product_id',
                'order_items.sender_first_name',
                'order_items.sender_mobile',
                'order_items.receiver_first_name',
                'order_items.receiver_mobile',
                'order_items.tt_mobile',
                'order_items.tt_operator',
                'order_items.app_mobile',
                'order_items.app_amount_topup',
                'order_items.app_currency',
                'order_items.tama_pin',
                'order_items.tama_serial',
                'order_status.name as order_status_name',
            ]);

        return $this->applyUserScope($query);
    }

    private function applyUserScope($query)
    {
        $groupId = (int) auth()->user()->group_id;

        if ($groupId === 2 || $groupId === 3) {
            $query->join('order_items', 'order_items.id', 'orders.order_item_id');
            $child = User::where('id', auth()->user()->id)->with('children')->first();
            $retailers = $child ? $child->children->pluck('id')->flatten()->toArray() : [];

            return $query->whereIn('users.id', $retailers)
                ->where('orders.is_parent_order', '=', '1');
        }

        $query->join('order_items', 'order_items.order_id', 'orders.id');

        return $query->where('users.id', auth()->user()->id)
            ->where('orders.is_parent_order', '=', '0');
    }

    private function dateRangeFromRequest(Request $request)
    {
        $fromDate = $request->input('from_date', $request->input('from'));
        $toDate = $request->input('to_date', $request->input('to'));

        if (!empty($fromDate) || !empty($toDate)) {
            return [
                $fromDate ?: '',
                $toDate ?: Carbon::now()->format('Y-m-d'),
                '',
            ];
        }

        if ($request->input('date') === 'today') {
            $today = Carbon::now()->format('Y-m-d');
            return [$today, $today, 'today'];
        }

        if ($request->input('range') === 'this-month') {
            $today = Carbon::now();

            return [
                $today->copy()->startOfMonth()->format('Y-m-d'),
                $today->format('Y-m-d'),
                'month',
            ];
        }

        return ['', '', 'all'];
    }

    private function applyDateFilter($query, Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if (empty($fromDate) && empty($toDate)) {
            return;
        }

        $fromDate = ($fromDate ?: '1970-01-01') . ' 00:00:00';
        $toDate = ($toDate ?: Carbon::now()->format('Y-m-d')) . ' 23:59:59';

        $query->whereBetween('orders.date', [$fromDate, $toDate]);
    }

    private function applySearchFilter($query, Request $request)
    {
        if (empty($request->input('query'))) {
            return;
        }

        $search = $request->input('query');

        $query->where(function ($q) use ($search) {
            $q->where('order_items.sender_first_name', 'like', "%{$search}%");
            $q->orWhere('order_items.receiver_first_name', 'like', "%{$search}%");
            $q->orWhere('order_items.sender_mobile', 'like', "%{$search}%");
            $q->orWhere('order_items.receiver_mobile', 'like', "%{$search}%");
            $q->orWhere('order_items.tt_mobile', 'like', "%{$search}%");
            $q->orWhere('order_items.app_mobile', 'like', "%{$search}%");
            $q->orWhere('orders.txn_ref', 'like', "%{$search}%");
            $q->orWhere('users.username', 'like', "%{$search}%");
        });
    }

    private function applyServiceFilter($query, array $serviceFilters)
    {
        if (empty($serviceFilters['services']) && empty($serviceFilters['operators'])) {
            return;
        }

        $query->where(function ($q) use ($serviceFilters) {
            foreach ($serviceFilters['operators'] as $operator) {
                $q->orWhere('order_items.tt_operator', $operator);
            }

            if (!empty($serviceFilters['services'])) {
                $q->orWhereIn('services.id', $serviceFilters['services']);
            }
        });
    }

    private function normalizeHistoryServiceFilters(Request $request)
    {
        $filters = [
            'services' => [],
            'operators' => [],
        ];

        foreach ((array) $request->input('service_id', []) as $value) {
            $value = trim((string) $value);

            if ($value === '') {
                continue;
            }

            if ($value === '111') {
                $filters['operators'][] = 'blabla';
                continue;
            }

            if ($value === '112') {
                $filters['operators'][] = 'flixbus';
                continue;
            }

            if (strpos($value, 'operator:') === 0) {
                $operator = strtolower(trim(substr($value, strlen('operator:'))));

                if (in_array($operator, ['blabla', 'flixbus'], true)) {
                    $filters['operators'][] = $operator;
                }

                continue;
            }

            if (strpos($value, 'service:') === 0) {
                $value = substr($value, strlen('service:'));
            }

            if (ctype_digit($value)) {
                $filters['services'][] = $value;
            }
        }

        $filters['services'] = array_values(array_unique($filters['services']));
        $filters['operators'] = array_values(array_unique($filters['operators']));

        return $filters;
    }
}
