<?php

declare(strict_types=1);

namespace App\IATI\Services\Publisher;

use App\IATI\API\CkanClient;
use App\IATI\Services\Activity\ActivityPublishedService;
use App\IATI\Services\Organization\OrganizationPublishedService;
use App\IATI\Services\Workflow\RegistryApiHandler;
use App\IATI\Traits\RegistryApiInvoker;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Class PublisherService.
 */
class PublisherService extends RegistryApiHandler
{
    use RegistryApiInvoker;

    /**
     * @var Model|null
     */
    protected $activityPublished = null;
    protected $organizationPublished = null;

    /**
     * PublisherService constructor.
     *
     * @param ActivityPublishedService $activityPublishedService
     * @param OrganizationPublishedService $organizationPublishedService
     */
    public function __construct(ActivityPublishedService $activityPublishedService, OrganizationPublishedService $organizationPublishedService)
    {
        $this->activityPublishedService = $activityPublishedService;
        $this->organizationPublishedService = $organizationPublishedService;
    }

    /**
     * Publishes the activity xml file to the IATI registry.
     *
     * @param $registryInfo
     * @param $activityPublished
     * @param $organization
     * @param  bool  $updatedActivityPublished
     *
     * @return void
     *
     * @throws \Exception
     */
    public function publishFile($registryInfo, $activityPublished, $organization, bool $updatedActivityPublished = true): void
    {
        $this->setFile($activityPublished);
        $this->init(env('IATI_API_ENDPOINT'), Arr::get($registryInfo, 'api_token', ''))
            ->setPublisher(Arr::get($registryInfo, 'publisher_id', ''));
        $this->searchForPublisher($this->publisherId);
        $this->publishToRegistry($organization, $activityPublished->filename, $updatedActivityPublished);
    }

    /**
     * Publishes the organization xml file to the IATI registry.
     *
     * @param $registryInfo
     * @param $organizationPublished
     * @param $organization
     * @param  bool  $updateActivityPublished
     *
     * @return void
     *
     * @throws \Exception
     */
    public function publishOrganizationFile($registryInfo, $organizationPublished, $organization, bool $updateOrganizationPublished = true): void
    {
        $this->organizationPublished = $organizationPublished;
        $this->init(env('IATI_API_ENDPOINT'), Arr::get($registryInfo, 'api_token', ''))
            ->setPublisher(Arr::get($registryInfo, 'publisher_id', ''));
        $this->searchForPublisher($this->publisherId);
        $this->publishOrganizationToRegistry($organization, $organizationPublished->filename, $updateOrganizationPublished);
    }

    /**
     * Publishes the organization xml file to the IATI registry.
     *
     * @param $registryInfo
     * @param $organizationPublished
     * @param  bool  $updateOrganizationPublished
     *
     * @return void
     *
     * @throws \Exception
     */
    public function unpublishOrganizationFile($registryInfo, $organizationPublished, bool $updateOrganizationPublished = true): void
    {
        $this->organizationPublished = $organizationPublished;
        $this->init(env('IATI_API_ENDPOINT'), Arr::get($registryInfo, 'api_token', ''))
            ->setPublisher(Arr::get($registryInfo, 'publisher_id', ''));
        $this->searchForPublisher($this->publisherId);
        $this->unpublishOrganizationFromRegistry($organizationPublished->filename, $updateOrganizationPublished);
    }

    /**
     * Set the file attribute.
     *
     * @param $activityPublished
     *
     * @return void
     */
    protected function setFile($activityPublished): void
    {
        $this->activityPublished = $activityPublished;
    }

    /**
     * Publish File to the IATI Registry.
     *
     * @param $organization
     * @param $filename
     * @param  bool  $updateActivityPublished
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function publishOrganizationToRegistry($organization, $filename, bool $updateOrganizationPublished = true): void
    {
        $data = $this->generateOrganizationPayload($organization, $filename);

        if ($this->isPackageAvailable($this->extractPackage($filename), $this->apiKey)) {
            $this->client->package_update($data);
        } else {
            $this->client->package_create($data);
        }

        if ($updateOrganizationPublished) {
            $this->organizationPublishedService->updateStatus($this->organizationPublished->id, true);
        }
    }

    /**
     * Publish File to the IATI Registry.
     *
     * @param $organization
     * @param $filename
     * @param  bool  $updateActivityPublished
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function publishToRegistry($organization, $filename, bool $updateActivityPublished = true): void
    {
        $data = $this->generatePayload($organization, $filename);

        if ($this->isPackageAvailable($this->extractPackage($filename), $this->apiKey)) {
            $this->client->package_update($data);
        } else {
            $this->client->package_create($data);
        }

        if ($updateActivityPublished) {
            $this->activityPublishedService->updateStatus($this->activityPublished);
        }
    }

    /**
     * Unpublish File from the IATI Registry.
     *
     * @param $filename
     * @param  bool  $updateOrganizationPublished
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function unpublishOrganizationFromRegistry($filename, bool $updateOrganizationPublished = true): void
    {
        $packageId = $this->extractPackage($filename);

        if ($this->isPackageAvailable($packageId, $this->apiKey)) {
            $this->client->package_delete($packageId);
        }

        if ($updateOrganizationPublished) {
            $this->organizationPublishedService->updateStatus($this->organizationPublished->id, false);
        }
    }

    /**
     * Returns the request header payload while publishing any files to the IATI Registry.
     *
     * @param      $organization
     * @param      $filename
     * @param      $publishingType
     * @param null $publishedFile
     *
     * @return string
     */
    protected function generatePayload($organization, $filename): string
    {
        $code = $this->getCode($filename);
        $key = $this->getKey($code);
        $fileType = $this->getFileType($code);
        $title = $this->extractTitle($organization, $fileType);

        return $this->formatHeaders($this->extractPackage($filename), $organization, $this->activityPublished, $key, $fileType, $title);
    }

    /**
     * Returns the request header payload while publishing any files to the IATI Registry.
     *
     * @param      $organization
     * @param      $filename
     * @param      $publishingType
     * @param null $publishedFile
     *
     * @return string
     */
    protected function generateOrganizationPayload($organization, $filename): string
    {
        $code = $this->getCode($filename);
        $key = $this->getKey($code);
        $fileType = $this->getFileType($code);
        $title = $this->extractTitle($organization, $fileType);

        return $this->formatOrganizationHeaders($this->extractPackage($filename), $organization, $this->organizationPublished, $key, $fileType, $title);
    }

    /**
     * Get the data type or country code/region code from the filename.
     *
     * @param $filename
     *
     * @return string
     */
    protected function getCode($filename): string
    {
        return substr(str_replace('.xml', '', $filename), strlen($this->publisherId) + 1);
    }

    /**
     * Get the required key for the code provided.
     *
     * @param $code
     *
     * @return string
     */
    protected function getKey($code): string
    {
        if ($code == '998') {
            return 'Others';
        } elseif (is_numeric($code)) {
            return 'region';
        }

        return 'country';
    }

    /**
     * Returns the type of file.
     *
     * @param $code
     *
     * @return mixed|string
     */
    protected function getFileType($code): mixed
    {
        if ($code === 'org' || $code === 'organisation') {
            return 'organisation';
        }

        return $code;
    }

    /**
     * Extract title for the file being published.
     *
     * @param $organization
     * @param $fileType
     *
     * @return string
     */
    protected function extractTitle($organization, $fileType): string
    {
        if ($fileType === 'organisation') {
            return $organization->publisher_name . ' Organisation File';
        }

        return $organization->publisher_name . ' Activity File';
    }

    /**
     * Extract the package name from the published filename.
     *
     * @param $filename
     *
     * @return string
     */
    protected function extractPackage($filename): string
    {
        return Arr::get(explode('.', $filename), 0, '');
    }

    /**
     * Format headers required to publish into the IATI Registry.
     *
     * @param $filename
     * @param $organization
     * @param $publishedFile
     * @param $key
     * @param $code
     * @param $title
     *
     * @return string
     */
    protected function formatOrganizationHeaders($filename, $organization, $publishedFile, $key, $code, $title): string
    {
        $data = [
            'title'        => $title,
            'name'         => $filename,
            'author_email' => $organization->getAdminUser()->email,
            'owner_org'    => $this->publisherId,
            'license_id'   => 'other-open',
            'resources'    => [
                [
                    'format'   => 'IATI-XML',
                    'mimetype' => 'application/xml',
                    'url'      => awsUrl('/organizationXmlFiles/' . $filename . '.xml'),
                ],
            ],
            'filetype'     => 'organisation',
            $key           => ($code == 'activities' || $code == 'organisation') ? '' : $code,
            'data_updated' => is_string($publishedFile->updated_at)
                ? (Carbon::parse($publishedFile->updated_at))->toDateTimeString()
                : $publishedFile->updated_at->toDateTimeString(),
            'language'     => 'en',
            'verified'     => 'no',
            'state'        => 'active',
        ];

        return json_encode($data);
    }

    /**
     * Format headers required to publish into the IATI Registry.
     *
     * @param $filename
     * @param $organization
     * @param $publishedFile
     * @param $key
     * @param $code
     * @param $title
     *
     * @return string
     */
    protected function formatHeaders($filename, $organization, $publishedFile, $key, $code, $title): string
    {
        $data = [
            'title'        => $title,
            'name'         => $filename,
            'author_email' => $organization->getAdminUser()->email,
            'owner_org'    => $this->publisherId,
            'license_id'   => 'other-open',
            'resources'    => [
                [
                    'format'   => 'IATI-XML',
                    'mimetype' => 'application/xml',
                    'url'      => awsUrl('xml/mergedActivityXml/' . $publishedFile->filename),
                ],
            ],
            'filetype'     => ($code != 'organisation') ? 'activity' : $code,
            $key           => ($code == 'activities' || $code == 'organisation') ? '' : $code,
            'data_updated' => is_string($publishedFile->updated_at)
                ? (Carbon::parse($publishedFile->updated_at))->toDateTimeString()
                : $publishedFile->updated_at->toDateTimeString(),
            'language'     => 'en',
            'verified'     => 'no',
            'state'        => 'active',
        ];

        if ($code != 'organisation') {
            $data['activity_count'] = count($publishedFile->published_activities);
        }

        return json_encode($data);
    }

    /**
     * Check if the package is already present in IATI Registry.
     * Returns true if package is present/deleted.
     *
     * @param $packageId
     * @param $apiKey
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function isPackageAvailable($packageId, $apiKey): bool
    {
        try {
            $response = json_decode($this->request('package_show', $packageId, $apiKey), true);

            if (Arr::get($response, 'result.state') == 'deleted') {
                return true;
            }

            return Arr::get($response, 'success') === true;
        } catch (\Exception $exception) {
            if ($exception instanceof ClientException) {
                if ($exception->getResponse()->getStatusCode() == 404) {
                    return false;
                }

                throw  $exception;
            }

            throw  $exception;
        }
    }

    /**
     * Unlink files from the IATI Registry.
     *
     * @param $apiKey
     * @param $files
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function unlink($apiKey, $files): bool
    {
        try {
            $api = new CkanClient(env('IATI_API_ENDPOINT'), $apiKey);

            if (count($files)) {
                foreach ($files as $file) {
                    if ($this->isPackageAvailable($file, $this->apiKey)) {
                        $api->package_delete($file);
                    }
                }
            }

            return true;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
