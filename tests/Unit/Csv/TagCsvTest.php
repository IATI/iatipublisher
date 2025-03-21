<?php

namespace Tests\Unit\Csv;

use App\CsvImporter\Entities\Activity\Components\Elements\Tag;
use Illuminate\Support\Arr;

/**
 * Class TagCsvTest.
 */
class TagCsvTest extends CsvBaseTest
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
            $element = new Tag($row, $this->validation);
            $element->validate()->withErrors();

            if (!empty($element->errors()) || !empty($element->criticals()) || !empty($element->warnings())) {
                $errors[] = $element->errors() + $element->criticals() + $element->warnings();
            }
        }

        return $errors;
    }

    /**
     * All Data Invalid.
     *
     * @return void
     * @test
     * @throws \JsonException
     */
    public function throw_validation_invalid_data(): void
    {
        $this->signIn();
        $rows = $this->invalid_data();
        $errors = $this->getErrors($rows);
        $flattenErrors = Arr::flatten($errors);

        $this->assertContains(trans('validation.vocabulary_is_invalid'), $flattenErrors);
        $this->assertContains(trans('validation.activity_tag.invalid_sdg_code'), $flattenErrors);
        $this->assertContains(trans('validation.activity_tag.invalid_sdg_targets_code'), $flattenErrors);
        $this->assertContains(trans('validation.url_valid'), $flattenErrors);
    }

    /**
     * Invalid tag data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeData;
        $data[0]['tag_vocabulary'] = ['0987', '2', '3', '99'];
        $data[0]['tag_code'] = ['invalid vocab', 'invalid code', 'invalid code', '3333'];
        $data[0]['tag_vocabulary_uri'] = ['', '', '', 'invalid url'];
        $data[0]['tag_narrative'] = ['nar1', 'narr2', 'narr3', 'narr 4'];

        $data[1]['tag_vocabulary'] = ['0987', '2', '3', '99'];
        $data[1]['tag_code'] = ['invalid vocab', 'invalid code', 'invalid code', '3333'];
        $data[1]['tag_vocabulary_uri'] = ['', '', '', 'invalid url'];
        $data[1]['tag_narrative'] = ['nar1', 'narr2', 'narr3', 'narr 4'];

        return $data;
    }

    /**
     * Pass if all data valid.
     *
     * @return void
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
     * All valid data.
     *
     * @return array
     */
    public function valid_data(): array
    {
        $data = $this->completeData;
        $data[0]['tag_vocabulary'] = ['1', '2', '3', '99'];
        $data[0]['tag_code'] = ['1234', '1', '1.1', '12345'];
        $data[0]['tag_vocabulary_uri'] = ['', '', '', 'https://www.google.com'];
        $data[0]['tag_narrative'] = ['nar1', 'narr2', 'narr3', 'narr 4'];

        $data[1]['tag_vocabulary'] = ['1', '2', '3', '99'];
        $data[1]['tag_code'] = ['1234', '1', '1.1', '12345'];
        $data[1]['tag_vocabulary_uri'] = ['', '', '', 'https://www.google.com'];
        $data[1]['tag_narrative'] = ['nar1', 'narr2', 'narr3', 'narr 4'];

        return $data;
    }
}
