<?php

declare(strict_types=1);

namespace App\IATI\Services\User;

use App\IATI\Models\User\User;
use App\IATI\Repositories\ApiLog\ApiLogRepository;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Repositories\Setting\SettingRepository;
use App\IATI\Repositories\User\RoleRepository;
use App\IATI\Repositories\User\UserRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserService.
 */
class UserService
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepo;

    /**
     * @var RoleRepository
     */
    private RoleRepository $roleRepo;

    /**
     * @var ApiLogRepository
     */
    private ApiLogRepository $apiLogRepo;

    /**
     * @var OrganizationRepository
     */
    private OrganizationRepository $organizationRepo;

    /**
     * @var SettingRepository
     */
    private SettingRepository $settingRepo;

    /**
     * UserService constructor.
     *
     * @param UserRepository         $userRepo
     * @param RoleRepository         $roleRepo
     * @param OrganizationRepository $organizationRepo
     * @param ApiLogRepository   $apiLogRepo
     * @param SettingRepository $settingRepo
     */
    public function __construct(UserRepository $userRepo, RoleRepository $roleRepo, OrganizationRepository $organizationRepo, ApiLogRepository $apiLogRepo, SettingRepository $settingRepo)
    {
        $this->userRepo = $userRepo;
        $this->roleRepo = $roleRepo;
        $this->organizationRepo = $organizationRepo;
        $this->apiLogRepo = $apiLogRepo;
        $this->settingRepo = $settingRepo;
    }

    /**
     * Store user.
     *
     * @param array $data
     *
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->userRepo->store($data);
    }

    /**
     * Register new user.
     *
     * @param array $data
     *
     * @return Model
     */
    public function registerNewUser(array $data): Model
    {
        $publisherSourceType = Arr::get($data, 'source', 'secondary_source');
        $secondaryReporterValue = $publisherSourceType === 'secondary_source' ? '1' : '0';
        $narrativeValue = $publisherSourceType === 'primary_source'
            ? [['narrative' => $data['publisher_name'], 'language' => $data['default_language']]]
            : [['narrative' => null, 'language' => null]];

        $organization = $this->organizationRepo->createOrganization([
            'publisher_id'        => $data['publisher_id'],
            'publisher_name'      => $data['publisher_name'],
            'country'             => $data['country'] ?? null,
            'registration_agency' => $data['registration_agency'],
            'registration_number' => $data['registration_number'],
            'registration_type'   => 'new_org',
            'publisher_type'      => $data['publisher_type'],
            'identifier'          => $data['registration_agency'] . '-' . $data['registration_number'],
            'iati_status'         => 'pending',
            'name'                => [['narrative' => $data['publisher_name'], 'language' => $data['default_language']]],
            'reporting_org'       => [[
                'type'               => $data['publisher_type'],
                'ref'                => $data['identifier'],
                'secondary_reporter' => $secondaryReporterValue,
                'narrative'          => $narrativeValue,
            ]],
        ]);

        $this->settingRepo->store([
            'organization_id' => $organization['id'],
            'publishing_info' => [
                'publisher_id'           => $data['publisher_id'],
                'api_token'              => $data['token'],
                'publisher_verification' => true,
                'token_verification'     => true,
                'token_status'           => 'Pending',
            ],
            'default_values' => [
                'default_currency' => '',
                'default_language' => $data['default_language'],
            ],
        ]);

        $user = $this->userRepo->store([
            'full_name'       => $data['full_name'],
            'email'           => $data['email'],
            'organization_id' => $organization['id'],
            'password'        => Hash::make($data['password']),
            'role_id'         => $this->roleRepo->getOrganizationAdminId(),
        ]);

        User::sendEmail();

        return $user;
    }

    /**
     * Returns user if found.
     *
     * @param $id
     *
     * @return object
     */
    public function getUser($id): object
    {
        return $this->userRepo->getUser($id);
    }

    /**
     * Check publisher id in iati registry.
     *
     * @param string $publisher_id
     * @param bool $exists
     *
     * @return array
     * @throws GuzzleException
     */
    public function checkPublisher(string $publisher_id, bool $exists = true): array
    {
        dd($exists);
        $clientConfig = ['base_uri' => env('IATI_API_ENDPOINT')];
        $clientConfig['headers']['User-Agent'] = 'iati-publisher';

        $requestConfig = [
            'http_errors' => false,
        ];

        if (env('APP_ENV') !== 'production') {
            $clientConfig['headers']['X-CKAN-API-Key'] = env('IATI_API_KEY');
            $requestConfig['auth'] = [env('IATI_USERNAME'), env('IATI_PASSWORD')];
        }

        $client = new Client($clientConfig);
        $res = $client->request('GET', env('IATI_API_ENDPOINT') . '/action/organization_list', $requestConfig);
        $this->apiLogRepo->store(generateApiInfo('GET', env('IATI_API_ENDPOINT') . '/action/organization_list', $requestConfig, $res));

        $errors = [];

        if ($res->getStatusCode() === 404) {
            if ($exists) {
                $errors['publisher_id'] = trans('common/common.publisher_id_doesnt_exist_in_iati_registry');
            }

            return $errors;
        }

        $response = json_decode($res->getBody()->getContents())->result;

        if (!in_array($publisher_id, $response) && $exists) {
            $errors['publisher_id'] = [trans('common/common.publisher_id_doesnt_match_your_iati_registry_information')];
        }

        if (in_array($publisher_id, $response) && !$exists) {
            $errors['publisher_id'] = [trans('user/user_service.publisher_id_already_exists_in_iati_registry')];
        }

        return $errors;
    }

    /**
     * Check if iatiIdentifier already exists at registry.
     *
     * @param string $identifier
     * @return array
     * @throws GuzzleException
     */
    public function checkIATIIdentifier(string $identifier): array
    {
        $clientConfig = ['base_uri' => env('IATI_API_ENDPOINT')];
        $clientConfig['headers']['User-Agent'] = 'iati-publisher';

        $requestConfig = [
            'http_errors' => false,
            'query'       => ['q' => $identifier ?? '', 'all_fields' => true, 'include_extras' => true],
        ];

        if (env('APP_ENV') !== 'production') {
            $clientConfig['headers']['X-CKAN-API-Key'] = env('IATI_API_KEY');
            $requestConfig['auth'] = [env('IATI_USERNAME'), env('IATI_PASSWORD')];
        }

        $client = new Client($clientConfig);
        $res = $client->request('GET', env('IATI_API_ENDPOINT') . '/action/organization_list', $requestConfig);
        $this->apiLogRepo->store(generateApiInfo('GET', env('IATI_API_ENDPOINT') . '/action/organization_list', $requestConfig, $res));

        $errors = [];

        if ($res->getStatusCode() === 404) {
            $errors['error'] = ['Error occurred while trying to check user email.'];

            return $errors;
        }

        $result = json_decode($res->getBody()->getContents())->result;

        if (!empty($result)) {
            foreach ($result as $publisher) {
                if (Arr::get($publisher, 'publisher_iati_id', false) === $identifier) {
                    return [
                        'identifier' => [
                            0 => trans('user/user_service.iati_organizational_identifier_already_exists_in_iati_registry'),
                        ],
                    ];
                }
            }
        }

        return $errors;
    }

    /**
     * Store user created by logged in user.
     *
     * @param $data
     * @return Model
     */
    public function store($data): Model
    {
        $data['organization_id'] = Auth::user()->organization_id;
        $superAdminId = [$this->roleRepo->getSuperAdminId(), $this->roleRepo->getIatiAdminId()];
        $data['role_id'] = in_array(Auth::user()->role_id, $superAdminId) ? $this->roleRepo->getIatiAdminId() : $data['role_id'];
        $user = $this->userRepo->store($data);
        User::sendNewUserEmail($user);

        return $user;
    }

    /**
     * Deletes user with id.
     *
     * @param $id
     *
     * @return bool
     */
    public function delete($id): bool
    {
        $user = $this->userRepo->find($id);
        $adminRole = $this->roleRepo->getOrganizationAdminId();
        $users = $this->userRepo->getUserDownloadData(['organization_id' => [$user->organization_id], 'role' => [$adminRole], 'status' => [1]]);

        if ($user->role_id === $adminRole && count($users) === 1 && $user->status) {
            return false;
        }

        return $this->userRepo->delete($id);
    }

    /**
     * Returns all activities present in database.
     *
     * @param int $page
     * @param array $queryParams
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getPaginatedUsers(int $page, array $queryParams): Collection|LengthAwarePaginator
    {
        return $this->userRepo->getPaginatedUsers($page, $queryParams);
    }

    /**
     * Toggle status of user with id.
     *
     * @param $id
     *
     * @return bool
     */
    public function toggleUserStatus($id): bool
    {
        $user = $this->userRepo->find($id);
        $status = !$user['status'];
        $adminRole = $this->roleRepo->getOrganizationAdminId();

        if (!$status) {
            $users = $this->userRepo->getUserDownloadData(['organization_id' => [$user->organization_id], 'role' => [$adminRole], 'status' => [1]]);

            if (($user->role_id === $adminRole && count($users) === 1)) {
                return false;
            }

            return $this->userRepo->update($id, ['status' => false]);
        }

        return $this->userRepo->update($id, ['status' => true]);
    }

    /**
     * Returns user data to be downloaded.
     *
     * @param $queryParams
     *
     * @return Collection|array
     */
    public function getUserDownloadData($queryParams): Collection | array
    {
        return $this->userRepo->getUserDownloadData($queryParams);
    }

    /**
     * Returns roles based on user type.
     *
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->roleRepo->pluckRoles()->toArray();
        $translatedRoles = trans('user.user_roles');

        if (Auth::user()->role->role === 'iati_admin' || Auth::user()->role->role === 'superadmin') {
            unset($roles[array_flip($roles)['superadmin']]);
        }

        if (Auth::user()->role->role === 'admin') {
            unset($roles[array_flip($roles)['iati_admin']], $roles[array_flip($roles)['superadmin']]);
        }

        foreach ($roles as $key => $role) {
            if (isset($translatedRoles[$role])) {
                $roles[$key] = $translatedRoles[$role];
            }
        }

        return $roles;
    }
}
