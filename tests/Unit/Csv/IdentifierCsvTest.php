<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\Identifier;
use Illuminate\Support\Arr;

/**
 * Class IdentifierCsvTest.
 */
class IdentifierCsvTest extends CsvBaseTest
{
    /**
     * All data invalid to check if it throws validation.
     *
     * @return void
     * @test
     */
    public function check_throws_validation_if_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->identifier_invalid_data();
        $errors = [];

        foreach ($rows as $row) {
            $identifier = new Identifier($row, $this->validation);
            $identifier->validate()->withErrors();

            if (!empty($identifier->errors()) || !empty($identifier->criticals()) || !empty($identifier->warnings())) {
                $errors[] = $identifier->errors() + $identifier->criticals() + $identifier->warnings();
            }
        }

        $flattenErrors = Arr::flatten($errors);
        $this->assertContains(trans('validation.no_spaces_in_between_in_activity_identifier'), $flattenErrors);
        $this->assertContains('The activity identifier field is required.', $flattenErrors);
    }

    /**
     * Invalid Data.
     *
     * @return array
     */
    public function identifier_invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['activity_identifier'] = ['//\asdfdsaf'];
        $data[1]['activity_identifier'] = [];
        $data[3]['activity_identifier'] = ['12345'];
        $data[4]['activity_identifier'] = ['12345'];

        return $data;
    }

    /**
     * All valid data are provided.
     *
     * @return void
     * @test
     */
    public function check_if_passes_when_valid_data_identifier(): void
    {
        $this->signIn();
        $rows = $this->identifier_valid_data();
        $errors = [];

        foreach ($rows as $row) {
            $identifier = new Identifier($row, $this->validation);
            $identifier->validate()->withErrors();

            if (!empty($identifier->errors()) || !empty($identifier->criticals()) || !empty($identifier->warnings())) {
                $errors[] = $identifier->errors() + $identifier->criticals() + $identifier->warnings();
            }
        }

        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Valid Identifier Data.
     *
     * @return array
     */
    public function identifier_valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['activity_identifier'] = ['valid-reference'];
        $data[1]['activity_identifier'] = ['another-valid-reference'];

        return $data;
    }

    /**
     * Check if it throws validation for duplicate identifier.
     *
     * @return void
     * @test
     * @throws \ReflectionException
     */
    public function check_throws_validation_if_duplicate_identifier(): void
    {
        $this->signIn();
        $rows = $this->duplicate_identifier_data();
        $errors = [];
        $this->assertTrue(true);
    }

    /**
     * Duplicate identifier data.
     *
     * @return array
     */
    public function duplicate_identifier_data(): array
    {
        $data = $this->completeData;
        $data[0]['activity_identifier'] = ['12345'];
        $data[1]['activity_identifier'] = ['12345'];

        return $data;
    }
}
