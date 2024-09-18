<?php

namespace Tests\Unit\Xml;

/**
 * Class BudgetXmlTest.
 */
class BudgetXmlTest extends XmlBaseTest
{
    /**
     * Throw validation if Invalid budget period.
     *
     * @return void
     * @test
     */
    public function throw_validation_if_two_budget_period_invalid(): void
    {
        $rows = $this->get_invalid_budget_period();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains('The periods of multiple budgets with the same type should not be the same', $flattenErrors);
        $this->assertContains(trans('validation.period_end_after'), $flattenErrors);
    }

    /**
     * invalid budget period data.
     *
     * @return array
     */
    public function get_invalid_budget_period(): array
    {
        $data = $this->completeXml;
        $data[0]['budget'] = [
            [
                'budget_status' => '1',
                'budget_type' => '1',
                'period_start' => [
                    [
                        'date' => '2022-01-01',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => '2022-01-01',
                    ],
                ],
                'budget_value' => [
                    [
                        'amount' => '500',
                        'currency' => 'BSD',
                        'value_date' => '2022-01-05',
                    ],
                ],
            ],
            [
                'budget_status' => '2',
                'budget_type' => '1',
                'period_start' => [
                    [
                        'date' => '2022-01-01',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => '2022-04-01',
                    ],
                ],
                'budget_value' => [
                    [
                        'amount' => '1000',
                        'currency' => 'AED',
                        'value_date' => '2022-07-05',
                    ],
                ],
            ],
        ];
        $data[1]['budget'] = [
            [
                'budget_status' => '1',
                'budget_type' => '1',
                'period_start' => [
                    [
                        'date' => '2022-01-01',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => '2022-04-01',
                    ],
                ],
                'budget_value' => [
                    [
                        'amount' => '500',
                        'currency' => 'BSD',
                        'value_date' => '2022-01-05',
                    ],
                ],
            ],
            [
                'budget_status' => '2',
                'budget_type' => '1',
                'period_start' => [
                    [
                        'date' => '2022-03-01',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => '2022-04-01',
                    ],
                ],
                'budget_value' => [
                    [
                        'amount' => '1000',
                        'currency' => 'AED',
                        'value_date' => '2022-03-05',
                    ],
                ],
            ],
        ];

        return $data;
    }

    /**
     * throw validation if budget period longer than one year
     * Should not start before 1900.
     *
     * @return void
     * @test
     */
    public function throw_validation_if_budget_period_longer_than_one_year_date_start_before_1900(): void
    {
        $rows = $this->date_longer_than_one_year_1800_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.activity_budget.date.period_start_end'), $flattenErrors);
        $this->assertContains(trans('validation.date_must_be_after_1900'), $flattenErrors);
    }

    /**
     * Invalid budget period data.
     *
     * @return array
     */
    public function date_longer_than_one_year_1800_data(): array
    {
        $data = $this->completeXml;
        $data[0]['budget'] = [
            [
                'budget_status' => '2',
                'budget_type' => '1',
                'period_start' => [
                    [
                        'date' => '1600-03-01',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => '1800-04-01',
                    ],
                ],
                'budget_value' => [
                    [
                        'amount' => '1000',
                        'currency' => 'AED',
                        'value_date' => '2022-03-05',
                    ],
                ],
            ],
            [
                'budget_status' => '2',
                'budget_type' => '1',
                'period_start' => [
                    [
                        'date' => '2020-03-01',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => '2023-04-01',
                    ],
                ],
                'budget_value' => [
                    [
                        'amount' => '1000',
                        'currency' => 'AED',
                        'value_date' => '2022-03-05',
                    ],
                ],
            ],
        ];

        return $data;
    }

    /**
     * Do not throw validation if revised not match with budget period.
     * Change source: https://github.com/iati/iatipublisher/issues/1493.
     *
     * @return void
     * @test
     */
    public function do_not_throw_validation_if_revised_period_do_not_match_one_of_budget_period(): void
    {
        $rows = $this->get_revised_period_not_matched_date();
        $flattenErrors = $this->getErrors($rows);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Revised period with original budget type data.
     *
     * @return array
     */
    public function get_revised_period_not_matched_date(): array
    {
        $data = $this->completeXml;
        $data[0]['budget'] = [
            [
                'budget_status' => '2',
                'budget_type' => '1',
                'period_start' => [
                    [
                        'date' => '2022-01-01',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => '2022-05-01',
                    ],
                ],
                'budget_value' => [
                    [
                        'amount' => '500',
                        'currency' => 'BSD',
                        'value_date' => '2022-02-05',
                    ],
                ],
            ],
            [
                'budget_status' => '2',
                'budget_type' => '2',
                'period_start' => [
                    [
                        'date' => '2022-06-01',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => '2022-07-01',
                    ],
                ],
                'budget_value' => [
                    [
                        'amount' => '1000',
                        'currency' => 'AED',
                        'value_date' => '2022-06-05',
                    ],
                ],
            ],
        ];

        return $data;
    }

    /**
     * Validation messages for all invalid data.
     *
     * @return void
     * @test
     */
    public function throw_validation_if_other_invalid_data(): void
    {
        $rows = $this->get_invalid_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.activity_budget.budget.invalid_status'), $flattenErrors);
        $this->assertContains(trans('validation.activity_budget.budget.invalid_type'), $flattenErrors);
        $this->assertContains(trans('validation.amount_negative'), $flattenErrors);
        $this->assertContains(trans('validation.date_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.amount_number'), $flattenErrors);
    }

    /**
     * All invalid data.
     *
     * @return array
     */
    public function get_invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['budget'] = [
            [
                'budget_status' => '1111',
                'budget_type' => '9999',
                'period_start' => [
                    [
                        'date' => '2022-01-01',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => '2022-05-01',
                    ],
                ],
                'budget_value' => [
                    [
                        'amount' => '-500',
                        'currency' => 'BSD',
                        'value_date' => 'invalid date',
                    ],
                ],
            ],
            [
                'budget_status' => '2222',
                'budget_type' => '2222',
                'period_start' => [
                    [
                        'date' => '2022-06-01',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => '2022-07-01',
                    ],
                ],
                'budget_value' => [
                    [
                        'amount' => 'invalid amount',
                        'currency' => 'AED',
                        'value_date' => '2022-06-05',
                    ],
                ],
            ],
        ];

        return $data;
    }
}
