<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\Identifier;

use App\IATI\Services\Activity\ActivityService;
use App\Rules\NoLeadingWhiteSpaceInActivityIdentifier;
use App\Rules\NoSpacesInBetweenInActivityIdentifier;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class IdentifierRequest.
 */
class IdentifierRequest extends FormRequest
{
    /**
     * @var ActivityService
     */
    protected ActivityService $activityService;

    /**
     * Get the validation rules that apply to the request.
     *
     * @param bool $fileUpload
     *
     * @return array
     */
    public function rules(bool $fileUpload = false): array
    {
        $totalRules = [$this->getWarningForIdentifier(), $this->getErrorsForIdentifier(auth()->user())];

        return mergeRules($totalRules);
    }

    /**
     * Return critical rules for identifier.
     *
     * @param Authenticatable|null $authUser
     * @param bool                 $fileUpload
     * @param string               $elementName
     * @param string               $fileUploadType
     * @param string               $iatiIdentifierText
     *
     * @return array
     */
    public function getErrorsForIdentifier(?Authenticatable $authUser, bool $fileUpload = false, string $elementName = '', string $fileUploadType = 'csv', string $iatiIdentifierText = ''): array
    {
        if (!$fileUpload) {
            return $this->getValidationRulesForManualEntry($authUser);
        }

        return $this->getValidationRulesForFileUpload($authUser, $elementName, $fileUploadType, $iatiIdentifierText);
    }

    /**
     * Return validation rule for form submission.
     *
     * @param Authenticatable $authUser
     *
     * @return array
     */
    private function getValidationRulesForManualEntry(Authenticatable $authUser): array
    {
        $activityIdentifiers = [];
        /** @var $activityService ActivityService */
        $activityService = app(ActivityService::class);
        $organizationActivityIdentifiers = $activityService->getActivityIdentifiersForOrganization($authUser->organization->id);
        $activity = $activityService->getActivity(request()->segment(2));

        foreach ($organizationActivityIdentifiers as $identifier) {
            if ($identifier->iati_identifier['activity_identifier'] !== $activity->iati_identifier['activity_identifier']) {
                $activityIdentifiers[] = $identifier->iati_identifier['activity_identifier'];
            }
        }

        return [
            'activity_identifier' => ['required', Rule::notIn($activityIdentifiers), new NoSpacesInBetweenInActivityIdentifier(), new NoLeadingWhiteSpaceInActivityIdentifier(request()->get('iati_identifier_text'), $authUser->organization->identifier)],
        ];
    }

    /**
     * Return validation rules for file upload case.
     *
     * @param Authenticatable|null $authUser
     * @param string                                          $elementName
     * @param string                                          $fileUploadType
     * @param string                                          $iatiIdentifierText
     *
     * @return array|array[]
     */
    private function getValidationRulesForFileUpload(?Authenticatable $authUser, string $elementName, string $fileUploadType, string $iatiIdentifierText): array
    {
        $ruleKey = empty($elementName) ? 'activity_identifier' : "$elementName.activity_identifier";

        $rules = [
            $ruleKey => [
                'required',
                new NoSpacesInBetweenInActivityIdentifier(),
            ],
        ];

        if ($fileUploadType === 'xml' && $authUser) {
            $rules[$ruleKey][] = new NoLeadingWhiteSpaceInActivityIdentifier($iatiIdentifierText, $authUser->organization->identifier);
        }

        return $rules;
    }

    /**
     * Return rules for identifier.
     *
     * @return array
     */
    public function getWarningForIdentifier(): array
    {
        return [
            'iati_identifier_text' => 'sometimes',
        ];
    }

    /**
     * Get the error message as required.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'activity_identifier.not_in' => trans('validation.attribute_exists'),
        ];
    }
}
