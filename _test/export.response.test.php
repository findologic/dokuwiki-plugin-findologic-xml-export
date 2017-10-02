<?php
/**
 * General tests for the findologicxmlexport plugin
 *
 * @group plugin_findologicxmlexport
 * @group plugins
 */

//======================================================================
// EXPORT RESPONSE TESTS
//======================================================================

require_once(__DIR__ . '/../DokuwikiXMLExport.php');
require_once(__DIR__ . '/../admin.php');
require_once(__DIR__ . '/helper.php');

class export_response_test extends DokuWikiTest
{
    public function setUp()
    {
        $helper = new helper;
        $helper->setUp();
    }

    /**
     * @dataProvider parameterProviderForXMLResponse
     */
    public function test_parameters_start_count_and_total_are_valid_for_($ids = array())
    {
        $helper = new helper;
        if ($ids) { // I also want to check for an empty DokuWiki
            $helper->savePages(array($ids));
            $expectedCount = count($ids);
            $expectedTotal = count($ids);
        } else {
            $expectedCount = 0;
            $expectedTotal = 0;
        }
        $xml = $helper->getXML();
        $start = implode('', ($xml->xpath('/findologic/items/@start')));
        $count = implode('', ($xml->xpath('/findologic/items/@count')));
        $total = implode('', ($xml->xpath('/findologic/items/@total')));

        $expectedStart = 0;

        $this->assertEquals($expectedStart, $start, 'Expected start value should match the requested start value.');
        $this->assertEquals($expectedCount, $count, 'Expected count value should match the requested count value.');
        $this->assertEquals($expectedTotal, $total, 'Expected total value should match the amount of total pages.');
    }

    public function parameterProviderForXMLResponse()
    {
        return [
            'no pages' => [''], // Empty DokuWiki
            'one page' => ['home'],
            'two pages' => ['home1', 'home2'],
            'ten pages' => ['page_1', 'page_2', 'page_3', 'page_4', 'page_5', 'page_6', 'page_7', 'page_8', 'page_9', 'page_10']
        ];
    }

    public function test_parameters_start_count_and_total_are_valid_when_calling_export_with_count_greater_than_total()
    {
        $helper = new helper;
        $pageId = array();
        for ($i = 1; $i <= 9; $i++) {
            $pageId[$i] = 'demopage0' . $i;
            $helper->savePages(array($pageId[$i]));
        }
        $xml = $helper->getXML();
        $start = implode('', ($xml->xpath('/findologic/items/@start')));
        $count = implode('', ($xml->xpath('/findologic/items/@count')));
        $total = implode('', ($xml->xpath('/findologic/items/@total')));
        $expectedStart = 0;
        $expectedCount = $expectedTotal = 9;
        $this->assertEquals($expectedStart, $start, 'Expected start value should match the requested start value.');
        $this->assertEquals($expectedCount, $count, 'Expected count value should match the requested count value.');
        $this->assertEquals($expectedTotal, $total, 'Expected total value should match the amount of total pages.');
    }

    /**
     * Test for all XML elements.
     */
    public function test_all_elements_are_set_according_to_page_data()
    {
        $helper = new helper;
        $pageId = 'test123:test123:test123';
        $helper->savePages(array($pageId));
        // Set title manually because you can't save it with the saveWikiText function.
        $pageTitle = 'test123';
        $pageMetaTitle = array('title' => $pageTitle);
        p_set_metadata($pageId, $pageMetaTitle);
        $xml = $helper->getXML();
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
        $expectedSummary = $expectedDescription = helper::PAGE_CONTENT_PLACEHOLDER;
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
        $this->assertEquals($expectedName, $name, 'Expected name in XML should match the pages first title.');
        $this->assertEquals($expectedSummary, $summary, 'Expected summary in XML should match the pages content.');
        $this->assertEquals($expectedDescription, $description, 'Expected description in XML should match the pages content.');
        $this->assertEquals($expectedOrdernumber, $ordernumber, 'Expected ordernumber in XML should match the pages namespace.');
        $this->assertEquals($expectedUrl, $url, 'Expected url in XML should match the DokuWiki-URL and the pages namespace.');
        $this->assertEquals($expectedPropertyKey, $propertyKey, 'Expected property key in XML should match the dummy value.');
        $this->assertEquals($expectedPropertyValue, $propertyValue, 'Expected property value in XML should match the dummy value.');
        $this->assertEquals($expectedAttributeKey, $attributeKey, 'Expected attribute key in XML should match the category value.');
        $this->assertEquals($expectedAttributeValue, $attributeValue, 'Expected attribute value in XML should match the namespace formatted in a FINDOLOGIC proper format.');
        $this->assertEquals($expectedDateAdded, $dateAdded, 'Expected dateAdded value in XML should match the created date of the page. Value can vary.');
    }

    /**
     * @dataProvider parameterProviderForNamespaceDepthTesting
     */
    public function test_elements_ordernumber_and_category_are_set_according_to_page_data_when_namespace_depth_is_($amount, $ids)
    {
        $helper = new helper;
        $helper->savePages(array($ids));
        $xml = $helper->getXML();
        $ordernumbers = $xml->xpath('/findologic/items/item/allOrdernumbers/ordernumbers/ordernumber');
        $ordernumber = $ordernumbers[0];
        $attributes = $xml->xpath('/findologic/items/item/allAttributes/attributes/attribute');
        $attributeKey = (string)$attributes[0]->key[0];
        $attributeValue = (string)$attributes[0]->values[0]->value[0];

        switch ($amount) {
            case 1:
                $expectedOrdernumber = 'home';
                $expectedAttributeValue = 'home';
                break;
            case 2:
                $expectedOrdernumber = 'home:home';
                $expectedAttributeValue = 'home_home';
                break;
            case 3:
                $expectedOrdernumber = 'home:home:home';
                $expectedAttributeValue = 'home_home_home';
                break;
            default:
                $this->fail('Expected amount of pages is not correct.');
        }

        $expectedAttributeKey = 'cat';
        $this->assertEquals($expectedAttributeKey, $attributeKey, 'Expected attribute key in XML should always match "cat".');
        $this->assertEquals($expectedAttributeValue, $attributeValue, 'Expected attribute value in XML should match the namespace formatted in a FINDOLOGIC proper format.');
        $this->assertEquals($expectedOrdernumber, $ordernumber, 'Expected ordernumber in XML should match the pages namespace.');
    }

    public function parameterProviderForNamespaceDepthTesting()
    {
        return [
            'one' => [1, 'home'],
            'two' => [2, 'home:home'],
            'three' => [3, 'home:home:home']
        ];
    }

    public function test_element_name_is_empty_when_page_has_no_title()
    {
        $helper = new helper;
        $helper->savePages(array('test321:test321:test321'));
        $xml = $helper->getXML();
        $names = $xml->xpath('/findologic/items/item/names/name');
        $name = (string)$names[0];
        $this->assertEmpty($name, 'Expected name in XML should be empty when page has no title.');
    }
}