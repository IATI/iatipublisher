<?php

declare(strict_types=1);

namespace App\Jobs;

use App\IATI\Models\Organization\Organization;
use App\IATI\Models\Setting\Setting;
use App\IATI\Repositories\Activity\BulkPublishingStatusRepository;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\BulkPublishingStatusService;
use App\IATI\Services\Workflow\ActivityWorkflowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Class BulkPublishActivities.
 */
class BulkPublishActivities implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var BulkPublishingStatusService
     */
    protected BulkPublishingStatusService $publishingStatusService;

    /**
     * @var ActivityWorkflowService
     */
    protected ActivityWorkflowService $activityWorkflowService;

    /**
     * @var ActivityService
     */
    protected ActivityService $activityService;

    /**
     * @var int
     */
    public $timeout = 3600;

    public function __construct(
        protected object $activities,
        protected Organization $organization,
        protected Setting $settings,
        protected string $accessToken,
        protected int $organizationId,
        protected string $uuid
    ) {
    }

    /**
     * Execute the job.
     *
     * @param BulkPublishingStatusService $publishingStatusService
     * @param ActivityWorkflowService $activityWorkflowService
     * @param ActivityService $activityService
     *
     * @return void
     */
    public function handle(BulkPublishingStatusService $publishingStatusService, ActivityWorkflowService $activityWorkflowService, ActivityService $activityService): void
    {
        $this->activities->load(['transactions', 'results.indicators.periods']);
        $this->setServices($publishingStatusService, $activityWorkflowService, $activityService);

        if (count($this->activities)) {
            /* See app/IATI/Services/Xml/XmlGeneratorService.php for change documentation. */
            $this->publishActivities();
        }
    }

    /**
     * Initializes required services.
     *
     * @param $publishingStatusService
     * @param $activityWorkflowService
     * @param $activityService
     *
     * @return void
     */
    public function setServices($publishingStatusService, $activityWorkflowService, $activityService): void
    {
        $this->publishingStatusService = $publishingStatusService;
        $this->activityWorkflowService = $activityWorkflowService;
        $this->activityService = $activityService;
    }

    /**
     * Publishes activity and updates publish status table.
     *
     * @return void
     * @throws \Throwable
     */
    public function publishActivities(): void
    {
        try {
            DB::beginTransaction();

            $this->activityWorkflowService->publishActivities(
                $this->activities,
                $this->organization,
                $this->settings,
                $this->accessToken,
                $this->uuid
            );

            $activityIds = $this->activities->pluck('id')->toArray();
            $this->publishingStatusService->updateBulkActivityStatus($activityIds, $this->uuid, 'completed');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            logger()->error($e);
            awsUploadFile('error-bulk-publish.log', $e->getMessage());

            $activityIds = $this->activities->pluck('id')->toArray();

            $this->activityService->bulkUpdatePublishedStatus($activityIds, 'draft', false);

            $this->publishingStatusService->updateBulkActivityStatus($activityIds, $this->uuid, 'failed');
        }
    }

    /**
     * In case of job failure , set created and processing to failed.
     *
     * @return void
     */
    public function failed(): void
    {
        try {
            app(BulkPublishingStatusRepository::class)->failStuckActivities($this->organizationId);
        } catch (\Exception $e) {
            awsUploadFile('error-bulk-publish.log', $e->getMessage());
        }
    }
}
