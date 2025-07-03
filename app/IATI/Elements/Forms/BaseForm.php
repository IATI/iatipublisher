<?php

declare(strict_types=1);

namespace App\IATI\Elements\Forms;

use Illuminate\Support\Arr;
use Kris\LaravelFormBuilder\Form;

/**
 * Class BaseForm.
 */
class BaseForm extends Form
{
    /**
     * @return void
     * @throws \JsonException
     */
    public function buildForm(): void
    {
        $this->setClientValidationEnabled(false);
        $element = $this->getData();
        $attributes = Arr::get($element, 'attributes', null);
        $sub_elements = Arr::get($element, 'sub_elements', null);

        if (Arr::get($element, 'narrative_first')) {
            if ($sub_elements) {
                foreach ($sub_elements as $i => $sub_element) {
                    $this->buildCollection($sub_element);
                    if (Arr::get($element, 'add_more', false) && Arr::get($sub_element, 'add_more', false) && !Arr::get($element, 'do_not_repeat_button', false)) {
                        $this->add(trans('common/common.delete_this') . $element['name'] ?? $sub_element['name'], 'button', [
                            'attr' => [
                                'class' => 'mt-4 mb-4 delete-parent one text-crimson-40 font-bold text-md uppercase absolute right-0 -bottom-[1rem] w-[100%] justify-end pr-6 delete-parent-item delete-parent-selector',
                            ],
                        ]);
                    }
                }
            }

            if ($attributes) {
                if (Arr::get($element, 'add_more', false) && !$sub_elements && Arr::get(
                    $element,
                    'make_collection',
                    true
                )) {
                    $this->buildCollection($attributes);
                } else {
                    foreach ($attributes as $i => $attribute) {
                        if (is_array($attribute)) {
                            $this->buildField($attribute, $i);
                        }
                    }

                    if (Arr::get($element, 'add_more', false) || Arr::get($element, 'add_more_attributes', false)) {
                        $name = $element['name'];
                        $this->add(trans('common/common.delete_this') . $element['name'], 'button', [
                            'attr' => [
                                'class' => 'mt-4 mb-4 delete-parent one text-crimson-40 font-bold text-md uppercase absolute right-0 -bottom-[1rem] w-[100%] justify-end pr-6 ' . " delete-parent-item-$name delete-parent-item delete-parent-selector",
                            ],
                        ]);
                    }
                }
            }
        } else {
            if ($attributes) {
                if (Arr::get($element, 'add_more', false) && !$sub_elements && Arr::get(
                    $element,
                    'make_collection',
                    true
                )) {
                    $this->buildCollection($attributes);
                } else {
                    foreach ($attributes as $i => $attribute) {
                        if (is_array($attribute)) {
                            $this->buildField($attribute, $i);
                        }
                    }

                    if (Arr::get($element, 'add_more', false) || Arr::get($element, 'add_more_attributes', false)) {
                        $name = $element['name'];
                        $this->add(trans('common/common.delete_this') . $element['name'], 'button', [
                            'attr' => [
                                'class' => 'mt-4 mb-4 delete-parent one text-crimson-40 font-bold text-md uppercase absolute right-0 -bottom-[1rem] w-[100%] justify-end pr-6 ' . " delete-parent-item-$name delete-parent-item delete-parent-selector",
                            ],
                        ]);
                    }
                }
            }

            if ($sub_elements) {
                foreach ($sub_elements as $i => $sub_element) {
                    $this->buildCollection($sub_element);
                    if (Arr::get($element, 'add_more', false) && Arr::get($sub_element, 'add_more', false) && !Arr::get($element, 'do_not_repeat_button', false)) {
                        $this->add(trans('common/common.delete_this') . $element['name'] ?? $sub_element['name'], 'button', [
                            'attr' => [
                                'class' => 'mt-4 mb-4 delete-parent one text-crimson-40 font-bold text-md uppercase absolute right-0 -bottom-[1rem] w-[100%] justify-end pr-6 delete-parent-item delete-parent-selector',
                            ],
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @param $field
     *
     * @return void
     */
    public function buildCollection($field): void
    {
        $element = $this->getData();
        $hasFieldType = Arr::get($field, 'type', null);
        $hasSubElements = array_key_exists('sub_elements', $field);
        $isWrapperCollection = Arr::get($field, 'wrapper_collection', true);

        if (!$hasFieldType && $hasSubElements && $isWrapperCollection) {
            $field['parent'] = $element['name'];
            $this->add(
                $field['name'],
                'collection',
                [
                    'type'           => 'form',
                    'property'       => 'name',
                    'prototype'      => true,
                    'prototype_name' => '__NAME__',
                    'options'        => [
                        'class'            => 'App\IATI\Elements\Forms\WrapperCollectionForm',
                        'data'             => $field,
                        'label'            => false,
                        'element_criteria' => Arr::get($field, 'element_criteria', ''),
                        'hover_text'       => Arr::get($field, 'hover_text', ''),
                        'help_text'        => Arr::get($field, 'help_text', ''),
                        'helper_text'      => Arr::get($field, 'helper_text', ''),
                        'is_collapsable'   => Arr::get($field, 'is_collapsable', ''),
                        'label_indicator'  => Arr::get($field, 'label_indicator', ),
                        'wrapper'          => ['class' => $this->getWrapperCollectionFormWrapperClasses()],
                        'dynamic_wrapper'  => ['class' => $this->getWrapperCollectionFormDynamicWrapperClasses($field, $element)],
                    ],
                ]
            );

            if ((isset($field['add_more']) && $field['add_more']) || (isset($element['add_more_attributes']) && $element['add_more_attributes'])) {
                $addMoreButtonClass = 'add_to_collection add_more button three relative pl-6 text-xs font-bold uppercase leading-normal text-spring-50 text-bluecoral ';

                if ($this->shouldRenderBorderOnAddMoreButton($field)) {
                    $addMoreButtonClass = $addMoreButtonClass . getAddAdditionalButtonBorders();
                }

                $this->add('add_to_collection_' . $field['name'], 'button', [
                    'label' => generateAddAdditionalLabel($element['name'], $field['name']),
                    'attr'  => [
                        'class'                => $addMoreButtonClass,
                        'form_type'            => $field['parent'] . '_' . $field['name'],
                        'has_child_collection' => 1,
                        'icon'                 => true,
                    ],
                ]);
            }
        } else {
            $this->add(
                $field['name'] ?? $element['name'],
                'collection',
                [
                    'type'           => 'form',
                    'property'       => 'name',
                    'prototype'      => true,
                    'prototype_name' => '__NAME__',
                    'options'        => [
                        'class'                     => 'App\IATI\Elements\Forms\SubElementForm',
                        'data'                      => $field,
                        'label'                     => false,
                        'element_criteria'          => $field['element_criteria'] ?? '',
                        'hover_text'                => isset($field['name']) ? Arr::get($field, 'hover_text', '') : Arr::get($element, 'hover_text', ''),
                        'help_text'                 => isset($field['name']) ? Arr::get($field, 'help_text', '') : Arr::get($element, 'help_text', ''),
                        'is_collapsable'            => Arr::get($field, 'is_collapsable', ''),
                        'label_indicator'           => Arr::get($field, 'label_indicator', ),
                        'wrapper'                   => ['class' => $this->getSubElementFormWrapperClasses($field, $element)],
                        'dynamic_wrapper'           => ['class' => $this->getSubElementFormDynamicWrapperClasses($field, $element)],
                        'overRideDefaultFieldValue' => $element['overRideDefaultFieldValue'] ?? [],
                    ],
                ]
            );

            $name = $field['name'] ?? $element['name'];

            if ((isset($field['add_more']) && $field['add_more']) || Arr::get($element, 'add_more_attributes', false)) {
                $addMoreButtonClass = 'add_to_collection add_more button four relative pl-6 text-xs font-bold uppercase leading-normal text-spring-50 text-bluecoral ';

                if ($this->shouldRenderBorderOnAddMoreButton($element)) {
                    $addMoreButtonClass = $addMoreButtonClass . getAddAdditionalButtonBorders();
                }

                $this->add('add_to_collection_' . $name, 'button', [
                    'label' => generateAddAdditionalLabel($element['name'], Arr::get($element, 'attributes', null) ? ($field['name'] ?? $name) : $element['name']),
                    'attr'  => [
                        'class'     => $addMoreButtonClass . (Arr::get($field, 'read_only', false) ? ' freeze' : ''),
                        'form_type' => !empty(Arr::get($this->getData(), 'name', null)) ? sprintf(
                            '%s_%s',
                            Arr::get($this->getData(), 'name', ''),
                            $name
                        ) : $name,
                        'icon'      => true,
                    ],
                ]);
            }
        }
    }

    /**
     * @param $field
     *
     * @return void
     * @throws \JsonException
     */
    public function buildField($field): void
    {
        $options = [
            'help_block'  => [
                'text'  => $field['help_text'] ?? '',
                'title' => $field['label'],
                'show_full_help_text'=>Arr::get($field, 'show_full_help_text', ''),
            ],
            'hover_block' => [
                'title' => $field['label'],
                'text'  => $field['hover_text'] ?? '',
            ],
            'label'       => $field['label'] ?? '',
            'required'    => $field['required'] ?? false,
            'multiple'    => $field['multiple'] ?? false,
            'attr'        => [
                'class'       => 'form__input code border-0',
                'readonly'    => (array_key_exists(
                    'read_only',
                    $field
                ) && $field['read_only'] == true) ? 'readonly' : false,
                'placeholder' => Arr::get($field, 'placeholder', ''),

            ],
            'wrapper'     => [
                'class' => 'form-field basis-auto w-full xl:basis-6/12 attribute' . (Arr::get($field, 'read_only', false) ? ' freeze' : ''),
            ],
        ];

        if ($field['type'] === 'text') {
            $options['attr']['class'] = $this->getAttributeClasses($field, $options);
        }

        if ($field['type'] === 'select') {
            $deprecationStatusMap = $this->getData()['deprecationStatusMap'];
            $overRideDefaultFieldValue = $this->getData()['overRideDefaultFieldValue'] ?? [];
            $defaultValue = getDefaultValue($overRideDefaultFieldValue, $field['name'], $field['choices'] ?? []);
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
            $options['attr']['disabled'] = (array_key_exists('read_only', $field) && $field['read_only'] == true)
                ? 'disabled'
                : false;

            $options['attr']['class'] = $this->getAttributeClasses($field, $options);
        }

        $this
            ->add(
                $field['name'],
                $field['type'],
                $options
            );
    }

    /**
     * Returns attribute class$options
     * Returns attribute class + 'cursor-not-allowed' (if read_only : true in json-schema).
     *
     * @param $field
     * @param $options
     *
     * @return string
     */
    public function getAttributeClasses($field, $options): string
    {
        $classWithCursorNotAllowed = $options['attr']['class'] . ' cursor-not-allowed';

        return (
            array_key_exists('read_only', $field) && $field['read_only'] == true
        ) ? $classWithCursorNotAllowed : $options['attr']['class'];
    }

    /**
     * Return codeList array from json codeList.
     *
     * @param string $filePath
     * @param bool $code
     * @param array|null $valuesInUse
     *
     * @return array
     * @throws \JsonException
     */
    public function getCodeList(string $filePath, bool $code = true, $deprecationStatusMap = []): array
    {
        $completePath = "AppData/Data/$filePath";
        $codeListFromFile = getJsonFromSource($completePath);
        $codeLists = json_decode($codeListFromFile, true, 512, JSON_THROW_ON_ERROR);
        $codeList = last($codeLists);

        $possibleSuffixes = getKeysThatUseThisCodeList($completePath);
        $deprecatedCodesInUse = filterArrayByKeyEndsWithPossibleSuffixes($deprecationStatusMap, $possibleSuffixes);

        $codeList = array_filter($codeList, function ($item) use ($deprecatedCodesInUse) {
            return filterDeprecated($item, $deprecatedCodesInUse);
        });

        $data = [];

        foreach ($codeList as &$item) {
            if (Arr::get($item, 'status', false) !== 'active' && in_array(Arr::get($item, 'code', ''), $deprecatedCodesInUse)) {
                $item['name'] = $item['name'] . ' (used)';
            }
        }

        foreach ($codeList as $list) {
            $data[$list['code']] = ($code) ? $list['code'] . (array_key_exists(
                'name',
                $list
            ) ? ' - ' . $list['name'] : '') : $list['name'];
        }

        return $data;
    }

    private function getWrapperCollectionFormWrapperClasses(): string
    {
        return 'wrapped-child-body one ';
    }

    private function getWrapperCollectionFormDynamicWrapperClasses($field, $element): string
    {
        $fieldHasAddMoreButton = isset($field['add_more']) && $field['add_more'];
        $isSubElementNarrative = isset($field['name']) && strtolower($field['name']) === 'narrative';
        $elementHasAttributes = Arr::get($element, 'attributes', null);
        $isMailingAddressElement = strtolower($element['name']) == 'mailing_address';
        $collapsableClass = getCollapsableClass($field, 'base-form');
        $formBorderClass = $this->shouldRenderBorderOnForm($field, $element) ? 'border-spring-50 border' : '';
        $labelWithBorder = $this->shouldRenderBorderOnLabel($field, $element) ? 'label-with-border' : '';

        if ($fieldHasAddMoreButton) {
            if (!$elementHasAttributes && $isSubElementNarrative && !$isMailingAddressElement) {
                return "$collapsableClass";
            }

            return "subelement rounded-t-sm one mt-6 $collapsableClass  $formBorderClass $labelWithBorder";
        }

        $fieldHasAttributes = Arr::get($field, 'attributes', null);
        $hasNarrativeSubElement = isset($field['sub_elements']['narrative']);

        /* @Doc: Nested sub element: country_budget_item -> description */
        if (!$fieldHasAttributes && $field['sub_elements'] && $hasNarrativeSubElement) {
            return "subelement rounded-t-sm two mx-6 mt-6 mb-3 $formBorderClass $collapsableClass $labelWithBorder";
        }

        /* @Doc Other_identifier -> description */
        return "subelement rounded-t-sm three mx-6 mb-6 mb-3 $collapsableClass  $formBorderClass $labelWithBorder";
    }

    private function getSubElementFormWrapperClasses($field, $element): string
    {
        $elementHasAttributes = Arr::get($element, 'attributes', null);
        $isSubElementNarrative = isset($field['name']) && strtolower($field['name']) === 'narrative';

        return 'form-field-group form-child-body xl:flex flex-wrap one mx-0 px-0';
    }

    private function getSubElementFormDynamicWrapperClasses($field, $element): string
    {
        $hasAddMoreButton = Arr::get($field, 'add_more', false);
        $canAddMoreAttributes = Arr::get($element, 'add_more_attributes', false);
        $elementHasAttributes = Arr::get($element, 'attributes', false);
        $isSubElementNarrative = strtolower(Arr::get($field, 'name', '')) === 'narrative';
        $collapsableClass = getCollapsableClass($field, 'base-form');
        $frozenClass = Arr::get($field, 'read_only', false) ? 'freeze' : '';
        $formBorderClass = $this->shouldRenderBorderOnForm($field, $element) ? 'border-spring-50 border' : '';
        $labelWithBorder = $this->shouldRenderBorderOnLabel($field, $element) ? 'label-with-border' : '';

        /** @Doc: Top level child element: Legacy_data, related_activity, default_aid_type */
        $baseClasses = "subelement rounded-t-sm four mt-6 $frozenClass $collapsableClass $formBorderClass $labelWithBorder";

        if (($hasAddMoreButton || $canAddMoreAttributes) && !$elementHasAttributes && $isSubElementNarrative) {
            return "$collapsableClass $formBorderClass $labelWithBorder";
        }

        return $baseClasses;
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
