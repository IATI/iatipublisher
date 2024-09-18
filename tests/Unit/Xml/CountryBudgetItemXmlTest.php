<?php

namespace Tests\Unit\Xml;

/**
 * Class CountryBudgetItemXmlTest.
 */
class CountryBudgetItemXmlTest extends XmlBaseTest
{
    /**
     * Throws validation messages for all invalid data.
     *
     * @return void
     * @test
     */
    public function throw_possible_validation_for_invalid_error(): void
    {
        $rows = $this->invalid_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.vocabulary_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_country_budget_items.invalid_code'), $flattenErrors);
        $this->assertContains(trans('validation.percentage_must_be_a_number'), $flattenErrors);
        $this->assertContains(trans('validation.activity_country_budget_items.percentage.sum'), $flattenErrors);
        $this->assertContains(trans('validation.activity_country_budget_items.percentage.total'), $flattenErrors);
        $this->assertContains(trans('validation.language_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.narrative_is_required_when_language_is_populated'), $flattenErrors);
    }

    /**
     * Invalid country budget item.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['country_budget_items'] = [
            [
                'country_budget_vocabulary' => 'invalid',
                'budget_item' => [
                    [
                        'code' => 'invalid',
                        'percentage' => 'invalid',
                        'description' => [
                            [
                                'narrative' => [
                                    [
                                        'narrative' => '',
                                        'language' => 'invalid language',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $data[1]['country_budget_items'] = [
            [
                'country_budget_vocabulary' => '2',
                'budget_item' => [
                    [
                        'code' => '1.2.1',
                        'percentage' => '10',
                        'description' => [
                            [
                                'narrative' => [
                                    [
                                        'narrative' => 'narr',
                                        'language' => 'en',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => '1.2.1',
                        'percentage' => '10',
                        'description' => [
                            [
                                'narrative' => [
                                    [
                                        'narrative' => 'narr',
                                        'language' => 'en',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $data;
    }
}
