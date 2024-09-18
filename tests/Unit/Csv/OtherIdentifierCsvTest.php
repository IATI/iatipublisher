<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\OtherIdentifier;
use Illuminate\Support\Arr;

/**
 * Class OtherIdentifierCsvTest.
 */
class OtherIdentifierCsvTest extends CsvBaseTest
{
    /**
     * All invalid data are provided.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function check_if_throws_validation_when_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->invalid_other_identifier_data();
        $errors = [];

        foreach ($rows as $row) {
            $otherIdentifier = new OtherIdentifier($row, $this->validation);
            $otherIdentifier->validate()->withErrors();

            if (!empty($otherIdentifier->errors()) || !empty($otherIdentifier->criticals()) || !empty($otherIdentifier->warnings())) {
                $errors[] = $otherIdentifier->errors() + $otherIdentifier->criticals() + $otherIdentifier->warnings();
            }
        }

        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.type_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.type_is_invalid'), $flattenErrors);
    }

    /**
     * Invalid other identifier data.
     *
     * @return array
     */
    public function invalid_other_identifier_data(): array
    {
        $data = $this->completeData;
        $data[0]['other_identifier_reference'] = ['//\sssss'];
        $data[0]['other_identifier_type'] = ['NOTA3'];
        $data[0]['owner_org_reference'] = ['//\rrrrr'];
        $data[0]['owner_org_narrative'] = ['narrative', 'narrative', 'narrative', 'narrative'];

        return $data;
    }

    /**
     * All valid data were passed.
     *
     * @return void
     * @test
     */
    public function check_if_passes_valid_other_identifier_data(): void
    {
        $this->signIn();
        $rows = $this->valid_other_identifier_data();
        $errors = [];

        foreach ($rows as $row) {
            $otherIdentifier = new OtherIdentifier($row, $this->validation);
            $otherIdentifier->validate()->withErrors();

            if (!empty($otherIdentifier->errors()) || !empty($otherIdentifier->criticals()) || !empty($otherIdentifier->warnings())) {
                $errors[] = $otherIdentifier->errors() + $otherIdentifier->criticals() + $otherIdentifier->warnings();
            }
        }

        $flattenErrors = Arr::flatten($errors);
        $this->assertEmpty($flattenErrors);
    }

    /**
     * Valid data.
     *
     * @return array
     */
    public function valid_other_identifier_data(): array
    {
        $data = $this->completeData;
        $data[0]['other_identifier_reference'] = ['reference one'];
        $data[0]['other_identifier_type'] = ['A3'];
        $data[0]['owner_org_reference'] = ['nice'];
        $data[0]['owner_org_narrative'] = ['narrative', 'narrative', 'narrative', 'narrative'];

        return $data;
    }
}
