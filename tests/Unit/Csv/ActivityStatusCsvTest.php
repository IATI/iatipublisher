<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\ActivityStatus;
use Illuminate\Support\Arr;

/**
 * Class ActivityStatusCsvTest.
 */
class ActivityStatusCsvTest extends CsvBaseTest
{
    /**
     * All data are invalid.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function check_throws_validation_if_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->activity_status_invalid_data();
        $errors = [];

        foreach ($rows as $row) {
            $activityStatus = new ActivityStatus($row, $this->validation);
            $activityStatus->validate()->withErrors();

            if (!empty($activityStatus->errors()) || !empty($activityStatus->criticals()) || !empty($activityStatus->warnings())) {
                $errors[] = $activityStatus->errors() + $activityStatus->criticals() + $activityStatus->warnings();
            }
        }

        $flattenErrors = Arr::flatten($errors);
        $this->assertContains(trans('validation.activity_status.size'), $flattenErrors);
        $this->assertContains(trans('validation.activity_status.in'), $flattenErrors);
    }

    /**
     * Activity status invalid data.
     *
     * @return array
     */
    public function activity_status_invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['activity_status'] = [1, 1];
        $data[1]['activity_status'] = [9];

        return $data;
    }

    /**
     * All data are valid.
     *
     * @return void
     * @throws \JsonException
     * @test
     */
    public function check_if_passes_when_valid_data(): void
    {
        $this->signIn();
        $rows = $this->activity_status_valid_data();
        $errors = [];

        foreach ($rows as $row) {
            $activityStatus = new ActivityStatus($row, $this->validation);
            $activityStatus->validate()->withErrors();

            if (!empty($activityStatus->errors()) || !empty($activityStatus->criticals()) || !empty($activityStatus->warnings())) {
                $errors[] = $activityStatus->errors() + $activityStatus->criticals() + $activityStatus->warnings();
            }
        }

        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Valid activity status data.
     *
     * @return array
     */
    public function activity_status_valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['activity_status'] = [1];
        $data[1]['activity_status'] = [5];

        return $data;
    }
}
