<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\IATI\Models\User\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

/**
 * Class ResetPasswordController.
 */
class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  Request  $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request)
    {
        $token = $request->route()->parameter('token');

        return view('web.reset_password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  Request  $request
     * @param  string  $response
     *
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    protected function sendResetResponse(Request $request, $response): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['success' => true, 'message' => trans($response)], 200);
        }

        return redirect($this->redirectPath())
            ->with('status', trans($response));
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => ['required', 'confirmed', 'string', 'min:8', 'max:255', Rules\Password::defaults()],
            'password_confirmation' => ['required', 'string', 'min:8', 'max:255'],
        ];
    }

    /**
     * Reset the given user's password.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        if (!$user->email_verified_at) {
            User::resendEmail($user);
        }

        event(new PasswordReset($user));
    }

    // Todo: Remove this
//    /**
//     * Get the password reset validation messages.
//     *
//     * @return array
//     */
//    protected function validationErrorMessages(): array
//    {
//        return [
//            'token.required'                 => trans(
//                'validation.required', ['attribute' => trans('public/forgot_password.reset_password_page.token')]
//            ),
//            'email.required'                 => trans('validation.required'),
//            'email.email'                    => trans('validation.email'),
//            'password.required'              => trans('validation.required'),
//            'password.confirmed'             => trans('validation.confirmed'),
//            'password.string'                => trans('validation.string'),
//            'password.min'                   => trans('validation.min'),
//            'password.max'                   => trans('validation.max'),
//            'password_confirmation.required' => trans('validation.required'),
//            'password_confirmation.min'      => trans('validation.min'),
//            'password_confirmation.max'      => trans('validation.max'),
//        ];
//    }
}
