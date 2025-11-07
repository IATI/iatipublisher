<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasOrganization
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
//        return $next($request);
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        $adminRoles = ['superadmin', 'iati_admin'];

        if ($user->role?->role && in_array($user->role->role, $adminRoles)) {
            return $next($request);
        }

        if (is_null($user->organization_id)) {
            if ($this->isApiRequest($request)) {
                return $next($request);
            }

            return redirect()->route('onboarding.organization-missing');
        }

        return $next($request);
    }

    public function isApiRequest($request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }
}
