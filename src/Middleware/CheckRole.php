<?php

namespace Juanfv2\BaseCms\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class CheckRole
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $isDev = request()->headers->get('dev', '');
        if ($isDev) {
            \Debugbar::enable();
        }

        if (!$this->userHasPermission()) {
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
        $cRoute = Route::getCurrentRoute()->action['as'];

        // logger(__FILE__ . ':' . __LINE__ . ' $cRoute ', [$cRoute]);

        if ($cRoute == 'api.login.logout') {
            return true;
        }

        if (request()->has('cp')) {
            $cRoute = request()->get('cp', '-.-._._.-.-');
        }

        // mysql
        $menu = DB::select('call sp_has_permission (?, ?);', [auth()->id(), $cRoute]);

        $hasPermission = $menu[0]->aggregate > 0;

        // -- sql-server $menu = DB::select('execute sp_has_permission ?, ?;', [$this->id, $cRoute]);

        // logger(__FILE__ . ':' . __LINE__ . ' $this->id ', [' . ' . $this->id . ' . ' . $cRoute . ' . \'' . $hasPermission . '\'']);

        return $hasPermission;
    }
}
