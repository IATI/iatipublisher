<?php

declare(strict_types=1);

namespace App\XmlImporter\Foundation\Mapper\Components\Elements;

use App\XmlImporter\Foundation\Support\Helpers\Traits\XmlHelper;
use Illuminate\Support\Arr;

/**
 * Class Result.
 */
class Result
{
    use XmlHelper;

    /**
     * @var
     */
    protected $result;
    /**
     * @var
     */
    protected $indicators;

    /**
     * @var array
     */
    protected array $documentLinkTemplate;

    /**
     * @param array $results
     * @param       $template
     *
     * @return mixed
     */
    public function map(array $results, $template): mixed
    {
        $this->documentLinkTemplate = $template['result']['document_link'];

        foreach ($results as $index => $result) {
            $value = Arr::get($result, 'value', []) ?? [];
            $this->result[$index] = $template['result'];
            $this->result[$index]['type'] = $this->attributes($result, 'type');
            $this->result[$index]['aggregation_status'] = $this->setBooleanField($this->attributes($result, 'aggregation-status'));
            $this->result[$index]['title'][0]['narrative'] = $this->value($value, 'title');
            $this->result[$index]['description'][0]['narrative'] = $this->value($value, 'description');
            $this->result[$index]['document_link'] = $this->documentLink($result, $index);
            $this->result[$index]['reference'] = $this->resultReference($value, [0 => $template['result']]);
            $this->result[$index]['indicator'] = $this->indicator($result, $index);
        }

        return $this->result;
    }

    /**
     * Returns value for boolean field.
     *
     * @param $value
     *
     * @return string
     */
    public function setBooleanField($value): string
    {
        if ($value) {
            if ((strtolower($value) === 'yes') || (strtolower($value) === 'true') || $value === true || $value === '1') {
                return '1';
            } elseif ((strtolower($value) === 'no') || (strtolower($value) === 'false') || $value === false || $value === '0') {
                return '0';
            }
        }

        return '';
    }

    /**
     * @param $result
     * @param $index
     * @return array
     */
    protected function indicator($result, $index): array
    {
        $indicatorAttributes = $this->filterAttributes(Arr::get($result, 'value', []), 'indicator', ['measure', 'ascending', 'aggregation-status']);
        $indicators = $this->filterValues(Arr::get($result, 'value', []), 'indicator');
        $indicatorTemplate = Arr::get($this->result[$index], 'indicator', []);
        $indicatorData = [Arr::get($indicatorTemplate, 0, [])];

        if (($indicators && !empty($indicators)) || implode('', Arr::flatten($indicatorAttributes)) !== '') {
            foreach ($indicators as $key => $indicator) {
                $indicator = $indicator['indicator'];
                $indicatorData[$key]['measure'] = $indicatorAttributes[$key]['measure'];
                $indicatorData[$key]['ascending'] = $this->setBooleanField(Arr::get($indicatorAttributes, "$key.ascending"));
                $indicatorData[$key]['aggregation_status'] = $this->setBooleanField(Arr::get($indicatorAttributes[$key], 'aggregation-status', ''));

                if (!empty($indicator) && $indicator !== '') {
                    $indicatorData[$key]['title'][0]['narrative'] = $this->value($indicator, 'title');
                    $indicatorData[$key]['description'][0]['narrative'] = $this->value($indicator, 'description');
                    $indicatorData[$key]['reference'] = $this->reference($indicator, $indicatorTemplate);
                    $indicatorData[$key]['baseline'] = $this->baseline($indicator, $indicatorTemplate, $index);
                    $indicatorData[$key]['document_link'] = $this->documentLink(['value' => $indicator], $index);
                    $indicatorData[$key]['period'] = $this->period($indicator, $indicatorTemplate, $index);
                }

                if (isset($indicatorData[$key]) && $this->checkEmptyData($indicatorData[$key])) {
                    unset($indicatorData[$key]);
                }
            }

            return $indicatorData;
        }

        return [];
    }

    /**
     * @param $indicator
     * @param $indicatorTemplate
     * @return array
     */
    protected function reference($indicator, $indicatorTemplate): array
    {
        $references = $this->filterAttributes($indicator, 'reference', ['vocabulary', 'code', 'indicator_uri']);
        $referenceData = Arr::get($indicatorTemplate, '0.reference');

        foreach ($references as $referenceIndex => $reference) {
            $referenceData[$referenceIndex] = $reference;
        }

        return $referenceData;
    }

    /**
     * @param $result
     * @param $resultTemplate
     * @return array
     */
    protected function resultReference($result, $resultTemplate): array
    {
        $references = $this->filterAttributes($result, 'reference', ['vocabulary', 'code', 'vocabulary-uri']);
        $referenceData = Arr::get($resultTemplate, '0.reference');

        foreach ($references as $referenceIndex => $reference) {
            $vocabulary = Arr::get($reference, 'vocabulary', '');
            $code = Arr::get($reference, 'code', '');
            $vocabulary_uri = Arr::get($reference, 'vocabulary-uri', '');
            $referenceData[$referenceIndex]['vocabulary'] = $vocabulary;
            $referenceData[$referenceIndex]['code'] = $code;
            $referenceData[$referenceIndex]['vocabulary_uri'] = $vocabulary_uri;
        }

        return $referenceData;
    }

    /**
     * @param $indicator
     * @param $indicatorTemplate
     * @param $index
     *
     * @return array
     */
    protected function baseline($indicator, $indicatorTemplate, $index): array
    {
        $baseline = Arr::get($indicatorTemplate, '0.baseline');
        $baselineAttributes = $this->filterAttributes($indicator, 'baseline', ['year', 'iso-date', 'value']);

        if (count($baselineAttributes)) {
            foreach ($baselineAttributes as $key => $baselineAttribute) {
                $baseline[$key] = Arr::get($indicatorTemplate, '0.baseline.0');
                $baseline[$key]['year'] = Arr::get($baselineAttribute, 'year', '');
                $baseline[$key]['date'] = Arr::get($baselineAttribute, 'iso-date', '');
                $baseline[$key]['value'] = Arr::get($baselineAttribute, 'value', '');
            }
        }

        $baselines = $this->filterValues($indicator, 'baseline');

        if (count($baselines)) {
            foreach ($baselines as $baselineKey => $baselineValue) {
                $baselineValues = Arr::get($baselineValue, 'baseline') ?? [];
                $baseline[$baselineKey]['comment'] = $this->comment($baselineValues, Arr::get($indicatorTemplate, '0.baseline'));
                $baseline[$baselineKey]['document_link'] = $this->documentLink(['value' => $baselineValues], $index);
                $baseline[$baselineKey]['location'] = $this->location($baselineValues, Arr::get($indicatorTemplate, '0.baseline'));
                $baseline[$baselineKey]['dimension'] = $this->dimension($baselineValues, Arr::get($indicatorTemplate, '0.baseline'));
            }
        }

        return $baseline;
    }

    /**
     * @param $indicator
     * @param $indicatorTemplate
     * @param $index
     *
     * @return array
     */
    protected function period($indicator, $indicatorTemplate, $index): array
    {
        $periods = $this->filterValues($indicator, 'period');
        $periodsData = $periodsTemplate = Arr::get($indicatorTemplate, '0.period');

        if ($periods && !empty($periods)) {
            foreach ($periods as $key => $period) {
                $period = Arr::get($period, 'period', []);

                if (!empty($period)) {
                    $periodsData[$key]['period_start'][0]['date'] = Arr::get($this->filterAttributes($period, 'periodStart', ['iso-date']), '0.iso-date', '');
                    $periodsData[$key]['period_end'][0]['date'] = Arr::get($this->filterAttributes($period, 'periodEnd', ['iso-date']), '0.iso-date', '');
                    $periodsData[$key]['target'] = $this->target($period, $periodsTemplate, $index);
                    $periodsData[$key]['actual'] = $this->actual($period, $periodsTemplate, $index);
                }

                if (isset($periodsData[$key]) && $this->checkEmptyData($periodsData[$key])) {
                    unset($periodsData[$key]);
                }
            }

            return $periodsData;
        }

        return [];
    }

    /**
     * Checks if the period, indicator or result is empty.
     *
     * @param $elementData
     *
     * @return bool
     */
    public function checkEmptyData($elementData): bool
    {
        $elementValues = Arr::flatten($elementData);

        foreach ($elementValues as $value) {
            if ($value && $value != '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $period
     * @param $periodTemplate
     * @param $index
     *
     * @return array
     */
    protected function target($period, $periodTemplate, $index): array
    {
        $targetData = Arr::get($periodTemplate, '0.target');
        $targetDataAttributes = $this->filterAttributes($period, 'target', ['value']);

        if (count($targetDataAttributes)) {
            foreach ($targetDataAttributes as $key => $targetDataAttribute) {
                $targetData[$key] = Arr::get($periodTemplate, '0.target.0');
                $targetData[$key]['value'] = Arr::get($targetDataAttribute, 'value', '');
            }
        }

        $periodTarget = $this->filterValues($period, 'target');

        if (count($periodTarget)) {
            foreach ($periodTarget as $targetKey => $targetValue) {
                $currentTarget = Arr::get($targetValue, 'target', []);
                $targetData[$targetKey]['location'] = $this->location($currentTarget, Arr::get($periodTemplate, '0.target'));
                $targetData[$targetKey]['dimension'] = $this->dimension($currentTarget, Arr::get($periodTemplate, '0.target'));
                $targetData[$targetKey]['comment'] = $this->comment($currentTarget, Arr::get($periodTemplate, '0.target'));
                $targetData[$targetKey]['document_link'] = $this->documentLink(['value' => $currentTarget], $index);
            }
        }

        return $targetData;
    }

    /**
     * @param $period
     * @param $periodTemplate
     * @param $index
     *
     * @return array
     */
    protected function actual($period, $periodTemplate, $index): array
    {
        $actualData = Arr::get($periodTemplate, '0.actual');
        $actualDataAttributes = $this->filterAttributes($period, 'actual', ['value']);

        if (count($actualDataAttributes)) {
            foreach ($actualDataAttributes as $key => $actualDataAttribute) {
                $actualData[$key] = Arr::get($periodTemplate, '0.actual.0');
                $actualData[$key]['value'] = Arr::get($actualDataAttribute, 'value', '');
            }
        }

        $periodActual = $this->filterValues($period, 'actual');

        if (count($periodActual)) {
            foreach ($periodActual as $actualKey => $actualValue) {
                $currentActual = Arr::get($actualValue, 'actual', []);
                $actualData[$actualKey]['location'] = $this->location($currentActual, Arr::get($periodTemplate, '0.actual'));
                $actualData[$actualKey]['dimension'] = $this->dimension($currentActual, Arr::get($periodTemplate, '0.actual'));
                $actualData[$actualKey]['comment'] = $this->comment($currentActual, Arr::get($periodTemplate, '0.actual'));
                $actualData[$actualKey]['document_link'] = $this->documentLink(['value' => $currentActual], $index);
            }
        }

        return $actualData;
    }

    /**
     * @param $data
     * @param $template
     *
     * @return array
     */
    protected function location($data, $template): array
    {
        $locationData = Arr::get($template, '0.location', []);
        $locations = $this->filterAttributes($data, 'location', ['ref']);

        foreach ($locations as $index => $location) {
            $locationData[$index]['reference'] = Arr::get($location, 'ref');
        }

        return $locationData;
    }

    /**
     * @param $data
     * @param $template
     *
     * @return array
     */
    protected function dimension($data, $template): array
    {
        $dimensionData = Arr::get($template, '0.dimension', []);
        $dimensions = $this->filterAttributes($data, 'dimension', ['name', 'value']);

        foreach ($dimensions as $index => $dimension) {
            $dimensionData[$index]['name'] = Arr::get($dimension, 'name');
            $dimensionData[$index]['value'] = Arr::get($dimension, 'value');
        }

        return $dimensionData;
    }

    /**
     * @param $data
     * @param $template
     *
     * @return array
     */
    protected function comment($data, $template): array
    {
        $commentData = Arr::get($template, '0.comment', []);
        $comments = Arr::get($this->filterValues($data, 'comment'), '0.comment', []);

        if (!empty($comments)) {
            foreach ($comments as $index => $comment) {
                $narrativeComment = Arr::get($this->narrative($comment), '0');
                $narrativeComment['language'] = strtolower($narrativeComment['language']);
                $commentData[0]['narrative'][$index] = $narrativeComment;
            }
        }

        return $commentData;
    }

    /**
     * @param $element
     * @param $index
     *
     * @return array
     */
    public function documentLink($element, $index): array
    {
        $documentLinkAttributes = $this->filterAttributes(Arr::get($element, 'value', []), 'documentLink', ['format', 'url']);
        $documentLinks = $this->filterValues(Arr::get($element, 'value', []), 'documentLink');
        $documentLinkData = [Arr::get($this->documentLinkTemplate, 0, [])];

        foreach ($documentLinks as $key => $document) {
            $document = $document['documentLink'];

            if (!empty($document)) {
                $documentLinkData[$key]['url'] = $documentLinkAttributes[$key]['url'];
                $documentLinkData[$key]['format'] = $documentLinkAttributes[$key]['format'];
                $documentLinkData[$key]['title'][0]['narrative'] = (($title = $this->value($document, 'title')) === '') ? $this->emptyNarrative : $title;
                $documentLinkData[$key]['description'][0]['narrative'] = (($title = $this->value($document, 'description')) === '') ? $this->emptyNarrative : $title;
                $documentLinkData[$key]['category'] = $this->filterAttributes($document, 'category', ['code']);

                foreach ($this->filterAttributes($document, 'language', ['code']) as $language_index => $language) {
                    $documentLinkData[$key]['language'][$language_index]['language'] = $language['code'];
                }

                $documentLinkData[$key]['document_date'][0]['date'] = dateFormat('Y-m-d', $this->attributes(['value' => $document], 'iso-date', 'documentDate'));
            }
        }

        return $documentLinkData;
    }
}
