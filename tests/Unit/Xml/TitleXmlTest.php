<?php

namespace Tests\Unit\Xml;

/**
 * Class TitleXmlTest.
 */
class TitleXmlTest extends XmlBaseTest
{
    /**
     * Pass if all valid data.
     *
     * @return void
     * @test
     */
    public function pass_if_all_valid_data(): void
    {
        $rows = $this->valid_data();
        $flattenErrors = $this->getErrors($rows);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * All valid data.
     *
     * @return array
     */
    public function valid_data(): array
    {
        return $this->completeXml;
    }

    /**
     * Throws validation messages for all invalid data.
     *
     * @test
     * @return void
     */
    public function throw_validation_if_invalid_data(): void
    {
        $rows = $this->invalid_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.first_title_required'), $flattenErrors);
        $this->assertContains(trans('validation.narrative_language_unique'), $flattenErrors);
        $this->assertContains(trans('validation.narrative_is_required_when_language_is_populated'), $flattenErrors);
    }

    /**
     * Invalid data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->valid_data();
        $data[0]['title'] = [
            [
                'narrative' => null,
                'language' => null,
            ],
            [
                'narrative' => null,
                'language' => null,
            ],
            [
                'narrative' => null,
                'language' => 'en',
            ],
        ];

        return $data;
    }
}
