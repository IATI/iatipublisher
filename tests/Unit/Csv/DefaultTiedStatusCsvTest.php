<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\DefaultTiedStatus;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;

/**
 * Class DefaultTiedStatusCsvTest.
 */
class DefaultTiedStatusCsvTest extends CsvBaseTest
{
    /**
     * Collects validation error messages.
     *
     * @throws \JsonException
     */
    public function getErrors($rows): array
    {
        $errors = [];
        foreach ($rows as $row) {
            $element = new DefaultTiedStatus($row, $this->validation);
            $element->validate()->withErrors();

            if (!empty($element->errors()) || !empty($element->criticals()) || !empty($element->warnings())) {
                $errors[] = $element->errors() + $element->criticals() + $element->warnings();
            }
        }

        return $errors;
    }

    /**
     * Throw Validation messages if invalid data.
     *
     * @return void
     * @throws \JsonException
     */
    #[Test]
    public function throw_validation_if_invalid_value(): void
    {
        $this->signIn();
        $rows = $this->invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains('The default tied status does not exist.', $flattenErrors);
        $this->assertContains('The default tied status cannot have more than one value.', $flattenErrors);
    }

    /**
     * Invalid Default tied status.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['default_tied_status'] = [99];
        $data[1]['default_tied_status'] = [1, 1];

        return $data;
    }

    /**
     * Valid data.
     *
     * @return void
     * @throws \JsonException
     */
    #[Test]
    public function pass_if_valid_data(): void
    {
        $this->signIn();
        $rows = $this->valid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertEmpty($flattenErrors);
    }

    /**
     * All Valid data.
     *
     * @return array
     */
    public function valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['default_tied_status'] = [3];
        $data[1]['default_tied_status'] = [4];

        return $data;
    }
}
