<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\IATI\Models\User\User;
use App\Mail\CustomResetPasswordEmail;
use Exception;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

/**
 * Class ForgotPasswordController.
 */
class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm(): \Illuminate\View\View
    {
        return view('web.forgot_password');
    }

    /**
     * Display the email successfully sent message.
     *
     * @return \Illuminate\View\View
     */
    public function showEmailSentMessage(): \Illuminate\View\View
    {
        return view('web.password_recovery');
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param Request $request
     * @param string  $response
     *
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    protected function sendResetLinkResponse(
        Request $request,
        $response
    ): \Illuminate\Http\RedirectResponse|JsonResponse {
        return $request->wantsJson()
            ? new JsonResponse(['success' => true, 'message' => trans($response)], 200)
            : back()->with('status', trans($response));
    }

    /**
     * Custom Reset Password Mail to incorporate translated text.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sendCustomPasswordResetNotification(Request $request): JsonResponse
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'exists:users',
                function ($attribute, $value, $fail) {
                    if (Cache::has("password_reset_{$value}")) {
                        $fail(trans('passwords.throttled'));
                    }
                },
            ],
        ]);

        try {
            $email = $request->input('email');

            // Set a cache key that expires in 1 minute
            Cache::put("password_reset_{$email}", true, now()->addMinute());

            $user = User::where('email', $email)->first();

            $token = app('auth.password.broker')->createToken($user);

            $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');
            $url = url(route('password.reset', ['token' => $token, 'email' => $user->email], false));

            $mailDetails = [
                'greeting'                                    => trans('common/common.hello') . ' ' . $user->username,
                'you_are_receiving_this_email_because'        => trans(
                    'passwords.you_are_receiving_this_email_because'
                ),
                'this_password_reset_link_will_expire_in'     => trans(
                    'passwords.this_password_reset_link_will_expire_in',
                    ['count' => $expire]
                ),
                'if_you_did_not_request_a_password_reset'     => trans(
                    'passwords.if_you_did_not_request_a_password_reset'
                ),
                'reset_url'                                   => $url,
                'password_update'                             => true,
                'if_youre_having_trouble_clicking_the_action' => trans(
                    'passwords.if_youre_having_trouble_clicking_the_action',
                    ['actionText' => trans('passwords.reset_password')]
                ),
            ];

            Mail::to($user->email)->send(new CustomResetPasswordEmail($user, $mailDetails));

            return response()->json(
                ['success' => true, 'message' => 'Reset password email mail sent.'],
                200
            );
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(
                ['success' => false, 'message' => 'Failed to send reset password email'],
                500
            );
        }
    }
}
