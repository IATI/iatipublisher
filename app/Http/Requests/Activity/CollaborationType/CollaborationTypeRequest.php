<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\CollaborationType;

use App\Http\Requests\Activity\ActivityBaseRequest;
use App\Rules\SingleCharacter;

/**
 * Class CollaborationTypeRequest.
 */
class CollaborationTypeRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @param $collaboration
     *
     * @return array
     */
    public function rules($collaboration = null): array
    {
        $totalRules = [
            $this->getWarningForCollaborationType($collaboration),
            $this->getErrorsForCollaborationType($collaboration),
        ];

        return mergeRules($totalRules);
    }

    /**
     * returns critical rule for capital spend.
     *
     * @param $collaboration
     *
     * @return array
     */
    public function getWarningForCollaborationType($collaboration = null): array
    {
        return [];
    }

    /**
     * returns critical rule for capital spend.
     *
     * @param $collaboration
     *
     * @return array
     */
    public function getErrorsForCollaborationType($collaboration = null): array
    {
        if ($collaboration && is_array($collaboration)) {
            return [
                'collaboration_type' => ['nullable', new SingleCharacter('collaboration_type')],
            ];
        }

        return [
            'collaboration_type' => sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles(
                            'CollaborationType',
                            'Activity',
                            false
                        )
                    )
                )
            ),
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
            'in'   => trans('validation.activity_collaboration_type.in'),
            'size' => trans('validation.activity_collaboration_type.size'),
        ];
    }
}
