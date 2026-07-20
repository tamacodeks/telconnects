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
        if (Auth::check()) {
            $lastActivity = Session::get('last_activity_time');
            $now = now()->timestamp;

            if ($lastActivity && ($now - $lastActivity > $this->timeout)) {
                Auth::logout();
                Session::flush();

                return redirect('/')->with([
                    'message' => 'You have been logged out due to inactivity.',
                    'message_type' => 'warning'
                ]);
            }

            Session::put('last_activity_time', $now);
        }

        return $next($request);
    }
}
