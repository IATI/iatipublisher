<?php

declare(strict_types=1);

namespace App\Constants;

/**
 * Class DBTables.
 */
abstract class DBTables
{
    /**
     * For Migration File.
     */
    public const ORGANIZATIONS = 'organizations';

    /**
     * For Migration File.
     */
    public const ORGANIZATION_PUBLISH = 'organization_published';

    /**
     * For Migration File.
     */
    public const USERS = 'users';

    /**
     * For Migration File.
     */
    public const SETTINGS = 'settings';

    /**
     * For Migration File.
     */
    public const DOCUMENTS = 'documents';

    /**
     * For Migration File.
     */
    public const ACTIVITY = 'activities';

    /**
     * For Migration File.
     */
    public const RESULT = 'activity_results';

    /**
     * For Migration File.
     */
    public const INDICATOR = 'activity_result_indicators';

    /**
     * For Migration File.
     */
    public const PERIOD = 'result_indicator_periods';

    /**
     * For Migration File.
     */
    public const IMPORT_STATUS = 'import_status';

    /**
     * For Migration File.
     */
    public const DOWNLOAD_STATUS = 'download_status';

    /**
     * For Validating Activity in IATI Registry.
     */
    public const VALIDATION_STATUS = 'validation_status';

    /**
     * For activity_published table.
     */
    public const ACTIVITY_PUBLISHED = 'activity_published';

    /**
     * For bulk_publishing_status table.
     */
    public const BULK_PUBLISHING_STATUS = 'bulk_publishing_status';
}
