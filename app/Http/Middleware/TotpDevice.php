<?php

namespace App\Http\Middleware;

use app\Library\AppHelper;
use App\Support\UserSessionManager;
use Closure;
use Auth;
use Session;

class TotpDevice
{
    public function handle($request, Closure $next)
    {
        $user_id = \Session::get('impersonate');
        if(!$user_id){
            $user = auth()->user();
            if ($user && UserSessionManager::requiresTotpSetup($user)) {
                if (! UserSessionManager::hasVerifiedTotp($user)) {
                    return redirect()->to('enable-2fa')->with('message', trans('common.access_totp'))
                        ->with('message_type', 'warning');
                }
            }
        }
        return $next($request);
    }

}
