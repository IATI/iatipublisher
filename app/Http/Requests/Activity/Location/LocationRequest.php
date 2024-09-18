<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\Location;

use App\Http\Requests\Activity\ActivityBaseRequest;

/**
 * Class LocationRequest.
 */
class LocationRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $data = $this->get('location');
        $totalRules = [$this->getWarningForLocation($data), $this->getErrorsForLocation($data)];

        return mergeRules($totalRules);
    }

    /**
     * prepare the error message.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->getMessagesForLocation($this->get('location'));
    }

    /**
     * returns rules for location form.
     *
     * @param $formFields
     *
     * @return array
     */
    public function getWarningForLocation($formFields): array
    {
        $rules = [];

        foreach ($formFields as $locationIndex => $location) {
            $locationForm = 'location.' . $locationIndex;
            $tempRules = [
                $this->getWarningForName($location['name'], $locationForm),
                $this->getWarningForLocationDescription($location['description'], $locationForm),
                $this->getWarningForActivityDescription($location['activity_description'], $locationForm),
            ];

            foreach ($tempRules as $tempRule) {
                foreach ($tempRule as $idx => $rule) {
                    $rules[$idx] = $rule;
                }
            }
        }

        return $rules;
    }

    /**
     * returns rules for location form.
     *
     * @param $formFields
     *
     * @return array
     */
    public function getErrorsForLocation($formFields): array
    {
        $rules = [];

        foreach ($formFields as $locationIndex => $location) {
            $locationForm = 'location.' . $locationIndex;
            $rules[sprintf('%s.ref', $locationForm)] = ['nullable', 'not_regex:/(&|!|\/|\||\?)/'];
            $rules[sprintf('%s.location_reach.0.code', $locationForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'GeographicLocationReach',
                        'Activity',
                        false
                    )
                )
            );
            $rules[sprintf('%s.exactness.0.code', $locationForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'GeographicExactness',
                        'Activity',
                        false
                    )
                )
            );
            $rules[sprintf('%s.location_class.0.code', $locationForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'GeographicLocationClass',
                        'Activity',
                        false
                    )
                )
            );
            $rules[sprintf('%s.feature_designation.0.code', $locationForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('LocationType', 'Activity', false)
                )
            );

            $tempRules = [
                $this->getWarningForLocationId($location['location_id'], $locationForm),
                $this->getErrorsForName($location['name'], $locationForm),
                $this->getErrorsForLocationDescription($location['description'], $locationForm),
                $this->getErrorsForActivityDescription($location['activity_description'], $locationForm),
                $this->getErrorsForAdministrative($location['administrative'], $locationForm),
                $this->getErrorsForPoing($location['point'], $locationForm),
            ];

            foreach ($tempRules as $tempRule) {
                foreach ($tempRule as $idx => $rule) {
                    $rules[$idx] = $rule;
                }
            }
        }

        return $rules;
    }

    /**
     * returns messages for location form.
     * @param $formFields
     * @return array
     */
    public function getMessagesForLocation($formFields): array
    {
        $messages = [];

        foreach ($formFields as $locationIndex => $location) {
            $locationForm = 'location.' . $locationIndex;
            $messages[sprintf(
                '%s.ref.not_regex',
                $locationForm
            )]
                = trans('validation.reference_should_not_contain_symbol');
            $messages[sprintf('%s.location_reach.0.code.in', $locationForm)] = trans(
                'validation.this_field_is_invalid'
            );
            $messages[sprintf('%s.exactness.0.code.in', $locationForm)] = trans(
                'validation.this_field_is_invalid'
            );
            $messages[sprintf('%s.location_class.0.code.in', $locationForm)] = trans(
                'validation.this_field_is_invalid'
            );
            $messages[sprintf(
                '%s.feature_designation.0.code.in',
                $locationForm
            )]
                = trans('validation.this_field_is_invalid');
            $tempMessages = [
                $this->getMessagesForLocationId($location['location_id'], $locationForm),
                $this->getMessagesForName($location['name'], $locationForm),
                $this->getMessagesForLocationDescription($location['description'], $locationForm),
                $this->getMessagesForActivityDescription($location['activity_description'], $locationForm),
                $this->getMessagesForAdministrative($location['administrative'], $locationForm),
                $this->getMessagesForPoint($location['point'], $locationForm),
            ];

            foreach ($tempMessages as $tempMessage) {
                foreach ($tempMessage as $idx => $message) {
                    $messages[$idx] = $message;
                }
            }
        }

        return $messages;
    }

    /**
     * returns rules for location id.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     * @throws \JsonException
     */
    public function getWarningForLocationId($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $locationIdIndex => $locationId) {
            $locationIdForm = sprintf('%s.location_id.%s', $formBase, $locationIdIndex);
            $rules[sprintf('%s.vocabulary', $locationIdForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'GeographicVocabulary',
                        'Activity',
                        false
                    )
                )
            );
        }

        return $rules;
    }

    /**
     * returns messages for location id.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForLocationId($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $locationIdIndex => $locationId) {
            $locationIdForm = sprintf('%s.location_id.%s', $formBase, $locationIdIndex);
            $messages[sprintf('%s.vocabulary.in', $locationIdForm)] = trans(
                'validation.vocabulary_is_invalid'
            );
        }

        return $messages;
    }

    /**
     * returns rules for name.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getWarningForName($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $nameIndex => $name) {
            $narrativeForm = sprintf('%s.name.%s', $formBase, $nameIndex);

            foreach (
                $this->getWarningForNarrative(
                    $name['narrative'],
                    $narrativeForm
                ) as $locationNameIndex => $locationNameNarrativeRules
            ) {
                $rules[$locationNameIndex] = $locationNameNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * returns rules for name.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     * @throws \JsonException
     */
    public function getErrorsForName($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $nameIndex => $name) {
            $narrativeForm = sprintf('%s.name.%s', $formBase, $nameIndex);

            foreach (
                $this->getErrorsForNarrative(
                    $name['narrative'],
                    $narrativeForm
                ) as $locationNameIndex => $locationNameNarrativeRules
            ) {
                $rules[$locationNameIndex] = $locationNameNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * returns messages for name.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForName($formFields, $formBase): array
    {
        $messages = [];
        foreach ($formFields as $nameIndex => $name) {
            $narrativeForm = sprintf('%s.name.%s', $formBase, $nameIndex);

            foreach (
                $this->getMessagesForNarrative(
                    $name['narrative'],
                    $narrativeForm
                ) as $locationNameIndex => $locationNameNarrativeMessages
            ) {
                $messages[$locationNameIndex] = $locationNameNarrativeMessages;
            }
        }

        return $messages;
    }

    /**
     * returns rules for location description.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getWarningForLocationDescription($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $descriptionIndex => $description) {
            $narrativeForm = sprintf('%s.description.%s', $formBase, $descriptionIndex);

            foreach (
                $this->getWarningForNarrative(
                    $description['narrative'],
                    $narrativeForm
                ) as $locationDescriptionIndex => $locationDescriptionNarrativeRules
            ) {
                $rules[$locationDescriptionIndex] = $locationDescriptionNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * returns rules for location description.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getErrorsForLocationDescription($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $descriptionIndex => $description) {
            $narrativeForm = sprintf('%s.description.%s', $formBase, $descriptionIndex);

            foreach (
                $this->getErrorsForNarrative(
                    $description['narrative'],
                    $narrativeForm
                ) as $locationDescriptionIndex => $locationDescriptionNarrativeRules
            ) {
                $rules[$locationDescriptionIndex] = $locationDescriptionNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * returns messages for location description.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForLocationDescription($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $descriptionIndex => $description) {
            $narrativeForm = sprintf('%s.description.%s', $formBase, $descriptionIndex);

            foreach (
                $this->getMessagesForNarrative(
                    $description['narrative'],
                    $narrativeForm
                ) as $locationDescriptionIndex => $locationDescriptionNarrativeMessages
            ) {
                $messages[$locationDescriptionIndex] = $locationDescriptionNarrativeMessages;
            }
        }

        return $messages;
    }

    /**
     * returns rules for activity description.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getWarningForActivityDescription($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $descriptionIndex => $description) {
            $narrativeForm = sprintf('%s.activity_description.%s', $formBase, $descriptionIndex);

            foreach (
                $this->getWarningForNarrative(
                    $description['narrative'],
                    $narrativeForm
                ) as $locationActivityDescriptionIndex => $locationActivityDescriptionNarrativeRules
            ) {
                $rules[$locationActivityDescriptionIndex] = $locationActivityDescriptionNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * returns rules for activity description.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getErrorsForActivityDescription($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $descriptionIndex => $description) {
            $narrativeForm = sprintf('%s.activity_description.%s', $formBase, $descriptionIndex);

            foreach (
                $this->getErrorsForNarrative(
                    $description['narrative'],
                    $narrativeForm
                ) as $locationActivityDescriptionIndex => $locationActivityDescriptionNarrativeRules
            ) {
                $rules[$locationActivityDescriptionIndex] = $locationActivityDescriptionNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * returns messages for activity description.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForActivityDescription($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $descriptionIndex => $description) {
            $narrativeForm = sprintf('%s.activity_description.%s', $formBase, $descriptionIndex);

            foreach (
                $this->getMessagesForNarrative(
                    $description['narrative'],
                    $narrativeForm
                ) as $locationActivityDescriptionIndex => $locationActivityDescriptionNarrativeMessages
            ) {
                $messages[$locationActivityDescriptionIndex] = $locationActivityDescriptionNarrativeMessages;
            }
        }

        return $messages;
    }

    /**
     * returns rules for administrative.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getErrorsForAdministrative($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $administrativeIndex => $administrative) {
            $administrativeForm = sprintf('%s.administrative.%s', $formBase, $administrativeIndex);
            $rules[sprintf('%s.vocabulary', $administrativeForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'GeographicVocabulary',
                        'Activity',
                        false
                    )
                )
            );
            $rules[sprintf('%s.code', $administrativeForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('Country', 'Activity', false)
                )
            );
            $rules[sprintf('%s.level', $administrativeForm)] = 'nullable|min:0|integer';
        }

        return $rules;
    }

    /**
     * returns messages for administrative.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForAdministrative($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $administrativeIndex => $administrative) {
            $administrativeForm = sprintf('%s.administrative.%s', $formBase, $administrativeIndex);
            $messages[sprintf(
                '%s.vocabulary.in',
                $administrativeForm
            )]
                = trans('validation.this_field_is_invalid');
            $messages[sprintf('%s.code.in', $administrativeForm)] = trans(
                'validation.this_field_is_invalid'
            );
            $messages[sprintf(
                '%s.level.min',
                $administrativeForm
            )]
                = trans('validation.activity_location.administrative.level_min');
            $messages[sprintf(
                '%s.level.integer',
                $administrativeForm
            )]
                = trans('validation.activity_location.administrative.level_int');
        }

        return $messages;
    }

    /**
     * returns rules for point.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getErrorsForPoing($formFields, $formBase): array
    {
        $rules = [];
        $pointForm = sprintf('%s.point.0', $formBase);
        $positionForm = sprintf('%s.pos.0', $pointForm);
        $latitude = sprintf('%s.latitude', $positionForm);
        $longitude = sprintf('%s.longitude', $positionForm);
        $rules[$latitude] = 'nullable|numeric';
        $rules[$longitude] = 'nullable|numeric';

        return $rules;
    }

    /**
     * returns messages for point.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForPoint($formFields, $formBase): array
    {
        $messages = [];

        $pointForm = sprintf('%s.point.0', $formBase);
        $positionForm = sprintf('%s.pos.0', $pointForm);
        $messages[sprintf('%s.latitude.numeric', $positionForm)] = trans(
            'validation.activity_location.point.latitude_numeric'
        );
        $messages[sprintf('%s.longitude.numeric', $positionForm)] = trans(
            'validation.activity_location.point.longitude_numeric'
        );

        return $messages;
    }
}
