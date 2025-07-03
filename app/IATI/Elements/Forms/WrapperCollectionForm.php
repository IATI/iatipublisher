<?php

declare(strict_types=1);

namespace App\IATI\Elements\Forms;

use Illuminate\Support\Arr;
use Kris\LaravelFormBuilder\Form;

/**
 * Class WrapperCollectionForm.
 */
class WrapperCollectionForm extends Form
{
    /**
     * Builds wrapper collection form.
     *
     * @return mixed|void
     */
    public function buildForm(): void
    {
        $data = $this->getData();
        $this->setClientValidationEnabled(false);

        if (isset($data['attributes']) && $data['attributes']) {
            $attributes = $data['attributes'];

            foreach ($attributes as $attribute) {
                if (is_array($attribute)) {
                    $attribute['overRideDefaultFieldValue'] = $data['overRideDefaultFieldValue'] ?? [];
                    $this->buildFields($attribute);
                }
            }
        }

        foreach ($data['sub_elements'] as $field) {
            if (count(Arr::get($field, 'sub_elements', []))) {
                $this->buildCollection($field);
            } else {
                $this->add(
                    $field['name'],
                    'collection',
                    [
                        'type'           => 'form',

                        'property'       => 'name',
                        'prototype'      => true,
                        'prototype_name' => '__NAME__',
                        'options'        => [
                            'class'           => 'App\IATI\Elements\Forms\SubElementForm',
                            'data'            => $field,
                            'label'           => false,
                            'element_criteria' => Arr::get($field, 'element_criteria', ''),
                            'hover_text' => Arr::get($field, 'hover_text', ''),
                            'help_text' => Arr::get($field, 'help_text', ''),
                            'helper_text' => Arr::get($field, 'helper_text', ''),
                            'is_collapsable'   => Arr::get($field, 'is_collapsable', ''),
                            'label_indicator'  => Arr::get($field, 'label_indicator', ),
                            'wrapper'         => ['class' => $this->getSubElementFormWrapperClass($field, $data)],
                            'dynamic_wrapper' => ['class' => $this->getSubElementFormDynamicWrapperClasses($field, $data)],
                        ],
                    ]
                );

                $name = isset($field['name']) ? $field['name'] : $data['name'];

                if (isset($field['add_more']) && $field['add_more']) {
                    $addMoreButtonClass = 'add_to_collection add_more button one relative my-3 pl-6 text-xs font-bold uppercase leading-normal text-spring-50 text-bluecoral w-full';

                    if (Arr::get($field, 'add_more_has_borders')) {
                        $addMoreButtonClass = $addMoreButtonClass . getAddAdditionalButtonBorders();
                    }

                    $this->add('add_to_collection_' . $name, 'button', [
                        'label' => generateAddAdditionalLabel($data['name'], Arr::get($data, 'attributes', null) ? $field['name'] : $data['name']),
                        'attr'  => [
                            'class'     => $addMoreButtonClass,
                            'form_type' => $data['parent'] . '_' . $data['name'] . '_' . $field['name'],
                            'icon'      => true,
                        ],
                    ]);
                }
            }
        }

        if (isset($data['add_more']) && $data['add_more']) {
            $name = $data['name'];
            $this->add(trans('common/common.delete_this') . $data['name'], 'button', [
                'attr' => [
                    'class' => 'delete-parent two text-crimson-40 font-bold text-md uppercase absolute right-0 -bottom-[1rem] w-[100%] justify-end pr-6 ' . " delete-parent-item-$name delete-parent-item delete-parent-selector",

                ],
            ]);
        }
    }

    /**
     * Builds form field.
     *
     * @param $field
     *
     * @return void
     */
    public function buildFields($field): void
    {
        $options = [
            'label'       => $field['label'] ?? '',
            'help_block'  => [
                'text' => $field['help_text'] ?? '',
                'title' => $field['label'],
                'show_full_help_text'=>Arr::get($field, 'show_full_help_text', ''),
            ],
            'hover_block' => [
                'title' => $field['label'],
                'text'  => $field['hover_text'] ?? '',
            ],
            'required'    => $field['required'],
            'multiple'    => $field['multiple'] ?? false,
            'attr'        => [
                'class' => 'form__input border-0',
                'placeholder' => Arr::get($field, 'placeholder', ''),
            ],
            'wrapper'     => [
                'class' => 'form-field basis-auto w-full xl:min-w-[300px] xl:basis-6/12 sub-attribute',
            ],
        ];

        if (array_key_exists('type', $field) && $field['type'] == 'select') {
            $deprecationStatusMap = $this->getData()['deprecationStatusMap'];
            $defaultValue = getDefaultValue($field['overRideDefaultFieldValue'], $field['name'], $field['choices'] ?? []);
            $options['attr']['class'] = 'select2';
            $options['attr']['class'] .= !empty($defaultValue) ? ' default-value-indicator' : '';
            $options['attr']['data-placeholder'] = $defaultValue ?? Arr::get($field, 'placeholder', '');
            $options['empty_value'] = $field['empty_value'] ?? trans('common/common.select_an_option');
            $options['choices'] = $field['choices']
                ? (is_string($field['choices'])
                    ? ($this->getCodeList(
                        $field['choices'],
                        deprecationStatusMap: flattenArrayWithKeys($deprecationStatusMap)
                    ))
                    : $field['choices'])
                : false;
            $options['default_value'] = $field['default'] ?? '';
        }

        $this
            ->add(
                $field['name'],
                $field['type'],
                $options
            );
    }

    /**
     * Return codeList array from json codeList.
     * @param string $filePath
     * @param bool $code
     * @return array
     */
    public function getCodeList(string $filePath, bool $code = true, $deprecationStatusMap = []): array
    {
        $completePath = "AppData/Data/$filePath";
        $codeListFromFile = getJsonFromSource($completePath);
        $codeLists = json_decode($codeListFromFile, true);
        $codeList = last($codeLists);

        $possibleSuffixes = getKeysThatUseThisCodeList($completePath);
        $deprecatedCodesInUse = filterArrayByKeyEndsWithPossibleSuffixes($deprecationStatusMap, $possibleSuffixes);

        $codeList = array_filter($codeList, function ($item) use ($deprecatedCodesInUse) {
            return filterDeprecated($item, $deprecatedCodesInUse);
        });

        foreach ($codeList as &$item) {
            if (Arr::get($item, 'status', false) !== 'active' && in_array(Arr::get($item, 'code', ''), $deprecatedCodesInUse)) {
                $item['name'] = $item['name'] . ' (used)';
            }
        }

        $data = [];

        foreach ($codeList as $list) {
            $data[$list['code']] = ($code) ? $list['code'] . (array_key_exists(
                'name',
                $list
            ) ? ' - ' . $list['name'] : '') : $list['name'];
        }

        return $data;
    }

    /**
     * @param $field
     *
     * @return void
     */
    public function buildCollection($field): void
    {
        $element = $this->getData();

        $this->add(
            $field['name'],
            'collection',
            [
                'type'           => 'form',
                'property'       => 'name',
                'prototype'      => true,
                'prototype_name' => '__NAME__',
                'options'        => [
                    'class'           => 'App\IATI\Elements\Forms\WrapperCollectionForm',
                    'data'            => $field,
                    'label'           => false,
                    'element_criteria' => Arr::get($field, 'element_criteria', ''),
                    'hover_text' => Arr::get($field, 'hover_text', ''),
                    'help_text' => Arr::get($field, 'help_text', ''),
                    'helper_text' => Arr::get($field, 'helper_text', ''),
                    'is_collapsable'   => Arr::get($field, 'is_collapsable', ''),
                    'label_indicator'  => Arr::get($field, 'label_indicator', ),
                    'wrapper'         => ['class' => $this->getWrapperCollectionFormWrapperClasses()],
                    'dynamic_wrapper' => ['class' => $this->getWrapperCollectionFormDynamicWrapperClasses($field, $element)],
                ],
            ]
        );

        if (isset($field['add_more']) && $field['add_more']) {
            $addMoreButtonClass = 'add_to_collection add_more button 2 relative pl-6 text-xs font-bold uppercase leading-normal text-spring-50 text-bluecoral 10 ';

            if (Arr::get($field, 'add_more_has_borders')) {
                $addMoreButtonClass = $addMoreButtonClass . getAddAdditionalButtonBorders();
            }

            $this->add('add_to_collection_' . $field['name'], 'button', [
                'label' => generateAddAdditionalLabel($element['name'], $field['name']),
                'attr'  => [
                    'class'     => $addMoreButtonClass,
                    'form_type' => $field['name'],
                    'icon'      => true,
                ],
            ]);
        }

        $this->add('delete_' . $field['name'], 'button', [
            'attr' => [
                'class' => 'delete-parent five delete-item two absolute right-0 top-16 -translate-y-1/2 translate-x-1/2 5',
            ],
        ]);
    }

    private function getSubElementFormWrapperClass($field, $data): string
    {
        return (Arr::get($data, 'attributes', null) && isset($field['name']) && strtolower(
            $field['name']
        ) === 'narrative') ? 'form-field-group form-child-body three xl:flex flex-wrap p-6' : 'form-field-group form-child-body four xl:flex flex-wrap p-6';
    }

    private function getSubElementFormDynamicWrapperClasses($field, $data): string
    {
        $hasAddMoreButton = isset($field['add_more']) && $field['add_more'];
        $isSubElementNarrative = isset($field['name']) && strtolower($field['name']) === 'narrative';
        $hasAttributes = Arr::get($data, 'attributes', null);
        $isNotMailingAddress = $data['name'] !== 'mailing_address';

        $collapsableClass = getCollapsableClass($field, 'wrapper-collection');
        $formBorderClass = $this->shouldRenderBorderOnForm($field, $data) ? 'border-spring-50 border' : '';
        $labelWithBorder = $this->shouldRenderBorderOnLabel($field, $data) ? 'label-with-border' : '';

        if ($hasAddMoreButton) {
            if ($isSubElementNarrative && !$hasAttributes && $isNotMailingAddress) {
                return "border-spring-50 $collapsableClass";
            }

            return "subelement rounded-t-sm eight border-spring-50 $collapsableClass $formBorderClass $labelWithBorder";
        }

        return "subelement rounded-t-sm nine border-spring-50 $collapsableClass $formBorderClass $labelWithBorder";
    }

    private function getWrapperCollectionFormWrapperClasses(): string
    {
        return 'wrapped-child-body two ';
    }

    private function getWrapperCollectionFormDynamicWrapperClasses($field, $element): string
    {
        $hasAddMoreButton = isset($field['add_more']) && $field['add_more'];
        $hasAttributes = Arr::get($element, 'attributes', null);
        $isSubElementNarrative = isset($field['name']) && strtolower($field['name']) === 'narrative';

        $collapsableClasses = getCollapsableClass($field, 'wrapper-collection-form');
        $formBorderClass = $this->shouldRenderBorderOnForm($field, $element) ? 'border-spring-50 border' : '';
        $labelWithBorder = $this->shouldRenderBorderOnLabel($field, $element) ? 'label-with-border' : '';

        if ($hasAddMoreButton) {
            if (!$hasAttributes && $isSubElementNarrative) {
                return "border-spring-50 $collapsableClasses $formBorderClass $labelWithBorder";
            }

            return "subelement rounded-t-sm ten border-spring-50 $collapsableClasses $formBorderClass $labelWithBorder";
        }

        $hasAttributes = Arr::get($field, 'attributes', null);
        $hasSubElementNarrative = $field['sub_elements'] && isset($field['sub_elements']['narrative']);

        if (!$hasAttributes && $hasSubElementNarrative) {
            return "subelement rounded-t-sm eleven  $collapsableClasses $formBorderClass $labelWithBorder";
        }

        return "subelement rounded-t-sm twelve border-spring-50 $collapsableClasses $formBorderClass $labelWithBorder";
    }

    private function shouldRenderBorderOnAddMoreButton($field): bool
    {
        /** Handles for contact_info */
        $attr = Arr::get($field, 'name', '');
        $noBorderAttrs = ['narrative', 'email', 'telephone', 'website'];

        return Arr::get($field, 'add_more_has_borders') && !in_array($attr, $noBorderAttrs);
    }

    private function shouldRenderBorderOnForm($field, $element): bool
    {
        $baseElements = ['related_activity', 'legacy_data', 'default_aid_type'];

        if (in_array(Arr::get($element, 'name'), $baseElements)) {
            return Arr::get($element, 'form_has_borders', false);
        }

        /** Handles for contact_info */
        $attr = Arr::get($field, 'name', '');
        $noBorderAttrs = ['narrative', 'email', 'telephone', 'website'];

        return Arr::get($field, 'form_has_borders') && !in_array($attr, $noBorderAttrs);
    }

    private function shouldRenderBorderOnLabel($field, $element): bool
    {
        $baseElements = ['related_activity', 'legacy_data', 'default_aid_type'];

        if (in_array(Arr::get($element, 'name'), $baseElements)) {
            return Arr::get($element, 'label_has_borders', false);
        }

        return Arr::get($field, 'label_has_borders') && Arr::get($field, 'name') != 'narrative';
    }
}
