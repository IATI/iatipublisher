<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\Budget;
use Illuminate\Support\Arr;

/**
 * Class BudgetCsvTest.
 */
class BudgetCsvTest extends CsvBaseTest
{
    /**
     * Collects validation error messages.
     *
     * @param $rows
     * @return array
     * @throws \JsonException
     */
    public function getErrors($rows): array
    {
        $errors = [];
        foreach ($rows as $row) {
            $element = new Budget($row, $this->validation);
            $element->validate()->withErrors();

            if (!empty($element->errors()) || !empty($element->criticals()) || !empty($element->warnings())) {
                $errors[] = $element->errors() + $element->criticals() + $element->warnings();
            }
        }

        return $errors;
    }

    /**
     * Invalid data for budget period
     *  - same budget type
     *  - period end before period start
     *  - budget period longer than one year.
     *
     * @return void
     * @throws \JsonException
     * @test
     */
    public function throw_validation_if_two_budget_period_invalid(): void
    {
        $this->signIn();
        $rows = $this->get_invalid_budget_period();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.activity_budget.budget.budgets_identical'), $flattenErrors);
        $this->assertContains(trans('validation.period_end_after'), $flattenErrors);
        $this->assertContains(trans('validation.activity_budget.date.period_start_end'), $flattenErrors);
        $this->assertContains(trans('validation.date_must_be_after_1900'), $flattenErrors);
    }

    /**
     * Invalid Budget Periods.
     *
     * @return array
     */
    public function get_invalid_budget_period(): array
    {
        $data = $this->completeData;
        $data[0]['budget_type'] = ['1', '1'];
        $data[0]['budget_status'] = ['1', '2'];
        $data[0]['budget_period_start'] = ['2022-01-01', '2022-01-01']; // identical period
        $data[0]['budget_period_end'] = ['2022-01-01', '2022-04-01']; // start date and end date identical
        $data[0]['budget_value'] = ['500', '1000'];
        $data[0]['budget_value_date'] = ['2022-01-05', '2022-07-05']; // budget value date not in between start and end date
        $data[0]['budget_currency'] = ['BSD', 'AED'];

        $data[1]['budget_type'] = ['1', '1'];
        $data[1]['budget_status'] = ['1', '2'];
        $data[1]['budget_period_start'] = ['2022-01-01', '2022-03-01']; // budget overlap
        $data[1]['budget_period_end'] = ['2022-04-01', '2022-04-01'];
        $data[1]['budget_value'] = ['500', '1000'];
        $data[1]['budget_value_date'] = ['2022-01-05', '2022-03-05'];
        $data[1]['budget_currency'] = ['BSD', 'AED'];

        $data[2]['budget_type'] = ['1', '1'];
        $data[2]['budget_status'] = ['1', '2'];
        $data[2]['budget_period_start'] = ['2022-01-01', '1800-03-01']; // budget period longer than one year and period start date with 1800
        $data[2]['budget_period_end'] = ['2024-04-01', '1800-04-01'];
        $data[2]['budget_value'] = ['500', '1000'];
        $data[2]['budget_value_date'] = ['2022-01-05', '2022-03-05'];
        $data[2]['budget_currency'] = ['BSD', 'AED'];

        return $data;
    }

    /**
     * Do not throw validation if revised not match with budget period.
     * Change source: https://github.com/iati/iatipublisher/issues/1493.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function do_not_throw_validation_if_revised_period_do_not_match_one_of_budget_period(): void
    {
        $this->signIn();
        $rows = $this->get_revised_period_not_matched_date();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertEmpty($flattenErrors);
    }

    /**
     * Invalid revised period.
     *
     * @return array
     */
    public function get_revised_period_not_matched_date(): array
    {
        $data = $this->completeData;
        $data[0]['budget_type'] = ['1', '2'];
        $data[0]['budget_status'] = ['1', '2'];
        $data[0]['budget_period_start'] = ['2022-01-01', '2022-06-01'];  // second one is revised type not matching budget type period
        $data[0]['budget_period_end'] = ['2022-05-01', '2022-07-01']; // second one is revised type not matching budget type period
        $data[0]['budget_value'] = ['500', '1000'];
        $data[0]['budget_value_date'] = ['2022-02-05', '2022-06-05'];
        $data[0]['budget_currency'] = ['BSD', 'AED'];

        $data[1]['budget_type'] = ['1', '2'];
        $data[1]['budget_status'] = ['1', '2'];
        $data[1]['budget_period_start'] = ['2022-01-01', '2022-06-01'];  // second one is revised type not matching budget type period
        $data[1]['budget_period_end'] = ['2022-05-01', '2022-07-01']; // second one is revised type not matching budget type period
        $data[1]['budget_value'] = ['500', '1000'];
        $data[1]['budget_value_date'] = ['2022-02-05', '2022-06-05'];
        $data[1]['budget_currency'] = ['BSD', 'AED'];

        return $data;
    }

    /**
     * All Possible invalid data.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function throw_validation_if_other_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->get_invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertContains(trans('validation.activity_budget.budget.invalid_status'), $flattenErrors);
        $this->assertContains(trans('validation.activity_budget.budget.invalid_type'), $flattenErrors);
        $this->assertContains(trans('validation.amount_negative'), $flattenErrors);
        $this->assertContains(trans('validation.date_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.amount_number'), $flattenErrors);
    }

    /**
     * negative value.
     *
     * @return array
     */
    public function get_invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['budget_type'] = ['9999', '2222']; // invalid budget type
        $data[0]['budget_status'] = ['11111', '22222']; // invalid status type
        $data[0]['budget_period_start'] = ['2022-01-01', '2022-06-01'];
        $data[0]['budget_period_end'] = ['2022-05-01', '2022-07-01'];
        $data[0]['budget_value'] = ['-500', '10asdfadsf00']; // negative number and alpha character
        $data[0]['budget_value_date'] = ['invalid date', '2022-06-05']; // invalid date
        $data[0]['budget_currency'] = ['BSD', 'AED'];

        $data[1]['budget_type'] = ['999', '2222']; // invalid budget type
        $data[1]['budget_status'] = ['11111', '22222']; // invalid status type
        $data[1]['budget_period_start'] = ['2022-01-01', '2022-06-01'];
        $data[1]['budget_period_end'] = ['2022-05-01', '2022-07-01'];
        $data[1]['budget_value'] = ['-500', '10asdfadsf00']; // negative number and alpha character
        $data[1]['budget_value_date'] = ['invalid date', '2022-06-05']; // invalid date
        $data[1]['budget_currency'] = ['BSD', 'AED'];

        return $data;
    }

    /**
     * All valid Data.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function pass_if_valid_data(): void
    {
        $this->signIn();
        $rows = $this->get_valid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Valid data.
     *
     * @return array
     */
    public function get_valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['budget_type'] = ['1', '1', '2'];
        $data[0]['budget_status'] = ['1', '1', '2'];
        $data[0]['budget_period_start'] = ['2022-01-01', '2022-06-02', '2022-01-01'];
        $data[0]['budget_period_end'] = ['2022-05-01', '2022-09-01', '2022-05-01'];
        $data[0]['budget_value'] = ['500', '1000', '1500'];
        $data[0]['budget_value_date'] = ['2022-02-01', '2022-07-01', '2022-03-05'];
        $data[0]['budget_currency'] = ['BSD', 'AED', 'AED'];

        $data[1]['budget_type'] = ['1', '1', '2'];
        $data[1]['budget_status'] = ['1', '1', '2'];
        $data[1]['budget_period_start'] = ['2022-01-01', '2022-06-02', '2022-01-01'];
        $data[1]['budget_period_end'] = ['2022-05-01', '2022-09-01', '2022-05-01'];
        $data[1]['budget_value'] = ['500', '1000', '1500'];
        $data[1]['budget_value_date'] = ['2022-02-01', '2022-07-01', '2022-03-05'];
        $data[1]['budget_currency'] = ['BSD', 'AED', 'AED'];

        return $data;
    }
}
