<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Helper class to manage import-related caching logic.
 *
 * @class  ImportCacheHelper
 */
class ImportCacheHelper
{
    private const STEP = 'step';

    private const IMPORTING_COMPLETE = 'importing_complete';

    private const VALIDATING_COMPLETE = 'validating_complete';

    private const HAS_ONGOING_IMPORT = 'has_ongoing_import';

    private const SESSION_IMPORT_FILETYPE = 'import_filetype';

    private const ACTIVITY_IDENTIFIERS = 'activity_identifiers';

    private const CACHE_KEY_PATTERN = 'ongoing_import_%s';

    /**
     * Cache Schema.
     */
    private const DEFAULT_CACHE_VALUE = [
        self::HAS_ONGOING_IMPORT   => true,
        self::STEP                 => self::VALIDATING_COMPLETE,
        self::ACTIVITY_IDENTIFIERS => [],
    ];

    /**
     * Checks if a specific activity identifier is already being imported.
     *
     * @param int    $orgId
     * @param string $activityIdentifier
     *
     * @return bool
     */
    public static function isThisActivityBeingImported(int $orgId, string $activityIdentifier): bool
    {
        return in_array($activityIdentifier, self::fetchCachedActivityIdentifiers($orgId), true);
    }

    /**
     * Checks if there is an ongoing import for the given organisation.
     *
     * @param int $orgId
     *
     * @return bool
     */
    public static function isAnotherImportInProgressForOrganisation(int $orgId): bool
    {
        return Arr::get(self::fetchOrganisationCache($orgId), self::HAS_ONGOING_IMPORT, false);
    }

    /**
     * Retrieves activity identifiers from the cache for the given organisation.
     *
     * @param int $orgId
     *
     * @return array
     */
    public static function fetchCachedActivityIdentifiers(int $orgId): array
    {
        return Arr::get(self::fetchOrganisationCache($orgId), self::ACTIVITY_IDENTIFIERS, []);
    }

    /**
     * Gets the cached value for the given organisation ID.
     *
     * @param int $orgId
     *
     * @return array
     */
    public static function fetchOrganisationCache(int $orgId): array
    {
        return Cache::get(self::generateCacheKey($orgId), []);
    }

    /**
     * Constructs the cache key for the given organisation ID.
     *
     * @param int $orgId
     *
     * @return string
     */
    public static function generateCacheKey(int $orgId): string
    {
        return sprintf(self::CACHE_KEY_PATTERN, $orgId);
    }

    /**
     * Appends an activity identifier to the cache for the given organisation.
     *
     * @param int    $orgId
     * @param string $activityIdentifier
     *
     * @return void
     */
    public static function appendActivityIdentifiersToCache(int $orgId, string $activityIdentifier): void
    {
        $cacheValue = self::fetchOrganisationCache($orgId);
        $activityIdentifiers = Arr::get($cacheValue, self::ACTIVITY_IDENTIFIERS, []);

        if (!in_array($activityIdentifier, $activityIdentifiers)) {
            $activityIdentifiers[] = $activityIdentifier;
        }

        $cacheValue[self::ACTIVITY_IDENTIFIERS] = $activityIdentifiers;
        Cache::put(self::generateCacheKey($orgId), $cacheValue);
    }

    /**
     * Marks the import as ongoing for the given organisation.
     *
     * @param int $orgId
     *
     * @return void
     */
    public static function beginOngoingImport(int $orgId): void
    {
        $cacheValue = self::fetchOrganisationCache($orgId);
        $cacheValue[self::HAS_ONGOING_IMPORT] = true;

        Cache::put(self::generateCacheKey($orgId), $cacheValue);
    }

    /**
     * Clears the import cache for the given organisation.
     *
     * @param int $orgId
     *
     * @return void
     */
    public static function clearImportCache(int $orgId): void
    {
        self::markImportStepComplete($orgId);
        Cache::forget(self::generateCacheKey($orgId));
    }

    /**
     * Gets the current import step for the given organisation.
     *
     * @param int $orgId
     *
     * @return string
     */
    public static function fetchCurrentImportStep(int $orgId): string
    {
        return Arr::get(self::fetchOrganisationCache($orgId), self::STEP, '');
    }

    /**
     * Checks if the organisation has completed importing data.
     *
     * @param int $orgId
     *
     * @return bool
     */
    public static function hasOrganisationFinishedImportStep(int $orgId): bool
    {
        return self::fetchCurrentImportStep($orgId) === self::IMPORTING_COMPLETE;
    }

    /**
     * Sets the import step to 'importing_complete' for the given organisation.
     *
     * @param int $orgId
     *
     * @return void
     */
    public static function markImportStepComplete(int $orgId): void
    {
        $cacheValue = self::fetchOrganisationCache($orgId);
        $cacheValue[self::STEP] = self::IMPORTING_COMPLETE;

        Cache::put(self::generateCacheKey($orgId), $cacheValue);
    }

    /**
     * Checks if the organisation has completed validating data.
     *
     * @param int $orgId
     *
     * @return bool
     */
    public static function hasOrganisationFinishedValidationStep(int $orgId): bool
    {
        return self::fetchCurrentImportStep($orgId) === self::VALIDATING_COMPLETE;
    }

    /**
     * Sets the import step to 'validating_complete' for the given organisation.
     *
     * @param int $orgId
     *
     * @return void
     */
    public static function markValidationStepComplete(int $orgId): void
    {
        $cacheValue = self::fetchOrganisationCache($orgId);
        $cacheValue[self::STEP] = self::VALIDATING_COMPLETE;

        Cache::put(self::generateCacheKey($orgId), $cacheValue);
    }

    /**
     * Get import_filetype from cache if not inaccessible from session for some reason.
     *
     * @param int $orgId
     *
     * @return string|null
     */
    public static function getSessionConsistentFiletype(int $orgId): ?string
    {
        return Arr::get(self::fetchOrganisationCache($orgId), self::SESSION_IMPORT_FILETYPE);
    }

    /**
     * Set import_filetype in cache. Only call when setting in session.
     *
     * @param int    $orgId
     * @param string $filetype
     *
     * @return void
     */
    public static function setSessionConsistentFiletype(int $orgId, string $filetype): void
    {
        $cacheValue = self::fetchOrganisationCache($orgId);
        $cacheValue[self::SESSION_IMPORT_FILETYPE] = $filetype;

        Cache::put(self::generateCacheKey($orgId), $cacheValue);
    }
}
