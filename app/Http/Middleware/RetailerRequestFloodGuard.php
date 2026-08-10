<?php

namespace App\Http\Middleware;

use app\Library\AppHelper;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RetailerRequestFloodGuard
{
    const ALL_REQUEST_THRESHOLD = 60;
    const MUTATING_REQUEST_THRESHOLD = 20;
    const WINDOW_MINUTES = 1;
    const RETAILER_TEMP_BLOCK_MINUTES = 30;

    public function handle($request, Closure $next)
    {
        if ($this->isPermanentlyBlacklisted($request->ip())) {
            return $this->blockCurrentRequest($request, 'Access denied.');
        }

        $user = $request->user();
        if (!$user || (int) $user->group_id !== 4) {
            return $next($request);
        }

        if (Cache::has($this->userBlockKey($user->id))) {
            return $this->blockCurrentRequest($request, 'Your account has been temporarily blocked for security reasons. Please contact support.');
        }

        $ip = $request->ip();
        if (!$ip || in_array($ip, ['127.0.0.1', '::1'], true)) {
            return $next($request);
        }

        $allAttempts = $this->incrementCounter('security:retailer-flood:all:' . $user->id . ':' . sha1($ip));
        $mutatingAttempts = 0;

        if (!$request->isMethod('get') && !$request->isMethod('head') && !$request->isMethod('options')) {
            $mutatingAttempts = $this->incrementCounter('security:retailer-flood:mutating:' . $user->id . ':' . sha1($ip));
        }

        $allLimitExceeded = $allAttempts >= self::ALL_REQUEST_THRESHOLD;
        $mutatingLimitExceeded = $mutatingAttempts >= self::MUTATING_REQUEST_THRESHOLD && $mutatingAttempts > 0;

        if (!$allLimitExceeded && !$mutatingLimitExceeded) {
            return $next($request);
        }

        $reason = $mutatingLimitExceeded
            ? 'Retailer mutating request flood'
            : 'Retailer request flood';

        $this->blockRetailerAndIp($request, $user, $ip, $reason, $allAttempts, $mutatingAttempts);

        return $this->blockCurrentRequest($request, 'Your account has been temporarily blocked for security reasons. Please contact support.');
    }

    private function incrementCounter($key)
    {
        Cache::add($key, 0, now()->addMinutes(self::WINDOW_MINUTES));

        return (int) Cache::increment($key);
    }

    private function blockRetailerAndIp($request, $user, $ip, $reason, $allAttempts, $mutatingAttempts)
    {
        Cache::put($this->userBlockKey($user->id), [
            'path' => $request->path(),
            'method' => $request->getMethod(),
            'ip' => $ip,
            'blocked_at' => now()->toDateTimeString(),
            'reason' => $reason,
            'all_attempts' => $allAttempts,
            'mutating_attempts' => $mutatingAttempts,
        ], now()->addMinutes(self::RETAILER_TEMP_BLOCK_MINUTES));

        $user->forceFill([
            'last_session_id' => null,
            'active_session_ids' => null,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $user->id,
        ])->save();

        $this->permanentlyBlockIp($ip);

        Log::critical('Retailer request flood blocked account and IP', [
            'event' => 'SECURITY_BLOCKED_RETAILER_REQUEST_FLOOD',
            'user_id' => $user->id,
            'username' => $user->username,
            'ip' => $ip,
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'reason' => $reason,
            'all_attempts' => $allAttempts,
            'mutating_attempts' => $mutatingAttempts,
            'all_threshold' => self::ALL_REQUEST_THRESHOLD,
            'mutating_threshold' => self::MUTATING_REQUEST_THRESHOLD,
            'window_minutes' => self::WINDOW_MINUTES,
            'retailer_block_minutes' => self::RETAILER_TEMP_BLOCK_MINUTES,
            'ip_block' => 'permanent',
            'user_agent' => Str::limit($request->userAgent() ?: '', 200),
            'referer' => $request->header('referer'),
            'origin' => $request->header('origin'),
        ]);

        AppHelper::logger('warning', 'Retailer Request Flood Security Block', $user->username . ' blocked for request flood from ' . $ip, [
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'reason' => $reason,
            'all_attempts' => $allAttempts,
            'mutating_attempts' => $mutatingAttempts,
            'retailer_block_minutes' => self::RETAILER_TEMP_BLOCK_MINUTES,
            'ip_block' => 'permanent',
        ], true);
    }

    private function blockCurrentRequest($request, $message)
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect('/')
            ->with('message', $message)
            ->with('message_type', 'warning');
    }

    private function userBlockKey($userId)
    {
        return 'security:retailer:user-blocked:' . (int) $userId;
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
            Cache::forget('security:blacklisted-ip:' . $ip);
        } catch (\Exception $exception) {
            Log::warning('Retailer request flood permanent IP block failed', [
                'ip' => $ip,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function isPermanentlyBlacklisted($ip)
    {
        if (!$ip || in_array($ip, ['127.0.0.1', '::1'], true)) {
            return false;
        }

        try {
            if (!Schema::hasTable('firewall')) {
                return false;
            }

            return Cache::remember('security:blacklisted-ip:' . $ip, now()->addMinutes(1), function () use ($ip) {
                return DB::table('firewall')
                    ->where('ip_address', $ip)
                    ->where('whitelisted', 0)
                    ->exists();
            });
        } catch (\Throwable $exception) {
            Log::warning('Retailer request flood blacklist check failed', [
                'ip' => $ip,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
