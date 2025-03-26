<?php

namespace Tests\Unit\Xml;

/**
 * Class DocumentLinkXmlTest.
 */
class DocumentLinkXmlTest extends XmlBaseTest
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

        $this->assertContains(trans('validation.document_link_format_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.url_valid'), $flattenErrors);
        $this->assertContains(trans('validation.this_must_be_a_valid_date'), $flattenErrors);
        $this->assertContains(trans('validation.date_must_be_after_1900'), $flattenErrors);
        $this->assertContains(trans('validation.document_link_category_unique'), $flattenErrors);
        $this->assertContains(trans('validation.document_link_category_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.document_link_language_unique'), $flattenErrors);
        $this->assertContains(trans('validation.language_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.language_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.narrative_is_required_when_language_is_populated'), $flattenErrors);
    }

    /**
     * Invalid document link.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['document_link'] = [
            [
                 'url' => 'invalid url',
                 'format' => 'invalid format',
                 'title' => [
                     [
                         'narrative' => [
                             [
                                 'narrative' => '',
                                 'language' => 'invalid language',
                             ],
                         ],
                     ],
                 ],
                 'category' => [
                     [
                         'code' => 'invalid code',
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
                 'language' => [
                     [
                         'code' => 'en',
                     ],
                     [
                         'code' => 'en',
                     ],
                     [
                         'code' => 'invalid',
                     ],
                 ],
                 'document_date' => [
                     [
                         'date' => '1200-02-02',
                     ],
                 ],
             ],
            [
                'url' => 'https://www.google.com',
                'format' => 'application/1d-interleaved-parityfec',
                'title' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => 'narrative one',
                                'language' => 'en',
                            ],
                        ],
                    ],
                ],
                'category' => [
                    [
                        'code' => 'A05',
                    ],
                    [
                        'code' => 'A05',
                    ],
                ],
                'description' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => 'narrative one',
                                'language' => 'en',
                            ],
                        ],
                    ],
                ],
                'language' => [
                    [
                        'code' => 'invalid langugae',
                    ],
                ],
                'document_date' => [
                    [
                        'date' => 'invalid',
                    ],
                ],
            ],
        ];
        $data[1]['document_link'] = [
            [
                'url' => 'https://www.google.com',
                'format' => 'application/1d-interleaved-parityfec',
                'title' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => 'narrative one',
                                'language' => 'en',
                            ],
                        ],
                    ],
                ],
                'category' => [
                    [
                        'code' => 'A05',
                    ],
                    [
                        'code' => 'A05',
                    ],
                ],
                'description' => [
                    [
                        'narrative' => [
                            [
                                'narrative' => 'narrative one',
                                'language' => 'en',
                            ],
                        ],
                    ],
                ],
                'language' => [
                    [
                        'language' => 'invalid langugae',
                    ],
                ],
                'document_date' => [
                    [
                        'date' => 'invalid',
                    ],
                ],
            ],
        ];

        return $data;
    }
}
