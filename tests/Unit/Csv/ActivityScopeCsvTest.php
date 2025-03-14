<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\ActivityScope;
use Illuminate\Support\Arr;

/**
 * Class ActivityScopeCsvTest.
 */
class ActivityScopeCsvTest extends CsvBaseTest
{
    /**
     * All Data invalid.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function check_throws_validation_when_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->invalid_activity_scope_data();
        $errors = [];

        foreach ($rows as $row) {
            $element = new ActivityScope($row, $this->validation);
            $element->validate()->withErrors();

            if (!empty($element->errors()) || !empty($element->criticals()) || !empty($element->warnings())) {
                $errors[] = $element->errors() + $element->criticals() + $element->warnings();
            }
        }

        $flattenErrors = Arr::flatten($errors);
        $this->assertContains(trans('validation.activity_scope.size'), $flattenErrors);
        $this->assertContains(trans('validation.activity_scope.in'), $flattenErrors);
    }

    /**
     * Invalid Scope Data.
     *
     * @return array
     */
    public function invalid_activity_scope_data(): array
    {
        $data = $this->completeData;
        $data[0]['activity_scope'] = [1, 2, 3];
        $data[1]['activity_scope'] = [10];

        return $data;
    }

    /**
     * All valid date.
     *
     * @return void
     * @throws \JsonException
     * @test
     */
    public function check_passes_if_valid_data(): void
    {
        $this->signIn();
        $rows = $this->valid_activity_scope_data();
        $errors = [];

        foreach ($rows as $row) {
            $element = new ActivityScope($row, $this->validation);
            $element->validate()->withErrors();

            if (!empty($element->errors()) || !empty($element->criticals()) || !empty($element->warnings())) {
                $errors[] = $element->errors() + $element->criticals() + $element->warnings();
            }
        }

        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Valid Scope Data.
     *
     * @return array
     */
    public function valid_activity_scope_data(): array
    {
        $data = $this->completeData;
        $data[0]['activity_scope'] = [1];
        $data[1]['activity_scope'] = [2];

        return $data;
    }
}
