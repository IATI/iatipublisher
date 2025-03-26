<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\IATI\Models\User\Role;
use App\IATI\Services\Audit\AuditService;
use App\IATI\Services\User\UserService;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use JsonException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LoginController.
 */
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected string $redirectTo = RouteServiceProvider::HOME;

    /**
     * @var AuditService
     */
    protected AuditService $auditService;

    /**
     * @var UserService
     */
    protected UserService $userService;

    /**
     * @var string
     */
    private string $field;

    /**
     * Create a new LoginController instance.
     *
     * Initialize Services.
     * Check middleware to only allow guest request.
     * Resolve actual field( either email or username).
     * Update request and set value for the resolved field.
     *
     * @param  AuditService  $auditService
     * @param  UserService  $userService
     */
    public function __construct(AuditService $auditService, UserService $userService)
    {
        $this->auditService = $auditService;
        $this->userService = $userService;

        $this->middleware('guest')->except('logout');

        $this->resolveField();
        request()?->merge([$this->field => request()?->input('emailOrUsername')]);
    }

    /**
     * Set $field.
     *
     * @return void
     */
    private function resolveField(): void
    {
        $this->field = filter_var(request()?->input('emailOrUsername'), FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';
    }

    /**
     * Get the login username to be used by the controller.
     *
     * IMPORTANT: Do not rename this method.
     * Multiple functions within the AuthenticatesUsers and other traits expects a username() method.
     *
     * @return string
     */
    public function username(): string
    {
        return $this->field;
    }

    /**
     * Validate the user login request.
     *
     * @param  Request  $request
     *
     * @return void
     */
    protected function validateLogin(Request $request): void
    {
        $emailOrUsernameValidation = 'required|string';

        if ($this->field === 'email') {
            $emailOrUsernameValidation .= '|email|not_in_spam_emails';
        }

        $request->validate(
            [
                'emailOrUsername' => $emailOrUsernameValidation,
                'password'        => 'required|string',
            ],
            [
                'emailOrUsername.required'           => trans(
                    'validation.required',
                    ['attribute' => trans('public/login.sign_in_section.username_label')]
                ),
                'emailOrUsername.string'             => trans(
                    'validation.string',
                    ['attribute' => trans('public/login.sign_in_section.username_label')]
                ),
                'emailOrUsername.email'              => trans(
                    'validation.email',
                    ['attribute' => trans('public/login.sign_in_section.username_label')]
                ),
                'emailOrUsername.not_in_spam_emails' => trans('validation.not_in_spam_emails'),
                'password.required'                  => trans(
                    'validation.required',
                ),
                'password.string'                    => trans(
                    'validation.string',
                ),
            ]
        );
    }

    /**
     * Log the user out of the application.
     *
     * @param  Request  $request
     *
     * @return RedirectResponse|JsonResponse
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $response = $this->loggedOut($request);

        if ($response) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse(['status' => true, 'message' => 'Successfully logged out.'], 204)
            : redirect('/');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  Request  $request
     *
     * @return Response
     *
     * @throws ValidationException
     * @throws JsonException
     */
    public function login(Request $request): Response
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.

        $fieldMappedToValue = $request->only('email', 'username');
        $credentials = [
            ...$fieldMappedToValue,
            'password'   => $request->input('password'),
            'deleted_at' => null,
        ];

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'emailOrUsername' => [trans('auth.failed')],
            ]);
        }

        $credentials['status'] = 1;

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'emailOrUsername' => [trans('validation.your_account_is_inactive')],
            ]);
        }

        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
                $request->session()->put('role_id', auth()->user()->role_id);

                if (in_array(
                    auth()->user()->role_id,
                    [app(Role::class)->getSuperAdminId(), app(Role::class)->getIatiAdminId()],
                    true
                )) {
                    $request->session()->put('superadmin_user_id', auth()->user()->id);
                }
            }

            $this->auditService->auditEvent(Auth::user(), 'signin', '');
            $this->userService->update(Auth::user()->id, ['last_logged_in' => now()]);

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }
}
