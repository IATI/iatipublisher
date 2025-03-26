<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\ActivityRow;
use App\CsvImporter\Entities\Activity\Components\Elements\Transaction;
use Illuminate\Support\Arr;
use ReflectionClass;

/**
 * Class TransactionCsvTest.
 */
class TransactionCsvTest extends CsvBaseTest
{
    /**
     * Pass if all valid data.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \JsonException
     * @throws \ReflectionException
     * @test
     */
    public function pass_if_valid_data(): void
    {
        $this->signIn();
        $rows = $this->valid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * All valid data.
     *
     * @return array
     */
    public function valid_data(): array
    {
        // here recipient region , country and sector at activity level
        $data = $this->completeData;
        $data[0]['transaction_internal_reference'] = ['12345'];
        $data[0]['transaction_type'] = ['1'];
        $data[0]['transaction_date'] = ['2022-01-01'];
        $data[0]['transaction_value'] = ['5000'];
        $data[0]['transaction_value_date'] = ['2022-02-01'];
        $data[0]['transaction_description'] = ['narrative one'];
        $data[0]['transaction_provider_organisation_identifier'] = ['12345'];
        $data[0]['transaction_provider_organisation_type'] = ['15'];
        $data[0]['transaction_provider_organisation_activity_identifier'] = ['123'];
        $data[0]['transaction_provider_organisation_description'] = ['narrative description'];
        $data[0]['transaction_receiver_organisation_identifier'] = ['12345'];
        $data[0]['transaction_receiver_organisation_type'] = ['10'];
        $data[0]['transaction_receiver_organisation_activity_identifier'] = ['123'];
        $data[0]['transaction_receiver_organisation_description'] = ['narrative description'];
        $data[0]['transaction_sector_vocabulary'] = [];
        $data[0]['transaction_sector_vocabulary_uri'] = [];
        $data[0]['transaction_sector_code'] = [];
        $data[0]['transaction_sector_narrative'] = [];
        $data[0]['transaction_recipient_country_code'] = [];
        $data[0]['transaction_recipient_region_code'] = [];
        $data[0]['transaction_recipient_region_vocabulary_uri'] = [];

        // this second row contains sector, recipient region , recipient region at transaction level
        $data[1]['sector_vocabulary'] = [];
        $data[1]['sector_vocabulary_uri'] = [];
        $data[1]['sector_code'] = [];
        $data[1]['sector_percentage'] = [];
        $data[1]['sector_narrative'] = [];

        $data[1]['recipient_country_code'] = [];
        $data[1]['recipient_country_percentage'] = [];
        $data[1]['recipient_country_narrative'] = [];
        $data[1]['recipient_region_percentage'] = [];
        $data[1]['recipient_region_code'] = [];
        $data[1]['recipient_region_narrative'] = [];
        $data[1]['recipient_region_vocabulary_uri'] = [];

        $data[1]['transaction_internal_reference'] = ['98765'];
        $data[1]['transaction_type'] = ['3'];
        $data[1]['transaction_date'] = ['2022-01-01'];
        $data[1]['transaction_value'] = ['1000'];
        $data[1]['transaction_value_date'] = ['2022-03-01'];
        $data[1]['transaction_description'] = ['transaction narrative'];
        $data[1]['transaction_provider_organisation_identifier'] = ['12345'];
        $data[1]['transaction_provider_organisation_type'] = ['15'];
        $data[1]['transaction_provider_organisation_activity_identifier'] = ['345'];
        $data[1]['transaction_provider_organisation_description'] = ['provider org desc'];
        $data[1]['transaction_receiver_organisation_identifier'] = ['123'];
        $data[1]['transaction_receiver_organisation_type'] = ['21'];
        $data[1]['transaction_receiver_organisation_activity_identifier'] = ['123'];
        $data[1]['transaction_receiver_organisation_description'] = ['nar desc'];
        $data[1]['transaction_sector_vocabulary'] = ['1', '99'];
        $data[1]['transaction_sector_vocabulary_uri'] = ['', 'https://www.google.com'];
        $data[1]['transaction_sector_code'] = ['11110', '12345'];
        $data[1]['transaction_sector_narrative'] = ['narrative one', 'narrative two'];
        $data[1]['transaction_recipient_country_code'] = ['AF', 'NP'];
        $data[1]['transaction_recipient_region_code'] = [];
        $data[1]['transaction_recipient_region_vocabulary_uri'] = [];

        return $data;
    }

    /**
     * Collects validation error messages.
     *
     * @param $rows
     *
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function getErrors($rows): array
    {
        $errors = [];
        foreach ($rows as $row) {
            $activityRow = new ActivityRow(
                $row,
                $this->organization->id,
                $this->user->id,
                $this->getIdentifiers(),
                $this->user->organization->reporting_org
            );
            $activityRow->validateUnique($row);
            $this->validateElements($activityRow);
            $errors[] = $activityRow->errors();
            $transactionRow = $this->getTransactionRows($activityRow);

            foreach ($transactionRow as $tRow) {
                $element = new Transaction($tRow, $activityRow, $this->validation);
                $element->validate()->withErrors();
                if (!empty($element->errors()) || !empty($element->criticals()) || !empty($element->warnings())) {
                    $errors[] = $element->errors() + $element->criticals() + $element->warnings();
                }
            }
        }

        return $errors;
    }

    /**
     * Validate elements.
     *
     * @param $activityRow
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function validateElements($activityRow): mixed
    {
        $activityRowReflection = new ReflectionClass($activityRow);
        $transactionRow = $activityRowReflection->getMethod('validateElements');
        $transactionRow->setAccessible(true);

        return $transactionRow->invoke($activityRow);
    }

    /**
     * Get transaction rows.
     *
     * @param $activityRow
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function getTransactionRows($activityRow): mixed
    {
        $activityRowReflection = new ReflectionClass($activityRow);
        $transactionRow = $activityRowReflection->getProperty('transactionRows');
        $transactionRow->setAccessible(true);

        return $transactionRow->getValue($activityRow);
    }

    /**
     * pass even if reference is duplicate in transaction.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function pass_when_duplicate_reference(): void
    {
        $this->signIn();
        $rows = $this->duplicate_reference_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Duplicate reference data.
     *
     * @return array
     */
    public function duplicate_reference_data(): array
    {
        $data = $this->valid_data();
        $data[0]['transaction_internal_reference'] = ['12345', '12345'];
        $data[1]['transaction_internal_reference'] = ['67890', '67890'];

        return $data;
    }

    /**
     * throw validation if sector already at activity level.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \JsonException
     * @throws \ReflectionException
     * @test
     */
    public function throw_validation_if_sector_at_activity_and_transaction(): void
    {
        $this->signIn();
        $rows = $this->sector_activity_transaction_level_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(
            trans('validation.activity_transactions.sector_in_activity'),
            $flattenErrors
        );
    }

    /**
     * Sector at activity level.
     *
     * @return array
     */
    public function sector_activity_transaction_level_data(): array
    {
        $data = $this->valid_data();
        $data[0]['transaction_sector_vocabulary'] = ['1'];
        $data[0]['transaction_sector_vocabulary_uri'] = [];
        $data[0]['transaction_sector_code'] = ['11110'];
        $data[0]['transaction_sector_narrative'] = ['narrative one'];

        return $data;
    }

    /**
     * Throw if sector empty at activity level but one at transaction level and empty at another transaction.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \JsonException
     * @throws \ReflectionException
     * @test
     */
    public function throw_validation_if_sector_at_one_transaction_empty_at_another_transaction(): void
    {
        $this->signIn();
        $rows = $this->sector_at_one_transaction_empty_at_another_transaction_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(
            trans('validation.activity_transactions.sector.required'),
            $flattenErrors
        );
    }

    /**
     * Sector at one transaction but empty at another.
     *
     * @return array
     */
    public function sector_at_one_transaction_empty_at_another_transaction_data(): array
    {
        $data = $this->valid_data();
        $data[1]['transaction_internal_reference'] = ['98765', '0120'];
        $data[1]['transaction_type'] = ['3', '3'];
        $data[1]['transaction_date'] = ['2022-01-01', '2022-01-01'];
        $data[1]['transaction_value'] = ['1000', '2000'];
        $data[1]['transaction_value_date'] = ['2022-03-01', '2022-03-01'];
        $data[1]['transaction_description'] = ['transaction narrative', 'two narrative'];
        $data[1]['transaction_provider_organisation_identifier'] = ['12345', '54321'];
        $data[1]['transaction_provider_organisation_type'] = ['15', '15'];
        $data[1]['transaction_provider_organisation_activity_identifier'] = ['345', '543'];
        $data[1]['transaction_provider_organisation_description'] = ['provider org desc', ' prov org desc 1'];
        $data[1]['transaction_receiver_organisation_identifier'] = ['123', '321'];
        $data[1]['transaction_receiver_organisation_type'] = ['21', '21'];
        $data[1]['transaction_receiver_organisation_activity_identifier'] = ['123', '321'];
        $data[1]['transaction_receiver_organisation_description'] = ['nar desc', 'nar desc two'];
        $data[1]['transaction_sector_vocabulary'] = ['1', ''];
        $data[1]['transaction_sector_vocabulary_uri'] = ['', ''];
        $data[1]['transaction_sector_code'] = ['11110', ''];
        $data[1]['transaction_sector_narrative'] = ['narrative one', ''];
        $data[1]['transaction_recipient_country_code'] = ['AF', 'NP'];
        $data[1]['transaction_recipient_region_code'] = [];
        $data[1]['transaction_recipient_region_vocabulary_uri'] = [];

        return $data;
    }

    /**
     * Allow negative value in transaction.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \JsonException
     * @throws \ReflectionException
     * @test
     */
    public function pass_if_negative_value_passed_in_transaction(): void
    {
        $this->signIn();
        $rows = $this->negative_value_in_transaction();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Negative value data.
     *
     * @return array
     */
    public function negative_value_in_transaction(): array
    {
        $data = $this->valid_data();
        $data[0]['transaction_value'] = ['-100'];

        return $data;
    }

    /**
     * All data invalid.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \JsonException
     * @throws \ReflectionException
     * @test
     */
    public function throw_all_possible_validation_for_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.amount_number'), $flattenErrors);
        $this->assertContains(trans('validation.organisation_type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.organisation_type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.date_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.date_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.future_date'), $flattenErrors);
        $this->assertContains(trans('validation.future_date'), $flattenErrors);
        $this->assertContains(trans('validation.vocabulary_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.country_code'), $flattenErrors);
        $this->assertContains(trans('validation.sector_code_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.country_code'), $flattenErrors);
        $this->assertContains(trans('validation.url_valid'), $flattenErrors);
    }

    /**
     * Invalid data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        // here recipient region , country and sector at activity level
        $data = $this->completeData;
        $data[0]['transaction_internal_reference'] = ['12345'];
        $data[0]['transaction_type'] = [
            '111111',
            '1',
        ];                                                                                                                          // invalid transaction type
        $data[0]['transaction_date'] = [
            '4000-01-01',
            'invalid date',
        ];                                                                                                                          // future and invalid date
        $data[0]['transaction_value'] = ['invalid value'];                                      // invalid value
        $data[0]['transaction_value_date'] = [
            '4000-02-01',
            ' invalid date',
        ];                                                                                                                          // future and invalid value date
        $data[0]['transaction_description'] = ['narrative one'];
        $data[0]['transaction_provider_organisation_identifier'] = ['12345'];
        $data[0]['transaction_provider_organisation_type'] = ['1500']; // invalid organization type
        $data[0]['transaction_provider_organisation_activity_identifier'] = ['123*(&)'];
        $data[0]['transaction_provider_organisation_description'] = ['narrative description'];
        $data[0]['transaction_receiver_organisation_identifier'] = ['12345'];
        $data[0]['transaction_receiver_organisation_type'] = ['11110'];                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 // invalida organzation type
        $data[0]['transaction_receiver_organisation_activity_identifier'] = ['12*(&)3'];
        $data[0]['transaction_receiver_organisation_description'] = ['narrative description'];
        $data[0]['transaction_sector_vocabulary'] = [];
        $data[0]['transaction_sector_vocabulary_uri'] = [];
        $data[0]['transaction_sector_code'] = [];
        $data[0]['transaction_sector_narrative'] = [];
        $data[0]['transaction_recipient_country_code'] = [];
        $data[0]['transaction_recipient_region_code'] = [];
        $data[0]['transaction_recipient_region_vocabulary_uri'] = [];

        // this second row contains sector, recipient region , recipient region at transaction level
        $data[1]['sector_vocabulary'] = [];                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        // invalid sector vocabulary
        $data[1]['sector_vocabulary_uri'] = [];
        $data[1]['sector_code'] = [];
        $data[1]['sector_percentage'] = [];
        $data[1]['sector_narrative'] = [];

        $data[1]['recipient_country_code'] = [];
        $data[1]['recipient_country_percentage'] = [];
        $data[1]['recipient_country_narrative'] = [];
        $data[1]['recipient_region_percentage'] = [];
        $data[1]['recipient_region_code'] = [];
        $data[1]['recipient_region_narrative'] = [];
        $data[1]['recipient_region_vocabulary_uri'] = [];

        $data[1]['transaction_internal_reference'] = ['98765'];
        $data[1]['transaction_type'] = ['3'];
        $data[1]['transaction_date'] = ['2022-01-01'];
        $data[1]['transaction_value'] = ['1000'];
        $data[1]['transaction_value_date'] = ['2022-03-01'];
        $data[1]['transaction_description'] = ['transaction narrative'];
        $data[1]['transaction_provider_organisation_identifier'] = ['12345'];
        $data[1]['transaction_provider_organisation_type'] = ['15'];
        $data[1]['transaction_provider_organisation_activity_identifier'] = ['345'];
        $data[1]['transaction_provider_organisation_description'] = ['provider org desc'];
        $data[1]['transaction_receiver_organisation_identifier'] = ['123'];
        $data[1]['transaction_receiver_organisation_type'] = ['21'];
        $data[1]['transaction_receiver_organisation_activity_identifier'] = ['123'];
        $data[1]['transaction_receiver_organisation_description'] = ['nar desc'];

        $data[1]['transaction_sector_vocabulary'] = ['111111', '1', '99'];   // invalid sector vocabulary
        $data[1]['transaction_sector_vocabulary_uri'] = ['', '', 'invalid url']; // invalid url
        $data[1]['transaction_sector_code'] = ['1', '009', '123'];     // invalid sector code
        $data[1]['transaction_sector_narrative'] = ['narrative one', 'narrative two', 'narrative three'];

        $data[1]['transaction_recipient_country_code'] = [
            'asdf',
            'asdf',
            'NasdfasdfO',
        ]; // invalid recipient country code
        $data[1]['transaction_recipient_region_code'] = [];
        $data[1]['transaction_recipient_region_vocabulary_uri'] = [];

        return $data;
    }

    /**
     * Throw validation if region or country already at activity level.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \JsonException
     * @throws \ReflectionException
     * @test
     */
    public function throw_validation_if_region_or_country_already_activity_level(): void
    {
        $this->signIn();
        $rows = $this->region_or_country_already_at_activity_level_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(
            trans('validation.activity_transactions.country_region_in_activity'),
            $flattenErrors
        );
    }

    /**
     * country or region already at activity level.
     *
     * @return array
     */
    public function region_or_country_already_at_activity_level_data(): array
    {
        $data = $this->valid_data();
        $data[0]['transaction_recipient_country_code'] = ['AF'];
        $data[0]['transaction_recipient_region_code'] = ['1'];

        return $data;
    }

    /**
     * Throw validation if both region or country at transaction.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \JsonException
     * @throws \ReflectionException
     * @test
     */
    public function throw_validation_if_both_region_or_country_at_transaction(): void
    {
        $this->signIn();
        $rows = $this->both_region_and_country_at_transaction_level_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.activity_transactions.country_or_region'), $flattenErrors);
    }

    /**
     * region or country both at transaction level.
     *
     * @return array
     */
    public function both_region_and_country_at_transaction_level_data(): array
    {
        $data = $this->valid_data();
        // this second row contains sector, recipient region , recipient region at transaction level
        $data[1]['sector_vocabulary'] = [];
        $data[1]['sector_vocabulary_uri'] = [];
        $data[1]['sector_code'] = [];
        $data[1]['sector_percentage'] = [];
        $data[1]['sector_narrative'] = [];

        $data[1]['recipient_country_code'] = [];
        $data[1]['recipient_country_percentage'] = [];
        $data[1]['recipient_country_narrative'] = [];
        $data[1]['recipient_region_percentage'] = [];
        $data[1]['recipient_region_code'] = [];
        $data[1]['recipient_region_narrative'] = [];
        $data[1]['recipient_region_vocabulary_uri'] = [];

        $data[1]['transaction_internal_reference'] = ['98765'];
        $data[1]['transaction_type'] = ['3'];
        $data[1]['transaction_date'] = ['2022-01-01'];
        $data[1]['transaction_value'] = ['1000'];
        $data[1]['transaction_value_date'] = ['2022-03-01'];
        $data[1]['transaction_description'] = ['transaction narrative'];
        $data[1]['transaction_provider_organisation_identifier'] = ['12345'];
        $data[1]['transaction_provider_organisation_type'] = ['15'];
        $data[1]['transaction_provider_organisation_activity_identifier'] = ['345'];
        $data[1]['transaction_provider_organisation_description'] = ['provider org desc'];
        $data[1]['transaction_receiver_organisation_identifier'] = ['123'];
        $data[1]['transaction_receiver_organisation_type'] = ['21'];
        $data[1]['transaction_receiver_organisation_activity_identifier'] = ['123'];
        $data[1]['transaction_receiver_organisation_description'] = ['nar desc'];
        $data[1]['transaction_sector_vocabulary'] = ['1', '99'];
        $data[1]['transaction_sector_vocabulary_uri'] = ['', 'https://www.google.com'];
        $data[1]['transaction_sector_code'] = ['11110', '12345'];
        $data[1]['transaction_sector_narrative'] = ['narrative one', 'narrative two'];
        $data[1]['transaction_recipient_country_code'] = ['AF', 'NP'];
        $data[1]['transaction_recipient_region_code'] = ['1', '99'];
        $data[1]['transaction_recipient_region_vocabulary_uri'] = ['', 'https://www.google.com'];

        return $data;
    }

    /**
     * throw validation region or country at one transaction but empty at other.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \JsonException
     * @throws \ReflectionException
     * @test
     */
    public function throw_validation_if_region_or_country_at_one_transaction_empty_at_another_transaction(): void
    {
        $this->signIn();
        $rows = $this->region_or_country_at_one_transaction_empty_at_another_transaction_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.activity_transactions.country_or_region'), $flattenErrors);
    }

    /**
     * Region or country at one transaction but empty at other data.
     *
     * @return array
     */
    public function region_or_country_at_one_transaction_empty_at_another_transaction_data(): array
    {
        $data = $this->valid_data();
        $data[1]['transaction_internal_reference'] = ['98765', '0120'];
        $data[1]['transaction_type'] = ['3', '3'];
        $data[1]['transaction_date'] = ['2022-01-01', '2022-01-01'];
        $data[1]['transaction_value'] = ['1000', '2000'];
        $data[1]['transaction_value_date'] = ['2022-03-01', '2022-03-01'];
        $data[1]['transaction_description'] = ['transaction narrative', 'two narrative'];
        $data[1]['transaction_provider_organisation_identifier'] = ['12345', '54321'];
        $data[1]['transaction_provider_organisation_type'] = ['15', '15'];
        $data[1]['transaction_provider_organisation_activity_identifier'] = ['345', '543'];
        $data[1]['transaction_provider_organisation_description'] = ['provider org desc', ' prov org desc 1'];
        $data[1]['transaction_receiver_organisation_identifier'] = ['123', '321'];
        $data[1]['transaction_receiver_organisation_type'] = ['21', '21'];
        $data[1]['transaction_receiver_organisation_activity_identifier'] = ['123', '321'];
        $data[1]['transaction_receiver_organisation_description'] = ['nar desc', 'nar desc two'];
        $data[1]['transaction_sector_vocabulary'] = [];
        $data[1]['transaction_sector_vocabulary_uri'] = [];
        $data[1]['transaction_sector_code'] = [];
        $data[1]['transaction_sector_narrative'] = [];
        $data[1]['transaction_recipient_country_code'] = ['AF'];
        $data[1]['transaction_recipient_region_code'] = [];
        $data[1]['transaction_recipient_region_vocabulary_uri'] = [];

        return $data;
    }
}
