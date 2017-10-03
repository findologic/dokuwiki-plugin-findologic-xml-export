<?php
/**
 * Helper functions for the findologicxmlexport plugin
 *
 * @group plugin_findologicxmlexport
 * @group plugins
 */

//======================================================================
// HELPER FUNCTIONS AND CONSTANTS
//======================================================================

class Helper
{
    /**
     * A page needs to have content. This is the placeholder for this value.
     */
    const PAGE_CONTENT_PLACEHOLDER = 'This page is for test purposes.';

    /**
     * Helper function to save a page.
     *
     * @param array $ids An array with page ids (eg. start or wiki:syntax)
     * @param string $content Optional content, else the default test value will be set
     */
    static function savePages($ids, $content = self::PAGE_CONTENT_PLACEHOLDER)
    {
        foreach ($ids as $id) {
            saveWikiText($id, $content, '');
            idx_addPage($id);
        }
    }

    /**
     * Gets parameters, calls the Export and returns the XML as SimpleXMLElement.
     *
     * @param int $start Optional start value
     * @param int $count Optional count value
     * @param array $conf Optional configuration
     * @return SimpleXMLElement Export generated XML
     */
    static function getXML($start = 0, $count = 20, $conf = array())
    {
        $dokuwikiXmlExport = new DokuwikiXMLExport($conf);
        return new SimpleXMLElement($dokuwikiXmlExport->generateXMLExport($start, $count));
    }

    /**
     * Remove all DokuWiki pages before each test
     */
    static function setUp()
    {
        $indexer = new Doku_Indexer();
        $pages = $indexer->getPages();
        self::savePages($pages, ''); // Saving a page with empty content will result in removing it.
    }
}