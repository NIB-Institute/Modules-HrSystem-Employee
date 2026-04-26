<?php

namespace Modules\Employee\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DashboardMiddlewareHandle
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
