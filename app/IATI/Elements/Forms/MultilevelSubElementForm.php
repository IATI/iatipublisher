<?php

declare(strict_types=1);

namespace App\IATI\Elements\Forms;

use Illuminate\Support\Arr;

/**
 * Class MultilevelSubElementForm.
 */
class MultilevelSubElementForm extends BaseForm
{
    /**
     * Builds multilevel subelement form.
     *
     * @return void
     * @throws \JsonException
     */
    public function buildForm(): void
    {
        $element = $this->getData();
        $attributes = Arr::get($this->getData(), 'attributes', null);
        $sub_elements = Arr::get($this->getData(), 'sub_elements', null);
        $this->setClientValidationEnabled(false);

        if ($attributes) {
            if (Arr::get($this->getData(), 'add_more', false) && !$sub_elements) {
                $this->buildCollection($attributes);
            } else {
                foreach ($attributes as $attribute) {
                    if (is_array($attribute)) {
                        $this->buildField($attribute);
                    }
                }
            }
        }

        if ($sub_elements) {
            foreach ($sub_elements as $name => $sub_element) {
                $addMoreButtonClass = 'add_to_parent add_more button one relative pl-6 text-xs font-bold uppercase leading-normal text-spring-50 text-bluecoral ';

                if ($this->shouldRenderBordersOnAddMoreButton($sub_element)) {
                    $addMoreButtonClass = $addMoreButtonClass . getAddAdditionalButtonBorders();
                }

                $this->add(
                    $this->getData(sprintf('sub_elements.%s.name', $name)),
                    'collection',
                    [
                        'type'           => 'form',
                        'property'       => 'name',
                        'prototype'      => true,
                        'prototype_name' => '__PARENT_NAME__',
                        'options'        => [
                            'class'            => 'App\IATI\Elements\Forms\BaseForm',
                            'data'             => $this->getData(sprintf('sub_elements.%s', $name)),
                            'label'            => false,
                            'element_criteria' => $this->getData(sprintf('sub_elements.%s.element_criteria', $name)),
                            'hover_text'       => $this->getData(sprintf('sub_elements.%s.hover_text', $name)) ?? '',
                            'help_text'        => $this->getData(sprintf('sub_elements.%s.help_text', $name)) ?? '',
                            'helper_text'      => $this->getData(sprintf('sub_elements.%s.helper_text', $name)) ?? '',
                            'is_collapsable'   => Arr::get($sub_element, 'is_collapsable', ''),
                            'label_indicator'  => Arr::get($sub_element, 'label_indicator', ),
                            'wrapper'          => ['class' => $this->getBaseFormWrapperClasses()],
                            'dynamic_wrapper'  => ['class' => $this->getBaseFormDynamicWrapperClasses($sub_element, $element)], ],
                    ]
                )->add('add_to_collection', 'button', [
                    'label' => generateAddAdditionalLabel($element['name'], $this->getData(sprintf('sub_elements.%s.name', $name))),
                    'attr' => [
                        'class' => $addMoreButtonClass,
                        'icon' => true,
                    ],
                ]);
            }
        }
    }

    private function getBaseFormWrapperClasses(): string
    {
        return 'multi-form relative two pb-6 mb-2';
    }

    private function getBaseFormDynamicWrapperClasses($sub_element, $element): string
    {
        $hasAddMoreButton = Arr::get($sub_element, 'add_more', false);
        $canAddMoreAttributes = Arr::get($element, 'add_more_attributes', false);
        $elementHasAttributes = !count(Arr::get($sub_element, 'attributes', [])) > 0;
        $isSubElementNarrative = strtolower(Arr::get($sub_element, 'name', '')) === 'narrative';
        $collapsableClass = getCollapsableClass($element, 'multi-level-form');
        $formBorderClass = $this->shouldRenderBorderOnForm($sub_element) ? 'border-spring-50 border' : '';
        $elementLabelClass = $this->shouldRenderBorderOnLabel($sub_element) ? 'label-with-border' : '';

        $baseClasses = "subelement rounded-t-sm five $collapsableClass $formBorderClass $elementLabelClass";

        if (($hasAddMoreButton || $canAddMoreAttributes) && ($isSubElementNarrative && !$elementHasAttributes)) {
            return "$collapsableClass $formBorderClass $elementLabelClass";
        }

        return $baseClasses;
    }

    private function shouldRenderBordersOnAddMoreButton($sub_element)
    {
        return Arr::get($sub_element, 'add_more_has_borders');
    }

    private function shouldRenderBorderOnForm($sub_element)
    {
        return Arr::get($sub_element, 'form_has_borders');
    }

    private function shouldRenderBorderOnLabel($sub_element)
    {
        return Arr::get($sub_element, 'label_has_borders');
    }
}
