<?php

declare(strict_types=1);

namespace App\CsvImporter\Entities\Activity\Components;

use App\CsvImporter\Entities\Row;
use App\Helpers\ImportCacheHelper;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class ActivityRow.
 */
class ActivityRow extends Row
{
    /**
     * Base Namespace for the Activity Element classes.
     */
    public const BASE_NAMESPACE = 'App\CsvImporter\Entities\Activity\Components\Elements';

    /**
     * Directory where the validated Csv data is written before import.
     */
    private $csv_data_storage_path;

    /**
     * File in which the valid Csv data is written before import.
     */
    public const VALID_CSV_FILE = 'valid.json';

    /**
     * Activity Elements for an Activity Row.
     *
     * @var array
     */
    protected array $activityElements = [
        'identifier',
        'title',
        'defaultFieldValues',
        'description',
        'activityDate',
        'participatingOrganization',
        'recipientCountry',
        'recipientRegion',
        'sector',
        'activityScope',
        'activityStatus',
    ];

    /**
     * Transaction Elements for an Activity Row.
     *
     * @var string
     */
    protected string $transactionElement = 'transaction';

    /**
     * @var array
     */
    protected array $transactionRows = [];

    /**
     * @var array
     */
    protected array $transactionCSVHeaders = [
        'transaction_internal_reference',
        'transaction_type',
        'transaction_date',
        'transaction_value',
        'transaction_value_date',
        'transaction_description',
        'transaction_provider_organisation_identifier',
        'transaction_provider_organisation_type',
        'transaction_provider_organisation_activity_identifier',
        'transaction_provider_organisation_description',
        'transaction_receiver_organisation_identifier',
        'transaction_receiver_organisation_type',
        'transaction_receiver_organisation_activity_identifier',
        'transaction_receiver_organisation_description',
        'transaction_sector_vocabulary',
        'transaction_sector_vocabulary_uri',
        'transaction_sector_code',
        'transaction_sector_narrative',
        'transaction_recipient_country_code',
        'transaction_recipient_region_code',
        'transaction_recipient_region_vocabulary_uri',
    ];

    /**
     * @var array
     */
    protected array $otherElements = [
        'budget',
        'policyMarker',
        'relatedActivity',
        'contactInfo',
        'otherIdentifier',
        'tag',
        'reportingOrganization',
        'collaborationType',
        'defaultFlowType',
        'defaultFinanceType',
        'defaultTiedStatus',
        'defaultAidType',
        'countryBudgetItem',
        'humanitarianScope',
        'capitalSpend',
        'condition',
        'legacyData',
        'documentLink',
        'location',
        'plannedDisbursement',
    ];

    /**
     * All Elements for an Activity Row.
     *
     * @var array
     */
    protected array $elements;

    /**
     * Flag for existence of the identifier.
     */
    protected bool $existence = false;

    /**
     * @var
     */
    protected $identifier;

    /**
     * @var array
     */
    protected array $transaction = [];

    /**
     * @var array
     */
    protected array $validElements = [];

    /**
     * Current Organization's id.
     *
     * @var int
     */
    public int $organizationId;

    /**
     * Current User's id.
     *
     * @var int
     */
    protected int $userId;

    /**
     * @var array
     */
    protected array $activityIdentifiers;

    /**
     * @var array
     */
    protected array $organizationReportingOrg;

    /**
     * ActivityRow constructor.
     *
     * @param $fields
     * @param $organizationId
     * @param $userId
     * @param $activityIdentifiers
     * @param $organizationReportingOrg
     */
    public function __construct($fields, $organizationId, $userId, $activityIdentifiers, $organizationReportingOrg)
    {
        $this->fields = $fields;
        $this->stringifyFields();
        $this->organizationId = $organizationId;
        $this->activityIdentifiers = $activityIdentifiers;
        $this->organizationReportingOrg = $organizationReportingOrg;
        $this->userId = $userId;
        $this->init();
        $this->csv_data_storage_path = env('CSV_DATA_STORAGE_PATH ', 'CsvImporter/tmp');
    }

    /**
     * Converts all the integer to string.
     */
    public function stringifyFields()
    {
        $int_fields = [
            'activity_scope',
            'activity_status',
            'default_flow_type',
            'default_finance_type',
            'default_tied_status',
            'collaboration_type',
            'default_flow_type',
            'default_finance_type',
            'capital_spend',
        ];

        foreach ($this->fields as $key => $dataDatum) {
            if (is_array($dataDatum) && !in_array($key, $int_fields)) {
                foreach ($dataDatum as $datumKey => $datum) {
                    if (is_int($datum)) {
                        $this->fields[$key][$datumKey] = strval($datum);
                    }
                }
            }
        }
    }

    /**
     * Initialize the Row object.
     */
    public function init(): void
    {
        $this->otherFieldsWithTransaction();
    }

    /**
     * Initiate the ActivityRow elements with Activity, Transaction and Other Fields.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function otherFieldsWithTransaction(): void
    {
        $this->makeActivityElements()->makeTransactionElements()->makeOtherFieldsElements();
    }

    /**
     * Process the Row.
     *
     * @return $this
     */
    public function process(): static
    {
        return $this;
    }

    /**
     * Validate the Row.
     *
     * @return $this
     */
    public function validate(): static
    {
        $this->validateElements()->validateSelf();

        return $this;
    }

    /**
     * Store the Row in a temporary JSON File for further usage.
     *
     * @return void
     * @throws \JsonException
     */
    public function keep(): void
    {
        $activityIdentifier = (string) Arr::get($this->data(), 'identifier.activity_identifier');

        if (ImportCacheHelper::isThisActivityBeingImported($this->organizationId, $activityIdentifier)) {
            return;
        }

        ImportCacheHelper::appendActivityIdentifiersToCache($this->organizationId, $activityIdentifier);

        $path = sprintf('%s/%s/%s/%s', $this->csv_data_storage_path, $this->organizationId, $this->userId, self::VALID_CSV_FILE);

        $this->writeCsvDataAsJson($path);
    }

    /**
     * Instantiate the Activity Element classes.
     *
     * @return $this
     * @throws BindingResolutionException
     */
    protected function makeActivityElements(): static
    {
        foreach ($this->activityElements() as $element) {
            $namespace = $this->getNamespace($element, self::BASE_NAMESPACE);

            if (class_exists($namespace)) {
                if ($element === 'sector') {
                    $this->$element = $this->make($namespace, $this->getFields(), $this->organizationId);
                } else {
                    $this->$element = $this->make($namespace, $this->getFields());
                }

                if ($element === 'identifier') {
                    $this->$element->setOrganization($this->organizationId);
                    $this->$element->setActivityIdentifier($this->activityIdentifiers);
                }

                $this->elements[] = $element;
            }
        }

        return $this;
    }

    /**
     * Instantiate the Transaction Element classes.
     *
     * @return $this
     * @throws BindingResolutionException
     */
    protected function makeTransactionElements(): static
    {
        $this->mapTransactionData();

        foreach ($this->transactionRows as $index => $transactionRow) {
            $namespace = $this->getNamespace($this->transactionElement(), self::BASE_NAMESPACE);

            if (class_exists($namespace)) {
                $this->transaction[] = app()->makeWith($namespace, ['transactionRow' => $transactionRow, 'activityRow' => $this]);
            }
        }

        $this->elements[] = $this->transactionElement();

        return $this;
    }

    /**
     * Instantiate the Other Elements classes.
     *
     * @return $this
     * @throws BindingResolutionException
     */
    protected function makeOtherFieldsElements(): static
    {
        foreach ($this->otherElements() as $element) {
            $namespace = $this->getNamespace($element, self::BASE_NAMESPACE);

            if (class_exists($namespace)) {
                if ($element === 'reportingOrganization') {
                    $this->$element = app()->makeWith($namespace, [
                        'fields'       => $this->getFields(),
                        'reportingOrg' => $this->organizationReportingOrg,
                    ]);
                } else {
                    $this->$element = $this->make($namespace, $this->getFields());
                }

                $this->elements[] = $element;
            }
        }

        return $this;
    }

    /**
     * Map Transaction data into singular Transaction block for each Activity.
     *
     * @return void
     */
    protected function mapTransactionData(): void
    {
        foreach ($this->getFields() as $key => $values) {
            if (array_key_exists($key, array_flip($this->transactionCSVHeaders))) {
                foreach ($values as $index => $value) {
                    $this->transactionRows[$index][$key] = $value ? (string) $value : $value;
                }
            }
        }

        $this->removeEmptyTransactionData();
    }

    /**
     * Remove empty Transaction rows.
     *
     * @return void
     */
    protected function removeEmptyTransactionData(): void
    {
        foreach ($this->transactionRows as $index => $transactionRow) {
            $totalNull = 0;
            foreach ($transactionRow as $value) {
                if (!$value) {
                    $totalNull++;
                }
            }

            if ($totalNull === count($this->transactionCSVHeaders)) {
                unset($this->transactionRows[$index]);
            }
        }
    }

    /**
     * Get the Activity elements.
     *
     * @return array
     */
    protected function activityElements(): array
    {
        return $this->activityElements;
    }

    /**
     * Get the Transaction Elements.
     *
     * @return array|string
     */
    protected function transactionElement(): array|string
    {
        return $this->transactionElement;
    }

    /**
     * Get the other Elements.
     *
     * @return array
     */
    protected function otherElements(): array
    {
        return $this->otherElements;
    }

    /**
     * Validate all elements contained in the ActivityRow.
     *
     * @return $this
     */
    protected function validateElements(): static
    {
        foreach ($this->elements() as $element) {
            if ($element === 'transaction') {
                foreach ($this->$element as $index => $transaction) {
                    $transaction->validate(Arr::get($this->data(), 'transaction', []))->withErrors($index);
                    $this->recordErrors($element, $transaction, true);
                    $this->validElements[] = $transaction->isElementValid();
                }
            } else {
                $this->$element->validate()->withErrors();
                $this->recordErrors($element, $this->$element);
                $this->validElements[] = $this->$element->isElementValid();
            }
        }

        return $this;
    }

    /**
     * Set the validity for the whole ActivityRow.
     *
     * @return $this
     */
    protected function validateSelf(): static
    {
        if (in_array(false, $this->validElements, true)) {
            $this->isValid = false;
        } else {
            $this->isValid = true;
        }

        return $this;
    }

    /**
     * Make the storage directory, if it does not exist, to store the validated Csv data before import.
     *
     * @return $this
     */
    protected function makeDirectoryIfNonExistent(): static
    {
        $path = sprintf('%s/%s/', storage_path($this->csv_data_storage_path), $this->organizationId);

        if (!file_exists($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }

        return $this;
    }

    /**
     * Get the file path for the validated Csv data to be stored before import.
     *
     * @return string
     */
    protected function getCsvFilepath(): string
    {
        return storage_path(sprintf('%s/%s/%s/%s', $this->csv_data_storage_path, $this->organizationId, $this->userId, self::VALID_CSV_FILE));
    }

    /**
     * Get the data in the current ActivityRow.
     *
     * @return array|string|int
     */
    protected function data(): array|string|int
    {
        $this->data = [];

        foreach ($this->elements() as $element) {
            if ($element === 'transaction') {
                foreach ($this->$element as $transaction) {
                    $this->data[$element][] = $transaction->data($transaction->pluckIndex());
                }
            } else {
                $this->data[Str::snake($element)] = ($element === 'identifier')
                    ? $this->$element->data()
                    : $this->$element->data(Str::snake($this->$element->pluckIndex()));
            }
        }

        return $this->data;
    }

    /**
     * Write the validated data into the designated destination file.
     * Basically the logic here if to fetch valid.json from s3 and append current data, then push to s3 again.
     * With an additional condition that checks if the current activity being processed already exists or not in s3's valid.json.
     * If already exists in valid.json, do not append. (To avoid duplication in the import listing page).
     *
     * @param string $destinationFilePath
     *
     * @return void
     * @throws \JsonException
     */
    protected function writeCsvDataAsJson(string $destinationFilePath): void
    {
        $validJsonFile = awsGetFile($destinationFilePath);

        if (!$validJsonFile) {
            $contentInValidDotJson = collect([
                [
                    'data'      => $this->data(),
                    'errors'    => $this->errors(),
                    'status'    => 'processed',
                    'existence' => $this->existence,
                ],
            ]);
        } else {
            $contentInValidDotJson = collect(json_decode($validJsonFile, true, 512, JSON_THROW_ON_ERROR));

            $activityIdentifiersPresentInValidJson = $contentInValidDotJson
                ->pluck('data.identifier.activity_identifier')
                ->filter();

            $currentActivityIdentifier = Arr::get($this->data(), 'identifier.activity_identifier', '');

            if (!$activityIdentifiersPresentInValidJson->contains($currentActivityIdentifier)) {
                $appendableData = [
                    'data'      => $this->data(),
                    'errors'    => $this->errors(),
                    'status'    => 'processed',
                    'existence' => $this->existence,
                ];

                $contentInValidDotJson->push($appendableData);
            }
        }

        $this->uploadContent($destinationFilePath, $contentInValidDotJson->toJson(JSON_THROW_ON_ERROR));
    }

    private function uploadContent(string $path, string $content): void
    {
        try {
            awsUploadFile($path, $content);
        } catch (\Exception $e) {
            awsUploadFile('error-csv-appendDataIntoFile.log', $e->getMessage());
        }
    }

    /**
     * Get all the errors associated with the current ActivityRow.
     *
     * @return array
     */
    public function errors(): array
    {
        $tempErrors = $this->errors;

        foreach ($this->errors as $key => $value) {
            if (empty($tempErrors[$key])) {
                unset($tempErrors[$key]);
            }
        }

        return $tempErrors;
    }

    /**
     * Record errors into warning, error and critical.
     *
     * @param mixed $name
     * @param mixed $element
     * @param bool $isTransaction
     *
     * @return void
     */
    protected function recordErrors($name, $element, $isTransaction = false): void
    {
        if (!empty($element->criticals())) {
            $this->errors['critical'][$name] = $isTransaction ? $this->mergeTransactionErrors('critical', $element) : $element->criticals();
        }

        if (!empty($element->errors())) {
            $this->errors['error'][$name] = $isTransaction ? $this->mergeTransactionErrors('error', $element) : $element->errors();
        }

        if (!empty($element->warnings())) {
            $this->errors['warning'][$name] = $isTransaction ? $this->mergeTransactionErrors('warning', $element) : $element->warnings();
        }
    }

    /**
     * Merge error of multiple transaction within activity.
     *
     * @param string $ruleType
     * @param $element
     *
     * @return array
     */
    protected function mergeTransactionErrors($ruleType, $element): array
    {
        $currentErrors = call_user_func([$element, $ruleType . 's']);
        $existingErrors = Arr::get($this->errors, "$ruleType.transaction", []);

        if (!empty($existingErrors)) {
            foreach ($currentErrors as $index => $errorMessage) {
                $existingErrors[$index] = $errorMessage;
            }

            return $existingErrors;
        }

        return $currentErrors;
    }

    /**
     * Validate unique against Identifiers Internal References within the uploaded CSV file.
     *
     * @param $rows
     *
     * @return $this
     */
    public function validateUnique($rows): static
    {
        $commonIdentifierCount = $this->countDuplicateActivityIdentifiers($rows);

        if ($this->containsDuplicateActivities($commonIdentifierCount)) {
            $this->isValid = false;
        }

        return $this;
    }

    /**
     * Checks if the activityIdentifiers already exists or not.
     *
     * @param $row
     *
     * @return $this
     */
    public function checkExistence($row): static
    {
        $currentActivityIdentifier = Arr::get($row, 'activity_identifier.0', null);
        $currentActivityIdentifier = trim($currentActivityIdentifier);

        if (in_array($currentActivityIdentifier, array_map('trim', $this->activityIdentifiers))) {
            $this->existence = true;
        }

        return $this;
    }

    /**
     * Get the Transactions for the ActivityRow.
     *
     * @return array
     */
    public function getTransactions(): array
    {
        return $this->transaction;
    }

    /**
     * Get all the internal references for an Activity's Transactions.
     *
     * @return array
     */
    protected function getTransactionInternalReferences(): array
    {
        $references = [];

        foreach ($this->getTransactions() as $transaction) {
            $reference = Arr::get($transaction->data(), 'transaction.reference');
            if ($reference !== '') {
                $references[] = $reference;
            }
        }

        return $references;
    }

    /**
     * Get the count of duplicated Activity Identifiers.
     *
     * @param $rows
     *
     * @return int
     */
    protected function countDuplicateActivityIdentifiers($rows): int
    {
        $commonIdentifierCount = 0;

        foreach ($rows as $row) {
            if (array_key_exists('activity_identifier', $row) && $this->identifier->data() && $this->identifier->data()['activity_identifier'] === strval(Arr::get($row, 'activity_identifier.0'))) {
                $commonIdentifierCount++;
            }
        }

        return $commonIdentifierCount;
    }

    /**
     * Check if the Activity Identifiers are duplicated within the uploaded CSV file.
     *
     * @param $commonIdentifierCount
     *
     * @return bool
     */
    protected function containsDuplicateActivities($commonIdentifierCount): bool
    {
        if ($commonIdentifierCount > 1) {
            $this->errors['critical']['activity_identifier']['activity_identifier'] = 'This Activity has been duplicated in the uploaded Csv File.';

            return true;
        }

        return false;
    }
}
