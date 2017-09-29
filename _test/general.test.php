<?php
/**
 * General tests for the findologicxmlexport plugin
 *
 * @group plugin_findologicxmlexport
 * @group plugins
 */

require_once(__DIR__ . '/../DokuwikiXMLExport.php');
require_once(__DIR__ . '/../admin.php');

class general_plugin_findologicxmlexport_test extends DokuWikiTest
{
    //======================================================================
    // HELPER FUNCTIONS AND CONSTANTS
    //======================================================================

    /**
     * A page needs to have content. This is the placeholder for this value.
     */
    const PAGE_CONTENT_PLACEHOLDER = 'This page is for test purposes.';

    /**
     * Helper function to save a page.
     *
     * @param array $ids An array with page ids (eg. start or wiki:syntax)
     * @param string $content Optional content, else the default test value will be set
     */
    private function savePages($ids, $content = self::PAGE_CONTENT_PLACEHOLDER)
    {
        foreach ($ids as $id) {
            saveWikiText($id, $content, '');
            idx_addPage($id);
        }
    }

    /**
     * Gets parameters, calls the Export and returns the XML as SimpleXMLElement.
     *
     * @param int $start Optional start value
     * @param int $count Optional count value
     * @param array $conf Optional configuration
     * @return SimpleXMLElement Export generated XML
     */
    private function getXML($start = 0, $count = 20, $conf = array())
    {
        $dokuwikiXmlExport = new DokuwikiXMLExport($conf);
        return new SimpleXMLElement($dokuwikiXmlExport->generateXMLExport($start, $count));
    }

    /**
     * Remove all DokuWiki pages before each test
     */
    public function setUp()
    {
        $indexer = new Doku_Indexer();
        $pages = $indexer->getPages();
        foreach ($pages as $page) {
            // Saving a page with empty content will result in removing it.
            $this->savePages(array($page), '');
        }
    }

    //======================================================================
    // GENERAL PLUGIN TESTS
    //======================================================================

    /**
     * Simple test to make sure the plugin.info.txt is in correct format
     */
    public function test_plugininfo()
    {
        $file = __DIR__ . '/../plugin.info.txt';
        $this->assertFileExists($file);

        $info = confToHash($file);

        $this->assertArrayHasKey('base', $info);
        $this->assertArrayHasKey('author', $info);
        $this->assertArrayHasKey('email', $info);
        $this->assertArrayHasKey('date', $info);
        $this->assertArrayHasKey('name', $info);
        $this->assertArrayHasKey('desc', $info);
        $this->assertArrayHasKey('url', $info);

        $this->assertEquals('findologicxmlexport', $info['base']);
        $this->assertRegExp('/^https?:\/\//', $info['url']);
        $this->assertTrue(mail_isvalid($info['email']));
        $this->assertRegExp('/^\d\d\d\d-\d\d-\d\d$/', $info['date']);
        $this->assertTrue(false !== strtotime($info['date']));
    }

    /**
     * Test to ensure that every conf['...'] entry in conf/default.php has a corresponding meta['...'] entry in
     * conf/metadata.php.
     */
    public function test_plugin_conf()
    {
        $conf_file = __DIR__ . '/../conf/default.php';
        if (file_exists($conf_file)) {
            include($conf_file);
        }
        $meta_file = __DIR__ . '/../conf/metadata.php';
        if (file_exists($meta_file)) {
            include($meta_file);
        }

        $this->assertEquals(gettype($conf), gettype($meta), 'Both ' . DOKU_PLUGIN . 'findologicxmlexport/conf/default.php and ' . DOKU_PLUGIN . 'findologicxmlexport/conf/metadata.php have to exist and contain the same keys.');

        if (gettype($conf) != 'NULL' && gettype($meta) != 'NULL') {
            foreach ($conf as $key => $value) {
                $this->assertArrayHasKey($key, $meta, 'Key $meta[\'' . $key . '\'] missing in ' . DOKU_PLUGIN . 'findologicxmlexport/conf/metadata.php');
            }

            foreach ($meta as $key => $value) {
                $this->assertArrayHasKey($key, $conf, 'Key $conf[\'' . $key . '\'] missing in ' . DOKU_PLUGIN . 'findologicxmlexport/conf/default.php');
            }
        }

    }

    //======================================================================
    // XML RESPONSE TESTS
    //======================================================================

    /**
     * Test to ensure that XML is valid when DokuWiki has a certain amount of pages.
     *
     * @param $ids array An array with page ids (eg. start or wiki:syntax)
     * @dataProvider parameterProviderForXMLResponse
     */
    public function test_xml_response_is_valid($ids = array())
    {
        if ($ids) { // I also want to check for an empty DokuWiki
            $this->savePages(array($ids));
        }

        $xml = $this->getXML();

        $start = implode('', ($xml->xpath('/findologic/items/@start')));
        $count = implode('', ($xml->xpath('/findologic/items/@count')));
        $total = implode('', ($xml->xpath('/findologic/items/@total')));

        $expectedStart = 0;
        $expectedCount = count($ids);
        $expectedTotal = count($ids);

        $this->assertEquals($expectedStart, $start, 'Expected start value should match the requested start value.');
        $this->assertEquals($expectedCount, $count, 'Expected count value should match the requested count value.');
        $this->assertEquals($expectedTotal, $total, 'Expected total value should match the amount of total pages.');
    }

    public function parameterProviderForXMLResponse()
    {
        return [
            'no pages' => array(), // Empty DokuWiki
            'one page' => ['home'],
            'two pages' => ['home1', 'home2'],
            'ten pages' => ['page_1', 'page_2', 'page_3', 'page_4', 'page_5', 'page_6', 'page_7', 'page_8', 'page_9', 'page_10']
        ];
    }

    /**
     * Test to ensure that the XML response is set accordingly to the page data.
     */
    public function test_xml_response_is_valid_and_elements_are_set_accordingly_to_page_data()
    {
        $pageId = 'test123:test123:test123';
        $this->savePages(array($pageId));

        // Set title manually because you can't save it with the saveWikiText function.
        $pageTitle = 'test123';
        $pageMetaTitle = array('title' => $pageTitle);
        p_set_metadata($pageId, $pageMetaTitle);

        $xml = $this->getXML();

        $names = $xml->xpath('/findologic/items/item/names/name');
        $name = $names[0];

        $summaries = $xml->xpath('/findologic/items/item/summaries/summary');
        $summary = $summaries[0];

        $descriptions = $xml->xpath('/findologic/items/item/descriptions/description');
        $description = $descriptions[0];

        $ordernumbers = $xml->xpath('/findologic/items/item/allOrdernumbers/ordernumbers/ordernumber');
        $ordernumber = $ordernumbers[0];

        $urls = $xml->xpath('/findologic/items/item/urls/url');
        $url = $urls[0];

        $properties = $xml->xpath('/findologic/items/item/allProperties/properties/property');
        $propertyKey = (string)$properties[0]->key[0];
        $propertyValue = (string)$properties[0]->value[0];

        $attributes = $xml->xpath('/findologic/items/item/allAttributes/attributes/attribute');
        $attributeKey = (string)$attributes[0]->key[0];
        $attributeValue = (string)$attributes[0]->values[0]->value[0];

        $dateAddeds = $xml->xpath('/findologic/items/item/dateAddeds');
        $dateAdded = (string)$dateAddeds[0]->dateAdded[0];

        $expectedName = $pageTitle;
        $expectedSummary = $expectedDescription = self::PAGE_CONTENT_PLACEHOLDER;
        $expectedOrdernumber = $pageId;
        $expectedUrl = 'http://wiki.example.com/./doku.php?id=' . $pageId;
        $expectedPropertyKey = 'dummy';
        $expectedPropertyValue = 'dummy';
        $expectedAttributeKey = 'cat';
        $expectedAttributeValue = 'test123_test123_test123';
        // Format DateTime because the creation date is not known. It can vary.
        $pageMetadata = p_get_metadata($pageId);
        $pageCreated = new DateTime();
        $pageCreated->setTimestamp($pageMetadata["date"]["created"]);
        $expectedDateAdded = (string)$pageCreated->format(\DateTime::ATOM);

        $this->assertEquals($expectedName, $name, 'Expected name in XML should match the pages first title.');
        $this->assertEquals($expectedSummary, $summary, 'Expected summary in XML should match the pages content.');
        $this->assertEquals($expectedDescription, $description, 'Expected description in XML should match the pages content.');
        $this->assertEquals($expectedOrdernumber, $ordernumber, 'Expected ordernumber in XML should match the pages namespace.');
        $this->assertEquals($expectedUrl, $url, 'Expected url in XML should match the DokuWiki-URL and the pages namespace.');
        $this->assertEquals($expectedPropertyKey, $propertyKey, 'Expected property key in XML should match the dummy value.');
        $this->assertEquals($expectedPropertyValue, $propertyValue, 'Expected property value in XML should match the dummy value.');
        $this->assertEquals($expectedAttributeKey, $attributeKey, 'Expected attribute key in XML should match the category value.');
        $this->assertEquals($expectedAttributeValue, $attributeValue, 'Expected attribute value in XML should match the namespace formatted in a FINDOLOGIC proper format.');
        $this->assertEquals($expectedDateAdded, $dateAdded, 'Expected dateAdded value in XML should match the created date of the page. Value can vary.');
    }

    /**
     * Test to ensure that if no title is given, the XML name will be empty as well.
     */
    public function test_xml_response_is_valid_and_element_name_is_empty_when_page_has_no_title()
    {
        $this->savePages(array('test321:test321:test321'));

        $xml = $this->getXML();

        $names = $xml->xpath('/findologic/items/item/names/name');
        $name = (string)$names[0];

        $this->assertEmpty($name, 'Expected name in XML should be empty when page has no title.');
    }

    /**
     * Test to ensure that ordernumber and attribute categories in the XML are valid when DokuWiki has a namespace height of two.
     */
    public function test_xml_response_is_valid_and_elements_are_set_accordingly_to_page_data_when_namespace_height_is_two()
    {
        $this->savePages(array('test123:test123'));

        $xml = $this->getXML();

        $ordernumbers = $xml->xpath('/findologic/items/item/allOrdernumbers/ordernumbers/ordernumber');
        $ordernumber = $ordernumbers[0];

        $attributes = $xml->xpath('/findologic/items/item/allAttributes/attributes/attribute');
        $attributeKey = (string)$attributes[0]->key[0];
        $attributeValue = (string)$attributes[0]->values[0]->value[0];

        $expectedOrdernumber = 'test123:test123';
        $expectedAttributeKey = 'cat';
        $expectedAttributeValue = 'test123_test123';

        $this->assertEquals($expectedAttributeKey, $attributeKey, 'Expected attribute key in XML should match the category value when namespace height is two "cat".');
        $this->assertEquals($expectedAttributeValue, $attributeValue, 'Expected attribute value in XML should match the namespace formatted in a FINDOLOGIC proper format when namespace height is two "test123_test123".');
        $this->assertEquals($expectedOrdernumber, $ordernumber, 'Expected ordernumber in XML should match the pages namespace when namespace height is two "test123:test123".');
    }

    /**
     * Test to ensure that ordernumber and attribute categories in the XML are valid when DokuWiki has a namespace height of one.
     */
    public function test_xml_response_is_valid_and_elements_are_set_accordingly_to_page_data_when_namespace_height_is_one()
    {
        $this->savePages(array('test321'));

        $xml = $this->getXML();

        $ordernumbers = $xml->xpath('/findologic/items/item/allOrdernumbers/ordernumbers/ordernumber');
        $ordernumber = $ordernumbers[0];

        $attributes = $xml->xpath('/findologic/items/item/allAttributes/attributes/attribute');
        $attributeKey = (string)$attributes[0]->key[0];
        $attributeValue = (string)$attributes[0]->values[0]->value[0];

        $expectedOrdernumber = 'test321';
        $expectedAttributeKey = 'cat';
        $expectedAttributeValue = 'test321';

        $this->assertEquals($expectedAttributeKey, $attributeKey, 'Expected attribute key in XML should match the category value when namespace height is two "cat".');
        $this->assertEquals($expectedAttributeValue, $attributeValue, 'Expected attribute value in XML should match the namespace formatted in a FINDOLOGIC proper format when namespace height is two "test123_test123".');
        $this->assertEquals($expectedOrdernumber, $ordernumber, 'Expected ordernumber in XML should match the pages namespace when namespace height is two "test123:test123".');
    }

    public function test_xml_response_is_valid_when_calling_export_with_count_greater_than_total()
    {
        $pageId = array();
        for ($i = 1; $i <= 9; $i++) {
            $pageId[$i] = 'demopage0' . $i;
            $this->savePages(array($pageId[$i]));
        }

        $xml = $this->getXML();

        $start = implode('', ($xml->xpath('/findologic/items/@start')));
        $count = implode('', ($xml->xpath('/findologic/items/@count')));
        $total = implode('', ($xml->xpath('/findologic/items/@total')));

        $expectedStart = 0;
        $expectedCount = $expectedTotal = 9;

        $this->assertEquals($expectedStart, $start, 'Expected start value should match the requested start value.');
        $this->assertEquals($expectedCount, $count, 'Expected count value should match the requested count value.');
        $this->assertEquals($expectedTotal, $total, 'Expected total value should match the amount of total pages.');
    }

    //======================================================================
    // XML CALL TESTS
    //======================================================================

    /**
     * Test to ensure that parameters are correctly handled in the export call and
     * an exception is thrown if it does not work.
     *
     * @param integer $start Start value for Export call
     * @param integer $count Count value for Export call
     * @param string $message Error message if test fails
     * @dataProvider parameterProviderForXMLCall
     */
    public function test_xml_call_exception_is_thrown_when_calling_export_with_wrong_params($start, $count, $message)
    {
        try {
            $this->savePages(array('demopage1'));
            $xml = $this->getXML($start, $count);
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

    //======================================================================
    // PLUGIN CONFIGURATION TESTS
    //======================================================================

    /**
     * Test to ensure that the configuration works as expected and excluding of pages work properly.
     * Correct page needs to be exported when 2 of 3 pages are excluded.
     */
    public function test_plugin_conf_exclude_two_pages()
    {
        $pageIds = array('settingtest1337', 'excludeme1', 'excludeme2');
        $this->savePages($pageIds);

        $conf = array();
        // Set configuration
        $conf['plugin']['findologicxmlexport']['excludePages'] = 'excludeme1, excludeme2';

        $xml = $this->getXML(0, 20, $conf);

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

    /**
     * Test to ensure that the configuration works as expected and excluding of pages work properly. Only one page.
     * Correct page needs to be exported when 1 of 2 pages are excluded.
     */
    public function test_plugin_conf_exclude_one_page()
    {
        $pageIds = array('settingtest1337', 'excludeme3');
        $this->savePages($pageIds);

        $conf = array();
        $conf['plugin']['findologicxmlexport']['excludePages'] = 'excludeme3';

        $xml = $this->getXML(0, 20, $conf);

        $total = implode('', ($xml->xpath('/findologic/items/@total')));
        $items = $xml->xpath('//item');
        $itemCount = count($items);

        $ordernumbers = $xml->xpath('/findologic/items/item/allOrdernumbers/ordernumbers/ordernumber');
        $ordernumber = $ordernumbers[0];

        $expectedTotal = 1;
        $expectedOrdernumber = $pageIds[0];
        $expectedItems = 1;

        $this->assertEquals($expectedTotal, $total, 'Unexpected total when calling export with one out of two pages is excluded in the configuration.');
        $this->assertEquals($expectedOrdernumber, $ordernumber, 'Unexpected ordernumber when calling export with one out of two pages is excluded in the configuration.');
        $this->assertEquals($expectedItems, $itemCount, 'Unexpected count when calling export with one out of two pages is excluded in the configuration.');
    }
}
