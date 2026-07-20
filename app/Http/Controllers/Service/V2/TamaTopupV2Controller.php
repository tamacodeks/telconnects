<?php

namespace App\Http\Controllers\Service\V2;

use App\Models\Order;
use Carbon\Carbon;
use app\Library\AppHelper;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Validator;

class TamaTopupV2Controller extends TamaTopupV2BaseController
{
    public function index()
    {
        return view('service.tama-topup-v2.index', [
            'page_title' => 'TamaTopup V2',
        ]);
    }

    public function markRefreshPopupSeen()
    {
        if ((int) auth()->user()->group_id !== 4) {
            return response()->json(['success' => false], 403);
        }

        DB::table('users')
            ->where('id', auth()->id())
            ->update([
                'tt_v2_refresh_popup_seen' => 1,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function encryptReview(Request $request)
    {
        $input = $request->except('_token');
        $validator = Validator::make($input, [
            'provider' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to prepare encrypted review link.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'query' => http_build_query($this->encryptTopupInputs($input)),
        ]);
    }

    public function review(Request $request)
    {
        try {
            $this->decryptTopupRequest($request);
        } catch (\Exception $exception) {
            AppHelper::logger('warning', 'TamaTopup V2 Review Decrypt Failed', $exception->getMessage(), $request->all(), true);
            return "<h4 class='text-center'>Unable to view your order summary, Please try again later!</h4>";
        }

        $provider = $request->input('provider');
        if ($provider === 'ding') {
            return $this->reviewDing($request);
        }
        if ($provider === 'reloadly') {
            return $this->reviewReloadly($request);
        }
        if ($provider === 'tellus') {
            return $this->reviewTellus($request);
        }
        if ($provider === 'transfer') {
            return $this->reviewTransfer($request);
        }

        return "<h4 class='text-center'>Unable to view your order summary, Please try again later!</h4>";
    }

    public function route(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'countryCode' => 'required',
            'countryIso' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['route' => null], 422);
        }
        $mobileNumber = str_replace("+", "", $request->input('mobile'));
        $cacheKey = 'tama:v2:route:' . md5(json_encode([
            'mobile' => $mobileNumber,
            'countryCode' => $request->countryCode,
            'countryIso' => strtolower($request->countryIso),
        ]));
        try {
            $cached = Cache::remember($cacheKey, now()->addMinutes(2), function () use ($mobileNumber, $request) {
                $response = $this->client->request('POST', 'topup', [
                    'headers'  => [
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer " . API_TOKEN
                    ],
                    'form_params' => [
                        'mobile_number' => $mobileNumber,
                        'countryIso' => $request->countryIso,
                        'countryCode' => $request->countryCode
                    ]
                ]);
                if ($response->getStatusCode() == 200) {
                    $data = json_decode((string) $response->getBody(), true);
                    $route = isset($data['data']['route']) ? $data['data']['route'] : null;
                    $dataBundleRoute = isset($data['data']['data_bundle_route']) ? $data['data']['data_bundle_route'] : null;
                    if ($route === null) {
                        // Match v1 behavior by falling back to ding when route is not provided.
                        $route = 'reloadly';
                    }
                    return [
                        'route' => $route,
                        'data_bundle_route' => $dataBundleRoute,
                    ];
                }
                return null;
            });
        } catch (\Exception $e) {
            return response()->json(['route' => null], 500);
        }
        if (!$cached) {
            return response()->json(['route' => null], 500);
        }
        return response()->json($cached);
    }

    private function reviewDing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'countryCode' => 'required',
            'mobile' => 'required',
            'provider_country' => 'required',
            'operator' => 'required',
            'euro_amount' => 'required',
            'euro_amount_formatted' => 'required',
            'dest_amount' => 'required',
            'dest_amount_formatted' => 'required',
            'SendAmount' => 'required',
            'SendCurrencyIso' => 'required',
            'commissionRate' => 'required',
            'UatNumber' => 'required',
            'SendValueOriginal' => 'required',
            'skuCode' => 'required',
        ]);
        if ($validator->fails()) {
            return "<h4 class='text-center'>Unable to view your order summary, Please try again later!</h4>";
        }
        $replacePlus = str_replace('+', '', $request->mobile);
        if (strlen($request->countryCode) == 2) {
            $accountNumber = $request->countryCode == '33' ? str_replace($request->countryCode, $request->countryCode . '0', $replacePlus) : $replacePlus;
        } else {
            $accountNumber = $replacePlus;
        }
        $amt = $request->countryCode == '33' ? $request->euro_amount_formatted : $request->dest_amount_formatted;

        return view('service.tama-topup-v2.review', [
            'provider' => 'ding',
            'phone_no' => $accountNumber,
            'countryCode' => $request->countryCode,
            'country' => $request->provider_country,
            'operator' => $request->operator,
            'euro_amount' => $request->euro_amount_formatted,
            'dest_amount' => $amt,
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

    private function reviewReloadly(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'SendAmount' => 'required',
            'skuCode' => 'required',
            'countryCode' => 'required',
            'country' => 'required',
            'operator' => 'required',
        ]);
        if ($validator->fails()) {
            return "<h4 class='text-center'>Unable to view your order summary, Please try again later!</h4>";
        }
        $replacePlus = str_replace('+', '', $request->mobile);
        if (strlen($request->countryCode) == 2) {
            $accountNumber = $request->countryCode == '33' ? str_replace($request->countryCode, $request->countryCode . '0', $replacePlus) : $replacePlus;
        } else {
            $accountNumber = $replacePlus;
        }
        $checkConfig = Country::where('phone_code', $request->countryCode)->first();
        return view('service.tama-topup-v2.review', [
            'provider' => 'reloadly',
            'phone_no' => $accountNumber,
            'SendValue' => $request->SendAmount,
            'sendValueOriginal' => $request->sendValueOriginal,
            'skuCode' => $request->skuCode,
            'country' => $request->country,
            'operator' => $request->operator,
            'dest_amount' => $request->local_currency,
            'countryCode' => $request->countryCode,
            'currency' => $checkConfig ? $checkConfig->currency : '',
            'ISO' => $checkConfig ? $checkConfig->iso : '',
            'description' => $request->description,
        ]);
    }

    private function reviewTransfer(Request $request)
    {
        $replacePlus = str_replace('+', '', $request->mobile);
        $accountNumber = $replacePlus;
        $sender_name = AppHelper::findusername(auth()->user()->id);
        $sender_parent_name = AppHelper::findusername(auth()->user()->parent_id);
        $displayName = $request->name;
        if ($request->display_text_formatted) {
            $displayName = trim(
                ($request->operator_name ? $request->operator_name . ' ' : '') .
                    $request->display_text_formatted .
                    ($request->receiveCurrencyIso ? ' ' . $request->receiveCurrencyIso : '')
            );
        } elseif (!$displayName || $displayName === 'Open_Range' || $displayName === 'Range') {
            $displayText = $request->display_text_formatted ?: $request->display_text;
            $displayName = trim(
                ($request->operator_name ? $request->operator_name . ' ' : '') .
                    ($displayText ?: '') .
                    ($request->receiveCurrencyIso ? ' ' . $request->receiveCurrencyIso : '')
            );
        }
        return view('service.tama-topup-v2.review', [
            'provider' => 'transfer',
            'phone_no' => $accountNumber,
            'SendValue' => $request->SendValue,
            'sendValueOriginal' => $request->sendValueOriginal ?: $request->SendValue,
            'skuCode' => $request->skuCode,
            'country' => $request->sendCurrencyIso,
            'operator' => $request->sendCurrencyIso,
            'dest_amount' => $request->display_text,
            'countryCode' => $request->countryCode,
            'currency' => $request->sendCurrencyIso,
            'ISO' => $request->receiveCurrencyIso,
            'name' => $displayName,
            'operator_id' => $request->operator_id,
            'operator_name' => $request->operator_name,
            'country' => $request->country,
            'sender_name' => $sender_name->username,
            'sender_parent_name' => $sender_parent_name->username,
        ]);
    }

    private function reviewTellus(Request $request)
    {
        $sendValue = $request->input('SendValue', $request->input('SendAmount'));
        $sendValueOriginal = $request->input('sendValueOriginal', $sendValue);
        $skuCode = $request->input('skuCode', $request->input('SkuCode', $request->input('productId')));
        $localAmount = $request->input('local_amt', $request->input('localAmount', $request->input('product', $request->input('productName'))));
        $validator = Validator::make(array_merge($request->all(), [
            'SendAmount' => $sendValue,
            'sendValueOriginal' => $sendValueOriginal,
            'skuCode' => $skuCode,
            'local_amt' => $localAmount,
        ]), [
            'mobile' => 'required',
            'SendAmount' => 'required',
            'sendValueOriginal' => 'required',
            'skuCode' => 'required',
            'countryCode' => 'required',
            'country' => 'required',
            'operator' => 'required',
            'currency' => 'required',
            'local_amt' => 'required',
        ]);
        if ($validator->fails()) {
            return "<h4 class='text-center'>Unable to view your order summary, Please try again later!</h4>";
        }
        $replacePlus = str_replace('+', '', $request->mobile);
        if (strlen($request->countryCode) == 2) {
            $accountNumber = $request->countryCode == '33' ? str_replace($request->countryCode, $request->countryCode . '0', $replacePlus) : $replacePlus;
        } else {
            $accountNumber = $replacePlus;
        }
        return view('service.tama-topup-v2.review', [
            'provider' => 'tellus',
            'phone_no' => $accountNumber,
            'SendValue' => $sendValue,
            'sendValueOriginal' => $sendValueOriginal,
            'skuCode' => $skuCode,
            'country' => $request->country,
            'operator' => $request->operator,
            'dest_amount' => $localAmount,
            'countryCode' => $request->countryCode,
            'currency' => $request->currency,
            'description' => $request->description,
            'minSendValue' => $request->minSendValue,
            'maxSendValue' => $request->maxSendValue,
            'priceValue' => $request->priceValue,
            'priceValueMax' => $request->priceValueMax,
            'localAmount' => $request->localAmount,
            'localAmountMin' => $request->localAmountMin,
            'localAmountMax' => $request->localAmountMax,
            'product' => $request->product,
            'productId' => $request->input('productId', $skuCode),
            'productName' => $request->productName,
            'type' => $request->type,
            'structure' => $request->structure,
            'infoMode' => $request->infoMode,
        ]);
    }

    public function printReceipt($order_id)
    {
        $this->data['order'] = Order::join('order_items', 'order_items.id', 'orders.order_item_id')
            ->where('orders.id', $order_id)
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
        } elseif (auth()->user()->group_id == 3) {
            $orders->whereIn('users.id', $to_retrieve);
            $orders->where('orders.is_parent_order', '=', '1');
        } elseif (auth()->user()->group_id == 4) {
            $orders->whereIn('users.id', [auth()->user()->id]);
            $orders->where('orders.is_parent_order', '=', '0');
        } elseif (auth()->user()->group_id == 6) {
            $orders->where('orders.service_id', 1);
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
        ])->orderBy('orders.id', 'DESC');

        $getTransactionsAmount = collect($orders->get());
        $today = date('Y-m-d');
        $today_trans = collect($getTransactionsAmount)->filter(function ($trans) use ($today) {
            $datetime = new \DateTime($trans->date);
            $date = $datetime->format('Y-m-d');
            return strtotime($date) === strtotime($today);
        });
        $today_trans_amount = $today_trans->sum('order_amount');
        $this->data['today_transaction'] = $today_trans_amount;
        $this->data['total_orders'] = $orders->whereBetween('orders.date', [date('Y-m-d') . ' 00:00:00', date('Y-m-d') . ' 23:59:59'])->count();
        $this->data['page_title'] = trans('service.tama_order_placed_suc_callback');
        AppHelper::logger('info', 'Tama Topup V2 Print ' . auth()->user()->username, 'tamatopup printed successfully', $this->data);
        return view('service.tama-topup-v2.receipt', $this->data);
    }
}
