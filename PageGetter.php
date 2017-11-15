<?php
/**
 * This is the Dokuwiki export for FINDOLOGIC.
 *
 * If any bugs occur, please submit a new issue
 * @see https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/issues/new
 * @author Dominik Brader <support@findologic.com>
 */

class PageGetter
{
    /**
     * @return array all pages that do not have a title set.
     */
    static function getPagesWithoutTitle()
    {
        $indexer = new Doku_Indexer();
        $allPages = $indexer->getPages();

        foreach ($allPages as $page) {
            if (p_get_metadata($page)['description']) { // Only get pages with content
                if (!p_get_metadata($page)['title']) { // Check for pages without a title
                    // Set variables that are used for the template
                    $pagesWithoutTitle[] = $page;
                }
            }
        }
        if ($pagesWithoutTitle) {
            return $pagesWithoutTitle;
        }
        else {
            return [];
        }
    }
}