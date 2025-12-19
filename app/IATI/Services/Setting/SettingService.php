<?php

declare(strict_types=1);

namespace App\IATI\Services\Setting;

use App\IATI\Models\Setting\Setting;
use App\IATI\Repositories\Setting\SettingRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * Class SettingService.
 */
class SettingService
{
    /**
     * @var SettingRepository
     */
    private SettingRepository $settingRepo;

    /**
     * Sett constructor.
     *
     * @param SettingRepository $settingRepo
     */
    public function __construct(SettingRepository $settingRepo)
    {
        $this->settingRepo = $settingRepo;
    }

    /**
     * Store user.
     *
     * @return Setting|null
     */
    public function getSetting(): ?Setting
    {
        return $this->settingRepo->findByOrganization(Auth::user()->organization_id);
    }

    /**
     * Store default values.
     *
     * @param array $data
     *
     * @return Model
     */
    public function storeDefaultValues(array $data): Model
    {
        return $this->settingRepo->updateSetting(Auth::user()->organization_id, [
            'organization_id'         => Auth::user()->organization_id,
            'default_values'          => [
                'default_currency' => $data['default_currency'] ?? '',
                'default_language' => $data['default_language'],
            ],
            'activity_default_values' => [
                'hierarchy' => $data['hierarchy'] ?? 1,
                'humanitarian' => Arr::get($data, 'humanitarian', ''),
                'budget_not_provided' => $data['budget_not_provided'] ?? '',
                'linked_data_uri' => $data['linked_data_uri'] ?? '',
                'default_collaboration_type' => $data['default_collaboration_type'] ?? '',
                'default_flow_type' => $data['default_flow_type'] ?? '',
                'default_finance_type' => $data['default_finance_type'] ?? '',
                'default_aid_type' => $data['default_aid_type'] ?? '',
                'default_tied_status' => $data['default_tied_status'] ?? '',
            ],
        ]);
    }

    /**
     * Return array containing status of default and publisher information.
     *
     * @return array
     */
    public function getStatus(): array
    {
        $setting = $this->getSetting();

        return [
            'default_status'   => $setting && $this->defaultSettingsCompleted($setting->default_values, $setting->activity_default_values),
            'publisher_status' => $setting && $setting['publishing_info']
                ? ($setting['publishing_info']['api_token'] && $setting['publishing_info']['token_verification'] ? true : false)
                : false,
            'token_status' => Arr::get($setting, 'publishing_info.token_verification', false),
        ];
    }

    /**
     * Checks if default settings is completed or not.
     *
     * @param $default_values
     * @param $activity_default_values
     *
     * @return bool
     */
    public function defaultSettingsCompleted($default_values, $activity_default_values): bool
    {
        if (!$default_values || !$activity_default_values) {
            return false;
        }

        $propertiesThatDoNotAffectCompletion = [
            'default_collaboration_type',
            'budget_not_provided',
            'linked_data_uri',
        ];

        $propertiesThatAffectCompletion = [
            'hierarchy',
            'default_flow_type',
            'default_finance_type',
            'default_aid_type',
            'default_tied_status',
            'humanitarian',
        ];

        $activity_default_values = Arr::except($activity_default_values, $propertiesThatDoNotAffectCompletion);

        $completeLanguageAndCurrency = Arr::get($default_values, 'default_language', false) && Arr::get($default_values, 'default_currency', false);
        $completeActivityDefaults = Arr::has($activity_default_values, $propertiesThatAffectCompletion)
            && !empty($activity_default_values['hierarchy'])
            && !empty($activity_default_values['default_flow_type'])
            && !empty($activity_default_values['default_finance_type'])
            && !empty($activity_default_values['default_aid_type'])
            && !empty($activity_default_values['default_tied_status'])
            && isset($activity_default_values['humanitarian']);

        return $completeLanguageAndCurrency && $completeActivityDefaults;
    }

    /**
     * Saves setting to table.
     *
     * @param $data
     *
     * @return Model
     */
    public function create($data): Model
    {
        return $this->settingRepo->store($data);
    }
}
