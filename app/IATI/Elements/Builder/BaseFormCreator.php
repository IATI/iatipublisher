<?php

declare(strict_types=1);

namespace App\IATI\Elements\Builder;

use App\IATI\Services\Setting\SettingService;
use Illuminate\Support\Arr;
use Kris\LaravelFormBuilder\Form;
use Kris\LaravelFormBuilder\FormBuilder;

/**
 * Class BaseFormCreator.
 */
class BaseFormCreator
{
    /**
     * @var string
     */
    public string $url;

    /**
     * @var FormBuilder
     */
    protected FormBuilder $formBuilder;

    /**
     * @var SettingService
     */
    protected SettingService $settingService;

    /**
     * BaseFormCreator constructor.
     *
     * @param FormBuilder $formBuilder
     */
    public function __construct(FormBuilder $formBuilder, SettingService $settingService)
    {
        $this->formBuilder = $formBuilder;
        $this->settingService = $settingService;
    }

    /**
     * Returns activity title edit form.
     *
     * @param array $model
     * @param $formData
     * @param $method
     * @param string $parent_url
     * @param bool $showCancelOrSaveButton
     * @param array $additonalInfo
     * @param array $overRideDefaultFieldValue
     *
     * @return Form
     */
    public function editForm(array $model, $formData, $method, string $parent_url, bool $showCancelOrSaveButton = true, array $additonalInfo = [], $overRideDefaultFieldValue = [], $deprecationStatusMap = []): Form
    {
        $formData['overRideDefaultFieldValue'] = $overRideDefaultFieldValue;
        $formData['deprecationStatusMap'] = $deprecationStatusMap;

        $settingsDefaultValue = ($this->settingService->getSetting()->default_values ?? []) + ($this->settingService->getSetting()->activity_default_values ?? []);

        if (!empty($overRideDefaultFieldValue) && count($overRideDefaultFieldValue)) {
            foreach ($overRideDefaultFieldValue as $key => $value) {
                if (!empty($value)) {
                    $settingsDefaultValue[$key] = $value;
                }
            }
        }
        $formData['overRideDefaultFieldValue'] = $settingsDefaultValue;

        $form = $this->formBuilder->create(
            'App\IATI\Elements\Forms\BaseForm',
            [
                'method' => $method,
                'model'  => $model,
                'url'    => $this->url,
                'data'   => $formData,
                'id'     => empty($additonalInfo) ? '' : Arr::get($additonalInfo, 'formId'),
            ]
        );

        if ($showCancelOrSaveButton) {
            $form->add('buttons', 'buttongroup', [
                'wrapper' => [
                    'class' => 'fixed left-0 bottom-0 w-full bg-eggshell py-5 pr-20 xl:pr-40 shadow-dropdown z-10',
                ],
                'buttons' => [
                    'clear'    => [
                        'label'     => trans('common/common.cancel'),
                        'attr'      => [
                            'type'  => 'anchor',
                            'class' => 'ghost-btn mr-8',
                            'href'  => $parent_url,
                        ],
                    ],
                    'submit'    => [
                        'label'     => trans('common/common.save_and_exit'),
                        'attr'      => [
                            'type'  => empty($additonalInfo) ? 'submit' : 'button',
                            'class' => 'primary-btn save-btn text-sm',
                            'id'    => empty($additonalInfo) ? '' : Arr::get($additonalInfo, 'submitId'),
                        ],
                    ],
                ],
            ]);
        }

        return $form;
    }
}
