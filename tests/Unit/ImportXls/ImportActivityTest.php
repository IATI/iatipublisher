<?php

namespace Tests\Unit\ImportXls;

use App\XlsImporter\Foundation\Mapper\Activity;
use App\XlsImporter\Foundation\XlsProcessor\XlsToArray;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Class ImportActivityTest.
 */
class ImportActivityTest extends TestCase
{
    /**
     * Throw validation for all invalid data.
     *
     * @return void
     */
    public function test_processed_data_against_actual_data(): void
    {
        $systemDataFilePath = 'tests/Unit/TestFiles/Xls/SystemData/activity.json';
        $xlsfilePath = 'tests/Unit/TestFiles/Xls/XlsData/Activity.xlsx';
        $actualData = json_decode(file_get_contents($systemDataFilePath), true, 512, 0);
        $xlsToArray = new XlsToArray();
        Excel::import($xlsToArray, $xlsfilePath);
        $xlsData = $xlsToArray->sheetData;

        $xlsMapper = new Activity();

        $xlsMapper->fillOrganizationReportingOrg(null)->setOrgId(1);

        $xlsMapper->map($xlsData);
        $processedData = $xlsMapper->getActivityData();

        foreach ($actualData as $key => $value) {
            $elementData = Arr::get($processedData, $key, []);

            if (is_array($value) && is_array($elementData)) {
                $expected = Arr::dot($value);
                $actual = Arr::dot($elementData);

                $normalize = fn ($v) => in_array($v, [false, 0, '', null], true) ? '' : $v;

                $normalizedExpected = array_map($normalize, $expected);
                $normalizedActual = array_map($normalize, $actual);

                $difference1 = array_diff_assoc($normalizedExpected, $normalizedActual);
                $difference2 = array_diff_assoc($normalizedActual, $normalizedExpected);

                $this->assertTrue(empty($difference1));
                $this->assertTrue(empty($difference2));
            } elseif ($elementData !== $value && !(empty($value) && empty($elementData))) {
                $this->assertTrue(false);
            }
        }
    }
}
