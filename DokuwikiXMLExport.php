<?php
/**
 * This is the Dokuwiki export for FINDOLOGIC.
 * If any bugs occur, please submit a new issue
 * @see https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/issues/new
 * @author Dominik Brader <support@findologic.com>
 */

if (!defined('DOKU_INC')) {
    define('DOKU_INC', realpath(dirname(__FILE__) . '/../../') . '/');
}

require_once(DOKU_INC . 'inc/init.php');
require_once(__DIR__ . '/PageGetter.php');
require(__DIR__ . '/vendor/autoload.php');

use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\Export\Data\Ordernumber;
use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Data\Keyword;

class DokuwikiXMLExport
{
    /**
     * Default value for a price. DokuWiki pages do not have a price and this is just a placeholder.
     * FINDOLOGIC requires the price attribute, so this is the reason why it is exported.
     */
    const PRICE_PLACEHOLDER = 0.0;

    /**
     * This value is needed to tell FINDOLOGIC this is a category.
     */
    const CATEGORY_KEY = 'cat';

    /**
     * Delimiter for category depth.
     */
    const CATEGORY_DELIMITER = '_';

    /**
     * In the DokuWiki, the Keyword seperator is a space.
     * To be able to have tags for multiple words, add an '_'
     */
    const KEYWORD_SPACE = '_';

    /**
     * DokuWiki saves keywords/tags in the subject of the page.
     * The subject is an array with all keywords/tags from the page in it.
     */
    const KEYWORD_KEY = 'subject';

    /**
     * The default usergroup is an empty string.
     */
    const DEFAULT_USERGROUP = '';

    /**
     * @var array $conf DokuWiki configuration.
     */
    protected $conf;

    /**
     * @var array $pages All pageIds.
     */
    protected $pages;

    /**
     * DokuwikiXMLExport constructor.
     * @param $conf array DokuWiki configuration array.
     */
    public function __construct($conf)
    {
        $this->conf = $conf;
        $this->pages = $this->getPageIds();
    }

    /**
     * Returns all pageIds, excluding those who were set in the configuration.
     *
     * @return array pageIds.
     */
    private function getPageIds()
    {
        $indexer = new Doku_Indexer();
        $pagesAndDeletedPages = $indexer->getPages();

        // Get all pages that do have a description and a title set
        $pagesAndDeletedPages = array_filter($pagesAndDeletedPages, function ($page, $k) {
            $pageDescriptionIsNotEmpty = !empty(p_get_metadata($page)['description']);
            $pageTitleIsNotEmpty = !empty(p_get_metadata($page)['title']);
            return $pageDescriptionIsNotEmpty && $pageTitleIsNotEmpty;
        }, ARRAY_FILTER_USE_BOTH);

        $excludedPages = $this->splitConfigToArray($this->conf['plugin']['findologicxmlexport']['excludePages']);
        $ids = array_diff($pagesAndDeletedPages, $excludedPages);

        return array_values($ids);
    }

    /**
     * Formats Config string to an array.
     *
     * @param string $config Excluded pages in a string.
     * @return array Returns the pages that should be excluded as array.
     */
    private function splitConfigToArray($config)
    {
        return preg_split('/\s*,\s*/', $config);
    }

    /**
     * Generate the entire XML Export based on the DokuWiki metadata.
     *
     * @param $start integer Determines the first item (offset) to be exported.
     * @param $submittedCount integer Determines the interval size / number of items to be exported.
     * @return string Returns the XML as string.
     */
    public function generateXMLExport($start, $submittedCount)
    {
        $exporter = Exporter::create(Exporter::TYPE_XML, $submittedCount);

        $total = count($this->pages);
        $count = min($total, $submittedCount); // The count can't be higher then the total number of pages.

        $this->pages = array_slice($this->pages, $start, $count);

        $items = [];
        foreach ($this->pages as $key => $page) {
            $item = $exporter->createItem($start + $key);
            $this->fillDataToItem($page, $item);
            $items[] = $item;
        }
        return $exporter->serializeItems($items, $start, $submittedCount, $total);
    }

    /**
     * Gets the Name of the current page.
     *
     * @param $pageId string Id of the DokuWiki page.
     * @return string Returns the Name/Title of the page.
     */
    private function getName($pageId)
    {
        $metadata = p_get_metadata($pageId);
        return $metadata['title'];
    }

    /**
     * Gets the Summary of the current page.
     *
     * @param $pageId string Id of the DokuWiki page.
     * @return string Returns the Summary of the page.
     */
    private function getSummary($pageId)
    {
        $metadata = p_get_metadata($pageId);
        return $metadata['description']['abstract'];
    }

    /**
     * Gets the Description of the current page.
     *
     * @param $pageId string Id of the DokuWiki page.
     * @return string Returns the Description of the page.
     */
    private function getDescription($pageId)
    {
        return rawWiki($pageId);
    }

    /**
     * Gets the Url of the current page.
     *
     * @param $pageId string Id of the DokuWiki page.
     * @return string Returns the Url of the page.
     */
    private function getUrl($pageId)
    {
        $url = wl($pageId, '', true);
        return $url;
    }

    /**
     * Gets the DateTime of the current page.
     *
     * @param $pageId string Id of the DokuWiki page.
     * @return DateTime Returns the Date formatted in ATOM DateTime of the page.
     */
    private function getDateAdded($pageId)
    {
        $metadata = p_get_metadata($pageId);
        $date = new DateTime();
        $date->setTimestamp($metadata['date']['created']);
        return $date;
    }

    /**
     * Returns the id of a given page.
     * Note: This function is trivial, but is used for legibility reasons.
     *
     * @param $pageId string Id of the DokuWiki page.
     * @return string Returns the pageId.
     */
    private function getPageId($pageId)
    {
        return $pageId;
    }

    /**
     * Gets the Category Attribute of the current page.
     *
     * Formats DokuWiki IDs to categories (FINDOLOGIC scheme).
     *
     * Examples:
     *
     * "customer_account:synonyms" -> "customer account:synonyms" -> "customer account_synonyms" -> "Customer account_Synonyms"
     * "plugin:dokuwiki-plugin-findologic-xml-export" -> "plugin:dokuwiki-plugin-findologic-xml-export" -> "plugin_findologicxmlexport" -> "Plugin_Findologicxmlexport"
     * "wiki:syntax" -> "wiki:syntax" -> "wiki_syntax" -> "Wiki_Syntax"
     *
     * @param $pageId string Id of the DokuWiki page.
     * @return array Returns the category attribute based on the export scheme.
     */
    private function getAttributesCategory($pageId)
    {
        $attribute = str_replace(self::CATEGORY_DELIMITER, ' ', $pageId); // Replace underscores with spaces
        $attribute = str_replace(':', self::CATEGORY_DELIMITER, $attribute); // Replace colons with underscores
        $attribute = ucwords($attribute, self::CATEGORY_DELIMITER); // Capitalize each category
        return (array($attribute));
    }

    /**
     * Gets the Keywords of the current page.
     *
     * @param $pageId string Id of the DokuWiki page.
     * @return array Returns all Keywords for the given page.
     */
    private function getKeywords($pageId)
    {
        $metadata = p_get_metadata($pageId);
        $allKeywords = $metadata[self::KEYWORD_KEY];

        if (empty($allKeywords)) {
            return [];
        }

        $keywords = [];
        foreach ($allKeywords as $key => $keyword) {
            // Keywords with multiple words are separated by an underscore.
            // To export them correctly, those underscores will be replaced by spaces.
            $keyword = str_replace(self::KEYWORD_SPACE, ' ', $keyword);
            $keywords[] = new Keyword($keyword);
        }

        $keywords = [self::DEFAULT_USERGROUP => $keywords];

        return $keywords;
    }

    /**
     * @param $page int Page number.
     * @param $item FINDOLOGIC\Export\Data\Item Item without data.
     *
     * @return FINDOLOGIC\Export\Data\Item Item with filled data.
     */
    public function fillDataToItem($page, $item)
    {
        $item->addName($this->getName($page));

        $item->addSummary($this->getSummary($page));

        $item->addDescription($this->getDescription($page));

        $item->addPrice(self::PRICE_PLACEHOLDER);

        $item->addUrl($this->getUrl($page));

        $item->addDateAdded($this->getDateAdded($page));

        $item->addOrdernumber(new Ordernumber($this->getPageId($page)));

        $keywordsData = $this->getKeywords($page);
        $item->setAllKeywords($keywordsData);

        $attributeCategory = new Attribute(self::CATEGORY_KEY, $this->getAttributesCategory($page));
        $item->addAttribute($attributeCategory);

        return $item;
    }
}