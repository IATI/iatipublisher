<?php

namespace Tests\Unit\Xml;

/**
 * Class ActivityScopeXmlTest.
 */
class ActivityScopeXmlTest extends XmlBaseTest
{
    /**
     * Throws validation for all invalid data.
     *
     * @return void
     * @test
     */
    public function check_throws_validation_when_invalid_data(): void
    {
        $rows = $this->invalid_activity_scope_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.activity_scope.in'), $flattenErrors);
        $this->assertContains(trans('validation.activity_scope.size'), $flattenErrors);
    }

    /**
     * Invalid scope data.
     *
     * @return array
     */
    public function invalid_activity_scope_data(): array
    {
        $data = $this->completeXml;
        $data[0]['activity_scope'] = 'Invalid Status';
        $data[1]['activity_scope'] = ['1', '1'];

        return $data;
    }
}
