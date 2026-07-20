<?php

namespace App\Http\Controllers\Api;

use app\Library\ApiHelper;
use app\Library\AppHelper;
use app\Library\SecurityHelper;
use app\Library\ServiceHelper;
use App\Models\CallingCard;
use App\Models\CallingCardAccess;
use App\Models\CallingCardPin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TelecomProvider;
use App\Models\TelecomProviderConfig;
use App\Transformers\CallingcardTransformer;
use App\Transformers\PrintCardTransformer;
use App\Transformers\PrintedCardTransformer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Validator;

class CallingCardController extends Controller
{
    private $log_title;
    protected $user;

    function __construct()
    {
        parent::__construct();
        $this->log_title = "Calling Card API";
        $this->middleware(function ($request, $next) {
            $this->user = \Auth::guard('api')->user();
            if (!$this->user) {
                AppHelper::logger('warning',$this->log_title,"Authentication problem!",request()->all(),true);
                return ApiHelper::response(401,401,"Authentication required");
            }
            return $next($request);
        });
    }

    /**
     * Get List of calling cards
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    function getCallingCards($id)
    {
        $provider_name = optional(TelecomProviderConfig::find($id))->name;
        $calling_cards = TelecomProvider::where('tp_config_id',$id)->select('id','name','description','face_value')->orderBy('ordering',"ASC")->get();
        $cards = fractal($calling_cards, new CallingcardTransformer())->addMeta(['provider_name' => $provider_name])->toArray();
        return response()->json($cards);
    }


    /**
     * Get Pin information
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    function getPrintCard($id)
    {
        $provider = TelecomProvider::find($id);
        //lets check any cards are locked by this user
        // if yes return the card bypass checking the access and rate table
        $check_card = CallingCard::join('calling_card_pins','calling_card_pins.cc_id','calling_cards.id')
            ->join('telecom_providers','telecom_providers.id','calling_cards.telecom_provider_id')
            ->where('calling_cards.telecom_provider_id',$id)
            ->where('calling_cards.status','1')
            ->where('calling_card_pins.is_used','0')
            ->where('calling_card_pins.is_locked', '=', '1')
            ->where('calling_card_pins.locked_by', '=',$this->user->id)
            ->orderBy('calling_card_pins.id', 'ASC')
            ->select([
                'calling_cards.id as cc_id',
                'calling_cards.name',
                'calling_cards.face_value',
                'calling_cards.description',
                'calling_cards.validity',
                'calling_cards.access_number',
                'calling_cards.comment_1',
                'calling_cards.comment_2',
                'calling_card_pins.id as ccp_id',
                'telecom_providers.id as tp_id'
            ])
            ->first();
        if($check_card)
        {
            $card = $check_card;//return this card
            Log::info($card->name."(".$card->cc_id.") card info fetched again by ".$this->user->username.' at '.date("Y-m-d H:i:s"));
        }
        else
        {
            $card = CallingCard::join('calling_card_pins','calling_card_pins.cc_id','calling_cards.id')
                ->where('calling_cards.telecom_provider_id',$id)
                ->where('calling_card_pins.is_used','0')
                ->where('calling_cards.status','1')
                ->select([
                    'calling_cards.id as cc_id',
                    'calling_cards.name',
                    'calling_cards.face_value',
                    'calling_cards.description',
                    'calling_cards.validity',
                    'calling_cards.access_number',
                    'calling_cards.comment_1',
                    'calling_cards.comment_2',
                    'calling_card_pins.id as ccp_id',
                    'calling_cards.telecom_provider_id as tp_id'
                ])
                ->first();
            if(!$card){
                Log::info('card is not available, changing route to aleda service');
                //lets check balance with aleda service
                $dematSoap = new DematSoapController();
                $balance = $dematSoap->getIncurBalance();
                if(empty($balance) || is_numeric($balance) == false){
                    AppHelper::logger('warning',$this->log_title." ".optional($provider)->name,"Aleda balance not enough or Terminal may be resync happened!");
                    return ApiHelper::response('503',503,"Please try again!");
                }
                $balance = number_format(($balance /100), 2, '.', '');
                $card_info =  CallingCard::join('calling_card_pins','calling_card_pins.cc_id','calling_cards.id')
                    ->where('calling_cards.telecom_provider_id',$id)
                    ->where('calling_cards.status','1')
                    ->select([
                        'calling_cards.id as cc_id',
                        'calling_cards.name',
                        'calling_cards.description',
                        'calling_cards.validity',
                        'calling_cards.access_number',
                        'calling_cards.comment_1',
                        'calling_cards.comment_2',
                        'calling_card_pins.id as ccp_id',
                        'calling_cards.face_value',
                        'calling_cards.aleda_product_code',
                        'calling_cards.telecom_provider_id as tp_id'
                    ])
                    ->first();
                if($balance < 0 || $balance < $card_info->face_value || empty($card_info->aleda_product_code)){
                    AppHelper::logger('warning',$this->log_title." ".optional($provider)->name,"No card found");
                    return ApiHelper::response('503',503,trans('myservice.no_card_found'));
                }
                //balance ok
                $provider_name = $provider->name;
                $telecom_provider_id = $provider->tp_config_id;
                $cards = fractal($card_info, new PrintCardTransformer())->addMeta(['provider_name' => $provider_name,'telecom_provider_id' => $telecom_provider_id])->toArray();
                return ApiHelper::response('200',200,"card info fetched",$cards);
            }
            //lets check user has access for this calling card
            $user_cc_access = CallingCardAccess::where('user_id',$this->user->id)->where('cc_id',$card->cc_id)->where('status',1)->first();
            if(!$user_cc_access){
                //blocked user from using this card
                AppHelper::logger('warning',$this->log_title,$this->user->username." does not have access to use this card");
                return ApiHelper::response('403',403,trans('common.access_violation'));
            }
            //check this user rate table
            if(ServiceHelper::check_user_rate_table($this->user->id,$card->cc_id)){
                //blocked rate table has not found or its 0
                AppHelper::logger('warning',$this->log_title,$this->user->username." sale_price was 0.00");
                return ApiHelper::response('403',403,trans('myservice.contact_admin'));
            }
            //let's check this user parent_id rate table
            $parent_user = User::find($this->user->parent_id);
            if($parent_user){
                if($parent_user->group_id != 2){
                    if(ServiceHelper::check_user_rate_table($parent_user->id,$card->cc_id)){
                        //blocked rate table has not found or its 0
                        AppHelper::logger('warning',$this->log_title,$this->user->username."->parent->".$parent_user->username ." sale_price was 0.00");
                        return ApiHelper::response('403',403,trans('myservice.contact_admin'));
                    }
                }
            }
            //lock this card for this logged user
            CallingCardPin::where('id',$card->ccp_id)->update([
                'is_locked' => 1,
                'locked_by' => $this->user->id,
                'locked_at' => date('Y-m-d H:i:s')
            ]);
            Log::info($card->name."(".$card->cc_id.") card has been locked by ".$this->user->username.' at '.date("Y-m-d H:i:s"));
        }
        $provider_name = $provider->name;
        $telecom_provider_id = $provider->tp_config_id;
        $cards = fractal($card, new PrintCardTransformer())->addMeta(['provider_name' => $provider_name,'telecom_provider_id' => $telecom_provider_id])->toArray();
        return ApiHelper::response('200',200,"card info fetched",$cards);
    }


    function confirmPrint(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(),[
            'card_id' => 'required|exists:calling_cards,id',
            'pin_id' => 'required||exists:calling_card_pins,id',
        ],[
            "card_id.required" => trans('myservice.unable_to_print'),
            "pin_id.required" => trans('myservice.unable_to_print')
        ]);
        if($validator->fails())
        {
            AppHelper::logger('warning',$this->log_title,'Validation failed',$request->all());
            return ApiHelper::response('400',400,AppHelper::create_error_bag($validator));
        }
        //lets check the card actually locked by this user
        $card_info = CallingCardPin::where('id',$request->pin_id)
            ->where('cc_id',$request->card_id)
            ->where('is_used','0')
            ->where('is_locked',1)
            ->where('locked_by',$this->user->id)
            ->first();
        if(!$card_info){
            //lets check with aleda service
            $card_info = CallingCard::find($request->card_id);
            if(!$card_info || empty($card_info->aleda_product_code)){
                AppHelper::logger('warning',$this->log_title,'No such card was found!',$request->all());
                return ApiHelper::response('404',400,trans('myservice.unable_to_print'));
            }
            $public_price = $card_info->face_value;
            if(ServiceHelper::check_user_rate_table($this->user->id,$request->card_id)){
                AppHelper::logger('warning',$this->log_title,'Rate Table is not set for this user',$request->all());
                return ApiHelper::response('503',400,trans('myservice.unable_to_print'));
            }
            $order_amount = ServiceHelper::get_user_rate_table($this->user->id,$request->card_id);
            $user_balance = AppHelper::getBalance($this->user->id,'EUR',false);
            $user_credit_limit = AppHelper::get_credit_limit($this->user->id);
            if(isset($order_amount->sale_price)){
                if ($user_balance < $order_amount->sale_price) {
                    //check with credit limit
                    if (ServiceHelper::check_with_credit_limit($order_amount->sale_price, $user_balance, $user_credit_limit) == false) {
                        AppHelper::logger('warning',$this->log_title,$this->user->username . ' does not have enough balance or credit limit to confirm Calling Card order', $request->all());
                        return ApiHelper::response('503',503,trans('myservice.unable_to_print'));
                    }
                }
            }else{
                AppHelper::logger('warning', 'Rate Table Sale Price Error', auth()->user()->username . ' rate table sale price may be 0', $request->all());
                return ApiHelper::response('400',400,trans('common.service_not_avail'));
            }
            $pin_printed_time = date('Y-m-d H:i:s');
            $root_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
            $after_order_balance = number_format((float)$user_balance - $order_amount->sale_price, 2, '.', '');
            try{
                //check the product whether ES or AS
                $catalogue_xml = Storage::disk('public')->get('catalogue/catalogue.xml');
                $ob= simplexml_load_string($catalogue_xml);
                $json  = json_encode($ob);
                $configData = json_decode($json, true);
                $collection = collect($configData['product']);
                $filtered = $collection->whereStrict('Gencod', $card_info->aleda_product_code);
                $catalogue = $filtered->first();
                if(!$catalogue){
                    Log::emergency("Product Code not found for  ".$card_info->name." ".$card_info->aleda_product_code);
                    throw new \Exception(trans("myservice.unable_to_print"));
                }
                $aleda = new DematSoapController();
                if($catalogue['productType'] == "ES"){
                    $dematSOAP = $aleda->sellDematModeES($card_info->aleda_product_code);
                    if(isset($dematSOAP->error)){
                        throw new \Exception($dematSOAP->error);
                    }
                    $dec_pin = $dematSOAP->secretCode;
                    $dec_serial = $dematSOAP->serialNb;
                    $dec_validityDate = $dematSOAP->validityDate;
                    if($dec_pin == "" || $dec_serial == ""){
                        throw new \Exception("Please try again!");
                    }
                }elseif($catalogue['productType'] == "AS"){
                    $dematSOAP = $aleda->sellDematModeXS($card_info->aleda_product_code);
                    if(isset($dematSOAP->error)){
                        throw new \Exception($dematSOAP->error);
                    }
                    $productList = $dematSOAP->productASList;
                    $dec_pin = $productList->secretCode;
                    $dec_serial = $productList->serialNb;
                    $dec_validityDate = $productList->validityDate;
                    if($dec_pin == "" || $dec_serial == ""){
                        throw new \Exception("Please try again!");
                    }
                }else{
                    Log::emergency("Unknown Product Type for  ".$card_info->name." ".$card_info->aleda_product_code." ".$catalogue['productType']);
                    throw new \Exception(trans('myservice.unable_to_print'));
                }
                \DB::beginTransaction();
                //order comment
                $order_comment = "Retailer " . $this->user->username . " used card " . $card_info->name . " " . $card_info->face_value;
                //user order and transaction
                $trans_id = ServiceHelper::sync_transaction($this->user->id, $pin_printed_time,'debit', $order_amount->sale_price, $user_balance, $after_order_balance, $order_comment);
                $order_id = Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => $this->user->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $order_amount->buying_price,
                    'order_amount' => $order_amount->sale_price,
                    'sale_margin' => $public_price - $order_amount->sale_price,
                    'grand_total' => $order_amount->sale_price,
                    'transaction_id' => $trans_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => $this->user->id
                ]);
                $order_item_id = OrderItem::insertGetId([
                    'order_id' => $order_id,
                    'tt_operator' => $card_info->name,
                    'app_currency' => "EUR",
                    'created_at' => $pin_printed_time,
                    'created_by' => $this->user->id
                ]);
                //update the order item id to order
                Order::where('id',$order_id)->update([
                    'order_item_id' => $order_item_id
                ]);
                $parent_user = User::find($this->user->parent_id);
                $calling_card = $card_info;
                if(!empty($this->user->parent_id) && $parent_user && $parent_user->group_id != 2){
                    $parent_order_amount = ServiceHelper::get_user_rate_table($parent_user->id,$request->card_id);
                    $parent_user_balance = AppHelper::getBalance($parent_user->id,'EUR',false);
                    $parent_balance_after_order = number_format((float)$parent_user_balance - $parent_order_amount->sale_price, 2, '.', '');
                    //parent user order and transaction
                    $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $pin_printed_time,'debit', $parent_order_amount->sale_price, $parent_user_balance, $parent_balance_after_order, $order_comment);
                    //by retailer to manager
                    Order::insertGetId([
                        'date' => $pin_printed_time,
                        'user_id' => $this->user->id,
                        'service_id' => '7',
                        'order_status_id' => '7',
                        'txn_ref' => $root_txn_id,
                        'comment' => $order_comment,
                        'currency' => "EUR",
                        'public_price' => $public_price,
                        'buying_price' => $parent_order_amount->buying_price,
                        'order_amount' => $order_amount->sale_price,
                        'sale_margin' => $order_amount->sale_price - $parent_order_amount->buying_price,
                        'grand_total' => $order_amount->sale_price,
                        'is_parent_order' => 1,
                        'order_item_id' => $order_item_id,
                        'transaction_id' => $parent_trans_id,
                        'created_at' => $pin_printed_time,
                        'created_by' => $this->user->id
                    ]);
                    //by manager to dematpro
                    Order::insertGetId([
                        'date' => $pin_printed_time,
                        'user_id' => $parent_user->id,
                        'service_id' => '7',
                        'order_status_id' => '7',
                        'txn_ref' => $root_txn_id,
                        'comment' => $order_comment,
                        'currency' => "EUR",
                        'public_price' => $public_price,
                        'buying_price' => $calling_card->buying_price,
                        'order_amount' => $parent_order_amount->sale_price,
                        'sale_margin' => $parent_order_amount->sale_price - $calling_card->buying_price,
                        'grand_total' => $parent_order_amount->sale_price,
                        'is_parent_order' => 1,
                        'order_item_id' => $order_item_id,
                        'created_at' => $pin_printed_time,
                        'created_by' => $this->user->id
                    ]);
                }
                else{
                    //by user to dematpro
                    Order::insertGetId([
                        'date' => $pin_printed_time,
                        'user_id' => $this->user->id,
                        'service_id' => '7',
                        'order_status_id' => '7',
                        'txn_ref' => $root_txn_id,
                        'comment' => $order_comment,
                        'currency' => "EUR",
                        'public_price' => $public_price,
                        'buying_price' => $calling_card->buying_price,
                        'order_amount' => $order_amount->sale_price,
                        'sale_margin' => $order_amount->sale_price - $calling_card->buying_price,
                        'grand_total' => $order_amount->sale_price,
                        'is_parent_order' => 1,
                        'order_item_id' => $order_item_id,
                        'created_at' => $pin_printed_time,
                        'created_by' => $this->user->id
                    ]);
                }
                $cacheKey = md5(vsprintf("%s", [
                    "Aleda-Balance"
                ]));
                \Cache::forget($cacheKey);
                getAledaBalance:
                $dematSoap = new DematSoapController();
                $aledaBalance = $dematSoap->getIncurBalance();
                if(isset($aledaBalance->error)){
                    $aledaRemainBalance = '0.00';
                }else{
                    if(empty($aledaBalance) || is_numeric($aledaBalance) == false){
                        AppHelper::logger('warning',$this->log_title,"Terminal may be resync happened, trigger goto procedure!");
                        //sleep for 3 seconds
                        sleep(3);
                        goto getAledaBalance;
                    }else {
                        $aledaRemainBalance = AppHelper::formatAmount('EUR', number_format(($aledaBalance / 100), 2, '.', ''));
                    }
                }
                //add it cache
                \Cache::put($cacheKey, $aledaRemainBalance, 60);
                AppHelper::logger('success',$this->log_title,$this->user->username." pin id ".$request->pin_id." used success",$request->all());
                $conv_date  = str_replace("/", "-", $dec_validityDate);
                $ret_data = (object)[
                    'card_name' => $card_info->name,
                    'face_value' => $card_info->face_value,
                    'pin' => $dec_pin,
                    'serial' => $dec_serial,
                    'time_printed' => $pin_printed_time,
                    'validity' =>  $dec_validityDate == "" ? "" : date('Y-m-d', strtotime($conv_date))
                ];
                AppHelper::aledaStatistics($card_info->id,$this->user->id, $dec_serial, $dec_pin, date('Y-m-d', strtotime($conv_date)));
                Log::info('info card',[$ret_data]);
                \DB::commit();
                $cards = fractal($ret_data, new PrintedCardTransformer())->toArray();
                return response()->json($cards);
            }catch (\Exception $e){
                \DB::rollBack();
                AppHelper::logger('warning',$this->log_title,"Exception ".$e->getMessage());
                Log::emergency($this->user->username." pin print exception => ".$e->getMessage());
                return ApiHelper::response('500',500,trans('myservice.unable_to_print'));
            }
        }
        else{
            $public_price = $card_info->value;
            if(ServiceHelper::check_user_rate_table($this->user->id,$request->card_id)){
                AppHelper::logger('warning',$this->log_title,'Rate Table is not set for this user',$request->all());
                return ApiHelper::response('503',400,trans('myservice.unable_to_print'));
            }
            $order_amount = ServiceHelper::get_user_rate_table($this->user->id,$request->card_id);
            $user_balance = AppHelper::getBalance($this->user->id,'EUR',false);
            $user_credit_limit = AppHelper::get_credit_limit($this->user->id);
            if(isset($order_amount->sale_price)){
                if ($user_balance < $order_amount->sale_price) {
                    //check with credit limit
                    if (ServiceHelper::check_with_credit_limit($order_amount->sale_price, $user_balance, $user_credit_limit) == false) {
                        AppHelper::logger('warning',$this->log_title,$this->user->username . ' does not have enough balance or credit limit to confirm Calling Card order', $request->all());
                        return ApiHelper::response('503',200,trans('myservice.unable_to_print'));
                    }
                }
            }else{
                AppHelper::logger('warning', 'Rate Table Sale Price Error', auth()->user()->username . ' rate table sale price may be 0', $request->all());
                return ApiHelper::response('400',400,trans('common.service_not_avail'));
            }
            $pin_printed_time = date('Y-m-d H:i:s');
            $root_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
            $after_order_balance = number_format((float)$user_balance - $order_amount->sale_price, 2, '.', '');
            try{
                \DB::beginTransaction();
                //update pin status
                CallingCardPin::where('id',$request->pin_id)->update([
                    'is_used' => 1,
                    'used_by' => $this->user->id,
                    'is_locked' => 0,
                    'locked_by' => null,
                    'updated_at' => $pin_printed_time,
                    'updated_by' => $this->user->id
                ]);
                //decrypt the pin
                $secret_key = SecurityHelper::decipherEncryption($card_info->public_key . "CJJbW7SaznW7cZhVzwLo");
                $dec_pin = SecurityHelper::tamaCipher($card_info->pin, "d", $secret_key);
                //order comment
                $order_comment = "Retailer " . $this->user->username . " used card " . $card_info->name . " " . $card_info->value;
                //user order and transaction
                $trans_id = ServiceHelper::sync_transaction($this->user->id, $pin_printed_time,'debit', $order_amount->sale_price, $user_balance, $after_order_balance, $order_comment);
                $order_id = Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => $this->user->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $order_amount->buying_price,
                    'order_amount' => $order_amount->sale_price,
                    'sale_margin' => $public_price - $order_amount->sale_price,
                    'grand_total' => $order_amount->sale_price,
                    'transaction_id' => $trans_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => $this->user->id
                ]);
                $order_item_id = OrderItem::insertGetId([
                    'order_id' => $order_id,
                    'tt_operator' => $card_info->name,
                    'app_currency' => "EUR",
                    'created_at' => $pin_printed_time,
                    'created_by' => $this->user->id
                ]);
                $parent_user = User::find($this->user->parent_id);
                $calling_card = CallingCard::find($request->card_id);
                if(!empty($this->user->parent_id) && $parent_user && $parent_user->group_id != 2){
                    $parent_order_amount = ServiceHelper::get_user_rate_table($parent_user->id,$request->card_id);
                    $parent_user_balance = AppHelper::getBalance($parent_user->id,'EUR',false);
                    $parent_balance_after_order = number_format((float)$parent_user_balance - $parent_order_amount->sale_price, 2, '.', '');
                    //parent user order and transaction
                    $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $pin_printed_time,'debit', $parent_order_amount->sale_price, $parent_user_balance, $parent_balance_after_order, $order_comment);
                    //by retailer to manager
                    Order::insertGetId([
                        'date' => $pin_printed_time,
                        'user_id' => $this->user->id,
                        'service_id' => '7',
                        'order_status_id' => '7',
                        'txn_ref' => $root_txn_id,
                        'comment' => $order_comment,
                        'currency' => "EUR",
                        'public_price' => $public_price,
                        'buying_price' => $parent_order_amount->buying_price,
                        'order_amount' => $order_amount->sale_price,
                        'sale_margin' => $order_amount->sale_price - $parent_order_amount->buying_price,
                        'grand_total' => $order_amount->sale_price,
                        'is_parent_order' => 1,
                        'order_item_id' => $order_item_id,
                        'transaction_id' => $parent_trans_id,
                        'created_at' => $pin_printed_time,
                        'created_by' => $this->user->id
                    ]);
                    //by manager to dematpro
                    Order::insertGetId([
                        'date' => $pin_printed_time,
                        'user_id' => $parent_user->id,
                        'service_id' => '7',
                        'order_status_id' => '7',
                        'txn_ref' => $root_txn_id,
                        'comment' => $order_comment,
                        'currency' => "EUR",
                        'public_price' => $public_price,
                        'buying_price' => $calling_card->buying_price,
                        'order_amount' => $parent_order_amount->sale_price,
                        'sale_margin' => $parent_order_amount->sale_price - $calling_card->buying_price,
                        'grand_total' => $parent_order_amount->sale_price,
                        'is_parent_order' => 1,
                        'order_item_id' => $order_item_id,
                        'created_at' => $pin_printed_time,
                        'created_by' => $this->user->id
                    ]);
                }
                else{
                    //by user to dematpro
                    Order::insertGetId([
                        'date' => $pin_printed_time,
                        'user_id' => $this->user->id,
                        'service_id' => '7',
                        'order_status_id' => '7',
                        'txn_ref' => $root_txn_id,
                        'comment' => $order_comment,
                        'currency' => "EUR",
                        'public_price' => $public_price,
                        'buying_price' => $calling_card->buying_price,
                        'order_amount' => $order_amount->sale_price,
                        'sale_margin' => $order_amount->sale_price - $calling_card->buying_price,
                        'grand_total' => $order_amount->sale_price,
                        'is_parent_order' => 1,
                        'order_item_id' => $order_item_id,
                        'created_at' => $pin_printed_time,
                        'created_by' => $this->user->id
                    ]);
                }
                //finally deduct balance from myservice balance
                $master_retailer = User::where('group_id',2)->select('id','username','currency')->orderBy('id','ASC')->first();
                $oldCCServiceBalance = AppHelper::getMyServiceBalance($master_retailer->id, $master_retailer->currency, false);
                $newCCBalance = $oldCCServiceBalance - $calling_card->buying_price;
                Log::info('new balance '.$newCCBalance);
                ServiceHelper::sync_myservice_transaction($master_retailer->id, $request->card_id, $pin_printed_time, 'debit', $calling_card->buying_price, $oldCCServiceBalance, $newCCBalance, $order_comment);
                \DB::commit();
                AppHelper::logger('success',$this->log_title,$this->user->username." pin id ".$request->pin_id." used success",$request->all());
                $ret_data = (object)[
                    'card_name' => $card_info->name,
                    'face_value' => $card_info->face_value,
                    'pin' => $dec_pin,
                    'serial' => $card_info->serial,
                    'time_printed' => $pin_printed_time
                ];
                Log::info('info card',[$ret_data]);
                $cards = fractal($ret_data, new PrintedCardTransformer())->toArray();
                return response()->json($cards);
            }catch (\Exception $e){
                \DB::rollBack();
                AppHelper::logger('warning',$this->log_title,"Exception ".$e->getMessage());
                Log::emergency($this->user->username." pin print exception => ".$e->getMessage());
                return ApiHelper::response('500',500,trans('myservice.unable_to_print'));
            }
        }
    }


}
