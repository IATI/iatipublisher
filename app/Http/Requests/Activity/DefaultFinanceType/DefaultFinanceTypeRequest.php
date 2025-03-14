<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\DefaultFinanceType;

use App\Http\Requests\Activity\ActivityBaseRequest;
use App\Rules\SingleCharacter;

/**
 * Class DefaultFinanceTypeRequest.
 */
class DefaultFinanceTypeRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @param $finance_type
     *
     * @return array
     */
    public function rules($finance_type = null): array
    {
        $totalRules = [$this->getErrorsForDefaultFinanceType($finance_type), $this->getWarningForDefaultFinanceType()];

        return mergeRules($totalRules);
    }

    /**
     * Get the critical validation rules that apply to the request.
     *
     * @param null $finance_type
     *
     * @return array
     * @throws \JsonException
     */
    public function getErrorsForDefaultFinanceType($finance_type = null): array
    {
        if ($finance_type && is_array($finance_type)) {
            return [
                'default_finance_type' => ['nullable', new SingleCharacter('default_finance_type')],
            ];
        }

        return [
            'default_finance_type' => sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('FinanceType', 'Activity', false)
                    )
                )
            ),
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function getWarningForDefaultFinanceType(): array
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
        return [
            'in'   => trans('validation.activity_default_finance_type.in'),
            'size' => trans('validation.activity_default_finance_type.size'),
        ];
    }
}
