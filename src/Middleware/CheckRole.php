<?php

namespace Juanfv2\BaseCms\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
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
        $default_prefix = config('base-cms.default_prefix');

        $menu = null;

        switch ($default_prefix) {
            case 'pgsql-':
                $menu = DB::select('SELECT public.sp_has_permission(?, ?,?) as "aggregate";', [auth()->id(), $cRouteParent, $cRouteChild]);
                break;
            case 'sqlsrv-':
                $menu = DB::select('execute sp_has_permission ?, ?, ?;', [auth()->id(), $cRouteParent, $cRouteChild]);
                break;
            default:
                // mysql
                $menu = DB::select('call sp_has_permission (?, ?, ?);', [auth()->id(), $cRouteParent, $cRouteChild]);
                // code...
                break;
        }

        $hasPermission = $menu ? $menu[0]->aggregate > 0 : 0;

        if (! $hasPermission) {
            logger(__FILE__.':'.__LINE__.' u:'.auth()->id().":'$hasPermission': [call sp_save_permission_permission('$cRouteParent','$cRouteChild');]:-", [$menu]);
        }

        return $hasPermission;
    }
}
