<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\RecipientRegion;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;

/**
 * Class RecipientRegionCsvTest.
 */
class RecipientRegionCsvTest extends CsvBaseTest
{
    /**
     * Throw validation if sum of region and country percentage not equal to 100.
     *
     * @return void
     * @test
     * @throws \JsonException
     * @throws BindingResolutionException
     */
    public function check_if_throws_validation_when_region_country_percentage_sum_not_equal_to_100(): void
    {
        $this->signIn();
        $rows = $this->region_country_percentage_sum_not_equal_to_100_single_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.recipient_country_region_percentage_sum'), $flattenErrors);
    }

    /**
     * 50-50.
     * region or country percentage sum equal to 100.
     *
     * @return void
     * @test
     * @throws \JsonException
     * @throws BindingResolutionException
     */
    public function pass_if_region_country_percentage_sum_equal_to_100(): void
    {
        $this->signIn();
        $rows = $this->region_country_percentage_sum_equal_to_100_single_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Throw validation if same vocabulary percentage sum not equal to 100
     * if country 20 then vocab percentage not equal to 80.
     *
     * @return void
     * @test
     * @throws \JsonException
     * @throws BindingResolutionException
     */
    public function throw_validation_if_same_vocabulary_sum_not_equal_to_80_if_country_20(): void
    {
        $this->signIn();
        $rows = $this->country_20_region_60_multiple_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.recipient_country_region_percentage_sum'), $flattenErrors);
    }

    /**
     * Throw validation if Percentage sum withing same vocabulary not equal.
     *
     * @return void
     * @test
     */
    public function throw_validation_percentage_sum_within_same_vocabulary_not_equal(): void
    {
        $this->signIn();
        $rows = $this->diff_vocal_percentage();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.recipient_country_region_percentage_sum'), $flattenErrors);
    }

    /**
     * Throws and collects Errors.
     *
     * @param $rows
     * @return array
     * @throws BindingResolutionException
     * @throws \JsonException
     */
    public function getErrors($rows): array
    {
        $errors = [];
        foreach ($rows as $row) {
            $element = new RecipientRegion($row, $this->validation);
            $element->validate()->withErrors();

            if (!empty($element->errors()) || !empty($element->criticals()) || !empty($element->warnings())) {
                $errors[] = $element->errors() + $element->criticals() + $element->warnings();
            }
        }

        return $errors;
    }

    /**
     * Recipient region 25
     * Recipient Country 25.
     *
     * @return array
     */
    public function region_country_percentage_sum_not_equal_to_100_single_data(): array
    {
        $data = $this->completeData;
        $data[0]['recipient_country_code'] = ['AF'];
        $data[0]['recipient_country_percentage'] = ['25'];
        $data[0]['recipient_country_narrative'] = ['narrative one'];
        $data[0]['recipient_region_percentage'] = ['25'];
        $data[0]['recipient_region_code'] = ['289'];
        $data[0]['recipient_region_narrative'] = ['region narrative one'];

        $data[1]['recipient_country_code'] = ['AF'];
        $data[1]['recipient_country_percentage'] = ['25'];
        $data[1]['recipient_country_narrative'] = ['two narrative one'];
        $data[1]['recipient_region_percentage'] = ['25'];
        $data[1]['recipient_region_code'] = ['289'];
        $data[1]['recipient_region_narrative'] = ['two narrative two'];

        return $data;
    }

    /**
     * Recipient region 50
     * Recipient Country 50.
     *
     * @return array
     */
    public function region_country_percentage_sum_equal_to_100_single_data(): array
    {
        $data = $this->completeData;
        $data[0]['recipient_country_code'] = ['AF'];
        $data[0]['recipient_country_percentage'] = ['50'];
        $data[0]['recipient_country_narrative'] = ['narrative one'];
        $data[0]['recipient_region_percentage'] = ['50'];
        $data[0]['recipient_region_code'] = ['289'];
        $data[0]['recipient_region_narrative'] = ['region narrative one'];

        $data[1]['recipient_country_code'] = ['AF'];
        $data[1]['recipient_country_percentage'] = ['50'];
        $data[1]['recipient_country_narrative'] = ['two narrative one'];
        $data[1]['recipient_region_percentage'] = ['50'];
        $data[1]['recipient_region_code'] = ['289'];
        $data[1]['recipient_region_narrative'] = ['two narrative two'];

        return $data;
    }

    /**
     * When vocabulary is same.
     *
     * @return array
     */
    public function country_20_region_60_multiple_data(): array
    {
        $data = $this->completeData;
        $data[0]['recipient_country_code'] = ['AF'];
        $data[0]['recipient_country_percentage'] = ['20'];
        $data[0]['recipient_country_narrative'] = ['narrative one'];
        $data[0]['recipient_region_percentage'] = ['30', '30'];
        $data[0]['recipient_region_code'] = ['289', '88'];
        $data[0]['recipient_region_narrative'] = ['region narrative one', 'two'];

        $data[1]['recipient_country_code'] = ['AF'];
        $data[1]['recipient_country_percentage'] = ['20'];
        $data[1]['recipient_country_narrative'] = ['one narrative one'];
        $data[1]['recipient_region_percentage'] = ['30', '30'];
        $data[1]['recipient_region_code'] = ['289', '89'];
        $data[1]['recipient_region_narrative'] = ['one narrative two', 'two narrative two'];

        return $data;
    }

    /**
     * Different vocal percentage.
     *
     * @return array
     */
    public function diff_vocal_percentage(): array
    {
        $data = $this->completeData;
        $data[0]['recipient_country_code'] = ['AF'];
        $data[0]['recipient_country_percentage'] = ['40'];
        $data[0]['recipient_country_narrative'] = ['narrative one'];
        $data[0]['recipient_region_percentage'] = ['30', '30', '30'];
        $data[0]['recipient_region_code'] = ['289', '88123', '12345'];
        $data[0]['recipient_region_narrative'] = ['region narrative one', 'two'];
        $data[0]['recipient_region_vocabulary_uri'] = ['', 'https://www.google.com', 'https://www.google.com'];

        $data[1]['recipient_country_code'] = ['AF'];
        $data[1]['recipient_country_percentage'] = ['40'];
        $data[1]['recipient_country_narrative'] = ['one narrative one'];
        $data[1]['recipient_region_percentage'] = ['30', '30', '30'];
        $data[1]['recipient_region_code'] = ['289', '88123', '12345'];
        $data[1]['recipient_region_narrative'] = ['one narrative two', 'two narrative two'];
        $data[1]['recipient_region_vocabulary_uri'] = ['', 'https://www.google.com', 'https://www.google.com'];

        return $data;
    }

    /**
     * invalid url
     * country already 100%.
     *
     * @return void
     * @test
     */
    public function throw_possible_validation_for_all_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.activity_recipient_region.percentage.country_percentage_complete'), $flattenErrors);
        $this->assertContains(trans('validation.url_valid'), $flattenErrors);
        $this->assertContains(trans('validation.percentage_must_be_a_number'), $flattenErrors);
        $this->assertContains(trans('validation.percentage_must_be_at_least_0'), $flattenErrors);
    }

    /**
     * All invalid data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['recipient_country_code'] = ['AF'];
        $data[0]['recipient_country_percentage'] = ['100']; // already 100% vakudation message
        $data[0]['recipient_country_narrative'] = ['narrative one'];
        $data[0]['recipient_region_percentage'] = ['60', '30', '30'];
        $data[0]['recipient_region_code'] = ['289', '88123', '12345'];
        $data[0]['recipient_region_narrative'] = ['region narrative one', 'two'];
        $data[0]['recipient_region_vocabulary_uri'] = ['', 'invalid url', 'https://www.google.com']; // invalid url

        $data[1]['recipient_country_code'] = ['AF'];
        $data[1]['recipient_country_percentage'] = ['-40'];
        $data[1]['recipient_country_narrative'] = ['one narrative one'];
        $data[1]['recipient_region_percentage'] = ['-30', 'asdfsa']; // negative percentage && aplha character
        $data[1]['recipient_region_code'] = ['289', '88123', '12345'];
        $data[1]['recipient_region_narrative'] = ['one narrative two', 'two narrative two'];
        $data[1]['recipient_region_vocabulary_uri'] = ['', 'https://www.google.com', 'https://www.google.com'];

        return $data;
    }
}
