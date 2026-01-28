<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Activity permissions.
     * @var array
     */
    protected array $superadmin_permissions = ['list_organizations', 'proxy_user'];

    /**
     * Activity admin_permissions.
     * @var array
     */
    protected array $admin_permissions = ['crud_organization', 'publish_organization', 'edit_setting'];

    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define(
            'crud_users',
            function ($user) {
                if (in_array($this->getUserRole($user), ['superadmin', 'iati_admin', 'admin'])) {
                    return true;
                } else {
                    return false;
                }
            }
        );

        Gate::define('crud_activity', function ($user) {
            return $this->getUserRole($user) === 'admin';
        });

        Gate::define('view_setting', function ($user) {
            return $this->getUserRole($user) === 'admin';
        });

        Gate::define('view_organization', function ($user) {
            return $this->getUserRole($user) === 'admin';
        });

        foreach ($this->admin_permissions as $permission) {
            Gate::define(
                $permission,
                function ($user) {
                    if ($this->getUserRole($user) === 'admin') {
                        return true;
                    } else {
                        return false;
                    }
                }
            );
        }

        foreach ($this->superadmin_permissions as $permission) {
            Gate::define(
                $permission,
                function ($user) {
                    return $this->getUserRole($user) === 'superadmin' || $this->getUserRole($user) === 'iati_admin';
                }
            );
        }
    }

    /**
     * Returns user role.
     *
     * @param $user
     *
     * @return string
     */
    protected function getUserRole($user): string
    {
        return $user->role->role;
    }
}
