<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\PolicyMarker;

use App\Http\Requests\Activity\ActivityBaseRequest;
use Illuminate\Support\Arr;

/**
 * Class PolicyMarkerRequest.
 */
class PolicyMarkerRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $data = $this->get('policy_marker');

        $totalRules = [
            $this->getWarningForPolicyMarker($data),
            $this->getErrorsForPolicyMarker($data),
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
        return $this->getMessagesForPolicyMarker($this->get('policy_marker'));
    }

    /**
     * Returns rules for related activity.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getWarningForPolicyMarker(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $policyMarkerIndex => $policyMarker) {
            $policyMarkerForm = sprintf('policy_marker.%s', $policyMarkerIndex);

            foreach (
                $this->getWarningForNarrative(
                    $policyMarker['narrative'],
                    $policyMarkerForm
                ) as $policyMarkerNarrativeIndex => $narrativeRules
            ) {
                $rules[$policyMarkerNarrativeIndex] = $narrativeRules;
            }

            if (Arr::get($policyMarker, 'policy_marker_vocabulary') === '99') {
                foreach (array_keys($policyMarker['narrative']) as $narrativeIndex) {
                    $rules[sprintf('%s.narrative.%s.narrative', $policyMarkerForm, $narrativeIndex)]
                        = 'required';
                }
            }
        }

        return $rules;
    }

    /**
     * Returns criticalrules for related activity.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getErrorsForPolicyMarker(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $policyMarkerIndex => $policyMarker) {
            $policyMarkerForm = sprintf('policy_marker.%s', $policyMarkerIndex);
            $rules[sprintf('%s.policy_marker_vocabulary', $policyMarkerForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'PolicyMarkerVocabulary',
                        'Activity',
                        false
                    )
                )
            );
            $rules[sprintf('%s.significance', $policyMarkerForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'PolicySignificance',
                        'Activity',
                        false
                    )
                )
            );
            $rules[sprintf('%s.policy_marker', $policyMarkerForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('PolicyMarker', 'Activity', false)
                )
            );
            $rules[sprintf('%s.vocabulary_uri', $policyMarkerForm)] = 'nullable|url';

            foreach (
                $this->getErrorsForNarrative(
                    $policyMarker['narrative'],
                    $policyMarkerForm
                ) as $policyMarkerNarrativeIndex => $narrativeRules
            ) {
                $rules[$policyMarkerNarrativeIndex] = $narrativeRules;
            }
        }

        return $rules;
    }

    /**
     * Returns messages for related activity validations.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForPolicyMarker(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $policyMarkerIndex => $policyMarker) {
            $policyMarkerForm = sprintf('policy_marker.%s', $policyMarkerIndex);
            $messages[sprintf(
                '%s.policy_marker_vocabulary.in',
                $policyMarkerForm
            )]
                = trans('validation.vocabulary_is_invalid');
            $messages[sprintf('%s.significance.in', $policyMarkerForm)] = trans('validation.this_field_is_invalid');
            $messages[sprintf('%s.policy_marker.in', $policyMarkerForm)] = trans('validation.activity_policy_marker.invalid_code');
            $messages[sprintf('%s.vocabulary_uri.url', $policyMarkerForm)] = trans('validation.url_valid');

            foreach (
                $this->getMessagesForNarrative(
                    $policyMarker['narrative'],
                    $policyMarkerForm
                ) as $policyMarkerNarrativeIndex => $narrativeMessages
            ) {
                $messages[$policyMarkerNarrativeIndex] = $narrativeMessages;
            }

            if (Arr::get($policyMarker, 'policy_marker_vocabulary') === '99') {
                foreach (array_keys($policyMarker['narrative']) as $narrativeIndex) {
                    $messages[sprintf(
                        '%s.narrative.%s.narrative.required',
                        $policyMarkerForm,
                        $narrativeIndex
                    )]
                        = trans('validation.activity_policy_marker.narrative_required');
                }
            }
        }

        return $messages;
    }
}
