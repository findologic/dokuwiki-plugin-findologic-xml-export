<?php
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
        $html = $dom->loadHTML($output);
        libxml_use_internal_errors($internalErrors);

        $header = $dom->getElementsByTagName('h1')[0];
        $header = $header->nodeValue;
        $expectedHeader = 'FINDOLOGIC XML Export Plugin';

        $legend = $dom->getElementsByTagName('legend')[0];
        $legend = $legend->nodeValue;
        $expectedLegend = 'Pages without title (0)';

        $this->assertEquals($expectedHeader, $header, 'Expected header should be equal to the translation set in the english translation file.');
        $this->assertEquals($expectedLegend, $legend, 'Expected legend should be equal to the translation set in the english translation file.');

    }

    public function test_response_has_one_element_when_one_page_has_no_title_set()
    {
        $pageHasNoTitle = ['ihavenotitle'];
        Helper::savePages($pageHasNoTitle);

        ob_start();

        $adminPlugin = new admin_plugin_findologicxmlexport();
        $adminPlugin->html();
        $output = ob_get_clean();
        $dom = new \DOMDocument('1.0', 'UTF-8');


        $internalErrors = libxml_use_internal_errors(true);
        $html = $dom->loadHTML($output);
        libxml_use_internal_errors($internalErrors);

        $header = $dom->getElementsByTagName('h1')[0];
        $header = $header->nodeValue;

        $expectedHeader = 'FINDOLOGIC XML Export Plugin';

        $legend = $dom->getElementsByTagName('legend')[0];
        $legend = $legend->nodeValue;

        $expectedLegend = 'Pages without title (1)';

        $this->assertEquals($expectedHeader, $header, 'Expected header should be equal to the translation set in the english translation file.');
        $this->assertEquals($expectedLegend, $legend, 'Expected legend should be equal to the translation set in the english translation file and should contain one item when one page has no title set.');
    }

    public function test_response_has_one_element_when_two_pages_has_no_title_set()
    {
        $pageHasNoTitle = ['ihavenotitle1', 'ihavenotitle2'];
        Helper::savePages($pageHasNoTitle);

        ob_start();

        $adminPlugin = new admin_plugin_findologicxmlexport();
        $adminPlugin->html();
        $output = ob_get_clean();
        $dom = new \DOMDocument('1.0', 'UTF-8');


        $internalErrors = libxml_use_internal_errors(true);
        $html = $dom->loadHTML($output);
        libxml_use_internal_errors($internalErrors);

        $header = $dom->getElementsByTagName('h1')[0];
        $header = $header->nodeValue;

        $expectedHeader = 'FINDOLOGIC XML Export Plugin';

        $legend = $dom->getElementsByTagName('legend')[0];
        $legend = $legend->nodeValue;

        $expectedLegend = 'Pages without title (2)';

        $this->assertEquals($expectedHeader, $header, 'Expected header should be equal to the translation set in the english translation file.');
        $this->assertEquals($expectedLegend, $legend, 'Expected legend should be equal to the translation set in the english translation file and should contain one item when one page has no title set.');
    }

    public function test_response_has_one_element_when_five_or_more_pages_has_no_title_set()
    {
        $pageHasNoTitle = ['noootitle0', 'noootitle1', 'noootitle2', 'noootitle3', 'noootitle4', 'noootitle5'];
        Helper::savePages($pageHasNoTitle);

        ob_start();

        $adminPlugin = new admin_plugin_findologicxmlexport();
        $adminPlugin->html();
        $output = ob_get_clean();
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $internalErrors = libxml_use_internal_errors(true);
        $html = $dom->loadHTML($output);
        libxml_use_internal_errors($internalErrors);

        $finder = new DomXPath($dom);

        $pageFields = ['page-id', 'page-url', 'page-author', 'page-last-edited'];

        foreach ($pageFields as $key => $pageField) {

            $nodes = $finder->query("//td[contains(@class, '$pageField')]");

            for ($i = 0; $i < 5; $i++) {
                $pageWithoutTitle[$pageField][] = trim($nodes->item($i)->nodeValue);
            }
        }

        // page-id
        $expectedPageWithoutTitle['page-id'][0] = 'noootitle0';
        $expectedPageWithoutTitle['page-id'][1] = 'noootitle1';
        $expectedPageWithoutTitle['page-id'][2] = 'noootitle2';
        $expectedPageWithoutTitle['page-id'][3] = 'noootitle3';
        $expectedPageWithoutTitle['page-id'][4] = 'noootitle4';

        // page-url
        $expectedPageWithoutTitle['page-url'][0] = 'http://wiki.example.com/./doku.php?id=noootitle0';
        $expectedPageWithoutTitle['page-url'][1] = 'http://wiki.example.com/./doku.php?id=noootitle1';
        $expectedPageWithoutTitle['page-url'][2] = 'http://wiki.example.com/./doku.php?id=noootitle2';
        $expectedPageWithoutTitle['page-url'][3] = 'http://wiki.example.com/./doku.php?id=noootitle3';
        $expectedPageWithoutTitle['page-url'][4] = 'http://wiki.example.com/./doku.php?id=noootitle4';

        // page-author
        $expectedPageWithoutTitle['page-author'][0] = '(external edit)';
        $expectedPageWithoutTitle['page-author'][1] = '(external edit)';
        $expectedPageWithoutTitle['page-author'][2] = '(external edit)';
        $expectedPageWithoutTitle['page-author'][3] = '(external edit)';
        $expectedPageWithoutTitle['page-author'][4] = '(external edit)';

        // page-last-edited
        // Can't be tested due to timing issues.

        $header = $dom->getElementsByTagName('h1')[0];
        $header = $header->nodeValue;

        $expectedHeader = 'FINDOLOGIC XML Export Plugin';

        $legend = $dom->getElementsByTagName('legend')[0];
        $legend = $legend->nodeValue;

        $expectedLegend = 'Pages without title (6)';

        $this->assertEquals($expectedHeader, $header, 'Expected header should be equal to the translation set in the english translation file.');
        $this->assertEquals($expectedLegend, $legend, 'Expected legend should be equal to the translation set in the english translation file and should contain one item when one page has no title set.');

        $this->assertEquals($expectedPageWithoutTitle['page-id'][0], $pageWithoutTitle['page-id'][0], 'Expected page-id does not match the template response.');
        $this->assertEquals($expectedPageWithoutTitle['page-id'][1], $pageWithoutTitle['page-id'][1], 'Expected page-id does not match the template response.');
        $this->assertEquals($expectedPageWithoutTitle['page-id'][2], $pageWithoutTitle['page-id'][2], 'Expected page-id does not match the template response.');
        $this->assertEquals($expectedPageWithoutTitle['page-id'][3], $pageWithoutTitle['page-id'][3], 'Expected page-id does not match the template response.');
        $this->assertEquals($expectedPageWithoutTitle['page-id'][4], $pageWithoutTitle['page-id'][4], 'Expected page-id does not match the template response.');

        $this->assertEquals($expectedPageWithoutTitle['page-url'][0], $pageWithoutTitle['page-url'][0], 'Expected page-id does not match the template response.');
        $this->assertEquals($expectedPageWithoutTitle['page-url'][1], $pageWithoutTitle['page-url'][1], 'Expected page-id does not match the template response.');
        $this->assertEquals($expectedPageWithoutTitle['page-url'][2], $pageWithoutTitle['page-url'][2], 'Expected page-id does not match the template response.');
        $this->assertEquals($expectedPageWithoutTitle['page-url'][3], $pageWithoutTitle['page-url'][3], 'Expected page-id does not match the template response.');
        $this->assertEquals($expectedPageWithoutTitle['page-url'][4], $pageWithoutTitle['page-url'][4], 'Expected page-id does not match the template response.');

        $this->assertEquals($expectedPageWithoutTitle['page-author'][0], $pageWithoutTitle['page-author'][0], 'Expected page-id does not match the template response.');
        $this->assertEquals($expectedPageWithoutTitle['page-author'][1], $pageWithoutTitle['page-author'][1], 'Expected page-id does not match the template response.');
        $this->assertEquals($expectedPageWithoutTitle['page-author'][2], $pageWithoutTitle['page-author'][2], 'Expected page-id does not match the template response.');
        $this->assertEquals($expectedPageWithoutTitle['page-author'][3], $pageWithoutTitle['page-author'][3], 'Expected page-id does not match the template response.');
        $this->assertEquals($expectedPageWithoutTitle['page-author'][4], $pageWithoutTitle['page-author'][4], 'Expected page-id does not match the template response.');

    }

    //TODO: MAKE TEMPLATE WORK PROPERLY

    //TODO: Test 0, 1, multiple, a lot.


}