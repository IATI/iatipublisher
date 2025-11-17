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
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'oidc'         => [
        'issuer'              => env('OIDC_ISSUER'),
        'client_id'           => env('OIDC_CLIENT_ID'),
        'client_secret'       => env('OIDC_CLIENT_SECRET'),
        'redirect_uri'        => env('OIDC_REDIRECT_URI'),
        'logout_endpoint'     => env('OIDC_LOGOUT_ENDPOINT'),
        'logout_redirect_uri' => env('OIDC_LOGOUT_REDIRECT_URI'),
        'userinfo_endpoint'   => env('OIDC_USERINFO_ENDPOINT'),
        'token_endpoint'      => env('OIDC_TOKEN_ENDPOINT'),
        'scopes'              => ['openid', 'email', 'profile', 'address', 'phone', 'roles', 'groups', 'iati_account', 'offline_access'],
        'iatiDesignSystemUrl' => env('IATI_DESIGN_SYSTEM_URL'),
    ],
    'registry_api' => [
        'base_url' => env('REGISTRY_API_BASE_URL', 'https://dev.api.registeryourdata.iatistandard.org/api/v1'),
        'audience' => env('REGISTRY_API_AUDIENCE', 'https://dev.api.registeryourdata.iatistandard.org/api/v1'),
        'scopes'   => ['ryd', 'ryd:reporting_org', 'ryd:reporting_org:create', 'ryd:reporting_org:update', 'ryd:reporting_org:delete', 'ryd:reporting_org:user', 'ryd:reporting_org:user:update', 'ryd:dataset', 'ryd:dataset:update', 'ryd:dataset:delete'],
    ],
];
