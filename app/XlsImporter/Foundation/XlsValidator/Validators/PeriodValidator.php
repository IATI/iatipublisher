<?php

declare(strict_types=1);

namespace App\XlsImporter\Foundation\XlsValidator\Validators;

use App\Http\Requests\Activity\Period\PeriodRequest;
use App\XlsImporter\Foundation\Factory\Validation;
use App\XlsImporter\Foundation\XlsValidator\ValidatorInterface;

/**
 * Class XmlValidator.
 */
class PeriodValidator implements ValidatorInterface
{
    /**
     * period with all its subelements.
     *
     * @var array
     */
    protected $period;

    /**
     * parent indicator id of period being validated.
     *
     * @var int
     */
    protected $indicatorId;

    /**
     * @var Validation
     */
    protected $factory;

    /**
     * Constructor.
     *
     * @param Validation $factory
     */
    public function __construct(Validation $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Initializes data for period validation.
     *
     * @param $period
     *
     * @return static
     */
    public function init($period): static
    {
        $this->period = $period['period'];
        $this->indicatorId = $period['indicatorId'];

        return $this;
    }

    /**
     * Returns warnings for xml uploaded activity.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = (new PeriodRequest())->getWarningForPeriod($this->period, true, [], [], $this->indicatorId);

        return $rules;
    }

    /**
     * Returns error rules for xml uploaded activity.
     *
     * @return array
     */
    public function errorRules(): array
    {
        $rules = (new PeriodRequest())->getErrorsForPeriod($this->period, true, []);

        return $rules;
    }

    /**
     * Returns critical rules for xml uploaded activity.
     *
     * @return array
     */
    public function criticalRules(): array
    {
        return [];
    }

    /**
     * Returns the required messages for the failed validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return (new PeriodRequest())->getMessagesForPeriod($this->period, true, [], $this->indicatorId);
    }

    /**
     * Validate period rules.
     *
     * @return array
     */
    public function validateData(): array
    {
        $errors = [
            'critical' => $this->factory->initialize($this->period, $this->criticalRules(), $this->messages())
                ->passes()
                ->withErrors(),
            'error' => $this->factory->initialize($this->period, $this->errorRules(), $this->messages())
                ->passes()
                ->withErrors(),
            'warning' => $this->factory->initialize($this->period, $this->rules(), $this->messages())
                ->passes()
                ->withErrors(),
        ];

        return $errors;
    }
}
