<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\CapitalSpend;

use App\Http\Requests\Activity\ActivityBaseRequest;
use App\Rules\SingleCharacter;

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
                'capital_spend' => ['nullable', new SingleCharacter('capital_spend')],
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
            'numeric' => trans('validation.amount_number'),
            'between' => trans('validation.the_capital_spend_must_be_a_number_between_0_and_100'),
            'size'    => trans('validation.activity_capital_spend.size'),
        ];
    }
}
