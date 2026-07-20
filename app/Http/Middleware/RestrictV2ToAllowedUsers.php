<?php

namespace App\Http\Middleware;

use App\Support\V2Access;
use Closure;

class RestrictV2ToAllowedUsers
{
    public function handle($request, Closure $next)
    {
        if (V2Access::userCanUseV2($request->user())) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 403,
                'message' => 'V2 access is not enabled for this user.',
                'redirect' => V2Access::legacyUrlForRequest($request),
            ], 403);
        }

        return redirect()->to(V2Access::legacyUrlForRequest($request));
    }
}
