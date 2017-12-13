<?php

/**
 * This is the Dokuwiki export for FINDOLOGIC.
 * If any bugs occur, please submit a new issue
 * @see https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/issues/new
 * @author Dominik Brader <support@findologic.com>
 */

require_once(__DIR__ . '/DokuwikiXMLExport.php');

const DOKUWIKI_INC = 'DOKU_INC';
const COUNT_NAME = 'count';
const START_NAME = 'start';
const EXPORT_HEADER = 'Content-type: text/xml';

const DEFAULT_COUNT_VALUE = 20;
const DEFAULT_START_VALUE = 0;

const ERROR_CODE_TEXT = 'Status: 400 Bad Request';
const ERROR_MESSAGE = 'start and count values are not valid';
const ERROR_CODE_VALUE = 400;

$start = getUrlParam(START_NAME, DEFAULT_START_VALUE);
$count = getUrlParam(COUNT_NAME, DEFAULT_COUNT_VALUE);

if (paramsValid($start, $count)) {
    header(EXPORT_HEADER);
    echo getXml($start, $count);
} else {
    header(ERROR_CODE_TEXT, true, ERROR_CODE_VALUE);
    die(ERROR_MESSAGE);
}

/**
 * Validates count and start values
 *
 * @param $start int start value
 * @param $count int count value
 * @return bool true if parameters are valid, else false
 */
function paramsValid($start, $count)
{
    return (is_numeric($count) && is_numeric($start) && $start >= 0 && $count > 0);
}

/**
 * Gets the value of _GET param, or the default value if _GET param was not set
 *
 * @param $paramName string Name of the URL parameter
 * @param $defaultValue string Default value if _GET parameter is not set
 * @return string _GET parameter or default value of _GET parameter is not set
 */
function getUrlParam($paramName, $defaultValue)
{
    if (isset($_GET[$paramName])) {
        return htmlspecialchars($_GET[$paramName]);
    } else {
        return $defaultValue;
    }
}

/**
 * Returns generated XML from export
 *
 * @param $start int start value
 * @param $count int count value
 * @return string Generated XML
 */
function getXml($start, $count)
{
    global $conf;
    $dokuwikiXmlExport = new DokuwikiXMLExport($conf);
    return $dokuwikiXmlExport->generateXMLExport($start, $count);
}