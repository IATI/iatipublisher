<?php

namespace App\IATI\Traits;

use Illuminate\Support\Arr;

/**
 * Class OrganizationXmlBaseElement.
 */
trait OrganizationXmlBaseElements
{
    /**
     * Build narratives for Elements.
     *
     * @param $narratives
     *
     * @return array
     */
    public function buildNarrative($narratives): array
    {
        $narrativeData = [];

        if ($narratives) {
            foreach ($narratives as $narrative) {
                if ($narrative != '') {
                    $narrativeData[] = [
                        '@value' => Arr::get($narrative, 'narrative', null),
                        '@attributes' => [
                            'xml:lang' => Arr::get($narrative, 'language', null),
                        ],
                    ];
                }
            }
        }

        return $narrativeData;
    }

    //     /**
    //  * Build narratives for Elements.
    //  *
    //  * @param $narratives
    //  *
    //  * @return array
    //  */
    // public function buildValue($values): array
    // {
    //     $valueData = [];

    //     if ($values) {
    //         foreach ($values as $value) {
    //             if ($value != '') {
    //                 $valueData[] = [
    //                     // '@value' => Arr::get($value, 'value', null),
    //                     '@attributes' => [
    //                         'currency' => Arr::get($value, 'currency', null),
    //                         'value_date' => Arr::get($value, 'value_date', null),
    //                         'amount' => Arr::get($value, 'amount', null),
    //                     ],
    //                 ];
    //             }
    //         }
    //     }

    //     return $valueData;
    // }

    // /**
    //  * Build narratives for Elements.
    //  *
    //  * @param $narratives
    //  *
    //  * @return array
    //  */
    // public function buildValue($values): array
    // {
    //     $valueData = [];

    //     if ($values) {
    //         foreach ($values as $value) {
    //             if ($value != '') {
    //                 $valueData[] = [
    //                     // '@value' => Arr::get($value, 'value', null),
    //                     '@attributes' => [
    //                         'currency' => Arr::get($value, 'currency', null),
    //                         'value_date' => Arr::get($value, 'value_date', null),
    //                         'amount' => Arr::get($value, 'amount', null),
    //                     ],
    //                 ];
    //             }
    //         }
    //     }

    //     return $valueData;
    // }

    /**
     * @param $budgetLines
     * @return array
     */
    public function buildBudgetLine($budgetLines)
    {
        $budgetLineData = [];
        foreach ($budgetLines as $budgetLine) {
            $budgetLineData[] = [
                '@attributes' => [
                    'ref' => $budgetLine['ref'],
                ],
                'value' => $this->buildValue($budgetLine['value']),
                'narrative' => $this->buildNarrative($budgetLine['narrative']),
            ];
        }

        return $budgetLineData;
    }

    /**
     * @param $values
     * @return array
     */
    protected function buildValue($values)
    {
        $valueData = [];
        foreach ($values as $value) {
            $valueData[] = [
                '@value' => $value['amount'],
                '@attributes' => [
                    'currency' => $value['currency'],
                    'value-date' => $value['value_date'],
                ],
            ];
        }

        return $valueData;
    }

    /**
     * Returns xml data for document link.
     *
     * @param $documentLinks
     *
     * @return array
     */
    protected function buildDocumentLink($documentLinks): array
    {
        $documentLinkData = [];

        if (count($documentLinks)) {
            foreach ($documentLinks as $documentLink) {
                $categories = [];

                foreach (Arr::get($documentLink, 'category', []) as $value) {
                    $categories[] = [
                        '@attributes' => ['code' => Arr::get($value, 'code', null)],
                    ];
                }

                $languages = [];

                foreach (Arr::get($documentLink, 'language', []) as $language) {
                    $languages[] = [
                        '@attributes' => ['code' => Arr::get($language, 'language', null)],
                    ];
                }

                $documentLinkData[] = [
                    '@attributes' => [
                        'url' => Arr::get($documentLink, 'url', null),
                        'format' => Arr::get($documentLink, 'format', null),
                    ],
                    'title' => [
                        'narrative' => $this->buildNarrative(Arr::get($documentLink, 'title.0.narrative', [])),
                    ],
                    'description' => [
                        'narrative' => $this->buildNarrative(Arr::get($documentLink, 'description.0.narrative', [])),
                    ],
                    'category' => $categories,
                    'language'      => $languages,
                    'document-date' => [
                        '@attributes' => [
                            'iso-date' => Arr::get($documentLink, 'document_date.0.date', null),
                        ],
                    ],
                ];
            }
        }

        return $documentLinkData;
    }

    /**
     * Returns xml data for reference.
     *
     * @param $references
     * @param $uriType
     *
     * @return array
     */
    protected function buildReference($references, $uriType): array
    {
        $referenceData = [];

        if (count($references)) {
            foreach ($references as $reference) {
                $referenceData[] = [
                    '@attributes' => [
                        'vocabulary' => Arr::get($reference, 'vocabulary', null),
                        'code' => Arr::get($reference, 'code', null),
                        $uriType => Arr::get($reference, 'vocabulary_uri', null),
                    ],
                ];
            }
        }

        return $referenceData;
    }

    /**
     * Returns xml data of location.
     *
     * @param $locations
     *
     * @return array
     */
    protected function buildLocation($locations): array
    {
        $locationData = [];

        if (count($locations)) {
            foreach ($locations as $location) {
                $locationData[] = [
                    '@attributes' => [
                        'ref' => Arr::get($location, 'reference'),
                    ],
                ];
            }
        }

        return $locationData;
    }

    /**
     * Returns xml data for dimension.
     *
     * @param $dimensions
     *
     * @return array
     */
    protected function buildDimension($dimensions, $measure = null): array
    {
        $dimensionData = [];

        if (count($dimensions)) {
            foreach ($dimensions as $dimension) {
                $dimensionValue = null;

                if ($measure != 5) {
                    $dimensionValue = Arr::get($dimension, 'value', null);
                }

                $dimensionData[] = [
                    '@attributes' => [
                        'name' => Arr::get($dimension, 'name', null),
                        'value' => $dimensionValue,
                    ],
                ];
            }
        }

        return $dimensionData;
    }
}
