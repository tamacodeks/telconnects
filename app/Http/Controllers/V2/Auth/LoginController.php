<?php

namespace App\Http\Controllers\V2\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\Support\UserSessionManager;
use app\Library\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoginController extends Controller
{
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

    protected function throttleKey(Request $request, string $action): string
    {
        return Str::lower($this->normalizeUsername($request->input('username'))).'|'.$request->ip().'|'.$action;
    }

    protected function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        if (class_exists(\Illuminate\Support\Facades\RateLimiter::class)) {
            return \Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, $maxAttempts);
        }

        return (int) Cache::get($key, 0) >= $maxAttempts;
    }

    protected function availableIn(string $key, int $defaultDecaySeconds): int
    {
        if (class_exists(\Illuminate\Support\Facades\RateLimiter::class)) {
            return max(1, (int) \Illuminate\Support\Facades\RateLimiter::availableIn($key));
        }

        $started = (int) Cache::get($key.':timer', 0);
        if ($started === 0) {
            return $defaultDecaySeconds;
        }

        return max(1, ($started + $defaultDecaySeconds) - time());
    }

    protected function hitAttempt(string $key, int $decaySeconds): int
    {
        if (class_exists(\Illuminate\Support\Facades\RateLimiter::class)) {
            \Illuminate\Support\Facades\RateLimiter::hit($key, $decaySeconds);

            return $this->availableIn($key, $decaySeconds);
        }

        $timerKey = $key.':timer';
        if (! Cache::has($timerKey)) {
            Cache::put($timerKey, time(), $decaySeconds);
        }

        Cache::put($key, ((int) Cache::get($key, 0)) + 1, $decaySeconds);

        return $this->availableIn($key, $decaySeconds);
    }

    protected function clearAttempts(string $key): void
    {
        if (class_exists(\Illuminate\Support\Facades\RateLimiter::class)) {
            \Illuminate\Support\Facades\RateLimiter::clear($key);
            return;
        }

        Cache::forget($key);
        Cache::forget($key.':timer');
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

        $pwKey = $this->throttleKey($request, 'password-login');
        if ($this->tooManyAttempts($pwKey, 5)) {
            $seconds = $this->availableIn($pwKey, 60);

            return response()->json([
                'status' => 'error',
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429, ['Retry-After' => $seconds]);
        }

        /** @var User|null $candidate */
        $candidate = User::where('username', $credentials['username'])->first();

        if (! Auth::validate(array_merge($credentials, ['status' => 1]))) {
            $this->hitAttempt($pwKey, 60);

            if ($candidate) {
                if ((int) $candidate->status === 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Account blocked.',
                    ], 423);
                }

                $candidate->increment('login_attempts');

                if ((int) $candidate->login_attempts >= 5) {
                    $candidate->forceFill([
                        'status' => 0,
                        'login_attempts' => 0,
                    ])->save();

                    Log::warning("User {$candidate->username} blocked after 5 failed password attempts");

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Account blocked due to too many failed attempts.',
                    ], 423);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $this->clearAttempts($pwKey);

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
                        'expires_in' => 600,
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

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid code.',
            ], 422);
        }

        if (empty($user->otp_hash) || empty($user->otp_expires_at)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid code.',
            ], 422);
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

        $throttleKey = $this->throttleKey($request, 'otp-validate');
        if ($this->tooManyAttempts($throttleKey, 6)) {
            $seconds = $this->availableIn($throttleKey, 600);

            return response()->json([
                'status' => 'error',
                'message' => 'Too many attempts. Try again in '.$seconds.' seconds.',
            ], 429, ['Retry-After' => $seconds]);
        }

        if (! Hash::check((string) $data['otp'], $user->otp_hash)) {
            $this->hitAttempt($throttleKey, 600);
            $user->increment('otp_attempts');

            if ((int) $user->otp_attempts >= 5) {
                $user->forceFill([
                    'status' => 0,
                    'otp' => null,
                    'otp_hash' => null,
                    'otp_expires_at' => null,
                    'otp_attempts' => 0,
                ])->save();

                $this->clearPendingAuth($request);

                Log::warning("User {$user->username} blocked after 5 failed OTP attempts");

                return response()->json([
                    'status' => 'error',
                    'message' => 'Account blocked due to too many failed OTP attempts.',
                ], 423);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid code.',
            ], 422);
        }

        $this->clearAttempts($throttleKey);

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

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to resend at this time.',
            ], 400);
        }

        $cooldownKey = $this->throttleKey($request, 'otp-resend-60s');
        $dailyKey = $this->throttleKey($request, 'otp-resend-daily');

        if ($this->tooManyAttempts($cooldownKey, 1)) {
            $seconds = $this->availableIn($cooldownKey, 60);

            return response()->json([
                'status' => 'error',
                'message' => 'Please wait '.$seconds.' seconds before requesting another code.',
            ], 429, ['Retry-After' => $seconds]);
        }

        if ($this->tooManyAttempts($dailyKey, 10)) {
            $seconds = $this->availableIn($dailyKey, 86400);

            return response()->json([
                'status' => 'error',
                'message' => 'Daily OTP resend limit reached. Try again tomorrow.',
            ], 429, ['Retry-After' => $seconds]);
        }

        if (! $this->issueOtpForUser($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP could not be delivered. Please contact support.',
            ], 503);
        }

        $this->refreshPendingAuthExpiry($request);
        $this->hitAttempt($cooldownKey, 60);
        $this->hitAttempt($dailyKey, 86400);

        return response()->json([
            'status' => 'otp_resent',
            'expires_in' => 600,
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

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid code.',
            ], 422);
        }

        if ((int) $user->enable_2fa !== 1 || (int) $user->verify_2fa !== 1 || empty($user->secret)) {
            $this->clearPendingAuth($request);

            return response()->json([
                'status' => 'error',
                'message' => '2FA is not enabled for this user.',
            ], 400);
        }

        $throttleKey = $this->throttleKey($request, 'totp-verify');
        if ($this->tooManyAttempts($throttleKey, 6)) {
            $seconds = $this->availableIn($throttleKey, 300);

            return response()->json([
                'status' => 'error',
                'message' => 'Too many attempts. Try again in '.$seconds.' seconds.',
            ], 429, ['Retry-After' => $seconds]);
        }

        try {
            $g2fa = new \PragmaRX\Google2FA\Google2FA();
            $verified = $g2fa->verifyKey($user->secret, $data['code'], 1);
        } catch (\Throwable $e) {
            Log::error('TOTP verify failed for user '.$user->id.': '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'TOTP verification error.',
            ], 500);
        }

        if (! $verified) {
            $this->hitAttempt($throttleKey, 300);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid code.',
            ], 422);
        }

        $this->clearAttempts($throttleKey);

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

    protected function issueOtpForUser(User $user): bool
    {
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

    protected function deliverOtp(User $user, int $otp): bool
    {
        $message = 'Hello '.$user->username.'. Greetings from '.$this->appName.'. Your verification code is '.$otp;
        if (empty($user->mobile)) {
            Log::warning('WhatsApp OTP delivery failed: mobile missing', [
                'user_id' => $user->id,
            ]);

            return false;
        }

        if (config('app.env') === 'local') {
            Log::info('OTP delivery skipped in local environment', [
                'user_id' => $user->id,
                'channel' => 'whatsapp',
            ]);

            return true;
        }

        $result = AppHelper::sendOtpMessage($user->mobile, $message, 'whatsapp', (string) $otp);
        if (! empty($result)) {
            return true;
        }

        Log::warning('OTP delivery failed', [
            'user_id' => $user->id,
            'channel' => 'whatsapp',
        ]);

        return false;
    }

    protected function totpLibraryAvailable(): bool
    {
        return class_exists(\PragmaRX\Google2FA\Google2FA::class);
    }

    protected function resolvedOtpChannel(User $user): string
    {
        return 'whatsapp';
    }

    protected function otpChannelLabel(string $channel): string
    {
        switch ($channel) {
            case 'whatsapp':
                return 'WhatsApp';
            case 'email':
                return 'Email';
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
            'mask_email' => null,
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

    private function maskEmail(?string $email): ?string
    {
        if (empty($email) || strpos($email, '@') === false) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);

        return substr($local, 0, 2).str_repeat('*', max(strlen($local) - 2, 0)).'@'.$domain;
    }

    private function maskMobile(?string $mobile): ?string
    {
        if (empty($mobile)) {
            return $mobile;
        }

        return str_repeat('*', max(strlen($mobile) - 4, 0)).substr($mobile, -4);
    }
}
