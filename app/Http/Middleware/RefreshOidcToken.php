<?php

namespace App\Http\Middleware;

use App\IATI\Services\OIDC\IatiOidcService;
use Closure;
use Illuminate\Http\Request;

class RefreshOidcToken
{
    public function __construct(private IatiOidcService $tokenService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && session()->has('oidc_access_token')) {
            try {
                $this->tokenService->getAccessToken();
            } catch (\Throwable $e) {
                logger()->warning('OIDC token refresh failed', ['message' => $e->getMessage()]);

                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('web.index.login')->withErrors([
                    'message' => 'Your session expired. Please sign in again.',
                ]);
            }
        }

        return $next($request);
    }
}
