<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Arr;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class GeneralController.
 */
class GeneralController extends Controller
{
    /**
     * Extracts mimetype of file or url.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getFileExtension(Request $request): JsonResponse
    {
        try {
            $fileUrl = $request->query('url');
            $type = $request->query('type');
            $mimeType = '';

            if (empty($fileUrl)) {
                $translatedMessage = 'Please enter file url to get mimetype.';

                return response()->json([
                    'success' => false,
                    'message' => $translatedMessage,
                    'data' => [
                        'mimetype' => $mimeType,
                        'type' => $type,
                        'url' => $fileUrl,
                        'validity' => filter_var($fileUrl, FILTER_VALIDATE_URL),
                    ],
                ]);
            }

            $fileFormat = getCodeList('FileFormat', 'Activity', filterDeprecated: true);
            $completePath = 'AppData/Data/Activity/Extension.json';
            $jsonContent = getJsonFromSource($completePath);
            $extensions = array_flip(json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR));
            $completePath = 'AppData/Data/Activity/AdditionalExtension.json';
            $jsonContent = getJsonFromSource($completePath);
            $additionalExtensions = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
            $fileFragment = $type === 'url' ? Arr::get(parse_url($fileUrl), 'path', null) : $fileUrl;
            $fileExtension = $fileFragment ? pathinfo($fileFragment, PATHINFO_EXTENSION) : null;

            if (!empty($fileExtension) && (Arr::get($extensions, $fileExtension, false) || Arr::get($additionalExtensions, $fileExtension, false))) {
                $mimeType = isset($extensions[$fileExtension]) ? Arr::get($extensions, $fileExtension, '') : Arr::get($additionalExtensions, $fileExtension, '');
            } elseif (str_starts_with($fileUrl, url('/'))) {
                $mimeType = 'text/html';
            } elseif (filter_var($fileUrl, FILTER_VALIDATE_URL)) {
                $fileUrl = filter_var($fileUrl, FILTER_VALIDATE_URL);
                $ch = curl_init($fileUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_exec($ch);
                $curlInfo = curl_getinfo($ch);
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if (!($statusCode >= 400 && $statusCode <= 500)) {
                    $mimeType = 'text/html';

                    if ($curlInfo['content_type']) {
                        $mimeType = explode(';', $curlInfo['content_type'])[0];
                    }
                } else {
                    $translatedMessage = trans('api/general_controller.please_ensure_that_url_is_correct_and_is_publicly_accessible');

                    return response()->json([
                        'success' => false,
                        'message' => $translatedMessage,
                        'data' => [
                            'mimetype' => $mimeType,
                        ],
                    ]);
                }
            }

            if (!array_key_exists($mimeType, $fileFormat)) {
                $mimeType = '';
            }
            $translatedMessage = 'Mimetype Fetched Successfully.';

            return response()->json([
                'success' => true,
                'message' => $translatedMessage,
                'data' => [
                    'mimetype' => $mimeType,
                ],
            ]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = 'Error Occurred While Mapping The File Extension.';

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }
}
