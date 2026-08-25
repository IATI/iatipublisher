<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\ReportingOrganization;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;

/**
 * Class ReportingOrgCsvTest.
 */
class ReportingOrgCsvTest extends CsvBaseTest
{
    /**
     * All value invalid for reporting org and its related data
     * and test if related validation exists.
     *
     * @return void
     * @throws \JsonException
     */
    #[Test]
    public function check_throws_validation_if_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->reporting_org_invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains('The selected reporting org.0.secondary reporter is invalid.', $flattenErrors);
    }

    /**
     * Invalid reporting org data.
     *
     * @return array
     */
    public function reporting_org_invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['reporting_org_secondary_reporter'] = [1, 1];
        $data[1]['reporting_org_secondary_reporter'] = [11];
        $data[2]['reporting_org_secondary_reporter'] = ['asdf'];

        return $data;
    }

    /**
     * All the reporting org data are valid
     * Should not contain any errors i.e. $errors array should be empty.
     *
     * @return void
     * @throws \JsonException
     */
    #[Test]
    public function check_if_passes_when_valid_data_reporting_org(): void
    {
        $this->signIn();
        $rows = $this->reporting_org_valid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertEmpty($flattenErrors);
    }

    /**
     * Valid reporting org data.
     *
     * @return array
     */
    public function reporting_org_valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['reporting_org_secondary_reporter'] = [1];
        $data[1]['reporting_org_secondary_reporter'] = [0];

        return $data;
    }

    /**
     * Collects validation messages.
     *
     * @param $rows
     * @return array
     * @throws \JsonException
     */
    public function getErrors($rows): array
    {
        $errors = [];

        foreach ($rows as $row) {
            $reportingOrg = new ReportingOrganization($row, $this->organization->reporting_org, $this->validation);
            $reportingOrg->validate()->withErrors();

            if (!empty($reportingOrg->errors()) || !empty($reportingOrg->criticals()) || !empty($reportingOrg->warnings())) {
                $errors[] = $reportingOrg->errors() + $reportingOrg->criticals() + $reportingOrg->warnings();
            }
        }

        return $errors;
    }
}
