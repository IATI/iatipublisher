<?php

namespace Tests\Unit\Xml;

/**
 * Class ConditionXmlTest.
 */
class ConditionXmlTest extends XmlBaseTest
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

        $this->assertContains(trans('validation.activity_conditions.invalid_type'), $flattenErrors);
        $this->assertContains(trans('validation.language_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.narrative_is_required_when_language_is_populated'), $flattenErrors);
    }

    /**
     * All invalid data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['conditions'] = [
            'condition_attached' => '1',
            'condition' => [
                    [
                        'condition_type' => 'invalid type',
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => 'invalid language',
                            ],
                        ],
                    ],
                ],
        ];

        return $data;
    }
}
