<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity;

use App\IATI\Services\Activity\ActivityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ActivityCreateRequest.
 */
class ActivityCreateRequest extends FormRequest
{
    /**
     * @var ActivityService
     */
    protected $activityService;

    /**
     * ActivityCreateRequest constructor.
     *
     * @param  ActivityService  $activityService
     */
    public function __construct(ActivityService $activityService)
    {
        parent::__construct();
        $this->activityService = $activityService;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $activityIdentifiers = [];
        $organizationActivityIdentifiers = $this->activityService->getActivityIdentifiersForOrganization(
            auth()->user()->organization->id
        );

        if (count($organizationActivityIdentifiers)) {
            foreach ($organizationActivityIdentifiers as $identifier) {
                $activityIdentifiers[] = $identifier->iati_identifier['activity_identifier'];
            }
        }

        return [
            'narrative'            => ['required'],
            'activity_identifier'  => ['required', Rule::notIn($activityIdentifiers), 'not_regex:/(&|!|\/|\||\?)/'],
            'iati_identifier_text' => ['sometimes'],
        ];
    }

    /**
     * Custom validation messages.
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
