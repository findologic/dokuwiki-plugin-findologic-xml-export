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
require(__DIR__ . '/vendor/autoload.php');

use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\Export\Data\Name;
use FINDOLOGIC\Export\Data\Summary;
use FINDOLOGIC\Export\Data\Description;
use FINDOLOGIC\Export\Data\Price;
use FINDOLOGIC\Export\Data\Url;
use FINDOLOGIC\Export\Data\Ordernumber;
use FINDOLOGIC\Export\Data\DateAdded;
use FINDOLOGIC\Export\Data\Attribute;
use FINDOLOGIC\Export\Data\Property;

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
     * This value is the key for a dummy property.
     * Hotfix workaround for a bug @ FINDOLOGIC
     */
    const PROPERTY_DUMMY_KEY = 'dummy';

    /**
     * This value is the value for a dummy property.
     * Hotfix workaround for a bug @ FINDOLOGIC
     */
    const PROPERTY_DUMMY_VALUE = array('dummy');

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

        // Get all pages that do have a description
        $pagesAndDeletedPages = array_filter($pagesAndDeletedPages, function ($page, $k) {
            return (p_get_metadata($page)['description'] !== '');
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
     * @param $count integer Determines the interval size / number of items to be exported.
     * @return string Returns the XML as string.
     */
    public function generateXMLExport($start, $count)
    {
        $exporter = Exporter::create(Exporter::TYPE_XML, $count);

        $total = count($this->pages);
        $count = min($total, $count); // The count can't be higher then the total number of pages.

        // If count + start is higher then total, then something went totally wrong.
        if (($count + $start) > $total) {
            throw new \InvalidArgumentException("Error: Failed while trying to validate start and count values");
        }

        $this->pages = array_slice($this->pages, $start, $count);

        $items = array();
        foreach ($this->pages as $key => $page) {
            $item = $exporter->createItem($start + $key);

            $name = new Name();
            $name->setValue($this->getName($page));
            $item->setName($name);

            $summary = new Summary();
            $summary->setValue($this->getSummary($page));
            $item->setSummary($summary);

            $description = new Description();
            $description->setValue($this->getDescription($page));
            $item->setDescription($description);

            $price = new Price();
            $price->setValue(self::PRICE_PLACEHOLDER);
            $item->setPrice($price);

            $Url = new Url();
            $Url->setValue($this->getUrl($page));
            $item->setUrl($Url);

            $dateAdded = new DateAdded();
            $dateAdded->setDateValue($this->getDateAdded($page));
            $item->setDateAdded($dateAdded);

            $item->addOrdernumber(new Ordernumber($this->getPageId($page)));

            $attributeCategory = new Attribute(self::CATEGORY_KEY, $this->getAttributesCategory($page));
            $item->addAttribute($attributeCategory);

            $propertyDummy = new Property(self::PROPERTY_DUMMY_KEY, self::PROPERTY_DUMMY_VALUE);
            $item->addProperty($propertyDummy);

            $items[] = $item;
        }
        return $exporter->serializeItems($items, $start, $total);
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
        return $metadata["title"];
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
        return $metadata["description"]["abstract"];
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
        $date->setTimestamp($metadata["date"]["created"]);
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
     * "plugin:findologicxmlexport" -> "plugin:findologicxmlexport" -> "plugin_findologicxmlexport" -> "Plugin_Findologicxmlexport"
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
}