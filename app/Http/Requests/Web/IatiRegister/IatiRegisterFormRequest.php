<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\IatiRegister;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class IatiRegisterFormRequest.
 */
class IatiRegisterFormRequest extends FormRequest
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
        $rules = [];
        $step = $this->get('step');

        switch ($step) {
            case '1':
                $rules = [
                    'publisher_id'        => ['required', 'string', 'max:255', 'unique:organizations,publisher_id', 'regex:/^([a-z0-9-_]+){2,}$/'],
                    'publisher_name'      => ['required', 'string', 'max:255', 'unique:organizations,publisher_name'],
                    'identifier'          => ['required', 'string', 'max:255', 'unique:organizations,identifier'],
                    'registration_agency' => ['required', sprintf('in:%s', implode(',', array_keys(getCodeList('OrganizationRegistrationAgency', 'Organization'))))],
                    'country'             => ['nullable', sprintf('in:%s', implode(',', array_keys(getCodeList('Country', 'Activity'))))],
                    'registration_number' => ['required', 'regex:/^([0-9A-Za-z-_.]+)$/'],
                    'publisher_type'      => ['required', sprintf('in:%s', implode(',', array_keys(getCodeList('OrganizationType', 'Organization'))))],
                    'license_id'          => ['required', sprintf('in:%s', implode(',', array_keys(getCodeList('DataLicense', 'Activity'))))],
                    'description'         => ['sometimes'],
                    'image_url'           => ['nullable', 'url'],
                ];
                break;
            case '2':
                $rules = [
                    'contact_email' => ['required', 'string', 'email', 'regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,}$/ix', 'max:255', 'not_in_spam_emails'],
                    'website' => ['nullable', 'url'],
                ];
                break;
            case '3':
                $rules = [
                    'source'           => ['required', sprintf('in:%s', implode(',', array_keys(getCodeList('Source', 'Activity', false))))],
                    'default_language' => ['required', sprintf('in:%s', implode(',', array_keys(getCodeList('Language', 'Activity', false, false))))],
                ];
                break;
            case '4':
                $rules = [
                    'username'              => ['required', 'max:255', 'string', 'unique:users,username', 'regex:/^[a-z]([0-9a-z-_])*$/'],
                    'full_name'             => ['required', 'string', 'max:255'],
                    'email'                 => ['required', 'string', 'email', 'regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,}$/ix', 'max:255', 'unique:users,email', 'not_in_spam_emails'],
                    'password'              => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
                    'password_confirmation' => ['required', 'string', 'min:8', 'max:255'],
                ];
                break;
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
        $messages = [];
        $step = $this->get('step');

        switch ($step) {
            case '1':
                $messages['publisher_id.regex'] = trans('common/common.the_publisher_id_is_invalid');
                $messages['registration_number.regex'] = trans('register/iati_register_form_request.the_registration_number_is_invalid');
                break;
            case '4':
                $messages['username.regex'] = trans('common/common.the_username_is_invalid');
                $messages['email.unique'] = trans('common/common.email_is_already_in_use_in_iati_publisher');
                break;
        }

        return $messages;
    }

    /**
     * Prepares data before validation.
     *
     * @return void
     */
    public function prepareForValidation(): void
    {
        $this->decryptPassword();
    }

    /**
     * Decrypt and update password and password field of form request.
     *
     * @return void
     */
    public function decryptPassword(): void
    {
        $request = $this->all();
        $password = Arr::get($request, 'password', null);
        $password_confirmation = Arr::get($request, 'password_confirmation', null);

        $this->merge([
            'password' => $password,
            'password_confirmation' => $password_confirmation,
        ]);
    }

    /**
     * Overwritten failedValidation method for JSON response.
     *
     * @param Validator $validator
     *
     * @return ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): ValidationException
    {
        $response = new JsonResponse(['success' => false, 'errors' => $validator->errors()]);

        throw new ValidationException($validator, $response);
    }
}
