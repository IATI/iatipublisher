<?php

declare(strict_types=1);

namespace App\IATI\Models\Organization;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OrganizationPublished.
 */
class OrganizationPublished extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'organization_published';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
      'organization_id',
      'publish_status',
      'filename',
      'published_to_registry',
      'dataset_uuid',
      'created_at',
      'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_to_registry' => 'boolean',
    ];

    /**
     * Organisation publish belongs to an organization.
     *
     * @return BelongsTo
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }
}
