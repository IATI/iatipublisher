<?php

namespace Tests\Unit\Xml;

/**
 * Class DescriptionXmlTest.
 */
class DescriptionXmlTest extends XmlBaseTest
{
    /**
     * Throws validation messages for all invalid data.
     *
     * @return void
     * @test
     */
    public function throw_validation_for_invalid_data(): void
    {
        $rows = $this->invalid_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.description_type_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.language_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.narrative_is_required_when_language_is_populated'), $flattenErrors);
    }

    /**
     * Invalid description data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['description'] = [
             [
                 'type' => 'invalid type',
                 'narrative' => [
                     [
                         'narrative' => '',
                         'language' => 'easdfn',
                     ],
                 ],
             ],
        ];

        return $data;
    }
}
