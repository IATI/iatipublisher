<?php

namespace App\IATI\Services\OIDC;

final class OidcTokenPair
{
    public function __construct(
        public string $accessToken,
        public ?string $refreshToken,
        public ?int $expiresIn,
    ) {
    }
}
