<?php

declare(strict_types=1);

namespace App\Observers;

use App\IATI\Models\Organization\Organization;
use App\IATI\Services\OrganizationElementCompleteService;
use App\IATI\Services\RegisterYourDataApi\IatiDataSyncService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * Class OrganizationObserver.
 */
class OrganizationObserver
{
    /**
     * Organization observer constructor.
     */
    public function __construct(
        protected OrganizationElementCompleteService $organizationElementCompleteService,
        protected IatiDataSyncService $iatiDataSyncService
    ) {
    }

    /**
     * @param $updatedAttributes
     *
     * @return array
     * @throws \JsonException
     */
    public function getUpdatedElement($updatedAttributes): array
    {
        $elements = getOrganizationElements();
        $updatedElements = [];

        $elements[] = 'identifier';

        foreach ($updatedAttributes as $element => $updatedAttribute) {
            if (in_array($element, $elements, true)) {
                $updatedElements[$element] = $updatedAttribute;
            }
        }

        return $updatedElements;
    }

    /**
     * Sets the complete status of elements.
     *
     * @param      $model
     * @param bool $isNew
     *
     * @return void
     * @throws \JsonException
     */
    public function setElementStatus($model, bool $isNew = false): void
    {
        $elementStatus = $model->element_status;
        $updatedElements = ($isNew) ? $this->getUpdatedElement($model->getAttributes()) : $this->getUpdatedElement($model->getChanges());

        foreach ($updatedElements as $attribute => $value) {
            $elementStatus[$attribute] = call_user_func(
                [$this->organizationElementCompleteService, dashesToCamelCase('is_' . $attribute . '_element_completed')],
                $model
            );
        }

        $model->element_status = $elementStatus;
    }

    /**
     * Handle the Organization "created" event.
     *
     * @param Organization $organization
     *
     * @return void
     * @throws \JsonException
     */
    public function created(Organization $organization): void
    {
        $this->setElementStatus($organization, true);

        if (!$organization->migrated_from_aidstream) {
            $this->resetOrganizationStatus($organization);
        }

        $organization->saveQuietly();
    }

    /**
     * Handle the Organization "updated" event.
     *
     * @param Organization $organization
     *
     * @return void
     * @throws \JsonException
     */
    public function updated(Organization $organization): void
    {
        $updatedElements = $this->removeElements($organization->getDirty());
        $key = array_key_first($updatedElements);
        $data = Arr::get($updatedElements, $key);

        if (Arr::has($organization->getDirty(), 'reporting_org')) {
            $organization->publisher_type = data_get($organization->reporting_org, '0.type');
        }

        if (!empty($updatedElements) && !in_array($key, getNonArrayElements(), true) && !Arr::has($organization->getDirty(), 'is_published')) {
            $updatedData = $this->organizationElementCompleteService->setOrganizationDefaultValues($data, $organization);
            $organization->$key = $updatedData;
        }

        $this->setElementStatus($organization);
        $this->resetOrganizationStatus($organization);

        if (auth()->check()) {
            $organization->updated_by = Auth::user()->id;
        }

        $organization->saveQuietly();

        $this->iatiDataSyncService->syncOrganizationUpstream($organization, $updatedElements);
    }

    /**
     * Removes organization fields that do not require setting default value.
     *
     * @param $organizationElements
     *
     * @return array
     */
    public function removeElements($organizationElements)
    {
        $ignorableElements = [
            'identifier',
            'iati_status',
            'status',
            'is_published',
            'updated_at',
        ];

        foreach (array_keys($organizationElements) as $key) {
            if (in_array($key, $ignorableElements)) {
                unset($organizationElements[$key]);
            }
        }

        return $organizationElements;
    }

    /**
     * Resets Organization status to draft.
     *
     * @param $model
     *
     * @return void
     */
    public function resetOrganizationStatus($model): void
    {
        $model->status = 'draft';
    }
}
