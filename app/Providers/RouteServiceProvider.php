<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/activities';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware(['admin', 'auth', 'has.organization', 'activity', 'api'])
                ->name('api.')
                ->group(base_path('routes/api.php'));
            Route::name('general.')->group(base_path('routes/general.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware(['admin', 'auth', 'has.organization'])
                ->name('admin.')
                ->group(base_path('routes/setting.php'));

            Route::middleware(['admin', 'auth', 'has.organization'])
                ->name('admin.')
                ->group(base_path('routes/organization.php'));

            Route::middleware(['admin', 'auth', 'has.organization'])
                ->name('admin.')
                ->group(base_path('routes/user.php'));

            Route::middleware(['admin', 'auth', 'has.organization', 'activity'])
                ->name('admin.')
                ->group(base_path('routes/activity.php'));

            Route::middleware(['admin', 'auth', 'has.organization'])
                ->name('admin.')
                ->group(base_path('routes/publish.php'));

            Route::middleware(['admin', 'auth', 'has.organization'])
                ->name('admin.')
                ->group(base_path('routes/import.php'));

            Route::middleware(['admin', 'auth', 'has.organization'])
                ->name('admin.')
                ->group(base_path('routes/dashboard.php'));

            Route::middleware(['admin', 'auth', 'has.organization', 'superadmin'])
                ->name('superadmin.')
                ->group(base_path('routes/superadmin.php'));

            Route::middleware(['admin', 'auth', 'has.organization'])
                ->name('admin.')
                ->group(base_path('routes/download.php'));
            Route::middleware(['admin', 'auth', 'has.organization', 'superadmin'])
                ->name('audit.')
                ->group(base_path('routes/audit.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', static function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
