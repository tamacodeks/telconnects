<?php

namespace App\Http\Controllers\App;

use App\Events\PaymentReceived;
use app\Library\AppHelper;
use App\Models\Payment;
use App\Models\Transaction;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Validator;
use App\Models\DailyLimit;

class PaymentController extends Controller
{
    private $client;
    function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'base_uri' => API_END_POINT,
            'timeout' => 5.0,
        ]);
    }

    /**
     * View All Payments
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function index()
    {
        $retailer_qry = User::where('status',1);
        if(in_array(auth()->user()->group_id,[1,2,3])){
            $child = User::where('id',auth()->user()->id)->with('children')->first();
            $retailers = $child->children->pluck('id')->flatten()->toArray();
            $retailer_qry->whereIn('id',$retailers);
            $retailers = $retailer_qry->select('id','username')->get();
        }else{
            $retailers = [];
        }
        $page_data = [
            'page_title' => trans('common.my_payments'),
            'retailers' => $retailers
        ];
        return view('app.payments.index',$page_data);
    }

    /**
     * Ajax Get All Payments
     * @param Request $request
     * @return mixed
     */
    function getPayments(Request $request)
    {
        $query = Payment::leftjoin('transactions','transactions.id','payments.transaction_id')
            ->join('users','users.id','payments.user_id')
            ->select([
                'payments.date',
                'transactions.date as payment_date',
                'users.username',
                'users.cust_id',
                'payments.amount',
                'transactions.prev_bal',
                'transactions.balance',
                'payments.description',
                'payments.received_by'
            ]);
        if(auth()->user()->group_id == 2){
            $child = User::where('id',auth()->user()->id)->with('children')->first();
            $retailers = $child->children->pluck('id')->flatten()->toArray();
//            $to_retrieve = array_add($retailers,count($retailers),auth()->user()->id);
            $query->whereIn('payments.user_id',$retailers);
        }elseif(auth()->user()->group_id == 3){
            $child = User::where('id',auth()->user()->id)->with('children')->first();
            $retailers = $child->children->pluck('id')->flatten()->toArray();
            $to_retrieve = array_add($retailers,count($retailers),auth()->user()->id);
            $query->whereIn('payments.user_id',$retailers);
        }elseif(auth()->user()->group_id == 4){
            $query->whereIn('payments.user_id',[auth()->user()->id]);
        }else{
            $query->where(function ($query) {
                $query->where('users.parent_id', '=', '0')
                    ->orWhereNull('users.parent_id');
            });
        }
        $payments = $query;
        return Datatables::of($payments)
            ->filter(function ($query) use ($request) {
                if (!empty($request->input('retailer_id'))) {
                    $query->whereIn('users.id',$request->input('retailer_id'));
                }
                if (!empty($request->input('from_date')) && !empty($request->input('to_date'))) {
                    $from_date = $request->input('from_date').' 00:00:00';
                    $to_date = $request->input('to_date').' 23:59:59';
                    $query->whereBetween('payments.date',[$from_date,$to_date]);
                }
            })
            ->make(true);

    }

    /**
     * View Add New payment
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function add_payment()
    {
        $retailer_qry = User::join('transactions','transactions.user_id','users.id')
            ->where('status',1);
        if(in_array(auth()->user()->group_id,[1,2])){
            $child = User::where('id',auth()->user()->id)->with('children')->first();
            $retailers = $child->children->pluck('id')->flatten()->toArray();
            $retailer_qry->whereIn('users.id',$retailers);
        }else{
            $child = User::where('parent_id',auth()->user()->id)->get();
            $retailers = $child->pluck('id')->flatten()->toArray();
            $retailer_qry->whereIn('users.id',$retailers);
        }
        $retailer_qry->whereNotIn('users.group_id',[1,2,6]); // to filter administrators
        $retailer_qry->whereRaw('transactions.id = (select max(`id`) from transactions where transactions.user_id = users.id)');
        $users = $retailer_qry->select('users.id','users.username','users.currency','transactions.balance')->get();
//        dd($users);
        $payments = Payment::join('transactions','transactions.id','payments.transaction_id')
            ->join('users','users.id','payments.user_id')
            ->select([
                'payments.date',
                'users.id',
                'users.username',
                'users.cust_id',
                'payments.amount',
                'transactions.prev_bal',
                'transactions.balance',
                'payments.description',
                'payments.received_by'
            ])->whereIn('users.id',$retailers)->orderBy('payments.id',"DESC")->whereBetween('payments.date',[date('Y-m-d')." 00:00:00",date('Y-m-d')." 23:59:59"])->take(20)->get();
        $intiated_payment = Payment::join('users', 'users.id', 'payments.user_id')
            ->select([
                'payments.date',
                'users.id',
                'users.username',
                'users.cust_id',
                'payments.amount',
                'payments.description',
                'payments.received_by'
            ])->Where('payments.transaction_id', NULL)
            ->whereIn('users.id', $retailers)->orderBy('payments.id', "DESC")
            ->whereBetween('payments.date', [date('Y-m-d') . " 00:00:00", date('Y-m-d') . " 23:59:59"])
            ->take(20)->get();

        $page_data = [
            'page_title' => trans('common.update_payment'),
            'retailers' => $users,
            'payments' => $payments,
            'intiated_payment' => $intiated_payment
        ];
        return view('app.payments.update', $page_data);
    }

    /**
     * POST update payment
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    function update_payment(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(),[
            'retailer_id' => 'required',
            'amount' => 'required'
        ]);
        if ($validator->fails()) {
            AppHelper::logger('warning','Payment Update Validation','Validation failed',$request->all());
            $html = AppHelper::create_error_bag($validator);
            return redirect()->back()
                ->with('message',$html)
                ->with('message_type','warning');
        }
        $user = User::find($request->retailer_id);
        $old_user_balance = AppHelper::getBalance($user->id,$user->currency,false);
        $payment_id = Payment::insertGetId([
            'user_id' => $user->id,
            'transaction_id' => NULL,
            'date' => date('Y-m-d H:i:s'),
            'amount' => $request->amount,
            'description' => $request->description,
            'received_by' => auth()->user()->id
        ]);
        $payment = Payment::find($payment_id);
        event(new PaymentReceived($payment));
        $emails = explode(',',PAYMENT_EMAILS);
        $send_email_order_data = array(
            'updater' => auth()->user()->username,
            'amount' => $request->amount,
            'reseller_name' => $user->username,
            'desc' => $request->description
        );
        \Mail::send('emails.payment_added', $send_email_order_data, function ($message) use ($emails) {
            $message->from('noreply@tamaexpress.com', 'Tama Retailer');
            $message->to($emails)->subject('Payment Added');
        });
        AppHelper::logger('success', 'Payment Update', $user->username . ' payment was updated by ' . auth()->user()->username);
        return redirect()->back()
            ->with('message',trans('common.payment_updated_message'))
            ->with('message_type','success');
    }


    /**
     * View My Payments
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function myPayments()
    {
        $page_data = [
            'page_title' => trans('common.my_payments')
        ];
        if(auth()->user()->group_id == 2){
            try{
                $response = $this->client->request('GET', 'payments', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer " . API_TOKEN
                    ]
                ]);
                if ($response->getStatusCode() == 200) {
                    $data = json_decode((string)$response->getBody(), true);
//                    dd($data);
                    $page_data['payments']  = $data['data'];
                    $page_data['payments_paginator']  = $data['meta'];
                } else {
                    Log::warning('MY PAYMENTS API HTTP Status ' . $response->getStatusCode());
                    $page_data['payments'] = [];
                }
            }catch (\Exception $e){
                Log::warning('MY PAYMENTS API HTTP Exception ' . $e->getMessage());
                $page_data['payments'] = [];
            }
        }
//        dd($page_data);
        return view('app.payments.mypayments',$page_data);
    }

    /**
     * Ajax Get All My Payments
     * @param Request $request
     * @return mixed
     */
    function getMyPayments(Request $request)
    {
        $query = Payment::join('transactions','transactions.id','payments.transaction_id')
            ->join('users','users.id','payments.user_id')
            ->select([
                'payments.date',
                'users.username',
                'users.cust_id',
                'payments.amount',
                'transactions.prev_bal',
                'transactions.balance',
                'payments.description',
                'payments.received_by'
            ]);
        $query->where('payments.user_id',auth()->user()->id);
        $payments = $query;
        return Datatables::of($payments)
            ->filter(function ($query) use ($request) {
                if (!empty($request->input('retailer_id'))) {
                    $query->whereIn('users.id',$request->input('retailer_id'));
                }
                if (!empty($request->input('from_date')) && !empty($request->input('to_date'))) {
                    $from_date = $request->input('from_date').' 00:00:00';
                    $to_date = $request->input('to_date').' 23:59:59';
                    $query->whereBetween('payments.date',[$from_date,$to_date]);
                }
            })
            ->make(true);

    }
    function add_limit()
    {
        $retailer_qry = User::where('users.status',1);
        if(auth()->user()->group_id == 3){
            $child = User::where('id',auth()->user()->id)->with('children')->first();
            $retailers = $child->children->pluck('id')->flatten()->toArray();
            $retailer_qry->whereIn('users.id',$retailers);
        }else{
            $retailer_qry->where(function ($query) {
                $query->where('users.parent_id', '=', '0')
                    ->orWhereNull('users.parent_id');
            });
            $child = User::where('users.parent_id', '=', '0')
                ->orWhereNull('users.parent_id')->get();
            $retailers = $child->pluck('id')->flatten()->toArray();
        }
        $retailer_qry->whereNotIn('users.group_id',[1,2,6]); // to filter administrators
        $users = $retailer_qry->select('users.id','users.username','users.currency')->get();
//        dd($users);

        $page_data = [
            'page_title' => trans('common.update_limit'),
            'retailers' => $users,
        ];
        return view('app.limits.update',$page_data);
    }
    function update_limit(Request $request)
    {
        $from = date("Y-m-d  00:00:00.000000'");
        $to = date("Y-m-d  23:59:59.999999'");
        $result = \App\Models\Transaction::where('user_id', $request->retailer_id)->where('type','debit')->whereBetween('created_at', [$from, $to])->orderBy('id', 'DESC')->get();
//        dd($result->sum('amount'));
        if($result->sum('amount') >= $request->amount)
        {
            AppHelper::logger('warning','Low Limit Problem','Limit failed',$request->all());
            return redirect()->back()
                ->with('message',trans('common.msg_update_error'))
                ->with('message_type','warning');
        }
        $min_limit =$request->current_balance + $request->remaining_bal;
        if($min_limit >= $request->amount)
        {
            AppHelper::logger('warning','Low Limit Problem','Limit failed',$request->all());
            return redirect()->back()
                ->with('message',trans('common.msg_update_error'))
                ->with('message_type','warning');
        }
        $user = User::find($request->retailer_id);
        $emails = explode(',',PAYMENT_EMAILS);
        $send_email_order_data = array(
            'updater' => auth()->user()->username,
            'amount' => $request->amount,
            'reseller_name' => $user->username,
            'current_balance' => $request->current_balance,
            'remaining_bal' => $request->remaining_bal,
        );

        $validator = Validator::make($request->all(),[
            'retailer_id' => 'required',
            'amount' => 'required'
        ]);
        if ($validator->fails()) {
            AppHelper::logger('warning','Limit Update Validation','Validation failed',$request->all());
            $html = AppHelper::create_error_bag($validator);
            return redirect()->back()
                ->with('message',$html)
                ->with('message_type','warning');
        }
        $user = User::find($request->retailer_id);
        try{
            \DB::beginTransaction();
            $daily_limit = DailyLimit::where('user_id', $request->retailer_id)->first();
            if (!empty($daily_limit)) {
                DailyLimit::where('id', $daily_limit->id)->where('user_id', $request->retailer_id)->update([
                    'daily_limit' => $request->amount,
                    'updated_at' => date("Y-m-d H:i:s"),
                    'updated_by' => auth()->user()->id
                ]);
            } else {
                //insert as new
                DailyLimit::insert([
                    'type' => 'credit',
                    'user_id' => $request->retailer_id,
                    'daily_limit' =>  $request->amount,
                    'created_at' => date("Y-m-d H:i:s"),
                    'created_by' => auth()->user()->id
                ]);
            }
            \DB::commit();
            \Mail::send('emails.daily_limit_update', $send_email_order_data, function ($message) use ($emails) {
                $message->from('noreply@tamaexpress.com', 'Tama Retailer');
                $message->to($emails)->subject('Updated Daily Limit');
            });
            AppHelper::logger('success','Limit Update',$user->username.' Limit was updated by '.auth()->user()->username);
            return redirect()->back()
                ->with('message',trans('common.limit_update_message'))
                ->with('message_type','success');
        }catch (\Exception $e){
            AppHelper::logger('warning','Limit Update Exception',$e->getMessage());
            return redirect()->back()
                ->with('message',trans('common.msg_update_error'))
                ->with('message_type','warning');
        }
    }

    function delete_limit(Request $request)
    {
//        dd($request->id_retailer);
        $post =DailyLimit::where('user_id',$request->id_retailer)->first();
        $user =User::where('id',$request->id_retailer)->first();
        if ($post != null) {
            $post->delete();
            AppHelper::logger('success','Limit Deleted',$user->username.' Limit was Deleted by '.auth()->user()->username);
            return redirect()->back()
                ->with('message','Limit was removed')
                ->with('message_type','success');
        }
        AppHelper::logger('warning','Limit Not Set For This User',$user->username);
        return redirect()->back()
            ->with('message','Limit Not Set For This User')
            ->with('message_type','success');

    }
}