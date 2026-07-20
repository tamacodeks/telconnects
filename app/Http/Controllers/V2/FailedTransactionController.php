<?php

namespace App\Http\Controllers\V2;

use app\Library\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PinHistory;
use App\Models\Product;
use App\Models\Service;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FailedTransactionController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        list($fromDate, $toDate) = $this->v2HistoryDateRange($request);

        return view('v2.app.failed-transactions.index', [
            'page_title' => trans('v2_history.failed_transactions.page_title'),
            'services' => Service::select('id', 'name')->where('status', '1')->get(),
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);
    }

    public function data(Request $request)
    {
        list($fromDate, $toDate) = $this->normalizeV2HistoryDateRange(
            $request->input('from_date'),
            $request->input('to_date')
        );

        $request->merge([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);

        $query = $this->failedTransactionsQuery($request);

        return DataTables::of($query)
            ->addColumn('product_name', function ($order) {
                return $this->productName($order);
            })
            ->addColumn('public_price', function ($order) {
                return $order->public_price;
            })
            ->addColumn('pin', function ($order) {
                if ((int) $order->service_id === 7) {
                    $pinHistory = PinHistory::where('used_by', $order->user_id)
                        ->where('date', $order->date)
                        ->first();

                    return optional($pinHistory)->pin;
                }

                return $order->tama_pin;
            })
            ->addColumn('serial', function ($order) {
                if ((int) $order->service_id === 7) {
                    $pinHistory = PinHistory::where('used_by', $order->user_id)
                        ->where('date', $order->date)
                        ->first();

                    return optional($pinHistory)->serial;
                }

                return $order->tama_serial;
            })
            ->addColumn('buying_price', function ($order) {
                return $order->buying_price;
            })
            ->addColumn('order_amount', function ($order) {
                return $order->order_amount;
            })
            ->addColumn('sale_margin', function ($order) {
                return $order->sale_margin;
            })
            ->addColumn('mobile', function ($order) {
                if ((int) $order->service_id === 1) {
                    return $order->receiver_mobile;
                }

                if ((int) $order->service_id === 2) {
                    return $order->tt_mobile;
                }

                return $order->app_mobile;
            })
            ->filter(function ($query) use ($request) {
                $this->applySearchFilter($query, $request);
            })
            ->make(true);
    }

    private function failedTransactionsQuery(Request $request)
    {
        $query = Order::join('users', 'users.id', '=', 'orders.user_id')
            ->join('order_status', 'order_status.id', '=', 'orders.order_status_id')
            ->join('services', 'services.id', '=', 'orders.service_id')
            ->select([
                'orders.id',
                'orders.date',
                'users.id as user_id',
                'users.cust_id as cust_id',
                'users.username',
                'services.id as service_id',
                'services.name as service_name',
                'orders.public_price',
                'orders.buying_price',
                'orders.sale_margin',
                'orders.order_amount',
                'orders.is_parent_order',
                'orders.order_item_id',
                'orders.txn_ref as txn_id',
                'orders.grand_total',
                'orders.sur_charge',
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
                'order_items.instructions',
                'order_items.link',
                'order_status.name as order_status_name',
            ]);

        $this->applyUserScope($query);
        $this->applyFailedStatusFilter($query);
        $this->applyDateFilter($query, $request);

        return $query->orderBy('orders.date', 'desc');
    }

    private function applyUserScope($query)
    {
        $groupId = (int) auth()->user()->group_id;

        if (in_array($groupId, [2, 3], true)) {
            $query->join('order_items', 'order_items.id', '=', 'orders.order_item_id');
            $child = User::where('id', auth()->user()->id)->with('children')->first();
            $retailers = $child ? $child->children->pluck('id')->flatten()->toArray() : [];

            if (empty($retailers)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('users.id', $retailers);
            }

            $query->where('orders.is_parent_order', '=', '1');
            return;
        }

        $query->join('order_items', 'order_items.order_id', '=', 'orders.id');
        $query->where('users.id', auth()->user()->id);
        $query->where('orders.is_parent_order', '=', '0');
    }

    private function applyFailedStatusFilter($query)
    {
        $query->where(function ($scope) {
            $scope->whereIn('orders.order_status_id', [8, 9])
                ->orWhereIn('order_status.name', ['Failed', 'Refunded']);
        });
    }

    private function applyDateFilter($query, Request $request)
    {
        $query->whereBetween('orders.date', [
            $request->input('from_date') . ' 00:00:00',
            $request->input('to_date') . ' 23:59:59',
        ]);
    }

    private function applySearchFilter($query, Request $request)
    {
        $search = trim((string) $request->input('query'));

        if ($search === '') {
            return;
        }

        $query->where(function ($scope) use ($search) {
            $scope->where('order_items.sender_first_name', 'like', "%{$search}%")
                ->orWhere('order_items.receiver_first_name', 'like', "%{$search}%")
                ->orWhere('order_items.sender_mobile', 'like', "%{$search}%")
                ->orWhere('order_items.receiver_mobile', 'like', "%{$search}%")
                ->orWhere('order_items.tt_mobile', 'like', "%{$search}%")
                ->orWhere('order_items.tt_operator', 'like', "%{$search}%")
                ->orWhere('order_items.app_mobile', 'like', "%{$search}%")
                ->orWhere('orders.txn_ref', 'like', "%{$search}%")
                ->orWhere('users.username', 'like', "%{$search}%")
                ->orWhere('users.cust_id', 'like', "%{$search}%");
        });
    }

    private function productName($order)
    {
        if ((int) $order->service_id === 1) {
            return optional(Product::find($order->product_id))->name;
        }

        if ((int) $order->service_id === 5) {
            return $order->service_name . ' ' . AppHelper::formatAmount($order->app_currency, $order->app_amount_topup);
        }

        if (in_array((int) $order->service_id, [2, 7], true)) {
            $orderItem = OrderItem::find($order->order_item_id);

            return $order->tt_operator === null ? optional($orderItem)->tt_operator : $order->tt_operator;
        }

        $isoCode = optional(User::find($order->user_id))->currency;
        $price = $order->public_price === '0.00' ? $order->grand_total : $order->public_price;

        return $order->service_name . ' ' . AppHelper::formatAmount($isoCode, $price);
    }

    private function v2HistoryDateRange(Request $request)
    {
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from') || $request->filled('to')) {
            return $this->normalizeV2HistoryDateRange(
                $request->input('from_date', $request->input('from')),
                $request->input('to_date', $request->input('to'))
            );
        }

        $today = Carbon::now()->format('Y-m-d');

        return [$today, $today];
    }

    private function normalizeV2HistoryDateRange($fromDate = null, $toDate = null)
    {
        $today = Carbon::now()->startOfDay();
        $minDate = $today->copy()->subMonths(3);
        $from = $this->parseV2HistoryDate($fromDate) ?: $today->copy();
        $to = $this->parseV2HistoryDate($toDate) ?: $today->copy();

        if ($from->lt($minDate)) {
            $from = $minDate->copy();
        }

        if ($to->lt($minDate)) {
            $to = $minDate->copy();
        }

        if ($from->gt($today)) {
            $from = $today->copy();
        }

        if ($to->gt($today)) {
            $to = $today->copy();
        }

        if ($to->lt($from)) {
            $to = $from->copy();
        }

        return [$from->format('Y-m-d'), $to->format('Y-m-d')];
    }

    private function parseV2HistoryDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', (string) $value)->startOfDay();

            return $date->format('Y-m-d') === (string) $value ? $date : null;
        } catch (\Exception $exception) {
            return null;
        }
    }
}
