<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\ParticipatingOrganization;
use Illuminate\Support\Arr;

/**
 * Class ParticipatingOrgCsvTest.
 */
class ParticipatingOrgCsvTest extends CsvBaseTest
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
            $reportingOrg = new ParticipatingOrganization($row, $this->validation);
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
        $data[0]['participating_organisation_role'] = ['1'];
        $data[0]['participating_organisation_reference'] = ['ref 1'];
        $data[0]['participating_organisation_type'] = ['10'];
        $data[0]['participating_organisation_name'] = ['org name'];
        $data[0]['participating_organisation_identifier'] = ['identifier 1'];
        $data[0]['participating_organisation_crs_channel_code'] = ['10000'];

        $data[1]['participating_organisation_role'] = [];
        $data[1]['participating_organisation_reference'] = [];
        $data[1]['participating_organisation_type'] = [];
        $data[1]['participating_organisation_name'] = [];
        $data[1]['participating_organisation_identifier'] = [];
        $data[1]['participating_organisation_crs_channel_code'] = [];

        return $data;
    }

    /**
     * Pass if all valid data.
     *
     * @return void
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
     * Throw validation for all invalid data.
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

        $this->assertContains(trans('validation.activity_participating_org.invalid_identifier'), $flattenErrors);
        $this->assertContains(trans('validation.activity_participating_org.invalid_role'), $flattenErrors);
        $this->assertContains(trans('validation.organisation_type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_participating_org.invalid_crs_channel_code'), $flattenErrors);
    }

    /**
     * Invalid participating org data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->valid_data();
        $data[1]['participating_organisation_role'] = ['invalid role'];
        $data[1]['participating_organisation_reference'] = [];
        $data[1]['participating_organisation_type'] = ['invalid type'];
        $data[1]['participating_organisation_name'] = [];
        $data[1]['participating_organisation_identifier'] = ['*&^%$'];
        $data[1]['participating_organisation_crs_channel_code'] = ['invalid code'];

        return $data;
    }
}
