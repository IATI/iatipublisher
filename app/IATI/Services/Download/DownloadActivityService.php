<?php

declare(strict_types=1);

namespace App\IATI\Services\Download;

use App\CsvImporter\Traits\ChecksCsvHeaders;
use App\IATI\Elements\Xml\XmlGenerator;
use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Traits\DownloadActivityComplexElementTrait;
use App\IATI\Traits\DownloadActivitySimpleElementTrait;
use App\IATI\Traits\DownloadTransactionTrait;
use Illuminate\Support\Arr;

/**
 * Class DownloadActivityService.
 */
class DownloadActivityService
{
    use ChecksCsvHeaders;
    use DownloadActivityComplexElementTrait;
    use DownloadActivitySimpleElementTrait;
    use DownloadTransactionTrait;

    /**
     * @var array
     */
    protected array $multipleElements = [
        'other_identifier',
        'title',
        'description',
        'activity_date',
        'contact_info',
        'participating_org',
        'recipient_country',
        'recipient_region',
        'location',
        'sector',
        'humanitarian_scope',
        'policy_marker',
        'default_aid_type',
        'budget',
        'planned_disbursement',
        'document_link',
        'related_activity',
        'legacy_data',
        'tag',
        'transactions',
    ];

    /**
     * @var array
     */
    protected array $insertedDates = [];

    /**
     * DownloadActivityService Constructor.
     *
     * @param ActivityRepository $activityRepository
     * @param XmlGenerator $xmlGenerator
     */
    public function __construct(
        protected ActivityRepository $activityRepository,
        protected XmlGenerator $xmlGenerator
    ) {
    }

    /**
     * Returns activities having given ids for downloading.
     *
     * @param $activityIds
     *
     * @return object
     */
    public function getActivitiesToDownload($activityIds): object
    {
        return $this->activityRepository->getActivitiesToDownload($activityIds);
    }

    /**
     * Returns activities Query having given ids for downloading.
     *
     * @param $activityIds
     * @param $authUser
     *
     * @return object
     */
    public function getActivitiesQueryToDownload($activityIds, $authUser): object
    {
        return $this->activityRepository->getActivitiesQueryToDownload($activityIds, $authUser);
    }

    /**
     * Returns formatted csv data for downloading.
     *
     * @param $activities
     *
     * @return array
     */
    public function getCsvData($activities): array
    {
        $data = [];
        foreach ($activities as $activity) {
            $activityArrayData = $this->getActivityArrayData($activity->toArray());

            if (count($activityArrayData)) {
                foreach ($activityArrayData as $arrayData) {
                    $data[] = $arrayData;
                }
            }
        }

        return $data;
    }

    /**
     * Returns combined xml for download.
     *
     * @param $activities
     *
     * @return string
     */
    public function getCombinedXmlFile($activities): string
    {
        return $this->xmlGenerator->getCombinedXmlFile($activities);
    }

    public function streamCombinedXmlFile($activities): string
    {
        return $this->xmlGenerator->streamCombinedXmlFile($activities);
    }

    /**
     * Get organization publisher id.
     *
     * @return string
     */
    public function getOrganizationPublisherId(): string
    {
        $organization = auth()->user()->organization;

        return $organization->publisher_id;
    }

    /**
     * Returns required data in array format.
     *
     * @param $activityArray
     *
     * @return array
     */
    public function getActivityArrayData($activityArray): array
    {
        $data = [];
        $count = $this->getElementCount($activityArray);
        $headers = $this->getCsvHeaderArray('Activity', 'other_fields_transaction');

        for ($i = 0; $i < $count; $i++) {
            foreach ($headers as $header) {
                $function = 'get' . str_replace([' ', '(', ')'], '', $header);
                $data[$i][$header] = $this->$function($activityArray, $i);
            }
        }

        return $this->removeEmptyData($data);
    }

    /**
     * Returns narrative text of the default language.
     *
     * @param $narratives
     * @param $language
     *
     * @return string|null
     */
    public function getNarrativeText($narratives, $language): ?string
    {
        if (!empty($narratives) && is_array($narratives) && count($narratives)) {
            foreach ($narratives as $narrative) {
                if (Arr::get($narrative, 'language', '') === $language || Arr::get($narrative, 'language', '') === '') {
                    return (string) Arr::get($narrative, 'narrative', '');
                }
            }
        }

        return '';
    }

    /**
     * Returns activity description narrative for particular description type.
     *
     * UNDERSTANDING THE : || $type === '1'
     *  That is to handle data where description narrative is present but no type is specified.
     *  That type of description will be default-ed to type 1 in the downloaded file.
     *
     *
     * @param $descriptions
     * @param $language
     * @param $type
     *
     * @return string|null
     */
    public function getDescriptionText($descriptions, $language, $type): ?string
    {
        if (!empty($descriptions) && is_array($descriptions) && count($descriptions)) {
            foreach ($descriptions as $description) {
                if (Arr::get($description, 'type', $type) === $type || $type === '1') {
                    return (string) $this->getNarrativeText(Arr::get($description, 'narrative', []), $language);
                }
            }
        }

        return '';
    }

    /**
     * Returns activity date according to the type specified.
     *
     * @param $activityDates
     * @param $type
     * @param $activityIdentifier
     *
     * @return string|null
     */
    public function getActivityDate($activityDates, $type, $activityIdentifier): ?string
    {
        if (!isset($this->insertedDates[$activityIdentifier])) {
            $this->insertedDates[$activityIdentifier] = [];
        }

        if (!empty($activityDates) && is_array($activityDates) && count($activityDates)) {
            foreach ($activityDates as $key => $activityDate) {
                if (Arr::get($activityDate, 'type', '') === $type && !in_array($key, $this->insertedDates[$activityIdentifier], true)) {
                    $this->insertedDates[$activityIdentifier][] = $key;

                    return (string) Arr::get($activityDate, 'date', '');
                }
            }
        }

        return '';
    }

    /**
     * Returns recipient region code based on the vocabulary.
     *
     * @param $regionVocabulary
     * @param $recipientRegion
     *
     * @return string|null
     */
    public function getRecipientRegionCodeFromVocabulary($regionVocabulary, $recipientRegion): ?string
    {
        if (!empty($regionVocabulary) && $regionVocabulary !== '1') {
            return (string) (Arr::get($recipientRegion, 'custom_code', ''));
        }

        return (string) (Arr::get($recipientRegion, 'region_code', ''));
    }

    /**
     * Returns sector code based on vocabulary.
     *
     * @param $sectorVocabulary
     * @param $sector
     *
     * @return string|null
     */
    public function getSectorCodeFromVocabulary($sectorVocabulary, $sector): ?string
    {
        if (!empty($sectorVocabulary)) {
            return match ($sectorVocabulary) {
                '1' => (string) Arr::get($sector, 'code', ''),
                '2' => (string) Arr::get($sector, 'category_code', ''),
                '7' => (string) Arr::get($sector, 'sdg_goal', ''),
                '8' => (string) Arr::get($sector, 'sdg_target', ''),
                default => (string) Arr::get($sector, 'text', ''),
            };
        }

        return (string) Arr::get($sector, 'text', '');
    }

    /**
     * Returns policy marker code based on the vocabulary.
     *
     * @param $policyMarkerVocabulary
     * @param $policyMarker
     *
     * @return null|string
     */
    public function getPolicyMarkerCodeFromVocabulary($policyMarkerVocabulary, $policyMarker): ?string
    {
        if ($policyMarkerVocabulary === '1') {
            return (string) Arr::get($policyMarker, 'policy_marker', '');
        }

        return Arr::get($policyMarker, 'policy_marker_text', '');
    }

    /**
     * Return mailing address narrative text.
     *
     * @param $mailingAddresses
     * @param $language
     *
     * @return string|null
     */
    public function getMailingAddressText($mailingAddresses, $language): ?string
    {
        if (!empty($mailingAddresses) && is_array($mailingAddresses) && count($mailingAddresses)) {
            foreach ($mailingAddresses as $mailingAddress) {
                $narrative = $this->getNarrativeText(Arr::get($mailingAddress, 'narrative', []), $language);

                if (!empty($narrative)) {
                    return $narrative;
                }
            }
        }

        return '';
    }

    /**
     * Returns tag code based on the vocabulary.
     *
     * @param $tagVocabulary
     * @param $tag
     *
     * @return null|string
     */
    public function getTagCodeFromVocabulary($tagVocabulary, $tag): ?string
    {
        if (!empty($tagVocabulary)) {
            return match ($tagVocabulary) {
                '2' => (string) Arr::get($tag, 'goals_tag_code', ''),
                '3' => (string) Arr::get($tag, 'targets_tag_code', ''),
                default => (string) Arr::get($tag, 'tag_text', ''),
            };
        }

        return (string) Arr::get($tag, 'tag_text', '');
    }

    /**
     * Returns default aid type code based on the vocabulary.
     *
     * @param $aidTypeVocabulary
     * @param $aidType
     *
     * @return null|string
     */
    public function getDefaultAidTypeCodeFromVocabulary($aidTypeVocabulary, $aidType): ?string
    {
        if (!empty($aidTypeVocabulary)) {
            return match ($aidTypeVocabulary) {
                '2' => (string) Arr::get($aidType, 'earmarking_category', ''),
                '3' => (string) Arr::get($aidType, 'earmarking_modality', ''),
                '4' => (string) Arr::get($aidType, 'cash_and_voucher_modalities', ''),
                default => (string) Arr::get($aidType, 'default_aid_type', ''),
            };
        }

        return (string) Arr::get($aidType, 'default_aid_type', '');
    }

    /**
     * Removes empty data.
     *
     * @param $data
     *
     * @return array|null
     */
    public function removeEmptyData($data): ?array
    {
        if (is_array($data) && !empty($data)) {
            foreach ($data as $key => $datum) {
                if ($this->isEmpty($datum)) {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }

    /**
     * Checks if data is empty.
     *
     * @param $array
     *
     * @return bool
     */
    public function isEmpty($array): bool
    {
        if (is_array($array) && !empty($array)) {
            foreach ($array as $data) {
                if (!empty($data)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns count of the highest no of repeated element.
     *
     * @param $activityArray
     *
     * @return int
     */
    public function getElementCount($activityArray): int
    {
        $count = 1;

        if (is_array($activityArray) && !empty($activityArray)) {
            foreach ($activityArray as $key => $arrayItem) {
                if (is_array($arrayItem) && in_array($key, $this->multipleElements, true) && count($arrayItem) > $count) {
                    $count = count($arrayItem);
                } elseif ($key === 'conditions' && count(Arr::get($activityArray, 'conditions.condition', [])) > $count) {
                    $count = count(Arr::get($activityArray, 'conditions.condition', []));
                } elseif ($key === 'country_budget_items' && count(Arr::get($activityArray, 'country_budget_items.budget_item', [])) > $count) {
                    $count = count(Arr::get($activityArray, 'country_budget_items.budget_item', []));
                }
            }
        }

        return $count;
    }

    /**
     * Returns all activities of an organization.
     *
     * @param $queryParams
     *
     * @return object
     */
    public function getAllActivitiesToDownload($queryParams): object
    {
        return $this->activityRepository->getAllActivitiesToDownload(auth()->user()->organization_id, $queryParams);
    }

    /**
     * Returns all activities Query of an organization.
     *
     * @param $queryParams
     * @param $authUser
     *
     * @return object
     */
    public function getAllActivitiesQueryToDownload($queryParams, $authUser): object
    {
        return $this->activityRepository->getAllActivitiesQueryToDownload($authUser['organization_id'], $queryParams);
    }

    /**
     * Returns the first narrative text of the first description that matches given description type.
     *
     * @param array  $descriptions
     * @param string $type
     *
     * @return string
     */
    public function getFirstDescriptionTextThatMatchesType(array $descriptions, string $type = '1'): string
    {
        foreach ($descriptions as $description) {
            if ($description['type'] === $type) {
                return Arr::get($description, 'narrative.0.narrative') ?? '';
            }
        }

        return '';
    }
}
