<?php

declare(strict_types=1);

namespace App\CsvImporter\Entities\Activity\Components\Elements;

use App\CsvImporter\Entities\Activity\Components\Elements\Foundation\Iati\Element;
use App\CsvImporter\Entities\Activity\Components\Factory\Validation;
use App\Http\Requests\Activity\CapitalSpend\CapitalSpendRequest;
use Illuminate\Support\Arr;

/**
 * Class CapitalSpend.
 */
class CapitalSpend extends Element
{
    /**
     * CSV Header of Description with their code.
     */
    private array $_csvHeader = ['capital_spend'];

    /**
     * Index under which the data is stored within the object.
     *
     * @var string
     */
    protected string $index = 'capital_spend';

    private CapitalSpendRequest $request;

    /**
     * Description constructor.
     *
     * @param            $fields
     * @param Validation $factory
     *
     * @throws \JsonException
     */
    public function __construct($fields, Validation $factory)
    {
        $this->prepare($fields);
        $this->factory = $factory;
        $this->request = new CapitalSpendRequest();
    }

    /**
     * Prepare the CapitalSpend element.
     *
     * @param $fields
     *
     * @return void
     * @throws \JsonException
     */
    public function prepare($fields): void
    {
        foreach ($fields as $key => $values) {
            if (!is_null($values) && array_key_exists($key, array_flip($this->_csvHeader))) {
                $this->data[$this->csvHeader()] = [];

                foreach ($values as $value) {
                    $this->map($value, $values);
                }

                if (is_array($this->data[$this->csvHeader()]) && empty($this->data[$this->csvHeader()])) {
                    $this->data[$this->csvHeader()] = '';
                }
            }
        }
    }

    /**
     * Map data from CSV into CapitalSpend data format.
     *
     * @param $value
     * @param $values
     *
     * @return void
     * @throws \JsonException
     */
    public function map($value, $values): void
    {
        if (!(is_null($value) || $value === '')) {
            ($this->countArrayElements($values) === 1) ? $this->data[$this->csvHeader()] = $value : $this->data[$this->csvHeader()][] = $value;
        }
    }

    /**
     * Validate data for IATI Element.
     *
     * @return $this
     * @throws \JsonException
     */
    public function validate(): static
    {
        $this->validator = $this->factory->sign($this->data())
            ->with($this->rules(), $this->messages())
            ->getValidatorInstance();
        $this->errorValidator = $this->factory->sign($this->data())
            ->with($this->errorRules(), $this->messages())
            ->getValidatorInstance();

        $this->setValidity();

        return $this;
    }

    /**
     * Provides the rules for the IATI Element validation.
     *
     * @return array
     * @throws \JsonException
     */
    public function rules(): array
    {
        return $this->request->getWarningForCapitalSpend(Arr::get($this->data, $this->csvHeader()));
    }

    /**
     * Provides the critical rules for the IATI Element validation.
     *
     * @return array
     * @throws \JsonException
     */
    public function errorRules(): array
    {
        return $this->request->getErrorsForCapitalSpend(Arr::get($this->data, $this->csvHeader()));
    }

    /**
     * Provides custom messages used for IATI Element Validation.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->request->messages();
    }

    /**
     * Get the Csv header for CapitalSpend.
     *
     * @return mixed
     */
    protected function csvHeader(): mixed
    {
        return end($this->_csvHeader);
    }
}
