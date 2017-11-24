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
        //TODO TESTS:
        //TODO 0: Empty Wiki; Wiki where all pages have a title.
        //TODO 1: The test covers this already.
        //TODO Multiple: Multiple pages without a title.
        //TODO A lot: What if you have 10.000.000 pages without a title? Does this work? How does your UI handle this?


        // Create one page that has a title and one that doesn't
        /*$pageHasTitle = 'ihaveatitle';
        $pageHasNoTitle = 'ihavenotitle';
        $allPages = [$pageHasNoTitle, $pageHasTitle];
        Helper::savePages($allPages);
        $pageMetaTitle = ['title' => $pageHasTitle];
        p_set_metadata($pageHasTitle, $pageMetaTitle);

        // Get all pages that do have no title set
        $allPagesWithoutTitle = PageGetter::getPagesWithoutTitle();

        $this->assertEquals([$pageHasNoTitle], $allPagesWithoutTitle, 'All pages that have no title should be returned.');*/
    }
}