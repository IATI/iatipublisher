<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Class UserRequest.
 */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $role = Auth::user()->role->role;

        $rules = [
            'full_name'             => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,}$/ix', 'max:255', 'unique:users,email', 'not_in_spam_emails'],
            'status'                => ['required'],
        ];

        if ($role === 'admin') {
            $rules['role_id'] = 'required';
        }

        return $rules;
    }

    /**
     * Get validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        $messages['email.unique'] = trans('validation.email_unique');

        return $messages;
    }

    /**
     * Overwritten failedValidation method for JSON response.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return ValidationException
     * @throws ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): ValidationException
    {
        $response = new JsonResponse(['success' => false, 'errors' => $validator->errors()]);

        throw new ValidationException($validator, $response);
    }
}
