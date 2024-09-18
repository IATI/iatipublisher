<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\CapitalSpend;
use Illuminate\Support\Arr;

/**
 * Class CapitalSpendCsvTest.
 */
class CapitalSpendCsvTest extends CsvBaseTest
{
    /**
     * Collects validation messages.
     *
     * @throws \JsonException
     */
    public function getErrors($rows): array
    {
        $errors = [];

        foreach ($rows as $row) {
            $reportingOrg = new CapitalSpend($row, $this->validation);
            $reportingOrg->validate()->withErrors();

            if (!empty($reportingOrg->errors()) || !empty($reportingOrg->criticals()) || !empty($reportingOrg->warnings())) {
                $errors[] = $reportingOrg->errors() + $reportingOrg->criticals() + $reportingOrg->warnings();
            }
        }

        return $errors;
    }

    /**
     * Valid Capital Spend.
     *
     * @return array
     */
    public function valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['capital_spend'] = ['100'];

        return $data;
    }

    /**
     * All valid data.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function pass_if_valid_data(): void
    {
        $this->signIn();
        $rows = $this->valid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Invalid Capital Spend data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['capital_spend'] = ['1000'];
        $data[1]['capital_spend'] = ['-100', '1000'];
        $data[2]['capital_spend'] = ['asfadf'];

        return $data;
    }

    /**
     * Throws all validation messages for all invalid data.
     *
     * @return void
     * @throws \JsonException
     * @test
     */
    public function throw_all_validation_if_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertContains('The capital spend must be a number between 0 and 100', $flattenErrors);
        $this->assertContains('The capital spend cannot have more than one value.', $flattenErrors);
    }
}
