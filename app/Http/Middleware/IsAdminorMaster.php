<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use app\Library\AppHelper;

class IsAdminorMaster
{
    public function handle($request, Closure $next)
    {
        if(in_array(auth()->user()->group_id,[1,2]) == false){
            AppHelper::logger('warning','MyService Access violation','User '.$request->user()->username. 'trying to access myservice',$request);
            return redirect()->back()->with('message',trans('common.access_violation'))
                ->with('message_type','warning');
        }
        return $next($request);
    }
}

