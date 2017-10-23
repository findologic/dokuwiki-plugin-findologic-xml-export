<?php
//======================================================================
// PAGEGETTER TESTS
//======================================================================

require_once(__DIR__ . '/../DokuwikiXMLExport.php');
require_once(__DIR__ . '/../admin.php');
require_once(__DIR__ . '/../_test/Helper.php');
require_once(__DIR__ . '/../PageGetter.php');

class page_getter_test extends DokuWikiTest
{
    public function setUp()
    {
        Helper::setUp();
    }
    public function test_get_pages_without_title_only_gets_pages_without_title()
    {
        // Create one page that has a title and one that doesn't
        $pageHasTitle = 'ihaveatitle';
        $pageHasNoTitle = 'ihavenotitle';
        $allPages = [$pageHasNoTitle, $pageHasTitle];
        Helper::savePages($allPages);
        $pageMetaTitle = array('title' => $pageHasTitle);
        p_set_metadata($pageHasTitle, $pageMetaTitle);

        // Get all pages that do have no title set
        $allPagesWithoutTitle = PageGetter::getPagesWithoutTitle();

        $this->assertEquals([$pageHasNoTitle], $allPagesWithoutTitle, 'All pages that have no title should be returned.');
    }
}