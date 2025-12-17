<?php

declare(strict_types=1);

namespace App\IATI\Services;

use App\IATI\Traits\ElementCompleteServiceTrait;
use Illuminate\Support\Arr;

/**
 * Class ElementCompleteService.
 */
class OrganizationElementCompleteService
{
    use ElementCompleteServiceTrait;
    /**
     * Public variable element.
     *
     * @var string
     */
    public string $element = '';

    /**
     * @var string
     */
    public $tempNarrative = '';

    /**
     * @var string
     */
    public $tempAmount = '';

    /**
     * Checks if attribute is complete.
     *
     * @param $mandatoryAttributes
     * @param $data
     *
     * @return bool
     * @throws \JsonException
     */
    public function isAttributeDataCompleted($mandatoryAttributes, $data): bool
    {
        if (empty($mandatoryAttributes)) {
            return true;
        }

        if (empty($data)) {
            return false;
        }

        $organizationSchema = getOrganizationElementSchema($this->element);

        return $this->checkAttributeDataStatus($mandatoryAttributes, $data, $organizationSchema);
    }

    /**
     * Checks if single dimension attribute is complete.
     *
     * @param $data
     *
     * @return bool
     * @throws \JsonException
     */
    public function singleDimensionAttributeCheck($data): bool
    {
        return $this->isSingleDimensionAttributeCompleted(getOrganizationElementSchema($this->element), $data);
    }

    /**
     * Checks if all element is complete.
     *
     * @param $data
     *
     * @return bool
     * @throws \JsonException
     */
    public function isLevelOneMultiDimensionElementCompleted($data): bool
    {
        return $this->isLevelOneMultiDimensionDataCompleted(getOrganizationElementSchema($this->element), $data);
    }

    /**
     * Checks if two level sub element is complete.
     *
     * @param $data
     *
     * @return bool
     * @throws \JsonException
     */
    public function isLevelTwoSingleDimensionElementCompleted($data): bool
    {
        if (!$this->singleDimensionAttributeCheck($data)) {
            return false;
        }

        $elementSchema = getOrganizationElementSchema($this->element);

        return $this->isSubElementCompleted($elementSchema['sub_elements'], $data);
    }

    /**
     * Checks if two level sub element is complete.
     *
     * @param $data
     *
     * @return bool
     * @throws \JsonException
     */
    public function isLevelTwoMultiDimensionElementCompleted($data): bool
    {
        return $this->isLevelTwoMultiDimensionDataCompleted(getOrganizationElementSchema($this->element), $data);
    }

    /**
     * Checks three level sub element is complete.
     *
     * @param $data
     *
     * @return bool
     * @throws \JsonException
     */
    public function isLevelThreeSingleDimensionElementCompleted($data): bool
    {
        if (!$this->singleDimensionAttributeCheck($data)) {
            return false;
        }

        $elementSchema = getOrganizationElementSchema($this->element);
        $subElements = $elementSchema['sub_elements'];

        foreach ($subElements as $key => $subElement) {
            $mandatorySubElementAttributes = $this->getMandatoryAttributes($subElement);

            if (empty($mandatorySubElementAttributes)) {
                continue;
            }

            if (
                !array_key_exists($key, $data)
                || empty($data[$key])
            ) {
                return false;
            }

            $tempData = $data[$key];

            foreach ($tempData as $datum) {
                if (!$this->isAttributeDataCompleted($mandatorySubElementAttributes, $datum)) {
                    return false;
                }
            }

            foreach ($tempData as $tempDatum) {
                if (!$this->isSubElementCompleted($subElement['sub_elements'], $tempDatum)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns identifier element complete status.
     *
     * @param $organization
     *
     * @return bool
     */
    public function isIdentifierElementCompleted($organization): bool
    {
        $identifier = $organization->identifier;

        return !(empty($identifier));
    }

    /**
     * Returns title element complete status.
     *
     * @param $organization
     *
     * @return bool
     * @throws \JsonException
     */
    public function isNameElementCompleted($organization): bool
    {
        $this->element = 'name';
        $elementSchema = getOrganizationElementSchema($this->element);

        return $this->isSubElementDataCompleted($this->mandatorySubElements($elementSchema['sub_elements']), ['narrative' => $organization->name]);
    }

    /**
     * Returns description element complete status.
     *
     * @param $organization
     *
     * @return bool
     * @throws \JsonException
     */
    public function isReportingOrgElementCompleted($organization): bool
    {
        $this->element = 'reporting_org';

        return $this->isLevelOneMultiDimensionElementCompleted($organization->reporting_org);
    }

    /**
     * Returns conditions element complete status.
     *
     * @param $organization
     *
     * @return bool
     * @throws \JsonException
     */
    public function isTotalBudgetElementCompleted($organization): bool
    {
        $this->element = 'total_budget';

        return $this->isLevelTwoMultiDimensionElementCompleted($organization->total_budget);
    }

    /**
     * Returns conditions element complete status.
     *
     * @param $organization
     *
     * @return bool
     * @throws \JsonException
     */
    public function isTotalExpenditureElementCompleted($organization): bool
    {
        $this->element = 'total_expenditure';

        return $this->isLevelTwoMultiDimensionElementCompleted($organization->total_expenditure);
    }

    /**
     * Returns conditions element complete status.
     *
     * @param $organization
     *
     * @return bool
     * @throws \JsonException
     */
    public function isDocumentLinkElementCompleted($organization): bool
    {
        $this->element = 'document_link';

        return $this->isLevelTwoMultiDimensionElementCompleted($organization->document_link);
    }

    /**
     * Returns conditions element complete status.
     *
     * @param $organization
     *
     * @return bool
     * @throws \JsonException
     */
    public function isRecipientOrgBudgetElementCompleted($organization): bool
    {
        $this->element = 'recipient_org_budget';

        return $this->isLevelTwoMultiDimensionElementCompleted($organization->recipient_org_budget);
    }

    /**
     * Returns conditions element complete status.
     *
     * @param $organization
     *
     * @return bool
     * @throws \JsonException
     */
    public function isRecipientCountryBudgetElementCompleted($organization): bool
    {
        $this->element = 'recipient_country_budget';

        return $this->isLevelTwoMultiDimensionElementCompleted($organization->recipient_country_budget);
    }

    /**
     * Returns conditions element complete status.
     *
     * @param $organization
     *
     * @return bool
     * @throws \JsonException
     */
    public function isRecipientRegionBudgetElementCompleted($organization): bool
    {
        $this->element = 'recipient_region_budget';

        return $this->isLevelTwoMultiDimensionElementCompleted($organization->recipient_region_budget);
    }

    /**
     * Sets default values of language and currency where required for organization.
     *
     * @param $data
     * @param $organization
     *
     * @return mixed
     * @throws \JsonException
     */
    public function setOrganizationDefaultValues(&$data, $organization): mixed
    {
        if ($data) {
            if (is_string($data)) {
                $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            } else {
                foreach ($data as $key => &$datum) {
                    if (is_array($datum)) {
                        $this->setOrganizationDefaultValues($datum, $organization);
                    }

                    $this->setTempNarrative((string) $key, $datum);
                    $this->setTempAmount((string) $key, $datum);

                    if ($organization->settings) {
                        $this->updateLanguage($data, (string) $key, $datum, $organization);

                        $this->updateCurrency($data, (string) $key, $datum, $organization);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Assigns language to $data['language'] variable.
     *
     * @param array $data
     * @param string $key
     * @param $datum
     * @param $organization
     * @return void
     */
    public function updateLanguage(array &$data, string $key, $datum, $organization): void
    {
        if ($key === 'language' && empty($datum) && !empty($this->tempNarrative)) {
            $data['language'] = Arr::get($organization->settings->default_values, 'default_language', null);
        }
    }

    /**
     * Assigns currency to $data['currency'] variable.
     *
     * @param array $data
     * @param string $key
     * @param $datum
     * @param $organization
     * @return void
     */
    public function updateCurrency(array &$data, string $key, $datum, $organization): void
    {
        if ($key === 'currency' && empty($datum) && !empty($this->tempAmount)) {
            $data['currency'] = Arr::get($organization->settings->default_values, 'default_currency', null);
        }
    }
}
