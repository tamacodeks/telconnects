<?php
/**
 * Created by Decipher Lab.
 * User: Prabakar
 * Date: 17-Apr-18
 * Time: 11:52 AM
 */

namespace app\Library;


use App\Models\AppCommission;
use App\Models\CallingCardPin;
use App\Models\CallingCardTransaction;
use App\Models\CallingCardUpload;
use App\Models\Commission;
use App\Models\RateTable;
use App\Models\TrackOrder;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserRateTable;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ServiceHelper
{
    /**
     * Get Commission for the service
     * @param $user_id
     * @param $service_id
     * @return mixed
     */
    static function get_service_commission($user_id, $service_id)
    {
        $commission = Commission::where('user_id', $user_id)
            ->where('service_id', $service_id)
            ->first();
        if (!$commission) {
            //get default commission
            $default_commission = AppCommission::where('service_id', $service_id)->first();
            return $default_commission->user_def_commission;
        }
        return $commission->commission;
    }

    /**
     * Calculate the commission using price & return commission
     * @param $price
     * @param $commission
     * @return string
     */
    static function calculate_commission($price, $commission)
    {
        $percentage = ($commission / 100) * $price;
        $diff = number_format((float)$price - $percentage, 2, '.', '');
        return $diff;
    }

    /**
     * Calculate sale margin
     * @param $price
     * @param $diff
     * @return mixed
     */
    static function calculate_sale_margin($price, $diff)
    {
        return $price - $diff;
    }

    /**
     * Check parent balance and credit limit before child confirm order
     * @param $user_id
     * @param $amount
     * @return bool
     */
    static function parent_rule_check($user_id, $amount,$service_id)
    {
        $user = User::find($user_id);
        if (!$user) return false;
        if($user->group_id == 2){
            if($service_id == 7){
                return false;
            }
            $user_balance = AppHelper::getAdminBalance(false);
            $user_credit_limit = AppHelper::getAdminBalance(false,true);
        }else{
            $user_balance = AppHelper::getBalance($user_id, $user->currency, false);
            $user_credit_limit = AppHelper::get_credit_limit($user_id);
        }
        $user_level_up = User::find($user->parent_id);
        if ($user_balance >= $amount) {
            //check parent of parent again
            if (isset($user_level_up) && $user_level_up->group_id == 2){
                if (self::parent_rule_check($user_level_up->id, $amount,$service_id)) {
                    return true;
                }
                return false;
            }
            return false;
        } else {
            //check with user credit limit
            if (self::check_with_credit_limit($amount, $user_balance, $user_credit_limit) == false) {
                return true;
            }
            if (isset($user_level_up) && $user_level_up->group_id == 2){
                if (self::parent_rule_check($user_level_up->id, $amount,$service_id)) {
                    return true;
                }
                return false;
            }
            return false;
        }
    }

    static function limit_check($user_id,$amount){
        $user_balance = AppHelper::get_remaning_limit_balance($user_id);
        if(str_replace('-', '', $user_balance) >= $amount){
            return false;
        }else{
            return true;
        }
    }
    /**
     * check if order amount with in credit limit
     * @param $amount
     * @param $balance
     * @param $credit_limit
     * @return bool
     */
    static function check_with_credit_limit($amount, $balance, $credit_limit)
    {
        $res_y = $balance - $amount;
        $check_after = $credit_limit - $res_y;
        if (str_replace('-', '', $res_y) <= str_replace('-', '', $credit_limit)) {
            //so credit limit checking from here
            if ($balance < 0) {
                if (str_replace('-', '', $check_after) <= str_replace('-', '', $credit_limit)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Insert User Transaction
     * @param $user_id
     * @param $date
     * @param string $type
     * @param $amount
     * @param $prev_bal
     * @param $balance
     * @param $description
     * @return mixed
     */
    static function sync_transaction($user_id, $date, $type = 'debit', $amount, $prev_bal, $balance, $description)
    {
        $data = [
            'user_id' => $user_id,
            'date' => $date,
            'type' => $type,
            'amount' => $amount,
            'prev_bal' => $prev_bal,
            'balance' => $balance,
            'description' => $description,
            'margin' => '0.00',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $user_id,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($type == 'debit') {
            $data['debit'] = $amount;
        }
        if ($type == 'credit') {
            $data['credit'] = $amount;
        }
        return Transaction::insertGetId($data);
    }

    /**
     * Insert User Order
     * @param $user_id
     * @param $created_at
     * @param string $status_id
     * @param $trans_id
     * @param $txn_ref
     * @param $order_desc
     * @param $currency
     * @param $euro_amount
     * @param $sale_margin
     * @param $order_amount
     * @return mixed
     */
    static function save_order($comment,$created_at, $user_id, $service_id, $status_id, $trans_id, $txn_ref, $order_desc,$currency,$euro_amount,$sale_margin,$order_amount,$buying_price,$total_amount,$order_item_id,$is_parent_order,$exclude)
    {
        //save Order
        $data = [
            'date' => $created_at,
            'user_id' => $user_id,
            'service_id' => $service_id,
            'order_status_id' => $status_id,
            'transaction_id' => $trans_id,
            'txn_ref' => $txn_ref,
            'comment' => $comment,
            'currency' => $currency,
            'public_price' => $euro_amount,
            'buying_price' => $buying_price,
            'sale_margin' => $sale_margin,
            'order_amount' => $total_amount,
            'grand_total' => $total_amount,
            'is_parent_order' => $is_parent_order,
            'order_item_id' => $order_item_id,
            'created_at' => $created_at,
            'created_by' => $user_id,
        ];
        return Order::insertGetId($data);
    }

    /**
     * Insert User Order Items
     * @param $order_id
     * @param $mobile_number
     * @param $euro_amount
     * @param $mobile_operator
     * @param $ins
     * @param $link
     * @param $created_at
     * @param $user_id
     * @return mixed
     */
    static function save_orders_items($order_id, $mobile_number, $euro_amount, $mobile_operator, $ins, $link, $created_at,$user_id)
    {
        $data = [
            'order_id' => $order_id,
            'tt_mobile' => $mobile_number,
            'tt_euro_amount' => $euro_amount,
            'tt_dest_amount' => $euro_amount,
            'tt_dest_currency' => $euro_amount,
            'tt_operator' => $mobile_operator,
            'instructions' => $ins,
            'link' => $link,
            'transfer_ref' => '',
            'created_at' => $created_at,
            'created_by' => $user_id,
        ];

        return OrderItem::insertGetId($data);
    }
    /**
     * Generate Random Transaction ID
     * @param $length
     * @return string
     */
    static function genTransID($length)
    {
        do {
            $cust_id = rand(10000,99999).strtoupper(date('M')).date('His').Rand(111,999);
        } while (!empty(\App\Models\Order::where('txn_ref', '=', TRANSACTION_PREFIX.$cust_id)->first()));
        return $cust_id;
    }

    /**
     * Generate Random Pin
     * @param int $length
     * @return string
     */
    static function genRandomPin($length = 9)
    {
        do {
            $digits = '';
            $numbers = range(0, 9);
            shuffle($numbers);
            for ($i = 0; $i < $length; $i++)
                $digits .= $numbers[$i];
            $pin = $digits;
        } while (!empty(\App\Models\OrderItem::where('tama_pin', $pin)->first()));
        return $pin;
    }

    static function app_commission($service_id)
    {
        $commission = AppCommission::where('service_id', $service_id)->first();
        return optional($commission)->commission;
    }

    /**
     * TamaTopup Error Responses
     * @param $code
     * @param bool $error_code
     * @return bool|string
     */
    public static function getTransferToStatus($code, $error_code = false)
    {
        $checkInArray = array(
            '101', '204', '207', '208', '213', '214', '216', '217', '218', '221', '224', '230', '231', '232', '233', '301', '311', '701'
        );
        if (!$error_code) {
            if (in_array(intval($code), $checkInArray)) {
                return true;
            } else {
                return false;
            }
        }
        if (in_array(intval($code), $checkInArray)) {
            //we have the error code and its corresponding message what its actually denotes
            $status_arr = array(
                '101' => trans('tamatopup.number_not_recognized'),
                '204' => trans('tamatopup.not_a_prepaid'),
                '207' => trans('tamatopup.limit_exceeded'),
                '208' => trans('tamatopup.day_amount_limit_exceeded'),
                '213' => trans('tamatopup.repeated_trans'),
                '214' => trans('tamatopup.topup_refused'),
                '216' => trans('tamatopup.dest_not_activated'),
                '217' => trans('tamatopup.dest_no_expired'),
                '218' => trans('tamatopup.request_timeout'),
                '221' => trans('tamatopup.fraud_suspicion'),
                '224' => trans('tamatopup.invalid_mobile_no'),
                '230' => trans('tamatopup.recipient_max_topup'),
                '231' => trans('tamatopup.recipient_max_topup_amount'),
                '232' => trans('tamatopup.account_max_topup_number'),
                '233' => trans('tamatopup.account_max_topup_amount'),
                '301' => trans('tamatopup.denomination_not_available'),
                '311' => trans('tamatopup.operator_blocked'),
                '701' => trans('tamatopup.price_not_available'),
            );
            return strtr($code, $status_arr);
        } else {
            return trans('tamatopup.unknown_error');
        }
    }

    static function checkPinExists($provider_id, $serial, $value)
    {
        $checkAE = CallingCardPin::join('calling_cards', 'calling_cards.id', 'calling_card_pins.cc_id')
            ->where('calling_card_pins.serial', str_replace("*", '', $serial))
            ->where('calling_cards.telecom_provider_id', $provider_id)
            ->where('calling_card_pins.value', $value)
            ->first();
        if (!empty($checkAE)) {
            return true;
        } else {
            return false;
        }
    }


    static function genUpTransID($length = 6)
    {
        do {
            $digits = '';
            $numbers = range(0, 9);
            shuffle($numbers);
            for ($i = 0; $i < $length; $i++)
                $digits .= $numbers[$i];
            $cust_id = $digits;
        } while (!empty(CallingCardUpload::where('up_trans_id', '=', $cust_id)->first()));
        return $cust_id;
    }

    static function sync_myservice_transaction($user_id, $cc_id, $date, $type, $amount, $prev_bal, $balance, $desc)
    {
        $data = [
            'user_id' => $user_id,
            'cc_id' => $cc_id,
            'date' => $date,
            'type' => $type,
            'amount' => $amount,
            'prev_bal' => $prev_bal,
            'balance' => $balance,
            'description' => $desc,
            'margin' => '0.00',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $user_id
        ];
        if ($type == 'debit') {
            $data['debit'] = $amount;
        }
        if ($type == 'credit') {
            $data['credit'] = $amount;
        }
        return CallingCardTransaction::insertGetId($data);
    }


    /**
     * Check whether user has been configured with rate table or not
     * with the sale_price != 0
     * @param $user_id
     * @param $cc_id
     * @return bool
     */
    static function check_user_rate_table($user_id, $cc_id)
    {
        //Does user has a rate table? Let's check
        $user_rate_table = UserRateTable::where('user_id', $user_id)->first();
        if (!$user_rate_table) {
            return true; //block this transaction
        }
        //if the sale_price 0 block this transaction
        $rate_table = RateTable::where('cc_id', $cc_id)
            ->where('rate_group_id', $user_rate_table->rate_group_id)
            ->first();
        if ($rate_table) {
            if ($rate_table->sale_price == "0.00" || $rate_table->sale_price == "0") {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    static function get_user_rate_table($user_id, $cc_id)
    {
        //Does user has a rate table? Let's check
        $user_rate_table = UserRateTable::where('user_id', $user_id)->first();
        if (!$user_rate_table) {
            return 0; //block this transaction
        }
        //if the sale_price 0 block this transaction
        $rate_table = RateTable::where('cc_id', $cc_id)
            ->where('rate_group_id', $user_rate_table->rate_group_id)
            ->first();
        if ($rate_table) {
            if ($rate_table->sale_price == "0.00" || $rate_table->sale_price == "0") {
                return 0;
            }
            return $rate_table;
        } else {
            return 0;
        }
    }


    static function rollBackOrder($transID)
    {
        $client = new Client([
            'base_uri' => API_END_POINT,
            'timeout' => 5.0,
        ]);
        $track_status = TrackOrder::where('trans_id',$transID)->first();
        $response = $client->request('POST', 'order/rollback', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer " . API_TOKEN
            ],
            'form_params' => [
                'trans_id' => $transID,
                'order_id' => $track_status->api_order_id,
                'transaction_id' => $track_status->api_trans_id,
            ]
        ]);
        if ($response->getStatusCode() == 200) {
            Log::info('rollback return response => ',[$response->getBody()]);
            AppHelper::logger('success',"Rollback API Order","rollback order using API done ".$transID);
        }else{
            Log::emergency("Unable to rollback order using API => ".$transID);
            AppHelper::logger('warning',"Rollback API Order","Unable to rollback order using API => ".$transID);
        }
    }

}