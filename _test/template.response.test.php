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
    public function test_response_has_translation_set_in_language_file_en()
    {
        // Save a page with a title
        $pageHasTitle = 'ihaveatitle';
        Helper::savePages([$pageHasTitle]);
        $pageMetaTitle = array('title' => $pageHasTitle);
        p_set_metadata($pageHasTitle, $pageMetaTitle);

        // Output Buffer
        ob_start();

        // Call html() function to generate output
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
        // Save a page with a title
        $pageHasNoTitle = 'ihavenotitle';
        Helper::savePages([$pageHasNoTitle]);

        // Output Buffer
        ob_start();

        // Call html() function to generate output
        $adminPlugin = new admin_plugin_findologicxmlexport();
        $adminPlugin->html();
        $output = ob_get_clean();
        print_r($output);

        $dom = new DOMDocument();

        $xml = $dom->loadHTML($output);
        print_r($xml);
        //$xml = new SimpleXMLElement($output);


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
}