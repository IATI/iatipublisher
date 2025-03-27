<?php

namespace App\Providers;

use App\Database\PostgresConnection;
use App\SpamEmail;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;
use URL;

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
        Validator::extend('not_in_spam_emails', function ($attribute, $value, $parameters, $validator) {
            return !SpamEmail::where('email', $value)->exists();
        });

        if (config('app.env') === 'production' || config('app.env') === 'staging') {
            URL::forceScheme('https');
        }

        Horizon::auth(function ($request) {
            if (env('APP_ENV') === 'local') {
                return true;
            }
        });
    }
}
