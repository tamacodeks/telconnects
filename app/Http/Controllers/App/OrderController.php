<?php

namespace App\Http\Controllers\App;

use app\Library\AppHelper;
use App\Models\Order;
use App\Models\PinHistory;
use App\Models\Product;
use App\Models\Service;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index(){
        $page_data = [
            'page_title' => "Orders",
            'services' => Service::select('id','name')->get()
        ];
        return view('app.orders.index',$page_data);
    }

    function indexV2(Request $request){
        list($from_date, $to_date, $active_range) = $this->v2HistoryDateRange($request);
        $page_data = [
            'page_title' => trans('v2_history.orders.page_title'),
            'services' => Service::select('id','name')->get(),
            'from_date' => $from_date,
            'to_date' => $to_date,
            'active_range' => $active_range,
        ];

        return view('v2.app.orders.index', $page_data);
    }

    private function v2HistoryDateRange(Request $request)
    {
        $from_date = $request->input('from_date', $request->input('from'));
        $to_date = $request->input('to_date', $request->input('to'));

        if (!empty($from_date) || !empty($to_date)) {
            return [
                $from_date ?: Carbon::now()->subMonths(3)->format('Y-m-d'),
                $to_date ?: Carbon::now()->format('Y-m-d'),
                $request->input('range', ''),
            ];
        }

        if ($request->input('date') === 'today' || $request->input('range') === 'today') {
            $today = Carbon::now()->format('Y-m-d');
            return [$today, $today, 'today'];
        }

        if (in_array($request->input('range'), ['month', 'this-month'], true)) {
            $today = Carbon::now();
            return [
                $today->copy()->startOfMonth()->format('Y-m-d'),
                $today->format('Y-m-d'),
                'month',
            ];
        }

        return ['', '', 'all'];
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

    function getOrders(Request $request){
//        \DB::enableQueryLog();
        $serviceFilters = $this->normalizeHistoryServiceFilters($request);
        $query = Order::join('users','users.id','orders.user_id')
            ->join('order_status','order_status.id','orders.order_status_id')
            ->join('services','services.id','orders.service_id')
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
                'order_status.name as order_status_name'
            ]);
        if(auth()->user()->group_id == 2){
            $query->join('order_items','order_items.id','orders.order_item_id');
            $child = User::where('id',auth()->user()->id)->with('children')->first();
            $retailers = $child->children->pluck('id')->flatten()->toArray();
            $query->whereIn('users.id',$retailers);
            $query->where('orders.is_parent_order', '=', '1');
        }elseif(auth()->user()->group_id == 3){
            $query->join('order_items','order_items.id','orders.order_item_id');
            $child = User::where('id',auth()->user()->id)->with('children')->first();
            $retailers = $child->children->pluck('id')->flatten()->toArray();
            $query->whereIn('users.id',$retailers);
            $query->where('orders.is_parent_order','=',1);
        }elseif(auth()->user()->group_id == 4){
            $query->join('order_items','order_items.order_id','orders.id');
            $query->where('users.id',auth()->user()->id);
            $query->where('orders.is_parent_order', '=', '0');
        }else{
            $query->join('order_items','order_items.order_id','orders.id');
            $query->where('users.id',auth()->user()->id);
            $query->where('orders.is_parent_order', '=', '0');
        }
        if (empty($request->input('from_date')) && empty($request->input('to_date'))) {
            $today_date = date("Y-m-d");
            switch (DEFAULT_RECORD_METHOD){
                case 1:
                    $query->whereBetween('orders.date', [$today_date." 00:00:00",$today_date." 23:59:59"]);
                    break;
                case 2:
                    $query->whereMonth('orders.date',date('m'));
                    break;
                case 3:
                    $query->whereBetween('orders.date', [Carbon::now()->startOfWeek(),Carbon::now()->endOfWeek()]);
                    break;
            }
        }else{
            $from_date = $request->input('from_date').' 00:00:00';
            $to_date = $request->input('to_date').' 23:59:59';
            $query->whereBetween('orders.date',[$from_date,$to_date]);
        }
        $orders = $query;
        return Datatables::of($orders)
            ->addColumn('product_name', function ($orders) {
                if($orders->service_id == 1){
                    return optional(Product::find($orders->product_id))->name;
                }elseif($orders->service_id == 2 || $orders->service_id == 7){
                    return $orders->tt_operator;
                }elseif($orders->service_id == 5){
                    return $orders->service_name.' '.AppHelper::formatAmount($orders->app_currency,$orders->app_amount_topup);
                }
                $iso_code = optional(User::find($orders->user_id))->currency;
                $price = $orders->public_price == "0.00" ? $orders->grand_total : $orders->public_price;
                return $orders->service_name.' '.AppHelper::formatAmount($iso_code,$price);
            })
            ->addColumn('order_amount', function ($orders){
                return $orders->public_price;
            })
            ->addColumn('pin', function ($orders){
                if($orders->service_id == 7){
                    $pin_history = PinHistory::where('used_by',$orders->user_id)->where("date",$orders->date)->first();
                    return optional($pin_history)->pin;
                }else{
                    return $orders->tama_pin;
                }
            })
            ->addColumn('serial', function ($orders){
                if($orders->service_id == 7){
                    $pin_history = PinHistory::where('used_by',$orders->user_id)->where("date",$orders->date)->first();
                    return optional($pin_history)->serial;
                }else{
                    return $orders->tama_serial;
                }
            })
            ->addColumn('mobile', function ($orders){
                if($orders->service_id == 1){
                    return $orders->receiver_mobile;
                }elseif ($orders->service_id == 2){
                    return $orders->tt_mobile;
                }else{
                    return $orders->app_mobile;
                }
            })
            ->addColumn('print_receipt', function ($orders){
                if($orders->service_id == 2) {
                    return "<a target='_blank' href='" . secure_url('tama-topup/print/receipt/' . $orders->id) . "' class='btn btn-primary btn-xs'><i class='fa fa-print'></i>&nbsp;" . trans('common.btn_print') . "</a>";
                }else{
                    return "";
                }
            })
            ->filter(function ($query) use ($request, $serviceFilters) {
                if (!empty($request->input('query'))) {
                    $qry = $request->input('query');
                    $query->Where(function ($q) use ($qry) {
                        $q->Where('order_items.sender_first_name', "like", "%{$qry}%");
                        $q->orWhere('order_items.receiver_first_name', "like", "%{$qry}%");
                        $q->orWhere('order_items.sender_mobile', "like", "%{$qry}%");
                        $q->orWhere('order_items.receiver_mobile', "like", "%{$qry}%");
                        $q->orWhere('order_items.tt_mobile', "like", "%{$qry}%");
                        $q->orWhere('order_items.app_mobile', "like", "%{$qry}%");
                        $q->orWhere('orders.txn_ref', "like", "%{$qry}%");
                        $q->orWhere('users.username', "like", "%{$qry}%");
                    });
                }
                if (!empty($serviceFilters['services']) || !empty($serviceFilters['operators'])) {
                    $query->where(function ($q) use ($serviceFilters) {
                        foreach ($serviceFilters['operators'] as $operator) {
                            $q->orWhere('order_items.tt_operator', $operator);
                        }

                        if (!empty($serviceFilters['services'])) {
                            $q->orWhereIn('services.id', $serviceFilters['services']);
                        }
                    });
                }
                if (!empty($request->input('from_date')) && !empty($request->input('to_date'))) {
                    $from_date = $request->input('from_date').' 00:00:00';
                    $to_date = $request->input('to_date').' 23:59:59';
                    $query->whereBetween('orders.date',[$from_date,$to_date]);
                }
            })
            ->rawColumns(['action','print_receipt'])
            ->make(true);
    }


    function myOrders()
    {
        $page_data = [
            'page_title' => "Orders",
            'services' => Service::select('id','name')->get()
        ];
        return view('app.orders.myorders',$page_data);
    }

    function fetchMyOrders(Request $request)
    {
        $query = Order::join('users','users.id','orders.user_id')
            ->join('order_status','order_status.id','orders.order_status_id')
            ->join('services','services.id','orders.service_id')
            ->join('order_items','order_items.id','orders.order_item_id')
            ->select([
                'orders.date',
                'orders.user_id',
                'orders.is_parent_order',
                'users.id as user_id',
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
                'order_items.tama_pin',
                'order_items.tama_serial',
                'order_status.name as order_status_name'
            ]);
        $query->whereIn('users.id',[auth()->user()->id]);
        $query->where('orders.is_parent_order', '=', '0');
        if (empty($request->input('from_date')) && empty($request->input('to_date'))) {
            $today_date = date("Y-m-d");
            switch (DEFAULT_RECORD_METHOD){
                case 1:
                    $query->whereBetween('orders.date', [$today_date." 00:00:00",$today_date." 23:59:59"]);
                    break;
                case 2:
                    $query->whereMonth('orders.date',date('m'));
                    break;
                case 3:
                    $query->whereBetween('orders.date', [Carbon::now()->startOfWeek(),Carbon::now()->endOfWeek()]);
                    break;
            }
        }else{
            $from_date = $request->input('from_date').' 00:00:00';
            $to_date = $request->input('to_date').' 23:59:59';
            $query->whereBetween('orders.date',[$from_date,$to_date]);
        }
        $orders = $query;
        return Datatables::of($orders)
            ->addColumn('product_name', function ($orders) {
                if($orders->service_id == 1){
                    return optional(Product::find($orders->product_id))->name;
                }elseif($orders->service_id == 7 || $orders->service_id == 2){
                    return $orders->tt_operator;
                }
                $iso_code = optional(User::find($orders->user_id))->currency;
                $price = $orders->public_price == "0.00" ? $orders->grand_total : $orders->public_price;
                return $orders->service_name.' '.AppHelper::formatAmount($iso_code,$price);
            })
            ->addColumn('order_amount', function ($orders){
                return $orders->public_price;
            })
            ->addColumn('mobile', function ($orders){
                if($orders->service_id == 1){
                    return $orders->receiver_mobile;
                }elseif ($orders->service_id == 2){
                    return $orders->tt_mobile;
                }else{
                    return $orders->app_mobile;
                }
            })
            ->filter(function ($query) use ($request) {
                if (!empty($request->input('query'))) {
                    $qry = $request->input('query');
                    $query->Where(function ($q) use ($qry) {
                        $q->Where('order_items.sender_first_name', "like", "%{$qry}%");
                        $q->orWhere('order_items.receiver_first_name', "like", "%{$qry}%");
                        $q->orWhere('order_items.sender_mobile', "like", "%{$qry}%");
                        $q->orWhere('order_items.receiver_mobile', "like", "%{$qry}%");
                        $q->orWhere('order_items.tt_mobile', "like", "%{$qry}%");
                        $q->orWhere('order_items.app_mobile', "like", "%{$qry}%");
                        $q->orWhere('orders.txn_ref', "like", "%{$qry}%");
                        $q->orWhere('users.username', "like", "%{$qry}%");
                    });
                }
                if (!empty($request->input('service_id'))) {
                    $query->whereIn('services.id',$request->input('service_id'));
                }
                if (!empty($request->input('from_date')) && !empty($request->input('to_date'))) {
                    $from_date = $request->input('from_date').' 00:00:00';
                    $to_date = $request->input('to_date').' 23:59:59';
                    $query->whereBetween('orders.date',[$from_date,$to_date]);
                }
            })
            ->make(true);
    }
}
