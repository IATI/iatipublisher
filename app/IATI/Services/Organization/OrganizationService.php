<?php

declare(strict_types=1);

namespace App\IATI\Services\Organization;

use App\Constants\Enums;
use App\Exceptions\PublisherIdChangeByIatiAdminException;
use App\IATI\Models\Activity\ActivityPublished;
use App\IATI\Models\Organization\Organization;
use App\IATI\Models\Organization\OrganizationPublished;
use App\IATI\Models\Setting\Setting;
use App\IATI\Repositories\ApiLog\ApiLogRepository;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Services\Publisher\PublisherService;
use App\IATI\Traits\OrganizationXmlBaseElements;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JsonException;

/**
 * Class OrganizationService.
 */
class OrganizationService
{
    use OrganizationXmlBaseElements;

    /**
     * @var OrganizationRepository
     */
    private OrganizationRepository $organizationRepo;

    /**
     * @var ApiLogRepository
     */
    private ApiLogRepository $apiLogRepo;

    /**
     * UserService constructor.
     *
     * @param OrganizationRepository $organizationRepo
     * @param ApiLogRepository $apiLogRepo
     */
    public function __construct(OrganizationRepository $organizationRepo, ApiLogRepository $apiLogRepo)
    {
        $this->organizationRepo = $organizationRepo;
        $this->apiLogRepo = $apiLogRepo;
    }

    /**
     * Store user.
     *
     * @param array $data
     *
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->organizationRepo->store($data);
    }

    /**
     * Returns data in required xml array format.
     *
     * @param Organization $organization
     *
     * @return array
     */
    public function getReportingOrgXmlData(Organization $organization): array
    {
        $organizationData = [];
        $orgReportingOrg = (array) $organization->reporting_org;

        if (count($orgReportingOrg)) {
            foreach ($orgReportingOrg as $OrgReportingOrg) {
                $organizationData[] = [
                    '@attributes' => [
                        'type' => Arr::get($OrgReportingOrg, 'type', null),
                        'ref' => Arr::get($OrgReportingOrg, 'ref', null),
                        'secondary-reporter' => Arr::get($OrgReportingOrg, 'secondary_reporter', null),
                    ],
                    'narrative' => $this->buildNarrative(Arr::get($OrgReportingOrg, 'narrative', [])),
                ];
            }
        }

        return $organizationData;
    }

    /**
     * Returns Organization object.
     *
     * @param $id
     *
     * @return Model
     */
    public function getOrganizationData($id): Organization
    {
        return $this->organizationRepo->getOrganizationData($id);
    }

    /**
     * Returns required service file.
     *
     * @param $serviceName
     *
     * @return Application|mixed
     */
    public function getService($serviceName): mixed
    {
        return app(sprintf("App\IATI\Services\Organization\%s", $serviceName));
    }

    /**
     * Updates status column of activity row.
     *
     * @param $organization
     * @param $status
     * @param $alreadyPublished
     *
     * @return bool
     */
    public function updatePublishedStatus($organization, $status, $alreadyPublished): bool
    {
        return $this->organizationRepo->updatePublishedStatus($organization, $status, $alreadyPublished);
    }

    public function update($organization, array $data)
    {
        return $organization->update($data);
    }

    /**
     * Return organization mandatory elements progress in percentage.
     *
     * @param $organization
     *
     * @return float|int
     */
    public function organizationMandatoryCompletePercentage($organization): float|int
    {
        $mandatory_elements = getMandatoryElements();
        $completed_mandatory_element_count = 0;

        foreach ($mandatory_elements as $mandatory_element) {
            if (
                array_key_exists(
                    $mandatory_element,
                    $organization->element_status
                ) && $organization->element_status[$mandatory_element]
            ) {
                $completed_mandatory_element_count++;
            }
        }

        return round(($completed_mandatory_element_count / count($mandatory_elements)) * 100, 2);
    }

    /**
     * Returns array of dropdown elements in organization.
     *
     * @return array
     * @throws JsonException
     */
    public function getOrganizationTypes(): array
    {
        return [
            'budgetType' => getCodeList('BudgetStatus', 'Activity', false, filterDeprecated: true),
            'languages' => getCodeList('Language', 'Organization', false, filterDeprecated: true),
            'documentCategory' => getCodeList('DocumentCategory', 'Organization', false, filterDeprecated: true),
            'organizationType' => getCodeList('OrganizationType', 'Organization', false, filterDeprecated: true),
            'country' => getCodeList('Country', 'Organization', false, filterDeprecated: true),
            'regionVocabulary' => getCodeList('RegionVocabulary', 'Activity', false, filterDeprecated: true),
            'region' => getCodeList('Region', 'Activity', false, filterDeprecated: true),
        ];
    }

    /**
     * Returns organizations in paginated format.
     *
     * @param $page
     * @param $request
     *
     * @return null|LengthAwarePaginator
     */
    public function getPaginatedOrganizations($page, $request): ?LengthAwarePaginator
    {
        return $this->organizationRepo->getPaginatedOrganizations($page, $request);
    }

    /**
     * Returns list of organization name with their id.
     *
     * @return Collection
     */
    public function pluckAllOrganizations(): Collection
    {
        return $this->organizationRepo->pluckAllOrganizations();
    }

    /**
     * Check if publisher id is active in iati registry.
     *
     * @param string $publisher_id
     *
     * @return bool
     */
    public function isPublisherStateActive(string $publisher_id): bool
    {
        $clientConfig = ['base_uri' => env('IATI_API_ENDPOINT')];
        $requestConfig = [
            'http_errors' => false,
            'query' => ['id' => $publisher_id ?? ''],
        ];
        $clientConfig['headers']['X-CKAN-API-Key'] = env('IATI_API_KEY');
        $clientConfig['headers']['User-Agent'] = 'iati-publisher';

        if (env('APP_ENV') !== 'production') {
            $requestConfig['auth'] = [env('IATI_USERNAME'), env('IATI_PASSWORD')];
        }

        $client = new Client($clientConfig);
        $res = $client->request('GET', env('IATI_API_ENDPOINT') . '/action/organization_show', $requestConfig);
        $this->apiLogRepo->store(generateApiInfo('GET', env('IATI_API_ENDPOINT') . '/action/organization_show', $requestConfig, $res));

        if ($res->getStatusCode() === 404) {
            return false;
        }

        $response = json_decode($res->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
        try {
            $result = $response->result;

            if (strcasecmp($result->state, 'active') === 0) {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * Returns organization by publisher id.
     *
     * @param $publisher_id
     *
     * @return object|null
     */
    public function getOrganizationByPublisherId($publisher_id): ?object
    {
        return $this->organizationRepo->getOrganizationByPublisherId($publisher_id);
    }

    /**
     * Returns organizations by publisher ids.
     *
     * @param array $publisherIds
     *
     * @return array|Collection
     */
    public function getOrganizationByPublisherIds(array $publisherIds): array|Collection
    {
        return $this->organizationRepo->getOrganizationByPublisherIds($publisherIds);
    }

    /**
     * Returns array containing publisher stats.
     *
     * @param $queryParams
     *
     * @return array
     */
    public function getPublisherStats($queryParams): array
    {
        return $this->organizationRepo->getPublisherStats($queryParams);
    }

    /**
     * Returns array containing Organisation Type.
     *
     * @param $queryParams
     * @param $type
     *
     * @return array
     */
    public function getPublisherBy($queryParams, $type): array
    {
        return $this->organizationRepo->getPublisherBy($queryParams, $type);
    }

    public function getPublisherBySetup($queryParams): array
    {
        return $this->organizationRepo->getPublisherBySetup($queryParams);
    }

    /**
     * Update organisation element to null. (basically deleting the element).
     *
     * @param $id
     * @param $element
     *
     * @return bool
     */
    public function deleteElement($id, $element): bool
    {
        return $this->organizationRepo->update($id, [$element => null]);
    }

    /**
     * @param array $settingData
     * @throws BindingResolutionException
     * @throws GuzzleException
     * @throws JsonException
     * @throws PublisherIdChangeByIatiAdminException
     */
    public function updateBySuperadmin(array $settingData): void
    {
        $organization = $this->organizationRepo->find($settingData['organization_id']);
        $oldOrgInstance = clone $organization;
        $dbField = $organization->getFillable();
        $organizationData = Arr::only($settingData, $dbField);

        $organization->fill($organizationData);

        $publisherIdHasChanged = $organization->isDirty('publisher_id');

        if ($publisherIdHasChanged) {
            $this->beginPublisherIdChangeWorkflow($organizationData, $settingData, $oldOrgInstance, $organization);
        } else {
            $settings = $organization->settings;
            $publishingInfo = $settings->publishing_info;
            $publishingInfo['publisher_id'] = $settingData['publisher_id'];
            $publishingInfo['api_token'] = $settingData['api_token'];
            $publishingInfo['publisher_verification'] = $settingData['publisher_verification'];
            $publishingInfo['token_verification'] = $settingData['token_verification'];

            $settings->publishing_info = $publishingInfo;
            $settings->timestamps = false;
            $settings->updateQuietly();
        }

        $organization->timestamps = false;
        $organization->updateQuietly();
    }

    /**
     * @param array $orgData
     * @param array $settingsData
     * @param Organization $oldOrgInstance
     * @param Organization $org
     *
     * @throws BindingResolutionException
     * @throws GuzzleException
     * @throws JsonException
     * @throws PublisherIdChangeByIatiAdminException
     */
    private function beginPublisherIdChangeWorkflow(array $orgData, array $settingsData, Organization $oldOrgInstance, Organization $org): void
    {
        $oldPublisherId = $oldOrgInstance->publisher_id;
        $settings = $org->settings;
        $publisherId = $orgData['publisher_id'];
        $orgApiToken = Arr::get($settingsData, 'api_token', '');

        $publishingInfo = $settings->publishing_info;
        $publishingInfo['publisher_id'] = $publisherId;
        $publishingInfo['api_token'] = $orgApiToken;
        $publishingInfo['publisher_verification'] = $settingsData['publisher_verification'];
        $publishingInfo['token_verification'] = $settingsData['token_verification'];

        $settings->publishing_info = $publishingInfo;
        $settings->timestamps = false;
        $settings->updateQuietly();

        $orgPublished = $org->organizationPublished ?? [];

        if ($org->is_published || count($orgPublished)) {
            $oldOrgFilename = "$oldPublisherId-organisation.xml";
            $newOrgFilename = "$publisherId-organisation.xml";
            $oldOrgFilenameMappedToNewOrgFilename = [$oldOrgFilename => $newOrgFilename];

            $this->renameOldFilesInS3($oldOrgFilenameMappedToNewOrgFilename, Enums::ORG_XML_BASE_PATH);

            $orgPublished->timestamps = false;
            $orgPublished->filename = $newOrgFilename;
            $orgPublished->updateQuietly();

            $this->linkNewOrgFileOnRegistry($org, $settings, $orgPublished);
            $this->linkNewOrgFileOnRegistry($org, $settings, $orgPublished);
        }

        $activityPublished = $org->publishedFiles ?? [];

        if (count($activityPublished)) {
            $activityPublished = $activityPublished[0];
            $newMergedFilename = "$publisherId-activities.xml";
            $oldXmlFilenamesMappedToNewXmlFilenames = $this->getOldXmlMappedToNewXml($activityPublished, $publisherId);
            $oldMergedFilenameMappedToNewMergedFilename = [$activityPublished->filename => $newMergedFilename];

            $this->renameOldFilesInS3($oldXmlFilenamesMappedToNewXmlFilenames, Enums::ACTIVITY_XML_BASE_PATH);
            $this->renameOldFilesInS3($oldMergedFilenameMappedToNewMergedFilename, Enums::MERGED_XML_BASE_PATH);

            $activityPublished->timestamps = false;
            $activityPublished->filename = $newMergedFilename;
            $activityPublished->published_activities = array_values($oldXmlFilenamesMappedToNewXmlFilenames);
            $activityPublished->updateQuietly();

            $this->linkNewMergedFileOnRegistry($org, $settings, $activityPublished);
            $this->linkNewMergedFileOnRegistry($org, $settings, $activityPublished);
        }
    }

    /**
     * Renames old files in Amazon S3.
     *
     * @param array<string, string> $fromMappedToTo An associative array where keys represent old file names and values represent new file names.
     * @param string $basePath The base path in Amazon S3 where the files are located.
     *
     * @return void
     */
    protected function renameOldFilesInS3(array $fromMappedToTo, string $basePath): void
    {
        foreach ($fromMappedToTo as $from => $to) {
            awsMoveFile("$basePath/$from", "$basePath/$to");
        }
    }

    /**
     * @param ActivityPublished $activityPublished
     * @param string $publisherId
     *
     * @return array<string, string> An associative array where keys represent old file names and values represent new file names.
     */
    protected function getOldXmlMappedToNewXml(ActivityPublished $activityPublished, string $publisherId): array
    {
        $returnMap = [];
        $publishedActivities = $activityPublished->published_activities;

        foreach ($publishedActivities as $oldActivityFilename) {
            $activityId = getFileIdentifier($oldActivityFilename);
            $newActivityFilename = "$publisherId-$activityId.xml";

            $returnMap[$oldActivityFilename] = $newActivityFilename;
        }

        return $returnMap;
    }

    /**
     * @param LengthAwarePaginator|null $rawPaginatedData
     *
     * @return LengthAwarePaginator|null
     *
     * @throws JsonException
     */
    private function resolvePaginatedOrganizationData(?LengthAwarePaginator $rawPaginatedData): ?LengthAwarePaginator
    {
        $publisherTypeList = getCodeList('OrganizationType', 'Organization', filterDeprecated: true);
        $dataLicenseList = getCodeList('DataLicense', 'Activity', false, filterDeprecated: true);
        $countryList = getCodeList('Country', 'Activity', false, filterDeprecated: true);

        foreach ($rawPaginatedData as $organization) {
            $organization->publisher_type = $organization->publisher_type ? Arr::get($publisherTypeList, $organization->publisher_type, 'Not available') : 'Not available';
            $organization->data_license = $organization->data_license ? Arr::get($dataLicenseList, $organization->data_license, 'Not available') : 'Not available';
            $organization->country = $organization->country ? Arr::get($countryList, $organization->country, 'Not available') : 'Not available';
        }

        return $rawPaginatedData;
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    private function unlinkOldFilesFromRegistry(string $oldPublisherId, string $orgApiToken, string $filetype): bool
    {
        /** @var PublisherService $publisherService */
        $publisherService = app()->make(PublisherService::class);
        $files = ["$oldPublisherId-$filetype"];

        return $publisherService->unlink($orgApiToken, $files);
    }

    /**
     * @throws BindingResolutionException
     */
    private function linkNewOrgFileOnRegistry(Organization $org, Setting $settings, OrganizationPublished $orgPublished): void
    {
        /** @var PublisherService $publisherService */
        $publisherService = app()->make(PublisherService::class);
        $publisherService->publishOrganizationFile(
            $settings->publishing_info,
            $orgPublished,
            $org,
            false
        );
    }

    /**
     * @param Organization $org
     * @param ActivityPublished $activityPublished
     * @param Setting $settings
     * @return void
     * @throws BindingResolutionException
     */
    private function linkNewMergedFileOnRegistry(Organization $org, Setting $settings, ActivityPublished $activityPublished)
    {
        /** @var PublisherService $publisherService */
        $publisherService = app()->make(PublisherService::class);
        $publisherService->publishFile(
            $settings->publishing_info,
            $activityPublished,
            $org,
            false
        );
    }
}
