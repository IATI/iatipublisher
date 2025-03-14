<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization\OrganizationIdentifier;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class OrganizationIdentifierRequest.
 */
class OrganizationIdentifierRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'organization_registration_agency' => 'required',
            'registration_number'              => ['required', 'not_regex:/(&|!|\/|\||\?)/'],
        ];
    }

    /**
     * Get the Validation Error message.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'registration_number.not_regex' => trans('validation.registration_number_regex'),
        ];
    }
}
