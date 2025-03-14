<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * @class AlreadyInActivity
 */
class AlreadyInActivity implements Rule
{
    private string $attribute;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        if (str_contains($this->attribute, 'recipient_region') || str_contains($this->attribute, 'recipient_country')) {
            return trans('validation.activity_transactions.country_region_in_activity');
        }

        return trans('validation.activity_transactions.sector_in_activity');
    }
}
