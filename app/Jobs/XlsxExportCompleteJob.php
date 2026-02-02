<?php

declare(strict_types=1);

namespace App\Jobs;

use App\IATI\Models\Download\DownloadStatus;
use App\IATI\Services\Download\DownloadXlsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class XlsExportMailJob.
 */
class XlsxExportCompleteJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Stores Email.
     *
     * @var string
     */
    public string $email;

    /**
     * Stores user id.
     *
     * @var int
     */
    public int $userId;

    /**
     * Stores statusID of download.
     *
     * @var int
     */
    public int $statusId;

    /**
     * Create a new job instance.
     *
     * @param $email
     * @param $userId
     * @param $statusId
     *
     * @return void
     */
    public function __construct($email, $userId, $statusId)
    {
        $this->email = $email;
        $this->userId = $userId;
        $this->statusId = $statusId;
    }

    /**
     * After zip file is uploaded in aws, a download link is sent to the user in mail.
     *
     * @throws BindingResolutionException
     *
     * @return void
     */
    public function handle(): void
    {
        if (empty(awsGetFile("Xls/$this->userId/$this->statusId/cancelStatus.json"))) {
            $updateData = ['status' => 'completed'];

            DownloadStatus::where('id', $this->statusId)->update($updateData);
        }
    }

    /**
     * If Export mail jobs fails to send a mail to user then update download progress as completed.
     *
     * @throws BindingResolutionException
     *
     * @return void
     */
    public function failed(): void
    {
        $downloadXlsService = app()->make(DownloadXlsService::class);
        $downloadStatusData = [
            'status' => 'completed',
            'url' => route('admin.activities.download-xls'),
        ];
        $downloadXlsService->updateDownloadStatus($this->statusId, $downloadStatusData);
    }
}
