<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

/**
 * @class NoLeadingWhiteSpaceInActivityIdentifier
 *
 * Change source: https://github.com/IATI/iatipublisher/issues/1657
 */
class NoLeadingWhiteSpaceInActivityIdentifier implements Rule
{
    /**
     * Create a new rule instance.
     * Make iati identifier text and  organisation identifier available for validation.
     *
     * @return void
     */
    public function __construct(private string $iatiIdentifierText, private string $organisationIdentifier)
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param string $value
     * @return bool
     */
    public function passes($attribute, $value) : bool
    {
        $untrimmedActivityIdentifier = $this->removeOrganisationIdentifierFromIatiIdentifierText();

        return !Str::startsWith($untrimmedActivityIdentifier, ' ');
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() : string
    {
        return trans('validation.no_leading_white_space_in_activity_identifier');
    }

    /**
     * Since the 'activity_identifier' field that we initially receive is trimmed everywhere by Laravel, there is no easy way to get the original untrimmed value.
     * So basically I'm extracting the activity identifier by removing the organisation identifier prefix.
     *
     * @return string
     */
    private function removeOrganisationIdentifierFromIatiIdentifierText(): string
    {
        $prefix = $this->organisationIdentifier . '-';

        return str_starts_with($this->iatiIdentifierText, $prefix)
            ? substr($this->iatiIdentifierText, strlen($prefix))
            : $this->iatiIdentifierText;
    }
}
