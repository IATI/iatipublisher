<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * @class NoSpacesInBetween
 *
 * Change source: https://github.com/IATI/iatipublisher/issues/1657
 */
class NoSpacesInBetweenInActivityIdentifier implements Rule
{
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
    public function passes($attribute, $value): bool
    {
        $trimmedValue = trim($value);

        return (bool) preg_match('/^[a-zA-Z0-9-]+$/', $trimmedValue) && !preg_match('/[&!\/|?]/', $trimmedValue);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('validation.no_spaces_in_between_in_activity_identifier');
    }
}
