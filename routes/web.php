<?php

use App\Http\Controllers\Admin\Activity\ActivityController;
use App\Http\Controllers\IatiLoginController;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\IATI\API\CkanClient;
use App\IATI\Models\Organization\Organization;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(RedirectIfAuthenticated::class)->name('web.')->group(function () {
    Route::get('/', [App\Http\Controllers\Web\WebController::class, 'index']);
    Route::get('/login', [App\Http\Controllers\Web\WebController::class, 'index'])->name('index.login');
});

Route::group(['middleware' => ['guest', 'sanitize'], 'name' => 'web.'], static function () {
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
});

Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
Route::get('/about', [App\Http\Controllers\Web\WebController::class, 'about'])->name('about');
Route::get('/publishing-checklist', [App\Http\Controllers\Web\WebController::class, 'publishingChecklist'])->name('publishingchecklist');
Route::get('/iati-standard', [App\Http\Controllers\Web\WebController::class, 'iatiStandard'])->name('iatistandard');
Route::get('/support', [App\Http\Controllers\Web\WebController::class, 'support'])->name('support');

Route::get('/activities/activities_count_by_published_status', [ActivityController::class, 'getActivitiesCountByPublishedStatus'])
    ->middleware('auth')
    ->name('activities.getActivitiesCountByPublishedStatus');
Route::get('/duplicate-activity', [ActivityController::class, 'duplicateActivity'])->middleware('auth');
Route::get('/language/{language}', [App\Http\Controllers\Web\WebController::class, 'setLocale'])->name('set-locale');
Route::get('/current-language', [App\Http\Controllers\Web\WebController::class, 'getLocale'])->name('get-locale');
Route::get('/translated-data', [App\Http\Controllers\Web\WebController::class, 'getTranslatedData'])->name('get-translated-data');

Route::get('/login/iati', [IatiLoginController::class, 'redirectToProvider'])->name('login.iati');
Route::get('/login/iati/callback', [IatiLoginController::class, 'handleProviderCallback'])->name('login.iati.callback');
Route::get('/logout/iati', [IatiLoginController::class, 'logout'])->name('logout.iati');
Route::get('/logout/callback', function () {
    return redirect('/');
});

Route::middleware('auth')->group(function () {
    Route::get('/onboarding/organization-missing', [IatiLoginController::class, 'showOrganizationMissingPage'])->name('onboarding.organization-missing');
});

Route::get('/pending-approval', [IatiLoginController::class, 'showYouArePendingApprovalPage'])->name('pending-approval');
Route::get('/multiple-orgs', [IatiLoginController::class, 'showNotSupportMultipleOrgsPage'])->name('multiple-orgs');
Route::get('/sync-error', [IatiLoginController::class, 'showErrorPage'])->name('sync-error');

Route::post('/logout/callback', function () {
    return redirect(session()->has('redirect') ? sprintf('/%s', session('redirect')) : '/');
});

//Route::get('/gen-uuid', function () {
//    // 1. Configuration
//    $ckanBaseUrl = config('services.ckan.staging_api_url', 'https://iatiregistry.org/api/');
//    $chunkSize = 50;
//    $api_call_limit = null;
//    $sleepDurationMicroseconds = 500000;
//
//    // 2. Initialize tracking arrays and counters
//    $results = [
//        'summary' => [
//            'orgs_with_settings_and_org_published' => 0,
//            'orgs_with_settings_and_activity_published' => 0,
//            'total_processed_by_api_check' => 0,
//        ],
//        'successes' => [],
//        'failed_api' => [],
//        'broken_prerequisites' => [],
//        'unpublished_with_settings' => [],
//    ];
//
//    $processedCount = 0;
//
//    // 3. Process Organizations in chunks for memory efficiency
//    Organization::with('settings', 'organizationPublished', 'activityPublished')->orderBy('id')
//        ->chunkById($chunkSize, function ($organisationsChunk) use (
//            $ckanBaseUrl,
//            $api_call_limit,
//            $sleepDurationMicroseconds,
//            &$results,
//            &$processedCount
//        ) {
//            foreach ($organisationsChunk as $organisation) {
//                if ($api_call_limit !== null && $processedCount >= $api_call_limit) {
//                    return false;
//                }
//
//                logger("Processing $organisation->id");
//
//                $processedCount++;
//                $results['summary']['total_processed_by_api_check'] = $processedCount;
//
//                $orgId = $organisation->id;
//                $publisherId = $organisation->publisher_id;
//
//                // --- Summary Calculations ---
//                if ($organisation->settings && $organisation->organizationPublished) {
//                    $results['summary']['orgs_with_settings_and_org_published']++;
//                }
//                if ($organisation->settings && $organisation->organizationPublished && $organisation->activityPublished) {
//                    $results['summary']['orgs_with_settings_and_activity_published']++;
//                }
//
//                // --- Prerequisite Checks ---
//
//                if (empty($publisherId)) {
//                    $results['broken_prerequisites'][] = ['id' => $orgId, 'publisher_id' => $publisherId, 'reason' => 'Missing publisher_id.'];
//                    continue;
//                }
//
//                if (!$organisation->settings) {
//                    $reason = 'Missing settings record.';
//                    if ($organisation->organizationPublished || $organisation->activityPublished) {
//                        $reason .= ' (Warning: Data published, but settings configuration missing.)';
//                    }
//                    $results['broken_prerequisites'][] = ['id' => $orgId, 'publisher_id' => $publisherId, 'reason' => $reason];
//                    continue;
//                }
//
//                // --- Check for unpublished organizations with settings ---
//                if (!$organisation->organizationPublished && !$organisation->activityPublished) {
//                    $results['unpublished_with_settings'][] = [
//                        'id' => $orgId,
//                        'publisher_id' => $publisherId,
//                        'reason' => 'Has settings, but no published activity or organization records found locally.'
//                    ];
//                    continue;
//                }
//
//                // Check 3: Safely extract the API Token
//                $apiToken = data_get($organisation->settings->publishing_info, 'api_token');
//
//                if (empty($apiToken)) {
//                    $results['broken_prerequisites'][] = [
//                        'id' => $orgId,
//                        'publisher_id' => $publisherId,
//                        'reason' => 'Settings present, but API Token is missing or empty within publishing_info.'
//                    ];
//                    continue;
//                }
//                // --- API Interaction ---
//
//                $ckanClient = new CkanClient($ckanBaseUrl, $apiToken);
//                $activityPackageName = $publisherId . '-activities';
//                $organizationPackageName = $publisherId . '-organisation';
//
//                $activityUuid = null;
//                $organizationUuid = null;
//                $failedActivity = null;
//                $failedOrganization = null;
//
//                // 6. Check Organization Package
//                try {
//                    $responseOrganization = $ckanClient->package_show($organizationPackageName); //what happens when 400 response with : "Bad request - Request made with old style API key, please use API token instead." response message?
//                    logger("Org Vettayo for $organizationPackageName");
//                    $organizationUuid = data_get($responseOrganization, 'result.id');
//                } catch (NotFoundHttpException $e) {
//                    $failedOrganization = '404 Not Found';
//                } catch (Exception $exception) {
//                    $failedOrganization = "API Error (Internal Client Failure for $organizationPackageName): " . $exception->getMessage();
//                    logger($exception);
//                }
//
//                // 5. Check Activity Package
//                try {
//                    // CkanClient::package_show returns a raw string response.
//                    $responseActivity = $ckanClient->package_show($activityPackageName);
//                    logger("Activity Vettayo for $activityPackageName");
//                    $activityUuid = data_get($responseActivity, 'result.id');
//                } catch (NotFoundHttpException $e) {
//                    $failedActivity = '404 Not Found';
//                } catch (Exception $exception) {
//                    // The exception message here will likely be the raw TypeError message
//                    // that originated internally in the client.
//                    $failedActivity = "API Error (Internal Client Failure for $activityPackageName): " . $exception->getMessage();
//                    logger($exception);
//                }
//
//                // --- Record Results ---
//
//                if ($activityUuid || $organizationUuid) {
//                    $results['successes'][$orgId] = [
//                        'publisher_id' => $publisherId,
//                        'activity_package_name' => $activityPackageName,
//                        'activity_uuid' => $activityUuid,
//                        'organization_package_name' => $organizationPackageName,
//                        'organization_uuid' => $organizationUuid,
//                        'activity_status' => $activityUuid ? 'OK' : $failedActivity,
//                        'organization_status' => $organizationUuid ? 'OK' : $failedOrganization,
//                    ];
//                } elseif ($failedActivity || $failedOrganization) {
//                    $results['failed_api'][] = [
//                        'id' => $orgId,
//                        'publisher_id' => $publisherId,
//                        'activity_package_name' => $activityPackageName,
//                        'organization_package_name' => $organizationPackageName,
//                        'activity_error' => $failedActivity,
//                        'organization_error' => $failedOrganization,
//                    ];
//                }
//
//                // --- THROTTLING DELAY ---
//                usleep($sleepDurationMicroseconds);
//            }
//        }); // End chunkById
//
//    // --- CSV File Writing ---
//
//    $timestamp = now()->format('Ymd_His');
//    $filename = "ckan_audit_results_{$timestamp}.csv";
//    $filePath = storage_path("logs/{$filename}");
//
//    try {
//        $handle = fopen($filePath, 'w');
//
//        // 7. Define Headers
//        $headers = [
//            'ID',
//            'Publisher ID',
//            'Status',
//            'Activity Package Name',
//            'Activity UUID',
//            'Activity Status/Error',
//            'Org Package Name',
//            'Org UUID',
//            'Org Status/Error',
//            'Prerequisite Reason',
//        ];
//        fputcsv($handle, $headers);
//
//        // 8. Write Successes
//        foreach ($results['successes'] as $orgId => $record) {
//            $status = ($record['activity_uuid'] && $record['organization_uuid']) ? 'SUCCESS_BOTH' : 'SUCCESS_PARTIAL';
//            fputcsv($handle, [
//                $orgId,
//                $record['publisher_id'],
//                $status,
//                $record['activity_package_name'],
//                $record['activity_uuid'],
//                $record['activity_status'],
//                $record['organization_package_name'],
//                $record['organization_uuid'],
//                $record['organization_status'],
//                '', // Prerequisite Reason
//            ]);
//        }
//
//        // 9. Write API Failures
//        foreach ($results['failed_api'] as $record) {
//            fputcsv($handle, [
//                $record['id'],
//                $record['publisher_id'],
//                'API_FAILURE',
//                $record['activity_package_name'],
//                '', // Activity UUID
//                $record['activity_error'],
//                $record['organization_package_name'],
//                '', // Org UUID
//                $record['organization_error'],
//                '', // Prerequisite Reason
//            ]);
//        }
//
//        // 10. Write Prerequisite Breakages
//        foreach (array_merge($results['broken_prerequisites'], $results['unpublished_with_settings']) as $record) {
//            $status = (str_contains($record['reason'], 'unpublished')) ? 'UNPUBLISHED_LOCAL' : 'PREREQ_BROKEN';
//            fputcsv($handle, [
//                $record['id'],
//                $record['publisher_id'],
//                $status,
//                '', // Activity Package Name
//                '', // Activity UUID
//                '', // Activity Status/Error
//                '', // Org Package Name
//                '', // Org UUID
//                '', // Org Status/Error
//                $record['reason'],
//            ]);
//        }
//
//        fclose($handle);
//
//    } catch (\Throwable $e) {
//        Log::error("Failed to write CKAN audit results CSV file.", [
//            'path' => $filePath,
//            'error' => $e->getMessage(),
//        ]);
//        return response("CKAN Audit completed, but failed to write CSV file. Error: " . $e->getMessage(), 500);
//    }
//
//    // Return summary and file path
//    $output = array_merge(['File Saved To' => $filePath], $results['summary']);
//    return response()->json($output);
//});
