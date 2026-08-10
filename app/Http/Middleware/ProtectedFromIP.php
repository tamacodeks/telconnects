<?php

namespace App\Http\Middleware;

use App\Library\AppHelper;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProtectedFromIP
{
    public function handle($request, Closure $next)
    {
        try {
            if ($this->isPermanentlyBlacklisted($request->ip())) {
                Log::critical('Blocked permanently blacklisted IP on '.config('app.name'), [
                    'ip' => $request->ip(),
                    'path' => $request->path(),
                    'method' => $request->getMethod(),
                    'user_id' => optional($request->user())->id,
                    'username' => optional($request->user())->username,
                    'user_agent' => Str::limit($request->userAgent() ?: '', 200),
                ]);

                if (Auth::check()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                return $this->deny($request, 'Access denied.');
            }

            if (app()->environment(['local', 'testing']) || !config('app.night_block_enabled', true)) {
                return $next($request);
            }

            if (in_array($request->ip(), ['127.0.0.1', '::1'], true)) {
                return $next($request);
            }

            $ip = AppHelper::getIP(true);
            $geo = Cache::remember('geoip:'.$ip, now()->addMinutes(10), function () use ($ip) {
                return AppHelper::iplocation($ip) ?: array();
            });

            if (($geo['provider'] ?? null) === 'local/private') {
                return $next($request);
            }

            $code = isset($geo['country_code']) ? strtoupper((string) $geo['country_code']) : '';
            $region = isset($geo['region_name']) ? (string) $geo['region_name'] : '';
            $city = isset($geo['city']) ? (string) $geo['city'] : '';
            $reqIp = isset($geo['ip']) && $geo['ip'] ? $geo['ip'] : $ip;
            $user = $request->user();

            $logCtx = array(
                'app' => config('app.name'),
                'ip' => $reqIp,
                'country' => $code,
                'region' => $region,
                'city' => $city,
                'method' => $request->getMethod(),
                'path' => $request->path(),
                'query' => $request->getQueryString(),
                'user_id' => $user ? $user->id : null,
                'username' => $user ? ($user->username ?? $user->email) : null,
                'user_agent' => Str::limit($request->userAgent() ?: '', 200),
                'payload' => collect($request->except(array(
                    'password', 'password_confirmation', 'current_password', 'token', '_token',
                )))->all(),
            );

            $allowedCountries = config('security.geo.allowed_countries', array('FR', 'IN' ,'ES'));
            if (!in_array($code, $allowedCountries, true)) {
                Log::emergency('Blocked on '.config('app.name').' (country not allowed): '.json_encode($logCtx));
                AppHelper::logger('warning', 'GeoIP Country Block', 'Country not allowed by policy', $logCtx, true);
                if (Auth::check()) {
                    Auth::logout();
                }

                return $this->deny($request, 'Access denied from your country.');
            }

            // if ($code === 'IN') {
            //     $allowedRegionsRaw = array_merge(
            //         config('security.geo.in_regions', array('Tamil Nadu', 'Karnataka')),
            //         array('union territory of puducherry')
            //     );
            //     $allowedRegions = array_map(function ($value) {
            //         return mb_strtolower(trim($value));
            //     }, $allowedRegionsRaw);

            //     if (!in_array(mb_strtolower(trim($region)), $allowedRegions, true)) {
            //         Log::warning('Blocked on '.config('app.name').' (region not allowed within India): '.json_encode($logCtx));
            //         AppHelper::logger('warning', 'GeoIP Region Block', 'Region not allowed within India', $logCtx, true);
            //         if (Auth::check()) {
            //             Auth::logout();
            //         }

            //         return $this->deny($request, 'Access restricted.');
            //     }
            // }

            return $next($request);
        } catch (\Throwable $e) {
            $errCtx = array(
                'app' => config('app.name'),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(3)->all(),
            );
            Log::error('ProtectedFromIP exception: '.json_encode($errCtx));
            AppHelper::logger('warning', 'GeoIP Middleware Exception', $e->getMessage(), $errCtx, true);

            return $next($request);
        }
    }

    private function deny($request, $message)
    {
        if ($request->expectsJson() || Str::startsWith($request->path(), 'api/')) {
            return response()->json(array('message' => $message), 403);
        }

        if (!$request->isMethod('get')) {
            return redirect()->back()->withErrors(array('username' => 'Login failed'));
        }

        return response()->view('restrict', array(), 403);
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

            return Cache::remember('tamatopup:v2:blacklisted-ip:' . $ip, now()->addMinutes(1), function () use ($ip) {
                return DB::table('firewall')
                    ->where('ip_address', $ip)
                    ->where('whitelisted', 0)
                    ->exists();
            });
        } catch (\Throwable $exception) {
            Log::warning('Firewall blacklist check failed', [
                'ip' => $ip,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
