<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\UploadActivity;

use App\Http\Requests\Activity\ActivityBaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class ImportXlsRequest.
 */
class ImportXlsRequest extends ActivityBaseRequest
{
    /**
     * Construct.
     */
    public function __construct()
    {
        Validator::extend(
            'activity_file',
            function ($attribute, $value, $parameters, $validator) {
                $fileMime = $value->getClientOriginalExtension();
                $extension = ['xls', 'xlsx', 'odt', 'odf', 'ods'];

                return in_array($fileMime, $extension, true);
            }
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return ['activity' => 'required|activity_file| max:10000', 'xlsType' => 'required|string'];
    }

    /**
     * prepare error message.
     *
     * @return array
     */
    public function messages(): array
    {
        $messages['activity.required'] = trans('validation.activity_upload.xls_required');
        $messages['activity.activity_file'] = trans('validation.activity_upload.xls_activity_file');
        $messages['activity.max'] = trans('validation.activity_upload.max');

        return $messages;
    }

    /**
     * Overwritten failedValidation method for JSON response.
     *
     * @param  Validator  $validator
     *
     * @return ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): ValidationException
    {
        $response = new JsonResponse(['success' => false, 'errors' => $validator->errors()]);

        throw new ValidationException($validator, $response);
    }
}
