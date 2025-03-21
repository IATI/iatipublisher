<?php

namespace Tests\Unit\Xml;

/**
 * Class PlannedDisbursementXmlTest.
 */
class PlannedDisbursementXmlTest extends XmlBaseTest
{
    /**
     * Throws validation messages for all invalid data.
     *
     * @return void
     * @test
     */
    public function throw_validation_if_invalid_data(): void
    {
        $rows = $this->invalid_data();
        $flattenErrors = $this->getErrors($rows);
        $this->assertContains(trans('validation.date_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.date_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.amount_number'), $flattenErrors);
        $this->assertContains(trans('validation.invalid_currency'), $flattenErrors);
        $this->assertContains(trans('validation.date_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.organisation_type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.organisation_type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_planned_disbursement.invalid_type'), $flattenErrors);
        $this->assertContains(trans('validation.amount_negative'), $flattenErrors);
        $this->assertContains(trans('validation.invalid_currency'), $flattenErrors);
        $this->assertContains(trans('validation.organisation_type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.organisation_type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_planned_disbursement.invalid_type'), $flattenErrors);
    }

    /**
     * Invalid planned disbursement data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['planned_disbursement'] = [
            [
                'planned_disbursement_type' => '',
                'period_start' => [
                    [
                        'date' => 'invalid date',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => 'invalid date',
                    ],
                ],
                'value' => [
                    [
                        'amount' => 'invalid amount',
                        'currency' => 'invalid currency',
                        'value_date' => 'invalid date',
                    ],
                ],
                'provider_org' => [
                    [
                        'ref' => '/\&*^',
                        'provider_activity_id' => 'A nulla nobis rerum',
                        'type' => 'invalid',
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
                'receiver_org' => [
                    [
                        'ref' => '/\*&^',
                        'receiver_activity_id' => '1234',
                        'type' => 'invalid',
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'planned_disbursement_type' => '199',
                'period_start' => [
                    [
                        'date' => '2020-01-01',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => '2023-01-01',
                    ],
                ],
                'value' => [
                    [
                        'amount' => '-1000',
                        'currency' => 'invalid currency',
                        'value_date' => '2019-01-01',
                    ],
                ],
                'provider_org' => [
                    [
                        'ref' => '/\&*^',
                        'provider_activity_id' => 'A nulla nobis rerum',
                        'type' => 'invalid',
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
                'receiver_org' => [
                    [
                        'ref' => '/\*&^',
                        'receiver_activity_id' => '1234',
                        'type' => 'invalid',
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'planned_disbursement_type' => '199',
                'period_start' => [
                    [
                        'date' => '2020-01-01',
                    ],
                ],
                'period_end' => [
                    [
                        'date' => '1999-01-01',
                    ],
                ],
                'value' => [
                    [
                        'amount' => '-1000',
                        'currency' => 'invalid currency',
                        'value_date' => '2019-01-01',
                    ],
                ],
                'provider_org' => [
                    [
                        'ref' => '/\&*^',
                        'provider_activity_id' => 'A nulla nobis rerum',
                        'type' => 'invalid',
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
                'receiver_org' => [
                    [
                        'ref' => '/\*&^',
                        'receiver_activity_id' => '1234',
                        'type' => 'invalid',
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $data;
    }
}
