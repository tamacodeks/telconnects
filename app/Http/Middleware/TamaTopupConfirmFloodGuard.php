<?php

namespace App\Http\Middleware;

use App\Models\TopupConfirmAttempt;
use app\Library\AppHelper;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TamaTopupConfirmFloodGuard
{
    const CONFIRM_FLOOD_THRESHOLD = 10;
    const CONFIRM_FLOOD_WINDOW_MINUTES = 1;
    const RETAILER_TEMP_BLOCK_MINUTES = 30;

    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if (!$user || (int) $user->group_id !== 4) {
            return $next($request);
        }

        $ip = $request->ip();
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

        $blockKey = 'tamatopup:v2:user-blocked:' . (int) $user->id;
        Cache::put($blockKey, [
            'path' => $request->path(),
            'ip' => $ip,
            'blocked_at' => now()->toDateTimeString(),
            'reason' => 'TamaTopup V2 confirm request flood',
        ], now()->addMinutes(self::RETAILER_TEMP_BLOCK_MINUTES));

        $user->forceFill([
            'last_session_id' => null,
            'active_session_ids' => null,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $user->id,
        ])->save();

        $this->permanentlyBlockIp($ip);
        $requestId = (string) Str::uuid();
        $provider = $this->providerFromPath($request->path());
        $this->recordSecurityBlockedAttempt($request, $requestId, $provider, $user, $ip, $attempts, $userAttempts, $ipAttempts);

        Log::critical('TamaTopup V2 confirm flood blocked retailer and IP', [
            'request_id' => $requestId,
            'event' => 'SECURITY_BLOCKED_CONFIRM_FLOOD',
            'provider' => $provider,
            'user_id' => $user->id,
            'username' => $user->username,
            'ip' => $ip,
            'path' => $request->path(),
            'attempts' => $attempts,
            'user_attempts' => $userAttempts,
            'ip_attempts' => $ipAttempts,
            'threshold' => self::CONFIRM_FLOOD_THRESHOLD,
            'retailer_block_minutes' => self::RETAILER_TEMP_BLOCK_MINUTES,
            'ip_block' => 'permanent',
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'origin' => $request->header('origin'),
        ]);

        AppHelper::logger('warning', 'TamaTopup V2 Confirm Flood Security Block', $user->username . ' blocked for topup confirm flood from ' . $ip, [
            'request_id' => $requestId,
            'provider' => $provider,
            'path' => $request->path(),
            'attempts' => $attempts,
            'user_attempts' => $userAttempts,
            'ip_attempts' => $ipAttempts,
            'retailer_block_minutes' => self::RETAILER_TEMP_BLOCK_MINUTES,
            'ip_block' => 'permanent',
        ], true);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('message', 'Your account has been temporarily blocked for security reasons. Please contact support.')
            ->with('message_type', 'warning');
    }

    private function permanentlyBlockIp($ip)
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
            Log::warning('TamaTopup V2 confirm flood permanent IP block failed', [
                'ip' => $ip,
                'error' => $exception->getMessage(),
            ]);
        }
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
                'status' => 'SECURITY_BLOCKED',
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'origin' => $request->header('origin'),
                'error_message' => 'Retailer account blocked for 30 minutes and IP permanently blacklisted after confirm request flood.',
                'metadata' => json_encode([
                    'path' => $request->path(),
                    'attempts' => $attempts,
                    'user_attempts' => $userAttempts,
                    'ip_attempts' => $ipAttempts,
                    'threshold' => self::CONFIRM_FLOOD_THRESHOLD,
                    'window_minutes' => self::CONFIRM_FLOOD_WINDOW_MINUTES,
                    'retailer_block_minutes' => self::RETAILER_TEMP_BLOCK_MINUTES,
                    'ip_block' => 'permanent',
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
}
