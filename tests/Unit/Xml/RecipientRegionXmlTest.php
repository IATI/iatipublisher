<?php

namespace Tests\Unit\Xml;

/**
 * Class RecipientRegionXmltest.
 */
class RecipientRegionXmlTest extends XmlBaseTest
{
    /**
     * Throws validation if region or country percentage sum not equal to 100.
     *
     * @return void
     * @test
     */
    public function check_if_throws_validation_when_region_country_percentage_sum_not_equal_to_100(): void
    {
        $rows = $this->region_country_percentage_sum_not_equal_to_100_single_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.recipient_country_region_percentage_sum'), $flattenErrors);
    }

    /**
     * Throws validation if country oregion percentage not equal to 100.
     *
     * @return array
     */
    public function region_country_percentage_sum_not_equal_to_100_single_data(): array
    {
        $data = $this->completeXml;
        $data[0]['recipient_country'] = [
          [
              'country_code' => 'AF',
              'percentage' => '25',
              'narrative' => [
                  [
                      'narrative' => 'narrative one',
                      'language' => 'en',
                  ],
              ],
          ],
        ];
        $data[0]['recipient_region'] = [
            [
                'region_code' => '289',
                'region_vocabulary' => '1',
                'vocabulary_uri' => null,
                'percentage' => '25',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
        ];

        return $data;
    }

    /**
     * Pass if sum percentage is equal to 100.
     *
     * @return void
     * @test
     */
    public function pass_if_region_country_percentage_sum_equal_to_100(): void
    {
        $rows = $this->region_country_percentage_sum_equal_to_100_single_data();
        $flattenErrors = $this->getErrors($rows);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Sum percentage equal to 100 data.
     *
     * @return array
     */
    public function region_country_percentage_sum_equal_to_100_single_data(): array
    {
        $data = $this->completeXml;
        $data[0]['recipient_country'] = [
            [
                'country_code' => 'AF',
                'percentage' => '50',
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
        ];

        $data[0]['recipient_region'] = [
            [
                'region_code' => '289',
                'region_vocabulary' => '1',
                'vocabulary_uri' => null,
                'percentage' => '50',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
        ];

        return $data;
    }

    /**
     * Throw validation if sam vocab percentage sum not equal to 100 one 20 but other not 80.
     *
     * @return void
     * @test
     */
    public function throw_validation_if_same_vocabulary_sum_not_equal_to_80_if_country_20(): void
    {
        $rows = $this->country_20_region_60_multiple_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.recipient_country_region_percentage_sum'), $flattenErrors);
    }

    /**
     * Country 20 but region 60 with multiple vocab.
     *
     * @return array
     */
    public function country_20_region_60_multiple_data(): array
    {
        $data = $this->completeXml;
        $data[0]['recipient_country'] = [
            [
                'country_code' => 'AF',
                'percentage' => '20',
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
        ];

        $data[0]['recipient_region'] = [
            [
                'region_code' => '289',
                'region_vocabulary' => '1',
                'vocabulary_uri' => null,
                'percentage' => '30',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'region_code' => '289',
                'region_vocabulary' => '1',
                'vocabulary_uri' => null,
                'percentage' => '30',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
        ];

        $data[1]['recipient_country'] = [
            [
                'country_code' => 'AF',
                'percentage' => '20',
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
        ];

        $data[1]['recipient_region'] = [
            [
                'region_code' => '289',
                'region_vocabulary' => '1',
                'vocabulary_uri' => null,
                'percentage' => '30',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'region_code' => '289',
                'region_vocabulary' => '1',
                'vocabulary_uri' => null,
                'percentage' => '30',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
        ];

        return $data;
    }

    /**
     * Throws validation if percentage sum not equal withing same vocab.
     *
     * @return void
     * @test
     */
    public function throw_validation_percentage_sum_within_same_vocabulary_not_equal(): void
    {
        $rows = $this->diff_vocal_percentage();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.recipient_country_region_percentage_sum'), $flattenErrors);
    }

    /**
     * Different vocab percentage but not equal.
     *
     * @return array
     */
    public function diff_vocal_percentage(): array
    {
        $data = $this->completeXml;
        $data[0]['recipient_country'] = [
            [
                'country_code' => 'AF',
                'percentage' => '40',
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
        ];
        $data[0]['recipient_region'] = [
            [
                'region_code' => '88',
                'region_vocabulary' => '1',
                'vocabulary_uri' => null,
                'percentage' => '30',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'region_code' => '289',
                'region_vocabulary' => '2',
                'vocabulary_uri' => null,
                'percentage' => '30',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'region_code' => '2899',
                'region_vocabulary' => '2',
                'vocabulary_uri' => null,
                'percentage' => '30',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
        ];

        return $data;
    }

    /**
     * Throws validation messages for all invalid data.
     *
     * @return void
     * @test
     */
    public function throw_possible_validation_for_all_invalid_data(): void
    {
        $rows = $this->invalid_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.percentage_must_be_at_least_0'), $flattenErrors);
        $this->assertContains(trans('validation.url_valid'), $flattenErrors);
        $this->assertContains(trans('validation.percentage_must_be_a_number'), $flattenErrors);
        $this->assertContains(trans('validation.recipient_country_region_percentage_sum'), $flattenErrors);
        $this->assertContains(trans('validation.activity_recipient_region.percentage.country_percentage_complete'), $flattenErrors);
    }

    /**
     * All invalid data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;

        $data[0]['recipient_country'] = [
            [
                'country_code' => 'AF',
                'percentage' => '100', // already 100% validation message
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
        ];
        $data[0]['recipient_region'] = [
            [
                'region_code' => '289',
                'region_vocabulary' => '1',
                'vocabulary_uri' => null,
                'percentage' => '60',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'custom_code' => '222',
                'region_vocabulary' => '2',
                'vocabulary_uri' => null,
                'percentage' => '20',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'custom_code' => '333',
                'region_vocabulary' => '2',
                'vocabulary_uri' => null,
                'percentage' => '-20',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'custom_code' => '3331',
                'region_vocabulary' => '99',
                'vocabulary_uri' => 'invalid url',
                'percentage' => 'invalid percentage',
                'narrative' => [
                    [
                        'narrative' => 'narrative one ',
                        'language' => 'en',
                    ],
                ],
            ],
        ];

        return $data;
    }
}
