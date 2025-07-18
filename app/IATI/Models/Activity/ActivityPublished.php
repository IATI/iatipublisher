<?php

declare(strict_types=1);

namespace App\IATI\Models\Activity;

use App\IATI\Models\Organization\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class ActivityPublished.
 */
class ActivityPublished extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * @var string
     */
    protected $table = 'activity_published';

    /**
     * @var array
     */
    protected $fillable
        = [
            'published_activities',
            'filename',
            'published_to_registry',
            'organization_id',
            'filesize',
            'dataset_uuid',
            'created_at',
            'updated_at',
        ];

    /**
     * @var array
     */
    protected $casts
        = [
            'published_activities' => 'json',
            'filesize'=>'float',
        ];

    /**
     * An ActivityPublished record belongs to an Organization.
     *
     * @return BelongsTo
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    /**
     * Extract the Activity Ids from the activity-xml filenames included with in the Activity Xml file.
     *
     * @return array
     */
    public function extractActivities(): array
    {
        $activityIds = [];

        if ($this->published_activities) {
            foreach ($this->published_activities as $publishedActivity) {
                $pieces = explode('-', $this->getFilename('.', $publishedActivity));
                $activityIds[end($pieces)] = $publishedActivity;
            }
        }

        return $activityIds;
    }

    /**
     * Returns filename.
     *
     * @param $delimiter
     * @param $file
     *
     * @return string
     */
    protected function getFilename($delimiter, $file): string
    {
        $file = explode($delimiter, $file);

        return reset($file);
    }
}
