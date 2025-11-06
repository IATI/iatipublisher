<?php

declare(strict_types=1);

namespace App\IATI\Elements\Forms;

use Illuminate\Support\Arr;

/**
 * Class ParentCollectionForm.
 */
class ParentCollectionForm extends BaseForm
{
    /**
     * Builds parent collection form.
     *
     * @return mixed|void
     */
    public function buildForm(): void
    {
        $field = $this->getData();

        $this->add(
            $this->getData('name'),
            'collection',
            [
                'type'           => 'form',
                'property'       => 'name',
                'prototype'      => true,
                'prototype_name' => '__PARENT_NAME__',
                'options'        => [
                    'class'            => 'App\IATI\Elements\Forms\BaseForm',
                    'data'             => $this->data,
                    'label'            => false,
                    'element_criteria' => $field['element_criteria'] ?? '',
                    'hover_text'       => Arr::get($field, 'hover_text', ''),
                    'help_text'        => Arr::get($field, 'help_text', ''),
                    'helper_text'      => Arr::get($field, 'helper_text', ''),
                    'info_text'        => Arr::get($field, 'info_text', ''),
                    'is_collapsable'   => Arr::get($field, 'is_collapsable', ''),
                    'label_indicator'  => Arr::get($field, 'label_indicator', ),
                    'wrapper'          => ['class' => $this->getBaseFormWrapperClasses()],
                    'dynamic_wrapper'  => ['class' => $this->getBaseFormDynamicWrapperClasses($field)],
                ],
            ]
        );

        if (Arr::get($field, 'add_more', false) || Arr::get($field, 'add_more_attributes', false)) {
            $addMoreButtonClass = 'add_to_parent add_more button four relative text-xs font-bold text-spring-50 text-bluecoral uppercase leading-normal pl-6 ';

            if (Arr::get($field, 'add_more_has_borders')) {
                $addMoreButtonClass = $addMoreButtonClass . getAddAdditionalButtonBorders() . ' mt-6';
            }

            $this->add('add_to_collection', 'button', [
                'label' => generateAddAdditionalLabel($field['name'], $field['name']),
                'attr' => [
                    'icon' => true,
                    'class' => $addMoreButtonClass,
                ],
            ]);
        }
    }

    private function getBaseFormWrapperClasses(): string
    {
        return 'multi-form relative one pb-3';
    }

    private function getBaseFormDynamicWrapperClasses($field): string
    {
        $hasAddMoreButton = isset($field['add_more']) && $field['add_more'];
        $isSubElementNarrative = isset($field['name']) && strtolower($field['name']) === 'narrative';
        $elementHasAttributes = Arr::get($field, 'attributes', null);
        $formBorderClass = $this->shouldRenderBorderOnForm($field) ? 'border-spring-50 border' : '';
        $labelWithBorder = $this->shouldRenderBorderOnLabel($field) ? 'label-with-border' : '';

        if ($hasAddMoreButton) {
            if ($isSubElementNarrative && $elementHasAttributes) {
                $dynamicWrapperClass = 'border-spring-50 pb-11';
            } else {
                $dynamicWrapperClass = "subelement subelement-parent rounded-t-sm six border-spring-50  $formBorderClass $labelWithBorder";
            }
        } else {
            $dynamicWrapperClass = "subelement subelement-parent rounded-t-sm seven border-spring-50 $formBorderClass $labelWithBorder";
        }

        if (Arr::get($field, 'freeze')) {
            $dynamicWrapperClass .= ' freeze';
        }

        $collapsableClass = getCollapsableClass($field, 'parent-form');

        return $dynamicWrapperClass . ' ' . $collapsableClass;
    }

    private function shouldRenderBorderOnForm($element)
    {
        return Arr::get($element, 'form_has_borders', false);
    }

    private function shouldRenderBorderOnLabel($element)
    {
        return Arr::get($element, 'form_has_borders', false);
    }
}
