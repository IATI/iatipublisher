<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'oidc' => [
        'issuer'              => env('OIDC_ISSUER'),
        'client_id'           => env('OIDC_CLIENT_ID'),
        'client_secret'       => env('OIDC_CLIENT_SECRET'),
        'redirect_uri'        => env('OIDC_REDIRECT_URI'),
        'logout_endpoint'     => env('OIDC_LOGOUT_ENDPOINT'),
        'logout_redirect_uri' => env('OIDC_LOGOUT_REDIRECT_URI'),
    ],
];
