<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\Sector;
use Illuminate\Support\Arr;

/**
 * Class SectorCsvTest.
 */
class SectorCsvTest extends CsvBaseTest
{
    /**
     * All Valid data for sector.
     *
     * @return array
     */
    public function get_valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['sector_vocabulary'] = ['1', '2', '3', '7', '8', '99', '98'];
        $data[0]['sector_vocabulary_uri'] = ['', '', '', '', '', 'https://www.google.com', 'https://www.google.com'];
        $data[0]['sector_code'] = ['11110', '111', '12345', '1', '1.1', '12345', '12345'];
        $data[0]['sector_percentage'] = ['100', '100', '100', '100', '100', '100'];
        $data[0]['sector_narrative'] = ['narrative one', 'narrative two', 'narrative three', 'narrative four', 'narrative five', 'narrative six', 'narrative seven'];

        $data[1]['sector_vocabulary'] = ['1', '2', '3', '7', '8', '99', '98'];
        $data[1]['sector_vocabulary_uri'] = ['', '', '', '', '', 'https://www.google.com', 'https://www.google.com'];
        $data[1]['sector_code'] = ['11110', '111', '12345', '1', '1.1', '12345', '12345'];
        $data[1]['sector_percentage'] = ['100', '100', '100', '100', '100', '100'];
        $data[1]['sector_narrative'] = ['narrative one', 'narrative two', 'narrative three', 'narrative four', 'narrative five', 'narrative six', 'narrative Seven'];

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
            $element = new Sector($row, $this->validation);
            $element->validate()->withErrors();

            if (!empty($element->errors()) || !empty($element->criticals()) || !empty($element->warnings())) {
                $errors[] = $element->errors() + $element->criticals() + $element->warnings();
            }
        }

        return $errors;
    }

    /**
     * All valid data passed.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function pass_if_valid_data(): void
    {
        $this->signIn();
        $rows = $this->get_valid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * multiple same vocabulary with empty percentage data.
     *
     * @return array
     */
    public function vocabulary_same_empty_percentage_data(): array
    {
        $data = $this->completeData;
        $data[0]['sector_vocabulary'] = ['1', '1', '1'];
        $data[0]['sector_vocabulary_uri'] = ['', '', ''];
        $data[0]['sector_code'] = ['11110', '11110', '11110'];
        $data[0]['sector_percentage'] = ['0.0', '0.0', '0.0'];
        $data[0]['sector_narrative'] = ['narrative one', 'narrative two', 'narrative three'];

        $data[1]['sector_vocabulary'] = ['1', '2', '3', '7', '8', '99', '98'];
        $data[1]['sector_vocabulary_uri'] = ['', '', '', '', '', 'https://www.google.com', 'https://www.google.com'];
        $data[1]['sector_code'] = ['11110', '111', '12345', '1', '1.1', '12345', '12345'];
        $data[1]['sector_percentage'] = ['100', '100', '100', '100', '100', '100', '100'];
        $data[1]['sector_narrative'] = ['narrative one', 'narrative two', 'narrative three', 'narrative four', 'narrative five', 'narrative six', 'narrative Seven'];

        return $data;
    }

    /**
     * Same 3 vocab but empty percentage.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function throw_validation_if_multiple_sector_same_vocabulary_empty_percentage(): void
    {
        $this->signIn();
        $rows = $this->vocabulary_same_empty_percentage_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.sum'), $flattenErrors);
    }

    /**
     * Test to check if single sector empty percentage passes.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function pass_if_single_sector_empty_percentage(): void
    {
        $this->signIn();
        $rows = $this->single_sector_empty_percentage_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Percentage empty in single sector data.
     *
     * @return array
     */
    public function single_sector_empty_percentage_data(): array
    {
        $data = $this->completeData;
        $data[0]['sector_vocabulary'] = ['1'];
        $data[0]['sector_vocabulary_uri'] = [''];
        $data[0]['sector_code'] = ['11110'];
        $data[0]['sector_percentage'] = [];
        $data[0]['sector_narrative'] = ['narrative one'];

        $data[1]['sector_vocabulary'] = ['1'];
        $data[1]['sector_vocabulary_uri'] = [''];
        $data[1]['sector_code'] = ['11110'];
        $data[1]['sector_percentage'] = ['100'];
        $data[1]['sector_narrative'] = ['narrative one'];

        return $data;
    }

    /**
     * When vocabulary 98 or 99 then narrative is required.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function check_if_narrative_required_when_vocabulary_98_or_99(): void
    {
        $this->signIn();
        $rows = $this->narrative_empty_vocabulary_98_or_99();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.narrative_is_required'), $flattenErrors);
    }

    /**
     * if vocab 98 or 99 then narrative empty.
     *
     * @return array
     */
    public function narrative_empty_vocabulary_98_or_99(): array
    {
        $data = $this->completeData;
        $data[0]['sector_vocabulary'] = ['1', '98', '99']; // narrative missing for vocab 98 and 99
        $data[0]['sector_vocabulary_uri'] = ['', 'https://www.google.com', 'https://www.google.com'];
        $data[0]['sector_code'] = ['11110', '12345', '12345'];
        $data[0]['sector_percentage'] = ['100', '100', '100'];
        $data[0]['sector_narrative'] = ['narrative first'];

        $data[1]['sector_vocabulary'] = ['1', '98', '99'];
        $data[1]['sector_vocabulary_uri'] = ['', 'https://www.google.com', 'https://www.google.com'];
        $data[1]['sector_code'] = ['11110', '12345', '12345'];
        $data[1]['sector_percentage'] = ['100', '100', '100'];
        $data[1]['sector_narrative'] = ['narrative first']; // narrative missing for vocab 98 and 99

        return $data;
    }

    /**
     * All invalid data.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function throws_validation_if_all_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->get_invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.sector_code_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.url_valid'), $flattenErrors);
        $this->assertContains(trans('validation.vocabulary_is_invalid'), $flattenErrors);
    }

    /**
     * Invalid sector data.
     *
     * @return array
     *
     * @throws \JsonException
     */
    public function get_invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['sector_vocabulary'] = ['1', '98', '91231239'];
        $data[0]['sector_vocabulary_uri'] = ['', 'invalid url', 'https://www.google.com'];
        $data[0]['sector_code'] = ['111ee10', '12345', '12345'];
        $data[0]['sector_percentage'] = ['-100', '1sdfasdf00', '100'];
        $data[0]['sector_narrative'] = ['narrative first'];

        $data[1]['sector_vocabulary'] = ['1', '98', '91231239'];
        $data[1]['sector_vocabulary_uri'] = ['', 'invalid url', 'https://www.google.com'];
        $data[1]['sector_code'] = ['111ee10', '12345', '12345'];
        $data[1]['sector_percentage'] = ['-100', '1sdfasdf00', '100'];
        $data[1]['sector_narrative'] = ['narrative first'];

        return $data;
    }
}
