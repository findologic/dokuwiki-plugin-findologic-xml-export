<?php
/**
 * General tests for the findologicxmlexport plugin
 *
 * @group plugin_findologicxmlexport
 * @group plugins
 */

//======================================================================
// PLUGIN CONFIGURATION TESTS
//======================================================================

require_once(__DIR__ . '/../DokuwikiXMLExport.php');
require_once(__DIR__ . '/../admin.php');
require_once(__DIR__ . '/../_test/Helper.php');

class configuration_plugin_findologicxmlexport_test extends DokuWikiTest
{
    public function setUp()
    {
        Helper::setUp();
    }

    /**
     * Test to ensure that the configuration works as expected and excluding of pages work properly.
     * Correct pages should be exported when certain are excluded in the configuration.
     * @dataProvider parameterProviderForExcludePagesConf
     */
    public function test_excluded_pages_are_not_exported_($ids)
    {
        $pageIds = ['settingtest1337', 'excludeme1', 'excludeme2'];
        Helper::savePages($pageIds);
        $conf = [];
        // Set configuration
        $conf['plugin']['findologicxmlexport']['excludePages'] = 'excludeme1, excludeme2';
        $xml = Helper::getXML(0, 20, $conf);
        $total = implode('', ($xml->xpath('/findologic/items/@total')));
        $items = $xml->xpath('//item');
        $itemCount = count($items);
        $ordernumbers = $xml->xpath('/findologic/items/item/allOrdernumbers/ordernumbers/ordernumber');
        $ordernumber = $ordernumbers[0];
        $expectedTotal = 1;
        $expectedOrdernumber = $pageIds[0];
        $expectedItems = 1;
        $this->assertEquals($expectedTotal, $total, 'Unexpected total when calling export with two out of three pages are excluded in the configuration.');
        $this->assertEquals($expectedOrdernumber, $ordernumber, 'Unexpected ordernumber when calling export with two out of three pages are excluded in the configuration..');
        $this->assertEquals($expectedItems, $itemCount, 'Unexpected count when calling export with two out of three pages are excluded in the configuration.');
    }

    public function parameterProviderForExcludePagesConf()
    {
        return [
            'three pages, two excluded' => [['notexcluded1337', 'excludeme1', 'excludeme2']],
            'two pages, one excluded' => [['start', 'excludeme1']]
        ];
    }
}