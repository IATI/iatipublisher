<?php

declare(strict_types=1);

namespace App\IATI\Traits;

/**
 * Class MigrateUserTrait.
 */
trait MigrateUserTrait
{
    /**
     * Returns new IATI user data.
     *
     * @param $aidstreamUser
     * @param $iatiOrganization
     *
     * @return array
     */
    public function getNewUser($aidstreamUser, $iatiOrganization): array
    {
        return [
            'username'                => $aidstreamUser->username,
            'full_name'               => sprintf('%s %s', $aidstreamUser->first_name, $aidstreamUser->last_name),
            'email'                   => $aidstreamUser->email,
            'address'                 => $iatiOrganization->address,
            'organization_id'         => $iatiOrganization->id,
            'is_active'               => true,
            'email_verified_at'       => $aidstreamUser->verified ? ($aidstreamUser->verification_created_at ?: $aidstreamUser->created_at) : null,
            'password'                => $aidstreamUser->password,
            'remember_token'          => null,
            'created_at'              => $aidstreamUser->created_at,
            'updated_at'              => $aidstreamUser->updated_at,
            'role_id'                 => $this->getRoleId($aidstreamUser->role_id),
            'status'                  => true,
            'registration_method'     => $aidstreamUser->role_id === 1 ? 'existing_org' : 'user_create',
            'language_preference'     => 'en',
            'created_by'              => null,
            'updated_by'              => null,
            'deleted_at'              => null,
            'migrated_from_aidstream' => true,
        ];
    }

    /**
     * Returns role id for IATI user.
     *
     * @param $roleId
     *
     * @return int
     */
    public function getRoleId($roleId): int
    {
        if ($roleId === 1 || $roleId === 5) {
            return $this->roleRepository->getOrganizationAdminId();
        }

        return $this->roleRepository->getGeneralUserId();
    }
}
