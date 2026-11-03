<?php

namespace App\Http\Controllers\V2\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\Support\UserSessionManager;
use app\Library\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    private const LOGIN_FAILURE_MESSAGE = 'Unable to sign in with the provided details.';
    private const VERIFICATION_FAILURE_MESSAGE = 'Verification failed. Please try again.';
    private const RESEND_FAILURE_MESSAGE = 'Unable to send a new code right now. Please try again later.';
    private const TOTP_VERIFICATION_WINDOW = 4;

    private $appName;

    public function __construct()
    {
        $this->appName = config('app.name', defined('APP_NAME') ? APP_NAME : 'TamaExpress');
    }

    protected function dashboardRouteName(User $user = null): string
    {
        $route = 'dashboard';

        if (! Route::has($route)) {
            return 'dashboard';
        }

        return $route;
    }

    protected function dashboardPath(User $user = null): string
    {
        return route($this->dashboardRouteName($user));
    }

    protected function guard()
    {
        return Auth::guard();
    }

    protected function loginFailureResponse(int $statusCode = 401, array $headers = [])
    {
        return response()->json([
            'status' => 'error',
            'message' => self::LOGIN_FAILURE_MESSAGE,
        ], $statusCode, $headers);
    }

    protected function verificationFailureResponse(int $statusCode = 422, array $headers = [])
    {
        return response()->json([
            'status' => 'error',
            'message' => self::VERIFICATION_FAILURE_MESSAGE,
        ], $statusCode, $headers);
    }

    protected function resendFailureResponse(int $statusCode = 400, array $headers = [])
    {
        return response()->json([
            'status' => 'error',
            'message' => self::RESEND_FAILURE_MESSAGE,
        ], $statusCode, $headers);
    }

    protected function clientIp(Request $request): string
    {
        $ip = config('app.env') === 'local'
            ? $request->getClientIp()
            : AppHelper::getIP(true);

        return $ip ?: $request->ip();
    }

    public function showLoginForm()
    {
        if (auth()->check()) {
            return redirect($this->dashboardPath(auth()->user()));
        }

        return view('v2.admin.authentication.login');
    }

    public function secureLoginValidate(Request $request)
    {
        $lang = $this->normalizeLocale($request->input('lang'));
        $remember = $this->requestBoolean($request, 'remember');
        $credentials = [
            'username' => $this->normalizeUsername($request->input('username')),
            'password' => (string) $request->input('password'),
        ];

        $validator = Validator::make(array_merge($credentials, ['lang' => $lang]), [
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:128'],
            'lang' => ['required', 'string', 'in:en,fr,de'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $this->applyLocale($request, $lang);

        if (! Auth::validate(array_merge($credentials, ['status' => 1]))) {
            return $this->loginFailureResponse();
        }

        /** @var User $user */
        $user = User::where('username', $credentials['username'])
            ->where('status', 1)
            ->firstOrFail();

        if (! empty($user->parent_id)) {
            $parent = User::find($user->parent_id);

            if (! $parent || (int) $parent->status !== 1) {
                Log::warning('Retailer login blocked due to inactive parent', [
                    'retailer_id' => $user->id,
                    'parent_id' => $user->parent_id,
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Login denied. Parent account is inactive.',
                ], 403);
            }
        }

        if ((int) $user->login_attempts > 0) {
            $user->forceFill(['login_attempts' => 0])->save();
        }

        $this->storePendingAuth($request, $user->username, $remember, $lang);

        $clientIp = $this->clientIp($request);
        if (UserSessionManager::requiresTotpChallenge($user)) {
            if (! $this->totpLibraryAvailable()) {
                $this->clearPendingAuth($request);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Authenticator verification is not available right now. Please contact support.',
                ], 503);
            }

            $request->session()->put('pending_totp_username', $user->username);
            $request->session()->forget('pending_otp_username');

            return response()->json([
                'status' => 'totp_required',
                'provider' => 'Google Authenticator',
            ]);
        }

        switch ((string) $user->method) {
            case '1':
                if ($user->ip_address !== $clientIp) {
                    if (! $this->issueOtpForUser($user)) {
                        $this->clearPendingAuth($request);

                        return response()->json([
                            'status' => 'error',
                            'message' => 'OTP could not be delivered. Please contact support.',
                        ], 503);
                    }

                    $request->session()->put('pending_otp_username', $user->username);
                    $request->session()->forget('pending_totp_username');

                    return response()->json(array_merge([
                        'status' => 'otp_required',
                        'reason' => 'ip_mismatch',
                        'username' => $user->username,
                        'expires_in' => $this->otpExpiresIn($user),
                    ], $this->otpDeliveryPayload($user)));
                }

                return $this->completeLogin($request, $user, $remember, $clientIp);

            case '2':
                if ((int) $user->enable_2fa === 1 && (int) $user->verify_2fa === 1 && ! empty($user->secret)) {
                    if (! $this->totpLibraryAvailable()) {
                        $this->clearPendingAuth($request);

                        return response()->json([
                            'status' => 'error',
                            'message' => 'Authenticator verification is not available right now. Please contact support.',
                        ], 503);
                    }

                    $request->session()->put('pending_totp_username', $user->username);
                    $request->session()->forget('pending_otp_username');

                    return response()->json([
                        'status' => 'totp_required',
                        'provider' => 'Google Authenticator',
                    ]);
                }

                return $this->completeLogin($request, $user, $remember, $clientIp);

            default:
                return $this->completeLogin($request, $user, $remember, $clientIp);
        }
    }

    public function validateOtp(Request $request)
    {
        $data = Validator::make([
            'username' => $this->normalizeUsername($request->input('username')),
            'otp' => $request->input('otp'),
        ], [
            'username' => ['required', 'string', 'max:50'],
            'otp' => ['required', 'digits:6'],
        ])->validate();

        if (! $this->pendingStepMatches($request, $data['username'], 'otp')) {
            $this->clearPendingAuth($request);

            return response()->json([
                'status' => 'error',
                'message' => 'Login session expired. Please sign in again.',
            ], 409);
        }

        /** @var User|null $user */
        $user = User::where('username', $data['username'])->first();
        if (! $user || (int) $user->status === 0) {
            $this->clearPendingAuth($request);

            return $this->verificationFailureResponse();
        }

        if (empty($user->otp_hash) || empty($user->otp_expires_at)) {
            return $this->verificationFailureResponse();
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            $user->forceFill([
                'otp' => null,
                'otp_hash' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
            ])->save();

            $this->clearPendingAuth($request);

            return response()->json([
                'status' => 'error',
                'message' => 'OTP expired.',
            ], 410);
        }

        if (! Hash::check((string) $data['otp'], $user->otp_hash)) {
            return $this->verificationFailureResponse();
        }

        return $this->completeLogin(
            $request,
            $user,
            (bool) data_get($this->pendingAuth($request), 'remember', false),
            $this->clientIp($request)
        );
    }

    public function resendOtp(Request $request)
    {
        $data = Validator::make([
            'username' => $this->normalizeUsername($request->input('username')),
        ], [
            'username' => ['required', 'string', 'max:50'],
        ])->validate();

        if (! $this->pendingStepMatches($request, $data['username'], 'otp')) {
            $this->clearPendingAuth($request);

            return response()->json([
                'status' => 'error',
                'message' => 'Login session expired. Please sign in again.',
            ], 409);
        }

        /** @var User|null $user */
        $user = User::where('username', $data['username'])->first();
        if (! $user || (int) $user->status === 0) {
            $this->clearPendingAuth($request);

            return $this->resendFailureResponse();
        }

        if (! $this->issueOtpForUser($user, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP could not be delivered. Please contact support.',
            ], 503);
        }

        $this->refreshPendingAuthExpiry($request);

        return response()->json([
            'status' => 'otp_resent',
            'expires_in' => $this->otpExpiresIn($user),
        ] + $this->otpDeliveryPayload($user));
    }

    public function verifyTotp(Request $request)
    {
        $data = Validator::make([
            'username' => $this->normalizeUsername($request->input('username')),
            'code' => $request->input('code'),
        ], [
            'username' => ['required', 'string', 'max:50'],
            'code' => ['required', 'digits:6'],
        ])->validate();

        if (! $this->pendingStepMatches($request, $data['username'], 'totp')) {
            $this->clearPendingAuth($request);

            return response()->json([
                'status' => 'error',
                'message' => 'Login session expired. Please sign in again.',
            ], 409);
        }

        /** @var User|null $user */
        $user = User::where('username', $data['username'])->first();
        if (! $user || (int) $user->status === 0) {
            $this->clearPendingAuth($request);

            return $this->verificationFailureResponse();
        }

        if ((int) $user->enable_2fa !== 1 || (int) $user->verify_2fa !== 1 || empty($user->secret)) {
            $this->clearPendingAuth($request);

            return response()->json([
                'status' => 'error',
                'message' => '2FA is not enabled for this user.',
            ], 400);
        }

        try {
            $g2fa = new \PragmaRX\Google2FA\Google2FA();
            $verified = $g2fa->verifyKey($user->secret, $data['code'], self::TOTP_VERIFICATION_WINDOW);
        } catch (\Throwable $e) {
            Log::error('TOTP verify failed for user '.$user->id.': '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'TOTP verification error.',
            ], 500);
        }

        if (! $verified) {
            return $this->verificationFailureResponse();
        }

        return $this->completeLogin(
            $request,
            $user,
            (bool) data_get($this->pendingAuth($request), 'remember', false),
            $this->clientIp($request)
        );
    }

    public function logout(Request $request)
    {
        $username = optional(Auth::user())->username;
        $user = Auth::user();
        $sessionId = $request->session()->getId();
        Log::info('User '.$username.' logged out');

        if ($user) {
            UserSessionManager::unregister($user, $sessionId);
        }

        Auth::logout();

        $request->session()->invalidate();

        if (method_exists($request->session(), 'regenerateToken')) {
            $request->session()->regenerateToken();
        }

        $this->clearPendingAuth($request);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Logged out.',
            ]);
        }

        return redirect()->route('login')
            ->with('message', trans('users.logged_out'))
            ->with('message_type', 'success');
    }

    protected function pendingAuth(Request $request): array
    {
        return (array) $request->session()->get('pending_auth', []);
    }

    protected function storePendingAuth(Request $request, string $username, bool $remember, string $lang): void
    {
        $request->session()->put('pending_auth', [
            'username' => $username,
            'remember' => $remember ? 1 : 0,
            'lang' => $lang,
            'expires_at' => time() + 600,
        ]);
    }

    protected function pendingStepMatches(Request $request, string $username, string $step): bool
    {
        $pending = $this->pendingAuth($request);
        if ($this->pendingAuthExpired($pending)) {
            $this->clearPendingAuth($request);

            return false;
        }

        if (data_get($pending, 'username') !== $username) {
            return false;
        }

        if ($step === 'otp') {
            return $request->session()->get('pending_otp_username') === $username;
        }

        if ($step === 'totp') {
            return $request->session()->get('pending_totp_username') === $username;
        }

        return true;
    }

    protected function pendingAuthExpired(array $pending): bool
    {
        $expiresAt = (int) data_get($pending, 'expires_at', 0);

        return $expiresAt <= 0 || $expiresAt < time();
    }

    protected function refreshPendingAuthExpiry(Request $request): void
    {
        $pending = $this->pendingAuth($request);
        if (empty($pending)) {
            return;
        }

        $pending['expires_at'] = time() + 600;
        $request->session()->put('pending_auth', $pending);
    }

    protected function clearPendingAuth(Request $request): void
    {
        $request->session()->forget([
            'pending_auth',
            'pending_otp_username',
            'pending_totp_username',
        ]);
    }

    protected function completeLogin(Request $request, User $user, bool $remember, string $clientIp)
    {
        $lang = $this->normalizeLocale(data_get($this->pendingAuth($request), 'lang'));
        $this->applyLocale($request, $lang);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->forceFill([
            'last_activity' => now(),
            'ip_address' => $clientIp,
            'verify_ip' => 1,
            'otp' => null,
            'otp_hash' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
            'login_attempts' => 0,
        ])->save();

        UserSessionManager::register($user, $request->session()->getId(), $clientIp);

        $this->clearPendingAuth($request);

        Log::info('User '.$user->username.' logged in via V2 authentication');

        return response()->json([
            'status' => 'authenticated',
            'redirect_url' => $this->dashboardPath($user),
        ]);
    }

    protected function issueOtpForUser(User $user, bool $force = false): bool
    {
        if (! $force && $this->hasActiveOtp($user)) {
            return true;
        }

        $otp = random_int(100000, 999999);

        $user->forceFill([
            'otp' => null,
            'otp_hash' => Hash::make((string) $otp),
            'otp_expires_at' => now()->addMinutes(10),
            'otp_attempts' => 0,
            'ip_address2' => $user->ip_address,
            'verify_ip' => 0,
        ])->save();

        if ($this->deliverOtp($user, $otp)) {
            return true;
        }

        $user->forceFill([
            'otp' => null,
            'otp_hash' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
        ])->save();

        return false;
    }

    protected function hasActiveOtp(User $user): bool
    {
        if (empty($user->otp_hash) || empty($user->otp_expires_at)) {
            return false;
        }

        return now()->lessThan($user->otp_expires_at);
    }

    protected function otpExpiresIn(User $user): int
    {
        if (empty($user->otp_expires_at)) {
            return 600;
        }

        $expiresAt = $user->otp_expires_at;
        if (is_object($expiresAt) && method_exists($expiresAt, 'getTimestamp')) {
            return max(1, $expiresAt->getTimestamp() - time());
        }

        $timestamp = strtotime((string) $expiresAt);

        return $timestamp ? max(1, $timestamp - time()) : 600;
    }

    protected function deliverOtp(User $user, int $otp): bool
    {
        $message = 'Hello '.$user->username.'. Greetings from '.$this->appName.'. Your verification code is '.$otp;
        if (empty($user->mobile)) {
            Log::warning('SMS OTP delivery failed: mobile missing', [
                'user_id' => $user->id,
            ]);

            return false;
        }

        if (config('app.env') === 'local') {
            Log::info('OTP delivery skipped in local environment', [
                'user_id' => $user->id,
                'channel' => 'sms',
            ]);

            return true;
        }

        $result = AppHelper::sendOtpMessage($user->mobile, $message, 'sms', (string) $otp);
        if (! empty($result)) {
            return true;
        }

        Log::warning('OTP delivery failed', [
            'user_id' => $user->id,
            'channel' => 'sms',
        ]);

        return false;
    }

    protected function totpLibraryAvailable(): bool
    {
        return class_exists(\PragmaRX\Google2FA\Google2FA::class);
    }

    protected function resolvedOtpChannel(User $user): string
    {
        return 'sms';
    }

    protected function otpChannelLabel(string $channel): string
    {
        switch ($channel) {
            case 'whatsapp':
                return 'WhatsApp';
            default:
                return 'SMS';
        }
    }

    protected function otpDeliveryPayload(User $user): array
    {
        $primaryChannel = $this->resolvedOtpChannel($user);
        $channels = [];
        $targets = [];
        $maskedMobile = $this->maskMobile($user->mobile);

        if (! empty($user->mobile)) {
            $channels[] = $primaryChannel;
            $targets[] = $this->otpChannelLabel($primaryChannel).' '.$maskedMobile;
        }

        if (empty($channels) && ! empty($maskedMobile)) {
            $channels[] = $primaryChannel;
            $targets[] = $this->otpChannelLabel($primaryChannel).' '.$maskedMobile;
        }

        $labels = array_map(function ($channel) {
            return $this->otpChannelLabel($channel);
        }, array_values(array_unique($channels)));

        return [
            'mask_mobile' => $maskedMobile,
            'delivery_channel' => $primaryChannel,
            'delivery_channel_label' => $this->otpChannelLabel($primaryChannel),
            'delivery_channels' => $labels,
            'delivery_targets' => $targets,
            'message' => $this->otpDeliveryMessage($labels),
        ];
    }

    protected function otpDeliveryMessage(array $labels): string
    {
        if (empty($labels)) {
            return 'A one-time code has been sent to your registered contact.';
        }

        if (count($labels) === 1) {
            return 'A one-time code has been sent via '.$labels[0].'.';
        }

        $last = array_pop($labels);

        return 'A one-time code has been sent via '.implode(', ', $labels).' and '.$last.'.';
    }

    protected function normalizeLocale($lang): string
    {
        $lang = strtolower(trim((string) $lang));
        $allowed = ['en', 'fr', 'de'];

        if (in_array($lang, $allowed, true)) {
            return $lang;
        }

        $sessionLocale = strtolower((string) session('locale', config('app.fallback_locale', 'en')));

        return in_array($sessionLocale, $allowed, true) ? $sessionLocale : 'en';
    }

    protected function normalizeUsername($username): string
    {
        return trim((string) $username);
    }

    protected function applyLocale(Request $request, string $lang): void
    {
        $request->session()->put('locale', $lang);
        app()->setLocale($lang);
    }

    protected function requestBoolean(Request $request, string $key): bool
    {
        return filter_var($request->input($key), FILTER_VALIDATE_BOOLEAN);
    }


    private function maskMobile(?string $mobile): ?string
    {
        if (empty($mobile)) {
            return $mobile;
        }

        return str_repeat('*', max(strlen($mobile) - 4, 0)).substr($mobile, -4);
    }
}
