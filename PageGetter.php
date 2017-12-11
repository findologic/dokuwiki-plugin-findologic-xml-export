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
     * Get all pages that do not have specified a title.
     * Pages that do not have a description are deleted pages.
     *
     * @return array All pages that do not have a title set.
     */
    static function getPagesWithoutTitle()
    {
        $indexer = new Doku_Indexer();
        $allPages = $indexer->getPages();

        // Get all pages that do have a description, because pages that don't, are deleted pages.
        // And only get pages that do not have a title set.
        $allPagesWithoutTitle = array_filter($allPages, function ($page, $k) {
            return (p_get_metadata($page)['description'] !== '' && !p_get_metadata($page)['title']);
        }, ARRAY_FILTER_USE_BOTH);

        // Create DokuWikiPage objects
        $pagesData = [];
        foreach ($allPagesWithoutTitle as $key => $sortedPage) {
            $pagesData[] = new DokuwikiPage($sortedPage);
        }

        // Sort pages by lastEdit
        usort($pagesData, function($object1, $object2){
            return ($object1->lastEdit < $object2->lastEdit) ? 1 : -1;
        });

        return $pagesData;
    }

}