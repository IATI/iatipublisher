<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\HumanitarianScope;
use Illuminate\Support\Arr;

/**
 * Class HumanitarianScopeCsvTest.
 */
class HumanitarianScopeCsvTest extends CsvBaseTest
{
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
            $reportingOrg = new HumanitarianScope($row, $this->validation);
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
        $data[0]['humanitarian_scope_type'] = ['1'];
        $data[0]['humanitarian_scope_vocabulary'] = ['1-2'];
        $data[0]['humanitarian_scope_vocabulary_uri'] = [];
        $data[0]['humanitarian_scope_code'] = ['code1'];
        $data[0]['humanitarian_scope_narrative'] = ['narr one'];

        $data[1]['humanitarian_scope_type'] = ['2'];
        $data[1]['humanitarian_scope_vocabulary'] = ['99'];
        $data[1]['humanitarian_scope_vocabulary_uri'] = ['https://www.google.com'];
        $data[1]['humanitarian_scope_code'] = ['code2'];
        $data[1]['humanitarian_scope_narrative'] = ['narrative two'];

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
     * Throw validation message for all invalid data.
     *
     * @throws \JsonException
     * @test
     */
    public function throw_all_possible_validation_for_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.vocabulary_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.url_valid'), $flattenErrors);
    }

    /**
     * Invalid humanitarian scope data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['humanitarian_scope_type'] = ['1aaaa', '2'];
        $data[0]['humanitarian_scope_vocabulary'] = ['1-2dd', '99'];
        $data[0]['humanitarian_scope_vocabulary_uri'] = ['', 'invalid uri'];
        $data[0]['humanitarian_scope_code'] = ['code1', '1234'];
        $data[0]['humanitarian_scope_narrative'] = ['narr one', 'narr two'];

        $data[1]['humanitarian_scope_type'] = ['2'];
        $data[1]['humanitarian_scope_vocabulary'] = ['99'];
        $data[1]['humanitarian_scope_vocabulary_uri'] = ['https://www.google.com'];
        $data[1]['humanitarian_scope_code'] = ['code2'];
        $data[1]['humanitarian_scope_narrative'] = ['narrative two'];

        return $data;
    }
}
