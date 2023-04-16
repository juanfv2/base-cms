<?php

namespace Juanfv2\BaseCms\Middleware;

use Closure;
use Sentry\State\Scope;

class SentryContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (app()->bound('sentry')) {
            // \Sentry\configureScope(function (Scope $scope): void {
            //     $c = request()->headers->get('r-country', '--');
            //     $scope->setTag('r-country', $c);
            // });
            if (auth()->check()) {
                \Sentry\configureScope(function (Scope $scope): void {
                    $scope->setUser([
                        'id' => auth()->user()->id,
                        'email' => auth()->user()->email,
                    ]);
                });
            }
        }

        return $next($request);
    }
}
