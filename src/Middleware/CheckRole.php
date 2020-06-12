<?php

namespace Juanfv2\BaseCms\Middleware;

use Closure;

class CheckRole
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->user()->hasPermission()) {
            // Redirect...
            return response()->json(['message' => __('auth.no.auth')], 401);
            //abort(401);
        }

        return $next($request);
    }
}
