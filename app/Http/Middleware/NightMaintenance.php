<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Library\AppHelper;

class NightMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        $tz  = 'Europe/Paris';
        $now = Carbon::now($tz);
        $t   = $now->format('H:i');

        // Define window: 07:30 → 00:30 (crosses midnight)
        $start = '07:30';
        $end   = '00:00';

        if ($start < $end) {
            $allowed = ($t >= $start && $t < $end);
        } else {
            // handles midnight wrap (07:30 → 00:30)
            $allowed = ($t >= $start || $t < $end);
        }

        // Case 1: allowed time window → all IPs can access
        if ($allowed) {
            return $next($request);
        }

        // Case 2: restricted window → only Tamil Nadu (India) can access
        $ip   = AppHelper::getIP(true);
        $geo  = AppHelper::iplocation($ip) ?: [];
        if (($geo['provider'] ?? null) === 'local/private') {
            return $next($request);
        }
        $cc   = isset($geo['country_code']) ? strtoupper($geo['country_code']) : null;
        $reg  = isset($geo['region_name'])  ? strtolower(trim($geo['region_name'])) : null;
        $city = isset($geo['city']) ? $geo['city'] : null;
        $rip  = isset($geo['ip']) ? $geo['ip'] : $ip;

        $user = $request->user();

        $ctx = [
            'app'        => config('app.name'),
            'time'       => $now->toDateTimeString(),
            'ip'         => $rip,
            'country'    => $cc,
            'region'     => $reg,
            'city'       => $city,
            'method'     => $request->getMethod(),
            'path'       => $request->path(),
            'query'      => $request->getQueryString(),
            'user_id'    => $user ? $user->id : null,
            'username'   => $user ? ($user->username ?? $user->email) : null,
            'user_agent' => Str::limit($request->userAgent() ?: '', 200),
        ];

        $allowedRegionsRaw = array_merge(
            config('security.geo.in_regions', ['Tamil Nadu', 'Karnataka']),
            ['union territory of puducherry']
        );
        $allowedRegions = array_map(function ($value) {
            return mb_strtolower(trim($value));
        }, $allowedRegionsRaw);

        if ($cc === 'IN' && in_array($reg, $allowedRegions, true)) {
            Log::info('NightMaintenance bypass (allowed Indian region)', $ctx);
            AppHelper::logger('info', 'NightMaintenance Bypass', 'Allowed Indian region during restricted window', $ctx, true);
            return $next($request);
        }

        Log::warning('NightMaintenance block (outside allowed Indian regions)', $ctx);
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
                'message' => "Service unavailable (allowed only $start – $end Paris time; outside this window only Tamil Nadu, IN is allowed).",
                'window'  => ['start' => $start, 'end' => $end, 'tz' => $tz],
            ], 503);
        }

        return response()->view('errors.5xx', ['status_code' => 503], 503);
    }
}
