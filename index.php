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
$start = $outputXmlHelper->getUrlParam($outputXmlHelper::START_NAME, $outputXmlHelper::DEFAULT_START_VALUE);
$count = $outputXmlHelper->getUrlParam($outputXmlHelper::COUNT_NAME, $outputXmlHelper::DEFAULT_COUNT_VALUE);

// Check if params are valid and return the XML with the corresponding header
if ($outputXmlHelper->paramsValid($start, $count)) {
    header($outputXmlHelper::EXPORT_HEADER);
    echo $outputXmlHelper->getXml($start, $count);
} else {
    header($outputXmlHelper::EXPORT_ERROR_HEADER, true, $outputXmlHelper::EXPORT_ERROR_CODE);
    die($outputXmlHelper::EXPORT_ERROR_MESSAGE);
}
