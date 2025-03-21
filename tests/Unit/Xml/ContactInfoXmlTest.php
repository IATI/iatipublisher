<?php

namespace Tests\Unit\Xml;

/**
 * Class ContactInfoXmlTest.
 */
class ContactInfoXmlTest extends XmlBaseTest
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

        $this->assertContains(trans('validation.type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.contact_info_telephone_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_contact_info.telephone.min'), $flattenErrors);
        $this->assertContains(trans('validation.activity_contact_info.telephone.max'), $flattenErrors);
        $this->assertContains(trans('validation.email_address_format_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.url_valid'), $flattenErrors);
    }

    /**
     * Invalid contact info data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['contact_info'] = [
            [
                'type' => 'invalid',
                'organization' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => 'invalid langugae',
                            ],
                        ],
                    ],
                ],
                'department' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
                'person_name' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
                'job_title' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
                'telephone' => [
                    [
                        'telephone' => 'invalid',
                    ],
                    [
                        'telephone' => '123',
                    ],
                    [
                        'telephone' => '123456789012345678905',
                    ],
                ],
                'email' => [
                    [
                        'email' => 'invalid',
                    ],
                ],
                'website' => [
                    [
                        'website' => 'invalid',
                    ],
                ],
                'mailing_address' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
                'organisation' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => '',
                                'language' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $data;
    }
}
