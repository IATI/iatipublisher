<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\Sector;

use App\Http\Requests\Activity\ActivityBaseRequest;
use App\IATI\Services\Activity\ActivityService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Validator;

/**
 * Class SectorRequest.
 */
class SectorRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function rules(): array
    {
        $data = $this->get('sector');

        $totalRules = [
            $this->getSectorsRules($data),
            $this->getErrorsForSector($data),
        ];

        return mergeRules($totalRules);
    }

    /**
     * Get the error message as required.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->getSectorsMessages($this->get('sector'));
    }

    /**
     * Returns grouped sector.
     *
     * @param $formFields
     *
     * @return array
     */
    public function groupSector($formFields): array
    {
        $groupedSector = [];

        foreach ($formFields as $formField) {
            if (array_key_exists($formField['sector_vocabulary'], $groupedSector)) {
                $groupedSectorPercentage = $groupedSector[$formField['sector_vocabulary']]['total'];
                $groupedSector[$formField['sector_vocabulary']]['count'] += 1;
                $groupedSector[$formField['sector_vocabulary']]['total'] = (float) number_format(
                    round((float) $groupedSectorPercentage + (float) $formField['percentage'], 2),
                    2
                );
            } else {
                $groupedSector[$formField['sector_vocabulary']] = [
                    'count' => 1,
                    'total' => (float) $formField['percentage'],
                ];
            }
        }

        return $groupedSector;
    }

    /**
     * returns rules for sector.
     *
     * @param $formFields
     * @param  bool  $fileUpload
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getErrorsForSector($formFields, bool $fileUpload = false): array
    {
        $rules = [];

        foreach ($formFields as $sectorIndex => $sector) {
            $sectorForm = sprintf('sector.%s', $sectorIndex);
            $rules[sprintf('%s.sector_vocabulary', $sectorForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('SectorVocabulary', 'Activity', false)
                )
            );
            $rules[sprintf('%s.code', $sectorForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('SectorCode', 'Activity', false)
                )
            );
            $rules[sprintf('%s.category_code', $sectorForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('SectorCategory', 'Activity', false)
                )
            );
            $rules[sprintf('%s.sdg_goal', $sectorForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('UNSDG-Goals', 'Activity', false)
                )
            );
            $rules[sprintf('%s.sdg_target', $sectorForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('UNSDG-Targets', 'Activity', false)
                )
            );
            $rules[sprintf('%s.percentage', $sectorForm)] = 'nullable|numeric';

            if (isset($sector['sector_vocabulary']) &&
                ($sector['sector_vocabulary'] === '99' || $sector['sector_vocabulary'] === '98')) {
                $rules[sprintf('%s.vocabulary_uri', $sectorForm)] = 'nullable|url';
            }

            $narrativeRules = $this->getErrorsForNarrative($sector['narrative'], $sectorForm);

            foreach ($narrativeRules as $key => $item) {
                $explodedKey = explode('.', $key);
                $isNarrative = count($explodedKey) === 5 && $explodedKey[4] === 'language';

                if ($isNarrative && in_array($sector['sector_vocabulary'], ['98', '99'])) {
                    $rules[sprintf(
                        '%s.%s.%s.%s',
                        $sectorForm,
                        'narrative',
                        $explodedKey[3],
                        'narrative'
                    )]
                        = ['required'];
                } else {
                    $rules[$key] = $item;
                }
            }
        }

        return $rules;
    }

    /**
     * returns rules for sector.
     *
     * @param $formFields
     * @param  bool  $fileUpload
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getSectorsRules($formFields, bool $fileUpload = false): array
    {
        if (empty($formFields)) {
            return [];
        }

        if (!$fileUpload) {
            $params = $this->route()->parameters();
            $activityService = app()->make(ActivityService::class);

            if ($activityService->hasSectorDefinedInTransactions($params['id'])) {
                Validator::extend('already_in_transactions', function () {
                    return false;
                });

                return ['sector' => 'already_in_transactions'];
            }
        }

        Validator::extend('sector_total_percent', function () {
            return false;
        });

        $rules = [];
        $groupedPercentSector = $this->groupSector($formFields);

        foreach ($formFields as $sectorIndex => $sector) {
            $sectorForm = sprintf('sector.%s', $sectorIndex);

            $rules[sprintf('%s.percentage', $sectorForm)] = 'nullable|min:0';

            if ($groupedPercentSector[$sector['sector_vocabulary']]['count'] > 1) {
                if ($groupedPercentSector[$sector['sector_vocabulary']]['total'] !== 100.0) {
                    $rules[$sectorForm . '.percentage'] = 'sector_total_percent';
                }
            } elseif (!empty($groupedPercentSector[$sector['sector_vocabulary']]['total']) &&
                $groupedPercentSector[$sector['sector_vocabulary']]['total'] !== 100.0) {
                $rules[$sectorForm . '.percentage'] = 'in:' . 100.0;
            }

            $narrativeRules = $this->getWarningForNarrative($sector['narrative'], $sectorForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * returns messages for sector.
     *
     * @param $formFields
     *
     * @return array
     */
    public function getSectorsMessages($formFields): array
    {
        $messages = [
            'sector.already_in_transactions' => trans(
                'validation.activity_sector.already_in_transactions'
            ),
        ];

        foreach ($formFields as $sectorIndex => $sector) {
            $sectorForm = sprintf('sector.%s', $sectorIndex);
            $messages[sprintf('%s.sector_vocabulary.in', $sectorForm)] = trans(
                'validation.vocabulary_is_invalid'
            );
            $messages[sprintf('%s.code.in', $sectorForm)] = trans(
                'validation.sector_code_is_invalid'
            );
            $messages[sprintf('%s.category_code.in', $sectorForm)] = trans(
                'validation.sector_code_is_invalid'
            );
            $messages[sprintf('%s.sdg_goal.in', $sectorForm)] = trans(
                'validation.sector_code_is_invalid'
            );
            $messages[sprintf('%s.sdg_target.in', $sectorForm)] = trans(
                'validation.sector_code_is_invalid'
            );
            $messages[sprintf(
                '%s.vocabulary_uri.url',
                $sectorForm
            )]
                = trans('validation.url_valid');
            // TODO : check this
            // $messages[sprintf('%s.percentage.numeric', $sectorForm)] = trans('common/common.the_sector_percentage_field_must_be_a_number');
            $messages[sprintf('%s.percentage.numeric', $sectorForm)] = trans('validation.percentage_must_be_a_number');
            $messages[sprintf(
                '%s.percentage.in',
                $sectorForm
            )]
                = trans('validation.activity_sector.percentage.numeric');
            $messages[sprintf(
                '%s.percentage.sector_total_percent',
                $sectorForm
            )]
                = trans('validation.sum');

            $messageNarratives = $this->getMessagesForNarrative($sector['narrative'], $sectorForm);

            foreach ($messageNarratives as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }
}
