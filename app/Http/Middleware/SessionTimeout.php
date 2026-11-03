<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SessionTimeout
{
    protected $timeout = 7200; // Timeout in seconds (2 hours)

    public function handle($request, Closure $next)
    {
        if (!Auth::check() && $this->routeRequiresAuth($request)) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated.',
                    'redirect_url' => '/',
                ], 401);
            }

            return redirect()->away('/')->with([
                'message' => 'Please login again.',
                'message_type' => 'warning'
            ]);
        }

        if (Auth::check()) {
            $lastActivity = Session::get('last_activity_time');
            $now = now()->timestamp;

            if ($lastActivity && ($now - $lastActivity > $this->timeout)) {
                Auth::logout();
                Session::flush();

                return redirect()->away('/')->with([
                    'message' => 'You have been logged out due to inactivity.',
                    'message_type' => 'warning'
                ]);
            }

            Session::put('last_activity_time', $now);
        }

        return $next($request);
    }

    protected function routeRequiresAuth($request)
    {
        $route = $request->route();
        if (!$route || !method_exists($route, 'gatherMiddleware')) {
            return false;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            if (!is_string($middleware)) {
                continue;
            }

            if ($middleware === 'auth' || strpos($middleware, 'auth:') === 0) {
                return true;
            }

            if ($middleware === \Illuminate\Auth\Middleware\Authenticate::class) {
                return true;
            }
        }

        return false;
    }
}
