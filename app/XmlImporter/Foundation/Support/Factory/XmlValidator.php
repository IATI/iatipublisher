<?php

declare(strict_types=1);

namespace App\XmlImporter\Foundation\Support\Factory;

use App\Http\Requests\Activity\Identifier\IdentifierRequest;
use App\Http\Requests\Activity\Title\TitleRequest;
use App\XmlImporter\Foundation\Support\Factory\Traits\ErrorValidationRules;
use App\XmlImporter\Foundation\Support\Factory\Traits\ValidationMessages;
use App\XmlImporter\Foundation\Support\Factory\Traits\WarningValidationRules;

/**
 * Class XmlValidator.
 */
class XmlValidator
{
    use ErrorValidationRules;
    use WarningValidationRules;
    use ValidationMessages;

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
            $this->warningForReportingOrganization($activity),
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
            $this->errorForReportingOrganization($activity),
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
     * @return array
     */
    public function criticalRules(): array
    {
        $activity = $this->activity;
        $rules = [];
        $tempRules = [
            (new TitleRequest())->getErrorsForTitle('title'),
            (new IdentifierRequest())->getErrorsForIdentifier(true, 'identifier'),
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
            $this->messagesForReportingOrganization($activity),
        ];

        foreach ($tempMessages as $tempMessage) {
            foreach ($tempMessage as $idx => $message) {
                $messages[$idx] = $message;
            }
        }

        return $messages;
    }

    public function init($activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * @param bool $isDuplicate
     * @param bool $isIdentifierValid
     *
     * @return array
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
            $errors['critical']['activity_identifier']['activity_identifier.identifier'] = 'The activity has been duplicated.';
        }

        if (!$isIdentifierValid) {
            $errors['critical']['activity_identifier']['activity_identifier.activity_identifier'] = 'The activity is invalid. Please ensure that the activity identifier matches with organization identifier.';
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

    /**
     * @param $organizationReportingOrg
     * @return $this
     */
    public function reportingOrganisationInOrganisation($organizationReportingOrg):static
    {
        $this->organizationReportingOrg = $organizationReportingOrg;

        return $this;
    }
}
