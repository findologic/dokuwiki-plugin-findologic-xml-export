<?php

/**
 * This is the Dokuwiki export made by Dominik Brader for FINDOLOGIC.
 * If any bugs occur, please submit a new issue
 * @see https://github.com/findologic/dokuwiki-plugin-findologic-xml-export/issues/new
 */

if (!defined('DOKU_INC')){
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

        $excludedPages = $this->splitConfigToArray($this->conf['plugin']['findologicxmlexport']['excludePages']);

        foreach ($pagesAndDeletedPages as $page) {
            if (p_get_metadata($page)['description']) { // Only get pages with content
                if (!in_array($page, $excludedPages)) { // Exclude pages from config
                    $ids[] = $page;
                }
            }
        }

        return $ids;
    }

    /**
     * Formats Config string to an array.
     *
     * @param string $config Excluded pages in a string.
     * @return array Returns the pages that should be excluded as array.
     */
    private function splitConfigToArray($config)
    {
        $array = explode(',', $config);
        $trimmedArray = array_map('trim', $array);
        return $trimmedArray;
    }

    /**
     * Generate the entire XML Export based on the Dokuwiki metadata.
     *
     * @param $start integer Determines the first item (offset) to be exported.
     * @param $count integer Determines the interval size / number of items to be exported.
     * @return string Returns the XML.
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

        $items = array();
        for ($i = $start; $i < $start + $count; $i++) {
            $item = $exporter->createItem($i);

            $name = new Name();
            $name->setValue($this->getName($this->pages[$i]));
            $item->setName($name);

            $summary = new Summary();
            $summary->setValue($this->getSummaryAndDescription($this->pages[$i]));
            $item->setSummary($summary);

            $description = new Description();
            $description->setValue($this->getSummaryAndDescription($this->pages[$i]));
            $item->setDescription($description);

            $price = new Price();
            $price->setValue(self::PRICE_PLACEHOLDER);
            $item->setPrice($price);

            $Url = new Url();
            $Url->setValue($this->getUrl($this->pages[$i]));
            $item->setUrl($Url);

            $dateAdded = new DateAdded();
            $dateAdded->setDateValue($this->getDateAdded($this->pages[$i]));
            $item->setDateAdded($dateAdded);

            $item->addOrdernumber(new Ordernumber($this->getPageId($this->pages[$i])));
            $items[] = $item;

            $attributeCategory = new Attribute(self::CATEGORY_KEY, $this->getAttributesCategory($this->pages[$i]));
            $item->addAttribute($attributeCategory);

            $propertyDummy = new Property(self::PROPERTY_DUMMY_KEY, self::PROPERTY_DUMMY_VALUE);
            $item->addProperty($propertyDummy);
        }
        return $exporter->serializeItems($items, $start, $total);
    }

    /**
     * Gets the Name of the current page.
     *
     * @return string Returns the Name/Title of the page.
     */
    private function getName($id)
    {
        $metadata = p_get_metadata($id);
        return $metadata["title"];
    }

    /**
     * Gets the Summary and Description of the current page.
     *
     * @return string Returns the Summary and Description of the page.
     */
    private function getSummaryAndDescription($id)
    {
        $metadata = p_get_metadata($id);
        return $metadata["description"]["abstract"];
    }

    /**
     * Gets the Url of the current page.
     *
     * @return string Returns the Url of the page.
     */
    private function getUrl($id)
    {
        $url = wl($id, '', true);
        return $url;
    }

    /**
     * Gets the DateTime of the current page.
     *
     * @return DateTime Returns the Date formatted in ATOM DateTime of the page.
     */
    private function getDateAdded($id)
    {
        $metadata = p_get_metadata($id);
        $date = new DateTime();
        $date->setTimestamp($metadata["date"]["created"]);
        return $date;
    }

    /**
     * Returns the id of a given page.
     * Note: This function is trivial, but is used for legibility reasons.
     *
     * @return string Returns the ordernumber.
     */
    private function getPageId($id)
    {
        return $id;
    }

    /**
     * Gets the Category Attribute of the current page.
     *
     * Formats DokuWiki IDs to categories.
     *
     * Examples: "customer_account:synonyms" -> "customer account:synonyms" -> "customer account_synonyms"
     *           "plugin:findologicxmlexport" -> "plugin:findologicxmlexport" -> "plugin_findologicxmlexport"
     *
     * @return array Returns the category attribute based on the export scheme.
     */
    private function getAttributesCategory($id)
    {
        $ordernumber = $this->getPageId($id);

        $attribute = str_replace('_', ' ', $ordernumber);
        $attribute = str_replace(':', '_', $attribute);
        return (array($attribute));
    }
}