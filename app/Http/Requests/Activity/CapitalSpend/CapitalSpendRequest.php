<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\CapitalSpend;

use App\Http\Requests\Activity\ActivityBaseRequest;

/**
 * Class CapitalSpendRequest.
 */
class CapitalSpendRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($capital_spend = null): array
    {
        $totalRules = [
            $this->getErrorsForCapitalSpend($capital_spend),
            $this->getWarningForCapitalSpend($capital_spend),
        ];

        return mergeRules($totalRules);
    }

    /**
     * returns critical rule for capital spend.
     *
     * @param $capital_spend
     *
     * @return array
     */
    public function getErrorsForCapitalSpend($capital_spend = null): array
    {
        if ($capital_spend && is_array($capital_spend)) {
            return [
                'capital_spend' => 'nullable|size:1',
            ];
        }

        return [
            'capital_spend' => ['nullable', 'numeric', 'between:0, 100'],
        ];
    }

    /**
     * returns critical rule for capital spend.
     *
     * @param $capital_spend
     *
     * @return array
     */
    public function getWarningForCapitalSpend($capital_spend = null): array
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
            'numeric' => trans('validation.activity_capital_spend.numeric'),
            'between' => trans('validation.activity_capital_spend.between'),
            'size'    => trans('validation.activity_capital_spend.size'),
        ];
    }
}
