<?php

declare(strict_types=1);

namespace App\CsvImporter\Entities\Activity\Components\Elements;

use App\CsvImporter\Entities\Activity\Components\Elements\Foundation\Iati\Element;
use App\CsvImporter\Entities\Activity\Components\Factory\Validation;
use App\Http\Requests\Activity\Identifier\IdentifierRequest;
use App\IATI\Traits\DataSanitizeTrait;

/**
 * Class Identifier.
 */
class Identifier extends Element
{
    use DataSanitizeTrait;

    /**
     * @var
     */
    protected $organizationId;

    /**
     * @var
     */
    protected $activityIdentifiers;

    /**
     * CSV Header of Description with their code.
     */
    private array $_csvHeader = ['activity_identifier'];

    /**
     * @var array
     */
    protected array $template = [['activity_identifier' => '']];

    /**
     * @var IdentifierRequest
     */
    private IdentifierRequest $request;

    /**
     * Description constructor.
     * @param                   $fields
     * @param Validation        $factory
     */
    public function __construct($fields, Validation $factory)
    {
        $this->prepare($fields);
        $this->factory = $factory;
        $this->request = new IdentifierRequest();
    }

    /**
     * Prepare the Identifier element.
     *
     * @param $fields
     *
     * @return void
     */
    protected function prepare($fields): void
    {
        foreach ($fields as $key => $values) {
            if (!is_null($values) && array_key_exists($key, array_flip($this->_csvHeader))) {
                foreach ($values as $value) {
                    $this->map($value);
                    break;
                }
            }
        }

        $fields = is_array($fields) ? $this->sanitizeData($fields) : $fields;
    }

    /**
     * Map data from CSV file into Identifier data format.
     *
     * @param $value
     *
     * @return void
     */
    public function map($value): void
    {
        $this->data[end($this->_csvHeader)] = $value;
    }

    /**
     * Validate data for Identifier.
     *
     * @return $this
     */
    public function validate(): static
    {
        $this->validator = $this->factory->sign($this->data())
            ->with($this->rules(), $this->messages())
            ->getValidatorInstance();
        $this->errorValidator = $this->factory->sign($this->data())
            ->with($this->errorRules(), $this->messages())
            ->getValidatorInstance();
        $this->criticalValidator = $this->factory->sign($this->data())
            ->with($this->criticalRules(), $this->messages())
            ->getValidatorInstance();
        $this->setValidity();

        return $this;
    }

    /**
     * Provides the rules for the Identifier validation.
     *
     * @return array
     */
    public function rules(): array
    {
        return $this->request->getWarningForIdentifier();
    }

    /**
     * Provides the rules for the Identifier validation.
     *
     * @return array
     */
    public function criticalRules(): array
    {
        return $this->request->getErrorsForIdentifier(null, true);
    }

    /**
     * Returns errorRules for identifier.
     *
     * @return array
     */
    public function errorRules(): array
    {
        return [];
    }

    /**
     * Provides custom messages used for Identifier Validation.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->request->messages();
    }

    /**
     * Set the organizationId for the current Organization.
     *
     * @param $organizationId
     *
     * @return void
     */
    public function setOrganization($organizationId): void
    {
        $this->organizationId = $organizationId;
    }

    /**
     * Set activity identifiers.
     *
     * @param array $activityIdentifiers
     *
     * @return void
     */
    public function setActivityIdentifier(array $activityIdentifiers): void
    {
        $this->activityIdentifiers = $activityIdentifiers;
    }
}
