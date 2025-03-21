<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization\ReportingOrg;

use App\Http\Requests\Organization\OrganizationBaseRequest;

/**
 * Class ReportingOrgRequest.
 */
class ReportingOrgRequest extends OrganizationBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return $this->getWarningForReportingOrganization($this->get('reporting_org'));
    }

    /**
     * Get the Validation Error message.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->getMessagesForReportingOrganization($this->get('reporting_org'));
    }

    /**
     * Rules for reporting organization form.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getWarningForReportingOrganization(array $formFields): array
    {
        $organization = auth()->user()->organization;
        $organisationIdentifier = $organization->identifier;

        $rules = [];

        foreach ($formFields as $reportingOrganizationIndex => $reportingOrganization) {
            $reportingOrganizationForm = sprintf('reporting_org.%s', $reportingOrganizationIndex);

            $rules[$reportingOrganizationForm . '.ref'] = [
                'nullable',
                'not_regex:/(&|!|\/|\||\?)/',
                !compareStringIgnoringWhitespace(
                    (string) $reportingOrganization['ref'],
                    (string) $organisationIdentifier
                ) ? 'must_match' : '',
            ];
            $rules[$reportingOrganizationForm . '.type'] = [
                'nullable',
                sprintf(
                    'in:%s',
                    implode(
                        ',',
                        array_keys(
                            $this->getCodeListForRequestFiles('OrganizationType', 'Organization')
                        )
                    )
                ),
            ];
            $rules[$reportingOrganizationForm . '.secondary_reporter'] = [
                'nullable',
                'in:0,1',
            ];
            $narrativeRules = $this->getWarningForNarrative(
                $reportingOrganization['narrative'],
                $reportingOrganizationForm
            );

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * Custom message for reporting organization form.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForReportingOrganization(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $reportingOrganizationIndex => $reportingOrganization) {
            $reportingOrganizationForm = sprintf('reporting_org.%s', $reportingOrganizationIndex);
            $messages[$reportingOrganizationForm . '.ref.not_regex'] = trans('validation.invalid_ref');
            $messages[$reportingOrganizationForm . '.ref.must_match']
                = trans('validation.reporting_org_ref_must_match');
            $narrativeMessages = $this->getMessagesForNarrative(
                $reportingOrganization['narrative'],
                $reportingOrganizationForm
            );

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }
}
