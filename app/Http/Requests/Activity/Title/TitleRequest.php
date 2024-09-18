<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\Title;

use App\Http\Requests\Activity\ActivityBaseRequest;
use Illuminate\Support\Arr;

/**
 * Class TitleRequest.
 */
class TitleRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($name = 'narrative', $data = []): array
    {
        if ($name === 'title') {
            $titles = $data;
        } else {
            $titles = request()->get('narrative');
        }

        $totalRules = [
            $this->getCriticalErrorsForTitle($name, $titles),
            $this->getErrorsForTitle($name, $titles),
            $this->getWarningForTitle($name, $titles),
        ];

        return mergeRules($totalRules);
    }

    /**
     * Return critical rules for title.
     *
     * @param $name
     *
     * @return array
     */
    public function getCriticalErrorsForTitle($name, $titles = []): array
    {
        $firstTitleKey = array_key_first($titles) ?? '0';
        $rules = [];

        if (empty(Arr::get($titles, sprintf('%s.narrative', $firstTitleKey), null))) {
            $rules[sprintf('%s.%s.narrative', $name, $firstTitleKey)] = 'required';
        }

        return $rules;
    }

    /**
     * Return errors for title.
     *
     * @param $name
     * @param $titles
     *
     * @return array
     */
    public function getErrorsForTitle($name, $titles = []): array
    {
        $rules = [];
        $validLanguages = implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles('Language', 'Activity', false, false)
            )
        );

        if (is_array($titles) && count($titles)) {
            foreach ($titles as $key => $title) {
                $rules[sprintf('%s.%s.language', $name, $key)][] = 'nullable';
                $rules[sprintf('%s.%s.language', $name, $key)][] = sprintf('in:%s', $validLanguages);
            }
        }

        return $rules;
    }

    /**
     * Return rules for title.
     *
     * @param $name
     * @param $titles
     *
     * @return array
     */
    public function getWarningForTitle($name, $titles = []): array
    {
        $rules[$name] = 'unique_lang|unique_default_lang';

        if (is_array($titles) && count($titles)) {
            foreach ($titles as $key => $title) {
                if ($key !== 0 && !empty(Arr::get($title, 'language', ''))) {
                    $rules[sprintf('%s.%s.narrative', $name, $key)] = 'required_with_language:' . Arr::get(
                        $title,
                        'narrative',
                        ''
                    );
                }
            }
        }

        return $rules;
    }

    /**
     * Get the error message as required.
     *
     * @return array
     */
    public function messages($name = 'narrative', $data = []): array
    {
        if ($name === 'title') {
            $titles = $data;
        } else {
            $titles = request()->get('narrative');
        }

        $messages[sprintf('%s.unique_lang', $name)] = trans('validation.narrative_language_unique');
        $messages[sprintf('%s.unique_default_lang', $name)] = trans('validation.narrative_language_unique');
        $messages[sprintf('%s.0.narrative.required', $name)] = trans('validation.first_title_required');

        if (is_array($titles) && count($titles)) {
            foreach ($titles as $key => $title) {
                if ($key !== 0) {
                    $messages[sprintf('%s.%s.narrative.required_with_language', $name, $key)] = trans(
                        'validation.narrative_is_required_when_language_is_populated'
                    );
                }
            }
        }

        return $messages;
    }
}
