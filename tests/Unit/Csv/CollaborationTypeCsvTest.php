<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\CollaborationType;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;

/**
 * Class CollaborationTypeCsvTest.
 */
class CollaborationTypeCsvTest extends CsvBaseTest
{
    /**
     * Collects error messages.
     *
     * @throws \JsonException
     */
    public function getErrors($rows): array
    {
        $errors = [];
        foreach ($rows as $row) {
            $element = new CollaborationType($row, $this->validation);
            $element->validate()->withErrors();

            if (!empty($element->errors()) || !empty($element->criticals()) || !empty($element->warnings())) {
                $errors[] = $element->errors() + $element->criticals() + $element->warnings();
            }
        }

        return $errors;
    }

    /**
     * Throws all validation messages if all invalid data.
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

        $this->assertContains('The collaboration type does not exist.', $flattenErrors);
        $this->assertContains('The collaboration type cannot have more than one value.', $flattenErrors);
    }

    /**
     * Invalid collaboration type data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['collaboration_type'] = [99];
        $data[1]['collaboration_type'] = [1, 1];

        return $data;
    }

    /**
     * All data invalid.
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
     * Valid data.
     *
     * @return array
     */
    public function valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['collaboration_type'] = [1];
        $data[1]['collaboration_type'] = [6];

        return $data;
    }
}
