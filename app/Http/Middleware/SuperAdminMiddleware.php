<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class SuperAdminMiddleware.
 */
class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request                                        $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (isSuperAdmin()) {
            if (session()->get('superadmin_user_id') !== auth()->user()->id) {
                auth()->loginUsingId(session()->get('superadmin_user_id'));
            }

            return $next($request);
        }

        return redirect()->route('admin.activities.index')->with(
            'error',
            'You need to be superadmin to use this route.'
        );
    }
}
