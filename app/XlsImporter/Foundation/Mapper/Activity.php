<?php

declare(strict_types=1);

namespace App\XlsImporter\Foundation\Mapper;

use App\Helpers\ImportCacheHelper;
use App\XlsImporter\Foundation\Mapper\Traits\XlsMapperHelper;
use App\XlsImporter\Foundation\XlsValidator\Validators\ActivityValidator;
use Illuminate\Support\Arr;

/**
 * Class Activity.
 */
class Activity
{
    use XlsMapperHelper;

    /**
     * List of all the activity sheets and corresponding elements.
     *
     *
     * @var array
     */
    protected array $activityElements = [
        'Title' => 'title',
        'Other Identifier' => 'other_identifier',
        'Description' => 'description',
        'Activity Date' => 'activity_date',
        'Recipient Country' => 'recipient_country',
        'Recipient Region' => 'recipient_region',
        'Sector' => 'sector',
        'Tag' => 'tag',
        'Policy Marker' => 'policy_marker',
        'Default Aid Type' => 'default_aid_type',
        'Country Budget Items' => 'country_budget_items',
        'Humanitarian Scope' => 'humanitarian_scope',
        'Related Activity' => 'related_activity',
        'Conditions' => 'conditions',
        'Legacy Data' => 'legacy_data',
        'Document Link' => 'document_link',
        'Contact Info' => 'contact_info',
        'Location' => 'location',
        'Planned Disbursement' => 'planned_disbursement',
        'Participating Org' => 'participating_org',
        'Budget' => 'budget',
        'Transaction' => 'transactions',
    ];

    /**
     * List of single valued elements.
     *
     * @var array
     */
    protected array $singleValuedElements = [
        'activity_status',
        'activity_scope',
        'collaboration_type',
        'default_flow_type',
        'default_finance_type',
        'default_tied_status',
        'capital_spend',
    ];

    /**
     * List of default values.
     *
     * @var array
     */
    protected array $defaultValueElements = [
        'default_currency',
        'default_language',
        'hierarchy',
        'humanitarian',
        'budget_not_provided',
    ];

    /**
     * List of element with enclosed narrative.
     *
     * @var array
     */
    protected array $enclosedNarrative = [
        'country_budget_items',
        'location',
        'contact_info',
        'document_link',
        'transactions',
    ];

    /**
     * activities whose identifier is mentioned on setting sheet.
     *
     * @var array
     */
    protected array $activities = [];

    /**
     * total number of activities.
     * @var
     */
    protected int $totalCount = 0;

    /**
     * total number of processed count.
     * @var
     */
    protected int $processedCount = 0;

    /**
     * Array of activities identifier in the xls file.
     *
     * @var array
     */
    protected array $activitiesIdentifier = [];

    /**
     * Array containing all the identifier that currently exists in the system.
     * @var
     */
    protected array $existingIdentifier = [];

    /**
     * Array tracking the identifier present in each sheet.
     *
     * @var array
     */
    protected array $trackIdentifierBySheet = [];

    /**
     * Row count in the sheet.
     *
     * @var int
     */
    protected int $rowCount = 2;

    /**
     * name of sheet currently being processed.
     *
     * @var string
     */
    protected string $sheetName = '';

    /**
     * Array with list of all fields and their position.
     *
     * @var array
     */
    protected array $columnTracker = [];

    /**
     * Array containing all the global errors.
     *
     * @var array
     */
    protected array $globalErrors = [];

    /**
     * Array containing all the processing errors.
     *
     * @var array
     */
    protected array $processingErrors = [];

    /**
     * Array for temporary storage of errors.
     *
     * @var array
     */
    protected array $tempErrors = [];

    /**
     * Count of validation error.
     *
     * @var array
     */
    protected array $errorCount = [
        'critical' => 0,
        'warning' => 0,
        'error' => 0,
    ];

    /**
     * Name of element currently being processed.
     *
     * @var string
     */
    protected string $elementBeingProcessed = '';

    /**
     * Path of status.json file.
     *
     * @var string
     */
    protected string $statusFilePath = '';

    /**
     * Path of valid.json file.
     *
     * @var string
     */
    protected string $validatedDataFilePath = '';

    /**
     * Path of globalError.json file.
     *
     * @var string
     */
    protected string $globalErrorFilePath = '';

    /**
     * Reporting org of the organization.
     *
     * @var string
     */
    protected array $organizationReportingOrg = [];

    private int $orgId;

    /**
     * Fill organization Reporting org.
     *
     * @param $organizationReportingOrg
     *
     * @return static
     */
    public function fillOrganizationReportingOrg($organizationReportingOrg = []): static
    {
        $this->organizationReportingOrg = $organizationReportingOrg ?? [];

        return $this;
    }

    /**
     * @param int $orgId
     *
     * @return static
     */
    public function setOrgId(int $orgId): static
    {
        $this->orgId = $orgId;

        return $this;
    }

    /**
     * Returns activity data for unit test.
     *
     * @return array
     */
    public function getActivityData(): array
    {
        return $this->activities[array_key_first($this->activities)];
    }

    /**
     * Map activity data sheets based on their type.
     *
     * @param $activityData
     *
     * @return static
     */
    public function map($activityData): static
    {
        foreach ($activityData as $sheetName => $content) {
            $this->sheetName = $sheetName;
            $this->rowCount = 2;

            if ($sheetName === 'Settings') {
                $this->defaultValues($content);
            }

            if ($sheetName === 'Element with single field') {
                $this->singleValuedFields($content);
            }

            if (in_array($sheetName, array_keys($this->activityElements))) {
                $this->columnToFieldMapper($this->activityElements[$sheetName], $content);
            }
        }

        return $this;
    }

    /**
     * Validates mapped data and store them to json files.
     *
     * @return void
     */
    public function validateAndStoreData(): void
    {
        $orgId = $this->orgId;
        $activityValidator = app(ActivityValidator::class);
        $this->totalCount = count($this->activities);

        foreach ($this->activities as $activityIdentifier => $activity) {
            $activityIdentifier = (string) $activityIdentifier;
            $errors = $activityValidator
                ->init($activity)
                ->validateData();
            $this->processedCount++;

            $excelColumnAndRowName = isset($this->columnTracker[$activityIdentifier]) ? Arr::collapse($this->columnTracker[$activityIdentifier]) : null;
            $error = $this->appendExcelColumnAndRowDetail($errors, $excelColumnAndRowName);
            $existingId = Arr::get($this->existingIdentifier, $activityIdentifier, false);

            if (!in_array($activityIdentifier, $this->activitiesIdentifier)) {
                $error['critical']['iati_identifier']['settings'] = 'The activity identifier has not been mentioned on setting sheet.';
                $this->errorCount['critical']++;
            }

            if (Arr::get($this->processingErrors, $activityIdentifier, null)) {
                $error['critical'] = Arr::get($this->processingErrors, $activityIdentifier);
                $this->errorCount['critical'] += count(Arr::get($this->processingErrors, $activityIdentifier));
            }

            if (!ImportCacheHelper::activityAlreadyBeingImported($orgId, $activityIdentifier)) {
                $this->storeValidatedData($activity, $error, $existingId, $activityIdentifier);
            }

            ImportCacheHelper::appendActivityIdentifiersToCache($orgId, $activityIdentifier);
        }

        $this->storeGlobalErrors();
        $this->updateStatus();
    }

    /**
     * Map default values for each activity.
     *
     * @param $data
     *
     * @return void
     */
    public function defaultValues($data): void
    {
        $dropDownFields = $this->getDropDownFields();
        $excelColumnName = $this->getExcelColumnNameMapper();
        $elementActivityIdentifier = null;

        foreach ($data as $index => $row) {
            $secondary_reporter = '';

            if ($this->checkRowNotEmpty($row)) {
                $elementActivityIdentifier = trim((string) Arr::get($row, 'activity_identifier'));

                if (is_null($elementActivityIdentifier)) {
                    $this->globalErrors[] = 'Error detected on ' . $this->sheetName . ' sheet, cell A' . $this->rowCount . ': Identifier is missing.';
                    $this->rowCount++;
                    continue;
                }

                $this->isIdentifierDuplicate($row['activity_identifier'], 'settings');

                $secondary_reporter = $row['secondary_reporter'];
                unset($row['secondary_reporter']);

                $this->activitiesIdentifier[] = $elementActivityIdentifier;

                foreach ($this->defaultValueElements as $element) {
                    $fieldValue = Arr::get($row, $element, null);

                    if (array_key_exists($element, $dropDownFields['settings'])) {
                        $elementDropDownFields = $dropDownFields['settings'][$element];
                        $fieldValue = $this->mapDropDownValueToKey($fieldValue, $elementDropDownFields, $element);
                    }

                    $this->activities[$elementActivityIdentifier]['default_field_values'][$element] = is_numeric($fieldValue) ? (string) $fieldValue : $fieldValue;
                    $this->columnTracker[$elementActivityIdentifier]['default_field_values']['default_field_values.' . $element]['sheet'] = $this->sheetName;
                    $this->columnTracker[$elementActivityIdentifier]['default_field_values']['default_field_values.' . $element]['cell'] = Arr::get($excelColumnName, $this->sheetName . '.' . $element) . $this->rowCount;
                }
            } else {
                break;
            }

            $this->rowCount++;
            $this->activities[$elementActivityIdentifier]['reporting_org'] = $this->getReportingOrganization($secondary_reporter);
            $this->activities[$elementActivityIdentifier]['iati_identifier'] = [
                'activity_identifier' => $elementActivityIdentifier,
            ];
        }
    }

    /**
     * Returns reporting organization data from organization.
     *
     * @param $secondary_reporter
     *
     * @return array
     */
    public function getReportingOrganization($secondary_reporter): array
    {
        if (is_string($secondary_reporter) && in_array(strtolower($secondary_reporter), ['false', 'true'])) {
            $secondary_reporter = (int) filter_var($secondary_reporter, FILTER_VALIDATE_BOOLEAN);
        } else {
            $secondary_reporter = $secondary_reporter ? (int) $secondary_reporter : $secondary_reporter;
        }

        if (!empty($this->organizationReportingOrg)) {
            $activityRef = $this->organizationReportingOrg;
            $activityRef[0]['secondary_reporter'] = $secondary_reporter;

            return $activityRef;
        }

        return [
            [
                'ref' => '',
                'type' => '',
                'secondary_reporter' => $secondary_reporter,
                'narrative' => [
                    [
                        'narrative' => '',
                        'language' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * Map single values field of each activity.
     *
     * @param $data
     *
     * @return void
     */
    public function singleValuedFields($data): void
    {
        $dropDownFields = $this->getDropDownFields();
        $elementActivityIdentifier = null;
        $excelColumnName = $this->getExcelColumnNameMapper();

        foreach ($data as $index => $row) {
            if ($this->checkRowNotEmpty($row)) {
                $elementActivityIdentifier = trim((string) Arr::get($row, 'activity_identifier'));

                if (is_null($elementActivityIdentifier)) {
                    $this->globalErrors[] = 'Error detected on ' . $this->sheetName . ' sheet, cell A' . $this->rowCount . ': Identifier is missing.';
                    $this->rowCount++;
                    continue;
                }

                foreach ($this->singleValuedElements as $element) {
                    $fieldValue = $row[$element];

                    if (array_key_exists($element, $dropDownFields)) {
                        $elementDropDownFields = $dropDownFields[$element];
                        $fieldValue = $this->mapDropDownValueToKey($row[$element], $elementDropDownFields, $element);
                    }

                    $this->activities[$elementActivityIdentifier][$element] = is_numeric($fieldValue) ? $fieldValue : $fieldValue;
                    $this->columnTracker[$elementActivityIdentifier]["$element"]["$element"]['sheet'] = $this->sheetName;
                    $this->columnTracker[$elementActivityIdentifier]["$element"]["$element"]['cell'] = Arr::get($excelColumnName, $this->sheetName . '.' . $element) . $this->rowCount;
                }
            } else {
                break;
            }
            $this->rowCount++;
        }
    }

    /**
     * Group columns for activity.
     *
     * @param $element
     * @param $data
     *
     * @return void
     */
    public function columnToFieldMapper($element, $data = []): void
    {
        $elementData = [];
        $columnMapper = $this->getLinearizedActivity();
        $dependency = $this->getDependencies();
        $dropDownFields = $this->getDropDownFields();
        $elementMapper = array_flip($columnMapper[$element]);
        $elementDropDownFields = $dropDownFields[$element];
        $elementActivityIdentifier = null;
        $this->elementBeingProcessed = $element;
        $tempRowCount = 1;

        foreach ($data as $index => $row) {
            $tempRowCount++;

            if ($this->checkRowNotEmpty($row)) {
                if (
                    is_null($elementActivityIdentifier) || (
                        Arr::get($row, 'activity_identifier', null) &&
                        Arr::get($row, 'activity_identifier', null) !== $elementActivityIdentifier
                    )
                ) {
                    if (!empty($elementData)) {
                        $this->setProcessedElementToActivity($element, $elementActivityIdentifier, $elementData, $dependency, $elementDropDownFields);
                        $this->rowCount = $tempRowCount;
                        $elementData = [];
                    }

                    $elementActivityIdentifier = trim((string) Arr::get($row, 'activity_identifier') ?? $elementActivityIdentifier);
                }

                foreach ($elementMapper as $xlsColumnName => $systemName) {
                    $systemMappedRow[$systemName] = Arr::get($row, $xlsColumnName, null);
                }

                $elementData[] = $systemMappedRow;
            } else {
                break;
            }
        }

        if (!empty($elementData) && in_array($elementActivityIdentifier, $this->activitiesIdentifier)) {
            $this->setProcessedElementToActivity($element, $elementActivityIdentifier, $elementData, $dependency, $elementDropDownFields);
            $this->rowCount = $tempRowCount;
            $elementData = [];
        }
    }

    /**
     * Set processed element to its specific position in activities array.
     *
     * @param $element
     * @param $elementActivityIdentifier
     * @param $elementData
     * @param $dependency
     * @param $elementDropDownFields
     *
     * @return void
     */
    public function setProcessedElementToActivity($element, $elementActivityIdentifier, $elementData, $dependency, $elementDropDownFields): void
    {
        if (is_null($elementActivityIdentifier)) {
            $this->globalErrors[] = 'Error detected on ' . $this->sheetName . ' sheet, cell A' . $this->rowCount . ': Identifier is missing.';
        } else {
            $processedData = $this->getElementData($elementData, $dependency[$element], $elementDropDownFields, $elementActivityIdentifier, $element);
            $this->activities[$elementActivityIdentifier][$element] = $processedData;
        }
    }

    /**
     * Map field values for each element of activity.
     *
     * @param $data
     * @param $dependency
     * @param $elementDropDownFields
     * @param $elementActivityIdentifier
     * @param $element
     *
     * @return array
     */
    public function getElementData($data, $dependency, $elementDropDownFields, $elementActivityIdentifier, $element): array
    {
        $this->isIdentifierDuplicate($elementActivityIdentifier, $element);

        $elementBase = Arr::get($dependency, 'elementBase', null);
        $elementBasePeer = Arr::get($dependency, 'elementBasePeer', []);
        $elementAddMore = Arr::get($dependency, 'add_more', true);
        $baseCount = null;
        $fieldDependency = $dependency['fieldDependency'];
        $excelColumnName = $this->getExcelColumnNameMapper();
        $parentBaseCount = [];
        $elementData = [];

        // variables to map code dependency in elements like sector, recipient region and so on
        $codeDependencyCondition = Arr::get($dependency, 'codeDependency.dependencyCondition', []);
        $dependentOn = Arr::get($dependency, 'codeDependency.dependentOn', []);
        $parentDependentOn = Arr::get($dependency, 'codeDependency.parentDependentOn', []);
        $dependentOnValue = [];

        foreach (array_values($dependentOn) as $dependency) {
            foreach (array_values($dependency) as $dependentVocab) {
                $dependentOnValue[$dependentVocab] = null;
            }
        }

        foreach (array_values($fieldDependency) as $dependents) {
            $parentBaseCount[$dependents['parent']] = null;
        }

        foreach ($data as $row) {
            foreach ($row as $fieldName => $fieldValue) {
                [$baseCount, $parentBaseCount, $dependentOnValue] = $this->checkElementAddMore($elementBase, $elementBasePeer, $elementAddMore, $dependentOnValue, $fieldName, $fieldValue, $baseCount, $parentBaseCount, $row, $element);
                [$parentBaseCount, $dependentOnValue] = $this->checkSubElementAddMore($fieldDependency, $parentBaseCount, $parentDependentOn, $dependentOnValue, $fieldName, $fieldValue, $row);
                $cell = Arr::get($excelColumnName, $this->sheetName . '.' . $fieldName);

                if (!empty($dependentOn)) {
                    if (in_array($fieldName, array_keys(Arr::get($dependentOn, 'codes', [])))) {
                        $dependentOnFieldName = $dependentOn['codes'][$fieldName];
                        $dependentOnFieldValue = $dependentOnValue[$dependentOnFieldName];
                        $dependencyCondition = $codeDependencyCondition[$dependentOnFieldName];
                        $defaultField = $dependencyCondition['defaultCodeField'];

                        $fieldName = $dependentOnFieldValue ? Arr::get($dependencyCondition, "dependencyRelation.$dependentOnFieldValue", $defaultField) : $defaultField;
                    }

                    if (in_array($fieldName, array_keys(Arr::get($dependentOn, 'uri', [])))) {
                        $dependentOnFieldName = $dependentOn['uri'][$fieldName];
                        $dependentOnFieldValue = $dependentOnValue[$dependentOnFieldName];
                        $uriDependencyCondition = $codeDependencyCondition[$dependentOnFieldName]['vocabularyUri'];

                        if (!in_array($dependentOnFieldValue, $uriDependencyCondition)) {
                            continue;
                        }
                    }
                }

                if (array_key_exists($fieldName, $elementDropDownFields)) {
                    $fieldValue = $this->mapDropDownValueToKey($fieldValue, $elementDropDownFields[$fieldName], $fieldName);
                }

                // check for non empty.. including zero
                if (in_array($fieldName, array_keys($dependentOnValue), false) && $fieldValue) {
                    $dependentOnValue[$fieldName] = $fieldValue;
                }

                $elementData = $this->setValueToField($elementBase, $elementAddMore, $elementData, $baseCount, $parentBaseCount, $fieldName, $fieldValue, $elementActivityIdentifier, $element, $cell, true);
            }

            $this->rowCount++;
        }

        if (!empty($this->tempErrors)) {
            foreach ($this->tempErrors as $errorIndex => $error) {
                $this->processingErrors[$elementActivityIdentifier][$element][$errorIndex] = $error;
            }
        }

        $this->tempErrors = [];

        return $elementData;
    }

    /**
     * Get position for each field of element.
     *
     * @param $fieldDependency
     * @param $dependencies
     *
     * @return string
     */
    public function getElementPosition($fieldDependency, $dependencies): string
    {
        $position = '';
        $dependency = explode(' ', $dependencies);
        $expected_position = '';
        $transactionNarrative = [
            'description narrative',
            'title narrative',
        ];

        foreach ($dependency as $key) {
            $expected_position = empty($expected_position) ? $key : "$expected_position $key";

            if (in_array($expected_position, array_keys($fieldDependency))) {
                if (in_array($this->elementBeingProcessed, $this->enclosedNarrative)) {
                    if ($this->elementBeingProcessed !== 'transactions' || in_array($expected_position, $transactionNarrative)) {
                        $key = $key === 'narrative' ? '0.narrative' : $key;
                    }
                }

                $positionValue = $fieldDependency[$expected_position] ?? 0;
                $position = empty($position) ? $key . '.' . $positionValue : "$position.$key.$positionValue";
            } else {
                $position = empty($position) ? "$key" : "$position.$key";
            }
        }

        return $position;
    }
}
