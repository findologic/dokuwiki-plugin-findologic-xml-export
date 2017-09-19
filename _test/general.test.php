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
        saveWikiText('home', '==== Home ==== Some demo text.', 'Some minor changes were made to this page.', true);
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
}
