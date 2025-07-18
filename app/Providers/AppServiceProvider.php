<?php

namespace App\Providers;

use App\Database\PostgresConnection;
use App\IATI\Traits\IatiTranslationTrait;
use App\SpamEmail;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new PostgresConnection($connection, $database, $prefix, $config);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        URL::forceScheme('https');

        Validator::extend('not_in_spam_emails', function ($attribute, $value, $parameters, $validator) {
            return !SpamEmail::where('email', $value)->exists();
        });

        Horizon::auth(function ($request) {
            if (env('APP_ENV') === 'local') {
                return true;
            }
        });

        /*
         * Supplies translatedData to blade files that are rendered via xyzController -> return view('xyz', compact())
         */
        View::composer('*', function ($view) {
            $translator = new class {
                use IatiTranslationTrait;
            };

            [$translatedData, $currentLanguage] = $translator->getPageTranslationDependency();

            $view->with(compact('translatedData', 'currentLanguage'));
        });
    }
}
