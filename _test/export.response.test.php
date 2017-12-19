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
require_once(__DIR__ . '/../_test/Helper.php');

class export_response_test extends DokuWikiTest
{
    public function setUp()
    {
        Helper::setUp();
    }

    /**
     * @dataProvider parameterProviderForXMLResponse
     */
    public function test_parameters_start_count_and_total_are_valid_for_($ids = [], $expectedCount, $expectedTotal)
    {
        // If there are no pages, no pages can be saved.
        Helper::savePages($ids);

        $xml = Helper::getXML();
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
            // name => [[ids], count, total]
            'no pages' => [[''], 0, 0], // Empty DokuWiki
            'one page' => [['home'], 1, 1],
            'two pages' => [['home1', 'home2'], 2, 2],
            'ten pages' => [['page_1', 'page_2', 'page_3', 'page_4', 'page_5', 'page_6', 'page_7', 'page_8', 'page_9', 'page_10'], 10, 10]
        ];
    }

    public function test_parameters_start_count_and_total_are_valid_when_calling_export_with_count_greater_than_total()
    {
        $pageId = [];
        for ($i = 1; $i <= 9; $i++) {
            $pageId[$i] = 'demopage0' . $i;
        }
        Helper::savePages($pageId);
        $xml = Helper::getXML();
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
        $pageId = 'test123:test123:test123';
        Helper::savePages([$pageId]);
        // Set title manually because you can't save it with the saveWikiText function.
        $pageTitle = 'test123';
        $pageMetaTitle = ['title' => $pageTitle];
        p_set_metadata($pageId, $pageMetaTitle);
        $xml = Helper::getXML();
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
        $expectedSummary = $expectedDescription = Helper::PAGE_CONTENT_PLACEHOLDER;
        $expectedOrdernumber = $pageId;
        $expectedUrl = 'http://wiki.example.com/./doku.php?id=' . $pageId;
        $expectedPropertyKey = 'dummy';
        $expectedPropertyValue = 'dummy';
        $expectedAttributeKey = 'cat';
        $expectedAttributeValue = 'Test123_Test123_Test123';
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
    public function test_elements_ordernumber_and_category_are_set_according_to_page_data_when_namespace_depth_is_($id, $expectedAttributeValue)
    {
        Helper::savePages([$id]);
        $xml = Helper::getXML();
        $ordernumbers = $xml->xpath('/findologic/items/item/allOrdernumbers/ordernumbers/ordernumber');
        $ordernumber = $ordernumbers[0];
        $attributes = $xml->xpath('/findologic/items/item/allAttributes/attributes/attribute');
        $attributeKey = (string)$attributes[0]->key[0];
        $attributeValue = (string)$attributes[0]->values[0]->value[0];

        $expectedOrdernumber = $id;

        $expectedAttributeKey = 'cat';
        $this->assertEquals($expectedAttributeKey, $attributeKey, 'Expected attribute key in XML should always match "cat".');
        $this->assertEquals($expectedAttributeValue, $attributeValue, 'Expected attribute value in XML should match the namespace formatted in a FINDOLOGIC proper format.');
        $this->assertEquals($expectedOrdernumber, $ordernumber, 'Expected ordernumber in XML should match the pages namespace.');
    }

    public function parameterProviderForNamespaceDepthTesting()
    {
        return [
            'one' => ['home', 'Home'],
            'two' => ['home:home', 'Home_Home'],
            'three' => ['home:home:home', 'Home_Home_Home'],
            'ten' => ['one:two:three:four:five:six:seven:eight:nine:ten', 'One_Two_Three_Four_Five_Six_Seven_Eight_Nine_Ten'],
            'underscore and space' => ['demo_page:deeeemooo:test', 'Demo page_Deeeemooo_Test']
        ];
    }

    public function test_element_name_is_empty_when_page_has_no_title()
    {
        Helper::savePages(['test321:test321:test321'], false);
        $xml = Helper::getXML();
        $names = $xml->xpath('/findologic/items/item/names/name');
        $name = (string)$names[0];
        $this->assertEmpty($name, 'Expected name in XML should be empty when page has no title.');
    }

    public function test_element_description_is_entire_page_data()
    {
        // Generate much text for DokuWiki page
        $muchContent = str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 500);

        Helper::savePages(['bigpage:muchcontent'], true, $muchContent);
        $xml = Helper::getXML();
        $descriptions = $xml->xpath('/findologic/items/item/descriptions/description');
        $description = (string)$descriptions[0];
        $this->assertEquals($muchContent, $description, 'Expected description in XML should be equal to the *entire* page content.');
    }

    public function test_element_summary_is_only_a_part_of_page_data()
    {
        // Generate much text for DokuWiki page
        $muchContent = str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 500);

        $pageId = 'bigpage:muchcontent';
        Helper::savePages([$pageId], $muchContent);
        $xml = Helper::getXML();
        $summaries = $xml->xpath('/findologic/items/item/summaries/summary');
        $summary = (string)$summaries[0];

        $metadata = p_get_metadata($pageId);
        $expectedSummary = $metadata["description"]["abstract"];

        // Make sure that metadata is correct
        $this->assertEquals($expectedSummary, $summary, 'Expected summary in XML should be only a part of the page content.');
        // Make sure that it is not the entire content
        $this->assertNotEquals($muchContent, $summary, 'Expected summary in XML should be only a part of the page content.');
    }

    /**
     * Checks that each article has an unique ID even for multiple calls.
     *
     * Example:
     *
     * When making the calls
     * * start=0&count=1
     * * start=1&count=1
     *
     * the ID of the first item should be unique for each article.
     *
     * This means the result for those two calls would be
     * * id=0
     * * id=1
     */
    public function test_element_item_id_is_unique_for_multible_calls() {
        Helper::savePages(['page01', 'page02']);
        $firstXmlCall = Helper::getXML(0, 1);
        $secondXmlCall = Helper::getXML(1, 1);

        $idFirstXmlCall = implode('', ($firstXmlCall->xpath('/findologic/items/item/@id')));
        $idSecondXmlCall = implode('', ($secondXmlCall->xpath('/findologic/items/item/@id')));

        $expectedIdFirstXmlCall = 0;
        $expectedIdSecondXmlCall = 1;

        $this->assertEquals($expectedIdFirstXmlCall, $idFirstXmlCall, 'The first call of the first item ID in the XML should always be zero when using two calls for requesting different articles.');
        $this->assertEquals($expectedIdSecondXmlCall, $idSecondXmlCall, 'Expected item ID in XML should be unique when using two calls for requesting different articles.');
    }
}