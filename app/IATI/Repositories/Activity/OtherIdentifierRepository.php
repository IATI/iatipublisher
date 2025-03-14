<?php

declare(strict_types=1);

namespace App\IATI\Repositories\Activity;

use App\IATI\Models\Activity\Activity;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OtherIdentifierRepository.
 */
class OtherIdentifierRepository
{
    /**
     * @var Activity
     */
    protected Activity $activity;

    /**
     * OtherIdentifierRepository Constructor.
     *
     * @param Activity $activity
     */
    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Returns other identifier data of an activity.
     *
     * @param $activityId
     *
     * @return array|null
     */
    public function getOtherIdentifierData($activityId): ?array
    {
        return $this->activity->findorFail($activityId)->other_identifier;
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
     * Updates activity conditions.
     *
     * @param $activityIdentifier
     * @param $activity
     *
     * @return bool
     */
    public function update($activityIdentifier, $activity): bool
    {
        $activityIdentifier = array_values($activityIdentifier);

        foreach ($activityIdentifier as $index => $other_identifier) {
            $activityIdentifier[$index]['owner_org'] = array_values($other_identifier['owner_org']);

            foreach ($other_identifier['owner_org'] as $owner_index => $owner_value) {
                $activityIdentifier[$index]['owner_org'][$owner_index]['narrative'] = array_values($owner_value['narrative']);
            }
        }

        $activity->other_identifier = $activityIdentifier;

        return $activity->save();
    }
}
