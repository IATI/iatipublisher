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
        $this->assertContains('Period Start must be a date.', $flattenErrors);
        $this->assertContains('Period end must be a date', $flattenErrors);
        $this->assertContains('Period end must be a date after period', $flattenErrors);
        $this->assertContains('The Planned Disbursement Period must not be longer than three months', $flattenErrors);
        $this->assertContains('Amount field must be a number', $flattenErrors);
        $this->assertContains('Amount field must not be in negative.', $flattenErrors);
        $this->assertContains('The value currency is invalid.', $flattenErrors);
        $this->assertContains('The Value Date must be a valid Date', $flattenErrors);
        $this->assertContains('The planned disbursement provider org type is invalid.', $flattenErrors);
        $this->assertContains('The planned disbursement provider org ref shouldn\'t contain the symbols /, &, | or ?.', $flattenErrors);
        $this->assertContains('The planned disbursement receiver org type is invalid.', $flattenErrors);
        $this->assertContains('The planned disbursement receiver org ref shouldn\'t contain the symbols /, &, | or ?.', $flattenErrors);
        $this->assertContains('The planned disbursement type is invalid.', $flattenErrors);
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
