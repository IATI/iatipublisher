<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\DefaultAidType;

use App\Http\Requests\Activity\ActivityBaseRequest;

/**
 * Class DefaultAidTypeRequest.
 */
class DefaultAidTypeRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $totalRules = [
            $this->getErrorsForDefaultAidType($this->get('default_aid_type')),
            $this->getWarningForDefaultAidType(),
        ];

        return mergeRules($totalRules);
    }

    /**
     * Returns rules for related activity.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getErrorsForDefaultAidType(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $index => $formField) {
            $baseForm = sprintf('default_aid_type.%s', $index);
            $rules[sprintf('%s.default_aid_type_vocabulary', $baseForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'AidTypeVocabulary',
                        'Activity',
                        false
                    )
                )
            );
            $rules[sprintf('%s.default_aid_type', $baseForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('AidType', 'Activity', false)
                )
            );
            $rules[sprintf('%s.earmarking_category', $baseForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'EarmarkingCategory',
                        'Activity',
                        false
                    )
                )
            );
            $rules[sprintf('%s.earmarking_modality', $baseForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'EarmarkingModality',
                        'Activity',
                        false
                    )
                )
            );
            $rules[sprintf('%s.cash_and_voucher_modalities', $baseForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'CashandVoucherModalities',
                        'Activity',
                        false
                    )
                )
            );
        }

        return $rules;
    }

    /**
     * Returns rules for related activity.
     *
     * @return array
     */
    public function getWarningForDefaultAidType(): array
    {
        return [];
    }

    /**
     * Get the error message as required.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->getMessagesForDefaultAidType($this->get('default_aid_type'));
    }

    /**
     * Returns messages for related activity validations.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForDefaultAidType(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $index => $formField) {
            $baseForm = sprintf('default_aid_type.%s', $index);
            $messages[sprintf(
                '%s.default_aid_type_vocabulary.in',
                $baseForm
            )]
                = trans('validation.vocabulary_is_invalid');
            $messages[sprintf('%s.default_aid_type.in', $baseForm)] = trans(
                'validation.activity_default_aid_type.invalid'
            );
            $messages[sprintf(
                '%s.earmarking_category.in',
                $baseForm
            )]
                = trans('validation.activity_default_aid_type.invalid_earmarking_category');
            $messages[sprintf(
                '%s.earmarking_modality.in',
                $baseForm
            )]
                = trans('validation.this_field_is_invalid');
            $messages[sprintf(
                '%s.cash_and_voucher_modalities.in',
                $baseForm
            )]
                = trans('validation.this_field_is_invalid');
        }

        return $messages;
    }
}
