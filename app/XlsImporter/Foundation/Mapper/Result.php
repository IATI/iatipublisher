<?php

declare(strict_types=1);

namespace App\XlsImporter\Foundation\Mapper;

use App\XlsImporter\Foundation\Mapper\Traits\XlsMapperHelper;
use App\XlsImporter\Foundation\XlsValidator\Validators\ResultValidator;
use Illuminate\Support\Arr;

/**
 * Class Result.
 */
class Result
{
    use XlsMapperHelper;

    /**
     * @var array
     */
    protected array $results = [];

    /**
     * @var string
     */
    protected string $destinationFilePath = '';

    /**
     * @var array
     */
    protected array $resultIdentifier = [];

    /**
     * @var array
     */
    protected array $identifiers = [];

    /**
     * @var array
     */
    protected array $resultDocumentLink = [];

    /**
     * @var int
     */
    protected int $totalCount = 0;

    /**
     * @var int
     */
    protected int $processedCount = 0;

    /**
     * @var array
     */
    protected array $columnTracker = [];

    /**
     * @var array
     */
    protected array $tempColumnTracker = [];

    /**
     * @var array
     */
    protected array $activityResultMapper = [];

    /**
     * @var int
     */
    protected int $rowCount = 2;

    /**
     * @var string
     */
    protected string $sheetName = '';

    /**
     * @var string
     */
    protected string $statusFilePath = '';

    /**
     * @var string
     */
    protected string $globalErrorFilePath = '';

    /**
     * @var string
     */
    protected string $validatedDataFilePath = '';

    /**
     * @var array
     */
    protected array $existingIdentifier = [];

    /**
     * @var array
     */
    protected array $trackIdentifierBySheet = [];

    /**
     * @var array
     */
    protected array $globalErrors = [];

    /**
     * @var array
     */
    protected array $processingErrors = [];

    /**
     * @var array
     */
    protected array $tempErrors = [];

    /**
     * @var array
     */
    protected array $errorCount = [
        'critical' => 0,
        'warning' => 0,
        'error' => 0,
    ];

    /**
     * Division of indicator data based on sheet names.
     *
     * @var array
     */
    protected array $resultDivision = [
        'Result' => 'result',
        'Result Document Link' => 'result document_link',
    ];

    /**
     * Identifier column for each indicator data sheet.
     *
     * @var array
     */
    protected array $elementIdentifiers = [
        'result' => 'result_identifier',
        'result document_link' => 'result_identifier',
    ];

    /**
     * Returns result data for unit test.
     *
     * @return array
     */
    public function getResultData(): array
    {
        $resultTestData = [];
        $errors = [];

        foreach ($this->results as $activityIdentifier => $results) {
            foreach ($results as $resultIdentifier => $resultData) {
                $resultTestData[$resultIdentifier] = $resultData['results'];
            }
        }

        return $resultTestData;
    }

    /**
     * Map result data based on sheet.
     *
     * @param $resultData
     *
     * @return static
     */
    public function map($resultData): static
    {
        foreach ($resultData as $sheetName => $content) {
            $this->sheetName = $sheetName;
            $this->rowCount = 2;

            if ($sheetName === 'Result_Mapper') {
                $this->mapResultMapperSheets($content, $sheetName);
            }

            if (array_key_exists($sheetName, $this->resultDivision)) {
                $this->columnToFieldMapper($this->resultDivision[$sheetName], $content);
            }
        }

        return $this;
    }

    /**
     * Map mapper sheets and store them in $identifiers.
     *
     * @return void
     */
    public function mapResultMapperSheets($data, $sheetName): void
    {
        $parentIdentifierValue = '';

        foreach ($data as $index => $row) {
            if ($this->checkRowNotEmpty($row)) {
                if ($index === 0 && is_null($row['activity_identifier'])) {
                    $this->globalErrors[] = 'Error detected on ' . $this->sheetName . ' sheet, cell A' . $this->rowCount . ': Identifier is missing.';
                }

                if ((empty($parentIdentifierValue) || $parentIdentifierValue !== $row['activity_identifier']) && !empty($row['activity_identifier'])) {
                    $parentIdentifierValue = $row['activity_identifier'];

                    $this->isIdentifierDuplicate($parentIdentifierValue, 'Result_Mapper');
                }

                $this->resultIdentifier[$sheetName][$parentIdentifierValue][] = sprintf('%s_%s', $parentIdentifierValue, $row['result_number']);
                $this->identifiers[sprintf('%s_%s', $parentIdentifierValue, $row['result_number'])] = $parentIdentifierValue;
            } else {
                break;
            }
        }
    }

    /**
     * Validate and store result data to json files.
     *
     * @return void
     */
    public function validateAndStoreData(): void
    {
        $errors = [];
        $resultValidator = app(ResultValidator::class);

        foreach ($this->results as $activityIdentifier => $results) {
            foreach ($results as $resultIdentifier => $resultData) {
                $errors = $resultValidator
                    ->init(
                        [
                            'result' => $resultData['results'],
                            'resultId' => Arr::get($this->existingIdentifier, sprintf('result.%s', $resultIdentifier), null),
                        ]
                    )
                    ->validateData();

                $existingId = Arr::get($this->existingIdentifier, sprintf('result.%s', $resultIdentifier), false);
                $error = $this->appendExcelColumnAndRowDetail($errors, $this->columnTracker[$activityIdentifier][$resultIdentifier]['results']);

                if (!in_array($activityIdentifier, array_keys($this->existingIdentifier['parent']))) {
                    $error['critical']['activity_identifier']['activity_identifier'] = "The activity identifier doesn't exist in the system";
                    $this->errorCount['critical']++;
                }

                if (!empty(Arr::get($this->processingErrors, "$activityIdentifier.$resultIdentifier", []))) {
                    $error['critical']['result_identifier'] = Arr::get($this->processingErrors, "$activityIdentifier.$resultIdentifier");
                    $this->errorCount['critical'] += count(Arr::get($this->processingErrors, "$activityIdentifier.$resultIdentifier"));
                }

                $this->processedCount++;
                $this->storeValidatedData($resultData['results'], $error, $existingId, $resultIdentifier, $activityIdentifier, str_replace($activityIdentifier . '_', '', $resultIdentifier));
            }
        }

        $this->storeGlobalErrors();
        $this->updateStatus();
    }

    /**
     * Group columns by identifier.
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
        $elementIdentifier = $this->elementIdentifiers[$element];

        foreach ($data as $row) {
            if ($this->checkRowNotEmpty($row)) {
                if (
                    is_null($elementActivityIdentifier) || (
                        Arr::get($row, $elementIdentifier, null) &&
                        Arr::get($row, $elementIdentifier, null) !== $elementActivityIdentifier
                    )
                ) {
                    if (!empty($elementData)) {
                        $processedData = $this->getElementData($elementData, $dependency[$element], $elementDropDownFields, $element, $elementActivityIdentifier);
                        $this->appendResultData($element, $elementActivityIdentifier, $processedData);
                        $elementData = [];
                    }

                    $elementActivityIdentifier = Arr::get($row, $elementIdentifier, null) ?? $elementActivityIdentifier;
                }

                $systemMappedRow = [];

                foreach ($elementMapper as $xlsColumnName => $systemName) {
                    $systemMappedRow[$systemName] = $row[$xlsColumnName];
                }

                $elementData[] = $systemMappedRow;
            } else {
                break;
            }
        }

        if (!empty($elementData)) {
            $processedData = $this->getElementData($elementData, $dependency[$element], $elementDropDownFields, $element, $elementActivityIdentifier);
            $this->appendResultData($element, $elementActivityIdentifier, $processedData);
            $elementData = [];
        }
    }

    /**
     * Map fields within element to form elementdata.
     *
     * @param $data
     * @param $dependency
     * @param $elementDropDownFields
     * @param $element
     * @param $elementActivityIdentifier
     *
     * @return array
     */
    public function getElementData($data, $dependency, $elementDropDownFields, $element, $elementActivityIdentifier): array
    {
        if (is_null($elementActivityIdentifier)) {
            $this->globalErrors[] = sprintf('Error detected on %s sheet, cell A %s: Identifier is missing.', $this->sheetName, $this->rowCount);

            return [];
        }

        if ($this->isIdentifierDuplicate($elementActivityIdentifier, $element, true, '')) {
            return [];
        }

        $elementBase = Arr::get($dependency, 'elementBase', null);
        $elementBasePeer = Arr::get($dependency, 'elementBasePeer', []);
        $baseCount = null;
        $elementAddMore = Arr::get($dependency, 'add_more', true);
        $fieldDependency = $dependency['fieldDependency'];
        $parentBaseCount = [];
        $activityTemplate = $this->getActivityTemplate();
        $elementData = Arr::get($activityTemplate, $element, []);
        $excelColumnName = $this->getExcelColumnNameMapper();

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
                list($baseCount, $parentBaseCount, $dependentOnValue) = $this->checkElementAddMore($elementBase, $elementBasePeer, $elementAddMore, $dependentOnValue, $fieldName, $fieldValue, $baseCount, $parentBaseCount, $row, $element);
                list($parentBaseCount, $dependentOnValue) = $this->checkSubElementAddMore($fieldDependency, $parentBaseCount, $parentDependentOn, $dependentOnValue, $fieldName, $fieldValue, $row);

                if (!empty($dependentOn)) {
                    if (in_array($fieldName, array_keys(Arr::get($dependentOn, 'uri', [])))) {
                        $dependentOnFieldName = $dependentOn['uri'][$fieldName];
                        $dependentOnFieldValue = $dependentOnValue[$dependentOnFieldName];
                        $uriDependencyCondition = $codeDependencyCondition[$dependentOnFieldName]['vocabularyUri'];

                        if (!in_array($dependentOnFieldValue, $uriDependencyCondition)) {
                            $elementPosition = $this->getElementPosition($parentBaseCount, $fieldName);
                            $elementPositionBasedOnParent = $elementBase && $elementAddMore ? (empty($elementPosition) ? $baseCount : $baseCount . '.' . $elementPosition) : $elementPosition;
                            Arr::forget($elementData, $elementPositionBasedOnParent);
                            continue;
                        }
                    }
                }

                if (in_array($fieldName, array_keys($elementDropDownFields))) {
                    $fieldValue = $this->mapDropDownValueToKey($fieldValue, $elementDropDownFields[$fieldName], $fieldName);
                }

                if (in_array($fieldName, array_keys($dependentOnValue), false) && $fieldValue) {
                    $dependentOnValue[$fieldName] = $fieldValue;
                }

                $elementData = $this->setValueToField($elementBase, $elementAddMore, $elementData, $baseCount, $parentBaseCount, $fieldName, $fieldValue, $elementActivityIdentifier, $element, Arr::get($excelColumnName, $this->sheetName . '.' . $fieldName));
            }
            $this->rowCount++;
        }

        return $elementData;
    }

    /**
     * Append the result data to their specific position.
     *
     * @param $element
     * @param $identifier
     * @param $data
     *
     * @return void
     */
    protected function appendResultData($element, $identifier, $data):void
    {
        $periodElementFunctions = [
            'result' => 'appendResult',
            'result document_link' => 'appendResultDocumentLink',
        ];

        call_user_func([$this, $periodElementFunctions[$element]], $identifier, $data);

        $this->tempColumnTracker = [];
        $this->tempErrors = [];
    }

    /**
     * Append result data.
     *
     * @param $identifier
     * @param $data
     *
     * @return void
     */
    protected function appendResult($identifier, $data): void
    {
        $activityIdentifier = Arr::get($this->identifiers, "$identifier", null);

        if (!empty($data)) {
            $this->results[$activityIdentifier][$identifier]['results'] = $data;
            $this->totalCount++;
            $this->columnTracker[$activityIdentifier][$identifier]['results'] = $this->tempColumnTracker;
        }

        $this->addProcessingErrors($activityIdentifier, $identifier);
    }

    /**
     * Append document link to its parent result.
     *
     * @param $identifier
     * @param $data
     *
     * @return void
     */
    protected function appendResultDocumentLink($identifier, $data): void
    {
        $activityIdentifier = Arr::get($this->identifiers, "$identifier", null);

        if (!empty($data)) {
            if (!isset($this->results[$activityIdentifier][$identifier]['results'])) {
                $activityTemplate = $this->getActivityTemplate();
                $this->results[$activityIdentifier][$identifier]['results'] = $activityTemplate['result'];
                $this->totalCount++;
            }

            $this->results[$activityIdentifier][$identifier]['results']['document_link'] = $data;

            $this->updateColumnTracker($activityIdentifier, $identifier, 'document_link');
        }

        $this->addProcessingErrors($activityIdentifier, $identifier);
    }

    /**
     * Append column tracker to parent element.
     *
     * @param $activityIdentifier
     * @param $identifier
     * @param $keyPrefix
     *
     * @return void
     */
    protected function updateColumnTracker($activityIdentifier, $identifier, $keyPrefix):void
    {
        foreach ($this->tempColumnTracker as $columnPosition => $columnIndex) {
            $this->columnTracker[$activityIdentifier][$identifier]['results']["$keyPrefix.$columnPosition"] = $columnIndex;
        }
    }
}
