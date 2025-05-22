<?php

declare(strict_types=1);

namespace App\XlsImporter\Foundation\Mapper\Traits;

use Illuminate\Support\Arr;

/**
 * Trait XlsMapperHelper.
 */
trait XlsMapperHelper
{
    /**
     * Initialize activity mapper.
     *
     * @param $validatedDataFilePath
     * @param $statusFilePath
     * @param $globalErrorFilePath
     * @param $existingIdentifier
     *
     * @return void
     */
    public function initMapper($validatedDataFilePath, $statusFilePath, $globalErrorFilePath, $existingIdentifier): void
    {
        $this->validatedDataFilePath = $validatedDataFilePath;
        $this->statusFilePath = $statusFilePath;
        $this->globalErrorFilePath = $globalErrorFilePath;
        $this->existingIdentifier = $existingIdentifier;
    }

    /**
     * Returns content of linearized-activity json file which contains mapping of system field name to column name present in xls sheet.
     *
     * @return array
     */
    public function getLinearizedActivity(): array
    {
        return json_decode(file_get_contents(app_path() . '/XlsImporter/Templates/linearized-activity.json'), true, 512, 0);
    }

    /**
     * Returns content of field-dependencies json file which maps the dependency of element and subelement on attributes and other fields.
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return json_decode(file_get_contents(app_path() . '/XlsImporter/Templates/field-dependencies.json'), true, 512, 0);
    }

    /**
     * Returns content of dropdown-field which contains list of all the select fields and corresponding file that contains the dropdown options.
     *
     * @return array
     */
    public function getDropDownFields(): array
    {
        return json_decode(file_get_contents(app_path() . '/XlsImporter/Templates/dropdown-fields.json'), true, 512, 0);
    }

    /**
     * Returns content of excel-column-name-mapper which contains column name and column position in Excel file.
     *
     * @return array
     * @throws \JsonException
     */
    public function getExcelColumnNameMapper(): array
    {
        return json_decode(file_get_contents(app_path('/XlsImporter/Templates/excel-column-name-mapper.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Returns content of activity-template which contains basic structure of element and subelement in the system.
     *
     * @return array
     */
    public function getActivityTemplate(): array
    {
        return json_decode(file_get_contents(app_path() . '/XlsImporter/Templates/activity-template.json'), true, 512, 0);
    }

    /**
     * Maps the dropdown values to their corresponding keys.
     *
     * @param $value
     * @param $location
     * @param $fieldName
     *
     * @return mixed
     *
     * @throws \JsonException
     */
    public function mapDropDownValueToKey($value, $location, $fieldName): mixed
    {
        $booleanFieldList = readJsonFile('XlsImporter/Templates/boolean-field-list.json');

        if (is_null($value)) {
            return null;
        }

        if (is_array($location)) {
            if (is_bool($value)) {
                return (int) filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            if (array_key_exists($fieldName, $booleanFieldList) && is_string($value) && in_array(strtolower($value), ['false', 'true', 'no', 'yes', '0', '1'])) {
                return (int) filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            foreach ($location as $locationIndex => $locationValue) {
                if ($locationValue === $value) {
                    return $locationIndex;
                }
            }

            return $value;
        }

        if (is_string($location)) {
            $locationArr = explode('/', $location);

            $dropDownValues = array_flip(getCodeList(explode('.', $locationArr[1])[0], $locationArr[0], filterDeprecated: true));

            return Arr::get($dropDownValues, $value, $value);
        }

        return $value;
    }

    /**
     * Checks if the peer attributes of an attributes are not empty.
     *
     * @return bool
     */
    public function checkIfPeerAttributesAreNotEmpty(array $peerAttributes, array $rowContent): bool
    {
        foreach ($peerAttributes as $attributeName) {
            $attributeContent = Arr::get($rowContent, $attributeName, null);

            if (!is_null($attributeContent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if excel row is not empty.
     *
     * @return bool
     */
    public function checkRowNotEmpty($row): bool
    {
        foreach (array_values($row) as $content) {
            if (is_numeric($content) || $content) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns position of a field in the element.
     *
     * @return string
     */
    public function getElementPosition($fieldDependency, $dependencies): string
    {
        $position = '';
        $dependency = explode(' ', $dependencies);
        $expected_position = '';

        foreach ($dependency as $key) {
            $expected_position = empty($expected_position) ? $key : "$expected_position $key";

            if (in_array($expected_position, array_keys($fieldDependency))) {
                $key = $key === 'narrative' ? '0.narrative' : $key;
                $positionValue = $fieldDependency[$expected_position] ?? 0;
                $position = empty($position) ? $key . '.' . $positionValue : "$position.$key.$positionValue";
            } else {
                $position = empty($position) ? "$key" : "$position.$key";
            }
        }

        return $position;
    }

    /**
     * Stores validated data to valid.json file.
     * Basically the logic here if to fetch valid.json from s3 and append current data, then push to s3 again.
     * With an additional condition that checks if the current activity being processed already exists or not in s3's valid.json.
     * If already exists in valid.json, do not append. (To avoid duplication in the import listing page).
     *
     * @param      $processedXlsData
     * @param      $errors
     * @param      $existingIdentifier
     * @param      $identifier
     * @param null $parentIdentifier
     * @param null $code
     *
     * @return void
     * @throws \JsonException
     */
    public function storeValidatedData($processedXlsData, $errors, $existingIdentifier, $identifier, $parentIdentifier = null, $code = null): void
    {
        $validJsonFile = awsGetFile($this->validatedDataFilePath);
        $contentInValidDotJson = $validJsonFile ? json_decode($validJsonFile, true, 512, JSON_THROW_ON_ERROR) : [];

        $contentInValidDotJson = collect($contentInValidDotJson);

        $appendableData = [
            'data'             => $processedXlsData,
            'errors'           => $errors,
            'existing'         => $existingIdentifier,
            'parentIdentifier' => $parentIdentifier,
            'code'             => $code,
            'identifier'       => $identifier,
            'status'           => 'processed',
        ];

        $contentInValidDotJson->push($appendableData);

        $content = json_encode($contentInValidDotJson, JSON_THROW_ON_ERROR);
        $status = json_encode([
            'success'         => true,
            'message'         => 'Processing',
            'total_count'     => $this->totalCount,
            'processed_count' => $this->processedCount,
        ]);

        awsUploadFile($this->validatedDataFilePath, $content);
        awsUploadFile($this->statusFilePath, $status);
    }

    /**
     * Update status after completion.
     *
     * @return void
     */
    public function updateStatus(): void
    {
        $status = json_encode([
            'success' => true,
            'message' => 'Complete',
            'total_count' => $this->totalCount,
            'processed_count' => $this->processedCount,

        ]);

        awsUploadFile($this->statusFilePath, $status);
    }

    /**
     * Appends the cell position of data on error message.
     *
     * @param $errors
     * @param $excelColumnAndRowName
     *
     * @return array
     */
    public function appendExcelColumnAndRowDetail($errors, $excelColumnAndRowName): array
    {
        foreach ($errors as $errorLevel => $errorData) {
            if (count($errorData)) {
                foreach ($errorData as $element => $error) {
                    foreach ($error as $key => $err) {
                        if (isset($excelColumnAndRowName[$key])) {
                            $errors[$errorLevel][$element][$key] = 'Error detected on ' . $excelColumnAndRowName[$key]['sheet'] . ' sheet, cell ' . $excelColumnAndRowName[$key]['cell'] . ':' . $errors[$errorLevel][$element][$key];
                        }

                        $this->errorCount[$errorLevel]++;
                    }
                }
            } else {
                unset($errors[$errorLevel]);
            }
        }

        return $errors;
    }

    /**
     * Check if element supports add more and increase count.
     *
     * @param $elementBase
     * @param $elementBasePeer
     * @param $elementAddMore
     * @param $dependentOnValue
     * @param $fieldName
     * @param $fieldValue
     * @param $baseCount
     * @param $parentBaseCount
     * @param $row
     * @param $element
     *
     * @return array
     */
    public function checkElementAddMore($elementBase, $elementBasePeer, $elementAddMore, $dependentOnValue, $fieldName, $fieldValue, $baseCount, $parentBaseCount, $row, $element): array
    {
        if ($elementBase && ($fieldName === $elementBase && ($fieldValue || is_numeric($fieldValue) || is_bool($fieldValue) || $this->checkIfPeerAttributesAreNotEmpty($elementBasePeer, $row)))) {
            if (!$elementAddMore && ($baseCount === 0 || $baseCount > 0)) {
                $this->tempErrors["$element > $this->rowCount"] =
                    empty($elementBasePeer) ?
                    sprintf('Error detected on %s sheet, row %s : The %s cannot have multiple %s.', $this->sheetName, $this->rowCount, $element, $elementBase) :
                    sprintf('Error detected on %s sheet, row %s : The %s cannot have multiple %s or %s.', $this->sheetName, $this->rowCount, $element, implode(', ', $elementBasePeer), $elementBase);
            }

            $baseCount = is_null($baseCount) || !$elementAddMore ? 0 : $baseCount + 1;
            $parentBaseCount = array_fill_keys(array_keys($parentBaseCount), null);
            $dependentOnValue = array_fill_keys(array_keys($dependentOnValue), null);
        }

        if (is_null($baseCount)) {
            $baseCount = 0;
        }

        return [
            $baseCount,
            $parentBaseCount,
            $dependentOnValue,
        ];
    }

    /**
     * Check if subelement supports add more and increase the count.
     *
     * @param $fieldDependency
     * @param $parentBaseCount
     * @param $parentDependentOn
     * @param $dependentOnValue
     * @param $fieldName
     * @param $fieldValue
     * @param $row
     *
     * @return array
     */
    public function checkSubElementAddMore($fieldDependency, $parentBaseCount, $parentDependentOn, $dependentOnValue, $fieldName, $fieldValue, $row): array
    {
        if (array_key_exists($fieldName, $fieldDependency)) {
            $parentKey = $fieldDependency[$fieldName]['parent'];
            $peerAttributes = Arr::get($fieldDependency, "$fieldName.peer", []);
            $children = Arr::get($fieldDependency, "$fieldName.children", []);
            $parentAddMore = Arr::get($fieldDependency, "$fieldName.add_more", true);

            if ($fieldValue || is_numeric($fieldValue) || is_bool($fieldValue) || $this->checkIfPeerAttributesAreNotEmpty($peerAttributes, $row)) {
                $parentBaseCount[$parentKey] = is_null($parentBaseCount[$parentKey]) || !$parentAddMore ? 0 : $parentBaseCount[$parentKey] + 1;

                foreach ($children as $child) {
                    $parentBaseCount[$child] = null;
                }

                if (array_key_exists($parentKey, $parentDependentOn)) {
                    foreach ($parentDependentOn[$parentKey] as $dependencyIndex) {
                        $dependentOnValue[$dependencyIndex] = null;
                    }
                }
            }
        }

        return [
            $parentBaseCount,
            $dependentOnValue,
        ];
    }

    /**
     * Append processing error to existing processing errors.
     *
     * @param $parentIdentifier
     * @param $identifier
     *
     * @return void
     */
    public function addProcessingErrors($parentIdentifier, $identifier): void
    {
        if (!empty($this->tempErrors)) {
            foreach ($this->tempErrors as $index => $errors) {
                $this->processingErrors[$parentIdentifier][$identifier][$index] = $errors;
            }
        }
    }

    /**
     * Store global errors in aws.
     *
     * @return void
     */
    public function storeGlobalErrors(): void
    {
        $status = json_encode([
            'success' => true,
            'message' => 'Global errors',
            'total_count' => count($this->globalErrors),
            'errors' => $this->globalErrors,
            'error_count' => $this->errorCount,

        ]);

        awsUploadFile($this->globalErrorFilePath, $status);
    }

    /**
     * Check if identifier is duplicate and validate identifier format.
     *
     *  NOTE by PG-Momik:
     *  Change source: https://github.com/IATI/iatipublisher/issues/1791
     *
     *  Issue Overview:
     *  Previously, when duplicate activity identifiers existed in the "settings" sheet of the XLSX file, the error was shown against the wrong activity.
     *
     *  This change introduces a minimal structural update by assigning the error under a nested format:
     *  - The outer key is "settings"
     *  - The inner key is the activity identifier
     *
     *  This structure ensures:
     *  1. Minimal changes to existing code, as this method is reused in multiple places.
     *  2. No need to refactor the existing error mapping logic.
     *  3. Errors under the "settings" key can be handled later when processing the corresponding sheet (i.e. Title).
     *  4. Nesting errors under the specific activity identifier helps assign error messages accurately to the correct activity.
     *
     *
     * @param $elementIdentifier
     * @param $element
     * @param $validate
     * @param $type
     *
     * @return bool
     */
    public function isIdentifierDuplicate($elementIdentifier, $element, $validate = false, $type = null): bool
    {
        if (in_array($elementIdentifier, Arr::get($this->trackIdentifierBySheet, $this->sheetName, []))) {
            $errorMessage = sprintf('Error detected on %s sheet, cell A%s : The identifier has been duplicated.', $this->sheetName, $this->rowCount);

            if ($element === 'settings') {
                $this->tempErrors['settings'][$elementIdentifier]["$element > $this->rowCount"] = $errorMessage;
            } else {
                $this->tempErrors["$element > $this->rowCount"] = $errorMessage;
            }

            return true;
        }

        $this->trackIdentifierBySheet[$this->sheetName][] = $elementIdentifier;

        if ($validate) {
            $identifier = '';
            $elementPosition = '';

            if (!is_null($type)) {
                $element = $type;
                $identifier = "$element.$elementIdentifier";
                $elementPosition = "$element > $this->rowCount";
            }

            if (empty($type)) {
                $element = $type;
                $identifier = "$elementIdentifier";
                $elementPosition = "$this->rowCount";
            }

            if (is_null(Arr::get($this->identifiers, "$identifier", null))) {
                $this->globalErrors["$elementPosition"] = sprintf('Error detected on %s sheet, cell A%s : The identifier does not have correct format or is not mentioned on mapper sheet.', $this->sheetName, $this->rowCount);

                return true;
            }
        }

        return false;
    }

    /**
     * Set Values to their corresponding fields within element.
     *
     * @param $elementBase
     * @param $elementAddMore
     * @param $elementData
     * @param $baseCount
     * @param $parentBaseCount
     * @param $fieldName
     * @param $fieldValue
     * @param $elementActivityIdentifier
     * @param $element
     * @param $cell
     * @param $isActivity
     *
     * @return array
     */
    public function setValueToField($elementBase, $elementAddMore, $elementData, $baseCount, $parentBaseCount, $fieldName, $fieldValue, $elementActivityIdentifier, $element, $cell, $isActivity = false): array
    {
        $elementPosition = $this->getElementPosition($parentBaseCount, $fieldName);
        $elementPositionBasedOnParent = $elementBase && $elementAddMore ? (empty($elementPosition) ? $baseCount : $baseCount . '.' . $elementPosition) : $elementPosition;

        if (is_null(Arr::get($elementData, $elementPositionBasedOnParent, null)) && !empty($elementPosition)) {
            $fieldValue = is_numeric($fieldValue) ? (string) $fieldValue : $fieldValue;
            Arr::set($elementData, $elementPositionBasedOnParent, $fieldValue);

            if ($isActivity) {
                $this->columnTracker[$elementActivityIdentifier][$element][$element . '.' . $elementPositionBasedOnParent]['sheet'] = $this->sheetName;
                $this->columnTracker[$elementActivityIdentifier][$element][$element . '.' . $elementPositionBasedOnParent]['cell'] = $cell . $this->rowCount;
            } else {
                $this->tempColumnTracker[$elementActivityIdentifier][$element][$element . '.' . $elementPositionBasedOnParent]['sheet'] = $this->sheetName;
                $this->tempColumnTracker[$elementActivityIdentifier][$element][$element . '.' . $elementPositionBasedOnParent]['cell'] = $cell . $this->rowCount;
            }
        }

        return $elementData;
    }
}
