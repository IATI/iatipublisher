<?php

declare(strict_types=1);

namespace App\Observers;

use App\Helpers\BulkPublishCacheHelper;
use App\IATI\Models\Activity\Activity;
use App\IATI\Services\ElementCompleteService;
use Illuminate\Support\Facades\Auth;

/**
 * Class ActivityObserver.
 */
class ActivityObserver
{
    /**
     * @var ElementCompleteService
     */
    protected ElementCompleteService $elementCompleteService;

    /**
     * Activity observer constructor.
     */
    public function __construct()
    {
        $this->elementCompleteService = new ElementCompleteService();
    }

    /**
     * @param $updatedAttributes
     *
     * @return array
     * @throws \JsonException
     */
    public function getUpdatedElement($updatedAttributes): array
    {
        $elements = getElements();
        $updatedElements = [];

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
            $callableFunction = dashesToCamelCase('is_' . $attribute . '_element_completed');
            $elementStatus[$attribute] = call_user_func([$this->elementCompleteService, $callableFunction], $model);
        }

        $model->setAttribute('element_status', $elementStatus);
    }

    /**
     * Handle the Activity "created" event.
     *
     * @param Activity $activity
     *
     * @return void
     * @throws \JsonException
     */
    public function created(Activity $activity): void
    {
        $this->setElementStatus($activity, true);
        $activity->complete_percentage = $this->elementCompleteService->calculateCompletePercentage($activity->element_status);

        if (!$activity->migrated_from_aidstream) {
            $this->resetActivityStatus($activity);
        }

        if (Auth::check()) {
            $activity->created_by = Auth::user()->id;
            $activity->updated_by = Auth::user()->id;
        }

        if ($activity->migrated_from_aidstream) {
            $activity->timestamps = false;
        }

        $activity->saveQuietly();
    }

    /**
     * Handle the Activity "updated" event.
     *
     * @param Activity $activity
     *
     * @return void
     * @throws \JsonException
     */
    public function updated(Activity $activity): void
    {
        $this->setElementStatus($activity);
        $this->resetActivityStatus($activity);
        $activity->updated_by = Auth::user()->id;
        $activity->complete_percentage = $this->elementCompleteService->calculateCompletePercentage($activity->element_status);
        $activity->saveQuietly();

        $orgId = $activity->org_id;

        if (BulkPublishCacheHelper::hasOngoingBulkPublish($orgId)) {
            BulkPublishCacheHelper::appendActivityIdInBulkPublishCache($orgId, $activity->id);
        }
    }

    /**
     * Resets activity status to draft.
     *
     * @param $model
     *
     * @return void
     */
    public function resetActivityStatus($model): void
    {
        $model->status = 'draft';
    }

    /**
     * Removes activity fields that do not require setting default value.
     *
     * @param $activityElements
     *
     * @return array
     */
    public function removeElements($activityElements): array
    {
        $ignorableElements = [
            'updated_at',
            'status',
            'updated_by',
            'created_by',
            'upload_medium',
        ];

        foreach (array_keys($activityElements) as $key) {
            if (in_array($key, $ignorableElements)) {
                unset($activityElements[$key]);
            }
        }

        return $activityElements;
    }
}
