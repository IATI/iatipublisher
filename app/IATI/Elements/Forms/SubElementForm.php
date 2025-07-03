<?php

declare(strict_types=1);

namespace App\IATI\Elements\Forms;

use Illuminate\Support\Arr;
use Kris\LaravelFormBuilder\Form;

/**
 * Class SubElementForm.
 */
class SubElementForm extends Form
{
    /**
     * Builds subelement form.
     *
     * @return mixed|void
     */
    public function buildForm():void
    {
        $data = $this->getData();
        $this->setClientValidationEnabled(false);

        if (Arr::get($data, 'type', null)) {
            $this->buildFields($this->getData(), false);
        }

        if (isset($data['attributes'])) {
            $attributes = $data['attributes'];

            foreach ($attributes as $i=>$attribute) {
                if (is_array($attribute)) {
                    $attribute['overRideDefaultFieldValue'] = $data['overRideDefaultFieldValue'] ?? [];

                    if (strtolower($data['label']) === 'language' &&
                        strtolower($data['name']) === 'language' &&
                        (isset($attribute['name']) &&
                        strtolower($attribute['name']) === 'language' && $attribute['label'] === 'code')) {
                        $attribute['overRideDefaultFieldValue'] = [];
                    }
                    $this->buildFields($attribute, true);
                }
            }
        }

        if (Arr::get($data, 'add_more', false)) {
            $this->add('delete', 'button', [
                'attr' => [
                    'class' => 'delete delete-item absolute right-0 top-2/4 -translate-y-1/2 translate-x-1/2',
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
    public function buildFields($field, $isAttribute = false): void
    {
        $options = [
            'label' => $field['label'] ?? '',
            'help_block' => [
                'text' => $field['help_text'] ?? '',
                'title' => $field['label'],
                'show_full_help_text'=>Arr::get($field, 'show_full_help_text', ''),
            ],
            'hover_block' => [
                'title' => $field['label'],
                'text' => $field['hover_text'] ?? '',
            ],
            'required' => $field['required'],
            'multiple' => $field['multiple'] ?? false,
            'attr' => [
                'class' => 'form__input border-0',
                'placeholder' => Arr::get($field, 'placeholder', ''),
            ],
            'wrapper' => [
                'class' => 'form-field xl:basis-6/12',
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

            $options['attr']['disabled'] = (array_key_exists(
                'read_only',
                $field
            ) && $field['read_only'] == true) ? 'readonly' : false;
            $classWithCursorNotAllowed = $options['attr']['class'] . ' cursor-not-allowed';

            $options['attr']['class'] = (
                array_key_exists('read_only', $field) && $field['read_only'] == true
            ) ? $classWithCursorNotAllowed : $options['attr']['class'];
        }

        if ($field['type'] === 'textarea') {
            $options['attr']['disabled'] = (array_key_exists(
                'read_only',
                $field
            ) && $field['read_only'] == true) ? 'readonly' : false;
            $classWithCursorNotAllowed = $options['attr']['class'] . ' cursor-not-allowed';

            $options['attr']['class'] = (
                array_key_exists('read_only', $field) && $field['read_only'] == true
            ) ? $classWithCursorNotAllowed : $options['attr']['class'];
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
}
