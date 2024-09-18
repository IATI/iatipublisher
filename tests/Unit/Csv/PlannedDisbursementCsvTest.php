<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\PlannedDisbursement;
use Illuminate\Support\Arr;

/**
 * Class PlannedDisbursementCsvTest.
 */
class PlannedDisbursementCsvTest extends CsvBaseTest
{
    /**
     * Collect validation error messages.
     *
     * @param $rows
     * @return array
     * @throws \JsonException
     */
    public function getErrors($rows): array
    {
        $errors = [];

        foreach ($rows as $row) {
            $reportingOrg = new PlannedDisbursement($row, $this->validation);
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
        $data[0]['planned_disbursement_type'] = ['1'];
        $data[0]['planned_disbursement_period_start'] = ['03/03/2023'];
        $data[0]['planned_disbursement_period_end'] = ['10/03/2023'];
        $data[0]['planned_disbursement_value'] = ['100'];
        $data[0]['planned_disbursement_value_currency'] = ['AED'];
        $data[0]['planned_disbursement_value_date'] = ['04/03/2023'];
        $data[0]['planned_disbursement_provider_org_reference'] = ['ref one'];
        $data[0]['planned_disbursement_provider_org_activity_id'] = ['activity id'];
        $data[0]['planned_disbursement_provider_org_type'] = ['10'];
        $data[0]['planned_disbursement_provider_org_narrative'] = ['narrative one'];
        $data[0]['planned_disbursement_receiver_org_reference'] = ['ref one'];
        $data[0]['planned_disbursement_receiver_org_activity_id'] = ['activity id'];
        $data[0]['planned_disbursement_receiver_org_type'] = ['10'];
        $data[0]['planned_disbursement_receiver_org_narrative'] = ['narrative one'];

        $data[1]['planned_disbursement_type'] = [];
        $data[1]['planned_disbursement_period_start'] = [];
        $data[1]['planned_disbursement_period_end'] = [];
        $data[1]['planned_disbursement_value'] = [];
        $data[1]['planned_disbursement_value_currency'] = [];
        $data[1]['planned_disbursement_value_date'] = [];
        $data[1]['planned_disbursement_provider_org_reference'] = [];
        $data[1]['planned_disbursement_provider_org_activity_id'] = [];
        $data[1]['planned_disbursement_provider_org_type'] = [];
        $data[1]['planned_disbursement_provider_org_narrative'] = [];
        $data[1]['planned_disbursement_receiver_org_reference'] = [];
        $data[1]['planned_disbursement_receiver_org_activity_id'] = [];
        $data[1]['planned_disbursement_receiver_org_type'] = [];
        $data[1]['planned_disbursement_receiver_org_narrative'] = [];

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
     * Invalid data.
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->valid_data();
        $data[0]['planned_disbursement_type'] = ['invalid type'];
        $data[0]['planned_disbursement_period_start'] = ['invalid date'];
        $data[0]['planned_disbursement_period_end'] = ['invalid date'];
        $data[0]['planned_disbursement_value'] = ['invalid amount'];
        $data[0]['planned_disbursement_value_currency'] = ['invalid currency'];
        $data[0]['planned_disbursement_value_date'] = ['invalid date'];
        $data[0]['planned_disbursement_provider_org_reference'] = ['invalid reference'];
        $data[0]['planned_disbursement_provider_org_activity_id'] = ['invalid activity id'];
        $data[0]['planned_disbursement_provider_org_type'] = ['invalid type'];
        $data[0]['planned_disbursement_provider_org_narrative'] = ['invalid narrative'];
        $data[0]['planned_disbursement_receiver_org_reference'] = ['invalid reference'];
        $data[0]['planned_disbursement_receiver_org_activity_id'] = ['invalid activity id'];
        $data[0]['planned_disbursement_receiver_org_type'] = ['invalid type'];
        $data[0]['planned_disbursement_receiver_org_narrative'] = ['invalid narrative'];

        $data[1]['planned_disbursement_type'] = ['1'];
        $data[1]['planned_disbursement_period_start'] = ['01/01/2020'];
        $data[1]['planned_disbursement_period_end'] = ['01/05/2020'];
        $data[1]['planned_disbursement_value'] = ['-10000'];
        $data[1]['planned_disbursement_value_currency'] = ['NPR'];
        $data[1]['planned_disbursement_value_date'] = ['02/06/2020'];
        $data[1]['planned_disbursement_provider_org_reference'] = ['/\/&?'];
        $data[1]['planned_disbursement_provider_org_activity_id'] = [];
        $data[1]['planned_disbursement_provider_org_type'] = [];
        $data[1]['planned_disbursement_provider_org_narrative'] = [];
        $data[1]['planned_disbursement_receiver_org_reference'] = ['/\/&?'];
        $data[1]['planned_disbursement_receiver_org_activity_id'] = [];
        $data[1]['planned_disbursement_receiver_org_type'] = [];
        $data[1]['planned_disbursement_receiver_org_narrative'] = [];

        $data[2]['planned_disbursement_period_start'] = ['01/03/2020'];
        $data[2]['planned_disbursement_period_end'] = ['01/02/2020'];

        return $data;
    }

    /**
     * Throw validation for all invalid data.
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

        $this->assertContains(trans('validation.date_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_planned_disbursement.date.period_start_end'), $flattenErrors);
        $this->assertContains(trans('validation.date_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.period_end_after'), $flattenErrors);
        $this->assertContains(trans('validation.activity_planned_disbursement.date.period_start_end'), $flattenErrors);
        $this->assertContains(trans('validation.amount_number'), $flattenErrors);
        $this->assertContains(trans('validation.amount_negative'), $flattenErrors);
        $this->assertContains(trans('validation.invalid_currency'), $flattenErrors);
        $this->assertContains(trans('validation.date_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.organisation_type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.reference_should_not_contain_symbol'), $flattenErrors);
        $this->assertContains(trans('validation.organisation_type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.reference_should_not_contain_symbol'), $flattenErrors);
    }
}
