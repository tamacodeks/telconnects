<?php

namespace App\Http\Middleware;

use app\Library\AppHelper;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RetailerRequestFloodGuard
{
    const ALL_REQUEST_THRESHOLD = 60;
    const MUTATING_REQUEST_THRESHOLD = 20;
    const WINDOW_MINUTES = 1;

    public function handle($request, Closure $next)
    {
        $ip = $this->clientIp($request);

        $user = $request->user();
        if (!$user || (int) $user->group_id !== 4) {
            return $next($request);
        }

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

        Log::warning('Retailer request flood detected without blocking account', [
            'event' => 'SECURITY_RETAILER_REQUEST_FLOOD_DETECTED',
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
            'user_agent' => Str::limit($request->userAgent() ?: '', 200),
        ]);

        return $next($request);
    }

    private function incrementCounter($key)
    {
        Cache::add($key, 0, now()->addMinutes(self::WINDOW_MINUTES));

        return (int) Cache::increment($key);
    }

    private function clientIp($request)
    {
        return AppHelper::getIP(true) ?: $request->ip();
    }

}
