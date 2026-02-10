<?php

declare(strict_types=1);

namespace App\IATI\Traits;

use Illuminate\Support\Arr;

/**
 * Class XmlBaseElement.
 */
trait XmlBaseElement
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
            $narrativeData = iterator_to_array($this->getNarrative($narratives));
            // foreach ($narratives as $narrative) {
            //     if ($narrative != '') {
            //         $narrativeData[] = [
            //             '@value' => Arr::get($narrative, 'narrative', null),
            //             '@attributes' => [
            //                 'xml:lang' => Arr::get($narrative, 'language', null),
            //             ],
            //         ];
            //     }
            // }
        }

        return $narrativeData;
    }

    protected function getNarrative($narratives)
    {
        foreach ($narratives as $narrative) {
            // if ($narrative != '') {
            $narrativeData = [
                '@value' => $this->sanitizeXml(Arr::get($narrative, 'narrative', null)),
                '@attributes' => [
                    'xml:lang' => Arr::get($narrative, 'language', null),
                ],
            ];

            yield $narrativeData;
            // }
        }
    }

    /**
     * Remove invalid XML characters.
     *
     * @param string|null $text
     * @return string|null
     */
    protected function sanitizeXml(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        // Remove control characters (0-31), except for tab (9), newline (10), and carriage return (13)
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
    }

    protected function category($categories)
    {
        foreach ($categories as $value) {
            $category = [
                '@attributes' => ['code' => Arr::get($value, 'code', null)],
            ];

            yield $category;
        }
    }

    protected function language($languages)
    {
        foreach ($languages as $value) {
            $language = [
                '@attributes' => ['code' => Arr::get($value, 'language', null)],
            ];

            yield $language;
        }
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
                // $categories = [];

                // foreach (Arr::get($documentLink, 'category', []) as $value) {
                //     $categories[] =[
                //         '@attributes' => ['code' => Arr::get($value, 'code', null)],
                //     ];
                // }

                // $languages = [];

                // foreach (Arr::get($documentLink, 'language', []) as $language) {
                //     $languages[] = [
                //         '@attributes' => ['code' => Arr::get($language, 'language', null)],
                //     ];
                // }

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
                    'category' => iterator_to_array($this->category(Arr::get($documentLink, 'category', []))),
                    'language' => iterator_to_array($this->category(Arr::get($documentLink, 'language', []))),
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
     * @param string $vocabularyUri
     *
     * @return array
     */
    protected function buildReference($references, $uriType, string $vocabularyUri = 'vocabulary_uri'): array
    {
        $referenceData = [];

        if (count($references)) {
            foreach ($references as $reference) {
                $referenceData[] = [
                    '@attributes' => [
                        'vocabulary' => Arr::get($reference, 'vocabulary', null),
                        'code' => Arr::get($reference, 'code', null),
                        $uriType => Arr::get($reference, $vocabularyUri, null),
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
