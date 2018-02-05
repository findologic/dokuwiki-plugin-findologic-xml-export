<?php
/**
 * General tests for the findologicxmlexport plugin
 *
 * @group plugin_findologicxmlexport
 * @group plugins
 */

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

    /**
     * Test one page with and one without title.
     */
    public function test_get_pages_without_title_only_gets_pages_without_title()
    {
        // Create one page that has a title and one that doesn't
        $pageHasTitle = 'ihaveatitle';
        Helper::savePages([$pageHasTitle]);
        $pageHasNoTitle = 'ihavenotitle';
        Helper::savePages([$pageHasNoTitle], false);

        // Get all pages that do have no title set
        $allPagesWithoutTitle = PageGetter::getPagesWithoutTitle();

        $id = $allPagesWithoutTitle[0]->id;
        $url = $allPagesWithoutTitle[0]->url;
        $author = $allPagesWithoutTitle[0]->author;

        $expectedId = 'ihavenotitle';
        $expectedUrl = 'http://wiki.example.com/./doku.php?id=ihavenotitle';
        $expectedAuthor = '(external edit)';


        $this->assertEquals($expectedId, $id, 'Expected id is not the same as the saved title.');
        $this->assertEquals($expectedUrl, $url, 'Expected url does not match the url of the dokuwiki and the namespace of the dokuwiki page.');
        $this->assertEquals($expectedAuthor, $author, 'Expected author does not match an edit by the server.');
    }

    public function test_get_pages_without_title_gets_pages_sorted_by_time()
    {
        $pagesHaveNoTitle = ['page0', 'page1', 'page2'];
        foreach ($pagesHaveNoTitle as $pageHasNoTitle) {
            Helper::savePages([$pageHasNoTitle], false);
            sleep(1);
        }

        $allPagesWithoutTitle = PageGetter::getPagesWithoutTitle();

        $id0 = $allPagesWithoutTitle[0]->id;
        $url0 = $allPagesWithoutTitle[0]->url;
        $author0 = $allPagesWithoutTitle[0]->author;

        $id1 = $allPagesWithoutTitle[1]->id;
        $url1 = $allPagesWithoutTitle[1]->url;
        $author1 = $allPagesWithoutTitle[1]->author;

        $id2 = $allPagesWithoutTitle[2]->id;
        $url2 = $allPagesWithoutTitle[2]->url;
        $author2 = $allPagesWithoutTitle[2]->author;

        $expectedId0 = 'page2';
        $expectedUrl0 = 'http://wiki.example.com/./doku.php?id=page2';
        $expectedAuthor0 = '(external edit)';

        $expectedId1 = 'page1';
        $expectedUrl1 = 'http://wiki.example.com/./doku.php?id=page1';
        $expectedAuthor1 = '(external edit)';

        $expectedId2 = 'page0';
        $expectedUrl2 = 'http://wiki.example.com/./doku.php?id=page0';
        $expectedAuthor2 = '(external edit)';

        $this->assertEquals($expectedId0, $id0, 'Expected id is not the same as the saved title.');
        $this->assertEquals($expectedUrl0, $url0, 'Expected url does not match the url of the dokuwiki and the namespace of the dokuwiki page.');
        $this->assertEquals($expectedAuthor0, $author0, 'Expected author does not match an edit by the server.');

        $this->assertEquals($expectedId1, $id1, 'Expected id is not the same as the saved title.');
        $this->assertEquals($expectedUrl1, $url1, 'Expected url does not match the url of the dokuwiki and the namespace of the dokuwiki page.');
        $this->assertEquals($expectedAuthor1, $author1, 'Expected author does not match an edit by the server.');

        $this->assertEquals($expectedId2, $id2, 'Expected id is not the same as the saved title.');
        $this->assertEquals($expectedUrl2, $url2, 'Expected url does not match the url of the dokuwiki and the namespace of the dokuwiki page.');
        $this->assertEquals($expectedAuthor2, $author2, 'Expected author does not match an edit by the server.');

    }
}