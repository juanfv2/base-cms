<?php

namespace Juanfv2\BaseCms\Middleware;

use App\Models\Auth\Permission;
use Closure;
use Illuminate\Support\Facades\Route;

class CheckRole
{
    /**
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $isDev = request()->headers->get('dev', '');
        if ($isDev) {
            \Barryvdh\Debugbar\Facades\Debugbar::enable();
        }

        if (! $this->userHasPermission()) {
            // Redirect...
            return response()->json(['message' => __('auth.no.auth')], 401);
            //abort(401);
        }

        return $next($request);
    }

    /**
     * !!!
     **/
    private function userHasPermission()
    {
        $cRouteParent = Route::getCurrentRoute()->action['as'];
        $cRouteChild = request()->get('cp', '-.-');

        /**
         * array: config('base-cms.authenticated')
         * api.names that only authenticated is needed
         */
        $authenticated = config('base-cms.authenticated') ?? ['api.login.logout'];

        if (in_array($cRouteParent, $authenticated)) {
            return true;
        }

        if ($cRouteChild != '-.-') {
            $temp = $cRouteParent;
            $cRouteParent = $cRouteChild;
            $cRouteChild = $temp;
        }

        $hasPermission = Permission::userHasPermission(auth()->id(), $cRouteParent, $cRouteChild);

        if (! $hasPermission) {
            logger(__FILE__.':'.__LINE__.' u:'.auth()->id().":'$hasPermission': [{ \"_urlParent\": \"$cRouteParent\", \"_urlChild\": \"$cRouteChild\" }]:-", [$hasPermission]);
            logger(__FILE__.':'.__LINE__.' u:'.auth()->id().":'$hasPermission': [call sp_save_permission_permission('$cRouteParent','$cRouteChild');]:-", [$hasPermission]);
        }

        return $hasPermission;
    }
}
