<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\Title;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;

/**
 * Class TitleCsvTest.
 */
class TitleCsvTest extends CsvBaseTest
{
    /**
     * checks if it throws validation if title empty.
     *
     * @return void
     */
    #[Test]
    public function check_if_throws_validation_if_title_empty(): void
    {
        $this->signIn();
        $rows = $this->title_empty_data();
        $errors = [];

        foreach ($rows as $row) {
            $title = new Title($row, $this->validation);
            $title->validate()->withErrors();

            if (!empty($title->errors()) || !empty($title->criticals()) || !empty($title->warnings())) {
                $errors[] = $title->errors() + $title->criticals() + $title->warnings();
            }
        }

        $this->assertContains('The activity title is required.', Arr::flatten($errors));
    }

    /**
     * Multiple activity tile upload is not allowed in csv
     * Check if it throws validation if multiple activity title.
     *
     * @return void
     */
    #[Test]
    public function check_throws_validation_if_title_multiple(): void
    {
        $this->signIn();
        $rows = $this->multiple_title_data();
        $errors = [];

        foreach ($rows as $row) {
            $title = new Title($row, $this->validation);
            $title->validate()->withErrors();

            if (!empty($title->errors()) || !empty($title->criticals()) || !empty($title->warnings())) {
                $errors[] = $title->errors() + $title->criticals() + $title->warnings();
            }
        }

        $this->assertContains('There should be only one activity title.', Arr::flatten($errors));
    }

    /**
     * sets of data with empty title.
     *
     * @return array
     */
    public function title_empty_data(): array
    {
        $data = $this->completeData;
        $data[0]['activity_title'] = [];

        return $data;
    }

    /**
     * Multiple title data.
     *
     * @return array
     */
    public function multiple_title_data(): array
    {
        $data = $this->completeData;
        $data[0]['activity_title'] = ['title one', 'title two'];

        return $data;
    }
}
