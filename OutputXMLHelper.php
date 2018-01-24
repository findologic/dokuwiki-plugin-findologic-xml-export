<?php

/**
 * This is the Dokuwiki export for FINDOLOGIC.
 * If any bugs occur, please submit a new issue
 * @see https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/issues/new
 * @author Dominik Brader <support@findologic.com>
 */

require_once(__DIR__ . '/DokuwikiXMLExport.php');

class OutputXMLHelper
{
    /**
     * Default DokuWiki include path
     */
    const DOKUWIKI_INC = 'DOKU_INC';
    /**
     * Name of count _GET parameter
     */
    const COUNT_NAME = 'count';
    /**
     * Name of start _GET parameter
     */
    const START_NAME = 'start';
    /**
     * Header if export succeeds
     */
    const EXPORT_HEADER = 'Content-type: text/xml';
    /**
     * Default value for count _GET parameter
     */
    const DEFAULT_COUNT_VALUE = 20;
    /**
     * Default value for start _GET parameter
     */
    const DEFAULT_START_VALUE = 0;
    /**
     * Gets set when an error appears.
     * An error may appear if _GET parameters are not valid.
     */
    const EXPORT_ERROR_HEADER = 'Status: 400 Bad Request';
    /**
     * This error message will be outputted if an error occurs.
     */
    const EXPORT_ERROR_MESSAGE = 'start and count values are not valid';
    /**
     * Error code that will be returned if an error occurs.
     */
    const EXPORT_ERROR_CODE = 400;

    /**
     * Validates count and start values
     *
     * @param $start int start value
     * @param $count int count value
     * @return bool true if parameters are valid, else false
     */
    public function paramsValid($start, $count)
    {
        return (is_int($count) && is_int($start) && $start >= 0 && $count > 0);
    }

    /**
     * Gets the value of _GET param converted to HTML entities, or the default value if _GET param was not set.
     *
     * @param $paramName string Name of the URL parameter
     * @param $defaultValue string Default value if _GET parameter is not set
     * @param $getParam array _GET param
     * @return string value of the _GET parameter or default value if _GET parameter is not set
     */
    public function getUrlParam($paramName, $defaultValue, $getParam)
    {
        if (isset($getParam[$paramName])) {
            return htmlspecialchars($getParam[$paramName]);
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
    public function getXml($start, $count)
    {
        global $conf;
        $dokuwikiXmlExport = new DokuwikiXMLExport($conf);
        return $dokuwikiXmlExport->generateXMLExport($start, $count);
    }
}