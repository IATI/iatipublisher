<?php

declare(strict_types=1);

namespace App\IATI\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class Transaction.
 */
class Transaction extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * @var string
     */
    protected $table = 'activity_transactions';

    /**
     * Fillable property for mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'activity_id',
        'transaction',
        'migrated_from_aidstream',
        'created_at',
        'updated_at',
        'deprecation_status_map',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'transaction' => 'json',
        'deprecation_status_map' => 'json',
    ];

    /**
     * Updates timestamp of activity on transaction update.
     *
     * @var array
     */
    protected $touches = ['activity'];

    /**
     * Transaction belongs to an activity.
     *
     * @return BelongsTo
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id', 'id');
    }
}
