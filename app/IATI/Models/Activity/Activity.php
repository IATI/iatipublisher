<?php

declare(strict_types=1);

namespace App\IATI\Models\Activity;

use App\IATI\Models\Document\Document;
use App\IATI\Models\ImportActivityError\ImportActivityError;
use App\IATI\Models\Organization\Organization;
use App\IATI\Services\Activity\RecipientRegionService;
use Database\Factories\IATI\Models\Activity\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class Activity.
 */
class Activity extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * Fillable property for mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'iati_identifier',
        'other_identifier',
        'title',
        'description',
        'activity_status',
        'status',
        'activity_date',
        'contact_info',
        'activity_scope',
        'participating_org',
        'recipient_country',
        'recipient_region',
        'location',
        'sector',
        'country_budget_items',
        'humanitarian_scope',
        'policy_marker',
        'collaboration_type',
        'default_flow_type',
        'default_finance_type',
        'default_aid_type',
        'default_tied_status',
        'budget',
        'planned_disbursement',
        'capital_spend',
        'document_link',
        'related_activity',
        'legacy_data',
        'conditions',
        'org_id',
        'default_field_values',
        'tag',
        'reporting_org',
        'element_status',
        'upload_medium',
        'created_by',
        'updated_by',
        'linked_to_iati',
        'migrated_from_aidstream',
        'complete_percentage',
        'created_at',
        'updated_at',
        'has_ever_been_published',
        'deprecation_status_map',
        'activity_identifier',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'iati_identifier'      => 'json',
        'other_identifier'     => 'json',
        'title'                => 'json',
        'description'          => 'json',
        'activity_date'        => 'json',
        'contact_info'         => 'json',
        'activity_scope'       => 'json',
        'participating_org'    => 'json',
        'recipient_country'    => 'json',
        'recipient_region'     => 'json',
        'location'             => 'json',
        'sector'               => 'json',
        'country_budget_items' => 'json',
        'humanitarian_scope'   => 'json',
        'policy_marker'        => 'json',
        'collaboration_type'   => 'json',
        'default_flow_type'    => 'json',
        'default_finance_type' => 'json',
        'default_aid_type'     => 'json',
        'default_tied_status'  => 'json',
        'budget'               => 'json',
        'planned_disbursement' => 'json',
        'capital_spend'        => 'json',
        'document_link'        => 'json',
        'related_activity'     => 'json',
        'legacy_data'          => 'json',
        'conditions'           => 'json',
        'default_field_values' => 'json',
        'tag'                  => 'json',
        'reporting_org'        => 'json',
        'element_status'       => 'json',
        'linked_to_iati'       => 'boolean',
        'complete_percentage'  => 'float',
        'deprecation_status_map'=>'json',
    ];

    /**
     * Before inbuilt function.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(
            function ($model) {
                if (Auth::check()) {
                    if (!empty($model->iati_identifier) && empty($model->activity_identifier)) {
                        $model->activity_identifier = Arr::get($model->iati_identifier, 'activity_identifier');
                    }

                    if ($model->isDirty('iati_identifier') && empty(Arr::get($model->iati_identifier, 'iati_identifier_text'))) {
                        $organisationIdentifier = Auth::user()->organization->identifier;
                        $activityIdentifier = $model->iati_identifier['activity_identifier'];
                        $iatiIdentifier = [
                            'activity_identifier' => $activityIdentifier,
                            'iati_identifier_text' => $organisationIdentifier . '-' . $activityIdentifier,
                            'present_organization_identifier' => $organisationIdentifier,
                        ];

                        $model->iati_identifier = $iatiIdentifier;
                    }

                    $model->created_by = auth()->user()->id;
                    $model->updated_by = auth()->user()->id;
                }
            }
        );
    }

    /**
     * Factory for creating activity.
     *
     * @return ActivityFactory
     */
    public static function newFactory(): ActivityFactory
    {
        return new ActivityFactory();
    }

    /**
     * An Activity has many ActivityDocumentLink.
     *
     * @return HasMany
     */
    public function documentLinks(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Activity has many transactions.
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'activity_id', 'id');
    }

    /**
     * Activity has many results.
     *
     * @return HasMany
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'activity_id', 'id');
    }

    /**
     * Activity has one import error.
     *
     * @return HasOne
     */
    public function importError(): HasOne
    {
        return $this->hasOne(ImportActivityError::class, 'activity_id', 'id');
    }

    /**
     * Factory for creating activity.
     *
     * Activity belongs to an organisation.
     *
     * @return BelongsTo
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Checks if activity belongs to organization.
     *
     * @return bool
     */
    public function isActivityOfOrg(): bool
    {
        return $this->org_id === Auth::user()->organization_id;
    }

    /**
     * Returns default title.
     *
     * @return ?string
     */
    public function getDefaultTitleNarrativeAttribute(): ?string
    {
        $titles = $this->title;

        if (!empty($titles)) {
            foreach ($titles as $title) {
                if (array_key_exists('language', $title) && !empty($title['language']) && $title['language'] === getDefaultLanguage($this->default_field_values)) {
                    return (string) $title['narrative'];
                }
            }

            return array_key_exists('narrative', $titles[0]) ? (string) $titles[0]['narrative'] : getTranslatedUntitled();
        }

        return getTranslatedUntitled();
    }

    /**
     * Sets recipient region value
     * Also removes recipient country completeness if null.
     *
     * @param $value
     * @return void
     * @throws \JsonException
     */
    public function setRecipientRegionAttribute($value): void
    {
        $elementStatus = Arr::get($this->attributes, 'element_status', []) ? json_decode($this->attributes['element_status'], true, 512, JSON_THROW_ON_ERROR) : [];

        if (empty($value) && !empty($this->attributes['recipient_country'])) {
            $percentages = array_map(
                fn ($v) => is_numeric($v) ? (float) $v : 0,
                array_column($this->recipient_country, 'percentage')
            );

            $countryTotalPercentage = (float) array_sum($percentages);

            if ($countryTotalPercentage !== 100.0) {
                $elementStatus['recipient_country'] = false;
            }
        }

        if (is_array_value_empty($value) && empty($this->recipient_country)) {
            $elementStatus['recipient_country'] = false;
        }

        $this->attributes['element_status'] = json_encode($elementStatus, JSON_THROW_ON_ERROR);
        $this->attributes['recipient_region'] = !empty($value) ? json_encode($value, JSON_THROW_ON_ERROR) : null;
    }

    /**
     * Sets recipient country value
     * Also removed recipient region completeness if null.
     *
     * @param $value
     * @return void
     * @throws \JsonException
     */
    public function setRecipientCountryAttribute($value): void
    {
        $elementStatus = Arr::get($this->attributes, 'element_status', []) ? json_decode($this->attributes['element_status'], true, 512, JSON_THROW_ON_ERROR) : [];

        if (empty($value) && !empty($this->attributes['recipient_region'])) {
            $recipientRegionService = app()->make(RecipientRegionService::class);
            $groupPercentage = $recipientRegionService->groupRegion($this->recipient_region);
            $firstGroupTotalPercentage = Arr::first($groupPercentage, static function ($value) {
                return $value;
            })['total'];

            if ($firstGroupTotalPercentage !== 100.0) {
                $elementStatus['recipient_region'] = false;
            }
        }

        if (is_array_value_empty($value) && empty($this->recipient_region)) {
            $elementStatus['recipient_region'] = false;
        }

        $this->attributes['element_status'] = json_encode($elementStatus, JSON_THROW_ON_ERROR);
        $this->attributes['recipient_country'] = !empty($value) ? json_encode($value, JSON_THROW_ON_ERROR) : null;
    }
}
