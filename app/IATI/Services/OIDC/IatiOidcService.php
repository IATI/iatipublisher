<?php

namespace App\IATI\Services\OIDC;

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
    private function buildClient(): OpenIDConnectClient
    {
        $client = new OpenIDConnectClient(
            provider_url : $this->oidcConfig['issuer'],
            client_id    : $this->oidcConfig['client_id'],
            client_secret: $this->oidcConfig['client_secret']
        );

        $allScopes = array_unique(array_merge($this->oidcConfig['scopes'], $this->apiConfig['scopes']));

        $client->setRedirectURL($this->oidcConfig['redirect_uri']);
        $client->addScope($allScopes);
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

            $client->verifyJWTSignature($idToken);

            $claims = (array) ($client->getVerifiedClaims() ?? []);
            $subject = Arr::get($claims, 'sub');

            if (!$subject) {
                throw new OidcAuthenticationException('User identifier (sub) not found in authentication response.');
            }

            return new OidcAuthenticationResult($idToken, $accessToken, $subject, $claims);
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
}
