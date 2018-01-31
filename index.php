<?php
/**
 * This is the Dokuwiki export for FINDOLOGIC.
 * If any bugs occur, please submit a new issue
 * @see https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/issues/new
 * @author Dominik Brader <support@findologic.com>
 */
require_once(__DIR__ . '/OutputXMLHelper.php');
$outputXmlHelper = new OutputXMLHelper();
// Get URL params
$start = (int)$outputXmlHelper->getUrlParam($outputXmlHelper::START_NAME, $outputXmlHelper::DEFAULT_START_VALUE, $_GET);
$count = (int)$outputXmlHelper->getUrlParam($outputXmlHelper::COUNT_NAME, $outputXmlHelper::DEFAULT_COUNT_VALUE, $_GET);
// Check if params are valid and return the XML with the corresponding header
if ($outputXmlHelper->paramsValid($start, $count, FILTER_VALIDATE_INT)) {
    $outputXmlHelper->printXml($start, $count);
} else {
    $outputXmlHelper->throwError();
}