<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Constants\Enums;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\IatiRegister\IatiRegisterFormRequest;
use App\IATI\Models\User\Role;
use App\IATI\Services\Organization\OrganizationService;
use App\IATI\Services\User\UserService;
use App\Providers\RouteServiceProvider;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

/**
 * Class IatiRegisterController.
 */
class IatiRegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | IatiRegister Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected string $redirectTo = RouteServiceProvider::HOME;

    /**
     * @var OrganizationService
     */
    protected OrganizationService $organizationService;

    /**
     * @var UserService
     */
    protected UserService $userService;

    /**
     * @var DatabaseManager
     */
    protected DatabaseManager $db;

    /**
     * Controller constructor.
     *
     * @param OrganizationService $organizationService
     * @param UserService         $userService
     * @param DatabaseManager     $db
     */
    public function __construct(OrganizationService $organizationService, UserService $userService, DatabaseManager $db)
    {
        $this->organizationService = $organizationService;
        $this->userService = $userService;
        $this->db = $db;

        $this->middleware('guest');
    }

    /**
     * Verifies and validates publisher form.
     *
     * @param Request $request
     *
     * @return JsonResponse|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verifyPublisher(IatiRegisterFormRequest $request): JsonResponse|\GuzzleHttp\Exception\GuzzleException
    {
        try {
            $postData = $request->only([
                'publisher_name',
                'publisher_id',
                'country',
                'registration_agency',
                'registration_number',
                'identifier',
                'publisher_type',
                'license_id',
                'image_url',
                'description',
            ]);
            $publisherCheck = $this->userService->checkPublisher($postData['publisher_id'], false);
            $identifierCheck = $this->userService->checkIATIIdentifier($postData['identifier']);

            if (!empty($publisherCheck) || !empty($identifierCheck)) {
                return response()->json([
                    'success'         => false,
                    'errors'          => array_merge($this->userService->mapError('publisher', $publisherCheck), $this->userService->mapError('publisher', $identifierCheck)),
                ]);
            }

            $translatedMessage = trans('common/common.publisher_verified_successfully');

            return response()->json(['success' => true, 'message' => $translatedMessage, 'data' => $publisherCheck]);
        } catch (ClientException $e) {
            logger()->error($e->getMessage());

            return response()->json(
                [
                    'success' => false, 'errors'  => [],
                ]
            );
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            $translatedMessage = trans('common/common.error_has_occurred_while_verifying_the_publisher');

            return response()->json(['success' => false, 'errors' => $translatedMessage]);
        }
    }

    /**
     * Verifies and validates contact info form.
     *
     * @param IatiRegisterFormRequest $request
     *
     * @return RedirectResponse|JsonResponse
     */
    public function verifyContactInfo(IatiRegisterFormRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $translatedMessage = trans('register/iati_register_controller.contact_info_successfully_verified');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            $translatedMessage = trans('register/iati_register_controller.error_occurred_while_verifying_contact_info');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        }
    }

    /**
     * Verifies and validates additional info form.
     *
     * @param Request $request
     *
     * @return RedirectResponse|JsonResponse
     * @throws \JsonException
     * @throws \Throwable
     */
    public function verifyAdditionalInfo(IatiRegisterFormRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $translatedMessage = trans('register/iati_register_controller.additional_information_successfully_verified');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            $translatedMessage = trans('register/iati_register_controller.error_occurred_while_verifying_additional_info');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        }
    }

    /**
     * Handle a registration request for the application.
     *
     * @param Request $request
     *
     * @return RedirectResponse|JsonResponse
     * @throws \JsonException
     * @throws \Throwable
     */
    public function register(IatiRegisterFormRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $postData = $request->only(
                [
                    'publisher_name',
                    'publisher_id',
                    'country',
                    'registration_agency',
                    'registration_number',
                    'identifier',
                    'publisher_type',
                    'license_id',
                    'image_url',
                    'description',
                    'contact_email',
                    'website',
                    'address',
                    'source',
                    'record_exclusions',
                    'username',
                    'full_name',
                    'email',
                    'password',
                    'password_confirmation',
                    'default_language',
                ]
            );

            $publisherCheck = $this->userService->checkPublisher($postData['publisher_id'], false);
            $identifierCheck = $this->userService->checkIATIIdentifier($postData['identifier']);
            $userCheck = $this->userService->checkUser($postData, false);

            if (!empty($publisherCheck) || !empty($userCheck) || !empty($identifierCheck)) {
                return response()->json([
                    'success'         => false,
                    'errors'          => array_merge($publisherCheck, $userCheck, $identifierCheck),
                ]);
            }

            $createUser = $this->create($postData);

            if (!$createUser['success']) {
                return response()->json([
                    'success'         => false,
                    'errors'          => $createUser['errors'],
                ]);
            }

            event(new Registered($createUser['user']));
            Session::put('role_id', app(Role::class)->getOrganizationAdminId());

            $translatedMessage = trans('common/common.user_registered_successfully');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (Exception $e) {
            logger()->error($e);

            $translatedMessage = trans('register/iati_register_controller.error_has_occurred_while_trying_to_register_user');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return array
     * @throws \Throwable
     */
    protected function create(array $data): array
    {
        try {
            $this->db->beginTransaction();
            $api_token = [];
            $publisher = [];
            $user = [];

            $iati_user = $this->userService->createUserInRegistry($data);

            if (Arr::get($iati_user, 'success', false)) {
                $api_token = $this->userService->createAPItoken($data['username']);
            }

            if (Arr::get($api_token, 'success', false)) {
                $publisher = $this->userService->createPublisherInRegistry($data, $api_token['token']);
                $data['token'] = $api_token['token'];
            }

            if (Arr::get($iati_user, 'success', false) && Arr::get($api_token, 'success', false) && Arr::get($publisher, 'success', false)) {
                $user = $this->userService->registerNewUser($data);
            } else {
                $iati_user['errors'] = Arr::get($iati_user, 'errors', []);
                $iati_user['errors'] = $this->changeRegistryEmailValidationMessageIfExists($iati_user['errors']);

                return [
                    'success' => false,
                    'errors' => array_merge(
                        $this->userService->mapError('user', $iati_user['errors']),
                        $api_token['errors'] ?? [],
                        $this->userService->mapError('publisher', $publisher['errors'] ?? [])
                    ),
                ];
            }

            $this->db->commit();

            return ['success' => true, 'user' => $user];
        } catch (Exception $e) {
            logger()->error($e);
        }
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm(): \Illuminate\View\View
    {
        try {
            $types = [
                'country'                               => getCodeList('Country', 'Organization', filterDeprecated: true),
                'registrationAgency'                    => getCodeList('OrganizationRegistrationAgency', 'Organization', filterDeprecated: true),
                'publisherType'                         => getCodeList('OrganizationType', 'Organization', filterDeprecated: true),
                'dataLicense'                           => getCodeList('DataLicense', 'Activity', false, filterDeprecated: true),
                'source'                                => getCodeList('Source', 'Activity', false, filterDeprecated: true),
                'uncategorizedRegistrationAgencyPrefix' => Enums::UNCATEGORIZED_ORGANISATION_AGENCY_PREFIX,
                'languages'                             => getCodeList('Language', 'Activity', filterDeprecated: true),
            ];

            return view('web.iati_register', compact('types'));
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            return view('web.welcome');
        }
    }

    private function changeRegistryEmailValidationMessageIfExists(mixed $iatiUserErrors)
    {
        $unflattenedArray = [];
        $iatiUserErrors = flattenArrayWithKeys($iatiUserErrors);

        foreach ($iatiUserErrors as $key => $value) {
            if ($value === '$Email already exists.') {
                $translatedMessage = trans('register/iati_register_controller.email_is_already_in_use_in_iati_registry');
                $value = $translatedMessage;
            }

            Arr::set($unflattenedArray, $key, $value);
        }

        return $unflattenedArray;
    }
}
