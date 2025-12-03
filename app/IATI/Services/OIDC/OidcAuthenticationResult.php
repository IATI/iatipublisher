<?php

namespace App\IATI\Services\OIDC;

/**
 * A simple, immutable Data Transfer Object to hold the results of a successful OIDC authentication.
 */
final class OidcAuthenticationResult
{
    public function __construct(
        public string $idToken,
        public string $accessToken,
        public string $refreshToken,
        public string $expiresIn,
        public string $uuid,
        public array $claims
    ) {
    }
}
