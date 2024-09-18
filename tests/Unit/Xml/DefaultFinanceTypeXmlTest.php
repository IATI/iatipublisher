<?php

namespace Tests\Unit\Xml;

/**
 * Class DefaultFinanceTypeXmltest.
 */
class DefaultFinanceTypeXmlTest extends XmlBaseTest
{
    /**
     * Throws validation messages for all invalid data.
     *
     * @return void
     * @test
     */
    public function throw_validation_if_invalid_value(): void
    {
        $rows = $this->invalid_data();
        $flattenErrors = $this->getErrors($rows);

        $this->assertContains(trans('validation.activity_default_finance_type.in'), $flattenErrors);
        $this->assertContains(trans('validation.activity_default_finance_type.size'), $flattenErrors);
    }

    /**
     * Invalid default finance type.
     *
     * @return array
     */
    public function invalid_data(): array
    {
        $data = $this->completeXml;
        $data[0]['default_finance_type'] = 'invalid';
        $data[1]['default_finance_type'] = ['1', '1'];

        return $data;
    }
}
