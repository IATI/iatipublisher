<?php

declare(strict_types=1);

namespace App\IATI\Models\Organization;

use App\IATI\Models\Activity\Activity;
use App\IATI\Models\Activity\ActivityPublished;
use App\IATI\Models\Setting\Setting;
use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use App\IATI\Services\ElementCompleteService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class Organization.
 */
class Organization extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'publisher_id',
        'publisher_name',
        'publisher_type',
        'identifier',
        'name',
        'address',
        'telephone',
        'reporting_org',
        'country',
        'logo_url',
        'organization_url',
        'status',
        'is_published',
        'registration_agency',
        'registration_number',
        'registration_type',
        'data_license',
        'reporting_org',
        'total_budget',
        'recipient_org_budget',
        'recipient_region_budget',
        'recipient_country_budget',
        'document_link',
        'total_expenditure',
        'organisation_identifier',
        'name',
        'element_status',
        'org_status',
        'updated_by',
        'migrated_from_aidstream',
        'created_at',
        'updated_at',
        'old_identifiers',
        'deprecation_status_map',
        'uuid',
        'registry_approved',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'reporting_org' => 'json',
        'total_budget' => 'json',
        'recipient_org_budget' => 'json',
        'recipient_region_budget' => 'json',
        'recipient_country_budget' => 'json',
        'document_link' => 'json',
        'total_expenditure' => 'json',
        'organisation_identifier' => 'json',
        'name' => 'json',
        'element_status' => 'json',
        'is_published' => 'boolean',
        'old_identifiers' => 'json',
        'deprecation_status_map'=>'json',
    ];

    /**
     * @var ElementCompleteService
     */
    protected ElementCompleteService $elementCompleteService;

    /**
     * Construct function.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->elementCompleteService = new ElementCompleteService();
    }

    /**
     * Organisation has many activities.
     *
     * @return HasMany
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ActivityPublished::class, 'organization_id', 'id');
    }

    /**
     * Organization has one user.
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'organization_id', 'id');
    }

    /**
     * Organization has settings.
     *
     * @return HasOne
     */
    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class, 'organization_id', 'id');
    }

    /**
     * Organization has Organization Published file.
     *
     * @return HasOne
     */
    public function organizationPublished(): HasOne
    {
        return $this->hasOne(OrganizationPublished::class, 'organization_id', 'id');
    }

    /**
     * Returns complete status of reporting_org.
     *
     * @return bool
     * @throws \JsonException
     */
    public function getReportingOrgElementCompletedAttribute(): bool
    {
        $this->elementCompleteService->element = 'reporting_org';

        return $this->elementCompleteService->isLevelOneMultiDimensionElementCompleted($this->reporting_org);
    }

    /**
     * An Organization can have many Activities published.
     *
     * @return HasMany
     */
    public function publishedFiles(): HasMany
    {
        return $this->hasMany(ActivityPublished::class, 'organization_id', 'id');
    }

    /**
     * Organization has many activities.
     *
     * @return HasMany
     */
    public function allActivities(): HasMany
    {
        return $this->hasMany(Activity::class, 'org_id', 'id');
    }

    /**
     * Encrypt when organisation model is audited.
     *
     * {@inheritdoc}
     *
     * @return array
     */
    public function transformAudit(array $data): array
    {
        if ($data['old_values']) {
            foreach ($data['old_values'] as $key => $val) {
                $data['old_values'][$key] = Crypt::encryptString($val);
            }
        }

        if ($data['new_values']) {
            foreach ($data['new_values'] as $key => $val) {
                $data['new_values'][$key] = Crypt::encryptString($val);
            }
        }

        return $data;
    }

    /**
     * Returns the admin user of the organization.
     *
     * @return object
     */
    public function getAdminUser(): object
    {
        $adminId = app(Role::class)->getOrganizationAdminId();

        return $this->users()->where('role_id', $adminId)->first();
    }

    /**
     * Organization has many users.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'organization_id', 'id');
    }

    /**
     * Organization has many users.
     *
     * @return HasOne
     */
    public function latestLoggedInUser():HasOne
    {
        return $this->hasOne(User::class)->ofMany('last_logged_in', 'max');
    }

    /**
     * Organization has one latest updated activity.
     *
     * @return HasOne
     */
    public function latestUpdatedActivity(): HasOne
    {
        return $this->hasOne(Activity::class, 'org_id')->ofMany('updated_at', 'max');
    }

    /**
     * @return HasOne
     */
    public function activityPublished(): HasOne
    {
        return $this->hasOne(ActivityPublished::class, 'organization_id', 'id');
    }

    /**
     * Organization has one onboarding.
     *
     * @return HasOne
     */
    public function onboarding(): HasOne
    {
        return $this->hasOne(OrganizationOnboarding::class, 'org_id', 'id');
    }

    public function getHumanReadableNameAttribute(): ?string
    {
        return $this->name[0]['narrative'] ?? null;
    }

    public function getSourceTypeAttribute(): string
    {
        $isSecondary = data_get($this->reporting_org, '0.secondary_reporter', false);

        return $isSecondary ? 'secondary_source' : 'primary_source';
    }
}
