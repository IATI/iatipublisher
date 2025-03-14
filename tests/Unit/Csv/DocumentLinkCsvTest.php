<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\DocumentLink;
use Illuminate\Support\Arr;

/**
 * Class DocumentLinkCsvTest.
 */
class DocumentLinkCsvTest extends CsvBaseTest
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
            $reportingOrg = new DocumentLink($row, $this->validation);
            $reportingOrg->validate()->withErrors();

            if (!empty($reportingOrg->errors()) || !empty($reportingOrg->criticals()) || !empty($reportingOrg->warnings())) {
                $errors[] = $reportingOrg->errors() + $reportingOrg->criticals() + $reportingOrg->warnings();
            }
        }

        return $errors;
    }

    /**
     * Valid Document Link Data.
     *
     * @return array
     */
    public function valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['document_link_url'] = ['https://www.google.com'];
        $data[0]['document_link_format'] = ['application/A2L'];
        $data[0]['document_link_title'] = ['title one'];
        $data[0]['document_link_description'] = ['title description'];
        $data[0]['document_link_category'] = ['A01'];
        $data[0]['document_link_language'] = ['aa'];
        $data[0]['document_date'] = ['01/12/2022'];

        $data[1]['document_link_url'] = ['https://www.google.com'];
        $data[1]['document_link_format'] = ['application/A2L'];
        $data[1]['document_link_title'] = ['title one'];
        $data[1]['document_link_description'] = ['title description'];
        $data[1]['document_link_category'] = ['A01'];
        $data[1]['document_link_language'] = ['aa'];
        $data[1]['document_date'] = ['01/12/2022'];

        return $data;
    }

    /**
     * Pass if all valid data.
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
     * Throw validation messages for all invalid data.
     *
     * @return void
     * @throws \JsonException
     * @test
     */
    public function throw_validation_if_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.document_link_format_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.url_valid'), $flattenErrors);
        $this->assertContains(trans('validation.this_must_be_a_valid_date'), $flattenErrors);
        $this->assertContains(trans('validation.date_must_be_after_1900'), $flattenErrors);
        $this->assertContains(trans('validation.document_link_category_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.language_is_invalid'), $flattenErrors);
    }

    /**
     * Invalid document link data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->valid_data();
        $data[0]['document_link_url'] = ['invalid url'];
        $data[0]['document_link_format'] = ['invalid format'];
        $data[0]['document_link_title'] = ['title one'];
        $data[0]['document_link_description'] = ['title description'];
        $data[0]['document_link_category'] = ['invalid category'];
        $data[0]['document_link_language'] = ['invalid language'];
        $data[0]['document_date'] = ['invalid date format', '01/12/1300']; // before 1900

        return $data;
    }
}
