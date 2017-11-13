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
        $xml = new SimpleXMLElement($output);

        $header = (string)$xml->xpath('h1')[0];
        $expectedHeader = 'FINDOLOGIC XML Export Plugin';

        $successInformation = (string)$xml->xpath('fieldset/div')[0];
        $expectedSuccessInformation = '
            All pages do have a title set! Nothing needs to be changed.
        ';

        $legend = (string)$xml->xpath('fieldset/legend')[0];
        $expectedLegend = 'Pages without title (0)';

        $this->assertEquals($expectedHeader, $header, 'Expected header should be equal to the translation set in the english translation file.');
        $this->assertEquals($expectedSuccessInformation, $successInformation, 'Expected success information should be equal to the translation set in the english translation file.');
        $this->assertEquals($expectedLegend, $legend, 'Expected legend should be equal to the translation set in the english translation file.');

    }

    public function test_response_has_one_element_when_one_page_has_no_title_set()
    {
        $pageHasNoTitle = 'ihavenotitle';
        Helper::savePages([$pageHasNoTitle]);

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

}