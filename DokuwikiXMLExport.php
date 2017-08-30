<?php

/**
 *  This is the Dokuwiki export made by D. Brader for FINDOLOGIC.
 *  If any bugs occur, please contact the support team (support@findologic.com).
 */
if(!defined('DOKU_INC')) {
    define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
}

require_once (DOKU_INC.'inc/init.php');
require (__DIR__ . '/vendor/autoload.php');

use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\Export\Data\Name;
use FINDOLOGIC\Export\Data\Summary;
use FINDOLOGIC\Export\Data\Description;
use FINDOLOGIC\Export\Data\Price;
use FINDOLOGIC\Export\Data\Url;
use FINDOLOGIC\Export\Data\Ordernumber;
use FINDOLOGIC\Export\Data\DateAdded;
use FINDOLOGIC\Export\Data\Attribute;

class DokuwikiXMLExport
{
    /**
     * Generate the entire XML Export based on the Dokuwiki metadata.
     *
     * @param $start integer Determines the first item (offset) to be exported.
     * @param $count integer Determines the interval size / number of items to be exported.
     * @return string Returns the XML.
     */
    public function generateXMLExport($start, $count)
    {
        global $conf;

        $Indexer = new Doku_Indexer();
        $pagesAndDeletedPages = $Indexer->getPages();

        $excludedPages = $this->splitConfigToArray($conf['plugin']['findologicxmlexport']['excludePages']);
        $pages = null;
        foreach ($pagesAndDeletedPages as $page){
            if (p_get_metadata($page)['description']){ // Only get pages with content
                if (!in_array($page, $excludedPages)){ // Exclude pages from config
                    $pages[] = $page;
                }
            }
        }
        $exporter = Exporter::create(Exporter::TYPE_XML, $count);

        $total = count($pages);

        // If count is higher then total, set count=total to prevent an exception.
        if ($count > $total) {
            $count = $total;
        }

        // If count + start is higher then total, then something went totally wrong.
        if (($count + $start) > $total) {
            echo ('Call "\DokuwikiXMLExport::generateXMLExport" failed while trying to validate start and count values.');
            return false;
        }

        $items = array();
        for ($i = $start; $i < $start + $count; $i++) {
            $item = $exporter->createItem($i);

            $name = new Name();
            $name->setValue($this->getName($pages, $i));
            $item->setName($name);

            $summary = new Summary();
            $summary->setValue($this->getSummaryAndDescription($pages, $i));
            $item->setSummary($summary);

            $description = new Description();
            $description->setValue($this->getSummaryAndDescription($pages, $i));
            $item->setDescription($description);

            $price = new Price();
            $price->setValue('0.0');
            $item->setPrice($price);

            $Url = new Url();
            $Url->setValue($this->getUrl($pages, $i));
            $item->setUrl($Url);

            $dateAdded = new DateAdded();
            $dateAdded->setDateValue($this->getDateAdded($pages, $i));
            $item->setDateAdded($dateAdded);

            $item->addOrdernumber(new Ordernumber($this->getOrdernumber($pages, $i)));
            $items[] = $item;

            $attributeCategory = new Attribute('cat', $this->getAttributesCategory($pages, $i));
            $item->addAttribute($attributeCategory);
        }
        return $exporter->serializeItems($items, $start, $total);
    }


    /**
     * Gets the Ordernumber of the current page.
     *
     * @param array $pages Contains all namespaces of all DokuWiki pages.
     * @param integer $key The Item ID.
     * @return string Returns the ordernumber.
     */
    private function getOrdernumber($pages, $key)
    {
        return $pages[$key];
    }

    /**
     * Gets the Name of the current page.
     *
     * @param array $pages Contains all namespaces of all DokuWiki pages.
     * @param integer $key The Item ID.
     * @return string Returns the Name/Title of the page.
     */
    private function getName($pages, $key)
    {
        $metadata = p_get_metadata($pages[$key]);
        return $metadata["title"];
    }

    /**
     * Gets the Summary and Description of the current page.
     *
     * @param array $pages Contains all namespaces of all DokuWiki pages.
     * @param integer $key The Item ID.
     * @return string Returns the Summary and Description of the page.
     */
    private function getSummaryAndDescription($pages, $key)
    {
        $metadata = p_get_metadata($pages[$key]);
        return $metadata["description"]["abstract"];
    }

    /**
     * Gets the Url of the current page.
     *
     * @param array $pages Contains all namespaces of all DokuWiki pages.
     * @param integer $key The Item ID.
     * @return string Returns the Url of the page.
     */
    private function getUrl($pages, $key)
    {
        $url = DOKU_URL . 'doku.php?id=' . $this->getOrdernumber($pages, $key);
        return $url;
    }

    /**
     * Gets the DateTime of the current page.
     *
     * @param array $pages Contains all namespaces of all DokuWiki pages.
     * @param integer $key The Item ID.
     * @return DateTime Returns the Date formatted in ATOM DateTime of the page.
     */
    private function getDateAdded($pages, $key)
    {
        $metadata = p_get_metadata($pages[$key]);
        $date = date_create();
        $date = date_timestamp_set($date, $metadata["date"]["created"]);
        return new DateTime(date_format($date, \DateTime::ATOM));
    }

    /**
     * Gets the Category Attribute of the current page.
     *
     * @param array $pages Contains all namespaces of all DokuWiki pages.
     * @param integer $key The Item ID.
     * @return array Returns the category attribute based on the export scheme.
     */
    private function getAttributesCategory($pages, $key)
    {
        $ordernumber = $this->getOrdernumber($pages, $key);
        $attribute = str_replace('_', ' ', $ordernumber);
        $attribute = str_replace(':', '_', $attribute);
        return (array($attribute));
    }

    /**
     * Formats Config string to an array.
     *
     * @param string $config Excluded pages in a string.
     * @return array Returns the pages that should be excluded as array.
     */
    private function splitConfigToArray($config) {
        $array = explode(',', $config);
        $trimmedArray = array_map('trim',$array);
        return $trimmedArray;
    }
}