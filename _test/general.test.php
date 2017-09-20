<?php
/**
 * General tests for the findologicxmlexport plugin
 *
 * @group plugin_findologicxmlexport
 * @group plugins
 */

require_once (__DIR__ . '/../DokuwikiXMLExport.php');
require_once (__DIR__ . '/../admin.php');
class general_plugin_findologicxmlexport_test extends DokuWikiTest {

    /**
     * Remove all DokuWiki pages before each test
     */
    public function setUp(){
        $indexer = new Doku_Indexer();
        $indexer->clear();
    }

    /**
     * Simple test to make sure the plugin.info.txt is in correct format
     */
    public function test_plugininfo() {
        $file = __DIR__.'/../plugin.info.txt';
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
    public function test_plugin_conf() {
        $conf_file = __DIR__.'/../conf/default.php';
        if (file_exists($conf_file)){
            include($conf_file);
        }
        $meta_file = __DIR__.'/../conf/metadata.php';
        if (file_exists($meta_file)) {
            include($meta_file);
        }

        $this->assertEquals(gettype($conf), gettype($meta),'Both ' . DOKU_PLUGIN . 'findologicxmlexport/conf/default.php and ' . DOKU_PLUGIN . 'findologicxmlexport/conf/metadata.php have to exist and contain the same keys.');

        if (gettype($conf) != 'NULL' && gettype($meta) != 'NULL') {
            foreach($conf as $key => $value) {
                $this->assertArrayHasKey($key, $meta, 'Key $meta[\'' . $key . '\'] missing in ' . DOKU_PLUGIN . 'findologicxmlexport/conf/metadata.php');
            }

            foreach($meta as $key => $value) {
                $this->assertArrayHasKey($key, $conf, 'Key $conf[\'' . $key . '\'] missing in ' . DOKU_PLUGIN . 'findologicxmlexport/conf/default.php');
            }
        }

    }

    /**
     * Test to ensure that XML is valid when DokuWiki has no pages.
     */
    public function test_xml_response_is_valid_if_dokuwiki_has_no_pages() {
        $conf = array();
        $DokuwikiXMLExport = new DokuwikiXMLExport($conf);
        $xml = new SimpleXMLElement($DokuwikiXMLExport->generateXMLExport(0, 20));

        $start = implode('', ($xml->xpath('/findologic/items/@start')));
        $count = implode('', ($xml->xpath('/findologic/items/@count')));
        $total = implode('', ($xml->xpath('/findologic/items/@total')));

        $expected = 0;
        $this->assertEquals($expected, $start, 'Expected start value should match "0" when DokuWiki has no pages.');
        $this->assertEquals($expected, $count, 'Expected count value should match "0" when DokuWiki has no pages.');
        $this->assertEquals($expected, $total, 'Expected total value should match "0" when DokuWiki has no pages.');
    }

    /**
     * Test to ensure that XML is valid when DokuWiki has one page.
     */
    public function test_xml_response_is_valid_if_dokuwiki_has_one_page() {
        saveWikiText('home', '==== Home ==== Some demo text.', '');
        idx_addPage('home', '', '');

        $conf = array();
        $DokuwikiXMLExport = new DokuwikiXMLExport($conf);
        $xml = new SimpleXMLElement($DokuwikiXMLExport->generateXMLExport(0, 20));

        $start = implode('', ($xml->xpath('/findologic/items/@start')));
        $count = implode('', ($xml->xpath('/findologic/items/@count')));
        $total = implode('', ($xml->xpath('/findologic/items/@total')));

        $expectedStart = 0;
        $expectedCount = 1;
        $expectedTotal = 1;

        $this->assertEquals($expectedStart, $start, 'Expected start value should match "0" when DokuWiki has one page.');
        $this->assertEquals($expectedCount, $count, 'Expected count value should match "1" when DokuWiki has one page.');
        $this->assertEquals($expectedTotal, $total, 'Expected total value should match "1" when DokuWiki has one page.');
    }

    /**
     * Test to ensure that XML is valid when DokuWiki has two pages.
     */
    public function test_xml_response_is_valid_if_dokuwiki_has_two_pages() {
        $pageId1 = 'home1';
        $pageId2 = 'home2';
        saveWikiText($pageId1, '==== Home1 ==== Some demo text.', '');
        saveWikiText($pageId2, '==== Home2 ==== Some demo text.', '');

        idx_addPage($pageId1, '', '');
        idx_addPage($pageId2, '', '');

        $conf = array();
        $DokuwikiXMLExport = new DokuwikiXMLExport($conf);
        $xml = new SimpleXMLElement($DokuwikiXMLExport->generateXMLExport(0, 20));

        $start = implode('', ($xml->xpath('/findologic/items/@start')));
        $count = implode('', ($xml->xpath('/findologic/items/@count')));
        $total = implode('', ($xml->xpath('/findologic/items/@total')));

        $expectedStart = 0;
        $expectedCount = 2;
        $expectedTotal = 2;

        $this->assertEquals($expectedStart, $start, 'Expected start value should match "0" when DokuWiki has two pages.');
        $this->assertEquals($expectedCount, $count, 'Expected count value should match "2" when DokuWiki has two pages.');
        $this->assertEquals($expectedTotal, $total, 'Expected total value should match "2" when DokuWiki has two pages.');
    }
    public function test_xml_elements_equal_to_dokuwiki_page_data() {
        $pageId = 'test123:test123:test123';
        $pageTitle = 'test123';
        $pageContent = 'This page is for test purposes.';
        saveWikiText($pageId, $pageContent, '');
        idx_addPage($pageId, '', '');

        // Set title manually because you cant save it with the saveWikiText function.
        $pageMetaTitle = array('title' => $pageTitle);
        p_set_metadata($pageId, $pageMetaTitle);

        $conf = array();
        $DokuwikiXMLExport = new DokuwikiXMLExport($conf);
        $xml = new SimpleXMLElement($DokuwikiXMLExport->generateXMLExport(0, 20));

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
        $expectedSummary = $expectedDescription = $pageContent;
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

        $this->assertEquals($expectedName, $name, 'Expected name in XML should match the pages first title "test123".');
        $this->assertEquals($expectedSummary, $summary, 'Expected summary in XML should match the pages content "This page is for test purposes.".');
        $this->assertEquals($expectedDescription, $description, 'Expected description in XML should match the pages content "This page is for test purposes.".');
        $this->assertEquals($expectedOrdernumber, $ordernumber, 'Expected ordernumber in XML should match the pages namespace "test123:test123:test123".');
        $this->assertEquals($expectedUrl, $url, 'Expected url in XML should match the DokuWiki-URL and the pages namespace "http://wiki.example.com/./doku.php?id=test123:test123:test123".');
        $this->assertEquals($expectedPropertyKey, $propertyKey, 'Expected property key in XML should match the dummy value "dummy".');
        $this->assertEquals($expectedPropertyValue, $propertyValue, 'Expected property value in XML should match the dummy value "dummy".');
        $this->assertEquals($expectedAttributeKey, $attributeKey, 'Expected attribute key in XML should match the category value "cat".');
        $this->assertEquals($expectedAttributeValue, $attributeValue, 'Expected attribute value in XML should match the namespace formatted in a FINDOLOGIC proper format "test123_test123_test123".');
        $this->assertEquals($expectedDateAdded, $dateAdded, 'Expected dateAdded value in XML should match the created date of the page. Value can vary.');
    }
}
