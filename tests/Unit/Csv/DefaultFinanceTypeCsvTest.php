<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\DefaultFinanceType;
use Illuminate\Support\Arr;

/**
 * Class DefaultFinanceTypeCsvTest.
 */
class DefaultFinanceTypeCsvTest extends CsvBaseTest
{
    /**
     * Collects validation messages.
     *
     * @throws \JsonException
     */
    public function getErrors($rows): array
    {
        $errors = [];
        foreach ($rows as $row) {
            $element = new DefaultFinanceType($row, $this->validation);
            $element->validate()->withErrors();

            if (!empty($element->errors()) || !empty($element->criticals()) || !empty($element->warnings())) {
                $errors[] = $element->errors() + $element->criticals() + $element->warnings();
            }
        }

        return $errors;
    }

    /**
     * Throws validation messages for invalid data.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function throw_validation_if_invalid_value(): void
    {
        $this->signIn();
        $rows = $this->invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.activity_default_finance_type.in'), $flattenErrors);
        $this->assertContains(trans('validation.activity_default_finance_type.size'), $flattenErrors);
    }

    /**
     * Invalid default finance type data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['default_finance_type'] = [99];
        $data[1]['default_finance_type'] = [10, 20];

        return $data;
    }

    /**
     * valid data.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function pass_if_valid_data(): void
    {
        $this->signIn();
        $rows = $this->valid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Valid data.
     *
     * @return array
     */
    public function valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['default_finance_type'] = [1];
        $data[1]['default_finance_type'] = [110];

        return $data;
    }
}
