<?php
/**
 * General tests for the findologicxmlexport plugin
 *
 * @group plugin_findologicxmlexport
 * @group plugins
 */

require_once(__DIR__ . '/../DokuwikiXMLExport.php');
require_once(__DIR__ . '/../DokuwikiPage.php');
require_once(__DIR__ . '/../admin.php');
require_once(__DIR__ . '/../_test/Helper.php');

class dokuwikipage_test extends DokuWikiTest
{
    public function setUp()
    {
        Helper::setUp();
    }

    /**
     * Test to ensure that a page object contains correct data when saving a page.
     */
    public function test_dokuwikipage_creation_returns_a_valid_page() {
        $pageId = 'validpage';
        Helper::savePages([$pageId]);
        $page = new DokuwikiPage($pageId);

        $this->assertEquals($pageId, $page->id, 'Expected pageId should match the id of the page.');
        $this->assertEquals('http://wiki.example.com/./doku.php?id=validpage', $page->url, 'Expected url should match the DokuWiki url with the pageId.');
        $this->assertEquals('(external edit)', $page->author, 'The author should match the editor of the page of if it was an external edit the placeholder for an external edit.');
        $this->assertEquals(p_get_metadata($pageId), $page->metadata, 'Expected metadata should match the metadata of this page.');
    }
}