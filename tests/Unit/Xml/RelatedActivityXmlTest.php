<?php

namespace Tests\Unit\Xml;

use PHPUnit\Framework\Attributes\Test;

/**
 * Class RelatedActivityXmlTest.
 */
class RelatedActivityXmlTest extends XmlBaseTest
{
    /**
     * Throws validation messages for all invalid data.
     *
     * @return void
     */
    #[Test]
    public function throw_validation_if_invalid_data(): void
    {
        $rows = $this->invalid_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.this_field_is_invalid'), $flattenErrors);
    }

    /**
     * Invalid related activity data.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['related_activity'] = [
            [
                'relationship_type' => 'invalid',
                'activity_identifier' => 'invalid',
            ],
        ];

        return $data;
    }
}
