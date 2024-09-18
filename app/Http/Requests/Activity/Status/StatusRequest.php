<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\Status;

use App\Http\Requests\Activity\ActivityBaseRequest;
use App\Rules\SingleCharacter;

/**
 * Class StatusRequest.
 */
class StatusRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @param $status
     *
     * @return array
     */
    public function rules($status = null): array
    {
        $totalRules = [$this->getErrorsForActivityStatus($status), $this->getWarningForActivityStatus($status)];

        return mergeRules($totalRules);
    }

    /**
     * Get critical rules for status request.
     *
     * @param $status
     *
     * @return array
     */
    public function getWarningForActivityStatus($status): array
    {
        return [];
    }

    /**
     * Get critical rules for status request.
     *
     * @param $status
     *
     * @return array
     */
    public function getErrorsForActivityStatus($status): array
    {
        if ($status && is_array($status)) {
            return [
                'activity_status' => ['nullable', new SingleCharacter('activity_status')],
            ];
        }

        return [
            'activity_status' => sprintf('nullable|in:%s', implode(',', array_keys(
                $this->getCodeListForRequestFiles('ActivityStatus', 'Activity', false)
            ))),
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
            'in'   => trans('validation.activity_status.in'),
            'size' => trans('validation.activity_status.size'),
        ];
    }
}
