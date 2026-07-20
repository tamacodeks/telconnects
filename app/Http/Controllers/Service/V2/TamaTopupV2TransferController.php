<?php

namespace App\Http\Controllers\Service\V2;

use app\Library\AppHelper;
use app\Library\ServiceHelper;
use App\Models\AppCommission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TrackOrder;
use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Validator;

class TamaTopupV2TransferController extends TamaTopupV2BaseController
{
    public function fetch(Request $request, $operation)
    {
        $params = [];
        $replacePlus = str_replace('+', '', $request->accountNumber);
        if (strlen($request->countryCode) == 2) {
            $accountNumber = $request->countryCode == '33' ? str_replace($request->countryCode, $request->countryCode . '0', $replacePlus) : $replacePlus;
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
                case 'providers':
                    $result = $this->callProviderApi('GET', 'topup/transfer/providers', $params);
                    break;
                case 'products':
                    $result = $this->callProviderApi('GET', 'topup/transfer/products', $params);
                    $result = $this->applyTransferRangeStepRules($result);
                    break;
            }
            return $result;
        } catch (\Exception $exception) {
            return responder()->error('EXCEPTION', $exception->getMessage() . ' line ' . $exception->getLine())->respond(400);
        }
    }

    private function applyTransferRangeStepRules($result)
    {
        if (!isset($result['data']['products']) || !is_array($result['data']['products'])) {
            return $result;
        }

        foreach ($result['data']['products'] as &$product) {
            $rangeStep = $this->transferRangeStepForProduct($product);
            if ($rangeStep === null) {
                continue;
            }
            $product['rangeStep'] = $rangeStep;
            $product['range_step'] = $rangeStep;
            $product['step'] = $rangeStep;
            $product = $this->increaseTransferRangeMinimum($product, $rangeStep);
        }
        unset($product);

        return $result;
    }

    private function increaseTransferRangeMinimum(array $product, $rangeStep)
    {
        if (!isset($product['minSendValue'])) {
            return $product;
        }

        $minSendValue = (float) $product['minSendValue'];
        $maxSendValue = isset($product['maxSendValue']) ? (float) $product['maxSendValue'] : null;
        $step = (float) $rangeStep;
        if ($step <= 0) {
            return $product;
        }

        $nextMinSendValue = $minSendValue + $step;
        if ($maxSendValue !== null && $nextMinSendValue > $maxSendValue) {
            return $product;
        }

        $product['minSendValue'] = number_format($nextMinSendValue, 2, '.', '');

        return $product;
    }

    private function transferRangeStepForProduct(array $product)
    {
        $operator = $this->normalizeMatchText(isset($product['operator_name']) ? $product['operator_name'] : (isset($product['operator']) ? $product['operator'] : (isset($product['name']) ? $product['name'] : '')));
        $country = $this->normalizeMatchText(isset($product['country']) ? $product['country'] : '');
        $countryIso = $this->normalizeMatchText(isset($product['country_iso']) ? $product['country_iso'] : '');

        $isOrangeGuinea = strpos($operator, 'orange') !== false
            && strpos($operator, 'bissau') === false
            && strpos($country, 'bissau') === false
            && ($countryIso === 'gn' || $countryIso === 'gin' || strpos($country, 'guinea') !== false || strpos($operator, 'guinea') !== false);

        $isDigicelHaiti = strpos($operator, 'digicel') !== false
            && ($countryIso === 'ht' || $countryIso === 'hti' || strpos($country, 'haiti') !== false || strpos($operator, 'haiti') !== false);

        $isBangladeshOperator = (strpos($operator, 'grameenphone') !== false || strpos($operator, 'robi') !== false)
            && ($countryIso === 'bd' || $countryIso === 'bgd' || strpos($country, 'bangladesh') !== false || strpos($operator, 'bangladesh') !== false);

        $isYasSenegal = strpos($operator, 'yas') !== false
            && ($countryIso === 'sn' || $countryIso === 'sen' || strpos($country, 'senegal') !== false || strpos($operator, 'senegal') !== false);

        if ($isOrangeGuinea) {
            return '0.30';
        }

        return ($isDigicelHaiti || $isBangladeshOperator || $isYasSenegal) ? '0.10' : null;
    }

    private function normalizeMatchText($value)
    {
        return strtolower(trim((string) $value));
    }

    public function confirm(Request $request)
    {
        try {
            $this->decryptTopupRequest($request);
        } catch (\Exception $exception) {
            AppHelper::logger('warning', 'TamaTopup V2 Confirm Decrypt Failed', $exception->getMessage(), $request->all(), true);
            return redirect('tama-topup-v2')
                ->with('message', trans('topup_v2.error_confirm_order'))
                ->with('message_type', 'warning');
        }

        AppHelper::logger('info', 'Tama Topup V2 Confirm Order ' . auth()->user()->username, 'Tama Topup Clicked order by', $request->all());
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required',
            'SkuCode' => 'required',
            'SendValue' => 'required',
            'sendValueOriginal' => 'required',
        ]);
        if ($validator->fails()) {
            $html = AppHelper::create_error_bag($validator);
            AppHelper::logger('warning', 'TamaTopup V2 Confirm Order Validation Failed', $html, $request->all());
            return redirect('tama-topup-v2')
                ->with('message', $html)
                ->with('message_type', 'warning');
        }
        $mobile_number = $request->input('mobile_number');
        $euro_amount = $request->input('SendValue');
        $local_amount = $request->input('local_amount');
        $dest_currency = $request->input('ISO');
        $country_code = $request->input('country_code');
        $country_name = $request->input('country');
        $mobile_operator = $request->input('operator_name');
        $user_info = User::find(auth()->user()->id);
        $order_comment = $user_info->username . ' Transfer to topup ' . $mobile_number . ' for ' . $euro_amount . ' destination currency is ' . $local_amount;
        $check_limit = AppHelper::get_daily_limit($user_info->id);
        if ($check_limit != null) {
            if (ServiceHelper::limit_check($user_info->id, $euro_amount)) {
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id, auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id = (auth()->user()->parent_id);
                if ($manager_id != '') {
                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email, 'balaji@prepaysolution.in'];
                } else {
                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email];
                }
                $send_email_data = [
                    'retailer_name' => auth()->user()->username,
                    'manager_name' => $result->username,
                    'current_bal' => $getBalance,
                    'total_limit' => $daily_limit,
                    'current_limit' => $blink_limit,
                ];
                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                    $message->from('noreply@tamaexpress.com', 'Tama Retailer');
                    $message->to($emails)->subject('Tama Daily Limit Alert');
                });
                AppHelper::logger('warning', 'Daily Limit Exceed', $user_info->username . 'Daily limit exceed to confirm tama topup order', $request->all());
                Log::warning('TamaTopup V2 Daily Limit Exceed => ' . $user_info->username . ' => ' . $user_info->id);
                return redirect('tama-topup-v2')
                    ->with('message', trans('common.contact_manager'))
                    ->with('message_type', 'warning');
            }
        }
        if (ServiceHelper::parent_rule_check($user_info->parent_id, $euro_amount, $this->service_id)) {
            AppHelper::logger('warning', 'Parent Rule Failed', $user_info->username . ' parent does not have enough balance or credit limit to confirm tama topup order', $request->all());
            Log::warning('TamaTopup V2 Parent Rule Failed => ' . $user_info->username . ' => ' . $user_info->parent_id);
            return redirect('tama-topup-v2')
                ->with('message', trans('common.parent_rule_failed'))
                ->with('message_type', 'warning');
        }
        $current_balance = AppHelper::getBalance($user_info->id, $user_info->currency, false);
        if ($country_code == 33) {
            $user_service_commission = 10;
        } else {
            $user_service_commission = ServiceHelper::get_service_commission($user_info->id, $this->service_id);
        }
        $order_amount = ServiceHelper::calculate_commission($euro_amount, $user_service_commission);
        $user_credit_limit = AppHelper::get_credit_limit($user_info->id);
        $sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount);
        if ($current_balance < $order_amount) {
            if (ServiceHelper::check_with_credit_limit($order_amount, $current_balance, $user_credit_limit) == false) {
                AppHelper::logger('warning', 'TamaTopup V2 Balance Error', $user_info->username . ' does not have enough balance or credit limit to confirm tamatopup order', $request->all());
                return redirect('tama-topup-v2')
                    ->with('message', trans('common.msg_order_failed_due_bal'))
                    ->with('message_type', 'warning');
            }
        }
        $transID = 'TT' . date('y') . strtoupper(date('M')) . date('d') . date('His') . rand(111, 999);
        try {
            $dingTTClient = new Client([
                'base_uri' => API_END_POINT,
                'timeout'  => 180,
            ]);
            $params = $request->except('_token');
            $response = $dingTTClient->request('POST', 'topup/confirm/transfer', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . API_TOKEN,
                ],
                'form_params' => $params,
            ]);
            if ($response->getStatusCode() == 200) {
                $response_api = json_decode((string) $response->getBody(), true);
                AppHelper::logger('info', 'TamaTopup V2 response' . auth()->user()->username, 'response from tamademat', $response_api);
                $response_data = $response_api['data'];
                if ($response_data['transaction_id'] == 000) {
                    AppHelper::logger('warning', 'Transfer TamaTopup V2 API', 'Transfer TamaTopup API HTTP Status 403', 403, true);
                    return redirect('tama-topup-v2')->with('message', trans('common.msg_order_failed'))
                        ->with('message_type', 'warning');
                } else {
                    $track_order_id = TrackOrder::insertGetId([
                        'trans_id' => $transID,
                        'user_id' => $user_info->id,
                        'api_order_id' => $response_data['order_id'],
                        'api_trans_id' => $response_data['transaction_id'],
                        'status' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => auth()->user()->id,
                        'remarks' => 'Order Transaction is about to initiate...',
                    ]);
                    $local_amount = $response_data['local_amount'];
                    $dest_currency = $response_data['dest_currency'];
                }
            }
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = json_decode((string) $response->getBody()->getContents(), true);
            AppHelper::logger('warning', 'Transfer TamaTopup V2 API', 'Transfer TamaTopup API HTTP Status ' . $e->getCode(), $responseBodyAsString, true);
            return redirect('tama-topup-v2')->with('message', trans('common.msg_order_failed'))
                ->with('message_type', 'warning');
        }
        try {
            \DB::beginTransaction();
            $tt_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
            $after_order_balance = number_format(
                (float) str_replace(',', '', $current_balance) - (float) str_replace(',', '', $order_amount),
                2,
                '.',
                ''
            );
            $order_desc = $order_comment;
            $txn_ref = isset($response_data['txn_ref']) ? $response_data['txn_ref'] : $tt_txn_id;
            $created_at = date('Y-m-d H:i:s');
            $trans_id = ServiceHelper::sync_transaction($user_info->id, $created_at, 'debit', $order_amount, $current_balance, $after_order_balance, $order_desc);
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
            $order_item_id = OrderItem::insertGetId([
                'order_id' => $order_id,
                'tt_mobile' => $mobile_number,
                'tt_euro_amount' => str_replace(',', '', $euro_amount),
                'tt_dest_amount' => str_replace(',', '', $local_amount),
                'tt_dest_currency' => $dest_currency,
                'tt_operator' => $mobile_operator,
                'transfer_ref' => $response_data['transRef'],
                'created_at' => $created_at,
                'created_by' => $user_info->id,
            ]);
            Order::where('id', $order_id)->update([
                'order_item_id' => $order_item_id,
            ]);
            $parent_user = User::find($user_info->parent_id);
            if (!empty($user_info->parent_id) && $parent_user && $parent_user->group_id != 2) {
                if ($country_code == 33) {
                    $parent_user_commission = 16;
                } else {
                    $parent_user_commission = ServiceHelper::get_service_commission($parent_user->id, $this->service_id);
                }
                $parent_current_balance = AppHelper::getBalance($parent_user->id, $parent_user->currency, false);
                $parent_actual_commission = $parent_user_commission - $user_service_commission;
                $buying_price_parent = ServiceHelper::calculate_commission($euro_amount, $parent_user_commission);

                $order_amount_parent = ServiceHelper::calculate_commission($euro_amount, $parent_actual_commission);
                $parent_sale_margin = ServiceHelper::calculate_sale_margin($order_amount, $buying_price_parent);
                $parent_after_order_balance = number_format(
                    (float) str_replace(',', '', $parent_current_balance) - (float) str_replace(',', '', $buying_price_parent),
                    2,
                    '.',
                    ''
                );
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $created_at, 'debit', $buying_price_parent, $parent_current_balance, $parent_after_order_balance, $order_desc);
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
                if ($country_code == 33) {
                    $app_commission = 18;
                } else {
                    $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                }
                $app_actual_commission = $app_commission - $parent_user_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($buying_price_parent, $buying_price_app);
                Log::info('commissions', [
                    'app commission' => $app_commission,
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
                $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                $app_actual_commission = $app_commission - $user_service_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount_app);
                Log::info('commissions', [
                    'app commission' => $app_commission,
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
            TrackOrder::where('id', $track_order_id)->update([
                'order_id' => $order_id,
                'order_status_id' => 1,
                'status' => 7,
                'remarks' => 'Topup mobile successfully!',
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id,
            ]);
            \DB::commit();
            AppHelper::logger('success', 'TamaTopup V2 Order #' . $order_id, $order_desc);
            return redirect('tama-topup-v2/print/receipt/' . $order_id)->with('message', trans('service.tama_order_placed_suc_callback'))->with('message_type', 'success');
        } catch (\Exception $e) {
            \DB::rollback();
            $exception_id = 'TTEX' . AppHelper::Numeric(5);
            TrackOrder::where('trans_id', $transID)->update([
                'status' => 0,
                'remarks' => 'Unable to place order, Exception occur => ' . $exception_id,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id,
            ]);
            $exceptions = [
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
                'Code' => $e->getCode(),
            ];
            Log::emergency(auth()->user()->username . ' TamaTopup V2 API Exception => ' . $e->getMessage());
            AppHelper::logger('warning', 'TamaTopup V2 Exception ' . $exception_id, $e->getMessage(), $exceptions);
            return redirect('tama-topup-v2')
                ->with('message', trans('common.error_confirm_order') . ' ' . $exception_id)
                ->with('message_type', 'warning');
        }
    }
}
