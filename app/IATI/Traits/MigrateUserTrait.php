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

    /**
     * Migrates users from AidStream to IATI Publisher if needed.
     *
     * @param $iatiOrganization
     * @param $aidstreamUsers
     * @param $aidStreamOrganization
     *
     * @return void
     */
    public function migrateUsersIfNeeded($iatiOrganization, $aidstreamUsers, $aidStreamOrganization): void
    {
        $iatiAllUsers = $iatiOrganization->users;

        $iatiUserEmails = ($iatiAllUsers && count($iatiAllUsers)) ? $iatiAllUsers->pluck('email')->toArray() : [];
        $iatiUserUsernames = ($iatiAllUsers && count($iatiAllUsers)) ? $iatiAllUsers->pluck('username')->toArray() : [];

        if (count($aidstreamUsers)) {
            foreach ($aidstreamUsers as $aidstreamUser) {
                if (!in_array($aidstreamUser->email, $iatiUserEmails, true) && !in_array($aidstreamUser->username, $iatiUserUsernames, true)) {
                    $this->logInfo(
                        'Started user migration for user id: ' . $aidstreamUser->id . ' of organization: ' . $aidStreamOrganization->name
                    );
                    $iatiUser = $this->userService->create(
                        $this->getNewUser($aidstreamUser, $iatiOrganization)
                    );
                    $this->auditService->setAuditableId($iatiUser->id)->auditMigrationEvent($iatiUser, 'migrated-user');
                    $this->logInfo(
                        'Completed user migration for user id: ' . $aidstreamUser->id . ' of organization: ' . $aidStreamOrganization->name
                    );
                } elseif (in_array($aidstreamUser->email, $iatiUserEmails, true) && in_array($aidstreamUser->username, $iatiUserUsernames, true)) {
                    $message = "User with email {$aidstreamUser->email} and username {$aidstreamUser->username} already exists in IATI Publisher so not migrated.";
                    $this->setGeneralError($message)->setDetailedError(
                        $message,
                        $aidStreamOrganization->id,
                        'users',
                        $aidstreamUser->id,
                        $iatiOrganization->id
                    );
                } elseif (in_array($aidstreamUser->email, $iatiUserEmails, true)) {
                    $message = "User with email {$aidstreamUser->email} already exists in IATI Publisher so not migrated.";
                    $this->setGeneralError($message)->setDetailedError(
                        $message,
                        $aidStreamOrganization->id,
                        'users',
                        $aidstreamUser->id,
                        $iatiOrganization->id
                    );
                } elseif (in_array($aidstreamUser->username, $iatiUserUsernames, true)) {
                    $message = "User with username {$aidstreamUser->username} already exists in IATI Publisher so not migrated.";
                    $this->setGeneralError($message)->setDetailedError(
                        $message,
                        $aidStreamOrganization->id,
                        'users',
                        $aidstreamUser->id,
                        $iatiOrganization->id
                    );
                }
            }
        }
    }
}
