<?php
/**
 * General tests for the findologicxmlexport plugin
 *
 * @group plugin_findologicxmlexport
 * @group plugins
 */

//======================================================================
// EXPORT CALL TESTS
//======================================================================

require_once(__DIR__ . '/../DokuwikiXMLExport.php');
require_once(__DIR__ . '/../admin.php');
require_once(__DIR__ . '/helper.php');

class export_call_test extends DokuWikiTest
{
    public function setUp()
    {
        $helper = new helper;
        $helper->setUp();
    }

    /**
     * Test to ensure that parameters are correctly handled in the export call and
     * an exception is thrown if it does not work.
     *
     * @param integer $start Start value for Export call
     * @param integer $count Count value for Export call
     * @param string $message Error message if test fails
     * @dataProvider parameterProviderForXMLCall
     */
    public function test_exception_is_thrown_when_calling_export_with_wrong_params($start, $count, $message)
    {
        try {
            $helper = new helper;
            $helper->savePages(array('demopage1'));
            $xml = $helper->getXML($start, $count);
            $this->fail($message);
        } catch (\InvalidArgumentException $e) {
        }
    }

    public function parameterProviderForXMLCall()
    {
        return [
            'test start = 1 and count = 1' => [1, 1, 'An exception should be thrown, because you can\'t get the second page if there is none.'],
            'test start = 2 and count = 1' => [2, 1, 'An exception should be thrown, because you can\'t get the third page if there is none.'],
            'test start = 0 and count = 0' => [0, 0, 'An exception should be thrown, because you can\'t get a page with no count.'],
            'test start = 1 and count = 0' => [1, 0, 'An exception should be thrown, because you can\'t get the second page with no count and does not even exist.'],
            'test start = 0 and count = -1' => [0, -1, 'An exception should be thrown, because you can\'t get a page with a negative count value.'],
            'test start = -1 and count = 0' => [-1, 0, 'An exception should be thrown, because you can\'t get a page with a negative start value.']
        ];
    }
}