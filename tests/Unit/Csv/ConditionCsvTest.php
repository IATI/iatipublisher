<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\Condition;
use Illuminate\Support\Arr;

/**
 * Class ConditionCsvTest.
 */
class ConditionCsvTest extends CsvBaseTest
{
    /**
     * Collects error messages.
     *
     * @param $rows
     * @return array
     */
    public function getErrors($rows): array
    {
        $errors = [];

        foreach ($rows as $row) {
            $reportingOrg = new Condition($row, $this->validation);
            $reportingOrg->validate()->withErrors();

            if (!empty($reportingOrg->errors()) || !empty($reportingOrg->criticals()) || !empty($reportingOrg->warnings())) {
                $errors[] = $reportingOrg->errors() + $reportingOrg->criticals() + $reportingOrg->warnings();
            }
        }

        return $errors;
    }

    /**
     * All valid data.
     *
     * @return array
     */
    public function valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['conditions_attached'] = ['0'];
        $data[0]['condition_type'] = ['1', '3'];
        $data[0]['condition_narrative'] = ['narr one', 'nar two'];

        $data[1]['conditions_attached'] = ['0'];
        $data[1]['condition_type'] = ['1', '3'];
        $data[1]['condition_narrative'] = ['narr one', 'nar two'];

        return $data;
    }

    /**
     * Valid data.
     *
     * @return void
     * @test
     */
    public function pass_if_all_valid_data(): void
    {
        $this->signIn();
        $rows = $this->valid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Throw validation messages for all invalid data.
     *
     * @return void
     * @test
     */
    public function throw_validation_if_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertContains(trans('validation.activity_conditions.invalid_type'), $flattenErrors);
    }

    /**
     * Invalid Condition data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['conditions_attached'] = ['0'];
        $data[0]['condition_type'] = ['1', '5'];
        $data[0]['condition_narrative'] = ['narr one', 'nar two'];

        $data[1]['conditions_attached'] = ['0'];
        $data[1]['condition_type'] = ['1', '5'];
        $data[1]['condition_narrative'] = ['narr one', 'nar two'];

        return $data;
    }
}
