<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LogoutIfInactive
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!Auth::user()->status || Auth::user()->deleted_at) {
            Auth::logout();
        }

        return $next($request);
    }
}
