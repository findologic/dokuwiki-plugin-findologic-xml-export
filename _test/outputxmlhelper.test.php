<?php

require_once(__DIR__ . '/../OutputXMLHelper.php');
require_once(__DIR__ . '/../_test/Helper.php');

class outputxmlhelper_test extends DokuWikiTest
{
    public function setUp()
    {
        Helper::setUp();
    }

    /**
     * Test to ensure that parameters are correctly handled when calling the Helper method with valid parameters.
     *
     * @param integer $start Start value for Export call
     * @param integer $count Count value for Export call
     * @dataProvider parameterProviderForXMLCallWithValidParams
     */
    function test_export_call_with_params($start, $count) {
        $outputXmlHelper = new OutputXMLHelper();
        $this->assertEquals(true, $outputXmlHelper->paramsValid($start, $count), 'Expected params should be valid.');
        $outputXmlHelper->getXml($start, $count);
    }

    public function parameterProviderForXMLCallWithValidParams()
    {
        return [
            'start = 0 and count = 1' => [0, 1],
            'start = 1 and count = 1' => [1, 1],
            'start = 10 and count = 1' => [10, 1],
            'start = 1 and count = 2' => [1, 2],
            'start = 1 and count = 10' => [1, 10]
        ];
    }

    /**
     * Test to ensure that parameters are correctly handled when calling the Helper method with invalid parameters.
     *
     * @param integer $start Start value for Export call
     * @param integer $count Count value for Export call
     * @dataProvider parameterProviderForXMLCallWithInvalidParams
     */
    function test_export_call_works_when_calling_export_with_invalid_params($start, $count) {
        $outputXmlHelper = new OutputXMLHelper();
        try {
            $this->assertEquals(false, $outputXmlHelper->paramsValid($start, $count), 'Expected params should be not valid.');
            $outputXmlHelper->getXml($start, $count);
            $this->fail('Invalid params should be recognized as invalid.');
        } catch (\InvalidArgumentException $e) {
        }

    }

    public function parameterProviderForXMLCallWithInvalidParams()
    {
        return [
            'invalid start = 0 and count = 0' => [0, 0],
            'invalid start = 1 and count = 0' => [1, 0],
            'invalid start = 0 and count = -1' => [0, -1],
            'invalid start = -1 and count = 0' => [-1, 0]
        ];
    }

    /**
     * Test to ensure that parameters are correctly handled when calling the Helper method with invalid parameters.
     *
     * @param integer $start Start value for Export call
     * @param integer $count Count value for Export call
     * @dataProvider parameterProviderForXMLCallWithFloatParams
     */
    function test_export_call_works_when_calling_export_with_float_params($start, $count) {
        $outputXmlHelper = new OutputXMLHelper();
        $this->assertEquals(false, $outputXmlHelper->paramsValid($start, $count), 'Expected params should be recognized as invalid.');
    }

    public function parameterProviderForXMLCallWithFloatParams()
    {
        return [
            'invalid start = 13.37 and count = 1' => [13.37, 1.2],
            'invalid start = 0 and count = 0' => [22.5, 01.22],
            'invalid start = 1 and count = 0' => [55.2, 2],
            'invalid start = 0 and count = -1' => [22.4, 300],
            'invalid start = -1 and count = 0' => [1.1, 02.00]
        ];
    }
}