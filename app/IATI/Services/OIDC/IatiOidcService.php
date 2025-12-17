<?php

namespace App\IATI\Services\OIDC;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;
use RuntimeException;

class IatiOidcService
{
    private array $oidcConfig;

    private array $apiConfig;

    /**
     * The service is constructed with the necessary configuration via dependency injection.
     */
    public function __construct()
    {
        $this->oidcConfig = config('services.oidc');
        $this->apiConfig = config('services.registry_api');

        if (empty($this->oidcConfig) || empty($this->apiConfig)) {
            throw new RuntimeException('OIDC or Registry API configuration is missing in services.php.');
        }
    }

    /**
     * Initiates the OIDC authentication flow by redirecting the user's browser.
     * This method will terminate the script and issue a redirect.
     */
    public function redirectToProvider(): void
    {
        $this->buildClient()->authenticate();
    }

    /**
     * Private helper to construct and configure the OIDC client library.
     */
    private function buildClient($isRefresh = false): OpenIDConnectClient
    {
        $client = new OpenIDConnectClient(
            provider_url : $this->oidcConfig['issuer'],
            client_id    : $this->oidcConfig['client_id'],
            client_secret: $this->oidcConfig['client_secret']
        );

        if (!$isRefresh) {
            $allScopes = array_unique(array_merge($this->oidcConfig['scopes'], $this->apiConfig['scopes']));
            $client->addScope($allScopes);
        }

        $client->setRedirectURL($this->oidcConfig['redirect_uri']);
        $client->addAuthParam(['audience' => $this->apiConfig['audience']]);

        return $client;
    }

    /**
     * Handles the incoming callback from the OIDC provider.
     *
     * @return OidcAuthenticationResult The result of a successful authentication.
     * @throws OidcAuthenticationException If the authentication process fails for any reason.
     */
    public function handleCallback(): OidcAuthenticationResult
    {
        try {
            $client = $this->buildClient();

            if (!$client->authenticate()) {
                throw new OidcAuthenticationException(
                    'OIDC authentication failed. The provider did not return a valid response.'
                );
            }

            $idToken = $client->getIdToken();
            $accessToken = $client->getAccessToken();
            $refreshToken = $client->getRefreshToken();
            $expiresIn = isset($client->getTokenResponse()->expires_in) ? (int) $client->getTokenResponse()->expires_in : null;

            $client->verifyJWTSignature($idToken);

            $claims = (array) ($client->getVerifiedClaims() ?? []);
            $uuid = Arr::get($claims, 'sub');

            if (!$uuid) {
                throw new OidcAuthenticationException('User identifier (sub) not found in authentication response.');
            }

            return new OidcAuthenticationResult($idToken, $accessToken, $refreshToken, $expiresIn, $uuid, $claims);
        } catch (OpenIDConnectClientException $e) {
            throw new OidcAuthenticationException('OIDC Client Error: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Logs the user out of the OIDC provider's session.
     * This method will terminate the script and issue a redirect.
     *
     * @param string $idToken
     *
     * @throws OpenIDConnectClientException
     */
    public function logout(string $idToken): void
    {
        $logoutEndpoint = Arr::get($this->oidcConfig, 'logout_endpoint');
        $logoutRedirectUri = Arr::get($this->oidcConfig, 'logout_redirect_uri');

        if ($idToken && $logoutEndpoint && $logoutRedirectUri) {
            $this->buildClient()->signOut($idToken, $logoutRedirectUri);
        }
    }

    /**
     * Attempts to refresh the access token using the given refresh token.
     * Communicates with the OIDC provider and returns a new token pair.
     *
     * @param string $refreshToken
     * @return OidcTokenPair
     *
     * @throws OidcAuthenticationException
     */
    public function refreshAccessToken(string $refreshToken): OidcTokenPair
    {
        try {
            $client = $this->buildClient(true);
            $response = $client->refreshToken($refreshToken);
        } catch (OpenIDConnectClientException $e) {
            throw new OidcAuthenticationException('Unable to refresh access token: ' . $e->getMessage(), 0, $e);
        }

        if (empty($response->access_token)) {
            throw new OidcAuthenticationException('Refresh response did not contain an access token.');
        }

        return new OidcTokenPair(
            $response->access_token,
            $response->refresh_token ?? $refreshToken,
            isset($response->expires_in) ? (int) $response->expires_in : null,
        );
    }

    /**
     * Retrieves the current access token from session.
     * If forced or expired, automatically refreshes the token.
     *
     * @param bool $forceRefresh
     * @return string
     *
     * @throws OidcAuthenticationException
     */
    public function getAccessToken(bool $forceRefresh = false): string
    {
        $accessToken = session('oidc_access_token');
        if (!$accessToken) {
            throw new OidcAuthenticationException('No OIDC access token in session.');
        }

        if ($forceRefresh || $this->tokenExpired()) {
            $accessToken = $this->refreshUsingStoredToken();
        }

        return $accessToken;
    }

    /**
     * Determines whether the stored access token has expired.
     *
     * @return bool
     */
    private function tokenExpired(): bool
    {
        $expiresAt = session('oidc_access_token_expires_at');

        return $expiresAt && Carbon::parse($expiresAt)->isPast();
    }

    /**
     * Refreshes the access token using the stored session refresh token.
     * Saves the newly returned tokens back into the session.
     *
     * @return string
     *
     * @throws OidcAuthenticationException
     */
    private function refreshUsingStoredToken(): string
    {
        $refreshToken = session('oidc_refresh_token');

        if (!$refreshToken) {
            throw new OidcAuthenticationException('No OIDC refresh token in session.');
        }

        $newTokens = $this->refreshAccessToken($refreshToken);

        session([
            'oidc_access_token'            => $newTokens->accessToken,
            'oidc_refresh_token'           => $newTokens->refreshToken ?? $refreshToken,
            'oidc_access_token_expires_at' => $newTokens->expiresIn
                ? now()->addSeconds($newTokens->expiresIn - 60)->toIso8601String()
                : null,
        ]);

        return $newTokens->accessToken;
    }
}
