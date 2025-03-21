<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\Location;
use Illuminate\Support\Arr;

/**
 * Class LocationCsvTest.
 */
class LocationCsvTest extends CsvBaseTest
{
    /**
     * Collect validation message.
     *
     * @param $rows
     * @return array
     * @throws \JsonException
     */
    public function getErrors($rows): array
    {
        $errors = [];

        foreach ($rows as $row) {
            $reportingOrg = new Location($row, $this->validation);
            $reportingOrg->validate()->withErrors();

            if (!empty($reportingOrg->errors()) || !empty($reportingOrg->criticals()) || !empty($reportingOrg->warnings())) {
                $errors[] = $reportingOrg->errors() + $reportingOrg->criticals() + $reportingOrg->warnings();
            }
        }

        return $errors;
    }

    /**
     * All Valid data.
     *
     * @return array
     */
    public function valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['location_reference'] = ['ref 1'];
        $data[0]['location_reach_code'] = ['1'];
        $data[0]['location_id_vocabulary'] = ['A1'];
        $data[0]['location_id_code'] = ['code 1'];
        $data[0]['location_name'] = ['location name'];
        $data[0]['location_description'] = ['location description'];
        $data[0]['location_activity_description'] = ['activity description'];
        $data[0]['location_administrative_vocabulary'] = ['A1'];
        $data[0]['location_administrative_code'] = ['AF'];
        $data[0]['location_administrative_level'] = ['1'];
        $data[0]['location_point_srsname'] = ['srs name'];
        $data[0]['pos_latitude'] = ['1234.332'];
        $data[0]['pos_longitude'] = ['123.3333'];
        $data[0]['location_exactness'] = ['1'];
        $data[0]['location_class'] = ['1'];
        $data[0]['feature_designation'] = ['AIRQ'];

        $data[1]['location_reference'] = [];
        $data[1]['location_reach_code'] = [];
        $data[1]['location_id_vocabulary'] = [];
        $data[1]['location_id_code'] = [];
        $data[1]['location_name'] = [];
        $data[1]['location_description'] = [];
        $data[1]['location_activity_description'] = [];
        $data[1]['location_administrative_vocabulary'] = [];
        $data[1]['location_administrative_code'] = [];
        $data[1]['location_administrative_level'] = [];
        $data[1]['location_point_srsname'] = [];
        $data[1]['pos_latitude'] = [];
        $data[1]['pos_longitude'] = [];
        $data[1]['location_exactness'] = [];
        $data[1]['location_class'] = [];
        $data[1]['feature_designation'] = [];

        return $data;
    }

    /**
     * Pass if all valid data.
     *
     * @return void
     * @test
     * @throws \JsonException
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
     * Throw all validation messages for invalid data.
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

        $this->assertContains(trans('validation.reference_should_not_contain_symbol'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.vocabulary_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_location.administrative.level_min'), $flattenErrors);
        $this->assertContains(trans('validation.activity_location.administrative.level_int'), $flattenErrors);
        $this->assertContains(trans('validation.activity_location.point.latitude_numeric'), $flattenErrors);
        $this->assertContains(trans('validation.activity_location.point.longitude_numeric'), $flattenErrors);
    }

    /**
     * Invalid data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->valid_data();
        $data[1]['location_reference'] = ['//'];
        $data[1]['location_reach_code'] = ['invalid reach code'];
        $data[1]['location_id_vocabulary'] = ['invalid vocabulary'];
        $data[1]['location_id_code'] = [];
        $data[1]['location_name'] = [];
        $data[1]['location_description'] = [];
        $data[1]['location_activity_description'] = [];
        $data[1]['location_administrative_vocabulary'] = ['invalid vocabulary'];
        $data[1]['location_administrative_code'] = ['invalid code'];
        $data[1]['location_administrative_level'] = ['invalid ldevel', '-123'];
        $data[1]['location_point_srsname'] = [];
        $data[1]['pos_latitude'] = ['not numeric'];
        $data[1]['pos_longitude'] = ['not numeric'];
        $data[1]['location_exactness'] = ['invalid exactness'];
        $data[1]['location_class'] = ['invalid class'];
        $data[1]['feature_designation'] = ['invalid desgnation'];

        return $data;
    }
}
