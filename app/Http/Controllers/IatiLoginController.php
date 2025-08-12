<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use App\IATI\Services\Audit\AuditService;
use App\IATI\Services\User\UserService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Jumbojett\OpenIDConnectClient;

class IatiLoginController extends Controller
{
    protected AuditService $auditService;

    protected UserService  $userService;

    public function __construct(AuditService $auditService, UserService $userService)
    {
        $this->auditService = $auditService;
        $this->userService = $userService;
    }

    /**
     * @throws \Jumbojett\OpenIDConnectClientException
     */
    public function redirectToProvider(): void
    {
        $this->getClient()->authenticate();
    }

    private function getClient(): OpenIDConnectClient
    {
        $config = config('services.oidc');

        $oidc = new OpenIDConnectClient(
            provider_url: $config['issuer'],
            client_id: $config['client_id'],
            client_secret: $config['client_secret']
        );

        $oidc->setRedirectURL(route('login.iati.callback'));
        $oidc->addScope([
            'openid',
            'email',
            'profile',
        ]);

        //        $oidc->addAuthParam([
        //            'audience' => 'https://registry.iatistandard.org/api/v1'
        //        ]);

        //        $oidc->addAuthParam(['prompt' => 'consent']);

        return $oidc;
    }

    public function handleProviderCallback(): RedirectResponse
    {
        try {
            $oidc = $this->getClient();

            if (!$oidc->authenticate()) {
                return redirect()->route('login')->withErrors(['message' => 'Authentication failed']);
            }

            $idToken = $oidc->getIdToken();
            $claims = $oidc->getVerifiedClaims();

            $sub = $claims->sub ?? null;

            if (!$sub) {
                throw new Exception('');
            }

            session(['oidc_id_token' => $idToken]);

            $user = $this->findOrCreateUser($sub, (array) ($claims ?? []));

            Auth::login($user);

            return redirect()->intended('/');
        } catch (Exception $e) {
            logger()->error('OIDC Authentication Failed: ' . $e->getMessage());

            return redirect()->route('login')->withErrors($e->getMessage());
        }
    }

    private function findOrCreateUser(string $sub, array $claims): User
    {
        $email = Arr::get($claims, 'email') ?? 'temp_' . $sub . '@noemail.local';

        $user = User::where('sub', $sub)->first();

        if (!$user) {
            $user = User::where('email', $email)->first();

            if ($user && !$user->sub) {
                $user->sub = $sub;
                $user->save();
            }
        }

        if (!$user) {
            $user = User::create([
                'email'                   => $email,
                'password'                => null,
                'username'                => $this->extractName($claims),
                'full_name'               => $this->extractName($claims),
                'address'                 => Arr::get($claims, 'address'),
                'organization_id'         => 150,
                'is_active'               => true,
                'email_verified_at'       => now(),
                'role_id'                 => Arr::get($claims, 'org_handle') === 'iati' ? Role::where(
                    'role',
                    'iati_admin'
                )->first()->id : Role::where('role', 'admin')->first()->id,
                'status'                  => true,
                'language_preference'     => 'en',
                'migrated_from_aidstream' => false,
                'created_at'              => now(),
                'updated_at'              => now(),
                'registration_method'     => 'existing_org',
                'last_logged_in'          => now(),
                'sub'                     => Arr::get($claims, 'sub'),
                'preferred_username'      => Arr::get($claims, 'preferred_username'),
                'given_name'              => Arr::get($claims, 'given_name'),
                'family_name'             => Arr::get($claims, 'family_name'),
                'locale'                  => Arr::get($claims, 'locale'),
                'picture'                 => Arr::get($claims, 'picture'),
                'sign_on_method'          => 'oidc',
            ]);
        }

        return $user;
    }

    private function extractName($claims): string
    {
        return Arr::get($claims, 'name') ?? Arr::get($claims, 'preferred_username') ?? Arr::get(
            $claims,
            'given_name'
        ) ?? Arr::get($claims, 'family_name') ?? 'User-' . substr($claims['sub'], 0, 8);
    }

    /**
     * @return RedirectResponse
     *
     * @throws \Jumbojett\OpenIDConnectClientException
     */
    public function logout(): RedirectResponse
    {
        $idToken = session('oidc_id_token');
        $config = config('services.oidc');

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        if ($idToken && !empty($config['logout_endpoint']) && !empty($config['logout_redirect_uri'])) {
            $this->getClient()->signOut($idToken, $config['logout_redirect_uri']);
        }

        return redirect('/');
    }

    public function getApiAccessToken($userAccessToken)
    {
        $config = config('services.oidc');

        $response = Http::asForm()->post($config['token_endpoint'], [
            'grant_type'         => 'urn:ietf:params:oauth:grant-type:token-exchange',
            'client_id'          => $config['client_id'],
            'client_secret'      => $config['client_secret'],
            'subject_token'      => $userAccessToken, // Token from OIDC login
            'subject_token_type' => 'urn:ietf:params:oauth:token-type:access_token',
            'audience'           => 'https://registry.iatistandard.org/api/v1',
            'scope'              => 'register_your_data ryd_org:read ryd_org:create ryd_dataset:read ryd_dataset:update ryd_org:user_admin',
        ]);

        return $response->json()['access_token'];
    }
}
