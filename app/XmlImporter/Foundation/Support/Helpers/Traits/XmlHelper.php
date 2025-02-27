<?php

declare(strict_types=1);

namespace App\XmlImporter\Foundation\Support\Helpers\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class XmlHelper.
 */
trait XmlHelper
{
    /**
     * Returns lat and long for location field.
     *
     * @param $values
     *
     * @return string[]
     */
    protected function latAndLong($values): array
    {
        $data = ['latitude' => '', 'longitude' => ''];
        $index = array_search('{}point', array_column($values, 'name'));

        if ($index === false || $index === '') {
            return $data;
        }

        $pointData = Arr::get($values, "$index.value", []);
        $latLongData = Arr::get($pointData, '0.value', '');
        $text = explode(' ', $latLongData);

        if (count($text) === 2) {
            $data['latitude'] = $text[0];
            $data['longitude'] = $text[1];
        }

        return $data;
    }

    /**
     * Filter the provided key and groups the values in array.
     *
     * @param $values
     * @param $key
     *
     * @return array|string[][]
     */
    protected function filterValues($values, $key = null): array
    {
        $index = 0;
        $data = [[$key => '']];

        $values = $values ?: [];

        foreach ($values as $value) {
            if ($this->name($value['name']) === $key) {
                $data[$index][$key] = $this->value($value);
                $index++;
            }
        }

        return $data;
    }

    /**
     * Filter the provided key, Convert the provided template to array and groups the attributes.
     *
     * @param       $values
     * @param       $key
     * @param array $template
     *
     * @return array
     */
    protected function filterAttributes($values, $key, array $template): array
    {
        $values = $values ?: [];
        $index = 0;
        $data = $this->templateToArray($template);

        foreach ($values as $value) {
            if ($this->name($value['name']) === $key) {
                foreach (Arr::get($value, 'attributes', []) as $attributeKey => $attribute) {
                    if ($attributeKey === 'indicator-uri') {
                        $attributeKey = 'indicator_uri';
                    }

                    if (array_key_exists($attributeKey, array_flip($template))) {
                        $data[$index][$attributeKey] = $attributeKey === 'vocabulary' || in_array($value['name'], ['{}category', '{}administrative'])
                            ? strtoupper($attribute)
                            : $attribute;
                    }
                }
                $index++;
            }
        }

        return $data;
    }

    /**
     * Converts the provided template into empty key => value pairs.
     *
     * @param array $template
     *
     * @return array
     */
    protected function templateToArray(array $template): array
    {
        $data = [array_flip($template)];

        foreach ($data as $index => $values) {
            foreach ($values as $key => $value) {
                $data[$index][$key] = null;
            }
        }

        return $data;
    }

    /**
     * Get the value from the array.
     * If key is provided then the $fields = $data['value'] else $fields = $data.
     * If key is provided then the value is fetched from the value field of the data.
     * If the value is array then narrative is returned else only the value is returned.
     *
     * @param array $fields
     * @param null  $key
     *
     * @return array
     */
    protected function value(array $fields, $key = null): mixed
    {
        if (!$key) {
            return Arr::get($fields, 'value', '') ?? '';
        }

        if (!empty($fields)) {
            foreach ($fields as $field) {
                if ($this->name(Arr::get($field, 'name')) === $key) {
                    if (is_array(Arr::get($field, 'value'))) {
                        return $this->narrative($field);
                    }

                    return Arr::get($field, 'value', '') ?? '';
                }
            }
        }

        return [['narrative' => '', 'language' => '']];
    }

    /**
     * Returns the all narrative present in the provided $subElement.
     *
     * @param $subElement
     *
     * @return array
     */
    protected function narrative($subElement): array
    {
        $field = [['narrative' => '', 'language' => '']];

        if (is_array(Arr::get($subElement, 'value', []))) {
            foreach (Arr::get($subElement, 'value', []) as $index => $value) {
                if ($this->name($value) === 'narrative') {
                    $narrative = empty(Arr::get($value, 'value', '')) ? '' : Arr::get($value, 'value', '');
                    $field[$index] = [
                        'narrative' => is_string($narrative) ? trim($narrative) : $narrative,
                        'language' => strtolower($this->attributes($value, 'lang')),
                    ];
                }
            }

            return $field;
        }

        $narrative = empty(Arr::get($subElement, 'value', '')) ? '' : Arr::get($subElement, 'value', '');

        $field[0] = [
            'narrative' => trim($narrative),
            'language' => $this->attributes((array) $subElement, 'lang'),
        ];

        return $field;
    }

    /**
     * Get the name of the current Xml element.
     *
     * @param      $element
     * @param bool $snakeCase
     *
     * @return string
     */
    protected function name($element, bool $snakeCase = false): string
    {
        if (is_array($element)) {
            $camelCaseString = Str::camel(str_replace('{}', '', $element['name']));

            return $snakeCase ? Str::snake($camelCaseString) : $camelCaseString;
        }

        $camelCaseString = Str::camel(str_replace('{}', '', $element));

        return $snakeCase ? Str::snake($camelCaseString) : $camelCaseString;
    }

    /**
     * Returns the attributes of the provided element.
     * If key is provided then the attribute equal to the key is returned.
     * If fieldName and key both are provided then the attributes inside value is returned.
     *
     * @param array $element
     * @param ?string  $key
     * @param ?string  $fieldName
     *
     * @return mixed|string
     */
    public function attributes(array $element, $key = null, $fieldName = null): mixed
    {
        if (!$key) {
            return Arr::get($element, 'attributes', []);
        }
        if ($fieldName) {
            return $this->getSpecificAttribute($element, $fieldName, $key);
        }

        return $this->getLanguageAttribute($element, $key);
    }

    /**
     * Get specific attributes for Xml element.
     *
     * @param array $element
     * @param       $fieldName
     * @param       $key
     *
     * @return mixed
     */
    protected function getSpecificAttribute(array $element, $fieldName, $key): mixed
    {
        $data = '';

        if (!empty(Arr::get($element, 'value', []))) {
            foreach (Arr::get($element, 'value', []) as $value) {
                if ($fieldName === $this->name($value['name'])) {
                    return $this->attributes($value, $key);
                }
            }
        }

        return $data;
    }

    /**
     * Get the Language attribute from a specific Xml element.
     *
     * @param array $element
     * @param       $key
     *
     * @return mixed
     */
    protected function getLanguageAttribute(array $element, $key): mixed
    {
        $value = Arr::get($element, 'attributes', []);
        if ($value) {
            foreach ($value as $itemKey => $item) {
                if ($key === substr($itemKey, -4, 4)) {
                    return $item;
                }
            }

            return Arr::get($element, 'attributes.' . $key, '');
        }

        return '';
    }
}
