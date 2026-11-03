<?php

namespace App\Http\Controllers\Service\V2;

use App\Http\Controllers\Controller;
use App\Models\TopupConfirmAttempt;
use App\Models\QueuedTopup;
use App\Models\TopupProcessingSetting;
use App\Models\TrackOrder;
use App\Services\QueuedTopupDispatcher;
use App\Services\TopupWorkerHealth;
use App\Support\ClientInfo;
use app\Library\AppHelper;
use app\Library\SecurityHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TamaTopupV2BaseController extends Controller
{
    const DUPLICATE_ABUSE_THRESHOLD = 10;
    const DUPLICATE_ABUSE_WINDOW_MINUTES = 1;
    const RETAILER_TEMP_BLOCK_MINUTES = 5;

    protected $service_id = 2;
    protected $decipher;
    protected $client;

    public function __construct()
    {
        parent::__construct();
        $this->decipher = new SecurityHelper();
        $this->middleware(function ($request, $next) {
            if (API_TOKEN == '' || API_END_POINT == '') {
                AppHelper::logger('warning', 'API SETTINGS ERROR', 'Missing API Token or API end point url', request()->all(), true);
                return redirect()->back()
                    ->with('message', trans('common.access_violation'))
                    ->with('message_type', 'warning');
            }
            if (AppHelper::user_access($this->service_id, auth()->user()->id) == 0) {
                AppHelper::logger('warning', 'Access Violation', auth()->user()->username . ' trying to access tamatopup service', request()->all(), true);
                return redirect()->back()
                    ->with('message', trans('common.access_violation'))
                    ->with('message_type', 'warning');
            }
            if (\app\Library\AppHelper::skip_service_as_menu('tama-topup') == false) {
                AppHelper::logger('warning', 'Access Violation', auth()->user()->username . ' trying to access TamaTopup service but parent of this user does not have a access', request()->all());
                return redirect('dashboard')
                    ->with('message', trans('common.access_violation'))
                    ->with('message_type', 'warning');
            }
            $this->client = new Client([
                'base_uri' => API_END_POINT,
                'timeout'  => 180,
            ]);
            return $next($request);
        });
    }

    protected function callProviderApi($method, $uri, $params, array $options = [])
    {
        $clientOptions = array_merge([
            'base_uri' => API_END_POINT,
            'timeout'  => 180,
            'connect_timeout' => 10,
        ], $options);

        $client = new Client($clientOptions);
        try {
            if ($method === 'GET') {
                $response = $client->request($method, $uri . '?' . http_build_query($params), [
                    'headers' => $this->topupProviderApiHeaders(request()),
                ]);
            } else {
                $response = $client->request($method, $uri, [
                    'headers' => $this->topupProviderApiHeaders(request()),
                    'form_params' => $params,
                ]);
            }
            if ($response->getStatusCode() == 200) {
                return json_decode((string) $response->getBody(), true);
            }
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            $responseBody = $response ? $response->getBody()->getContents() : $exception->getMessage();
            return [
                'status' => $response ? $response->getStatusCode() : 500,
                'message' => $responseBody,
                'data' => [],
            ];
        }
    }

    protected function topupProviderApiHeaders(Request $request)
    {
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . API_TOKEN,
        ];

        $clientIp = ClientInfo::clientIp();
        if ($clientIp && filter_var($clientIp, FILTER_VALIDATE_IP)) {
            $headers['X-Tamashop-Client-IP'] = $clientIp;
        }

        $userAgent = $request->userAgent();
        if ($userAgent) {
            $headers['X-Tamashop-Client-User-Agent'] = Str::limit($userAgent, 500, '');
        }

        $requestId = $request->attributes->get('topup_confirm_request_id');
        if ($requestId) {
            $headers['X-Tamashop-Request-ID'] = $requestId;
        }

        return $headers;
    }

    protected function encryptTopupInputs(array $inputs, array $except = ['_token', '_method'])
    {
        $encrypted = [];
        foreach ($inputs as $key => $value) {
            if (in_array($key, $except, true)) {
                continue;
            }
            $encrypted[$key] = $this->encryptTopupValue($value);
        }

        return $encrypted;
    }

    protected function decryptTopupRequest(Request $request, array $except = ['_token', '_method'])
    {
        $request->replace($this->decryptTopupInputs($request->all(), $except));
    }

    protected function newTopupConfirmRequestId(Request $request)
    {
        $requestId = (string) Str::uuid();
        $request->attributes->set('topup_confirm_request_id', $requestId);

        return $requestId;
    }

    protected function logTopupConfirmIncoming(Request $request, $provider, $requestId = null)
    {
        $user = auth()->user();
        $requestId = $requestId ?: $request->attributes->get('topup_confirm_request_id');

        Log::info('TamaTopup V2 CONFIRM RECEIVED', [
            'request_id' => $requestId,
            'event' => 'CONFIRM_RECEIVED',
            'provider' => $provider,
            'user_id' => $user ? $user->id : null,
            'username' => $user ? $user->username : '',
            'ip' => $this->topupConfirmClientIp($request),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'origin' => $request->header('origin'),
            'mobile' => $this->topupConfirmMobile($request),
            'amount' => $this->topupConfirmAmount($request),
            'time' => now()->format('Y-m-d H:i:s.u'),
        ]);
    }

    protected function logTopupConfirmStep($requestId, $event, array $context = [])
    {
        Log::info('TamaTopup V2 ' . $event, array_merge([
            'request_id' => $requestId,
            'event' => $event,
            'time' => now()->format('Y-m-d H:i:s.u'),
        ], $context));

        if ($event === 'API_REQUEST_STARTED') {
            $this->updateTopupConfirmAttempt($requestId, [
                'status' => 'API_REQUEST_STARTED',
                'metadata' => json_encode($context),
            ]);
        } elseif ($event === 'API_RESPONSE_RECEIVED') {
            $this->updateTopupConfirmAttempt($requestId, [
                'status' => 'API_RESPONSE_RECEIVED',
                'metadata' => json_encode($context),
            ]);
        } elseif ($event === 'ORDER_CREATED') {
            if (!empty($context['queued'])) {
                // The queue audit was committed with the order. A fast worker may
                // already have completed it, so do not overwrite its final status.
                $this->updateTopupConfirmAttempt($requestId, [
                    'transaction_id' => isset($context['transaction_id']) ? $context['transaction_id'] : null,
                ]);
                return;
            }
            $this->updateTopupConfirmAttempt($requestId, [
                'status' => 'SUCCESS',
                'order_id' => isset($context['order_id']) ? $context['order_id'] : null,
                'transaction_id' => isset($context['transaction_id']) ? $context['transaction_id'] : null,
                'provider_reference' => isset($context['txn_ref']) ? $context['txn_ref'] : null,
                'completed_at' => now(),
            ]);
        } elseif ($event === 'API_ERROR') {
            $this->updateTopupConfirmAttempt($requestId, [
                'status' => 'UNKNOWN',
                'error_message' => isset($context['message']) ? substr((string) $context['message'], 0, 1000) : null,
            ]);
        }
    }

    protected function blockDuplicateTopupConfirm(Request $request, $provider, $requestId = null, $minutes = 5)
    {
        $mobile = $this->topupConfirmMobile($request);
        if ($mobile === '') {
            return null;
        }

        $user = auth()->user();
        $requestId = $requestId ?: $request->attributes->get('topup_confirm_request_id');
        $lockKey = 'tamatopup:v2:confirm:' . $mobile;
        $internalReference = 'TAMA-' . ($user ? $user->id : 'guest') . '-' . $requestId;

        if (Schema::hasTable('queued_topups') && Schema::hasColumn('queued_topups', 'mobile')
            && QueuedTopup::where('mobile', $mobile)->whereIn('status', ['pending', 'processing', 'unknown'])->exists()) {
            $this->recordTopupConfirmAttempt($request, $provider, $requestId, 'BLOCKED_PENDING', $lockKey, null);
            return redirect('tama-topup-v2')
                ->with('message', trans('topup_v2.queue_already_processing'))
                ->with('message_type', 'warning');
        }

        if (Cache::add($lockKey, [
            'request_id' => $requestId,
            'user_id' => $user ? $user->id : null,
            'provider' => $provider,
            'internal_reference' => $internalReference,
            'created_at' => now()->toDateTimeString(),
        ], now()->addMinutes($minutes))) {
            $request->attributes->set('topup_confirm_internal_reference', $internalReference);
            $this->recordTopupConfirmAttempt($request, $provider, $requestId, 'PROCESSING', $lockKey, $internalReference);
            $this->logTopupConfirmStep($requestId, 'LOCK_ACQUIRED', [
                'provider' => $provider,
                'user_id' => $user ? $user->id : null,
                'username' => $user ? $user->username : '',
                'mobile' => $mobile,
                'lock_key' => $lockKey,
                'internal_reference' => $internalReference,
            ]);
            return null;
        }

        $this->recordTopupConfirmAttempt($request, $provider, $requestId, 'BLOCKED_DUPLICATE', $lockKey, null);
        $abuseResponse = $this->handleDuplicateConfirmAbuse($request, $provider, $requestId, $mobile, $lockKey);

        Log::warning('TamaTopup V2 duplicate confirm blocked', [
            'request_id' => $requestId,
            'event' => 'BLOCKED_DUPLICATE',
            'provider' => $provider,
            'user_id' => $user ? $user->id : null,
            'username' => $user ? $user->username : '',
            'mobile' => $mobile,
            'amount' => $this->topupConfirmAmount($request),
            'ip' => $this->topupConfirmClientIp($request),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'origin' => $request->header('origin'),
        ]);

        AppHelper::logger('warning', 'TamaTopup V2 Duplicate Confirm Blocked', ($user ? $user->username : 'unknown') . ' repeated ' . $provider . ' topup for ' . $mobile, $request->except(['_token']), true);

        if ($abuseResponse) {
            return $abuseResponse;
        }

        return redirect('tama-topup-v2')
            ->with('message', 'A top-up for this number is already being processed. Please try again later.')
            ->with('message_type', 'warning');
    }

    protected function topupConfirmMobile(Request $request)
    {
        foreach (['mobile_number', 'AccountNumber', 'recipientNumber', 'accountNumber'] as $field) {
            $value = $request->input($field);
            if ($value !== null && $value !== '') {
                return preg_replace('/\D+/', '', (string) $value);
            }
        }

        return '';
    }

    protected function topupConfirmAmount(Request $request)
    {
        foreach (['SendValue', 'euro_amount', 'sendValueOriginal', 'SendValueOriginal'] as $field) {
            $value = $request->input($field);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function topupConfirmClientIp(Request $request)
    {
        return AppHelper::getIP(true) ?: $request->ip();
    }

    protected function recordTopupConfirmAttempt(Request $request, $provider, $requestId, $status, $lockKey = null, $internalReference = null)
    {
        if (!$this->topupConfirmAttemptTableAvailable()) {
            return null;
        }

        try {
            $user = auth()->user();
            $attempt = TopupConfirmAttempt::create([
                'request_id' => $requestId,
                'layer' => 'frontend',
                'provider' => $provider,
                'user_id' => $user ? $user->id : null,
                'username' => $user ? $user->username : null,
                'mobile' => $this->topupConfirmMobile($request),
                'amount' => $this->topupConfirmAmount($request),
                'status' => $status,
                'lock_key' => $lockKey,
                'internal_reference' => $internalReference,
                'ip' => $this->topupConfirmClientIp($request),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'origin' => $request->header('origin'),
                'locked_at' => $status === 'PROCESSING' ? now() : null,
            ]);
            $request->attributes->set('topup_confirm_attempt_id', $attempt->id);

            return $attempt;
        } catch (\Exception $exception) {
            Log::warning('TamaTopup V2 confirm attempt audit failed', [
                'request_id' => $requestId,
                'provider' => $provider,
                'status' => $status,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    protected function updateTopupConfirmAttempt($requestId, array $attributes)
    {
        if (!$requestId || !$this->topupConfirmAttemptTableAvailable()) {
            return;
        }

        try {
            TopupConfirmAttempt::where('request_id', $requestId)->update($attributes);
        } catch (\Exception $exception) {
            Log::warning('TamaTopup V2 confirm attempt update failed', [
                'request_id' => $requestId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    protected function handleDuplicateConfirmAbuse(Request $request, $provider, $requestId, $mobile, $lockKey)
    {
        $user = auth()->user();
        if (!$user || (int) $user->group_id !== 4) {
            return null;
        }

        $ip = $this->topupConfirmClientIp($request);
        if (!$ip || in_array($ip, ['127.0.0.1', '::1'], true)) {
            return null;
        }

        $userCounterKey = 'tamatopup:v2:duplicate-abuse:user:' . $user->id;
        $ipCounterKey = 'tamatopup:v2:duplicate-abuse:user-ip:' . $user->id . ':' . sha1($ip);
        Cache::add($userCounterKey, 0, now()->addMinutes(self::DUPLICATE_ABUSE_WINDOW_MINUTES));
        Cache::add($ipCounterKey, 0, now()->addMinutes(self::DUPLICATE_ABUSE_WINDOW_MINUTES));
        $userAttempts = (int) Cache::increment($userCounterKey);
        $ipAttempts = (int) Cache::increment($ipCounterKey);
        $attempts = max($userAttempts, $ipAttempts);

        if ($attempts < self::DUPLICATE_ABUSE_THRESHOLD) {
            return null;
        }

        $blockKey = $this->topupConfirmUserBlockKey($user->id);
        Cache::put($blockKey, [
            'request_id' => $requestId,
            'provider' => $provider,
            'mobile' => $mobile,
            'ip' => $ip,
            'blocked_at' => now()->toDateTimeString(),
            'reason' => 'TamaTopup V2 duplicate confirm abuse',
        ], now()->addMinutes(self::RETAILER_TEMP_BLOCK_MINUTES));

        $user->forceFill([
            'last_session_id' => null,
            'active_session_ids' => null,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $user->id,
        ])->save();

        $this->permanentlyBlockTopupConfirmIp($ip, $requestId, $provider, $user, $attempts);

        $this->updateTopupConfirmAttempt($requestId, [
            'status' => 'SECURITY_BLOCKED',
            'error_message' => 'Retailer account blocked for 30 minutes and IP permanently blacklisted after duplicate confirm abuse.',
            'metadata' => json_encode([
                'attempts' => $attempts,
                'user_attempts' => $userAttempts,
                'ip_attempts' => $ipAttempts,
                'threshold' => self::DUPLICATE_ABUSE_THRESHOLD,
                'window_minutes' => self::DUPLICATE_ABUSE_WINDOW_MINUTES,
                'retailer_block_minutes' => self::RETAILER_TEMP_BLOCK_MINUTES,
                'lock_key' => $lockKey,
                'ip' => $ip,
            ]),
        ]);

        Log::critical('TamaTopup V2 duplicate confirm abuse blocked retailer and IP', [
            'request_id' => $requestId,
            'event' => 'SECURITY_BLOCKED',
            'provider' => $provider,
            'user_id' => $user->id,
            'username' => $user->username,
            'mobile' => $mobile,
            'ip' => $ip,
            'attempts' => $attempts,
            'user_attempts' => $userAttempts,
            'ip_attempts' => $ipAttempts,
            'retailer_block_minutes' => self::RETAILER_TEMP_BLOCK_MINUTES,
            'ip_block' => 'permanent',
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'origin' => $request->header('origin'),
        ]);

        AppHelper::logger(
            'warning',
            'TamaTopup V2 Security Block',
            $user->username . ' blocked for duplicate topup confirm abuse from ' . $ip,
            [
                'request_id' => $requestId,
                'provider' => $provider,
                'mobile' => $mobile,
                'attempts' => $attempts,
                'retailer_block_minutes' => self::RETAILER_TEMP_BLOCK_MINUTES,
                'ip_block' => 'permanent',
            ],
            true
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('message', 'Your account has been temporarily blocked for security reasons. Please contact support.')
            ->with('message_type', 'warning');
    }

    protected function permanentlyBlockTopupConfirmIp($ip, $requestId, $provider, $user, $attempts)
    {
        try {
            if (!Schema::hasTable('firewall')) {
                return;
            }

            $existing = DB::table('firewall')->where('ip_address', $ip)->first();
            if ($existing) {
                DB::table('firewall')->where('ip_address', $ip)->update([
                    'whitelisted' => 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                DB::table('firewall')->insert([
                    'ip_address' => $ip,
                    'whitelisted' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            Cache::forget('tamatopup:v2:blacklisted-ip:' . $ip);
        } catch (\Exception $exception) {
            Log::warning('TamaTopup V2 permanent IP block failed', [
                'request_id' => $requestId,
                'provider' => $provider,
                'user_id' => $user ? $user->id : null,
                'ip' => $ip,
                'attempts' => $attempts,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public static function topupConfirmUserBlockKey($userId)
    {
        return 'tamatopup:v2:user-blocked:' . (int) $userId;
    }

    protected function topupConfirmAttemptTableAvailable()
    {
        static $available = null;

        if ($available !== null) {
            return $available;
        }

        try {
            $available = Schema::hasTable('topup_confirm_attempts');
        } catch (\Exception $exception) {
            $available = false;
        }

        return $available;
    }

    protected function queuedTopupModeEnabled($userId)
    {
        try {
            if (!Schema::hasTable('topup_processing_settings')) {
                return false;
            }

            return TopupProcessingSetting::where('user_id', $userId)->value('mode') === 'queued';
        } catch (\Exception $exception) {
            Log::warning('Unable to resolve top-up processing mode; synchronous flow retained.', [
                'user_id' => $userId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    protected function prepareQueuedTopup(Request $request, $provider, $userId, $transId, array $payload = null)
    {
        if (!$this->queuedTopupModeEnabled($userId)) {
            return null;
        }

        if (!Schema::hasTable('queued_topups') || !Schema::hasColumn('queued_topups', 'mobile')
            || !Schema::hasTable('jobs') || !app(TopupWorkerHealth::class)->isRunning()) {
            return redirect('tama-topup-v2')
                ->with('message', trans('topup_v2.queue_unavailable'))
                ->with('message_type', 'warning');
        }

        $trackOrderId = TrackOrder::insertGetId([
            'trans_id' => $transId,
            'user_id' => $userId,
            'api_order_id' => '',
            'api_trans_id' => '',
            'status' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $userId,
            'remarks' => 'Topup accepted and waiting for queue processing.',
        ]);

        return [
            'provider' => $provider,
            'payload' => $payload === null ? $request->except(['_token', '_method']) : $payload,
            'track_order_id' => $trackOrderId,
            'response_data' => [
                'txn_ref' => 'TQ' . str_replace('-', '', (string) Str::uuid()),
                'order_id' => '',
                'orderId' => '',
                'transaction_id' => '',
                'transactionId' => '',
                'transRef' => '',
                'transferRef' => '',
            ],
        ];
    }

    protected function dispatchQueuedTopup(Request $request, array $queuedContext, $requestId, $userId, $orderId, $localReference)
    {
        if (DB::transactionLevel() < 1) {
            throw new \RuntimeException('Queued top-up must be stored inside the order transaction.');
        }

        $queued = QueuedTopup::create([
            'request_id' => $requestId,
            'provider' => $queuedContext['provider'],
            'user_id' => $userId,
            'order_id' => $orderId,
            'track_order_id' => $queuedContext['track_order_id'],
            'local_reference' => $localReference,
            'mobile' => $this->topupConfirmMobile($request),
            'status' => 'pending',
            'payload' => $queuedContext['payload'],
            'client_ip' => $this->topupConfirmClientIp($request),
            'user_agent' => Str::limit($request->userAgent() ?: '', 1000, ''),
        ]);

        app(QueuedTopupDispatcher::class)->dispatch($queued->id);
        TrackOrder::where('id', $queuedContext['track_order_id'])->update([
            'status' => 0,
            'remarks' => 'Topup accepted and waiting for queue processing.',
        ]);
        $this->updateTopupConfirmAttempt($requestId, [
            'status' => 'QUEUED',
            'order_id' => $orderId,
            'metadata' => json_encode(['queued_topup_id' => $queued->id]),
            'completed_at' => null,
        ]);
    }

    protected function decryptTopupInputs(array $inputs, array $except = ['_token', '_method'])
    {
        $decrypted = [];
        foreach ($inputs as $key => $value) {
            if (in_array($key, $except, true)) {
                $decrypted[$key] = $value;
                continue;
            }
            $decrypted[$key] = $this->decryptTopupValue($value, $key);
        }

        return $decrypted;
    }

    private function encryptTopupValue($value)
    {
        if (is_array($value)) {
            return array_map(function ($item) {
                return $this->encryptTopupValue($item);
            }, $value);
        }

        return SecurityHelper::randomEncDec('ec', (string) $value);
    }

    private function decryptTopupValue($value, $field)
    {
        if (is_array($value)) {
            return array_map(function ($item) use ($field) {
                return $this->decryptTopupValue($item, $field);
            }, $value);
        }

        $decrypted = SecurityHelper::randomEncDec('de', (string) $value);
        if ($decrypted === false) {
            throw new \InvalidArgumentException('Invalid encrypted topup field: ' . $field);
        }

        return $decrypted;
    }
}
