<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\ReportingOrg;

use App\Http\Requests\Activity\ActivityBaseRequest;
use Illuminate\Support\Arr;

/**
 * Class ReportingOrgRequest.
 */
class ReportingOrgRequest extends ActivityBaseRequest
{
    /**
     * Holds organization level reporting org values
     * Only set during import.
     *
     * @var array|bool
     */
    protected array|bool $reportingOrganisationInOrganisation = false;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @throws \JsonException
     */
    public function rules(): array
    {
        $totalRules = [
            $this->getErrorsForReportingOrganization($this->get('reporting_org')),
            $this->getWarningForReportingOrganization($this->get('reporting_org')),
            $this->getCriticalErrorsForReportingOrganization(($this->get('reporting_org'))),
        ];

        return mergeRules($totalRules);
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
     * Critical rules for reporting organization.
     *
     * @param array $formFields
     *
     * @return array
     * @throws \JsonException
     */
    public function getCriticalErrorsForReportingOrganization(array $formFields): array
    {
        $rules = [];
        $rules['reporting_org'] = 'size:1';

        $reportingOrganizationTypes = implode(',', array_keys(
            $this->getCodeListForRequestFiles('OrganizationType', 'Organization', false)
        ));
        $organizationReportingOrg = $this->reportingOrganisationInOrganisation ?: auth()->user()->organization->reporting_org;
        $reportingOrganization = $formFields[0];
        $reportingOrganizationIndex = 0;
        $reportingOrganizationForm = sprintf('reporting_org.%s', $reportingOrganizationIndex);

        $rules[$reportingOrganizationForm . '.type'] = [
            'nullable',
            sprintf('in:%s', $reportingOrganizationTypes),
            $this->reportingOrgKeyExistsAndDoesntMatch('type', $reportingOrganization, $organizationReportingOrg[0]) ? 'must_match' : '',
        ];
        $rules[$reportingOrganizationForm . '.secondary_reporter'] = [
            'nullable',
            'in:0,1',
        ];
        $rules[$reportingOrganizationForm . '.ref'] = [
            'nullable',
            'not_regex:/(&|!|\/|\||\?)/',
            $this->reportingOrgKeyExistsAndDoesntMatch('ref', $reportingOrganization, $organizationReportingOrg[0]) ? 'must_match' : '',
        ];

        if ($this->reportingOrganisationInOrganisation) {
            $narrativeRules = $this->getWarningForNarrative($reportingOrganization['narrative'], $reportingOrganizationForm);
            list($orgNarratives, $orgLanguages) = $this->getNarrativesAndLanguages($organizationReportingOrg);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }

            foreach ($reportingOrganization['narrative'] as $index => $narrative) {
                if (!compareStringIgnoringWhitespace((string) $narrative['narrative'], (string) $orgNarratives[$index])) {
                    $rules["$reportingOrganizationForm.narrative.$index.narrative"][] = 'must_match';
                }
                if (!compareStringIgnoringWhitespace((string) $narrative['language'], (string) $orgLanguages[$index])) {
                    $rules["$reportingOrganizationForm.narrative.$index.language"][] = 'must_match';
                }
            }
        }

        return $rules;
    }

    /**
     * Error rules for reporting organization form.
     *
     * @param array $formFields
     *
     * @return array
     */
    public function getErrorsForReportingOrganization(array $formFields): array
    {
        return [];
    }

    /**
     * Warning rules for reporting organization form.
     *
     * @param array $formFields
     *
     * @return array
     */
    public function getWarningForReportingOrganization(array $formFields): array
    {
        return [];
    }

    /**
     * Custom message for reporting organization form.
     *
     * @param array $formFields
     *
     * @return array
     */
    public function getMessagesForReportingOrganization(array $formFields): array
    {
        $messages = [];
        $messages['reporting_org.size'] = trans('validation.the_reporting_organisation_should_not_have_multiple_values_or_narratives');
        $reportingOrganization = $formFields[0];
        $reportingOrganizationIndex = 0;
        $reportingOrganizationForm = sprintf('reporting_org.%s', $reportingOrganizationIndex);

        $messages[$reportingOrganizationForm . '.ref.must_match'] = trans('validation.the_reference_of_reporting_org_must_match_reference_of_reporting_org_in_organisation');
        $messages[$reportingOrganizationForm . '.ref.not_regex'] = trans('validation.the_reference_format_for_reporting_organisation_is_invalid');

        $messages[$reportingOrganizationForm . '.type.must_match'] = trans('validation.the_type_of_reporting_org_must_match_type_of_reporting_org_in_organisation');
        $messages[$reportingOrganizationForm . '.type.in:0,1'] = trans('validation.the_type_for_reporting_organisation_is_invalid');

        if ($this->reportingOrganisationInOrganisation) {
            $narrativeMessages = $this->getMessagesForNarrative($reportingOrganization['narrative'], $reportingOrganizationForm);

            foreach ($reportingOrganization['narrative'] as $index => $narrative) {
                $narrativeMessages["$reportingOrganizationForm.narrative.$index.narrative.must_match"] = trans('validation.narrative_must_match_narrative_in_organisations_reporting_org');
                $narrativeMessages["$reportingOrganizationForm.narrative.$index.language.must_match"] = trans('validation.language_must_match_language_in_organisations_reporting_org');
            }

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }

    /**
     * Sets reporting org values in property $reportingOrganisationInOrganisation.
     *
     * @param $reportingOrganisationInOrganisation
     *
     * @return $this
     */
    public function reportingOrganisationInOrganisation($reportingOrganisationInOrganisation): static
    {
        $this->reportingOrganisationInOrganisation = $reportingOrganisationInOrganisation;

        return $this;
    }

    /**
     * Returns organization level reporting orgs narratives and languages in respective arrays.
     *
     * @param $reportingOrg
     *
     * @return array
     */
    private function getNarrativesAndLanguages($reportingOrg): array
    {
        $narrativeFields = Arr::get($reportingOrg[0], 'narrative', '');

        if ($narrativeFields) {
            $narratives = [];
            $languages = [];

            foreach ($narrativeFields as $index => $item) {
                $narratives[] = Arr::get($item, 'narrative', null);
                $languages[] = Arr::get($item, 'language', null);
            }

            return [$narratives, $languages];
        }

        return [false, false];
    }

    /**
     * Check if specified key has the same value.
     *
     * @param string $key
     * @param mixed $reportingOrganization
     * @param array $organizationReportingOrg
     *
     * @return bool
     */
    public function reportingOrgKeyExistsAndDoesntMatch(string $key, mixed $reportingOrganization, array $organizationReportingOrg): bool
    {
        return !compareStringIgnoringWhitespace(
            (string) Arr::get($reportingOrganization, $key, ''),
            (string) Arr::get($organizationReportingOrg, $key, '')
        );
    }
}
