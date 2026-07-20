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
use App\Models\SeriveProvider;
use App\Models\TelecomProvider;
use App\Models\TelecomProviderConfig;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
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

        $data = $cards->map(function ($item) use ($stockStatuses) {
            $src_img = $item->getMedia('telecom_providers_cards')->first();
            $hasImage = !empty($src_img);
            $img = $hasImage ? asset(optional($src_img)->getUrl()) : '';
            $stockStatus = $this->stockStatusForCard($item, $stockStatuses);
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
                \DB::raw('SUM(CASE WHEN calling_card_pins.is_locked = 0 THEN 1 ELSE 0 END) as available_count'),
                \DB::raw("SUM(CASE WHEN calling_card_pins.is_locked = 1 AND calling_card_pins.locked_by = {$userId} THEN 1 ELSE 0 END) as locked_by_user_count"),
                \DB::raw('SUM(CASE WHEN calling_card_pins.is_locked = 1 THEN 1 ELSE 0 END) as locked_count')
            ])
            ->groupBy('calling_cards.telecom_provider_id')
            ->get()
            ->keyBy('telecom_provider_id');
    }

    private function stockStatusForCard(TelecomProvider $provider, $stockStatuses)
    {
        if (isset($provider->is_card) && (string) $provider->is_card === '1') {
            return [
                'status' => 'live',
                'label' => 'Live print',
                'count' => null
            ];
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
                'calling_cards.telecom_provider_id',
                'calling_card_pins.id as ccp_id',
            ])
            ->first();
        if ($check_card) {
            return $this->formatCardInfo($check_card, 'bimedia');
        }

        $dematSoap = new DematSoapBimediaController();
        $bimediaBalance = $dematSoap->FetchBalance();
        if ($bimediaBalance == false) {
            $card = CallingCard::join('calling_card_pins', 'calling_card_pins.cc_id', 'calling_cards.id')
                ->where('calling_cards.telecom_provider_id', $dec_id)
                ->where('calling_card_pins.is_used', '0')
                ->where('calling_cards.status', '1')
                ->where('calling_card_pins.is_locked', '=', '0')
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
                    'calling_cards.telecom_provider_id',
                    'calling_card_pins.id as ccp_id',
                ])
                ->first();
            if (!$card) {
                return ApiHelper::response('503', 200, $this->noCardFoundMessage());
            }
            $locked = CallingCardPin::where('id', $card->ccp_id)
                ->where('is_used', '0')
                ->where('is_locked', '0')
                ->update([
                    'is_locked' => 1,
                    'locked_by' => auth()->user()->id,
                    'locked_at' => date('Y-m-d H:i:s')
                ]);
            if (!$locked) {
                return ApiHelper::response('503', 200, $this->noCardFoundMessage());
            }
            return $this->formatCardInfo($card, 'bimedia');
        }

        $card = CallingCard::join('calling_card_pins', 'calling_card_pins.cc_id', 'calling_cards.id')
            ->where('calling_cards.telecom_provider_id', $dec_id)
            ->where('calling_cards.status', '1')
            ->where('calling_card_pins.is_used', '0')
            ->where('calling_card_pins.is_locked', '=', '0')
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
                'calling_cards.telecom_provider_id',
                'calling_card_pins.id as ccp_id',
            ])
            ->first();
        if (!$card) {
            return ApiHelper::response('503', 200, $this->noCardFoundMessage());
        }
        $locked = CallingCardPin::where('id', $card->ccp_id)
            ->where('is_used', '0')
            ->where('is_locked', '0')
            ->update([
                'is_locked' => 1,
                'locked_by' => auth()->user()->id,
                'locked_at' => date('Y-m-d H:i:s')
            ]);
        if (!$locked) {
            return ApiHelper::response('503', 200, $this->noCardFoundMessage());
        }

        return $this->formatCardInfo($card, 'bimedia');
    }

    private function formatCardInfo($card, $service)
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
                'ccp_id' => $card->ccp_id,
                'name' => $card->name,
                'description' => $card->description,
                'access_number' => $card->access_number,
                'validity' => $card->validity,
                'comment_1' => $card->comment_1,
                'comment_2' => $card->comment_2,
                'face_value' => $card->face_value,
                'image' => $img,
                'has_image' => $hasImage
            ]
        ]);
    }
}
