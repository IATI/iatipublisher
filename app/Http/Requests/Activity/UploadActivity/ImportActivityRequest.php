<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\UploadActivity;

use App\Http\Requests\Activity\ActivityBaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class ImportActivityRequest.
 */
class ImportActivityRequest extends ActivityBaseRequest
{
    /**
     * Construct.
     */
    public function __construct()
    {
        Validator::extend(
            'activity_file',
            function ($attribute, $value, $parameters, $validator) {
                $mimes = ['text/csv', 'text/xml', 'application/xml'];
                $fileMime = $value->getClientMimeType();

                return in_array($fileMime, $mimes, true);
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
        $rules = [];
        $rules['activity'] = 'required|activity_file| max:10000';

        return $rules;
    }

    /**
     * prepare error message.
     *
     * @return array
     */
    public function messages(): array
    {
        $messages['activity.required'] = trans('validation.activity_upload.required');
        $messages['activity.activity_file'] = trans('validation.activity_upload.activity_file');
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
