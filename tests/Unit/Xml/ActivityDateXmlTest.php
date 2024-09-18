<?php

namespace Tests\Unit\Xml;

/**
 * Class ActivityDateXmlTest.
 */
class ActivityDateXmlTest extends XmlBaseTest
{
    /**
     * Throw validation for all invalid data.
     *
     * @return void
     * @test
     */
    public function throw_all_validation_if_invalid_data(): void
    {
        $rows = $this->invalid_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.date_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_date.date_before'), $flattenErrors);
        $this->assertContains(trans('validation.activity_date.end_later_than_start'), $flattenErrors);
        $this->assertContains(trans('validation.language_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.narrative_is_required_when_language_is_populated'), $flattenErrors);
        $this->assertContains(trans('validation.type_is_invalid'), $flattenErrors);
    }

    /**
     * All invalid data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['activity_date'] = [
            [
                 'date' => 'invalid date',
                 'type' => 'invalid type',
                 'narrative' => [
                     [
                         'narrative' => '',
                         'language' => 'invalid language',
                     ],
                 ],
             ],
            [
                'date' => '4000-01-01',
                'type' => '2',
                'narrative' => [
                    [
                        'narrative' => 'narrative one',
                        'language' => 'en',
                    ],
                ],
            ],
            [
                'date' => '2000-01-01',
                'type' => '4',
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
