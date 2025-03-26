<?php

namespace Tests\Unit\Xml;

/**
 * Class ParticipatingOrgXmlTest.
 */
class ParticipatingOrgXmlTest extends XmlBaseTest
{
    /**
     * Throws validation messages for all invalid data.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \JsonException
     * @test
     */
    public function throw_validation_if_invalid_data(): void
    {
        $rows = $this->invalid_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.activity_participating_org.invalid_identifier'), $flattenErrors);
        $this->assertContains(trans('validation.activity_participating_org.invalid_role'), $flattenErrors);
        $this->assertContains(trans('validation.organisation_type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_participating_org.invalid_crs_channel_code'), $flattenErrors);
        $this->assertContains(trans('validation.language_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.narrative_is_required_when_language_is_populated'), $flattenErrors);
    }

    /**
     * Invalid participating org.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['participating_org'] = [
            [
                'organization_role' => 'invalid role',
                'ref' => '123',
                'type' => 'invalid type',
                'identifier' => '/\*&&^',
                'crs_channel_code' => 'invalid',
                'narrative' => [
                    [
                        'narrative' => '',
                        'language' => 'invalid language',
                    ],
                ],
            ],
        ];

        return $data;
    }
}
