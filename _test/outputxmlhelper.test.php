<?php
/**
 * General tests for the findologicxmlexport plugin
 *
 * @group plugin_findologicxmlexport
 * @group plugins
 */

require_once(__DIR__ . '/../DokuwikiXMLExport.php');
require_once(__DIR__ . '/../OutputXMLHelper.php');
require_once(__DIR__ . '/../admin.php');
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
        $this->assertEquals(true, $outputXmlHelper->paramsValid($start, $count, FILTER_VALIDATE_INT), 'Expected params should be valid.');
        $outputXmlHelper->getXml($start, $count);
    }

    public function parameterProviderForXMLCallWithValidParams()
    {
        return [
            'start = 0 and count = 1' => [0, 1],
            'start = 1 and count = 1' => [1, 1],
            'start = 10 and count = 1' => [10, 1],
            'start = 1 and count = 2' => [1, 2],
            'start = 1 and count = 10' => [1, 10],
            'start = "1" and count = "10"' => ['1', '10']
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
            $this->assertEquals(false, $outputXmlHelper->paramsValid($start, $count, FILTER_VALIDATE_INT), 'Expected params should be invalid.');
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
     * Test to ensure that parameters are correctly handled when calling the Helper method with float parameters.
     *
     * @param integer $start Start value for Export call
     * @param integer $count Count value for Export call
     * @dataProvider parameterProviderForXMLCallWithFloatOrStringParams
     */
    function test_export_call_works_when_calling_export_with_float_or_string_params($start, $count) {
        $outputXmlHelper = new OutputXMLHelper();
        $this->assertEquals(false, $outputXmlHelper->paramsValid($start, $count, FILTER_VALIDATE_INT), 'Expected float params should be invalid.');
    }

    public function parameterProviderForXMLCallWithFloatOrStringParams()
    {
        return [
            'invalid start = 13.37 and count = 1.2' => [13.37, 1.2],
            'invalid start = 22.5 and count = 01.22' => [22.5, 01.22],
            'invalid start = 55.2 and count = 2' => [55.2, 2],
            'invalid start = 22.4 and count = 300' => [22.4, 300],
            'invalid start = 1.1 and count = 02.00' => [1.1, 02.00]
        ];
    }

    /**
     * Test to ensure that the getUrlParam method gets the correct url params set in the url
     * and formats them as integers, and not as strings.
     */
    public function test_geturlparam_returns_set_url_parameters()
    {
        $startName = 'start';
        $countName = 'count';
        $defaultStart = 0;
        $defaultCount = 20;

        $expectedStartValue = 1;
        $expectedCountValue = 5000;

        $getParamWithRandomParams = ['asd' => 'hehe', 'demo' => 'eeeeh', 'start' => '1', 'count' => '5000'];
        $outputXmlHelper = new OutputXMLHelper();
        $start = $outputXmlHelper->getUrlParam($startName, $defaultStart, $getParamWithRandomParams);
        $count = $outputXmlHelper->getUrlParam($countName, $defaultCount, $getParamWithRandomParams);

        $this->assertEquals($expectedStartValue, $start, 'Expected parameter start value does not match the given _GET parameter');
        $this->assertEquals($expectedCountValue, $count, 'Expected parameter count value does not match the given _GET parameter');
    }

    /**
     * Test to ensure that the getUrlParam method gets the default params if _GET param is not set.
     */
    public function test_geturlparam_returns_default_if_not_specified()
    {
        $startName = 'start';
        $countName = 'count';
        $defaultStart = 0;
        $defaultCount = 20;

        $getParamWithRandomParams = ['asd' => 'hehe', 'demo' => 'eeeeh'];
        $outputXmlHelper = new OutputXMLHelper();
        $start = $outputXmlHelper->getUrlParam($startName, $defaultStart, $getParamWithRandomParams);
        $count = $outputXmlHelper->getUrlParam($countName, $defaultCount, $getParamWithRandomParams);

        $this->assertEquals($defaultStart, $start, 'Expected parameter start value does not match the given _GET parameter');
        $this->assertEquals($defaultCount, $count, 'Expected parameter count value does not match the given _GET parameter');
    }

    public function test_error_should_be_thrown_when_calling_throwerror()
    {
        $outputXmlHelper = new OutputXMLHelper();
        $expectedErrorMessage = '<div class="error">start and count values are not valid</div>';

        ob_start();
        $outputXmlHelper->throwError();
        $error = ob_get_clean();

        $this->assertEquals($expectedErrorMessage, $error);
    }

    public function test_xml_will_be_printed_when_calling_printxml()
    {
        $outputXmlHelper = new OutputXMLHelper();
        $expectedErrorMessage = '<?xml version="1.0" encoding="utf-8"?>
<findologic version="1.0"><items start="0" count="20" total="0"/></findologic>';

        ob_start();
        $outputXmlHelper->printXml(0, 20);
        $error = ob_get_clean();

        $this->assertEquals($expectedErrorMessage, trim($error));
    }
}