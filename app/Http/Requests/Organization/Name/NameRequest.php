<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization\Name;

use App\Http\Requests\Organization\OrganizationBaseRequest;

/**
 * Class NameRequest.
 */
class NameRequest extends OrganizationBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules['narrative'] = 'unique_lang|unique_default_lang';
        $rules['narrative.0.narrative'] = 'required';

        return $rules;
    }

    /**
     * Get the error message as required.
     *
     * @return array
     */
    public function messages(): array
    {
        $messages['narrative.unique_lang'] = trans('validation.xml_lang_unique');
        $messages['narrative.0.narrative.required'] = trans('validation.name_narrative_required');
        $messages['narrative.unique_default_lang'] = trans('validation.xml_lang_unique');

        return $messages;
    }
}
