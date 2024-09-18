<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\DefaultTiedStatus;

use App\Http\Requests\Activity\ActivityBaseRequest;

/**
 * Class DefaultTiedStatusRequest.
 */
class DefaultTiedStatusRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @param $tied_status
     *
     * @return array
     */
    public function rules($tied_status = null): array
    {
        $totalRules = [$this->getErrorsForDefaultTiedStatus(), $this->getWarningForDefaultTiedStatus()];

        return mergeRules($totalRules);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param $tied_status
     *
     * @return array
     */
    public function getWarningForDefaultTiedStatus(): array
    {
        return [];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param $tied_status
     *
     * @return array
     */
    public function getErrorsForDefaultTiedStatus($tied_status = null): array
    {
        if ($tied_status && is_array($tied_status)) {
            return [
                'default_tied_status' => 'nullable|size:1',
            ];
        }

        return [
            'default_tied_status' => sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('TiedStatus', 'Activity', false)
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
            'in'   => trans('validation.activity_default_tied_status.in'),
            'size' => trans('validation.activity_default_tied_status.size'),
        ];
    }
}
