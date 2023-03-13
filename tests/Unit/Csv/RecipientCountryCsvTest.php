<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\RecipientCountry;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;

class RecipientCountryCsvTest extends CsvBaseTest
{
    /**
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['recipient_country_code'] = ['AF', 'AF'];
        $data[0]['recipient_country_percentage'] = ['-50'];
        $data[0]['recipient_country_narrative'] = ['narrative one'];
        $data[0]['recipient_region_percentage'] = ['20'];
        $data[0]['recipient_region_code'] = ['289'];
        $data[0]['recipient_region_narrative'] = ['region narrative one'];

        $data[1]['recipient_country_code'] = ['HHHHH'];
        $data[1]['recipient_country_percentage'] = ['150'];
        $data[1]['recipient_country_narrative'] = ['two narrative one'];
        $data[1]['recipient_region_percentage'] = ['20'];
        $data[1]['recipient_region_code'] = ['289'];
        $data[1]['recipient_region_narrative'] = ['two narrative two'];

        $data[2]['recipient_country_code'] = ['AF', 'AX'];
        $data[2]['recipient_country_percentage'] = ['90', '90'];
        $data[2]['recipient_country_narrative'] = ['two narrative one'];
        $data[2]['recipient_region_percentage'] = ['20'];
        $data[2]['recipient_region_code'] = ['289'];
        $data[2]['recipient_region_narrative'] = ['two narrative two'];

        return $data;
    }

    /**
     * @throws BindingResolutionException
     * @throws \JsonException
     * @test
     */
    public function check_throws_validation_if_region_country_percentage_sum_not_100(): void
    {
        $this->signIn();
        $rows = $this->invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertContains('The recipient country percentage must be at least 0.', $flattenErrors);
        $this->assertContains('The Country Code cannot be redundant.', $flattenErrors);
        $this->assertContains('The recipient country code is invalid.', $flattenErrors);
        $this->assertContains('The sum of recipient country percentage cannot be greater than 100', $flattenErrors);
    }

    /**
     * All data valid.
     * @return void
     * @test
     */
    public function check_if_passes_when_data_valid(): void
    {
        $this->signIn();
        $rows = $this->valid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * @param $rows
     * @return array
     */
    public function getErrors($rows): array
    {
        $errors = [];

        foreach ($rows as $row) {
            $element = new RecipientCountry($row, $this->validation);
            $element->validate()->withErrors();

            if (!empty($element->errors()) || !empty($element->criticals()) || !empty($element->warnings())) {
                $errors[] = $element->errors() + $element->criticals() + $element->warnings();
            }
        }

        return $errors;
    }

    /**
     * @return array
     */
    public function valid_data(): array
    {
        $data = $this->completeData;

        $data[0]['recipient_country_code'] = ['AF', 'AX'];
        $data[0]['recipient_country_percentage'] = ['10', '10']; // sum 20
        $data[0]['recipient_country_narrative'] = ['narrative one', 'narrative two'];
        $data[0]['recipient_region_percentage'] = ['50'];
        $data[0]['recipient_region_code'] = ['289'];
        $data[0]['recipient_region_narrative'] = ['region narrative one'];

        $data[1]['recipient_country_code'] = ['AX'];
        $data[1]['recipient_country_percentage'] = ['100'];
        $data[1]['recipient_country_narrative'] = ['two narrative one'];
        $data[1]['recipient_region_percentage'] = ['20'];
        $data[1]['recipient_region_code'] = ['289'];
        $data[1]['recipient_region_narrative'] = ['two narrative two'];

        return $data;
    }
}
