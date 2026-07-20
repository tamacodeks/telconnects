<?php

namespace App\Http\Controllers\Service;

use App\Events\TamaTopupOrder;
use app\Library\AppHelper;
use app\Library\SecurityHelper;
use app\Library\ServiceHelper;
use App\Models\AppCommission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TrackOrder;
use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Validator;
use Response;
use App\Models\Country;

class TamaTopupController extends Controller
{
    private $service_id;
    private $decipher;
    private $client;
    function __construct()
    {
        parent::__construct();
        $this->service_id = 2;
        $this->decipher = new SecurityHelper();
        $this->middleware(function ($request, $next) {
            if(API_TOKEN == '' || API_END_POINT == ''){
                AppHelper::logger('warning','API SETTINGS ERROR',"Missing API Token or API end point url",request()->all(),true);
                return redirect()->back()
                    ->with('message',trans('common.access_violation'))
                    ->with('message_type','warning');
            }
            if(AppHelper::user_access($this->service_id,auth()->user()->id) == 0){
                AppHelper::logger('warning','Access Violation',auth()->user()->username. " trying to access tamatopup service",request()->all(),true);
                return redirect()->back()
                    ->with('message',trans('common.access_violation'))
                    ->with('message_type','warning');
            }
            //lets check with this user parent has access
            if(\app\Library\AppHelper::skip_service_as_menu('tama-topup') == false){
                AppHelper::logger('warning', 'Access Violation', auth()->user()->username . " trying to access TamaTopup service but parent of this user does not have a access", request()->all());
                return redirect('dashboard')
                    ->with('message', trans('common.access_violation'))
                    ->with('message_type', 'warning');
            }
            $this->client = new Client([
                'base_uri' => API_END_POINT,
                'timeout'  => 120,
            ]);
            return $next($request);
        });
    }

    /**
     * View TamaTopup Index
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function index()
    {
        $this->data = [
            'page_title' => "TamaTopup",
            'select_flag' => ""
        ];
        return view('service.tama-topup.index',$this->data);
    }
    function franceindex()
    {
        $this->data = [
            'page_title' => "TamaTopupFrance",
            'select_flag' => "france"
        ];
        return view('service.tama-topup.index', $this->data);
    }

    /**
     * POST get denominations
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    function ding_plans(Request $request){
//        dd($request->all());
        $to_replace = "(+".$request->countryCode.")";
        $mobileNumber = str_replace("+", "", $request->input('accountNumber'));
//        dd($mobileNumber);
        $this->data = [
            'page_title' => ucfirst(trans('view'))." ".trans('service.plans')." ".$request->accountNumber,
            'mobile_number' => $mobileNumber,
            'countryCode' => $request->countryCode,
            'countryIso' => $request->countryCode
        ];
        return view('service.tama-topup.ding.index',$this->data);
    }
    function transfer_plans(Request $request){
        $to_replace = "(+".$request->countryCode.")";
        $mobileNumber = str_replace("+", "", $request->input('accountNumber'));
        $this->data = [
            'page_title' => ucfirst(trans('view'))." ".trans('service.plans')." ".$request->mobile,
            'mobile_number' => $mobileNumber,
            'countryCode' => $request->countryCode,
            'countryIso' => $request->countryCode
        ];
        return view('service.tama-topup.plans',$this->data);
    }
private function checkTopupLimitAndGap($mobileNumber, $amount)
{
    $userId = auth()->id();
    $now    = now();
    $start  = $now->copy()->startOfDay();
    $end    = $now->copy()->endOfDay();

    // ---- Per-number daily € cap (unchanged) ----
    $todayTotal = Orderitem::where('tt_mobile', $mobileNumber)
        ->where('created_by', $userId)
        ->whereBetween('created_at', [$start, $end])
        ->sum('tt_euro_amount'); // replace with your actual amount column if different

    if (($todayTotal + $amount) > 20) {
        Log::warning("Daily limit exceeded: user $userId, mobile $mobileNumber");
		return trans('common.access_violation');
    }

    // Helper to detect +92 even on older PHP without str_starts_with
    $isPakistan = function($msisdn) {
        if (function_exists('str_starts_with')) {
            return str_starts_with($msisdn, '92');
        }
        return substr($msisdn, 0, 2) === '92';
    };

    // ---- Global 30-minute gap for ANY Pakistan number (user-level) ----
    if ($isPakistan($mobileNumber)) {
        $lastPk = Orderitem::where('created_by', $userId)
            ->where('tt_mobile', 'like', '92%') // any PK MSISDN
            ->latest('created_at')
            ->first();

        if ($lastPk) {
            $mins = $now->diffInMinutes($lastPk->created_at);
            if ($mins < 30) {
                Log::warning("Pakistan global gap: user $userId must wait ".(30 - $mins)." more minute(s)", [
                    'last_mobile' => $lastPk->tt_mobile,
                    'last_minutes_ago' => $mins,
                ]);
                return trans('common.access_violation');
				
            }
        }

        // (Optional) Per-number gap as well (also 30 min). Keep or remove as you prefer.
        $lastSameNumber = Orderitem::where('tt_mobile', $mobileNumber)
            ->where('created_by', $userId)
            ->latest('created_at')
            ->first();

        if ($lastSameNumber) {
            $minsSame = $now->diffInMinutes($lastSameNumber->created_at);
            if ($minsSame < 30) {
                Log::warning("Pakistan per-number gap: user $userId, mobile $mobileNumber, wait ".(30 - $minsSame)." more minute(s)");
                return trans('common.access_violation');
            }
        }
    }

    return null;
}



    function plans(Request $request)
    {
        $mobileNumber = str_replace("+", "", $request->input('mobile'));
        $from_date = date("Y-m-d") . ' 00:00:00';
        $to_date = date("Y-m-d") . ' 23:59:59';
		 $check_mobile_exits = Orderitem::select(['tt_mobile',\DB::raw('COUNT(tt_mobile) as totalmobile')])
			->where('tt_mobile',$mobileNumber)
			->where('created_by',auth()->user()->id)
			->whereBetween('created_at', [$from_date, $to_date])
			->groupBy('tt_mobile')
			->first();
        if($check_mobile_exits){
            if($check_mobile_exits['totalmobile'] <= 3){
                AppHelper::logger('warning',"Hacked",'Some User Access More than 2 Time ','error',true);

                Log::emergency("Hacked More Than 2 time  => ". $mobileNumber);
                return redirect()->back()->with('message',trans('service.service_unavailable'))
                    ->with('message_type','warning');
            }
        }
		$amount = 10.0;
		if ($msg = $this->checkTopupLimitAndGap($mobileNumber, $amount)) {
			return back()->with('message', $msg)->with('message_type', 'warning');
		}
        $validator = Validator::make($request->all(),[
            'mobile' => 'required',
            'countryCode' => 'required|max:4',
            'countryIso' => 'required|max:2',
        ]);
        if($validator->fails()){
            AppHelper::logger('warning','TamaTopup Validation Failed','Missing required parameters',$request->all());
            $html = AppHelper::create_error_bag($validator);
            return redirect()->back()
                ->with('message',$html)
                ->with('message_type','warning');
        }
        $to_replace = "(+".$request->countryCode.")";
        $mobileNumber = str_replace("+", "", $request->input('mobile'));
//        dd($mobileNumber);
        try{
            $response = $this->client->request('POST', 'topup',[
                'headers'  => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer ".API_TOKEN
                ],
                'form_params' => [
                    'mobile_number' => $mobileNumber,
                    'countryIso' => $request->countryIso,
                    'countryCode' => $request->countryCode
                ]
            ]);
            if($response->getStatusCode() == 200){
                $data = json_decode((string)$response->getBody(),true);
//                dd($data);
                AppHelper::logger('info', 'Tama Topup plan number Searched By ' . auth()->user()->username,'new tamatopup was fetched',$response,'true');
                $routeCheck = $data['data'];
                if($routeCheck['route'] == 'ding'){
                    $this->data = [
                        'page_title' => ucfirst(trans('view'))." ".trans('service.plans')." ".$request->mobile,
                        'mobile_number' => $mobileNumber,
                        'countryCode' => $request->countryCode,
                        'countryIso' => $request->countryCode
                    ];
                    return view('service.tama-topup.ding.index',$this->data);
                }elseif($routeCheck['route'] == 'transfer_to'){
                    $this->data = [
                        'page_title' => ucfirst(trans('view'))." ".trans('service.plans')." ".$request->mobile,
                        'plan' => $data['data'],
                        'country_code' => $request->countryCode
                    ];
                    return view('service.tama-topup.t_plans',$this->data);
                }elseif($routeCheck['route'] == 'reloadly'){

                    $this->data = [
                        'page_title' => ucfirst(trans('view'))." ".trans('service.plans')." ".$request->mobile,
                        'mobile_number' => $mobileNumber,
                        'countryCode' => $request->countryCode,
                        'countryIso' => $request->countryIso
                    ];
//                    dd($request->all());
                    return view('service.tama-topup.reloadly.index',$this->data);
                }elseif($routeCheck['route'] == 'transfer_to_new'){
                    $this->data = [
                        'page_title' => ucfirst(trans('view'))." ".trans('service.plans')." ".$request->mobile,
                        'mobile_number' => $mobileNumber,
                        'countryCode' => $request->countryCode,
                        'countryIso' => $request->countryCode
                    ];
                    return view('service.tama-topup.plans',$this->data);
                }elseif($routeCheck['route'] == 'stop'){

                    $this->data = [
                        'page_title' => ucfirst(trans('view'))." ".trans('service.plans')." ".$request->mobile,
                        'mobile_number' => $mobileNumber,
                        'countryCode' => $request->countryCode,
                        'countryIso' => $request->countryIso
                    ];
//                    dd($request->all());
                    return view('service.tama-topup.temporary',$this->data);
                }else{
                    $this->data = [
                        'page_title' => ucfirst(trans('view'))." ".trans('service.plans')." ".$request->mobile,
                        'mobile_number' => $mobileNumber,
                        'countryCode' => $request->countryCode,
                        'countryIso' => $request->countryCode
                    ];
                    return view('service.tama-topup.ding.index',$this->data);
                }
            }else{
                AppHelper::logger('warning',"Tama Topup API",'Tama Topup API HTTP Status '.$response->getStatusCode(),$response,true);
                return redirect()->back()->with('message',trans('service.service_unavailable'))
                    ->with('message_type','warning');
            }
        }catch (\Exception $e){
            AppHelper::logger('warning',"Tama Topup API HTTP Exception",$e->getMessage(),$e,true);
            return redirect()->back()->with('message',trans('service.service_unavailable'))
                ->with('message_type','warning');
        }

    }

    /**
     * POST confirm order
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    function confirm_topup(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(),[
            'country_code' => 'required',
            'mobile_number' => 'required',
            'mobile_operator' => 'required',
            'euro_amount' => 'required',
            'local_amount' => 'required',
            'dest_currency' => 'required',
            'country_name' => 'required'
        ]);
        if($validator->fails()){
            $html = AppHelper::create_error_bag($validator);
            AppHelper::logger('warning','TamaTopup Confirm Order Validation Failed',$html,$request->all());
            return redirect('tama-topup')
                ->with('message',$html)
                ->with('message_type','warning');
        }
        $mobile_number = $request->input('mobile_number');
        $euro_amount = $request->input('euro_amount');
        $local_amount = $request->input('local_amount');
        $dest_currency = $request->input('dest_currency');
        $country_code = $request->input('country_code');
        $country_name = $request->input('country_name');
        $mobile_operator = $request->input('mobile_operator');
        $user_info = User::find(auth()->user()->id);
        $order_comment = $user_info->username . " topup " . $mobile_number . " for " . $euro_amount . " destination currency is " . $local_amount . ' ' . $dest_currency;
        //lets check the parent balance or credit limit with in the order amount
        $check_limit = AppHelper::get_daily_limit($user_info->id);
        if($check_limit !=NULL)
        {
            if (ServiceHelper::limit_check($user_info->id, $euro_amount)) {
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id =(auth()->user()->parent_id);
                if($manager_id != '')
                {
                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email,'balaji@prepaysolution.in'];
                }
                else
                {
                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email];
                }
                $send_email_data = array(
                    'retailer_name' => auth()->user()->username,
                    'manager_name' => $result->username,
                    'current_bal' => $getBalance,
                    'total_limit' => $daily_limit,
                    'current_limit' => $blink_limit,
                );
                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                    $message->from('noreply@tamaexpress.com', 'Tama Retailer');
                    $message->to($emails)->subject('Tama Daily Limit Alert');
                });
                AppHelper::logger('warning', 'Daily Limit Exceed', $user_info->username . 'Daily limit exceed to confirm tama topup order', $request->all());
                Log::warning('TamaTopup Daily Limit Exceed => ' . $user_info->username . ' => ' . $user_info->id);
                return redirect('tama-topup')
                    ->with('message', trans('common.contact_manager'))
                    ->with('message_type', 'warning');
            }
        }
        if (ServiceHelper::parent_rule_check($user_info->parent_id, $euro_amount,$this->service_id)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', $user_info->username . ' parent does not have enough balance or credit limit to confirm tama topup order', $request->all());
            Log::warning('TamaTopup Parent Rule Failed => ' . $user_info->username . ' => ' . $user_info->parent_id);
            return redirect('tama-topup')
                ->with('message', trans('common.parent_rule_failed'))
                ->with('message_type', 'warning');
        }
        $current_balance = AppHelper::getBalance($user_info->id, $user_info->currency, false);
        if($country_code == 33){
            $user_service_commission = 10;
        }else{
            $user_service_commission = ServiceHelper::get_service_commission($user_info->id, $this->service_id);
        }
        //service_id may change
        $order_amount = ServiceHelper::calculate_commission($euro_amount, $user_service_commission);
        $user_credit_limit = AppHelper::get_credit_limit($user_info->id);
        $sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount);
        if ($current_balance < $order_amount) {
            //check with credit limit
            if (ServiceHelper::check_with_credit_limit($order_amount, $current_balance, $user_credit_limit) == false) {
                AppHelper::logger('warning', 'TamaTopup Balance Error', $user_info->username . ' does not have enough balance or credit limit to confirm tamatopup order', $request->all());
                return redirect('tama-topup')
                    ->with('message', trans('common.msg_order_failed_due_bal'))
                    ->with('message_type', 'warning');
            }
        }
        $transID = "TT".date("y") . strtoupper(date('M')) . date('d') . date('His').Rand(111,999);
        try{
            //tamaservice confirm order api call
            $response = $this->client->request('POST', 'topup/confirm', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
                'form_params' => [
                    'mobile_number' => $request->mobile_number,
                    'euro_amount' => $request->euro_amount,
                    'local_amount' => $request->local_amount,
                    'dest_currency' => $request->dest_currency,
                    'country_code' => $request->country_code
                ]
            ]);
            if ($response->getStatusCode() == 200) {
                $response_api = json_decode((string)$response->getBody(), true);
                AppHelper::logger('info', 'TamaTopup response' . auth()->user()->username, 'response from tamademat',$response_api);
                $response_data = $response_api['data'];
                if($response_data['transaction_id'] == 000)
                {
                    AppHelper::logger('warning',"Transfer to TamaTopup API",'Transfer to TamaTopup API HTTP Status 403',403,true);
                    return redirect('tama-topup')->with('message',trans('common.msg_order_failed'))
                        ->with('message_type','warning');
                }
                else {
                    $track_order_id = TrackOrder::insertGetId([
                        'trans_id' => $transID,
                        'user_id' => $user_info->id,
                        'api_order_id' => $response_data['order_id'],
                        'api_trans_id' => $response_data['transaction_id'],
                        'status' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => auth()->user()->id,
                        'remarks' => "Order Transaction is about to initiate..."
                    ]);
                }
            }
        }catch (BadResponseException $e){
            $response = $e->getResponse();
            $responseBodyAsString =  json_decode((string)$response->getBody()->getContents(),true);
            AppHelper::logger('warning',"TamaTopup API",'TamaTopup API HTTP Status '.$e->getCode(),$responseBodyAsString,true);
            return redirect('tama-topup')->with('message',trans('common.msg_order_failed'))
                ->with('message_type','warning');
        }
        try {
            \DB::beginTransaction();
            $tt_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
            $after_order_balance = number_format((float)$current_balance - $order_amount, 2, '.', '');
            $order_desc = $order_comment;
            $txn_ref = $tt_txn_id;
            $created_at = date("Y-m-d H:i:s");
            //make transaction
            $trans_id = ServiceHelper::sync_transaction($user_info->id, $created_at, 'debit', $order_amount, $current_balance, $after_order_balance, $order_desc);
            //make order
            $order_id = Order::insertGetId([
                'date' => $created_at,
                'user_id' => $user_info->id,
                'service_id' => $this->service_id,
                'order_status_id' => 7,
                'transaction_id' => $trans_id,
                'txn_ref' => $txn_ref,
                'comment' => $order_desc,
                'currency' => $user_info->currency,
                'public_price' => $euro_amount,
                'sale_margin' => $sale_margin,
                'buying_price' => $order_amount,
                'order_amount' => $order_amount,
                'grand_total' => $order_amount,
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            //insert order items
            $order_item_id = OrderItem::insertGetId([
                'order_id' => $order_id,
                'tt_mobile' => $mobile_number,
                'tt_euro_amount' => $euro_amount,
                'tt_dest_amount' => $local_amount,
                'tt_dest_currency' => $dest_currency,
                'tt_operator' => $mobile_operator,
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find($user_info->parent_id);
            if (!empty($user_info->parent_id) && $parent_user && $parent_user->group_id != 2) {
                if($country_code == 33){
                    $parent_user_commission = 16;
                }else{
                    $parent_user_commission = ServiceHelper::get_service_commission($parent_user->id, $this->service_id);
                }
                $parent_current_balance = AppHelper::getBalance($parent_user->id, $parent_user->currency, false);
                $parent_actual_commission = $parent_user_commission - $user_service_commission;
                $buying_price_parent = ServiceHelper::calculate_commission($euro_amount, $parent_user_commission);
                $order_amount_parent = ServiceHelper::calculate_commission($euro_amount, $parent_actual_commission);
                $parent_sale_margin = ServiceHelper::calculate_sale_margin($order_amount, $buying_price_parent);
                $parent_after_order_balance = number_format((float)$parent_current_balance - $buying_price_parent, 2, '.', '');

                //make transaction for parent
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $created_at, 'debit', $buying_price_parent, $parent_current_balance, $parent_after_order_balance, $order_desc);
                //parent order insertion
                $parent_order_id = Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $user_info->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $parent_trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_parent,
                    'sale_margin' => $parent_sale_margin,
                    'order_amount' => $order_amount,
                    'grand_total' => $order_amount,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
                //use the app commission to update order buying_price
                if($country_code == 33){
                    $app_commission = 18;
                }else{
                    $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                }
                $app_actual_commission = $app_commission - $parent_user_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($buying_price_parent, $buying_price_app);
                Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $parent_user->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_app,
                    'sale_margin' => $app_sale_margin,
                    'order_amount' => $buying_price_parent,
                    'grand_total' => $buying_price_parent,
                    'is_parent_order' => 1,
                    'exclude' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
            } else {
                //use the app commission to update order buying_price
                $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                $app_actual_commission = $app_commission - $user_service_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount_app);
                Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $user_info->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_app,
                    'sale_margin' => $app_sale_margin,
                    'order_amount' => $order_amount,
                    'grand_total' => $order_amount,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
            }
            //dd($euro_amount);exit;
            TrackOrder::where('id', $track_order_id)->update([
                'order_id' => $order_id,
                'order_status_id' => 1,
                'status' => 7,
                'remarks' => "Topup mobile successfully!",
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            \DB::commit();
            AppHelper::logger('success', 'TamaTopup Order #' . $order_id, $order_desc);
            return redirect("tama-topup/print/receipt/".$order_id)->with('message', trans('service.tama_order_placed_suc_callback'))->with('message_type', 'success');
        }
        catch (\Exception $e) {
            \DB::rollback();
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            //change the order status
            TrackOrder::where('trans_id', $transID)->update([
                'status' => 0,
                'remarks' => "Unable to place order, Exception occur => ".$exception_id,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            $exceptions = [
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
                'Code' => $e->getCode()
            ];
            Log::emergency(auth()->user()->username . " TamaTopup API Exception => " . $e->getMessage());
            AppHelper::logger('warning', 'TamaTopup Exception ' . $exception_id, $e->getMessage(),$exceptions);
            return redirect('tama-topup')
                ->with('message', trans('common.error_confirm_order') . ' ' . $exception_id)
                ->with('message_type', 'warning');
        }
    }

    function printReceipt($order_id)
    {
        $this->data['order'] = Order::join('order_items','order_items.id','orders.order_item_id')->where('orders.id',$order_id)
            ->join('order_status','order_status.id','orders.order_status_id')
            ->select([
                'orders.id',
                'orders.date',
                'orders.txn_ref',
                'order_status.name as status',
                'order_items.tt_mobile',
                'order_items.tt_euro_amount',
                'order_items.tt_dest_amount',
                'order_items.tt_dest_currency',
                'order_items.tt_operator',
                'order_items.transfer_ref',
                'order_items.tama_pin',
            ])->first();


        $orders = Order::join('order_items', 'order_items.id', '=', 'orders.order_item_id')
            ->join('users', 'users.id', 'orders.user_id')
            ->join('order_status', 'order_status.id', 'orders.order_status_id')
            ->join('services', 'services.id', 'orders.service_id');
        switch (DEFAULT_RECORD_METHOD) {
            case 1:
                break;
            case 2:
                $orders->whereMonth('orders.date', date('m'));
                break;
            case 3:
                $orders->whereBetween('orders.date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
        }
        if (auth()->user()->group_id == 2) {
            $orders->whereIn('users.id', $to_retrieve);
            $orders->where('orders.is_parent_order', '=', '1');
        }elseif (auth()->user()->group_id == 3) {
            $orders->whereIn('users.id', $to_retrieve);
            $orders->where('orders.is_parent_order', '=', '1');
        }elseif(auth()->user()->group_id == 4){
            $orders->whereIn('users.id',[auth()->user()->id]);
            $orders->where('orders.is_parent_order', '=', '0');
        } elseif(auth()->user()->group_id == 6){
            $orders->where('orders.service_id',1);
            $orders->where('orders.is_parent_order', '=', '0');
        } else {
            $orders->where(function ($query) {
                $query->where('users.parent_id', '=', '0')
                    ->orWhereNull('users.parent_id');
            });
            $orders->where('orders.is_parent_order', '=', '0');
        }
        $orders->select([
            'orders.id',
            'orders.date',
            'users.id as user_id',
            'users.username',
            'services.id as service_id',
            'services.name as service_name',
            'orders.public_price',
            'orders.order_amount',
            'orders.grand_total',
            'orders.is_parent_order',
            'orders.order_item_id',
            'order_status.name as order_status_name',
        ])->orderBy('orders.id',"DESC");
        //transactions
        $getTransactionsAmount = collect($orders->get());
        $today = date("Y-m-d");
        $today_trans = collect($getTransactionsAmount)->filter(function ($trans) use ($today){
            $datetime = new \DateTime($trans->date);
            $date = $datetime->format('Y-m-d');
            return strtotime($date) === strtotime($today);
        });
        $today_trans_amount = $today_trans->sum('order_amount');
        $this->data['today_transaction'] = $today_trans_amount;
        $this->data['total_orders'] = $orders->whereBetween('orders.date', [date('Y-m-d')." 00:00:00", date("Y-m-d")." 23:59:59"])->count();
        $this->data['page_title'] = trans('service.tama_order_placed_suc_callback');
        AppHelper::logger('info', 'Tama Topup Print' . auth()->user()->username,'tamatopup printed succesully',$this->data);
        return view('service.tama-topup.receipt',$this->data);
    }

    function dingTopup(Request $request)
    {
        return view('service.tama-topup.ding.index',[
            'page_title' => "Ding Topup"
        ]);
    }

    /**
     * TamaTopup API Interface
     * @param Request $request
     * @param $operation
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function fetchDingInterface(Request $request, $operation)
    {
//        dd($request->all());
        $params = [];
        $accountNumber = $request->accountNumber;
        $result = [];
        try {
            switch ($operation) {
                case "providers";
                    $params['accountNumber'] = $accountNumber;
                    $params['countryCode'] = $request->countryCode;
                    $params['providerCodes'] = $request->providerCode;
                    $params['countryIsos'] = $request->countryIso;
                    $params['regionCodes'] = $request->regionCode;
                    $result = $this->callDingAPI("GET","topup/ding/providers",$params);
                    break;

                case "regions";
                    $params['countryIsos'] = $request->countryIso;
                    $params['countryCode'] = $request->countryCode;
                    $result = $this->callDingAPI("GET","topup/ding/regions",$params);
                    break;

                case "products";
                    $params['accountNumber'] = $accountNumber;
                    $params['countryCode'] = $request->countryCode;
                    $params['countryIso'] = $request->countryIso;
                    $params['providerCode'] = $request->providerCode;
                    $params['skuCode'] = $request->skuCode;
                    $params['regionCode'] = $request->regionCode;
                    $result = $this->callDingAPI("GET","topup/ding/products",$params);
                    break;

                case "estimate";
                    $params = [
                        "sendAmount" => $request->sendAmount,
                        "skuCode" => $request->skuCode,
                        'countryCode' => $request->countryCode,
                        'providerCode' => $request->providerCode
                    ];
                    $result = $this->callDingAPI("GET","topup/ding/estimate",$params);
                    break;
            }
            return $result;
        } catch (\Exception $exception) {
//            Log::emergency("Ding Topup API returns exception => " . $exception->getMessage()." on line ".$exception->getLine());
            return responder()->error("EXCEPTION", $exception->getMessage() . " line " . $exception->getLine())->respond(400);
//            return responder()->error("EXCEPTION", "Please try again later!")->respond(400);
        }
    }

    /**
     * TamaTopup API Interface
     * @param Request $request
     * @param $operation
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function fetchPrepayInterface(Request $request, $operation)
    {
//        dd($request->all());
        $params = [];
        $accountNumber = $request->accountNumber;
        $result = [];
        try {
            switch ($operation) {
                case "providers";
                    $params['accountNumber'] = $accountNumber;
                    $params['countryCode'] = $request->countryCode;
                    $params['providerCodes'] = $request->providerCode;
                    $params['countryIsos'] = $request->countryIso;
                    $params['regionCodes'] = $request->regionCode;
                    $result = $this->callDingAPI("GET","topup/prepay/providers",$params);
                    break;

                case "products";
                    $params['accountNumber'] = $accountNumber;
                    $params['countryCode'] = $request->countryCode;
                    $params['countryIso'] = $request->countryIso;
                    $params['providerCode'] = $request->providerCode;
                    $params['skuCode'] = $request->skuCode;
                    $params['regionCode'] = $request->regionCode;
                    $result = $this->callDingAPI("GET","topup/prepay/products",$params);
                    break;

                case "estimate";
                    $params = [
                        "sendAmount" => $request->sendAmount,
                        "skuCode" => $request->skuCode,
                        'countryCode' => $request->countryCode,
                        'providerCode' => $request->providerCode
                    ];
                    $result = $this->callDingAPI("GET","topup/prepay/estimate",$params);
                    break;
            }
            return $result;
        } catch (\Exception $exception) {
//            Log::emergency("Ding Topup API returns exception => " . $exception->getMessage()." on line ".$exception->getLine());
            return responder()->error("EXCEPTION", $exception->getMessage() . " line " . $exception->getLine())->respond(400);
//            return responder()->error("EXCEPTION", "Please try again later!")->respond(400);
        }
    }


    function callDingAPI($method,$uri,$params)
    {
//        dd($uri."?".http_build_query($params));
        $dingClient = new Client([
            'base_uri' => API_END_POINT,
            'timeout'  => 180,
        ]);
        try{
            if($method == "GET")
            {
                $response = $dingClient->request($method, $uri."?".http_build_query($params), [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer " . API_TOKEN
                    ]
                ]);
            }else{
                $response = $dingClient->request($method, $uri, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer " . API_TOKEN
                    ],
                    'form_params' => $params
                ]);
            }
            if ($response->getStatusCode() == 200) {
                return json_decode((string)$response->getBody(), true);
            }
        }catch (RequestException $exception)
        {
            Log::warning("Exception calling Ding API => ".$exception->getMessage()." line ".$exception->getLine());
            $responseBody = $exception->getResponse()->getBody()->getContents();
            return [
                'status' => $exception->getResponse()->getStatusCode(),
                'message' => $responseBody,
                'data' => []
            ];
        }
    }

    /**
     * Modal window to review Topup
     * @param Request $request
     */

    function reviewTopup(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(), [
            "countryCode" => "required",
            "mobile" => "required",
            "provider_country" => "required",
            "operator" => "required",
            "euro_amount" => "required",
            "euro_amount_formatted" => "required",
            "dest_amount" => "required",
            "dest_amount_formatted" => "required",
            "SendAmount" => "required",
            "SendCurrencyIso" => "required",
            "commissionRate" => "required",
            "UatNumber" => "required",
            "SendValueOriginal" => "required",
        ]);
        if ($validator->fails()) {
            return "<h4 class='text-center'>Unable to view your order summary, Please try again later!</h4>";
        }
        $replacePlus = str_replace("+", "", $request->mobile);
        if (strlen($request->countryCode) == 2) {
            $accountNumber = $request->countryCode == "33" ? str_replace($request->countryCode, $request->countryCode . "0", $replacePlus) : $accountNumber = $replacePlus;
        } else {
            $accountNumber = $replacePlus;
        }
        if($request->countryCode == "33")
        {
            $amt_fr =$request->euro_amount_formatted;
        }
        else{
            $amt_fr =$request->dest_amount_formatted;
        }
        return view('service.tama-topup.ding.review', [
            'phone_no' => $accountNumber,
            'countryCode' => $request->countryCode,
            'country' => $request->provider_country,
            'operator' => $request->operator,
            'euro_amount' => $request->euro_amount_formatted,
            'dest_amount' =>  $amt_fr,
            '_hid_euro_amount' => $request->euro_amount,
            '_hid_dest_amount' => $request->dest_amount,
            'commissionRate' => $request->commissionRate,
            'skuCode' => $request->skuCode,
            'SendCurrencyIso' => $request->SendCurrencyIso,
            'SendValue' => $request->SendAmount,
            'UatNumber' => $request->UatNumber,
            'SendValueOriginal' => $request->SendValueOriginal,
        ]);
    }

    function reviewTopupPrepay(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(), [
            "mobile" => "required",
            "SendAmount" => "required",
        ]);
        if ($validator->fails()) {
            return "<h4 class='text-center'>Unable to view your order summary, Please try again later!</h4>";
        }
        $replacePlus = str_replace("+", "", $request->mobile);
        if (strlen($request->countryCode) == 2) {
            $accountNumber = $request->countryCode == "33" ? str_replace($request->countryCode, $request->countryCode . "0", $replacePlus) : $accountNumber = $replacePlus;
        } else {
            $accountNumber = $replacePlus;
        }
        $checkConfig = Country::where('phone_code', $request->countryCode)->first();
        return view('service.tama-topup.prepay.review', [
            'phone_no' => $accountNumber,
            'SendValue' => $request->SendAmount,
            'sendValueOriginal' => $request->sendValueOriginal,
            'skuCode' => $request->skuCode,
            'country' => $request->country,
            'operator' => $request->operator,
            'dest_amount' => $request->local_currency,
            'countryCode' => $request->countryCode,
            'currency'=> $checkConfig->currency
        ]);
    }
    /**
     * Ding Confirm Topup
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function confirmPrepayTopup(Request $request)
    {
        AppHelper::logger('info', 'Tama Topup Confirm Order ' . auth()->user()->username, 'Tama Topup Clicked order by',$request->all());
//        dd($request->all());
        $validator = Validator::make($request->all(), [
            "AccountNumber" => "required",
            "SkuCode" => "required",
            "SendValue" => "required",
            "sendValueOriginal" => "required",
        ]);
        if($validator->fails()){
            $html = AppHelper::create_error_bag($validator);
            AppHelper::logger('warning','TamaTopup Confirm Order Validation Failed',$html,$request->all());
            return redirect('tama-topup')
                ->with('message',$html)
                ->with('message_type','warning');
        }
        $mobile_number = str_replace("+", "", $request->input('AccountNumber'));
        $euro_amount = str_replace(',', '', $request->input('SendValue'));
        $local_amount = str_replace(',', '', $request->input('local_amt'));
        $dest_currency =$request->input('currency');
        $country_code = $request->input('countryCode');
        $country_name = $request->input('country');
        $mobile_operator = $request->input('operator');
        $user_info = User::find(auth()->user()->id);
        $order_comment = $user_info->username . " prepay topup " . $mobile_number . " for " . $euro_amount . " destination currency is " . $local_amount;
        $check_limit = AppHelper::get_daily_limit($user_info->id);
        //lets check the parent balance or credit limit with in the order amount
        $check_limit = AppHelper::get_daily_limit($user_info->id);
        if($check_limit !=NULL)
        {
            if (ServiceHelper::limit_check($user_info->id, $euro_amount)) {
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id =(auth()->user()->parent_id);
                if($manager_id != '')
                {
                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email,'balaji@prepaysolution.in'];
                }
                else
                {
                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email];
                }
                $send_email_data = array(
                    'retailer_name' => auth()->user()->username,
                    'manager_name' => $result->username,
                    'current_bal' => $getBalance,
                    'total_limit' => $daily_limit,
                    'current_limit' => $blink_limit,
                );
                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                    $message->from('noreply@tamaexpress.com', 'Tama Retailer');
                    $message->to($emails)->subject('Tama Daily Limit Alert');
                });
                AppHelper::logger('warning', 'Daily Limit Exceed', $user_info->username . 'Daily limit exceed to confirm tama topup order', $request->all());
                Log::warning('TamaTopup Daily Limit Exceed => ' . $user_info->username . ' => ' . $user_info->id);
                return redirect('tama-topup')
                    ->with('message', trans('common.contact_manager'))
                    ->with('message_type', 'warning');
            }
        }
        if (ServiceHelper::parent_rule_check($user_info->parent_id, $euro_amount,$this->service_id)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', $user_info->username . ' parent does not have enough balance or credit limit to confirm tama topup order', $request->all());
            Log::warning('TamaTopup Parent Rule Failed => ' . $user_info->username . ' => ' . $user_info->parent_id);
            return redirect('tama-topup')
                ->with('message', trans('common.parent_rule_failed'))
                ->with('message_type', 'warning');
        }
        $current_balance = AppHelper::getBalance($user_info->id, $user_info->currency, false);
        if($country_code == 33){
            $user_service_commission = 10;
        }else{
            $user_service_commission = ServiceHelper::get_service_commission($user_info->id, $this->service_id);//service_id may change
        }
        $order_amount = ServiceHelper::calculate_commission($euro_amount, $user_service_commission);
        $user_credit_limit = AppHelper::get_credit_limit($user_info->id);
        $sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount);
        if ($current_balance < $order_amount) {
            //check with credit limit
            if (ServiceHelper::check_with_credit_limit($order_amount, $current_balance, $user_credit_limit) == false) {
                AppHelper::logger('warning', 'TamaTopup Balance Error', $user_info->username . ' does not have enough balance or credit limit to confirm tamatopup order', $request->all());
                return redirect('tama-topup')
                    ->with('message', trans('common.msg_order_failed_due_bal'))
                    ->with('message_type', 'warning');
            }
        }
        $transID = "TT".date("y") . strtoupper(date('M')) . date('d') . date('His').Rand(111,999);
        try{
            //call ding topup api
            $dingTTClient = new Client([
                'base_uri' => API_END_POINT,
                'timeout'  => 180,
            ]);
            $params = $request->except("_token");
            $response = $dingTTClient->request("POST", "topup/confirm/prepay", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
                'form_params' => $params
            ]);
            if ($response->getStatusCode() == 200) {
                $response_api = json_decode((string)$response->getBody(), true);
                AppHelper::logger('info', 'TamaTopup response' . auth()->user()->username, 'response from tamademat',$response_api);
//                dd($response_api);
                $response_data = $response_api['data'];
                if($response_data['transaction_id'] == 000)
                {
                    AppHelper::logger('warning',"Prepeay TamaTopup API",'Prepeay TamaTopup API HTTP Status 403',403,true);
                    return redirect('tama-topup')->with('message',trans('common.msg_order_failed'))
                        ->with('message_type','warning');
                }
                else{
                    $track_order_id = TrackOrder::insertGetId([
                        'trans_id' => $transID,
                        'user_id' => $user_info->id,
                        'api_order_id' => $response_data['order_id'],
                        'api_trans_id' => $response_data['transaction_id'],
                        'status' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => auth()->user()->id,
                        'remarks' => "Order Transaction is about to initiate..."
                    ]);
                }
            }
        }catch (BadResponseException $e){
            $response = $e->getResponse();
            $response = $e->getResponse();
            $responseBodyAsString =  json_decode((string)$response->getBody()->getContents(),true);
//            dd($responseBodyAsString);
            AppHelper::logger('warning',"Ding TamaTopup API",'Ding TamaTopup API HTTP Status '.$e->getCode(),$responseBodyAsString,true);
            return redirect('tama-topup')->with('message',trans('common.msg_order_failed'))
                ->with('message_type','warning');
        }
        try {
            \DB::beginTransaction();
            $tt_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
            $after_order_balance = number_format((float)$current_balance - $order_amount, 2, '.', '');
            $order_desc = $order_comment;
            $txn_ref = isset($response_data['txn_ref']) ? $response_data['txn_ref'] : $tt_txn_id;
            $created_at = date("Y-m-d H:i:s");
            //make transaction
            $trans_id = ServiceHelper::sync_transaction($user_info->id, $created_at, 'debit', $order_amount, $current_balance, $after_order_balance, $order_desc);
            //make order
            $order_id = Order::insertGetId([
                'date' => $created_at,
                'user_id' => $user_info->id,
                'service_id' => $this->service_id,
                'order_status_id' => 7,
                'transaction_id' => $trans_id,
                'txn_ref' => $txn_ref,
                'comment' => $order_desc,
                'currency' => $user_info->currency,
                'public_price' => $euro_amount,
                'sale_margin' => $sale_margin,
                'buying_price' => $order_amount,
                'order_amount' => $order_amount,
                'grand_total' => $order_amount,
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            //insert order items
            $order_item_id = OrderItem::insertGetId([
                'order_id' => $order_id,
                'tt_mobile' => $mobile_number,
                'tt_euro_amount' => str_replace(',','',$euro_amount),
                'tt_dest_amount' => str_replace(",", '', $local_amount),
                'tt_dest_currency' => $dest_currency,
                'tt_operator' => $mobile_operator,
                'transfer_ref' => $response_data['transRef'],
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find($user_info->parent_id);
            if (!empty($user_info->parent_id) && $parent_user && $parent_user->group_id != 2) {
                if($country_code == 33){
                    $parent_user_commission = 16;
                }else{
                    $parent_user_commission = ServiceHelper::get_service_commission($parent_user->id, $this->service_id);
                }
                $parent_current_balance = AppHelper::getBalance($parent_user->id, $parent_user->currency, false);
                $parent_actual_commission = $parent_user_commission - $user_service_commission;
                $buying_price_parent = ServiceHelper::calculate_commission($euro_amount, $parent_user_commission);

                $order_amount_parent = ServiceHelper::calculate_commission($euro_amount, $parent_actual_commission);
                $parent_sale_margin = ServiceHelper::calculate_sale_margin($order_amount, $buying_price_parent);
                $parent_after_order_balance = number_format((float)$parent_current_balance - $buying_price_parent, 2, '.', '');
                //make transaction for parent
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $created_at, 'debit', $buying_price_parent, $parent_current_balance, $parent_after_order_balance, $order_desc);
                //parent order insertion
                $parent_order_id = Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $user_info->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $parent_trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_parent,
                    'sale_margin' => $parent_sale_margin,
                    'order_amount' => $order_amount,
                    'grand_total' => $order_amount,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
                //use the app commission to update order buying_price
                if($country_code == 33){
                    $app_commission = 18;
                }else{
                    $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                }
                $app_actual_commission = $app_commission - $parent_user_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($buying_price_parent, $buying_price_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);
                Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $parent_user->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_app,
                    'sale_margin' => $app_sale_margin,
                    'order_amount' => $buying_price_parent,
                    'grand_total' => $buying_price_parent,
                    'is_parent_order' => 1,
                    'exclude' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
            } else {
                //use the app commission to update order buying_price
                $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                $app_actual_commission = $app_commission - $user_service_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);

                Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $user_info->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_app,
                    'sale_margin' => $app_sale_margin,
                    'order_amount' => $order_amount,
                    'grand_total' => $order_amount,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
            }
            //dd($euro_amount);exit;
            TrackOrder::where('id', $track_order_id)->update([
                'order_id' => $order_id,
                'order_status_id' => 1,
                'status' => 7,
                'remarks' => "Topup mobile successfully!",
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            \DB::commit();
            AppHelper::logger('success', 'TamaTopup Order #' . $order_id, $order_desc);
            return redirect("tama-topup/print/receipt/".$order_id)->with('message', trans('service.tama_order_placed_suc_callback'))->with('message_type', 'success');
        }
        catch (\Exception $e) {
            \DB::rollback();
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            //change the order status
            TrackOrder::where('trans_id', $transID)->update([
                'status' => 0,
                'remarks' => "Unable to place order, Exception occur => ".$exception_id,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            $exceptions = [
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
                'Code' => $e->getCode()
            ];
            Log::emergency(auth()->user()->username . " TamaTopup API Exception => " . $e->getMessage());
            AppHelper::logger('warning', 'TamaTopup Exception ' . $exception_id, $e->getMessage(),$exceptions);
            return redirect('tama-topup')
                ->with('message', trans('common.error_confirm_order') . ' ' . $exception_id)
                ->with('message_type', 'warning');
        }
    }
    /**
     * Ding Confirm Topup
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function confirmDingTopup(Request $request)
    {
        AppHelper::logger('info', 'Tama Topup Confirm Order ' . auth()->user()->username, 'Tama Topup Clicked order by',$request->all());
//        dd($request->all());
        $validator = Validator::make($request->all(), [
            "countryCode" => "required",
            "AccountNumber" => "required",
            "SkuCode" => "required",
            "SendValue" => "required",
            "SendCurrencyIso" => "required",
            "commissionRate" => "required",
            "_hid_country" => "required",
            "_hid_operator" => "required",
            "_hid_euro_amount_formatted" => "required",
            "_hid_dest_amount_formatted" => "required",
            "_hid_euro_amount" => "required",
            "_hid_dest_amount" => "required",
            "_hid_dest_amount" => "required",
            "UatNumber" => "required",
            "SendValueOriginal" => "required",
        ]);
        if($validator->fails()){
            $html = AppHelper::create_error_bag($validator);
            AppHelper::logger('warning','TamaTopup Confirm Order Validation Failed',$html,$request->all());
            return redirect('tama-topup')
                ->with('message',$html)
                ->with('message_type','warning');
        }
        $mobile_number = str_replace("+", "", $request->input('AccountNumber'));
        $euro_amount = $request->input('SendValue');
        $local_amount = $request->input('_hid_dest_amount');
        $dest_currency = str_replace($request->input('_hid_dest_amount'),"",$request->input('_hid_dest_amount_formatted'));
        $country_code = $request->input('countryCode');
        $country_name = $request->input('_hid_country');
        $mobile_operator = $request->input('_hid_operator');
        $user_info = User::find(auth()->user()->id);
        $order_comment = $user_info->username . " ding topup " . $mobile_number . " for " . $euro_amount . " destination currency is " . $local_amount . ' ' . $dest_currency;
        //lets check the parent balance or credit limit with in the order amount
        $check_limit = AppHelper::get_daily_limit($user_info->id);
        if($check_limit !=NULL)
        {
            if (ServiceHelper::limit_check($user_info->id, $euro_amount)) {
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id =(auth()->user()->parent_id);
                if($manager_id != '')
                {
                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email,'balaji@prepaysolution.in'];
                }
                else
                {
                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email];
                }
                $send_email_data = array(
                    'retailer_name' => auth()->user()->username,
                    'manager_name' => $result->username,
                    'current_bal' => $getBalance,
                    'total_limit' => $daily_limit,
                    'current_limit' => $blink_limit,
                );
                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                    $message->from('noreply@tamaexpress.com', 'Tama Retailer');
                    $message->to($emails)->subject('Tama Daily Limit Alert');
                });
                AppHelper::logger('warning', 'Daily Limit Exceed', $user_info->username . 'Daily limit exceed to confirm tama topup order', $request->all());
                Log::warning('TamaTopup Daily Limit Exceed => ' . $user_info->username . ' => ' . $user_info->id);
                return redirect('tama-topup')
                    ->with('message', trans('common.contact_manager'))
                    ->with('message_type', 'warning');
            }
        }
        if (ServiceHelper::parent_rule_check($user_info->parent_id, $euro_amount,$this->service_id)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', $user_info->username . ' parent does not have enough balance or credit limit to confirm tama topup order', $request->all());
            Log::warning('TamaTopup Parent Rule Failed => ' . $user_info->username . ' => ' . $user_info->parent_id);
            return redirect('tama-topup')
                ->with('message', trans('common.parent_rule_failed'))
                ->with('message_type', 'warning');
        }
        $current_balance = AppHelper::getBalance($user_info->id, $user_info->currency, false);
        if($country_code == 33){
            $user_service_commission = 10;
        }else{
            $user_service_commission = ServiceHelper::get_service_commission($user_info->id, $this->service_id);//service_id may change
        }
        $order_amount = ServiceHelper::calculate_commission($euro_amount, $user_service_commission);
        $user_credit_limit = AppHelper::get_credit_limit($user_info->id);
        $sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount);
        if ($current_balance < $order_amount) {
            //check with credit limit
            if (ServiceHelper::check_with_credit_limit($order_amount, $current_balance, $user_credit_limit) == false) {
                AppHelper::logger('warning', 'TamaTopup Balance Error', $user_info->username . ' does not have enough balance or credit limit to confirm tamatopup order', $request->all());
                return redirect('tama-topup')
                    ->with('message', trans('common.msg_order_failed_due_bal'))
                    ->with('message_type', 'warning');
            }
        }
        $transID = "TT".date("y") . strtoupper(date('M')) . date('d') . date('His').Rand(111,999);
        try{
            //call ding topup api
            $dingTTClient = new Client([
                'base_uri' => API_END_POINT,
                'timeout'  => 180,
            ]);
            $params = $request->except("_token");
            $response = $dingTTClient->request("POST", "topup/confirm/ding", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
                'form_params' => $params
            ]);
            if ($response->getStatusCode() == 200) {
                $response_api = json_decode((string)$response->getBody(), true);
//                dd($response_api);
                AppHelper::logger('info', 'TamaTopup response ' . auth()->user()->username, 'response from tamademat',$response_api);
                $response_data = $response_api['data'];
                $track_order_id = TrackOrder::insertGetId([
                    'trans_id' => $transID,
                    'user_id' => $user_info->id,
                    'api_order_id' => $response_data['order_id'],
                    'api_trans_id' => $response_data['transaction_id'],
                    'status' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => auth()->user()->id,
                    'remarks' => "Order Transaction is about to initiate..."
                ]);
            }
        }catch (BadResponseException $e){
            $response = $e->getResponse();
            $response = $e->getResponse();
            $responseBodyAsString =  json_decode((string)$response->getBody()->getContents(),true);
//            dd($responseBodyAsString);
            AppHelper::logger('warning',"Ding TamaTopup API",'Ding TamaTopup API HTTP Status '.$e->getCode(),$responseBodyAsString,true);
            return redirect('tama-topup')->with('message',trans('common.msg_order_failed'))
                ->with('message_type','warning');
        }
        try {
            \DB::beginTransaction();
            $tt_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
            $after_order_balance = number_format((float)$current_balance - $order_amount, 2, '.', '');
            $order_desc = $order_comment;
            $txn_ref = isset($response_data['txn_ref']) ? $response_data['txn_ref'] : $tt_txn_id;
            $created_at = date("Y-m-d H:i:s");
            //make transaction
            $trans_id = ServiceHelper::sync_transaction($user_info->id, $created_at, 'debit', $order_amount, $current_balance, $after_order_balance, $order_desc);
            //make order
            $order_id = Order::insertGetId([
                'date' => $created_at,
                'user_id' => $user_info->id,
                'service_id' => $this->service_id,
                'order_status_id' => 7,
                'transaction_id' => $trans_id,
                'txn_ref' => $txn_ref,
                'comment' => $order_desc,
                'currency' => $user_info->currency,
                'public_price' => $euro_amount,
                'sale_margin' => $sale_margin,
                'buying_price' => $order_amount,
                'order_amount' => $order_amount,
                'grand_total' => $order_amount,
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            //insert order items
            $order_item_id = OrderItem::insertGetId([
                'order_id' => $order_id,
                'tt_mobile' => $mobile_number,
                'tt_euro_amount' => str_replace(',','',$euro_amount),
                'tt_dest_amount' => str_replace(",", '', $local_amount),
                'tt_dest_currency' => $dest_currency,
                'tt_operator' => $mobile_operator,
                'transfer_ref' => $response_data['transRef'],
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find($user_info->parent_id);
            if (!empty($user_info->parent_id) && $parent_user && $parent_user->group_id != 2) {
                if($country_code == 33){
                    $parent_user_commission = 11;
                }else{
                    $parent_user_commission = ServiceHelper::get_service_commission($parent_user->id, $this->service_id);
                }
                $parent_current_balance = AppHelper::getBalance($parent_user->id, $parent_user->currency, false);
                $parent_actual_commission = $parent_user_commission - $user_service_commission;
                $buying_price_parent = ServiceHelper::calculate_commission($euro_amount, $parent_user_commission);

                $order_amount_parent = ServiceHelper::calculate_commission($euro_amount, $parent_actual_commission);
                $parent_sale_margin = ServiceHelper::calculate_sale_margin($order_amount, $buying_price_parent);
                $parent_after_order_balance = number_format((float)$parent_current_balance - $buying_price_parent, 2, '.', '');
                //make transaction for parent
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $created_at, 'debit', $buying_price_parent, $parent_current_balance, $parent_after_order_balance, $order_desc);
                //parent order insertion
                $parent_order_id = Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $user_info->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $parent_trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_parent,
                    'sale_margin' => $parent_sale_margin,
                    'order_amount' => $order_amount,
                    'grand_total' => $order_amount,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
                //use the app commission to update order buying_price
                if($country_code == 33){
                    $app_commission = 11;
                }else{
                    $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                }
                $app_actual_commission = $app_commission - $parent_user_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($buying_price_parent, $buying_price_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);
                Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $parent_user->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_app,
                    'sale_margin' => $app_sale_margin,
                    'order_amount' => $buying_price_parent,
                    'grand_total' => $buying_price_parent,
                    'is_parent_order' => 1,
                    'exclude' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
            } else {
                //use the app commission to update order buying_price
                $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                $app_actual_commission = $app_commission - $user_service_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);

                Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $user_info->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_app,
                    'sale_margin' => $app_sale_margin,
                    'order_amount' => $order_amount,
                    'grand_total' => $order_amount,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
            }
            //dd($euro_amount);exit;
            TrackOrder::where('id', $track_order_id)->update([
                'order_id' => $order_id,
                'order_status_id' => 1,
                'status' => 7,
                'remarks' => "Topup mobile successfully!",
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            \DB::commit();
            AppHelper::logger('success', 'TamaTopup Order #' . $order_id, $order_desc);
            return redirect("tama-topup/print/receipt/".$order_id)->with('message', trans('service.tama_order_placed_suc_callback'))->with('message_type', 'success');
        }
        catch (\Exception $e) {
            \DB::rollback();
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            //change the order status
            TrackOrder::where('trans_id', $transID)->update([
                'status' => 0,
                'remarks' => "Unable to place order, Exception occur => ".$exception_id,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            $exceptions = [
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
                'Code' => $e->getCode()
            ];
            Log::emergency(auth()->user()->username . " TamaTopup API Exception => " . $e->getMessage());
            AppHelper::logger('warning', 'TamaTopup Exception ' . $exception_id, $e->getMessage(),$exceptions);
            return redirect('tama-topup')
                ->with('message', trans('common.error_confirm_order') . ' ' . $exception_id)
                ->with('message_type', 'warning');
        }
    }
    function fetchDingcards(Request $request)
    {
        $params = [];
        $result = [];

        $params['countryCodes'] = $request->countryCode;
        $params['providerCodes'] = $request->providerCode;
        $params['countryIsos'] = $request->countryIso;
        $params['region'] = $request->region;
        $result = $this->callDingAPI("POST","CallingCards",$params);
        return $result;
    }
    function callingcardreviewTopup(Request $request)
    {
//dd($request->all());
        $validator = Validator::make($request->all(), [
            "countryCode" => "required",
            "mobile" => "required",
            "provider_country" => "required",
            "operator" => "required",
            "euro_amount" => "required",
            "euro_amount_formatted" => "required",
            "dest_amount" => "required",
            "dest_amount_formatted" => "required",
            "SendAmount" => "required",
            "SendCurrencyIso" => "required",
            "commissionRate" => "required",
            "UatNumber" => "required",
            "SendValueOriginal" => "required",
        ]);
        $amt_fr =$request->euro_amount_formatted;
        $param['languageCodes'] ='fr';
        $param['skuCodes'] = $request->skuCode;

        return view('service.calling-card.ding-review', [
            'countryCode' => $request->countryCode,
            'country' => $request->provider_country,
            'operator' => $request->operator,
            'euro_amount' => $request->euro_amount_formatted,
            'dest_amount' => $amt_fr,
            '_hid_euro_amount' => $request->euro_amount,
            '_hid_dest_amount' => $request->dest_amount,
            'commissionRate' => $request->commissionRate,
            'skuCode' => $request->skuCode,
            'SendCurrencyIso' => $request->SendCurrencyIso,
            'SendValue' => $request->SendAmount,
            'UatNumber' => $request->UatNumber,
            'SendValueOriginal' => $request->SendValueOriginal,
//            'Description' => base64_decode($request->Description),
            'Description' => "",
            'Instruction' => "",
        ]);
    }
    function confirm_ding_callingcard(Request $request)
    {
//        dd($request->all());
        AppHelper::logger('info', 'Tama Topup calling card' . auth()->user()->username, 'Tama Topup Clicked order by',$request->all());
        $validator = Validator::make($request->all(), [
            "SkuCode" => "required",
            "SendValue" => "required",
            "SendCurrencyIso" => "required",
            "_hid_country" => "required",
            "_hid_operator" => "required",
            "_hid_euro_amount_formatted" => "required",
            "_hid_dest_amount_formatted" => "required",
            "_hid_euro_amount" => "required",
            "_hid_dest_amount" => "required",
            "_hid_dest_amount" => "required",
            "UatNumber" => "required",
            "SendValueOriginal" => "required",
        ]);
        $sendvl = $request->input('SendValue');
//        if($request->input('_hid_operator') == 'White Calling EUR') {
//            $sendvl = $request->input('SendValueOriginal');
//        }
        $mobile_number = str_replace("+", "", $request->input('AccountNumber'));
        $euro_amount = $sendvl;
        $local_amount = $request->input('_hid_dest_amount');
        $dest_currency = str_replace($request->input('_hid_dest_amount'),"",$request->input('_hid_dest_amount_formatted'));
        $country_code = $request->input('countryCode');
        $country_name = $request->input('_hid_country');
        $mobile_operator = $request->input('_hid_operator');
        $user_info = User::find(auth()->user()->id);
        if ($validator->fails()) {
            $html = AppHelper::create_error_bag($validator);
            Log::warning("Ding Topup Validation failed", [$request->except(['_token'])]);
            AppHelper::logger('warning', 'Calling Card Ding Confirm Order Validation Failed', $html, $request->except(['_token']));
            return Response::json(array(
                'success' => 0,
                'message'   => "Unable to topup , Please try again later!",
                'redirect' => 'tama-topup-france',
            ));
        }
        $order_comment = $user_info->username . " ding topup " . $mobile_number . " for " . $euro_amount . " destination currency is " . $local_amount . ' ' . $dest_currency;
        $transID = "TT".date("y") . strtoupper(date('M')) . date('d') . date('His').Rand(111,999);

        if (ServiceHelper::parent_rule_check($user_info->parent_id, $euro_amount,$this->service_id)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', $user_info->username . ' parent does not have enough balance or credit limit to confirm tama topup order', $request->all());
            Log::warning('TamaTopup Parent Rule Failed => ' . $user_info->username . ' => ' . $user_info->parent_id);
            return Response::json(array(
                'success' => 1,
                'message'   => trans('common.parent_rule_failed'),
                'redirect' => 'tama-topup-france',
            ));
        }
        $current_balance = AppHelper::getBalance($user_info->id, $user_info->currency, false);

        if($mobile_operator == 'Libon')
        {
            $user_service_commission = 10;
        }elseif($mobile_operator == 'White Calling EUR'){
            $user_service_commission = 3;
        }
        else
        {
            $user_service_commission = 10;
        }

        $order_amount = ServiceHelper::calculate_commission($euro_amount, $user_service_commission);
        $user_credit_limit = AppHelper::get_credit_limit($user_info->id);
        $sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount);
        if ($current_balance < $order_amount) {
            //check with credit limit
            if (ServiceHelper::check_with_credit_limit($order_amount, $current_balance, $user_credit_limit) == false) {
                AppHelper::logger('warning', 'TamaTopup Balance Error', $user_info->username . ' does not have enough balance or credit limit to confirm tamatopup order', $request->all());
                return Response::json(array(
                    'success' => 1,
                    'message'   => trans('common.msg_order_failed_due_bal'),
                    'redirect' => 'tama-topup-france',
                ));
            }
        }
        try{
            //call ding topup api
            $dingTTClient = new Client([
                'base_uri' => API_END_POINT,
                'timeout'  => 180,
            ]);
            $params = $request->except("_token");
            $response = $dingTTClient->request("POST", "callingcards/confirm/ding", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
                'form_params' => $params
            ]);

            if ($response->getStatusCode() == 200) {
                $response_api = json_decode((string)$response->getBody(), true);
//                dd($response_api);
                AppHelper::logger('info', 'TamaTopup response' . auth()->user()->username, 'response from tamademat',$response_api);
                $response_data = $response_api['data'];
                $track_order_id = TrackOrder::insertGetId([
                    'trans_id' => $transID,
                    'user_id' => $user_info->id,
                    'api_order_id' => $response_data['order_id'],
                    'api_trans_id' => $response_data['transaction_id'],
                    'status' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => auth()->user()->id,
                    'remarks' => "Order Transaction is about to initiate..."
                ]);
            }
        }catch (BadResponseException $e){
            $response = $e->getResponse();
            $response = $e->getResponse();
            $responseBodyAsString =  json_decode((string)$response->getBody()->getContents(),true);
//            dd($responseBodyAsString);
            AppHelper::logger('warning',"Ding TamaTopup API",'Ding TamaTopup API HTTP Status '.$e->getCode(),$responseBodyAsString,true);
            return Response::json(array(
                'success' => 3,
                'message'   => 'Unable to send topup, please try again later!',
                'redirect' => 'tama-topup-france',
            ));
        }
        try {
            \DB::beginTransaction();
            $tt_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
            $after_order_balance = number_format((float)$current_balance - $order_amount, 2, '.', '');
            $order_desc = $order_comment;
            $txn_ref = isset($response_data['txn_ref']) ? $response_data['txn_ref'] : $tt_txn_id;
            $created_at = date("Y-m-d H:i:s");
            //make transaction
            $trans_id = ServiceHelper::sync_transaction($user_info->id, $created_at, 'debit', $order_amount, $current_balance, $after_order_balance, $order_desc);
            //make order
            $order_id = Order::insertGetId([
                'date' => $created_at,
                'user_id' => $user_info->id,
                'service_id' => $this->service_id,
                'order_status_id' => 7,
                'transaction_id' => $trans_id,
                'txn_ref' => $txn_ref,
                'comment' => $order_desc,
                'currency' => $user_info->currency,
                'public_price' => $euro_amount,
                'sale_margin' => $sale_margin,
                'buying_price' => $order_amount,
                'order_amount' => $order_amount,
                'grand_total' => $order_amount,
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            //insert order items
            $order_item_id = OrderItem::insertGetId([
                'order_id' => $order_id,
                'tt_mobile' => $mobile_number,
                'instructions'=>  $response_data['instructions'],
                'tama_pin'=>  $response_data['tama_pin'],
                'tt_euro_amount' => str_replace(',','',$euro_amount),
                'tt_dest_amount' => str_replace(",", '', $local_amount),
                'tt_dest_currency' => $dest_currency,
                'tt_operator' => $mobile_operator,
                'transfer_ref' => $response_data['transRef'],
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find($user_info->parent_id);
            if (!empty($user_info->parent_id) && $parent_user && $parent_user->group_id != 2) {
                if($mobile_operator == 'Libon')
                {
                    $parent_user_commission = 11;
                }elseif($mobile_operator == 'White Calling EUR'){
                    $parent_user_commission = 4;
                }
                else
                {
                    $parent_user_commission = 11;
                }
                $parent_current_balance = AppHelper::getBalance($parent_user->id, $parent_user->currency, false);
                $parent_actual_commission = $parent_user_commission - $user_service_commission;
                $buying_price_parent = ServiceHelper::calculate_commission($euro_amount, $parent_user_commission);

                $order_amount_parent = ServiceHelper::calculate_commission($euro_amount, $parent_actual_commission);
                $parent_sale_margin = ServiceHelper::calculate_sale_margin($order_amount, $buying_price_parent);
                $parent_after_order_balance = number_format((float)$parent_current_balance - $buying_price_parent, 2, '.', '');
                //make transaction for parent
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $created_at, 'debit', $buying_price_parent, $parent_current_balance, $parent_after_order_balance, $order_desc);
                //parent order insertion
                $parent_order_id = Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $user_info->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $parent_trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_parent,
                    'sale_margin' => $parent_sale_margin,
                    'order_amount' => $order_amount,
                    'grand_total' => $order_amount,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
                //use the app commission to update order buying_price
                if($mobile_operator == 'Libon')
                {
                    $app_commission = 11;
                }elseif($mobile_operator == 'White Calling EUR'){
                    $app_commission = 4;
                }
                else
                {
                    $app_commission = 11;
                }
                $app_actual_commission = $app_commission - $parent_user_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($buying_price_parent, $buying_price_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);
                Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $parent_user->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_app,
                    'sale_margin' => $app_sale_margin,
                    'order_amount' => $buying_price_parent,
                    'grand_total' => $buying_price_parent,
                    'is_parent_order' => 1,
                    'exclude' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
            } else {
                //use the app commission to update order buying_price
                $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                $app_actual_commission = $app_commission - $user_service_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);

                Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $user_info->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_app,
                    'sale_margin' => $app_sale_margin,
                    'order_amount' => $order_amount,
                    'grand_total' => $order_amount,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
            }
            //dd($euro_amount);exit;
            TrackOrder::where('id', $track_order_id)->update([
                'order_id' => $order_id,
                'order_status_id' => 1,
                'status' => 7,
                'remarks' => "Topup mobile successfully!",
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            \DB::commit();
            AppHelper::logger('success', 'TamaTopup Order #' . $order_id, $order_desc);
            return Response::json(array(
                'success' => 4,
                'message'   => trans('service.tama_order_placed_suc_callback'),
                'redirect' => 'tama-topup-france/print/receipt/'.$order_id,
            ));
        }
        catch (\Exception $e) {
            \DB::rollback();
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            //change the order status
            TrackOrder::where('trans_id', $transID)->update([
                'status' => 0,
                'remarks' => "Unable to place order, Exception occur => ".$exception_id,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            $exceptions = [
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
                'Code' => $e->getCode()
            ];
            Log::emergency(auth()->user()->username . " TamaTopup API Exception => " . $e->getMessage());
            AppHelper::logger('warning', 'TamaTopup Exception ' . $exception_id, $e->getMessage(),$exceptions);
            return Response::json(array(
                'success' => 5,
                'redirect' => 'tama-topup',
                'message'   => trans('common.error_confirm_order').' '.$exception_id,
            ));
        }


    }
    function CallingCardPrint($order_id)
    {
        $this->data['order'] = Order::join('order_items', 'order_items.id', 'orders.order_item_id')->where('orders.id', $order_id)
            ->join('order_status', 'order_status.id', 'orders.order_status_id')
            ->select([
                'orders.id',
                'orders.date',
                'orders.txn_ref',
                'order_status.name as status',
                'order_items.tt_mobile',
                'order_items.tt_euro_amount',
                'order_items.tt_dest_amount',
                'order_items.tt_dest_currency',
                'order_items.instructions',
                'order_items.tt_operator',
                'order_items.transfer_ref',
                'order_items.tama_pin',
            ])->first();
        $orders = Order::join('users', 'users.id', 'orders.user_id')
            ->join('order_status', 'order_status.id', 'orders.order_status_id')
            ->join('services', 'services.id', 'orders.service_id');

        switch (DEFAULT_RECORD_METHOD) {
            case 1:
                break;
            case 2:
                $orders->whereMonth('orders.date', date('m'));
                break;
            case 3:
                $orders->whereBetween('orders.date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
        }
        $orders->join('order_items', 'order_items.order_id', 'orders.id');
        $orders->whereIn('users.id', [auth()->user()->id]);
        $orders->select([
            'orders.id',
            'orders.date',
            'users.id as user_id',
            'users.username',
            'services.id as service_id',
            'services.name as service_name',
            'orders.public_price',
            'orders.order_amount',
            'orders.grand_total',
            'order_items.product_id',
            'order_items.sender_first_name',
            'order_items.sender_mobile',
            'order_items.receiver_first_name',
            'order_items.receiver_mobile',
            'order_items.tt_mobile',
            'order_items.tt_operator',
            'order_items.app_mobile',
            'order_items.instructions',
            'order_items.tama_pin',
            'order_items.tama_serial',
            'order_status.name as order_status_name',
        ])->orderBy('orders.id', "DESC");
        //transactions

        $getTransactionsAmount = collect($orders->get());
        $today = date("Y-m-d");
        $today_trans = collect($getTransactionsAmount)->filter(function ($trans) use ($today) {
            $datetime = new \DateTime($trans->date);
            $date = $datetime->format('Y-m-d');
            return strtotime($date) === strtotime($today);
        });
        $today_trans_amount = $today_trans->sum('order_amount');

        $month = date("m");
        $total_trans = collect($getTransactionsAmount)->filter(function ($trans) use ($month) {
            $datetime = new \DateTime($trans->date);
            $date = $datetime->format('m');
            return $date === $month;
        });
        $total_trans_amount = $total_trans->sum('order_amount');

        $this->data['page_title'] = trans('service.tama_order_placed_suc_callback');
        $this->data['today_transaction'] = $today_trans_amount;
        $this->data['total_transaction'] = $total_trans_amount;
        $this->data['total_orders'] = $orders->whereBetween('orders.date', [date('Y-m-d')." 00:00:00", date("Y-m-d")." 23:59:59"])->count();
        AppHelper::logger('info', 'Tama Topup Print' . auth()->user()->username, 'printing card successfully',$this->data);
        return view('service.calling-card.dingreceipt', $this->data);
    }
    function fetchReloadlyInterface(Request $request, $operation)
    {
        $params = [];
        $result = [];
        try {
            switch ($operation) {
                case "providers";
                    $params['accountNumber'] = $request->accountNumber;
                    $params['countryCode'] = $request->countryCode;
                    $params['providerCodes'] = $request->providerCode;
                    $params['countryIsos'] = $request->countryIsos;
                    $params['regionCodes'] = $request->regionCode;
                    $result = $this->callDingAPI("GET","topup/reloadly/providers",$params);
                    break;
                case "data";
                    $params['countryCode'] = $request->countryCode;
                    $result = $this->callDingAPI("GET","topup/reloadly/data",$params);
                    break;
                case "products";
                    $params['accountNumber'] = $request->accountNumber;
                    $params['countryCode'] = $request->countryCode;
                    $params['countryIsos'] = $request->countryIsos;
                    $result = $this->callDingAPI("GET","topup/reloadly/products",$params);
                    break;
                case "productsID";
                    $params['operator_id'] = $request->operator_id;
                    $result = $this->callDingAPI("GET","topup/reloadly/productsID",$params);
                    break;
            }
            return $result;
        } catch (\Exception $exception) {
            return responder()->error("EXCEPTION", $exception->getMessage() . " line " . $exception->getLine())->respond(400);
        }
    }
    function reviewTopupreloadly(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(), [
            "mobile" => "required",
            "SendAmount" => "required",
        ]);
        if ($validator->fails()) {
            return "<h4 class='text-center'>Unable to view your order summary, Please try again later!</h4>";
        }
        $replacePlus = str_replace("+", "", $request->mobile);
        if (strlen($request->countryCode) == 2) {
            $accountNumber = $request->countryCode == "33" ? str_replace($request->countryCode, $request->countryCode . "0", $replacePlus) : $accountNumber = $replacePlus;
        } else {
            $accountNumber = $replacePlus;
        }
        $checkConfig = Country::where('phone_code', $request->countryCode)->first();
        return view('service.tama-topup.reloadly.review', [
            'phone_no' => $accountNumber,
            'SendValue' => $request->SendAmount,
            'sendValueOriginal' => $request->sendValueOriginal,
            'skuCode' => $request->skuCode,
            'country' => $request->country,
            'operator' => $request->operator,
            'dest_amount' => $request->local_currency,
            'countryCode' => $request->countryCode,
            'currency'=> $checkConfig->currency,
            'ISO'=> $checkConfig->iso,
            'description' => $request->description,
        ]);
    }
    function confirmReloadlyTopup(Request $request)
    {
//        dd($request->all());
        AppHelper::logger('info', 'Tama Topup Confirm Order ' . auth()->user()->username, 'Tama Topup Clicked order by',$request->all());
        $validator = Validator::make($request->all(), [
            "AccountNumber" => "required",
            "SkuCode" => "required",
            "SendValue" => "required",
            "sendValueOriginal" => "required",
        ]);
        if($validator->fails()){
            $html = AppHelper::create_error_bag($validator);
            AppHelper::logger('warning','TamaTopup Confirm Order Validation Failed',$html,$request->all());
            return redirect('tama-topup')
                ->with('message',$html)
                ->with('message_type','warning');
        }
        $mobile_number = str_replace("+", "", $request->input('AccountNumber'));
        $euro_amount = str_replace(',', '', $request->input('SendValue'));
        $local_amount = str_replace(',', '', $request->input('local_amt'));
        $dest_currency =$request->input('currency');
        $country_code = $request->input('countryCode');
        $country_name = $request->input('country');
        $description = $request->input('description');
        $mobile_operator = $request->input('operator');
        if (!empty($mobile_operator) && stripos($mobile_operator, 'data') !== false) {
             $dest_currency = $description ;
        }
        $user_info = User::find(auth()->user()->id);
        $order_comment = $user_info->username . " Reloadly topup " . $mobile_number . " for " . $euro_amount . " destination currency is " . $local_amount;
        $check_limit = AppHelper::get_daily_limit($user_info->id);
        //lets check the parent balance or credit limit with in the order amount
        $check_limit = AppHelper::get_daily_limit($user_info->id);
        if($check_limit !=NULL)
        {
            if (ServiceHelper::limit_check($user_info->id, $euro_amount)) {
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id =(auth()->user()->parent_id);
                if($manager_id != '')
                {
                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email,'balaji@prepaysolution.in'];
                }
                else
                {
                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email];
                }
                $send_email_data = array(
                    'retailer_name' => auth()->user()->username,
                    'manager_name' => $result->username,
                    'current_bal' => $getBalance,
                    'total_limit' => $daily_limit,
                    'current_limit' => $blink_limit,
                );
                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                    $message->from('noreply@tamaexpress.com', 'Tama Retailer');
                    $message->to($emails)->subject('Tama Daily Limit Alert');
                });
                AppHelper::logger('warning', 'Daily Limit Exceed', $user_info->username . 'Daily limit exceed to confirm tama topup order', $request->all());
                Log::warning('TamaTopup Daily Limit Exceed => ' . $user_info->username . ' => ' . $user_info->id);
                return redirect('tama-topup')
                    ->with('message', trans('common.contact_manager'))
                    ->with('message_type', 'warning');
            }
        }
        if (ServiceHelper::parent_rule_check($user_info->parent_id, $euro_amount,$this->service_id)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', $user_info->username . ' parent does not have enough balance or credit limit to confirm tama topup order', $request->all());
            Log::warning('TamaTopup Parent Rule Failed => ' . $user_info->username . ' => ' . $user_info->parent_id);
            return redirect('tama-topup')
                ->with('message', trans('common.parent_rule_failed'))
                ->with('message_type', 'warning');
        }
        $current_balance = AppHelper::getBalance($user_info->id, $user_info->currency, false);
        if($country_code == 33){
            $user_service_commission = 10;
        }else{
            $user_service_commission = ServiceHelper::get_service_commission($user_info->id, $this->service_id);//service_id may change
        }
        $order_amount = ServiceHelper::calculate_commission($euro_amount, $user_service_commission);
        $user_credit_limit = AppHelper::get_credit_limit($user_info->id);
        $sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount);
        if ($current_balance < $order_amount) {
            //check with credit limit
            if (ServiceHelper::check_with_credit_limit($order_amount, $current_balance, $user_credit_limit) == false) {
                AppHelper::logger('warning', 'TamaTopup Balance Error', $user_info->username . ' does not have enough balance or credit limit to confirm tamatopup order', $request->all());
                return redirect('tama-topup')
                    ->with('message', trans('common.msg_order_failed_due_bal'))
                    ->with('message_type', 'warning');
            }
        }
        $transID = "TT".date("y") . strtoupper(date('M')) . date('d') . date('His').Rand(111,999);
        try{
            //call ding topup api
            $dingTTClient = new Client([
                'base_uri' => API_END_POINT,
                'timeout'  => 180,
            ]);
            $params = $request->except("_token");
            $response = $dingTTClient->request("POST", "topup/confirm/reloadly", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
                'form_params' => $params
            ]);
            if ($response->getStatusCode() == 200) {
                $response_api = json_decode((string)$response->getBody(), true);
//                dd($response_api);
                AppHelper::logger('info', 'TamaTopup response' . auth()->user()->username, 'response from tamademat',$response_api);
                $response_data = $response_api['data'];
                if($response_data['transaction_id'] == 000)
                {
                    AppHelper::logger('warning',"Reloadly TamaTopup API",'Reloadly TamaTopup API HTTP Status 403',403,true);
                    return redirect('tama-topup')->with('message',trans('common.msg_order_failed'))
                        ->with('message_type','warning');
                }
                else{
                    $track_order_id = TrackOrder::insertGetId([
                        'trans_id' => $transID,
                        'user_id' => $user_info->id,
                        'api_order_id' => $response_data['order_id'],
                        'api_trans_id' => $response_data['transaction_id'],
                        'status' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => auth()->user()->id,
                        'remarks' => "Order Transaction is about to initiate..."
                    ]);
                }
            }
        }catch (BadResponseException $e){
            $response = $e->getResponse();
            $response = $e->getResponse();
            $responseBodyAsString =  json_decode((string)$response->getBody()->getContents(),true);
//            dd($responseBodyAsString);
            AppHelper::logger('warning',"Reoladly api error",'Ding TamaTopup API HTTP Status '.$e->getCode(),$responseBodyAsString,true);
            return redirect('tama-topup')->with('message',trans('common.msg_order_failed'))
                ->with('message_type','warning');
        }
        try {
            \DB::beginTransaction();
            $tt_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
            $after_order_balance = number_format((float)$current_balance - $order_amount, 2, '.', '');
            $order_desc = $order_comment;
            $txn_ref = isset($response_data['txn_ref']) ? $response_data['txn_ref'] : $tt_txn_id;
            $created_at = date("Y-m-d H:i:s");
            //make transaction
            $trans_id = ServiceHelper::sync_transaction($user_info->id, $created_at, 'debit', $order_amount, $current_balance, $after_order_balance, $order_desc);
            //make order
            $order_id = Order::insertGetId([
                'date' => $created_at,
                'user_id' => $user_info->id,
                'service_id' => $this->service_id,
                'order_status_id' => 7,
                'transaction_id' => $trans_id,
                'txn_ref' => $txn_ref,
                'comment' => $order_desc,
                'currency' => $user_info->currency,
                'public_price' => $euro_amount,
                'sale_margin' => $sale_margin,
                'buying_price' => $order_amount,
                'order_amount' => $order_amount,
                'grand_total' => $order_amount,
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            //insert order items
            $order_item_id = OrderItem::insertGetId([
                'order_id' => $order_id,
                'tt_mobile' => $mobile_number,
                'tt_euro_amount' => str_replace(',','',$euro_amount),
                'tt_dest_amount' => str_replace(",", '', $local_amount),
                'tt_dest_currency' => $dest_currency,
                'tt_operator' => $mobile_operator,
                'transfer_ref' => $response_data['transRef'],
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find($user_info->parent_id);
            if (!empty($user_info->parent_id) && $parent_user && $parent_user->group_id != 2) {
                if($country_code == 33){
                    $parent_user_commission = 16;
                }else{
                    $parent_user_commission = ServiceHelper::get_service_commission($parent_user->id, $this->service_id);
                }
                $parent_current_balance = AppHelper::getBalance($parent_user->id, $parent_user->currency, false);
                $parent_actual_commission = $parent_user_commission - $user_service_commission;
                $buying_price_parent = ServiceHelper::calculate_commission($euro_amount, $parent_user_commission);

                $order_amount_parent = ServiceHelper::calculate_commission($euro_amount, $parent_actual_commission);
                $parent_sale_margin = ServiceHelper::calculate_sale_margin($order_amount, $buying_price_parent);
                $parent_after_order_balance = number_format((float)$parent_current_balance - $buying_price_parent, 2, '.', '');
                //make transaction for parent
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $created_at, 'debit', $buying_price_parent, $parent_current_balance, $parent_after_order_balance, $order_desc);
                //parent order insertion
                $parent_order_id = Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $user_info->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $parent_trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_parent,
                    'sale_margin' => $parent_sale_margin,
                    'order_amount' => $order_amount,
                    'grand_total' => $order_amount,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
                //use the app commission to update order buying_price
                if($country_code == 33){
                    $app_commission = 18;
                }else{
                    $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                }
                $app_actual_commission = $app_commission - $parent_user_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($buying_price_parent, $buying_price_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);
                Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $parent_user->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_app,
                    'sale_margin' => $app_sale_margin,
                    'order_amount' => $buying_price_parent,
                    'grand_total' => $buying_price_parent,
                    'is_parent_order' => 1,
                    'exclude' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
            } else {
                //use the app commission to update order buying_price
                $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                $app_actual_commission = $app_commission - $user_service_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);

                Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $user_info->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_app,
                    'sale_margin' => $app_sale_margin,
                    'order_amount' => $order_amount,
                    'grand_total' => $order_amount,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
            }
            //dd($euro_amount);exit;
            TrackOrder::where('id', $track_order_id)->update([
                'order_id' => $order_id,
                'order_status_id' => 1,
                'status' => 7,
                'remarks' => "Topup mobile successfully!",
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            \DB::commit();
            AppHelper::logger('success', 'TamaTopup Order #' . $order_id, $order_desc);
            return redirect("tama-topup/print/receipt/".$order_id)->with('message', trans('service.tama_order_placed_suc_callback'))->with('message_type', 'success');
        }
        catch (\Exception $e) {
            \DB::rollback();
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            //change the order status
            TrackOrder::where('trans_id', $transID)->update([
                'status' => 0,
                'remarks' => "Unable to place order, Exception occur => ".$exception_id,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            $exceptions = [
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
                'Code' => $e->getCode()
            ];
            Log::emergency(auth()->user()->username . " TamaTopup API Exception => " . $e->getMessage());
            AppHelper::logger('warning', 'TamaTopup Exception ' . $exception_id, $e->getMessage(),$exceptions);
            return redirect('tama-topup')
                ->with('message', trans('common.error_confirm_order') . ' ' . $exception_id)
                ->with('message_type', 'warning');
        }
    }
    function fetchTransferInterface(Request $request, $operation)
    {
        $params = [];
        $replacePlus = str_replace("+", "", $request->accountNumber);
        if (strlen($request->countryCode) == 2) {
            $accountNumber = $request->countryCode == "33" ? str_replace($request->countryCode, $request->countryCode . "0", $replacePlus) : $accountNumber = $replacePlus;
        } else {
            $accountNumber = $replacePlus;
        }
        try {
            $params['accountNumber'] = $accountNumber;
            $params['providerCodes'] = $request->providerCode;
            $params['countryIsos'] = $request->countryIso;
            $params['skuCodes'] = $request->skuCode;
            $params['regionCodes'] = $request->regionCode;
            $params['countryCode'] = $request->countryCode;
            $params['type'] = $request->type;
            $params['operator_id'] = $request->operator_id;
            $params['country_iso_code'] = $request->country_iso_code;
            switch ($operation) {
                case "providers";
                    $result = $this->callDingAPI("GET","topup/transfer/providers",$params);
                    break;
                case "products";
                    $result = $this->callDingAPI("GET","topup/transfer/products",$params);
                    break;
            }
            return $result;
        } catch (\Exception $exception) {
            return responder()->error("EXCEPTION", $exception->getMessage() . " line " . $exception->getLine())->respond(400);
        }
    }
    function reviewTopuptransfer(Request $request)
    {
        $replacePlus = str_replace("+", "", $request->mobile);
        $accountNumber = $replacePlus;
        $sender_name =  AppHelper::findusername(auth()->user()->id);
        $sender_parent_name =  AppHelper::findusername(auth()->user()->parent_id);
        return view('service.tama-topup.review', [
            'phone_no' => $accountNumber,
            'SendValue' => $request->SendValue,
            'sendValueOriginal' => $request->ReceiveValue,
            'skuCode' => $request->skuCode,
            'country' => $request->sendCurrencyIso,
            'operator' => $request->sendCurrencyIso,
            'dest_amount' => $request->display_text,
            'countryCode' => $request->countryCode,
            'currency'=> $request->sendCurrencyIso,
            'ISO'=> $request->receiveCurrencyIso,
            'name'=> $request->name,
            'operator_id'=> $request->operator_id,
            'operator_name'=> $request->operator_name,
            'country'=> $request->country,
            'sender_name' => $sender_name->username,
            'sender_parent_name' => $sender_parent_name->username,
        ]);
    }
    function confirmTransferTopup(Request $request)
    {
//        dd($request->all());
        AppHelper::logger('info', 'Tama Topup Confirm Order ' . auth()->user()->username, 'Tama Topup Clicked order by',$request->all());
        $validator = Validator::make($request->all(), [
            "mobile_number" => "required",
            "SkuCode" => "required",
            "SendValue" => "required",
            "sendValueOriginal" => "required",
        ]);
        if($validator->fails()){
            $html = AppHelper::create_error_bag($validator);
            AppHelper::logger('warning','TamaTopup Confirm Order Validation Failed',$html,$request->all());
            return redirect('tama-topup')
                ->with('message',$html)
                ->with('message_type','warning');
        }
        $mobile_number = $request->input('mobile_number');
        $euro_amount = $request->input('SendValue');
        $local_amount = $request->input('local_amount');
        $dest_currency = $request->input('ISO');
        $country_code = $request->input('country_code');
        $country_name = $request->input('country');
        $mobile_operator = $request->input('operator_name');
//
        $user_info = User::find(auth()->user()->id);
        $order_comment = $user_info->username . " Transfer to topup " . $mobile_number . " for " . $euro_amount . " destination currency is " . $local_amount;
        $check_limit = AppHelper::get_daily_limit($user_info->id);
        //lets check the parent balance or credit limit with in the order amount
        $check_limit = AppHelper::get_daily_limit($user_info->id);
        if($check_limit !=NULL)
        {
            if (ServiceHelper::limit_check($user_info->id, $euro_amount)) {
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id =(auth()->user()->parent_id);
                if($manager_id != '')
                {
                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email,'balaji@prepaysolution.in'];
                }
                else
                {
                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email];
                }
                $send_email_data = array(
                    'retailer_name' => auth()->user()->username,
                    'manager_name' => $result->username,
                    'current_bal' => $getBalance,
                    'total_limit' => $daily_limit,
                    'current_limit' => $blink_limit,
                );
                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                    $message->from('noreply@tamaexpress.com', 'Tama Retailer');
                    $message->to($emails)->subject('Tama Daily Limit Alert');
                });
                AppHelper::logger('warning', 'Daily Limit Exceed', $user_info->username . 'Daily limit exceed to confirm tama topup order', $request->all());
                Log::warning('TamaTopup Daily Limit Exceed => ' . $user_info->username . ' => ' . $user_info->id);
                return redirect('tama-topup')
                    ->with('message', trans('common.contact_manager'))
                    ->with('message_type', 'warning');
            }
        }
        if (ServiceHelper::parent_rule_check($user_info->parent_id, $euro_amount,$this->service_id)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', $user_info->username . ' parent does not have enough balance or credit limit to confirm tama topup order', $request->all());
            Log::warning('TamaTopup Parent Rule Failed => ' . $user_info->username . ' => ' . $user_info->parent_id);
            return redirect('tama-topup')
                ->with('message', trans('common.parent_rule_failed'))
                ->with('message_type', 'warning');
        }
        $current_balance = AppHelper::getBalance($user_info->id, $user_info->currency, false);
        if($country_code == 33){
            $user_service_commission = 10;
        }else{
            $user_service_commission = ServiceHelper::get_service_commission($user_info->id, $this->service_id);//service_id may change
        }
        $order_amount = ServiceHelper::calculate_commission($euro_amount, $user_service_commission);
        $user_credit_limit = AppHelper::get_credit_limit($user_info->id);
        $sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount);
        if ($current_balance < $order_amount) {
            //check with credit limit
            if (ServiceHelper::check_with_credit_limit($order_amount, $current_balance, $user_credit_limit) == false) {
                AppHelper::logger('warning', 'TamaTopup Balance Error', $user_info->username . ' does not have enough balance or credit limit to confirm tamatopup order', $request->all());
                return redirect('tama-topup')
                    ->with('message', trans('common.msg_order_failed_due_bal'))
                    ->with('message_type', 'warning');
            }
        }
        $transID = "TT".date("y") . strtoupper(date('M')) . date('d') . date('His').Rand(111,999);
        try{
            //call ding topup api
            $dingTTClient = new Client([
                'base_uri' => API_END_POINT,
                'timeout'  => 180,
            ]);
            $params = $request->except("_token");
//            dd($params);
            $response = $dingTTClient->request("POST", "topup/confirm/transfer", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
                'form_params' => $params
            ]);
            if ($response->getStatusCode() == 200) {
                $response_api = json_decode((string)$response->getBody(), true);
//                dd($response_api);
                AppHelper::logger('info', 'TamaTopup response' . auth()->user()->username, 'response from tamademat',$response_api);
                $response_data = $response_api['data'];
                if($response_data['transaction_id'] == 000)
                {
                    AppHelper::logger('warning',"Transfer TamaTopup API",'Transfer TamaTopup API HTTP Status 403',403,true);
                    return redirect('tama-topup')->with('message',trans('common.msg_order_failed'))
                        ->with('message_type','warning');
                }
                else{
                    $track_order_id = TrackOrder::insertGetId([
                        'trans_id' => $transID,
                        'user_id' => $user_info->id,
                        'api_order_id' => $response_data['order_id'],
                        'api_trans_id' => $response_data['transaction_id'],
                        'status' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => auth()->user()->id,
                        'remarks' => "Order Transaction is about to initiate..."
                    ]);
                    $local_amount = $response_data['local_amount'];
                    $dest_currency = $response_data['dest_currency'];
                }
            }
        }catch (BadResponseException $e){
            $response = $e->getResponse();
            $response = $e->getResponse();
            $responseBodyAsString =  json_decode((string)$response->getBody()->getContents(),true);
//            dd($responseBodyAsString);
            AppHelper::logger('warning',"Ding TamaTopup API",'Ding TamaTopup API HTTP Status '.$e->getCode(),$responseBodyAsString,true);
            return redirect('tama-topup')->with('message',trans('common.msg_order_failed'))
                ->with('message_type','warning');
        }
        try {
            \DB::beginTransaction();
            $tt_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
            $after_order_balance = number_format((float)$current_balance - $order_amount, 2, '.', '');
            $order_desc = $order_comment;
            $txn_ref = isset($response_data['txn_ref']) ? $response_data['txn_ref'] : $tt_txn_id;
            $created_at = date("Y-m-d H:i:s");
            //make transaction
            $trans_id = ServiceHelper::sync_transaction($user_info->id, $created_at, 'debit', $order_amount, $current_balance, $after_order_balance, $order_desc);
            //make order
            $order_id = Order::insertGetId([
                'date' => $created_at,
                'user_id' => $user_info->id,
                'service_id' => $this->service_id,
                'order_status_id' => 7,
                'transaction_id' => $trans_id,
                'txn_ref' => $txn_ref,
                'comment' => $order_desc,
                'currency' => $user_info->currency,
                'public_price' => $euro_amount,
                'sale_margin' => $sale_margin,
                'buying_price' => $order_amount,
                'order_amount' => $order_amount,
                'grand_total' => $order_amount,
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            //insert order items
            $order_item_id = OrderItem::insertGetId([
                'order_id' => $order_id,
                'tt_mobile' => $mobile_number,
                'tt_euro_amount' => str_replace(',','',$euro_amount),
                'tt_dest_amount' => str_replace(",", '', $local_amount),
                'tt_dest_currency' => $dest_currency,
                'tt_operator' => $mobile_operator,
                'transfer_ref' => $response_data['transRef'],
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find($user_info->parent_id);
            if (!empty($user_info->parent_id) && $parent_user && $parent_user->group_id != 2) {
                if($country_code == 33){
                    $parent_user_commission = 16;
                }else{
                    $parent_user_commission = ServiceHelper::get_service_commission($parent_user->id, $this->service_id);
                }
                $parent_current_balance = AppHelper::getBalance($parent_user->id, $parent_user->currency, false);
                $parent_actual_commission = $parent_user_commission - $user_service_commission;
                $buying_price_parent = ServiceHelper::calculate_commission($euro_amount, $parent_user_commission);

                $order_amount_parent = ServiceHelper::calculate_commission($euro_amount, $parent_actual_commission);
                $parent_sale_margin = ServiceHelper::calculate_sale_margin($order_amount, $buying_price_parent);
                $parent_after_order_balance = number_format((float)$parent_current_balance - $buying_price_parent, 2, '.', '');
                //make transaction for parent
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $created_at, 'debit', $buying_price_parent, $parent_current_balance, $parent_after_order_balance, $order_desc);
                //parent order insertion
                $parent_order_id = Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $user_info->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $parent_trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_parent,
                    'sale_margin' => $parent_sale_margin,
                    'order_amount' => $order_amount,
                    'grand_total' => $order_amount,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
                //use the app commission to update order buying_price
                if($country_code == 33){
                    $app_commission = 18;
                }else{
                    $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                }
                $app_actual_commission = $app_commission - $parent_user_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($buying_price_parent, $buying_price_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);
                Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $parent_user->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_app,
                    'sale_margin' => $app_sale_margin,
                    'order_amount' => $buying_price_parent,
                    'grand_total' => $buying_price_parent,
                    'is_parent_order' => 1,
                    'exclude' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
            } else {
                //use the app commission to update order buying_price
                $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                $app_actual_commission = $app_commission - $user_service_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);

                Order::insertGetId([
                    'date' => $created_at,
                    'user_id' => $user_info->id,
                    'service_id' => $this->service_id,
                    'order_status_id' => 7,
                    'transaction_id' => $trans_id,
                    'txn_ref' => $txn_ref,
                    'comment' => $order_desc,
                    'currency' => $user_info->currency,
                    'public_price' => $euro_amount,
                    'buying_price' => $buying_price_app,
                    'sale_margin' => $app_sale_margin,
                    'order_amount' => $order_amount,
                    'grand_total' => $order_amount,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $created_at,
                    'created_by' => $user_info->id,
                ]);
            }
            //dd($euro_amount);exit;
            TrackOrder::where('id', $track_order_id)->update([
                'order_id' => $order_id,
                'order_status_id' => 1,
                'status' => 7,
                'remarks' => "Topup mobile successfully!",
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            \DB::commit();
            AppHelper::logger('success', 'TamaTopup Order #' . $order_id, $order_desc);
            return redirect("tama-topup/print/receipt/".$order_id)->with('message', trans('service.tama_order_placed_suc_callback'))->with('message_type', 'success');
        }
        catch (\Exception $e) {
            \DB::rollback();
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            //change the order status
            TrackOrder::where('trans_id', $transID)->update([
                'status' => 0,
                'remarks' => "Unable to place order, Exception occur => ".$exception_id,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            $exceptions = [
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
                'Code' => $e->getCode()
            ];
            Log::emergency(auth()->user()->username . " TamaTopup API Exception => " . $e->getMessage());
            AppHelper::logger('warning', 'TamaTopup Exception ' . $exception_id, $e->getMessage(),$exceptions);
            return redirect('tama-topup')
                ->with('message', trans('common.error_confirm_order') . ' ' . $exception_id)
                ->with('message_type', 'warning');
        }
    }
}
