<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\RelatedActivity;
use Illuminate\Support\Arr;

/**
 * Class RelatedActivityCsvTest.
 */
class RelatedActivityCsvTest extends CsvBaseTest
{
    /**
     * Collects validation error messages.
     *
     * @param $rows
     * @return array
     * @throws \JsonException
     */
    public function getErrors($rows): array
    {
        $errors = [];

        foreach ($rows as $row) {
            $reportingOrg = new RelatedActivity($row, $this->validation);
            $reportingOrg->validate()->withErrors();

            if (!empty($reportingOrg->errors()) || !empty($reportingOrg->criticals()) || !empty($reportingOrg->warnings())) {
                $errors[] = $reportingOrg->errors() + $reportingOrg->criticals() + $reportingOrg->warnings();
            }
        }

        return $errors;
    }

    /**
     * All valid data.
     *
     * @return array
     */
    public function valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['related_activity_identifier'] = ['id-1', 'id-3'];
        $data[0]['related_activity_type'] = ['1', '5'];
        $data[1]['related_activity_identifier'] = ['id-2'];
        $data[1]['related_activity_type'] = ['2'];

        return $data;
    }

    /**
     * pass if all valid data.
     *
     * @throws \JsonException
     * @test
     */
    public function pass_if_all_valid_data(): void
    {
        $this->signIn();
        $rows = $this->valid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * All Invalid data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['related_activity_identifier'] = ['id-1'];
        $data[0]['related_activity_type'] = ['invalid', 'invalid'];
        $data[1]['related_activity_identifier'] = ['id-2'];
        $data[1]['related_activity_type'] = ['invalid'];

        return $data;
    }

    /**
     * Throws validation for all invalid data.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function throw_validation_if_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
    }
}
