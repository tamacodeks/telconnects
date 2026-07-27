<?php

namespace App\Http\Controllers\Service\V2;

use App\Http\Controllers\Api\DematSoapBimediaController;
use App\Http\Controllers\Api\DematSoapController;
use App\Http\Controllers\Controller;
use app\Library\ApiHelper;
use app\Library\AppHelper;
use app\Library\SecurityHelper;
use app\Library\ServiceHelper;
use App\Models\CallingCard;
use App\Models\CallingCardAccess;
use App\Models\CallingCardPin;
use App\Models\CallingCardTransaction;
use App\Models\Bimedia_statistics;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PinHistory;
use App\Models\SeriveProvider;
use App\Models\TelecomProvider;
use App\Models\TelecomProviderConfig;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class CallingCardV2Controller extends Controller
{
    private $decipher;

    public function __construct()
    {
        parent::__construct();
        $this->decipher = new SecurityHelper();
    }

    public function index()
    {
        return view('service.calling-card-v2.index', [
            'page_title' => 'Calling Cards V2',
        ]);
    }

    private function noCardFoundMessage()
    {
        $message = trans('myservice.no_card_found');

        return $message === 'myservice.no_card_found'
            ? 'No card stock available for this selection.'
            : $message;
    }

    private function unableToPrintMessage()
    {
        $message = trans('myservice.unable_to_print');

        return $message === 'myservice.unable_to_print'
            ? 'Unable to print this card right now.'
            : $message;
    }

    public function providers()
    {
        $operator = SeriveProvider::select('primary')->first();
        if ($operator && $operator->primary == 'Aleda') {
            $providers = TelecomProviderConfig::select('id', 'name')->get();
        } else {
            $providers = TelecomProviderConfig::where('bimedia_card', 1)
                ->select('id', 'name')
                ->orderBy('ordering')
                ->get();
        }

        $data = $providers->map(function ($item) {
            $src_img = $item->getMedia('telecom_providers')->first();
            $hasImage = !empty($src_img);
            $img = $hasImage ? asset(optional($src_img)->getUrl('thumb')) : '';
            return [
                'id' => $this->decipher->encrypt($item->id),
                'name' => $item->name,
                'image' => $img,
                'has_image' => $hasImage
            ];
        });

        return ApiHelper::response('200', 200, 'providers', $data);
    }

    public function cards($enc_id)
    {
        $dec_id = $this->decipher->decrypt($enc_id);
        $operator = SeriveProvider::select('primary')->first();
        if ($operator && $operator->primary == 'Aleda') {
            $cards = TelecomProvider::where('tp_config_id', $dec_id)
                ->select('id', 'name', 'description', 'face_value')
                ->orderBy('ordering', 'ASC')
                ->get();
        } else {
            $cards = TelecomProvider::where('tp_config_id', $dec_id)
                ->where('bimedia_card', 1)
                ->select('id', 'name', 'description', 'face_value', 'is_card')
                ->orderBy('ordering', 'ASC')
                ->get();
        }

        $stockStatuses = $this->cardStockStatuses($cards);
        $callingCards = $this->callingCardsForProviders($cards);

        $data = $cards->map(function ($item) use ($stockStatuses, $callingCards, $operator) {
            $src_img = $item->getMedia('telecom_providers_cards')->first();
            $hasImage = !empty($src_img);
            $img = $hasImage ? asset(optional($src_img)->getUrl()) : '';
            $stockStatus = $this->stockStatusForCard($item, $stockStatuses, $callingCards, $operator);
            return [
                'id' => $this->decipher->encrypt($item->id),
                'name' => $item->name,
                'description' => $item->description,
                'face_value' => $item->face_value,
                'image' => $img,
                'has_image' => $hasImage,
                'is_card' => isset($item->is_card) ? $item->is_card : '0',
                'stock_status' => $stockStatus['status'],
                'stock_label' => $stockStatus['label'],
                'stock_count' => $stockStatus['count']
            ];
        });

        return ApiHelper::response('200', 200, 'cards', [
            'provider_id' => $enc_id,
            'cards' => $data
        ]);
    }

    private function cardStockStatuses($cards)
    {
        $cardIds = $cards->pluck('id')->all();
        if (empty($cardIds)) {
            return collect();
        }

        $userId = (int) auth()->user()->id;

        return CallingCard::join('calling_card_pins', 'calling_card_pins.cc_id', 'calling_cards.id')
            ->whereIn('calling_cards.telecom_provider_id', $cardIds)
            ->where('calling_cards.status', '1')
            ->where('calling_card_pins.is_used', '0')
            ->select([
                'calling_cards.telecom_provider_id',
                \DB::raw('SUM(CASE WHEN calling_card_pins.is_blocked = 0 THEN 1 ELSE 0 END) as unused_count'),
                \DB::raw('SUM(CASE WHEN calling_card_pins.is_locked = 0 THEN 1 ELSE 0 END) as available_count'),
                \DB::raw("SUM(CASE WHEN calling_card_pins.is_locked = 1 AND calling_card_pins.locked_by = {$userId} THEN 1 ELSE 0 END) as locked_by_user_count"),
                \DB::raw('SUM(CASE WHEN calling_card_pins.is_locked = 1 THEN 1 ELSE 0 END) as locked_count')
            ])
            ->groupBy('calling_cards.telecom_provider_id')
            ->get()
            ->keyBy('telecom_provider_id');
    }

    private function callingCardsForProviders($cards)
    {
        $cardIds = $cards->pluck('id')->all();
        if (empty($cardIds)) {
            return collect();
        }

        return CallingCard::whereIn('telecom_provider_id', $cardIds)
            ->where('status', '1')
            ->select('id', 'telecom_provider_id', 'activate', 'bimedia_product_code')
            ->orderBy('id', 'ASC')
            ->get()
            ->keyBy('telecom_provider_id');
    }

    private function stockStatusForCard(TelecomProvider $provider, $stockStatuses, $callingCards, $operator)
    {
        if (isset($provider->is_card) && (string) $provider->is_card === '1') {
            return [
                'status' => 'live',
                'label' => 'Live print',
                'count' => null
            ];
        }

        $operatorName = $operator ? $operator->primary : null;
        $callingCard = $callingCards->get($provider->id);
        if ($operatorName !== 'Aleda') {
            if (!$callingCard) {
                return [
                    'status' => 'out',
                    'label' => 'Not configured',
                    'count' => 0
                ];
            }

            if ((string) $callingCard->activate !== '1' && !empty($callingCard->bimedia_product_code)) {
                return [
                    'status' => 'live',
                    'label' => 'Bimedia live',
                    'count' => null
                ];
            }

            if ((string) $callingCard->activate !== '1') {
                return [
                    'status' => 'out',
                    'label' => 'Not configured',
                    'count' => 0
                ];
            }

            $stock = $stockStatuses->get($provider->id);
            if ($stock && (int) $stock->unused_count > 0) {
                return [
                    'status' => 'available',
                    'label' => 'Local DB',
                    'count' => (int) $stock->unused_count
                ];
            }
        }

        $stock = $stockStatuses->get($provider->id);
        if ($stock && (int) $stock->available_count > 0) {
            return [
                'status' => 'available',
                'label' => 'Available',
                'count' => (int) $stock->available_count
            ];
        }

        if ($stock && ((int) $stock->locked_by_user_count > 0 || (int) $stock->locked_count > 0)) {
            return [
                'status' => 'locked',
                'label' => 'Locked',
                'count' => (int) $stock->locked_count
            ];
        }

        return [
            'status' => 'out',
            'label' => 'Out of stock',
            'count' => 0
        ];
    }

    public function cardInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => 'required'
        ], [
            'card_id.required' => $this->unableToPrintMessage()
        ]);
        if ($validator->fails()) {
            return ApiHelper::response('400', 200, AppHelper::create_error_bag($validator));
        }

        $dec_id = $this->decipher->decrypt($request->card_id);
        $provider = TelecomProvider::find($dec_id);
        if (!$provider) {
            return ApiHelper::response('404', 200, $this->noCardFoundMessage());
        }

        $operator = SeriveProvider::select('primary')->first();
        if (isset($provider->is_card) && $provider->is_card == '1') {
            return $this->myCardInfo($provider);
        }
        if ($operator && $operator->primary == 'Aleda') {
            return $this->aledaInfo($provider);
        }
        return $this->bimediaInfo($provider);
    }

    private function myCardInfo(TelecomProvider $provider)
    {
        $data = [
            'cus_id' => auth()->user()->cust_id,
            'telecom_provider_id' => $provider->id,
            'face_value' => $provider->face_value,
            'description' => $provider->description
        ];

        try {
            $client = new Client([
                'base_uri' => API_END_POINT,
                'timeout' => 120,
            ]);
            $ccResponse = $client->request('POST', 'Mycards', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
                'form_params' => $data
            ]);
            if ($ccResponse->getStatusCode() != 200) {
                return ApiHelper::response('503', 200, $this->unableToPrintMessage());
            }
            $response_data = json_decode((string)$ccResponse->getBody(), true);
            $response = $response_data['data']['result']['card_info'];
        } catch (\Exception $e) {
            Log::warning('MyCard API error: ' . $e->getMessage());
            return ApiHelper::response('503', 200, $this->unableToPrintMessage());
        }

        $src_img = $provider->getMedia('telecom_providers_cards')->first();
        $hasImage = !empty($src_img);
        $img = $hasImage ? asset(optional($src_img)->getUrl('thumb')) : '';

        return ApiHelper::response('200', 200, 'card', [
            'service' => 'mycard',
            'card' => [
                'name' => $response['name'],
                'description' => $response['description'],
                'access_number' => $response['access_number'],
                'validity' => $response['validity'],
                'comment_1' => $response['comment_1'],
                'comment_2' => $response['comment_2'],
                'image' => $img,
                'has_image' => $hasImage
            ],
            'print' => [
                'pin_id' => $response['ccp_id'],
                'telecom_provider_id' => $provider->id,
                'face_value' => $provider->face_value
            ]
        ]);
    }

    private function aledaInfo(TelecomProvider $provider)
    {
        $dec_id = $provider->id;
        $check_card = CallingCard::join('calling_card_pins', 'calling_card_pins.cc_id', 'calling_cards.id')
            ->where('calling_cards.telecom_provider_id', $dec_id)
            ->where('calling_cards.status', '1')
            ->where('calling_card_pins.is_used', '0')
            ->where('calling_card_pins.is_locked', '=', '1')
            ->where('calling_card_pins.locked_by', '=', auth()->user()->id)
            ->orderBy('calling_card_pins.id', 'ASC')
            ->select([
                'calling_cards.id as cc_id',
                'calling_cards.name',
                'calling_cards.description',
                'calling_cards.validity',
                'calling_cards.access_number',
                'calling_cards.comment_1',
                'calling_cards.comment_2',
                'calling_cards.buying_price',
                'calling_cards.face_value',
                'calling_cards.aleda_product_code',
                'calling_cards.telecom_provider_id',
                'calling_card_pins.id as ccp_id',
            ])
            ->first();
        if ($check_card) {
            return $this->formatCardInfo($check_card, 'aleda');
        }

        $card = CallingCard::join('calling_card_pins', 'calling_card_pins.cc_id', 'calling_cards.id')
            ->where('calling_cards.telecom_provider_id', $dec_id)
            ->where('calling_card_pins.is_used', '0')
            ->where('calling_cards.status', '1')
            ->where('calling_card_pins.is_locked', '=', '0')
            ->select([
                'calling_cards.id as cc_id',
                'calling_cards.name',
                'calling_cards.description',
                'calling_cards.validity',
                'calling_cards.access_number',
                'calling_cards.comment_1',
                'calling_cards.comment_2',
                'calling_cards.face_value',
                'calling_cards.aleda_product_code',
                'calling_cards.telecom_provider_id',
                'calling_card_pins.id as ccp_id',
            ])
            ->first();

        if (!$card) {
            $dematSoap = new DematSoapController();
            $balance = $dematSoap->getIncurBalance();
            if (empty($balance) || is_numeric($balance) == false) {
                return ApiHelper::response('503', 200, $this->noCardFoundMessage());
            }
            $balance = number_format(($balance / 100), 2, '.', '');
            $card = CallingCard::join('calling_card_pins', 'calling_card_pins.cc_id', 'calling_cards.id')
                ->where('calling_cards.telecom_provider_id', $dec_id)
                ->where('calling_cards.status', '1')
                ->select([
                    'calling_cards.id as cc_id',
                    'calling_cards.name',
                    'calling_cards.description',
                    'calling_cards.validity',
                    'calling_cards.access_number',
                    'calling_cards.comment_1',
                    'calling_cards.comment_2',
                    'calling_cards.face_value',
                    'calling_cards.aleda_product_code',
                    'calling_cards.telecom_provider_id',
                    'calling_card_pins.id as ccp_id',
                ])
                ->first();
            if (!$card || $balance < $card->face_value || empty($card->aleda_product_code)) {
                return ApiHelper::response('503', 200, $this->noCardFoundMessage());
            }
            return $this->formatCardInfo($card, 'aleda');
        }

        $user_cc_access = CallingCardAccess::where('user_id', auth()->user()->id)
            ->where('cc_id', $card->cc_id)
            ->where('status', 1)
            ->first();
        if (!$user_cc_access) {
            return ApiHelper::response('403', 200, trans('common.access_violation'));
        }
        if (ServiceHelper::check_user_rate_table(auth()->user()->id, $card->cc_id)) {
            return ApiHelper::response('403', 200, trans('myservice.contact_admin'));
        }
        $parent_user = User::find(auth()->user()->parent_id);
        if ($parent_user && $parent_user->group_id != 2) {
            if (ServiceHelper::check_user_rate_table($parent_user->id, $card->cc_id)) {
                return ApiHelper::response('403', 200, trans('myservice.contact_admin'));
            }
        }

        CallingCardPin::where('id', $card->ccp_id)->update([
            'is_locked' => 1,
            'locked_by' => auth()->user()->id,
            'locked_at' => date('Y-m-d H:i:s')
        ]);
        Log::info($card->name . "(" . $card->cc_id . ") card locked by " . auth()->user()->username);

        return $this->formatCardInfo($card, 'aleda');
    }

    private function bimediaInfo(TelecomProvider $provider)
    {
        $card = $this->bimediaCallingCard($provider->id);
        if (!$card) {
            return ApiHelper::response('503', 200, $this->noCardFoundMessage());
        }

        $printMode = (string) $card->activate === '1' ? 'local' : 'bimedia';
        if ($printMode === 'local' && !$this->availableLocalPin($card->cc_id)) {
            return ApiHelper::response('503', 200, $this->noCardFoundMessage());
        }

        if ($printMode === 'bimedia' && empty($card->bimedia_product_code)) {
            return ApiHelper::response('503', 200, $this->unableToPrintMessage());
        }

        return $this->formatCardInfo($card, 'bimedia', [
            'print_mode' => $printMode
        ]);
    }

    private function bimediaCallingCard($telecomProviderId)
    {
        return CallingCard::where('telecom_provider_id', $telecomProviderId)
            ->where('status', '1')
            ->orderBy('id', 'ASC')
            ->select([
                'id as cc_id',
                'name',
                'description',
                'validity',
                'access_number',
                'comment_1',
                'comment_2',
                'buying_price',
                'buying_price1',
                'face_value',
                'telecom_provider_id',
                'activate',
                'bimedia_product_code',
            ])
            ->first();
    }

    private function availableLocalPin($ccId)
    {
        return CallingCardPin::where('cc_id', $ccId)
            ->where('is_used', '0')
            ->where('is_blocked', '0')
            ->orderBy('id', 'ASC')
            ->first();
    }

    public function printBimedia(Request $request)
    {
        $operator = SeriveProvider::select('primary')->first();
        if ($operator && $operator->primary == 'Aleda') {
            return ApiHelper::response('400', 200, trans('common.access_violation'));
        }

        $validator = Validator::make($request->all(), [
            'card_id' => 'required'
        ], [
            'card_id.required' => $this->unableToPrintMessage()
        ]);
        if ($validator->fails()) {
            return ApiHelper::response('400', 200, AppHelper::create_error_bag($validator));
        }

        $providerId = $this->decipher->decrypt($request->card_id);
        $provider = TelecomProvider::find($providerId);
        if (!$provider || (isset($provider->is_card) && (string) $provider->is_card === '1')) {
            return ApiHelper::response('404', 200, $this->noCardFoundMessage());
        }

        $card = $this->bimediaCallingCard($provider->id);
        if (!$card) {
            return ApiHelper::response('503', 200, $this->noCardFoundMessage());
        }

        if ((string) $card->activate === '1') {
            return $this->printLocalBimediaCard($card, $request->all());
        }

        if (empty($card->bimedia_product_code)) {
            return ApiHelper::response('404', 200, $this->unableToPrintMessage());
        }

        $dematSoap = new DematSoapBimediaController();
        $bimediaBalance = $dematSoap->FetchBalance();
        if ($bimediaBalance == false) {
            return ApiHelper::response('503', 200, $this->unableToPrintMessage());
        }

        return $this->printLiveBimediaCard($card, $bimediaBalance, $request->all());
    }

    private function printLocalBimediaCard($card, $requestData)
    {
        $pin = $this->availableLocalPin($card->cc_id);
        if (!$pin) {
            AppHelper::logger('warning', 'Calling Cards V2', 'No local Bimedia PIN available', $requestData);
            return ApiHelper::response('503', 200, $this->noCardFoundMessage());
        }

        $printContext = $this->printContext($card->cc_id, $pin->value, $requestData);
        if (!is_array($printContext)) {
            return $printContext;
        }

        $pinPrintedTime = date('Y-m-d H:i:s');
        $rootTxnId = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
        $orderComment = 'Retailer ' . auth()->user()->username . ' used card ' . $pin->name . ' ' . $pin->value;
        $transactionStarted = false;

        try {
            DB::beginTransaction();
            $transactionStarted = true;

            $updated = CallingCardPin::where('id', $pin->id)
                ->where('cc_id', $card->cc_id)
                ->where('is_used', '0')
                ->where('is_blocked', '0')
                ->update([
                    'is_used' => 1,
                    'used_by' => auth()->user()->id,
                    'is_locked' => 0,
                    'locked_by' => null,
                    'locked_at' => null,
                    'updated_at' => $pinPrintedTime,
                    'updated_by' => auth()->user()->id
                ]);

            if (!$updated) {
                DB::rollBack();
                return ApiHelper::response('503', 200, $this->noCardFoundMessage());
            }

            $secretKey = SecurityHelper::decipherEncryption($pin->public_key . 'CJJbW7SaznW7cZhVzwLo');
            $decPin = SecurityHelper::tamaCipher($pin->pin, 'd', $secretKey);

            $this->createOrderRecords($card, $pin->name, $pin->value, $printContext, $pinPrintedTime, $rootTxnId, $orderComment, false);

            $masterRetailer = User::where('group_id', 2)->select('id', 'username', 'currency')->orderBy('id', 'ASC')->first();
            $oldCCServiceBalance = $this->callingCardServiceBalance($masterRetailer);
            $newCCBalance = number_format((float) $oldCCServiceBalance - (float) $card->buying_price, 2, '.', '');
            if ($masterRetailer) {
                ServiceHelper::sync_myservice_transaction($masterRetailer->id, $card->cc_id, $pinPrintedTime, 'debit', $card->buying_price, $oldCCServiceBalance, $newCCBalance, $orderComment);
            }

            PinHistory::insert([
                'cc_id' => $card->cc_id,
                'date' => $pinPrintedTime,
                'name' => $card->name,
                'pin' => $decPin,
                'serial' => $pin->serial,
                'is_aleda' => 0,
                'used_by' => auth()->user()->id
            ]);

            DB::commit();

            AppHelper::logger('success', 'Calling Cards V2', auth()->user()->username . ' printed local Bimedia pin id ' . $pin->id, $requestData);

            return ApiHelper::response('200', 200, trans('myservice.print_success'), [
                'pin' => $decPin,
                'serial' => $pin->serial,
                'time_printed' => $pinPrintedTime,
                'remain_balance' => AppHelper::getBalance(auth()->user()->id, 'EUR')
            ]);
        } catch (\Exception $e) {
            if ($transactionStarted) {
                DB::rollBack();
            }
            AppHelper::logger('warning', 'Calling Cards V2', 'Local Bimedia print exception ' . $e->getMessage(), $requestData);
            Log::emergency(auth()->user()->username . ' local Bimedia print exception => ' . $e->getMessage(), [$e]);
            return ApiHelper::response('500', 200, $this->unableToPrintMessage());
        }
    }

    private function printLiveBimediaCard($card, $balanceSnapshot, $requestData)
    {
        $printContext = $this->printContext($card->cc_id, $card->face_value, $requestData);
        if (!is_array($printContext)) {
            return $printContext;
        }

        $pinPrintedTime = date('Y-m-d H:i:s');
        $rootTxnId = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
        $orderComment = 'Retailer ' . auth()->user()->username . ' used card ' . $card->name . ' ' . $card->face_value;
        $previousBalance = isset($balanceSnapshot->max_srd, $balanceSnapshot->conso_srd)
            ? $balanceSnapshot->max_srd - $balanceSnapshot->conso_srd
            : 0;
        $transactionStarted = false;

        try {
            $bimedia = new DematSoapBimediaController();
            $dematSOAP = $bimedia->sellDematBimedia($card->bimedia_product_code);
            if (isset($dematSOAP->error)) {
                throw new \Exception($dematSOAP->error);
            }

            $decPin = $dematSOAP->codeConfidentiel;
            $decTrxref = $dematSOAP->trxref;
            $decSerial = $dematSOAP->referenceOperateur;
            $decValidityDate = $dematSOAP->dateValidite;
            if ($decPin == '' || $decSerial == '') {
                throw new \Exception('Please try again!');
            }

            DB::beginTransaction();
            $transactionStarted = true;

            $this->createOrderRecords($card, $card->name, $card->face_value, $printContext, $pinPrintedTime, $rootTxnId, $orderComment, true);

            $convDate = str_replace('/', '-', $decValidityDate);
            $validity = $decValidityDate == '' ? null : date('Y-m-d', strtotime($convDate));
            PinHistory::insert([
                'cc_id' => $card->cc_id,
                'date' => $pinPrintedTime,
                'name' => $card->name,
                'pin' => $decPin,
                'serial' => $decSerial,
                'is_aleda' => 1,
                'validity' => $validity,
                'used_by' => auth()->user()->id
            ]);

            $cacheKey = md5(vsprintf('%s', ['bimedia-Balance']));
            \Cache::forget($cacheKey);
            $fetchData = (new DematSoapBimediaController())->FetchBalance();
            $bimediaBalance = isset($fetchData->max_srd, $fetchData->conso_srd)
                ? $fetchData->max_srd - $fetchData->conso_srd
                : 0;
            $amountDetected = $previousBalance - $bimediaBalance;

            Bimedia_statistics::insert([
                'date' => $pinPrintedTime,
                'card_name' => $card->name,
                'face_value' => $card->face_value,
                'amount_deducted' => $printContext['order_amount']->sale_price,
                'bimedia_amount_deducted' => $amountDetected,
                'previous_balance' => $previousBalance,
                'new_balance' => $bimediaBalance,
                'pin' => $decPin,
                'serial' => $decSerial,
                'trxref' => $decTrxref,
                'validity' => $validity,
                'used_by' => auth()->user()->id,
                'created_at' => $pinPrintedTime,
                'created_by' => auth()->user()->id
            ]);

            $acknowledge = $bimedia->acknowledgement($dematSOAP);
            if (isset($acknowledge->error)) {
                throw new \Exception($acknowledge->error);
            }

            DB::commit();

            if (is_numeric($bimediaBalance)) {
                \Cache::put($cacheKey, AppHelper::formatAmount('EUR', number_format(($bimediaBalance / 100), 2, '.', '')), 60);
            }

            return ApiHelper::response('200', 200, trans('myservice.print_success'), [
                'pin' => $decPin,
                'serial' => $decSerial,
                'time_printed' => $pinPrintedTime,
                'validity' => $validity,
                'remain_balance' => AppHelper::getBalance(auth()->user()->id, 'EUR')
            ]);
        } catch (\Exception $e) {
            if ($transactionStarted) {
                DB::rollBack();
            }
            AppHelper::logger('warning', 'Calling Cards V2', 'Live Bimedia print exception ' . $e->getMessage(), $requestData);
            Log::emergency(auth()->user()->username . ' live Bimedia print exception => ' . $e->getMessage(), [$e]);
            return ApiHelper::response('500', 200, $this->unableToPrintMessage());
        }
    }

    private function printContext($ccId, $publicPrice, $requestData)
    {
        $checkLimit = AppHelper::get_daily_limit(auth()->user()->id);
        if ($checkLimit != null && ServiceHelper::limit_check(auth()->user()->id, $publicPrice)) {
            AppHelper::logger('warning', 'Calling Cards V2 Daily Limit Exceed', auth()->user()->username . ' daily limit exceeded for Calling Card order', $requestData);
            Log::warning('Calling Cards V2 Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
            return ApiHelper::response('400', 200, trans('common.parent_rule_failed'));
        }

        if (ServiceHelper::parent_rule_check(auth()->user()->parent_id, $publicPrice, 7)) {
            AppHelper::logger('warning', 'Calling Cards V2 Parent Rule Failed', auth()->user()->username . ' parent does not have enough balance or credit limit', $requestData);
            Log::warning('Calling Cards V2 Parent Rule Failed => ' . auth()->user()->username . ' => ' . auth()->user()->parent_id);
            return ApiHelper::response('400', 200, trans('myservice.contact_admin'));
        }

        if (ServiceHelper::check_user_rate_table(auth()->user()->id, $ccId)) {
            AppHelper::logger('warning', 'Calling Cards V2', 'Rate Table is not set for this user', $requestData);
            return ApiHelper::response('503', 200, $this->unableToPrintMessage());
        }

        $orderAmount = ServiceHelper::get_user_rate_table(auth()->user()->id, $ccId);
        $userBalance = AppHelper::getBalance(auth()->user()->id, 'EUR', false);
        $userCreditLimit = AppHelper::get_credit_limit(auth()->user()->id);
        if (!isset($orderAmount->sale_price)) {
            AppHelper::logger('warning', 'Calling Cards V2 Rate Table Sale Price Error', auth()->user()->username . ' rate table sale price may be 0', $requestData);
            return ApiHelper::response('400', 200, trans('common.service_not_avail'));
        }

        if ($userBalance < $orderAmount->sale_price && ServiceHelper::check_with_credit_limit($orderAmount->sale_price, $userBalance, $userCreditLimit) == false) {
            AppHelper::logger('warning', 'Calling Cards V2', auth()->user()->username . ' does not have enough balance or credit limit', $requestData);
            return ApiHelper::response('503', 200, trans('myservice.err_no_balance'));
        }

        $parentContext = null;
        $parentUser = User::find(auth()->user()->parent_id);
        if (!empty(auth()->user()->parent_id) && $parentUser && $parentUser->group_id != 2) {
            $parentOrderAmount = ServiceHelper::get_user_rate_table($parentUser->id, $ccId);
            $parentUserBalance = AppHelper::getBalance($parentUser->id, 'EUR', false);
            $parentCreditLimit = AppHelper::get_credit_limit($parentUser->id);
            if (!isset($parentOrderAmount->sale_price)) {
                Log::warning('Calling Cards V2 parent rate table sale price may be 0', [$requestData]);
                return ApiHelper::response('400', 200, trans('myservice.contact_admin'));
            }
            if ($parentUserBalance < $parentOrderAmount->sale_price && ServiceHelper::check_with_credit_limit($parentOrderAmount->sale_price, $parentUserBalance, $parentCreditLimit) == false) {
                Log::warning($parentUser->username . ' does not have enough balance or credit limit to confirm client Calling Card order', [$requestData]);
                return ApiHelper::response('400', 200, trans('myservice.contact_admin'));
            }
            $parentContext = [
                'user' => $parentUser,
                'order_amount' => $parentOrderAmount,
                'user_balance' => $parentUserBalance,
                'after_order_balance' => number_format((float) $parentUserBalance - (float) $parentOrderAmount->sale_price, 2, '.', '')
            ];
        }

        return [
            'order_amount' => $orderAmount,
            'user_balance' => $userBalance,
            'after_order_balance' => number_format((float) $userBalance - (float) $orderAmount->sale_price, 2, '.', ''),
            'parent' => $parentContext
        ];
    }

    private function createOrderRecords($card, $operatorName, $publicPrice, $context, $printedAt, $rootTxnId, $orderComment, $liveBimedia)
    {
        $orderAmount = $context['order_amount'];
        $transId = ServiceHelper::sync_transaction(auth()->user()->id, $printedAt, 'debit', $orderAmount->sale_price, $context['user_balance'], $context['after_order_balance'], $orderComment);
        $orderId = Order::insertGetId([
            'date' => $printedAt,
            'user_id' => auth()->user()->id,
            'service_id' => '7',
            'order_status_id' => '7',
            'txn_ref' => $rootTxnId,
            'comment' => $orderComment,
            'currency' => 'EUR',
            'public_price' => $publicPrice,
            'buying_price' => $orderAmount->buying_price,
            'order_amount' => $orderAmount->sale_price,
            'sale_margin' => $publicPrice - $orderAmount->sale_price,
            'grand_total' => $orderAmount->sale_price,
            'transaction_id' => $transId,
            'created_at' => $printedAt,
            'created_by' => auth()->user()->id
        ]);

        $orderItemId = OrderItem::insertGetId([
            'order_id' => $orderId,
            'tt_operator' => $operatorName,
            'app_currency' => 'EUR',
            'created_at' => $printedAt,
            'created_by' => auth()->user()->id
        ]);

        Order::where('id', $orderId)->update([
            'order_item_id' => $orderItemId
        ]);

        $parentBuyingPrice = $liveBimedia ? $card->buying_price1 : $card->buying_price;
        if ($context['parent']) {
            $parent = $context['parent'];
            $parentTransId = ServiceHelper::sync_transaction($parent['user']->id, $printedAt, 'debit', $parent['order_amount']->sale_price, $parent['user_balance'], $parent['after_order_balance'], $orderComment);
            Order::insertGetId([
                'date' => $printedAt,
                'user_id' => auth()->user()->id,
                'service_id' => '7',
                'order_status_id' => '7',
                'txn_ref' => $rootTxnId,
                'comment' => $orderComment,
                'currency' => 'EUR',
                'public_price' => $publicPrice,
                'buying_price' => $parent['order_amount']->sale_price,
                'order_amount' => $orderAmount->sale_price,
                'sale_margin' => $orderAmount->sale_price - $parent['order_amount']->sale_price,
                'grand_total' => $orderAmount->sale_price,
                'is_parent_order' => 1,
                'order_item_id' => $orderItemId,
                'transaction_id' => $parentTransId,
                'created_at' => $printedAt,
                'created_by' => auth()->user()->id
            ]);

            Order::insertGetId([
                'date' => $printedAt,
                'user_id' => $parent['user']->id,
                'service_id' => '7',
                'order_status_id' => '7',
                'txn_ref' => $rootTxnId,
                'comment' => $orderComment,
                'currency' => 'EUR',
                'public_price' => $publicPrice,
                'buying_price' => $parentBuyingPrice,
                'order_amount' => $parent['order_amount']->sale_price,
                'sale_margin' => $parent['order_amount']->sale_price - $parentBuyingPrice,
                'grand_total' => $parent['order_amount']->sale_price,
                'is_parent_order' => 1,
                'order_item_id' => $orderItemId,
                'created_at' => $printedAt,
                'created_by' => auth()->user()->id
            ]);
            return;
        }

        Order::insertGetId([
            'date' => $printedAt,
            'user_id' => auth()->user()->id,
            'service_id' => '7',
            'order_status_id' => '7',
            'txn_ref' => $rootTxnId,
            'comment' => $orderComment,
            'currency' => 'EUR',
            'public_price' => $publicPrice,
            'buying_price' => $parentBuyingPrice,
            'order_amount' => $orderAmount->sale_price,
            'sale_margin' => $orderAmount->sale_price - $parentBuyingPrice,
            'grand_total' => $orderAmount->sale_price,
            'is_parent_order' => 1,
            'order_item_id' => $orderItemId,
            'created_at' => $printedAt,
            'created_by' => auth()->user()->id
        ]);
    }

    private function callingCardServiceBalance($masterRetailer)
    {
        if (!$masterRetailer) {
            Log::warning('Calling Cards V2 print could not find master retailer for service balance.');
            return '0.00';
        }

        $oldCCServiceBalance = CallingCardTransaction::select('balance')
            ->lockForUpdate()
            ->where('user_id', $masterRetailer->id)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$oldCCServiceBalance) {
            Log::warning('Calling Cards V2 service balance was empty; starting ledger from zero.', [
                'master_retailer_id' => $masterRetailer->id
            ]);
            return '0.00';
        }

        return $oldCCServiceBalance->balance;
    }

    private function formatCardInfo($card, $service, array $extra = [])
    {
        $tp = isset($card->telecom_provider_id)
            ? TelecomProvider::find($card->telecom_provider_id)
            : null;
        $src_img = $tp ? $tp->getMedia('telecom_providers_cards')->first() : null;
        $hasImage = !empty($src_img);
        $img = $hasImage ? asset(optional($src_img)->getUrl('thumb')) : '';

        return ApiHelper::response('200', 200, 'card', [
            'service' => $service,
            'card' => [
                'cc_id' => $card->cc_id,
                'name' => $card->name,
                'description' => $card->description,
                'access_number' => $card->access_number,
                'validity' => $card->validity,
                'comment_1' => $card->comment_1,
                'comment_2' => $card->comment_2,
                'face_value' => $card->face_value,
                'image' => $img,
                'has_image' => $hasImage,
                'activate' => isset($card->activate) ? $card->activate : null,
                'ccp_id' => isset($card->ccp_id) ? $card->ccp_id : null
            ]
        ] + $extra);
    }
}
