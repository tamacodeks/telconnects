<?php

namespace App\Http\Middleware;

use App\Library\AppHelper;
use App\Support\UserSessionManager;
use Closure;
use Auth;
use Session;

class LogoutDevice
{
    public function handle($request, Closure $next)
    {
        $excludedRoutes = ['dashboard','securelogin', 'check_otp', 'login', 'resend_otp'];

        if (in_array($request->route()->getName(), $excludedRoutes)) {
            return $next($request);
        }

        $sessionId = session()->getId();
        $userId = Session::get('impersonate');
        $user = auth()->user();

        if (!$user) {
            return redirect('/')->with('message', trans('common.access_violation'))
                ->with('message_type', 'warning');
        }

        if (!$userId && in_array($user->group_id, [3, 4])) {
            if (! UserSessionManager::isAllowed($user, $sessionId)) {
                \Log::warning("Session not allowed for user ID {$user->id}: active session {$sessionId}");
                Auth::logout();
                return redirect()->to('/')
                    ->with('message', trans('common.access_violation'))
                    ->with('message_type', 'warning');
            }
        }

        if (auth()->check() && auth()->user()->status != 1) {
            auth()->logout();
            return redirect()->to('/')
                ->with('message', trans('common.access_violation'))
                ->with('message_type', 'warning');
        }
        return $next($request);
    }
}
