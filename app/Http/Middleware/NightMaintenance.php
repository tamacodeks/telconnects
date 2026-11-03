<?php

namespace App\Http\Middleware;

use App\Library\AppHelper;
use App\Models\NightMaintenanceSetting;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NightMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        $userId = optional($request->user())->id;
        $setting = NightMaintenanceSetting::where('user_id', $userId)->first();
        if (!$setting) {
            $setting = NightMaintenanceSetting::whereNull('user_id')->first();
        }
        if (!$setting || !$setting->enabled || in_array($request->ip(), ['127.0.0.1', '::1'], true)) {
            return $next($request);
        }

        $tz = $setting->timezone ?: 'Europe/Paris';
        try {
            $now = Carbon::now($tz);
        } catch (\Throwable $e) {
            $tz = 'Europe/Paris';
            $now = Carbon::now($tz);
        }

        $time = $now->format('H:i');
        $start = substr($setting->allowed_start, 0, 5);
        $end = substr($setting->allowed_end, 0, 5);
        $allowed = $start < $end ? ($time >= $start && $time < $end) : ($time >= $start || $time < $end);
        if ($allowed) {
            return $next($request);
        }

        $geo = AppHelper::iplocation(AppHelper::getIP(true)) ?: [];
        $region = strtolower(trim($geo['region_name'] ?? ''));
        $regions = array_map('strtolower', config('security.geo.in_regions', ['Tamil Nadu', 'Karnataka']));
        $regions[] = 'union territory of puducherry';

        if (strtoupper($geo['country_code'] ?? '') === 'IN' && in_array($region, $regions, true)) {
            return $next($request);
        }

        $ctx = [
            'app' => config('app.name'),
            'time' => $now->toDateTimeString(),
            'ip' => $geo['ip'] ?? $request->ip(),
            'country' => $geo['country_code'] ?? null,
            'region' => $region,
            'city' => $geo['city'] ?? null,
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'query' => $request->getQueryString(),
            'user_id' => $userId,
            'username' => optional($request->user())->username,
            'user_agent' => Str::limit($request->userAgent() ?: '', 200),
        ];
        Log::warning('NightMaintenance block', $ctx);
        AppHelper::logger('warning', 'NightMaintenance Block', 'Blocked by maintenance window', $ctx, true);

        if (Auth::check()) {
            Auth::logout();
            try {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            } catch (\Throwable $e) {
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Service unavailable during maintenance.',
                'window' => ['start' => $start, 'end' => $end, 'tz' => $tz],
            ], 503);
        }

        return response()->view('errors.5xx', ['status_code' => 503], 503);
    }
}
