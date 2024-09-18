<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SingleCharacter implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(private $element = 'activity_scope')
    {
    }

    /**
     * @param $attribute
     * @param $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return is_string($value) && mb_strlen($value) === 1;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return match ($this->element) {
            'activity_scope'       => trans('validation.activity_scope.size'),
            'activity_status'      => trans('validation.activity_status.size'),
            'capital_spend'        => trans('validation.activity_capital_spend.size'),
            'collaboration_type'   => trans('validation.activity_collaboration_type.size'),
            'default_finance_type' => trans('validation.activity_default_finance_type.size'),
            'default_flow_type'    => trans('validation.activity_default_flow_type.size'),
            'default_tied_status'  => trans('validation.activity_default_tied_status.size'),
        };
    }
}
