<?php

declare(strict_types=1);

namespace App\XmlImporter\Foundation\Support\Factory;

use App\Http\Requests\Activity\Identifier\IdentifierRequest;
use App\Http\Requests\Activity\ReportingOrg\ReportingOrgRequest;
use App\Http\Requests\Activity\Title\TitleRequest;
use App\XmlImporter\Foundation\Support\Factory\Traits\CriticalErrorValidationRules;
use App\XmlImporter\Foundation\Support\Factory\Traits\ErrorValidationRules;
use App\XmlImporter\Foundation\Support\Factory\Traits\ValidationMessages;
use App\XmlImporter\Foundation\Support\Factory\Traits\WarningValidationRules;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use JsonException;

/**
 * Class XmlValidator.
 */
class XmlValidator
{
    use CriticalErrorValidationRules;
    use ErrorValidationRules;
    use WarningValidationRules;
    use ValidationMessages;

    /**
     * @var bool|array
     */
    public bool|array $organizationReportingOrg = false;

    public ?Authenticatable $authUser = null;

    /**
     * @var
     */
    protected $activity;

    /**
     * @var Validation
     */
    protected $factory;

    /**
     * @param Validation $factory
     */
    public function __construct(Validation $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Returns warnings for xml uploaded activity.
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function rules(): array
    {
        $activity = $this->activity;
        $rules = [];

        $tempRules = [
            $this->warningForActivityStatus($activity),
            $this->warningForActivityScope($activity),
            $this->warningForCollaborationType($activity),
            $this->warningForDefaultFlowType($activity),
            $this->warningForDefaultFinanceType($activity),
            $this->warningForDefaultTiedStatus($activity),
            $this->warningForCapitalSpend($activity),
            $this->warningForTitle($activity),
            $this->warningForIdentifier($activity),
            $this->warningForDescription($activity),
            $this->warningForOtherIdentifier($activity),
            $this->warningForActivityDate($activity),
            $this->warningForDefaultAidType($activity),
            $this->warningForContactInfo($activity),
            $this->warningForParticipatingOrg($activity),
            $this->warningForRecipientCountry($activity),
            $this->warningForRecipientRegion($activity),
            $this->warningForLocation($activity),
            $this->warningForSector($activity),
            $this->warningForTag($activity),
            $this->warningForCountryBudgetItems($activity),
            $this->warningForHumanitarianScope($activity),
            $this->warningForPolicyMarker($activity),
            $this->warningForBudget($activity),
            $this->warningForPlannedDisbursement($activity),
            $this->warningForDocumentLink($activity),
            $this->warningForRelatedActivity($activity),
            $this->warningForLegacyData($activity),
            $this->warningForCondition($activity),
            $this->warningForTransaction($activity),
            $this->warningForResult($activity),
            $this->warningForReportingOrganization($activity, $this->organizationReportingOrg),
        ];

        foreach ($tempRules as $tempRule) {
            foreach ($tempRule as $idx => $rule) {
                $rules[$idx] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Returns error rules for xml uploaded activity.
     *
     * @return array
     */
    public function errorRules(): array
    {
        $activity = $this->activity;
        $rules = [];

        $tempRules = [
            $this->errorForTitle($activity),
            $this->errorForActivityStatus($activity),
            $this->errorForActivityScope($activity),
            $this->errorForCollaborationType($activity),
            $this->errorForDefaultFlowType($activity),
            $this->errorForDefaultFinanceType($activity),
            $this->errorForDefaultTiedStatus($activity),
            $this->errorForCapitalSpend($activity),
            $this->errorForDescription($activity),
            $this->errorForOtherIdentifier($activity),
            $this->errorForActivityDate($activity),
            $this->errorForDefaultAidType($activity),
            $this->errorForContactInfo($activity),
            $this->errorForParticipatingOrg($activity),
            $this->errorForRecipientCountry($activity),
            $this->errorForRecipientRegion($activity),
            $this->errorForLocation($activity),
            $this->errorForSector($activity),
            $this->errorForTag($activity),
            $this->errorForCountryBudgetItem($activity),
            $this->errorForHumanitarianScope($activity),
            $this->errorForPolicyMarker($activity),
            $this->errorForBudget($activity),
            $this->errorForPlannedDisbursement($activity),
            $this->errorForDocumentLink($activity),
            $this->errorForRelatedActivity($activity),
            $this->errorForLegacyData($activity),
            $this->errorForCondition($activity),
            $this->errorForTransaction($activity),
            $this->errorForResult($activity),
            $this->errorForReportingOrganization($activity, $this->organizationReportingOrg),
        ];

        foreach ($tempRules as $tempRule) {
            foreach ($tempRule as $idx => $rule) {
                $rules[$idx] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Returns critical rules for xml uploaded activity.
     *
     * @return array
     *
     * @throws JsonException
     * @throws BindingResolutionException
     */
    public function criticalRules(): array
    {
        $activity = $this->activity;
        $rules = [];
        $reportingOrgRequest = new ReportingOrgRequest();
        $reportingOrgRequest->reportingOrganisationInOrganisation($this->organizationReportingOrg);

        $iatiIdentifierText = Arr::get($activity, 'iati_identifier.iati_identifier_text');

        $tempRules = [
            (new TitleRequest())->getCriticalErrorsForTitle('title'),
            (new IdentifierRequest())->getErrorsForIdentifier($this->authUser, true, 'identifier', 'xml', $iatiIdentifierText),
            $reportingOrgRequest->getCriticalErrorsForReportingOrganization(Arr::get($activity, 'reporting_org')),
            $this->getCriticalErrorsForTransactions($activity),
        ];

        foreach ($tempRules as $index => $tempRule) {
            foreach ($tempRule as $key => $rule) {
                $rules[$key] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Returns the required messages for the failed validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        $activity = $this->activity;
        $messages = [];

        $tempMessages = [
            $this->messagesForActivityStatus($activity),
            $this->messagesForActivityScope($activity),
            $this->messagesForCollaborationType($activity),
            $this->messagesForDefaultFlowType($activity),
            $this->messagesForDefaultFinanceType($activity),
            $this->messagesForDefaultTiedStatus($activity),
            $this->messagesForCapitalSpend($activity),
            $this->messagesForTitle($activity),
            $this->messagesForDescription($activity),
            $this->messagesForOtherIdentifier($activity),
            $this->messagesForActivityDate($activity),
            $this->messagesForDefaultAidType($activity),
            $this->messagesForContactInfo($activity),
            $this->messagesForParticipatingOrg($activity),
            $this->messagesForRecipientCountry($activity),
            $this->messagesForRecipientRegion($activity),
            $this->messagesForLocation($activity),
            $this->messagesForSector($activity),
            $this->messagesForTag($activity),
            $this->messagesForCountryBudgetItems($activity),
            $this->messagesForHumanitarianScope($activity),
            $this->messagesForPolicyMarker($activity),
            $this->messagesForBudget($activity),
            $this->messagesForPlannedDisbursement($activity),
            $this->messagesForDocumentLink($activity),
            $this->messagesForRelatedActivity($activity),
            $this->messagesForLegacyData($activity),
            $this->messagesForCondition($activity),
            $this->messagesForTransaction($activity),
            $this->messagesForResult($activity),
            $this->messagesForReportingOrganization($activity, $this->organizationReportingOrg),
        ];

        foreach ($tempMessages as $tempMessage) {
            foreach ($tempMessage as $idx => $message) {
                $messages[$idx] = $message;
            }
        }

        return $messages;
    }

    public function init($activity): static
    {
        $this->activity = $activity;
        $tempFlattened = flattenArrayWithKeys($this->activity);
        $tempFlattened = changeEmptySpaceValueToNullValue($tempFlattened);
        $tempFlattened = trimStringValueInArray($tempFlattened);

        $this->activity = convertDotKeysToNestedArray($tempFlattened);

        return $this;
    }

    /**
     * @param $authUser
     * @param $organizationReportingOrg
     *
     * @return $this
     */
    public function prepareDependencies($authUser, $organizationReportingOrg): static
    {
        $this->authUser = $authUser;
        $this->organizationReportingOrg = $organizationReportingOrg;

        return $this;
    }

    /**
     * @param bool $isDuplicate
     * @param bool $isIdentifierValid
     *
     * @return array
     *
     * @throws JsonException
     * @throws BindingResolutionException
     */
    public function validateActivity(bool $isDuplicate, bool $isIdentifierValid): array
    {
        $errors = [
            'critical' => $this->factory->initialize($this->activity, $this->criticalRules(), $this->messages())
                ->passes()
                ->withErrors(),
            'error' => $this->factory->initialize($this->activity, $this->errorRules(), $this->messages())
                ->passes()
                ->withErrors(),
            'warning' => $this->factory->initialize($this->activity, $this->rules(), $this->messages())
                ->passes()
                ->withErrors(),
        ];

        if ($isDuplicate) {
            $errors['critical']['activity_identifier']['activity_identifier.identifier'] = trans('validation.the_activity_has_been_duplicated');
        }

        if (!$isIdentifierValid) {
            $errors['critical']['activity_identifier']['activity_identifier.activity_identifier'] = trans('validation.the_activity_is_invalid');
        }

        foreach ($errors as $key => $value) {
            if (empty($value)) {
                unset($errors[$key]);
            }
        }

        return $errors;
    }

    /**
     * Create base rule for multilevel elements.
     *
     * @param $baseRules
     * @param $element
     * @param $data
     * @param $indexRequired
     *
     * @return array
     */
    public function getBaseRules($baseRules, $element, $data, $indexRequired = true): array
    {
        $rules = [];

        if (!empty($data)) {
            foreach ($data as $idx => $value) {
                foreach ($baseRules as $elementName => $baseRule) {
                    $fieldName = $indexRequired ? $element . '.' . $idx . '.' . $elementName : $element . '.' . $elementName;
                    $rules[$fieldName] = $baseRule;
                }
            }
        }

        return $rules;
    }

    /**
     * Create base messages for multilevel elements.
     *
     * @param $baseRules
     * @param $element
     * @param $data
     * @param $indexRequired
     *
     * @return array
     */
    public function getBaseMessages($baseMessages, $element, $data, $indexRequired = true): array
    {
        $messages = [];

        if (is_array($data)) {
            foreach ($data as $idx => $value) {
                foreach ($baseMessages as $elementName => $baseMessage) {
                    $fieldName = $indexRequired ? $element . '.' . $idx . '.' . $elementName : $element . '.' . $elementName;
                    $messages[$fieldName] = $baseMessage;
                }
            }
        } else {
            foreach ($baseMessages as $elementName => $baseMessage) {
                $messages[$element . '.' . $elementName] = $baseMessage;
            }
        }

        return $messages;
    }
}
