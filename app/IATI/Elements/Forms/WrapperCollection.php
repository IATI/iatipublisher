<?php

declare(strict_types=1);

namespace App\IATI\Elements\Forms;

use Illuminate\Support\Arr;
use Kris\LaravelFormBuilder\Form;

/**
 * Class WrapperCollection.
 */
class WrapperCollection extends Form
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
                            'wrapper'         => [
                                'class' => $this->getSubElementFormWrapperClasses(),
                            ],
                            'dynamic_wrapper' => [
                                'class' => $this->getSubElementFormDynamicWrapperClasses($field, $data),
                            ],
                        ],
                    ]
                );

                $name = isset($field['name']) ? $field['name'] : $data['name'];

                if (isset($field['add_more']) && $field['add_more']) {
                    $addMoreButtonClass = 'add_to_collection add_more button 5 relative pl-6 text-xs font-bold uppercase leading-normal text-spring-50 text-bluecoral 7 ';

                    if (Arr::get($field, 'add_more_has_borders')) {
                        $addMoreButtonClass = $addMoreButtonClass . getAddAdditionalButtonBorders();
                    }

                    $this->add('add_to_collection_' . $name, 'button', [
                        'label' => generateAddAdditionalLabel($name, $name),
                        'attr'  => [
                            'class'     => $addMoreButtonClass,
                            'form_type' => $data['parent'] . '_' . $data['name'] . '_' . $field['name'],
                            'icon'      => true,
                        ],
                    ]);
                }
            }
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
                    'wrapper'         => [
                        'class' => 'wrapped-child-body',
                    ],
                    'dynamic_wrapper' => [
                        'class' => (isset($field['add_more']) && $field['add_more']) ?
                            ((!Arr::get(
                                $element,
                                'attributes',
                                null
                            ) && strtolower($field['name']) === 'narrative') ? 'border-spring-50 pb-11' : 'subelement rounded-t-sm border-spring-50 pb-11')
                            : ((!Arr::get(
                                $field,
                                'attributes',
                                null
                            ) && $field['sub_elements'] && isset($field['sub_elements']['narrative'])) ? 'subelement rounded-t-sm ' : 'subelement rounded-t-sm border-spring-50'),
                    ],
                ],
            ]
        );

        if (isset($field['add_more']) && $field['add_more']) {
            $addMoreButtonClass = 'add_to_collection add_more button 6 relative pl-6 text-xs font-bold uppercase leading-normal text-spring-50 text-bluecoral 8 ';

            if (Arr::get($field, 'add_more_has_borders')) {
                $addMoreButtonClass = $addMoreButtonClass . getAddAdditionalButtonBorders();
            }

            $this->add('add_to_collection_' . $field['name'], 'button', [
                'label' => generateAddAdditionalLabel($field['name'], $field['name']),
                'attr'  => [
                    'class'     => $addMoreButtonClass,
                    'form_type' =>  $field['name'],
                    'icon'      => true,
                ],
            ]);
        }

        $this->add('delete_' . $element['name'], 'button', [
            'attr' => [
                'class' => 'delete-parent three delete-item absolute right-0 top-16 -translate-y-1/2 translate-x-1/2 3',
            ],
        ]);
    }

    private function getSubElementFormWrapperClasses(): string
    {
        return 'form-field-group form-child-body two xl:flex flex-wrap p-6';
    }

    private function getSubElementFormDynamicWrapperClasses($field, $data)
    {
        $hasAddMoreButton = isset($field['add_more']) && $field['add_more'];
        $isSubElementNarrative = isset($field['name']) && strtolower($field['name']) === 'narrative';
        $hasAttributes = Arr::get($data, 'attributes', null);

        $collapsableClass = getCollapsableClass($field, 'wrapper-collection');

        if ($hasAddMoreButton) {
            if ($isSubElementNarrative && !$hasAttributes) {
                return "border-spring-50 $collapsableClass";
            }

            return "subelement rounded-t-sm border-spring-50 $collapsableClass";
        }

        return "subelement rounded-t-sm border-spring-50 $collapsableClass";
    }
}
