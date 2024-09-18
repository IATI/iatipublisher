<?php

namespace Tests\Unit\Xml;

/**
 * Class PolicyMarkerXmlTest.
 */
class PolicyMarkerXmlTest extends XmlBaseTest
{
    /**
     * Throws validation messages for all invalid data.
     *
     * @return void
     * @test
     */
    public function throw_all_possible_validation_if_invalid_data(): void
    {
        $rows = $this->invalid_data();
        $flattenErrors = $this->getErrors($rows);
        $this->assertContains(trans('validation.vocabulary_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_policy_marker.invalid_code'), $flattenErrors);
        $this->assertContains(trans('validation.url_valid'), $flattenErrors);
        $this->assertContains(trans('validation.language_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.narrative_is_required_when_language_is_populated'), $flattenErrors);
        $this->assertContains(trans('validation.activity_policy_marker.narrative_required'), $flattenErrors);
    }

    /**
     * Invalid policy marker data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['policy_marker'] = [
            [
                'policy_marker_vocabulary' => 'invalid vocabulary',
                'vocabulary_uri' => 'invalid uri',
                'significance' => 'invalid significance',
                'policy_marker' => 'invalid marker code',
                'policy_marker_text' => '8',
                'narrative' => [
                    [
                        'narrative' => '',
                        'language' => 'invalid language',
                    ],
                ],
            ],
            [
                'policy_marker_vocabulary' => '99',
                'vocabulary_uri' => 'https://www.google.com',
                'significance' => '0',
                'policy_marker' => '',
                'policy_marker_text' => '8',
                'narrative' => [
                    [
                        'narrative' => '',
                        'language' => '',
                    ],
                ],
            ],
        ];

        return $data;
    }
}
