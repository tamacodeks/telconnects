<?php

namespace App\Http\Middleware;

use app\Library\AppHelper;
use App\Models\Service;
use App\Models\UserAccess;
use Closure;

class ServiceAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $segment = $request->segment(1);

        // Normalize segment (e.g., 'bus' becomes 'flix-bus')
        if ($segment === 'bus') {
            $segment = 'flix-bus';
        }
        if($segment === 'callings-cards'){
            $segment = 'calling-cards';
        }
        $serviceName = ucwords(str_replace('-', ' ', $segment));
        $check_service = Service::where('name', $serviceName)->first();

        // If service exists, check access
        if ($check_service) {
            $hasAccess = UserAccess::where('user_id', auth()->id())
                ->where('service_id', $check_service->id)
                ->where('status', 1)
                ->exists();
            if (!$hasAccess) {
                AppHelper::logger(
                    'warning',
                    'Service Access Restricted',
                    'User ' . auth()->user()->username . ' tried to access: ' . $segment
                );

                // JSON / AJAX response
                if ($request->ajax() || $request->expectsJson()) {
                    // Use responder() if available; otherwise fallback
                    if (function_exists('responder')) {
                        return responder()
                            ->error("error", trans('service.service_unavailable'))
                            ->respond(403);
                    }

                    return response()->json([
                        'status' => 'error',
                        'message' => trans('service.service_unavailable')
                    ], 403);
                }

                // Redirect fallback for browser requests
                return redirect()->to('/dashboard')
                    ->with('message', trans('service.service_unavailable'))
                    ->with('message_type', 'warning');
            }
        }
        return $next($request);
    }
}
