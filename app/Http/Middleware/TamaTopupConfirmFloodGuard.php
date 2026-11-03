<?php

namespace App\Http\Middleware;

use App\Models\TopupConfirmAttempt;
use app\Library\AppHelper;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TamaTopupConfirmFloodGuard
{
    const CONFIRM_FLOOD_THRESHOLD = 10;
    const CONFIRM_FLOOD_WINDOW_MINUTES = 1;

    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if (!$user || (int) $user->group_id !== 4) {
            return $next($request);
        }

        $ip = $this->clientIp($request);
        if (!$ip || in_array($ip, ['127.0.0.1', '::1'], true)) {
            return $next($request);
        }

        $userCounterKey = 'tamatopup:v2:confirm-flood:user:' . $user->id;
        $ipCounterKey = 'tamatopup:v2:confirm-flood:user-ip:' . $user->id . ':' . sha1($ip);
        Cache::add($userCounterKey, 0, now()->addMinutes(self::CONFIRM_FLOOD_WINDOW_MINUTES));
        Cache::add($ipCounterKey, 0, now()->addMinutes(self::CONFIRM_FLOOD_WINDOW_MINUTES));
        $userAttempts = (int) Cache::increment($userCounterKey);
        $ipAttempts = (int) Cache::increment($ipCounterKey);
        $attempts = max($userAttempts, $ipAttempts);

        if ($attempts < self::CONFIRM_FLOOD_THRESHOLD) {
            return $next($request);
        }

        $requestId = (string) Str::uuid();
        $provider = $this->providerFromPath($request->path());
        $this->recordSecurityBlockedAttempt($request, $requestId, $provider, $user, $ip, $attempts, $userAttempts, $ipAttempts);

        Log::warning('TamaTopup V2 confirm flood detected without blocking account', [
            'request_id' => $requestId,
            'event' => 'SECURITY_CONFIRM_FLOOD_DETECTED',
            'provider' => $provider,
            'user_id' => $user->id,
            'username' => $user->username,
            'ip' => $ip,
            'path' => $request->path(),
            'attempts' => $attempts,
            'user_attempts' => $userAttempts,
            'ip_attempts' => $ipAttempts,
            'threshold' => self::CONFIRM_FLOOD_THRESHOLD,
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'origin' => $request->header('origin'),
        ]);

        AppHelper::logger('warning', 'TamaTopup V2 Confirm Flood', $user->username . ' exceeded topup confirm limit from ' . $ip, [
            'request_id' => $requestId,
            'provider' => $provider,
            'path' => $request->path(),
            'attempts' => $attempts,
            'user_attempts' => $userAttempts,
            'ip_attempts' => $ipAttempts,
        ], true);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Too Many Attempts.',
            ], 429);
        }

        return redirect()->back()
            ->with('message', 'Too Many Attempts.')
            ->with('message_type', 'warning');
    }

    private function recordSecurityBlockedAttempt($request, $requestId, $provider, $user, $ip, $attempts, $userAttempts, $ipAttempts)
    {
        try {
            if (!Schema::hasTable('topup_confirm_attempts')) {
                return;
            }

            TopupConfirmAttempt::create([
                'request_id' => $requestId,
                'layer' => 'frontend',
                'provider' => $provider,
                'user_id' => $user->id,
                'username' => $user->username,
                'mobile' => '',
                'amount' => null,
                'status' => 'TOO_MANY_ATTEMPTS',
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'origin' => $request->header('origin'),
                'error_message' => 'Too many confirm requests.',
                'metadata' => json_encode([
                    'path' => $request->path(),
                    'attempts' => $attempts,
                    'user_attempts' => $userAttempts,
                    'ip_attempts' => $ipAttempts,
                    'threshold' => self::CONFIRM_FLOOD_THRESHOLD,
                    'window_minutes' => self::CONFIRM_FLOOD_WINDOW_MINUTES,
                ]),
                'completed_at' => now(),
            ]);
        } catch (\Exception $exception) {
            Log::warning('TamaTopup V2 confirm flood audit failed', [
                'request_id' => $requestId,
                'provider' => $provider,
                'user_id' => $user->id,
                'ip' => $ip,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function providerFromPath($path)
    {
        foreach (['ding', 'reloadly', 'transfer', 'tellus'] as $provider) {
            if (strpos($path, '/' . $provider . '/confirm') !== false || strpos($path, $provider . '/confirm') !== false) {
                return $provider;
            }
        }

        return 'unknown';
    }

    private function clientIp($request)
    {
        return AppHelper::getIP(true) ?: $request->ip();
    }
}
