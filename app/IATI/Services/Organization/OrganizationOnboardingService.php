<?php

declare(strict_types=1);

namespace App\IATI\Services\Organization;

use App\Constants\Enums;
use App\IATI\Models\Organization\Organization;
use App\IATI\Models\Organization\OrganizationOnboarding;
use App\IATI\Repositories\Organization\OrganizationOnboardingRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class OrganizationOnboardingService.
 */
class OrganizationOnboardingService
{
    /**
     * OrganizationOnboardingService constructor.
     *
     * @param  OrganizationOnboardingRepository  $organizationOnboardingRepository
     */
    public function __construct(
        private OrganizationOnboardingRepository $organizationOnboardingRepository
    ) {
    }

    /**
     * Returns organization onboarding information.
     *
     * @param $organization_id
     *
     * @return object|null
     */
    public function getOrganizationOnboarding($organization_id): ?object
    {
        return $this->organizationOnboardingRepository->getOrganizationOnboarding($organization_id);
    }

    /**
     * Creates and stores organization onboarding data.
     *
     * @param $organization
     *
     * @return Model
     */
    public function createOrganizationOnboarding($organization): Model
    {
        $data = [];
        $data['org_id'] = $organization->id;
        $data['steps_status'] = $this->getOrganizationOnboardingStepsStatus($organization);
        $data['dont_show_again'] = false;
        $data['completed_onboarding'] = collect($data['steps_status'])->every(
            fn ($item) => Arr::get($item, 'complete', false)
        );

        return $this->create($data);
    }

    /**
     * Returns organization onboarding steps status.
     *
     * @param $organization
     *
     * @return array
     */
    public function getOrganizationOnboardingStepsStatus($organization): array
    {
        $array = [];
        $settings = $organization->settings;

        if (!$settings) {
            return [
                [
                    'step'     => 1,
                    'title'    => OrganizationOnboarding::PUBLISHING_SETTINGS,
                    'complete' => false,
                ],
                [
                    'step'     => 2,
                    'title'    => OrganizationOnboarding::DEFAULT_VALUES,
                    'complete' => false,
                ],
                [
                    'step'     => 3,
                    'title'    => OrganizationOnboarding::ORGANIZATION_DATA,
                    'complete' => false,
                ],
                [
                    'step'     => 4,
                    'title'    => OrganizationOnboarding::ACTIVITY,
                    'complete' => false,
                ],
            ];
        }

        $array[] = [
            'step'     => 1,
            'title'    => OrganizationOnboarding::PUBLISHING_SETTINGS,
            'complete' => $this->checkPublishingSettingsComplete($settings->publishing_info),
        ];
        $array[] = [
            'step'     => 2,
            'title'    => OrganizationOnboarding::DEFAULT_VALUES,
            'complete' => $this->checkDefaultValuesComplete($settings->default_values),
        ];
        $array[] = [
            'step'     => 3,
            'title'    => OrganizationOnboarding::ORGANIZATION_DATA,
            'complete' => $organization->is_published,
        ];
        $array[] = [
            'step'     => 4,
            'title'    => OrganizationOnboarding::ACTIVITY,
            'complete' => ($organization->registration_type === Enums::EXISTING_ORG) && count($organization->activities),
        ];

        return $array;
    }

    /**
     * Checks if present publishing settings information is complete or not.
     *
     * @param $publishingInfo
     *
     * @return bool
     */
    public function checkPublishingSettingsComplete($publishingInfo): bool
    {
        if (!$publishingInfo) {
            return false;
        }

        return Arr::get($publishingInfo, 'publisher_id') &&
            Arr::get($publishingInfo, 'api_token') &&
            in_array(Arr::get($publishingInfo, 'token_status'), [Enums::TOKEN_CORRECT, Enums::TOKEN_PENDING], true);
    }

    /**
     * Checks if default values are completed or not.
     *
     * @param $defaultValues
     *
     * @return bool
     */
    public function checkDefaultValuesComplete($defaultValues): bool
    {
        if (!$defaultValues) {
            return false;
        }

        return Arr::get($defaultValues, 'default_currency') &&
            Arr::get($defaultValues, 'default_language');
    }

    /**
     * Stores organization onboarding data.
     *
     * @param $data
     *
     * @return Model
     */
    public function create($data): Model
    {
        return $this->organizationOnboardingRepository->store($data);
    }

    /**
     * Updates status of a step if required.
     *
     * @param $organizationId
     * @param $stepName
     * @param  bool  $value
     *
     * @return void
     */
    public function updateOrganizationOnboardingStepToComplete($organizationId, $stepName, bool $value = true): void
    {
        $update = false;
        $organizationOnboarding = $this->getOrganizationOnboarding($organizationId);
        $stepsStatus = $organizationOnboarding->steps_status;

        foreach ($stepsStatus as $key => $step) {
            if ($step['title'] === $stepName && $step['complete'] !== $value) {
                $stepsStatus[$key]['complete'] = $value;
                $update = true;
                break;
            }
        }

        if ($update) {
            $complete = true;

            foreach ($stepsStatus as $step) {
                if (!$step['complete']) {
                    $complete = false;
                    break;
                }
            }

            $organizationOnboarding->completed_onboarding = $complete;
            $organizationOnboarding->steps_status = $stepsStatus;
            $organizationOnboarding->save();
        }
    }

    /**
     * Updates don't show again if required.
     *
     * @param $organizationId
     * @param $value
     *
     * @return void
     */
    public function updateDontShowAgain($organizationId, $value): void
    {
        $organizationOnboarding = $this->getOrganizationOnboarding($organizationId);

        if ($organizationOnboarding->dont_show_again !== $value) {
            $organizationOnboarding->dont_show_again = $value;
            $organizationOnboarding->save();
        }
    }

    /**
     * Since onboarding titles are pulled from db and rendered, we need to translate the step titles.
     *
     * @param OrganizationOnboarding $organizationOnboarding
     *
     * @return array
     */
    public function translateOrganisationOnboardingTitles(OrganizationOnboarding $organizationOnboarding): array
    {
        $organizationOnboarding = $organizationOnboarding->toArray();

        foreach ($organizationOnboarding['steps_status'] as &$onboardingStep) {
            $onboardingStep['title'] = match ($onboardingStep['step']) {
                1 => Str::title(trans('common/common.publishing_settings')),
                2 => Str::title(trans('common/common.default_values')),
                3 => Str::title(trans('adminHeader/admin_header.organisation_data')),
                4 => Str::title(trans('common/common.activity')),
            };
        }

        return $organizationOnboarding;
    }
}
