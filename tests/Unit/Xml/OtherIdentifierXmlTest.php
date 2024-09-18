<?php

namespace Tests\Unit\Xml;

/**
 * Class OtherIdentifierXmlTest.
 */
class OtherIdentifierXmlTest extends XmlBaseTest
{
    /**
     * Throws validation messages for all invalid data.
     *
     * @return void
     * @test
     */
    public function check_if_throws_validation_when_invalid_data(): void
    {
        $rows = $this->invalid_other_identifier_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.reference_should_not_contain_symbol'), $flattenErrors);
        $this->assertContains(trans('validation.reference_should_not_contain_symbol'), $flattenErrors);
        $this->assertContains(trans('validation.language_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.narrative_is_required_when_language_is_populated'), $flattenErrors);
    }

    /**
     * Invalid identifier data.
     *
     * @return array
     */
    public function invalid_other_identifier_data(): array
    {
        $data = $this->completeXml;
        $data[0]['other_identifier'] = [
            [
                'reference' => '/\ssss',
                'reference_type' => 'Invalid type',
                'owner_org' => [
                    [
                        'ref' => '//\rrrrr',
                        'narrative' => [
                            [
                                'narrative' => 'narrative one',
                                'language' => 'invalid language',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'reference' => '/\ssss',
                'reference_type' => 'Invalid type',
                'owner_org' => [
                    [
                        'ref' => '//\rrrrr',
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => 'en',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $data;
    }
}
