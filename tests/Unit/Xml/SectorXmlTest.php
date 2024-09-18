<?php

namespace Tests\Unit\Xml;

/**
 * Class ScopeXmlTest.
 */
class SectorXmlTest extends XmlBaseTest
{
    /**
     * throw validation same vocab but empty percentage.
     *
     * @return void
     * @test
     */
    public function throw_validation_if_multiple_sector_same_vocabulary_empty_percentage(): void
    {
        $rows = $this->vocabulary_same_empty_percentage_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.sum'), $flattenErrors);
    }

    /**
     * empty percentage data.
     *
     * @return array
     */
    public function vocabulary_same_empty_percentage_data(): array
    {
        $data = $this->completeXml;
        $data[0]['sector'] = [
            [
                'sector_vocabulary' => '1',
                'vocabulary_uri' => null,
                'code' => '11130',
                'category_code' => null,
                'sdg_goal' => null,
                'sdg_target' => null,
                'text' => null,
                'percentage' => null,
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'sector_vocabulary' => '1',
                'vocabulary_uri' => null,
                'code' => '11130',
                'category_code' => null,
                'sdg_goal' => null,
                'sdg_target' => null,
                'text' => null,
                'percentage' => null,
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'sector_vocabulary' => '1',
                'vocabulary_uri' => null,
                'code' => '11130',
                'category_code' => null,
                'sdg_goal' => null,
                'sdg_target' => null,
                'text' => null,
                'percentage' => null,
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
        ];

        return $data;
    }

    /**
     * pass single sector with empty percentage.
     *
     * @return void
     * @test
     */
    public function pass_if_single_sector_empty_percentage(): void
    {
        $rows = $this->single_sector_empty_percentage_data();
        $flattenErrors = $this->getErrors($rows);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * empty percentage data for single sector.
     *
     * @return array
     */
    public function single_sector_empty_percentage_data(): array
    {
        $data = $this->completeXml;
        $data[0]['sector'] = [
            [
                'sector_vocabulary' => '1',
                'vocabulary_uri' => null,
                'code' => '11130',
                'category_code' => null,
                'sdg_goal' => null,
                'sdg_target' => null,
                'text' => null,
                'percentage' => null,
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
        ];

        return $data;
    }

    /**
     * Throw validation ifn empty narrative for vocab 98 or 99.
     *
     * @return void
     * @test
     */
    public function check_if_narrative_required_when_vocabulary_98_or_99(): void
    {
        $rows = $this->narrative_empty_vocabulary_98_or_99();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.narrative_is_required'), $flattenErrors);
    }

    /**
     * empty narrative for vocab 98 or 99.
     *
     * @return array
     */
    public function narrative_empty_vocabulary_98_or_99(): array
    {
        $data = $this->completeXml;
        $data[0]['sector'] = [
            [
                'sector_vocabulary' => '1',
                'vocabulary_uri' => null,
                'code' => '11130',
                'category_code' => null,
                'sdg_goal' => null,
                'sdg_target' => null,
                'text' => null,
                'percentage' => '100',
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'sector_vocabulary' => '98',
                'vocabulary_uri' => 'https://www.google.com',
                'code' => null,
                'category_code' => null,
                'sdg_goal' => null,
                'sdg_target' => null,
                'text' => '12345',
                'percentage' => '100',
                'narrative' => [
                    [
                        'narrative' => null,
                        'language' => null,
                    ],
                ],
            ],
            [
                'sector_vocabulary' => '99',
                'vocabulary_uri' => 'https://www.google.com',
                'code' => null,
                'category_code' => null,
                'sdg_goal' => null,
                'sdg_target' => null,
                'text' => '12345',
                'percentage' => '100',
                'narrative' => [
                    [
                        'narrative' => null,
                        'language' => null,
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
    public function throws_validation_if_all_invalid_data(): void
    {
        $rows = $this->get_invalid_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.sector_code_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.url_valid'), $flattenErrors);
        $this->assertContains(trans('validation.vocabulary_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_sector.percentage.numeric'), $flattenErrors);
    }

    /**
     * Invalid data.
     *
     * @return array
     */
    public function get_invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['sector'] = [
            [
                'sector_vocabulary' => '1',
                'vocabulary_uri' => null,
                'code' => 'invalid code',
                'category_code' => null,
                'sdg_goal' => null,
                'sdg_target' => null,
                'text' => null,
                'percentage' => '-100',
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'sector_vocabulary' => '98',
                'vocabulary_uri' => 'invalid uri',
                'code' => null,
                'category_code' => null,
                'sdg_goal' => null,
                'sdg_target' => null,
                'text' => '12345',
                'percentage' => 'invalid percentage',
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'sector_vocabulary' => '91231239',
                'vocabulary_uri' => 'https://www.google.com',
                'code' => null,
                'category_code' => null,
                'sdg_goal' => null,
                'sdg_target' => null,
                'text' => '123',
                'percentage' => '100',
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
        ];

        return $data;
    }
}
