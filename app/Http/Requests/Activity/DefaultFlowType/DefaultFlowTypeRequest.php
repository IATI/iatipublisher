<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\DefaultFlowType;

use App\Http\Requests\Activity\ActivityBaseRequest;

/**
 * Class DefaultFlowTypeRequest.
 */
class DefaultFlowTypeRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @param $default_flow_type
     *
     * @return array
     */
    public function rules($default_flow_type = null): array
    {
        $totalRules = [$this->getErrorsForDefaultFlowType($default_flow_type), $this->getWarningForDefaultFlowType()];

        return mergeRules($totalRules);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param $default_flow_type
     *
     * @return array
     */
    public function getErrorsForDefaultFlowType($default_flow_type = null): array
    {
        if ($default_flow_type && is_array($default_flow_type)) {
            return [
                'default_flow_type' => 'nullable|size:1',
            ];
        }

        return [
            'default_flow_type' => sprintf('nullable|in:%s', implode(',', array_keys(
                $this->getCodeListForRequestFiles('FlowType', 'Activity', false)
            ))),
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param $default_flow_type
     *
     * @return array
     */
    public function getWarningForDefaultFlowType(): array
    {
        return [];
    }

    /**
     * Get the error message as required.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'in'   => 'The default flow type does not exist.',
            'size' => 'The default flow type cannot have more than one value.',
        ];
    }
}
