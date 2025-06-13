<?php

namespace Backpack\CRUD\app\Http\Middleware;

use Closure;

class UseBackpackAuthGuardInsteadOfDefaultAuthGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  ?string  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        app('auth')->setDefaultDriver(config('backpack.base.guard'));

        return $next($request);
    }
}
