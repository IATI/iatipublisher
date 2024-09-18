<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

/**
 * @class RequiredEitherNumericTargetValueOrActualValue
 *
 * Change source: https://github.com/iati/iatipublisher/issues/1492
 */
class RequiredEitherNumericTargetValueOrActualValue implements Rule
{
    /**
     * @var string
     */
    public string $errorType = '';

    /**
     * Create a new rule instance.
     *
     * @param  string  $field
     * @param  string  $otherField
     * @param  array  $formFields
     */
    public function __construct(protected string $field, protected string $otherField, protected array $formFields)
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if ($this->bothTargetAndActualHaveEmptyValues()) {
            return false;
        }

        $value = trim($value ?? '');

        if (strlen($value) > 0 && !is_numeric($value)) {
            $this->errorType = 'numeric';

            return false;
        }

        if ($this->otherFieldHasAtleastOneNonEmptyValue() && strlen($value) > 0 && !is_numeric($value)) {
            $this->errorType = 'numeric';

            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        if ($this->errorType === 'numeric') {
            return trans('validation.activity_periods.value.numeric');
        }

        if ($this->field === 'target') {
            return trans('validation.activity_periods.value.target_required');
        }

        return trans('validation.activity_periods.value.actual_required');
    }

    /**
     * Check if both target and actual fields have empty value field.
     *
     * @return bool
     */
    private function bothTargetAndActualHaveEmptyValues(): bool
    {
        $targetValues = Arr::get($this->formFields, 'target', []);
        $actualValues = Arr::get($this->formFields, 'actual', []);

        $targetAllNonEmpty = !collect($targetValues)->every(function ($item) {
            return !empty(trim($item['value'] ?? ''));
        });

        $actualAllNonEmpty = !collect($actualValues)->every(function ($item) {
            return !empty(trim($item['value'] ?? ''));
        });

        return $targetAllNonEmpty && $actualAllNonEmpty;
    }

    /**
     * Check if the other field (target or actual) has at least one non-empty value field.
     *
     * @return bool
     */
    private function otherFieldHasAtleastOneNonEmptyValue(): bool
    {
        foreach ($this->formFields[$this->otherField] as $item) {
            $item = trim($item['value'] ?? '');
            if ($item !== '') {
                return true;
            }
        }

        return false;
    }
}
