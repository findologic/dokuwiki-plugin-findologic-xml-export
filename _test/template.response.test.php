<?php
/**
 * General tests for the findologicxmlexport plugin
 *
 * @group plugin_findologicxmlexport
 * @group plugins
 */

//======================================================================
// TEMPLATE RESPONSE TESTS
//======================================================================

require_once(__DIR__ . '/../DokuwikiXMLExport.php');
require_once(__DIR__ . '/../admin.php');
require_once(__DIR__ . '/../_test/Helper.php');

class template_response_test extends DokuWikiTest
{
    public function setUp()
    {
        Helper::setUp();
    }

    /**
     * This function basically saves a page with a title and checks the response
     * in admin view of the plugin.
     */
    public function test_response_has_translation_set_in_language_file()
    {
        $pageHasTitle = 'ihaveatitle';
        Helper::savePages([$pageHasTitle]);
        $pageMetaTitle = ['title' => $pageHasTitle];
        p_set_metadata($pageHasTitle, $pageMetaTitle);

        // Output Buffer
        ob_start();

        // Call html() function from the plugin
        $adminPlugin = new admin_plugin_findologicxmlexport();
        $adminPlugin->html();
        $output = ob_get_clean();
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($output);
        libxml_use_internal_errors($internalErrors);

        $header = $dom->getElementsByTagName('h1')[0];
        $header = $header->nodeValue;
        $expectedHeader = 'FINDOLOGIC XML Export Plugin';

        $legend = $dom->getElementsByTagName('legend')[0];
        $legend = trim($legend->nodeValue);
        $expectedLegend = 'Pages without title (0)';

        $this->assertEquals($expectedHeader, $header, 'Expected header should be equal to the translation set in the english translation file.');
        $this->assertEquals($expectedLegend, $legend, 'Expected legend should be equal to the translation set in the english translation file.');

    }

    public function test_response_has_one_element_when_one_page_has_no_title_set()
    {
        $pageHasNoTitle = ['ihavenotitle'];
        Helper::savePages($pageHasNoTitle, false);

        ob_start();

        $adminPlugin = new admin_plugin_findologicxmlexport();
        $adminPlugin->html();
        $output = ob_get_clean();
        $dom = new \DOMDocument('1.0', 'UTF-8');


        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($output);
        libxml_use_internal_errors($internalErrors);

        $header = $dom->getElementsByTagName('h1')[0];
        $header = $header->nodeValue;

        $expectedHeader = 'FINDOLOGIC XML Export Plugin';

        $legend = $dom->getElementsByTagName('legend')[0];
        $legend = trim($legend->nodeValue);

        $expectedLegend = 'Pages without title (1)';

        $this->assertEquals($expectedHeader, $header, 'Expected header should be equal to the translation set in the english translation file.');
        $this->assertEquals($expectedLegend, $legend, 'Expected legend should be equal to the translation set in the english translation file and should contain one item when one page has no title set.');
    }

    public function test_response_has_one_element_when_two_pages_has_no_title_set()
    {
        $pageHasNoTitle = ['ihavenotitle1', 'ihavenotitle2'];
        Helper::savePages($pageHasNoTitle, false);

        ob_start();

        $adminPlugin = new admin_plugin_findologicxmlexport();
        $adminPlugin->html();
        $output = ob_get_clean();
        $dom = new \DOMDocument('1.0', 'UTF-8');


        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($output);
        libxml_use_internal_errors($internalErrors);

        $header = $dom->getElementsByTagName('h1')[0];
        $header = $header->nodeValue;

        $expectedHeader = 'FINDOLOGIC XML Export Plugin';

        $legend = $dom->getElementsByTagName('legend')[0];
        $legend = trim($legend->nodeValue);

        $expectedLegend = 'Pages without title (2)';

        $this->assertEquals($expectedHeader, $header, 'Expected header should be equal to the translation set in the english translation file.');
        $this->assertEquals($expectedLegend, $legend, 'Expected legend should be equal to the translation set in the english translation file and should contain one item when one page has no title set.');
    }

    public function test_response_have_multiple_elements_when_six_or_more_pages_has_no_title_set()
    {
        // Save pages
        $pagesHaveNoTitle = ['noootitle324', 'noootitle1784', 'noootitle1203', 'noootitle356', 'noootitle1337', 'noootitle1338'];
        foreach ($pagesHaveNoTitle as $pageHasNoTitle) {
            Helper::savePages([$pageHasNoTitle], false);
        }

        ob_start();
        $adminPlugin = new admin_plugin_findologicxmlexport();
        $adminPlugin->html();
        $output = ob_get_clean();
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($output);
        libxml_use_internal_errors($internalErrors);

        $finder = new DomXPath($dom);

        $pageFields = ['page-id', 'page-url', 'page-author', 'page-last-edited'];

        foreach ($pageFields as $key => $pageField) {

            $nodes = $finder->query("//td[contains(@class, '$pageField')]");

            for ($i = 0; $i < 5; $i++) {
                $pageWithoutTitle[$pageField][] = trim($nodes->item($i)->nodeValue);
                sort($pageWithoutTitle[$pageField]);
            }
        }

        // page-id
        $expectedPageWithoutTitle['page-id'] = [
            'noootitle1338',
            'noootitle1337',
            'noootitle356',
            'noootitle1203',
            'noootitle1784'
        ];
        sort($expectedPageWithoutTitle['page-id']);

        // page-url
        $expectedPageWithoutTitle['page-url'] = [
            'http://wiki.example.com/./doku.php?id=noootitle1338',
            'http://wiki.example.com/./doku.php?id=noootitle1337',
            'http://wiki.example.com/./doku.php?id=noootitle356',
            'http://wiki.example.com/./doku.php?id=noootitle1203',
            'http://wiki.example.com/./doku.php?id=noootitle1784'
        ];
        sort($expectedPageWithoutTitle['page-url']);

        // page-author
        $expectedPageWithoutTitle['page-author'] = [
            '(external edit)',
            '(external edit)',
            '(external edit)',
            '(external edit)',
            '(external edit)'
        ];
        sort($expectedPageWithoutTitle['page-author']);

        // page-last-edited
        // Can't be tested due to timing issues.

        $header = $dom->getElementsByTagName('h1')[0];
        $header = $header->nodeValue;

        $expectedHeader = 'FINDOLOGIC XML Export Plugin';

        $legend = $dom->getElementsByTagName('legend')[0];
        $legend = trim($legend->nodeValue);

        $expectedLegend = 'Pages without title (6)';

        $notifyMorePagesNodes = $finder->query("//*[contains(@class, 'fl-notify')]");
        $notifyMorePages = trim($notifyMorePagesNodes->item(0)->nodeValue);

        $expectedNotifyMorePages = 'There is/are 1 more page(s) that do not have a title.';

        $this->assertEquals($expectedHeader, $header, 'Expected header should be equal to the translation set in the english translation file.');
        $this->assertEquals($expectedLegend, $legend, 'Expected legend should be equal to the translation set in the english translation file and should contain one item when one page has no title set.');
        $this->assertEquals($expectedNotifyMorePages, $notifyMorePages, 'Expected Notification message when pages are higher then the maximum amount of pages does not match the translation or the amount of returned pages.');

        $this->assertEqualsArrays($expectedPageWithoutTitle['page-id'], $pageWithoutTitle['page-id'], 'Expected page-id does not match the template response.');
        $this->assertEqualsArrays($expectedPageWithoutTitle['page-url'], $pageWithoutTitle['page-url'], 'Expected page-url does not match the template response.');
        $this->assertEqualsArrays($expectedPageWithoutTitle['page-author'], $pageWithoutTitle['page-author'], 'Expected page-author does not match the template response.');
    }

    public function test_response_has_no_notify_message_when_five_or_less_pages_have_no_title_set() {
        $pageHasNoTitle = ['noootitle0', 'noootitle1', 'noootitle2', 'noootitle3', 'noootitle4'];
        Helper::savePages($pageHasNoTitle);

        ob_start();

        $adminPlugin = new admin_plugin_findologicxmlexport();
        $adminPlugin->html();
        $output = ob_get_clean();
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($output);
        libxml_use_internal_errors($internalErrors);

        $finder = new DomXPath($dom);

        $notifyMorePagesNodes = $finder->query("//*[contains(@class, 'fl-notify')]");
        $notifyMorePages = trim($notifyMorePagesNodes->item(0)->nodeValue);

        $this->assertEmpty($notifyMorePages, 'Expected Notification message should not exist when four or less pages have no title set.');

    }

    public function test_response_with_multiple_pages_saved_in_different_times()
    {
        Helper::savePages(['times13213', 'times24124'], false);

        ob_start();

        $adminPlugin = new admin_plugin_findologicxmlexport();
        $adminPlugin->html();
        $output = ob_get_clean();
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($output);
        libxml_use_internal_errors($internalErrors);

        $finder = new DomXPath($dom);

        $pageFields = ['page-id', 'page-url', 'page-author', 'page-last-edited'];

        foreach ($pageFields as $key => $pageField) {

            $nodes = $finder->query("//td[contains(@class, '$pageField')]");

            for ($i = 0; $i < 5; $i++) {
                $pageWithoutTitle[$pageField][] = trim($nodes->item($i)->nodeValue);
                sort($pageWithoutTitle[$pageField]);
            }
        }

        // page-id
        $expectedPageWithoutTitle['page-id'] = [
            'times13213',
            'times24124'
        ];
        sort($expectedPageWithoutTitle['page-id']);

        // page-url
        $expectedPageWithoutTitle['page-url'] = [
            'http://wiki.example.com/./doku.php?id=times13213',
            'http://wiki.example.com/./doku.php?id=times24124'
        ];
        sort($expectedPageWithoutTitle['page-url']);

        // page-author
        $expectedPageWithoutTitle['page-author'] = [
            '(external edit)',
            '(external edit)'
        ];
        sort($expectedPageWithoutTitle['page-author']);

        $legend = $dom->getElementsByTagName('legend')[0];
        $legend = trim($legend->nodeValue);

        $expectedLegend = 'Pages without title (2)';

        $this->assertEqualsArrays($expectedPageWithoutTitle['page-id'], $pageWithoutTitle['page-id'], 'Expected page-id does not match the template response.');
        $this->assertEqualsArrays($expectedPageWithoutTitle['page-url'], $pageWithoutTitle['page-url'], 'Expected page-url does not match the template response.');
        $this->assertEqualsArrays($expectedPageWithoutTitle['page-author'], $pageWithoutTitle['page-author'], 'Expected page-author does not match the template response.');

        $this->assertEquals($expectedLegend, $legend, 'Expected legend should be equal to the translation set in the english translation file and should contain two items when two pages have no title set.');

    }

    public function test_response_has_notify_message_when_five_hundred_pages_have_no_title() {
        for ($i = 0; $i < 500; $i++){
            Helper::savePages(['fivehundredpages' . $i], false);
        }
        ob_start();

        $adminPlugin = new admin_plugin_findologicxmlexport();
        $adminPlugin->html();
        $output = ob_get_clean();
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($output);
        libxml_use_internal_errors($internalErrors);

        $finder = new DomXPath($dom);

        $notifyMorePagesNodes = $finder->query("//*[contains(@class, 'fl-notify')]");
        $notifyMorePages = trim($notifyMorePagesNodes->item(0)->nodeValue);

        $expectedNotifyMorePages = 'There is/are 495 more page(s) that do not have a title.';

        $this->assertEquals($expectedNotifyMorePages, $notifyMorePages, 'Expected Notification message when pages are higher then the maximum amount of pages does not match.');
    }

    public function test_plugin_is_sorted_at_the_top() {
        $adminPlugin = new admin_plugin_findologicxmlexport();
        $expectedSortValue = 1;
        $this->assertEquals($expectedSortValue, $adminPlugin->getMenuSort());
    }

    public function test_plugin_not_requires_to_be_superuser() {
        $adminPlugin = new admin_plugin_findologicxmlexport();
        $adminCanAccessAswell = true;
        $this->assertEquals($adminCanAccessAswell, $adminPlugin->forAdminOnly());
    }

    /**
     * Asserts that two arrays are equal.
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    protected function assertEqualsArrays($expected, $actual, $message) {
        $this->assertTrue(count($expected) == count(array_intersect($expected, $actual)), $message);
    }

}