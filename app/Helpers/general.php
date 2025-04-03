<?php

declare(strict_types=1);

use App\Constants\CoreElements;
use App\IATI\Models\User\Role;
use App\IATI\Services\Setting\SettingService;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

if (!function_exists('dashesToCamelCase')) {
    /**
     * Changes dash case string to camelcase.
     *
     * @param      $string
     * @param bool $capitalizeFirstCharacter
     *
     * @return string
     */
    function dashesToCamelCase($string, bool $capitalizeFirstCharacter = false): string
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }
}

if (!function_exists('readJsonFile')) {
    /**
     * Reads JSON file.
     *
     * @param $filePath
     *
     * @return array
     * @throws JsonException
     */
    function readJsonFile($filePath): array
    {
        return json_decode(file_get_contents(app_path($filePath)), true, 512, JSON_THROW_ON_ERROR);
    }
}

if (!function_exists('readElementGroup')) {
    /**
     * Reads ElementGroup json file.
     *
     * @return array
     */
    function readElementGroup(): array
    {
        $completePath = 'AppData/Data/Activity/ElementGroup.json';

        return json_decode(getJsonFromSource($completePath), true);
    }
}

if (!function_exists('readElementJsonSchema')) {
    /**
     * Reads elementJsonSchema.
     *
     * @return array
     * @throws JsonException
     */
    function readElementJsonSchema(): array
    {
        $currentLanguage = App::getLocale();
        $cacheKey = "elementJsonSchema_$currentLanguage";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $jsonContentAsAssocArray = readJsonFile('IATI/Data/elementJsonSchema.json');

        $jsonContentAsFlattenedArray = Arr::dot($jsonContentAsAssocArray);

        foreach ($jsonContentAsFlattenedArray as $key => &$value) {
            if (preg_match('/label|placeholder|hover_text|help_text|helper_text/', $key)) {
                $value = trans($value);
            }
        }

        $cacheableData = Arr::undot($jsonContentAsFlattenedArray);

        Cache::put($cacheKey, $cacheableData);

        return $cacheableData;
    }
}

if (!function_exists('readOrganizationElementJsonSchema')) {
    /**
     * Reads elementJsonSchema.
     *
     * @return array
     * @throws JsonException
     */
    function readOrganizationElementJsonSchema(): array
    {
        $currentLanguage = App::getLocale();
        $cacheKey = "organizationElementJsonSchema_$currentLanguage";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $jsonContentAsAssocArray = readJsonFile('IATI/Data/organizationElementJsonSchema.json');

        $jsonContentAsFlattenedArray = Arr::dot($jsonContentAsAssocArray);

        foreach ($jsonContentAsFlattenedArray as $key => &$value) {
            if (preg_match('/label|placeholder|hover_text|help_text/', $key)) {
                {
                    $value = trans($value);
                }
            }
        }

        $cacheableData = Arr::undot($jsonContentAsFlattenedArray);

        Cache::put($cacheKey, $cacheableData);

        return $cacheableData;
    }
}

if (!function_exists('getElementSchema')) {
    /**
     * Returns element schema.
     *
     * @throws JsonException
     */
    function getElementSchema($element): array
    {
        return getArr(readElementJsonSchema(), $element);
    }
}

if (!function_exists('getOrganizationElementSchema')) {
    /**
     * Returns organization element schema.
     *
     * @throws JsonException
     */
    function getOrganizationElementSchema($element): array
    {
        return getArr(readOrganizationElementJsonSchema(), $element);
    }
}

if (!function_exists('getElements')) {
    /**
     * Returns elements list.
     *
     * @return array
     * @throws JsonException
     */
    function getElements(): array
    {
        $elementJsonSchema = readElementJsonSchema();
        unset($elementJsonSchema['indicator'], $elementJsonSchema['period']);

        return array_keys($elementJsonSchema);
    }
}

if (!function_exists('getOrganizationElements')) {
    /**
     * Returns elements list.
     *
     * @return array
     * @throws JsonException
     */
    function getOrganizationElements(): array
    {
        return array_keys(readOrganizationElementJsonSchema());
    }
}

if (!function_exists('getDefaultElementStatus')) {
    /**
     * Returns Default Elements Status.
     *
     * @return array
     */
    function getDefaultElementStatus(): array
    {
        return [
            'iati_identifier' => false,
            'title' => false,
            'description' => false,
            'activity_status' => false,
            'activity_date' => false,
            'activity_scope' => false,
            'recipient_country' => false,
            'recipient_region' => false,
            'collaboration_type' => false,
            'default_flow_type' => false,
            'default_finance_type' => false,
            'default_aid_type' => false,
            'default_tied_status' => false,
            'capital_spend' => false,
            'related_activity' => false,
            'conditions' => false,
            'sector' => false,
            'humanitarian_scope' => false,
            'legacy_data' => false,
            'tag' => false,
            'policy_marker' => false,
            'other_identifier' => false,
            'country_budget_items' => false,
            'budget' => false,
            'participating_org' => false,
            'document_link' => false,
            'contact_info' => false,
            'location' => false,
            'planned_disbursement' => false,
            'transactions' => false,
            'result' => false,
        ];
    }
}

if (!function_exists('getDefaultOrganizationElementStatus')) {
    /**
     * Returns Default Elements Status.
     *
     * @return array
     */
    function getDefaultOrganizationElementStatus(): array
    {
        return [
            'identifier' => false,
            'name' => false,
            'reporting_org' => false,
            'total_budget' => false,
            'total_expenditure' => false,
            'recipient_org_budget' => false,
            'recipient_country_budget' => false,
            'recipient_region_budget' => false,
            'document_link' => false,
        ];
    }
}

if (!function_exists('getMandatoryElements')) {
    /**
     * Returns Core Elements.
     *
     * @return array
     */
    function getMandatoryElements(): array
    {
        return [
            'name',
            'reporting_org',
        ];
    }
}

if (!function_exists('getMandatoryElementsWithTrueValue')) {
    /**
     * Returns Core Elements with true value.
     *
     * @return array
     */
    function getMandatoryElementsWithTrueValue(): array
    {
        return [
            'identifier' => true,
            'name' => true,
            'reporting_org' => true,
            'total_budget' => true,
            'total_expenditure' => true,
            'recipient_org_budget' => true,
            'recipient_country_budget' => true,
            'recipient_region_budget' => true,
            'document_link' => true,
        ];
    }
}

if (!function_exists('isCoreElementCompleted')) {
    /**
     * Checks if all core elements are complete.
     *
     * @param $elementStatus
     *
     * @return bool
     */
    function isCoreElementCompleted($elementStatus): bool
    {
        return empty(array_diff_assoc(CoreElements::getCoreElementsWithTrueValue(), $elementStatus));
    }
}

if (!function_exists('isMandatoryElementCompleted')) {
    /**
     * Checks if all core elements are complete.
     *
     * @param $elementStatus
     *
     * @return bool
     */
    function isMandatoryElementCompleted($elementStatus): bool
    {
        return Arr::get($elementStatus, 'name', false) && Arr::get($elementStatus, 'reporting_org', false);
    }
}

if (!function_exists('getArr')) {
    /**
     * Checks if array key exists.
     *
     * @param $data
     * @param $key
     *
     * @return array
     */
    function getArr($data, $key): array
    {
        return array_key_exists($key, $data) ? $data[$key] : [];
    }
}

if (!function_exists('getCodeList')) {
    /**
     * return codeList array from json codeList.
     *
     * @param      $listName
     * @param      $listType
     * @param bool $code
     * @param bool $filterDeprecated
     * @param array $deprecationStatusMap
     *
     * @return array
     * @throws JsonException
     */
    function getCodeList($listName, $listType, bool $code = true, bool $filterDeprecated = false, $deprecationStatusMap = []): array
    {
        $completePath = "AppData/Data/$listType/$listName.json";
        $codeListFromFile = getJsonFromSource($completePath);
        $codeLists = json_decode($codeListFromFile, true, 512, JSON_THROW_ON_ERROR);
        $codeList = $codeLists[$listName];

        if ($filterDeprecated) {
            $possibleSuffixes = getKeysThatUseThisCodeList($completePath);

            $deprecatedCodesInUse = filterArrayByKeyEndsWithPossibleSuffixes(flattenArrayWithKeys($deprecationStatusMap), $possibleSuffixes);

            $codeList = array_filter($codeList, function ($item) use ($deprecatedCodesInUse) {
                return filterDeprecated($item, Arr::flatten($deprecatedCodesInUse));
            });
        }

        $data = [];

        foreach ($codeList as $list) {
            $data[$list['code']] = ($code) ? $list['code'] . (array_key_exists('name', $list) ? ' - ' . $list['name'] : '') : $list['name'];
        }

        return $data;
    }
}

if (!function_exists('getCodeListArray')) {
    /**
     * return codeList array from codeList.
     *
     * @param      $listName
     * @param      $listType
     * @param bool $code
     *
     * @return array
     */
    function getCodeListArray($listName, $listType, bool $code = true): array
    {
        $completePath = "AppData/Data/$listType/$listName.json";
        $content = getJsonFromSource($completePath);
        $codeListFromFile = json_decode($content);
        $data = [];

        foreach ($codeListFromFile as $key => $value) {
            $data[$key] = ($code) ? $key . ' - ' . $value : $value;
        }

        return $data;
    }
}

if (!function_exists('getList')) {
    /**
     * Return codeList array from json codeList.
     *
     * @param string $filePath
     * @param bool   $code
     *
     * @return array
     * @throws JsonException
     */
    function getList(string $filePath, bool $code = true): array
    {
        $completePath = "AppData/Data/$filePath";
        $codeListFromFile = getJsonFromSource($completePath);
        $codeLists = json_decode($codeListFromFile, true, 512, JSON_THROW_ON_ERROR);
        $codeList = last($codeLists);
        $data = [];

        foreach ($codeList as $list) {
            $data[$list['code']] = ($code) ? $list['code'] . (
                array_key_exists(
                    'name',
                    $list
                ) ? ' - ' . $list['name'] : ''
            ) : $list['name'];
        }

        return $data;
    }
}

if (!function_exists('getTransactionTypes')) {
    /**
     * Get activity transaction data type.
     *
     * @return array
     * @throws JsonException
     */
    function getTransactionTypes(): array
    {
        return [
            'transactionType'          => getCodeList('TransactionType', 'Activity', false),
            'organizationType'         => getCodeList('OrganizationType', 'Organization', false),
            'disbursementChannel'      => getCodeList('DisbursementChannel', 'Activity', false),
            'sectorVocabulary'         => getCodeList('SectorVocabulary', 'Activity', false),
            'sectorCode'               => getCodeList('SectorCode', 'Activity', false),
            'sectorCategory'           => getCodeList('SectorCategory', 'Activity', false),
            'unsdgGoals'               => getCodeList('UNSDG-Goals', 'Activity', false),
            'unsdgTargets'             => getCodeList('UNSDG-Targets', 'Activity', false),
            'countryCode'              => getCodeList('Country', 'Activity', false),
            'regionVocabulary'         => getCodeList('RegionVocabulary', 'Activity', false),
            'regionCode'               => getCodeList('Region', 'Activity', false),
            'flowType'                 => getCodeList('FlowType', 'Activity', false),
            'financeType'              => getCodeList('FinanceType', 'Activity', false),
            'tiedStatusType'           => getCodeList('TiedStatus', 'Activity', false),
            'aidTypeVocabulary'        => getCodeList('AidTypeVocabulary', 'Activity', false),
            'aidType'                  => getCodeList('AidType', 'Activity', false),
            'cashAndVoucherModalities' => getCodeList('CashandVoucherModalities', 'Activity', false),
            'earMarkingCategory'       => getCodeList('EarmarkingCategory', 'Activity', false),
            'earMarkingModality'       => getCodeList('EarmarkingModality', 'Activity', false),
            'languages'                => getCodeList('Language', 'Activity', false),
        ];
    }
}

if (!function_exists('getResultTypes')) {
    /**
     * Get activity result data type.
     *
     * @return array
     * @throws JsonException
     */
    function getResultTypes(): array
    {
        return [
            'resultType'       => getCodeList('ResultType', 'Activity', false),
            'resultVocabulary' => getCodeList('ResultVocabulary', 'Activity', false),
            'indicatorMeasure' => getCodeList('IndicatorMeasure', 'Activity', false),
            'language'         => getCodeList('Language', 'Activity', false),
            'documentCategory' => getCodeList('DocumentCategory', 'Activity', false),
        ];
    }
}

if (!function_exists('getIndicatorTypes')) {
    /**
     * Get activity indicator data type.
     *
     * @return array
     * @throws JsonException
     */
    function getIndicatorTypes(): array
    {
        return [
            'indicatorVocabulary' => getCodeList('IndicatorVocabulary', 'Activity'),
            'indicatorMeasure'    => getCodeList('IndicatorMeasure', 'Activity', false),
            'language'            => getCodeList('Language', 'Activity', false),
            'documentCategory'    => getCodeList('DocumentCategory', 'Activity', false),
            'fileFormat'          => getCodeList('FileFormat', 'Activity', false),
        ];
    }
}

if (!function_exists('getPeriodTypes')) {
    /**
     * Get activity periods data type.
     *
     * @return array
     * @throws JsonException
     */
    function getPeriodTypes(): array
    {
        return [
            'indicatorMeasure' => getCodeList('IndicatorMeasure', 'Activity', false),
            'language'         => getCodeList('Language', 'Activity', false),
            'documentCategory' => getCodeList('DocumentCategory', 'Activity', false),
            'fileFormat'       => getCodeList('FileFormat', 'Activity', false),
        ];
    }
}

if (!function_exists('generateToastData')) {
    /**
     * Generates toast array.
     *
     * @return array
     */
    function generateToastData(): array
    {
        $toast['message'] = Session::exists('error') ? Session::get('error') : (Session::exists('success') ? Session::get('success') : '');
        $toast['type'] = Session::exists('error') ? false : true;
        Session::forget('success');
        Session::forget('error');

        return $toast;
    }
}

if (!function_exists('isCoreElement')) {
    /**
     * Checks if an activity element is a core element.
     *
     * @param $element
     *
     * @return bool
     */
    function isCoreElement($element): bool
    {
        return in_array($element, CoreElements::all(), true);
    }
}

if (!function_exists('isMandatoryElement')) {
    /**
     * Checks if an activity element is a core element.
     *
     * @param $element
     *
     * @return bool
     */
    function isMandatoryElement($element): bool
    {
        $mandatory_elements = [
            'indicator',
        ];

        return in_array($element, $mandatory_elements, true);
    }
}

if (!function_exists('getTableConfig')) {
    /**
     * Gets the table config of activity.
     *
     * @param $module
     *
     * @return string[][]
     */
    function getTableConfig($module): array
    {
        $tableConfig = [
            'activity' => [
                'orderBy'   => ['updated_at', 'complete_percentage'],
                'direction' => ['asc', 'desc'],
                'filterBy'  => ['all', 'published', 'ready_for_republishing', 'draft'],
            ],
            'organisation' => [
                'orderBy'   => ['updated_at', 'all_activities_count', 'name', 'registered_on', 'publisher_type', 'data_license', 'country', 'last_logged_in'],
                'direction' => ['asc', 'desc'],
                'filters' => [
                    'completeness'      => 'single',
                    'registration_type' => 'single',
                    'country'           => 'multiple',
                    'publisher_type'    => 'multiple',
                    'data_license'      => 'multiple',
                ],
            ],
            'user' => ['orderBy' => ['username', 'publisher_name', 'created_at', 'organisation', 'admin', 'general', 'active', 'deactivated', 'total', 'last_logged_in'], 'direction' => ['asc', 'desc']],
            'audit' => ['orderBy' => ['user_id', 'user_type', 'event', 'auditable_type', 'created_at'], 'direction' => ['asc', 'desc']],
            'transaction' => [
                'orderBy'   => ['type', 'value', 'date'],
                'direction' => ['asc', 'desc'],
                'filterBy'  => [
                    'all',
                    'incoming_funds',
                    'outgoing_commitment',
                    'disbursement',
                    'expenditure',
                    'others',
                ],
            ],
            'result'=>[
                'orderBy'   => ['name', 'type'],
                'direction' => ['asc', 'desc'],
                'filterBy'  => [
                    'all',
                    'output',
                    'outcome',
                    'impact',
                    'other',
                ],
            ],
        ];

        return $tableConfig[$module];
    }
}

if (!function_exists('getDefaultLanguage')) {
    /**
     * Gets the default language.
     *
     * @param $defaultValues
     *
     * @return string
     */
    function getDefaultLanguage($defaultValues): string
    {
        if (!empty($defaultValues) && array_key_exists('default_language', $defaultValues) && !empty($defaultValues['default_language'])) {
            return $defaultValues['default_language'];
        }

        return '';
    }
}

if (!function_exists('removeEmptyValues')) {
    /**
     * Removes empty values.
     *
     * @param $data
     */
    function removeEmptyValues(&$data): void
    {
        foreach ($data as &$subData) {
            if (is_array($subData)) {
                removeEmptyValues($subData);
            }
        }

        $data = array_filter(
            $data,
            function ($value) {
                return $value !== '' && $value != [];
            }
        );
    }
}

if (!function_exists('getNonArrayElements')) {
    /**
     * Returns array of elements which do not store data as array.
     *
     * @return array
     */
    function getNonArrayElements(): array
    {
        return ['activity_status', 'activity_scope', 'default_flow_type', 'default_finance_type', 'default_tied_status', 'capital_spend', 'collaboration_type', 'identifier', 'org_id', 'default_field_values', 'updated_at', 'created_at', 'id', 'iati_identifier', 'element_status', 'updated_by'];
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * Returns whether user is superadmin or not.
     *
     * @return bool
     */
    function isSuperAdmin(): bool
    {
        $superAdminId = [app(Role::class)->getSuperAdminId(), app(Role::class)->getIatiAdminId()];

        return in_array(auth()->user()->role_id, $superAdminId) || in_array(session()->get('role_id'), $superAdminId);
    }
}

if (!function_exists('isSuperAdminRoute')) {
    /**
     * Checks if the request route contains prefix SuperAdmin.
     *
     * @return bool
     */
    function isSuperAdminRoute(): bool
    {
        if (request()->route()) {
            $superAdminId = [app(Role::class)->getSuperAdminId(), app(Role::class)->getIatiAdminId()];

            return in_array(auth()->user()->role_id, $superAdminId);
        }

        return false;
    }
}

/**
 * Get csv header count.
 *
 * @return int
 */
function getCsvHeaderCount(): int
{
    return 69;
}

if (!function_exists('get_user_status')) {
    /**
     * Returns user status types.
     *
     * @return array
     */
    function getUserStatus(): array
    {
        return [
            1 => 'Active',
            0 => 'Inactive',
        ];
    }
}

if (!function_exists('get_language_preference')) {
    /**
     * Returns language preference types.
     *
     * @return array
     */
    function getLanguagePreference(): array
    {
        return [
            'en' => 'English',
            'fr' => 'French',
            'es' => 'Spanish',
        ];
    }
}

if (!function_exists('get_user_csv_header')) {
    /**
     * Returns user download csv header.
     *
     * @return array
     */
    function getUserCsvHeader(): array
    {
        return ['username' => 'User Name', 'full_name' => 'Full Name', 'organization_id' => 'Organization', 'email' => 'Email', 'role_id' => 'Role', 'created_at' => 'Joined On'];
    }
}

if (!function_exists('get_time_stamped_text')) {
    /**
     * Returns filename with ymdhis.
     *
     * @return array
     */
    function getTimeStampedText(string $filename): string
    {
        return sprintf('%s_%s', $filename, date('Y_m_d_His'));
    }
}

if (!function_exists('generateApiInfo')) {
    /**
     * Generates api log info for API logging.
     *
     * @param $request
     *
     * @return array
     */
    function generateApiInfo($method, $requestURI, $requestOption, $response = null): array
    {
        $responseBody = is_string($response) ? null : $response->getBody();
        $uri = Str::startsWith($requestURI, 'http') ? $requestURI : sprintf('%s/%s', env('IATI_API_ENDPOINT'), $requestURI);

        $requestInfo = [
            'method' => $method,
            'url' => $uri,
            'request' => $requestOption,
            'response' => is_string($response) ? $response : json_decode((string) $response->getBody(), true),
        ];

        if ($responseBody) {
            $responseBody->seek(0);
        }

        return $requestInfo;
    }
}

if (!function_exists('mergeRules')) {
    /**
     * Generates api log info for API logging.
     *
     * @param $request
     *
     * @return array
     */
    function mergeRules($totalRules): array
    {
        $mergedRules = [];

        foreach ($totalRules as $rules) {
            foreach ($rules as $index => $rule) {
                if (is_string($rule)) {
                    $rule = explode('|', $rule);
                }

                if (is_array($rule)) {
                    foreach (array_values($rule) as $ruleValue) {
                        if (!in_array($ruleValue, Arr::get($mergedRules, $index, []))) {
                            $mergedRules[$index][] = $ruleValue;
                        }
                    }
                }
            }
        }

        return $mergedRules;
    }
}

if (!function_exists('unsetErrorFields')) {
    /**
     * unset fields from imported activity that contains critical error.
     *
     * @param $importContent
     *
     * @return array
     *
     * @throws JsonException
     */
    function unsetErrorFields($importContent): array
    {
        $importData = json_decode(json_encode($importContent, JSON_THROW_ON_ERROR | 512), true, 512, JSON_THROW_ON_ERROR);
        $activity = $importData['data'];
        $errors = Arr::get($importData, 'errors.error', []);

        if (!empty($errors)) {
            foreach (array_values($errors) as $error) {
                foreach (array_keys($error) as $key) {
                    Arr::set($activity, $key, null);
                }
            }
        }

        $importData['data'] = $activity;

        return $importData;
    }
}

if (!function_exists('compareStringIgnoringWhitespace')) {
    /**
     * Check if 2 strings are exactly same after removing all whitespaces.
     *
     * @param string $string1
     * @param string $string2
     *
     * @return bool
     */
    function compareStringIgnoringWhitespace(string $string1, string $string2): bool
    {
        return preg_replace('/\s+/', '', $string1) === preg_replace('/\s+/', '', $string2);
    }
}

if (!function_exists('getAllocatedPercentageOfRecipientRegion')) {
    /**
     * Returns currently consumed % by recipient region.
     *
     * @param $activity
     *
     * @return float
     */
    function getAllocatedPercentageOfRecipientRegion($activity): float
    {
        $data = $activity->recipient_region;
        $groupedRegion = [];

        if (!empty($data)) {
            foreach ($data as $datum) {
                if (array_key_exists($datum['region_vocabulary'], $groupedRegion)) {
                    $groupedRegion[$datum['region_vocabulary']] += (float) $datum['percentage'];
                } else {
                    $groupedRegion[$datum['region_vocabulary']] = (float) $datum['percentage'];
                }
            }

            if (!empty($groupedRegion)) {
                $groupedRegion = array_values($groupedRegion);

                return $groupedRegion[0];
            }
        }

        return 0.0;
    }
}

if (!function_exists('getAllocatedPercentageOfRecipientCountry')) {
    /**
     * Returns currently consumed % by recipient country.
     *
     * @param $activity
     *
     * @return float
     */
    function getAllocatedPercentageOfRecipientCountry($activity): float
    {
        $data = $activity->recipient_country;
        $total = 0.0;

        if (!empty($data)) {
            foreach ($data as $datum) {
                $total += (float) $datum['percentage'];
            }
        }

        return $total;
    }
}

if (!function_exists('getStringBetween')) {
    /**
     * Returns string between two characters.
     *
     * @param $string
     * @param $start
     * @param $end
     *
     * @return string
     */
    function getStringBetween($string, $start, $end): string
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);

        if (!$ini) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }
}

if (!function_exists('checkUrlExists')) {
    /**
     * Returns if path exists in project.
     *
     * @param $path
     *
     * @return bool
     */
    function checkUrlExists($path): bool
    {
        $routes = Route::getRoutes();
        $request = Request::create($path);

        try {
            if ($routes->match($request)) {
                return true;
            }

            return false;
        } catch (NotFoundHttpException) {
            return false;
        }
    }
}

if (!function_exists('strReplaceLastOccurrence')) {
    /**
     * Replaces string of last occurrence.
     *
     * @param $search
     * @param $replace
     * @param $subject
     *
     * @return string
     */
    function strReplaceLastOccurrence($search, $replace, $subject): String
    {
        $pos = strrpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}

if (!function_exists('getTimestampFromSingleXml')) {
    /**
     * Returns timestamp from xml file or last updated at if xml not exists.
     *
     * @param string $publisherId
     * @param App\IATI\Models\Activity\Activity $activity
     *
     * @return string
     */
    function getTimestampFromSingleXml(string $publisherId, App\IATI\Models\Activity\Activity $activity): string
    {
        $xmlName = "$publisherId-$activity->id.xml";
        $xmlPath = "xml/activityXmlFiles/$xmlName";

        if (awsHasFile($xmlPath)) {
            $xmlString = awsGetFile($xmlPath);

            if ($xmlString) {
                $xmlContent = new SimpleXMLElement($xmlString);

                $lastUpdatedDatetime = (string) $xmlContent->xpath('//iati-activity/@last-updated-datetime')[0];

                if ($lastUpdatedDatetime) {
                    $carbonDate = Carbon::parse($lastUpdatedDatetime);

                    return $carbonDate->toIso8601String();
                }
            }
        }

        return $activity->updated_at->toIso8601String();
    }
}

if (!function_exists('getJsonFromSource')) {
    /**
     * Return codelist json.
     *
     * @param string $completePath
     *
     * @return string
     */
    function getJsonFromSource(string $completePath): string
    {
        if (env('APP_ENV') === 'local') {
            return file_get_contents(public_path($completePath));
        }

        $jsonString = Cache::get($completePath);

        if (!$jsonString) {
            $jsonString = awsGetFile($completePath);

            Cache::set($completePath, $jsonString);
        }

        return $jsonString;
    }
}

if (!function_exists('getDefaultValue')) {
    /**
     * Returns Default value.
     *
     * @param $defaultValueList
     * @param $selectDefaultValueKey
     *
     * @return string|null
     *
     * @throws JsonException
     */
    function getDefaultValue($defaultValueList, $selectDefaultValueKey, $location = []): ?string
    {
        $defaultValueKeys = [
            'language'  => 'default_language',
            'currency'  => 'default_currency',
            'default_aid_type'   => 'default_aid_type',
            'collaboration_type'    => 'default_collaboration_type',
            'default_flow_type'     => 'default_flow_type',
            'default_finance_type'  => 'default_finance_type',
            'default_tied_status'   => 'default_tied_status',
        ];

        if (isset($defaultValueKeys[$selectDefaultValueKey]) && isset($defaultValueList[$defaultValueKeys[$selectDefaultValueKey]])) {
            $explodedLocation = explode('/', $location);
            $type = $explodedLocation[0];
            $jsonFile = str_replace('.json', '', $explodedLocation[1]);
            $codeList = getCodeList($jsonFile, $type, filterDeprecated: true);
            $defaultValue = $defaultValueList[$defaultValueKeys[$selectDefaultValueKey]];

            return $codeList[$defaultValue] ?? null;
        }

        return null;
    }
}

if (!function_exists('getSettingDefaultLanguage')) {
    /**
     * Returns default language from settings.
     *
     * @return string|null
     * @throws JsonException
     *
     * @throws BindingResolutionException
     */
    function getSettingDefaultLanguage(): ?string
    {
        $settingService = app()->make(SettingService::class);
        $settingsDefaultValue = $settingService->getSetting()->default_values ?? [];

        return getDefaultValue($settingsDefaultValue, 'language', 'Activity/Language.json' ?? []);
    }
}

if (!function_exists('getTimestampFromOrganizationXml')) {
    /**
     * Returns timestamp from xml file or last updated at if xml not exists.
     *
     * @param string $publisherId
     *
     * @return string
     */
    function getTimestampFromOrganizationXml(string $publisherId, App\IATI\Models\Organization\Organization $organization): string
    {
        $xmlName = "$publisherId-organisation.xml";
        $xmlPath = "organizationXmlFiles/$xmlName";

        if (awsHasFile($xmlPath)) {
            $xmlString = awsGetFile($xmlPath);

            if ($xmlString) {
                $xmlContent = new SimpleXMLElement($xmlString);

                $lastUpdatedDatetime = (string) $xmlContent->xpath('//iati-organisation/@last-updated-datetime')[0];

                if ($lastUpdatedDatetime) {
                    $carbonDate = Carbon::parse($lastUpdatedDatetime);

                    return $carbonDate->toIso8601String();
                }
            }
        }

        return $organization->updated_at->toIso8601String();
    }
}

if (!function_exists('addAdditionalLabel')) {
    /**
     * @param string $parentElement
     * @param string $elementName
     *
     * @return string
     */
    function generateAddAdditionalLabel(string $parentElement, string $elementName): string
    {
        $elementsToShowNarrativeAsName = ['reporting_org', 'participating_org'];
        $elementName = str_replace('_', ' ', $elementName);

        if ($elementName === 'narrative' && in_array($parentElement, $elementsToShowNarrativeAsName)) {
            $elementName = 'name';
        }

        return trans('common/common.add_additional') . ' ' . $elementName;
    }
}

if (!function_exists('arrayToLowercase')) {
    function arrayToLowercase(array $array)
    {
        $lowercaseArray = [];

        foreach ($array as $key => $value) {
            $lowercaseKey = is_string($key) ? strtolower($key) : $key;

            if (is_array($value)) {
                $lowercaseValue = arrayToLowercase($value);
            } elseif (is_string($value)) {
                $lowercaseValue = strtolower($value);
            } else {
                $lowercaseValue = $value;
            }

            $lowercaseArray[$lowercaseKey] = $lowercaseValue;
        }

        return $lowercaseArray;
    }
}

if (!function_exists('flattenArrayWithKeys')) {
    function flattenArrayWithKeys($array, $prefix = ''): array
    {
        return Arr::dot($array);
    }
}

if (!function_exists('arrayOr')) {
    function arrayOr(array $array): bool
    {
        foreach ($array as $item) {
            if ($item) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('refreshActivityDeprecationStatusMap')) {
    function refreshActivityDeprecationStatusMap(array $activity): array
    {
        $deprecationMap = [];

        foreach ($activity as $attribute => $value) {
            $callable = match ($attribute) {
                'iati_identifier' => 'doesIdentifierHaveDeprecatedCode',
                'title' => 'doesTitleHaveDeprecatedCode',
                'default_field_values' => 'doesDefaultFieldValuesHaveDeprecatedCode',
                'description' => 'doesDescriptionHaveDeprecatedCode',
                'activity_date' => 'doesActivityDateHaveDeprecatedCode',
                'participating_org',
                'participating_organization' => 'doesParticipatingOrgHaveDeprecatedCode',
                'recipient_country' => 'doesRecipientCountryHaveDeprecatedCode',
                'recipient_region' => 'doesRecipientRegionHaveDeprecatedCode',
                'sector' => 'doesSectorHaveDeprecatedCode',
                'activity_scope' => 'doesActivityScopeHaveDeprecatedCode',
                'activity_status' => 'doesActivityStatusHaveDeprecatedCode',
                'budget' => 'doesBudgetHaveDeprecatedCode',
                'policy_marker' => 'doesPolicyMarkerHaveDeprecatedCode',
                'related_activity' => 'doesRelatedActivityHaveDeprecatedCode',
                'contact_info' => 'doesContactInfoHaveDeprecatedCode',
                'other_identifier' => 'doesOtherIdentifierHaveDeprecatedCode',
                'tag' => 'doesTagHaveDeprecatedCode',
                'collaboration_type' => 'doesCollaborationTypeHaveDeprecatedCode',
                'default_flow_type' => 'doesDefaultFlowTypeHaveDeprecatedCode',
                'default_finance_type' => 'doesDefaultFinanceTypeHaveDeprecatedCode',
                'default_tied_status' => 'doesDefaultTiedStatusHaveDeprecatedCode',
                'default_aid_type' => 'doesDefaultAidTypeHaveDeprecatedCode',
                'country_budget_item' => 'doesCountryBudgetItemHaveDeprecatedCode',
                'humanitarian_scope' => 'doesHumanitarianScopeHaveDeprecatedCode',
                'capital_spend' => 'doesCapitalSpendHaveDeprecatedCode',
                'condition' => 'doesConditionHaveDeprecatedCode',
                'legacy_data' => 'doesLegacyDataHaveDeprecatedCode',
                'document_link' => 'doesDocumentLinkHaveDeprecatedCode',
                'location' => 'doesLocationHaveDeprecatedCode',
                'planned_disbursement' => 'doesPlannedDisbursementHaveDeprecatedCode',
                default => 'default'
            };

            if (is_callable($callable)) {
                if (in_array($attribute, ['activity_scope', 'activity_status', 'default_flow_type', 'default_finance_type', 'default_tied_status'])) {
                    $element = ['code'=>$value];
                } else {
                    $element = $value;
                }

                $deprecationMap[$attribute] = call_user_func($callable, $element);
            }
        }

        return $deprecationMap;
    }
}

if (!function_exists('refreshTransactionDeprecationStatusMap')) {
    function refreshTransactionDeprecationStatusMap(array $transaction): array
    {
        return [
            'humanitarian'          => doesHumanitarianHaveDeprecatedCode($transaction['humanitarian'] ?? []),
            'transaction_type'      => doesTransactionTypeHaveDeprecatedCode($transaction['transaction_type'] ?? []),
            'description'           => doesDescriptionHaveDeprecatedCode($transaction['description'] ?? []),
            'provider_organization' => doesProviderOrganizationHaveDeprecatedCode($transaction['provider_organization'] ?? []),
            'receiver_organization' => doesReceiverOrganizationHaveDeprecatedCode($transaction['receiver_organization'] ?? []),
            'disbursement_channel'  => doesDisbursementChannelHaveDeprecatedCode($transaction['disbursement_channel'] ?? []),
            'sector'                => doesRecipientRegionHaveDeprecatedCode($transaction['sector'] ?? []),
            'recipient_country'     => doesRecipientCountryHaveDeprecatedCode($transaction['recipient_country'] ?? []),
            'recipient_region'      => doesRecipientRegionHaveDeprecatedCode($transaction['recipient_region'] ?? []),
            'flow_type'             => doesFlowTypeHaveDeprecatedCode($transaction['flow_type'] ?? []),
            'finance_type'          => doesFinanceTypeHaveDeprecatedCode($transaction['finance_type'] ?? []),
            'aid_type'              => doesAidTypeHaveDeprecatedCode($transaction['aid_type'] ?? []),
            'tied_status'           => doesTiedStatusHaveDeprecatedCode($transaction['tied_status'] ?? []),
            'value'                 => doesValueHaveDeprecatedCode($transaction['value'] ?? []),
        ];
    }
}

if (!function_exists('refreshResultDeprecationStatusMap')) {
    function refreshResultDeprecationStatusMap(array $results): array
    {
        $compareMap = [
            'type'       => onlyDeprecatedItemsFromCodeList('AppData/Data/Activity/TagVocabulary.json'),
            'format'     => onlyDeprecatedItemsFromCodeList('AppData/Data/Activity/TagVocabulary.json'),
            'code'       => onlyDeprecatedItemsFromCodeList('AppData/Data/Activity/DocumentCategory.json'),
            'language'   => onlyDeprecatedItemsFromCodeList('AppData/Data/Activity/Language.json'),
            'vocabulary' => onlyDeprecatedItemsFromCodeList('AppData/Data/Activity/ResultVocabulary.json'),
        ];

        return generateDeprecationMap($results, $compareMap);
    }
}

if (!function_exists('refreshIndicatorDeprecationStatusMap')) {
    function refreshIndicatorDeprecationStatusMap(array $indicators): array
    {
        return [
            'measure'       => doesMeasureHaveDeprecatedCode(['code' => Arr::get($indicators, 'measure', [])]),
            'title'         => doesTitleHaveDeprecatedCode(Arr::get($indicators, 'title', [])),
            'description'   => doesDescriptionHaveDeprecatedCode(Arr::get($indicators, 'description', [])),
            'document_link' => doesDocumentLinkHaveDeprecatedCode(Arr::get($indicators, 'document_link', [])),
            'reference'     => doesReferenceHaveDeprecatedCode(Arr::get($indicators, 'reference', [])),
            'baseline'      => doesBaselineHaveDeprecatedCode(Arr::get($indicators, 'baseline', [])), ];
    }
}

if (!function_exists('refreshPeriodDeprecationStatusMap')) {
    function refreshPeriodDeprecationStatusMap(array $periods): array
    {
        $compareMap = [
            'format'     => onlyDeprecatedItemsFromCodeList('AppData/Data/Activity/TagVocabulary.json'),
            'code'       => onlyDeprecatedItemsFromCodeList('AppData/Data/Activity/DocumentCategory.json'),
            'language'   => onlyDeprecatedItemsFromCodeList('AppData/Data/Activity/Language.json'),
        ];

        return generateDeprecationMap($periods, $compareMap);
    }
}

if (!function_exists('unsetDeprecatedFieldValues')) {
    /**
     * @param $activity
     *
     * @return array
     */
    function unsetDeprecatedFieldValues($activity): array
    {
        if (isset($activity['transaction'])) {
            $flattenedTransaction = flattenArrayWithKeys(
                ['transaction'=>refreshTransactionDeprecationStatusMap(Arr::only($activity, 'transaction', []))]
            );
        }

        if (isset($activity['transactions'])) {
            $flattenedTransactions = flattenArrayWithKeys(
                ['transactions'=>refreshTransactionDeprecationStatusMap(Arr::only($activity, 'transactions', []))]
            );
        }

        if (isset($activity['results'])) {
            $flattenResult = flattenArrayWithKeys(
                ['results'=>refreshResultDeprecationStatusMap(Arr::only($activity, 'results', []))]
            );
        }

        if (isset($activity['results'])) {
            $flattenResult = flattenArrayWithKeys(
                ['results'=>refreshResultDeprecationStatusMap(Arr::only($activity, 'results', []))]
            );
        }

        if (isset($activity['results'])) {
            $flattenResult = flattenArrayWithKeys(
                ['results'=>refreshResultDeprecationStatusMap(Arr::only($activity, 'results', []))]
            );
        }

        $flattenActivity = flattenArrayWithKeys(refreshActivityDeprecationStatusMap(Arr::except($activity, ['transaction', 'transactions', 'results'])));
        $flattened = array_merge($flattenActivity, $flattenedTransaction ?? [], $flattenedTransactions ?? [], $flattenResult ?? []);

        foreach ($flattened as $key => $value) {
            if ($value) {
                Arr::set($activity, $key, null);
            }
        }

        return $activity;
    }
}

if (!function_exists('changeEmptySpaceValueToNullValue')) {
    function changeEmptySpaceValueToNullValue(array $arr): array
    {
        foreach ($arr as &$value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === '') {
                $value = null;
            }
        }

        return $arr;
    }
}

if (!function_exists('convertDotKeysToNestedArray')) {
    function convertDotKeysToNestedArray(array $array): array
    {
        return Arr::undot($array);
    }
}

if (!function_exists('trimStringValueInArray')) {
    function trimStringValueInArray($array): array
    {
        return array_map(function ($item) {
            if (is_string($item)) {
                return trim($item);
            }

            return $item;
        }, $array);
    }
}

if (!function_exists('calculateStringSizeInMb')) {
    /**
     * @param string $stringVal
     *
     * @return float|int
     */
    function calculateStringSizeInMb(string $stringVal): float|int
    {
        return strlen($stringVal) / 1048576;
    }
}

function getOffset($childXmlLineDetails, $identifier, $order)
{
    if ($order === 0) {
        return 0;
    }

    $firstXmlOffsetInfo = Arr::first(array_filter($childXmlLineDetails, fn ($detail) => Arr::get($detail, 'order') === 0));

    $baseOffset = Arr::get($firstXmlOffsetInfo, 'offset');
    $currentXmlOffsetInfo = Arr::get($childXmlLineDetails, $identifier);

    return $currentXmlOffsetInfo['offset'] - $baseOffset;
}

if (!function_exists('mapErrorLinesToChildren')) {
    function mapErrorLinesToChildren($identifier, $childXmlLineDetails, $originalErrorLineNumbers, $arrayWithErrorLineNumbersInsideTextMessage): array
    {
        $childXmlLineDetail = $childXmlLineDetails[$identifier];
        $order = Arr::get($childXmlLineDetail, 'order');
        $offset = getOffset($childXmlLineDetails, $identifier, $order);

        $extraOffset = count(array_filter($childXmlLineDetails, fn ($detail) => Arr::get($detail, 'order') < $order));
        if ($order !== 0) {
            $arrayWithErrorLineNumbersInsideTextMessage = json_encode($arrayWithErrorLineNumbersInsideTextMessage);

            foreach ($originalErrorLineNumbers as $key => $lineNumber) {
                $newLineNumber = $lineNumber - $offset;
                $newLineNumber = str_contains($key, '.lineNumber') ? $newLineNumber + $extraOffset : $newLineNumber + 1;

                $arrayWithErrorLineNumbersInsideTextMessage = str_replace("At line: $lineNumber", "At line: $newLineNumber", $arrayWithErrorLineNumbersInsideTextMessage);
                $arrayWithErrorLineNumbersInsideTextMessage = str_replace("at line: $lineNumber", "at line: $newLineNumber", $arrayWithErrorLineNumbersInsideTextMessage);

                $originalErrorLineNumbers[$key] = $newLineNumber;
            }

            $arrayWithErrorLineNumbersInsideTextMessage = json_decode($arrayWithErrorLineNumbersInsideTextMessage, true);
        }

        return [$originalErrorLineNumbers, $arrayWithErrorLineNumbersInsideTextMessage];
    }
}

if (!function_exists('getItemsWhereKeyContains')) {
    function getItemsWhereKeyContains(array $array, string $searchString): array
    {
        return Arr::where($array, function ($value, $key) use ($searchString) {
            return str_contains($key, $searchString);
        });
    }
}

if (!function_exists('getLineNumbersOfEachActivity')) {
    function getLineNumbersOfEachActivity($xmlDoc, array $uniqueIdentifiers, array $individualActivityXmlLength): array
    {
        $sumOfPreviousNodes = 0;
        $result = [];
        $xpath = new DOMXPath($xmlDoc);
        $nodes = $xpath->query('//iati-activities/iati-activity/iati-identifier');

        foreach ($nodes as $index => $node) {
            $identifier = $node->textContent;

            if (in_array($identifier, $uniqueIdentifiers)) {
                if ($index == 0) {
                    $lineNumber = 0;
                    $sumOfPreviousNodes = $individualActivityXmlLength[$identifier] + 3; // this is correct
                } else {
                    $lineNumber = $sumOfPreviousNodes;
                    $sumOfPreviousNodes = $sumOfPreviousNodes + $individualActivityXmlLength[$identifier]; //55 -> 55+36
                }

                $result[$identifier] = [
                    'order' => $index,
                    'beginsOn' => $lineNumber,
                    'individualLength' => $individualActivityXmlLength[$identifier],
                ];
            }
        }

        return $result;
    }
}

if (!function_exists('filterErrorsByIdentifier')) {
    function filterErrorsByIdentifier(array $errors, string $identifier): array
    {
        return array_values(array_filter($errors, function ($error) use ($identifier) {
            return $error['identifier'] === $identifier;
        }));
    }
}

function getIndividualActivityXmlLength(string $fileContent): int
{
    return substr_count($fileContent, PHP_EOL) + 1 - 3;
}

function regroupResponseForAllActivity(array $response, array $uniqueIdentifiers, array $xmlLineNumberMap): array
{
    $groupedResponses = [];

    foreach ($uniqueIdentifiers as $identifier) {
        $clonedResponse = $response;
        $clonedResponse['errors'] = filterErrorsByIdentifier($response['errors'], $identifier);
        $clonedResponse['summary'] = ['critical' => 0, 'error' => 0, 'warning' => 0, 'advisory' => 0];

        foreach ($clonedResponse['errors'] as $error) {
            $severity = Arr::get($error, 'severity');
            $clonedResponse['summary'][$severity]++;
        }

        $clonedResponse = flattenArrayWithKeys($clonedResponse);
        $arrayOfErrorLineNumbersInValue = getItemsWhereKeyContains($clonedResponse, 'line');
        $arrayOfErrorLineNumbersInTextMessage = getItemsWhereKeyContains($clonedResponse, '.text');

        [$mappedLineNumbersInValue, $mappedLineNumbersInTextMessage] = mapErrorLinesToChildren($identifier, $xmlLineNumberMap, $arrayOfErrorLineNumbersInValue, $arrayOfErrorLineNumbersInTextMessage);

        foreach ($arrayOfErrorLineNumbersInValue as $key => $value) {
            $clonedResponse[$key] = $mappedLineNumbersInValue[$key];
        }

        foreach ($arrayOfErrorLineNumbersInTextMessage as $key => $value) {
            $clonedResponse[$key] = $mappedLineNumbersInTextMessage[$key];
        }

        $clonedResponse = convertDotKeysToNestedArray($clonedResponse);

        $groupedResponses[$identifier] = $clonedResponse;
    }

    return $groupedResponses;
}

function getTranslatedUntitled(): string
{
    return trans('common/common.untitled');
}

/**
 * Returns array of activity element names in snake case.
 * Making this instead of using $activity->getAttributes() method that laravel has because:
 * There were case where I was not getting attributes reliably.
 * Need this function when using ElementCompleteService::refreshElementStatus().
 *
 * @return string[]
 */
function getActivityAttributes(): array
{
    return  [
        'iati_identifier',
        'other_identifier',
        'title',
        'description',
        'activity_status',
        'activity_date',
        'contact_info',
        'activity_scope',
        'participating_org',
        'recipient_country',
        'recipient_region',
        'location',
        'sector',
        'country_budget_items',
        'humanitarian_scope',
        'policy_marker',
        'collaboration_type',
        'default_flow_type',
        'default_finance_type',
        'default_aid_type',
        'default_tied_status',
        'budget',
        'planned_disbursement',
        'capital_spend',
        'document_link',
        'related_activity',
        'legacy_data',
        'conditions',
        'tag',
        'reporting_org',
        'transactions',
        'result',
    ];
}
