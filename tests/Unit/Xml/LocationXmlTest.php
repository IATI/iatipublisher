<?php

namespace Tests\Unit\Xml;

/**
 * Class LocationXmlTest.
 */
class LocationXmlTest extends XmlBaseTest
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

        $this->assertContains(trans('validation.reference_should_not_contain_symbol'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.vocabulary_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_location.administrative.level_min'), $flattenErrors);
        $this->assertContains(trans('validation.activity_location.administrative.level_int'), $flattenErrors);
        $this->assertContains(trans('validation.activity_location.point.latitude_numeric'), $flattenErrors);
        $this->assertContains(trans('validation.activity_location.point.longitude_numeric'), $flattenErrors);
    }

    /**
     * Invalid location.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['location'] = [
            [
                'ref' => '/\&*^%',
                'location_reach' => [
                    [
                        'code' => 'invalid code',
                    ],
                ],
                'location_id' => [
                    [
                        'code' => '',
                        'vocabulary' => 'invalid vocab',
                    ],
                ],
                'name' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
                'description' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
                'activity_description' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
                'administrative' => [
                    [
                        'code' => 'invalid',
                        'vocabulary' => 'invalid',
                        'level' => '-2',
                    ],
                    [
                        'code' => 'A1',
                        'vocabulary' => '',
                        'level' => 'invalid',
                    ],
                ],
                'point' => [
                    [
                        'srs_name' => '',
                        'pos' => [
                            [
                                'latitude' => 'invalid',
                                'longitude' => 'invalid',
                            ],
                        ],
                    ],
                ],
                'exactness' => [
                    [
                        'code' => 'invalid',
                    ],
                ],
                'location_class' => [
                    [
                        'code' => 'invalid',
                    ],
                ],
                'feature_designation' => [
                    [
                        'code' => 'invalid',
                    ],
                ],
            ],
        ];

        return $data;
    }
}
