<?php

declare(strict_types=1);

namespace App\IATI\Repositories\Activity;

use App\IATI\Models\Activity\Activity;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ContactInfoRepository.
 */
class ContactInfoRepository
{
    /**
     * @var Activity
     */
    protected Activity $activity;

    /**
     * ContactInfoRepository Constructor.
     *
     * @param Activity $activity
     */
    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Returns contact info data of an activity.
     *
     * @param $activityId
     *
     * @return array|null
     */
    public function getContactInfoData($activityId): ?array
    {
        return $this->activity->findorFail($activityId)->contact_info;
    }

    /**
     * Returns activity object.
     *
     * @param $id
     *
     * @return Model
     */
    public function getActivityData($id): Model
    {
        return $this->activity->findOrFail($id);
    }

    /**
     * Updates contact info.
     *
     * @param $contactInfo
     * @param $activity
     *
     * @return bool
     * @throws \JsonException
     */
    public function update($contactInfo, $activity): bool
    {
        $element = getElementSchema('contact_info');

        foreach ($contactInfo['contact_info'] as $key => $contact) {
            foreach (array_keys($element['sub_elements']) as $subElement) {
                $contactInfo['contact_info'][$key][$subElement] = array_values($contact[$subElement]);
            }
        }

        $activity->contact_info = $contactInfo['contact_info'];

        return $activity->save();
    }
}
