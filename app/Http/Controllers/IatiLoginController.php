<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Session;
use Jumbojett\OpenIDConnectClient;

class IatiLoginController extends Controller
{
    /**
     * @return OpenIDConnectClient
     */
    private function getClient(): OpenIDConnectClient
    {
        $config = config('services.oidc');

        $oidc = new OpenIDConnectClient(
            provider_url : '',
            client_id    : '',
            client_secret: ''
        );

        $oidc->setRedirectURL('');
        $oidc->addScope(['openid', 'email', 'profile']);

        return $oidc;
    }

    /**
     * @throws \Jumbojett\OpenIDConnectClientException
     */
    public function redirectToProvider(): void
    {
        $this->getClient()->authenticate();
    }

    public function handleProviderCallback(): JsonResponse|RedirectResponse
    {
        try {
            dd('in handle callback');
            $oidc = $this->getClient();
            $oidc->authenticate();
            $userInfo = $oidc->requestUserInfo();

            Session::put('user', [
                'name' => $userInfo->name ?? $userInfo->preferred_username ?? 'Unknown',
                'email' => $userInfo->email ?? null,
            ]);

            return redirect()->intended('/');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @return Redirector|Application|RedirectResponse
     */
    public function logout(): Redirector|Application|RedirectResponse
    {
        Session::flush();

        $logoutUrl = config('services.oidc.logout_endpoint') . '?' . http_build_query([
                'post_logout_redirect_uri' => config('services.oidc.logout_redirect_uri'),
            ]);

        return redirect($logoutUrl);
    }
}
