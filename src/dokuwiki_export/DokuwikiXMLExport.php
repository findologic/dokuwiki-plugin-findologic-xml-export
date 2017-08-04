<?php

/**
 *  This is the Dokuwiki export made by D. Brader for FINDOLOGIC.
 *  If any bugs occur, please contact the support team (support@findologic.com).
 */

require __DIR__ . '/../../vendor/autoload.php';
require_once ('filesAndDirectories.php');

use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\Export\Data\Name;
use FINDOLOGIC\Export\Data\Summary;
use FINDOLOGIC\Export\Data\Description;
use FINDOLOGIC\Export\Data\Price;
use FINDOLOGIC\Export\Data\Url;
use FINDOLOGIC\Export\Data\Ordernumber;

class DokuwikiXMLExport
{

    /**
     * Generate the entire XML Export based on the Dokuwiki data.
     *
     * @param $start integer Determines the first item (offset) to be exported.
     * @param $count integer Determines the interval size / number of items to be exported.
     * @param $config array Configuration that is set in the .ini file.
     * @return string Returns the XML.
     */
    public function generateXMLExport($start, $count, $config)
    {
        $getFilesAndDirectories = new filesAndDirectories($config["dataDir"]);
        $pages = $getFilesAndDirectories->getFilesAndDirectories($config["dataDir"]);

        $exporter = Exporter::create(Exporter::TYPE_XML, $count);

        $total = count($pages);

        // If count is higher then total, set count=total to prevent an exception.
        if ($count > $total) {
            $count = $total;
        }

        // If count + start is higher then total, then something went totally wrong.
        if (($count + $start) > $total) {
            echo "Error count/start value(s) is/are not valid.";
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

            $item->addOrdernumber(new Ordernumber($this->getOrdernumber($pages, $i)));
            $items[] = $item;
        }
        return $exporter->serializeItems($items, $start, $total);
    }


    /**
     * Gets the Ordernumber of the current page.
     *
     * @param array $pages An array that contains all paths to each Dokuwiki page.
     * @param integer $key The number of the loop.
     * @return string Returns the ordernumber.
     */
    private function getOrdernumber($pages, $key)
    {
        $ordernumber = str_replace(($_SERVER["DOCUMENT_ROOT"]), "", $pages[$key]);
        $ordernumber = str_replace("/", ":", $ordernumber);
        $ordernumber = strstr($ordernumber, "pages");
        $ordernumber = ltrim($ordernumber, "pages");
        $ordernumber = ltrim($ordernumber, ":");
        $ordernumber = rtrim($ordernumber, "txt");
        $ordernumber = rtrim($ordernumber, ".");
        return $ordernumber;
    }

    /**
     * Gets the Name of the current page.
     *
     * @param array $pages An array that contains all directories to each Dokuwiki page.
     * @param integer $key The number of the loop.
     * @return string Returns the Name/Title of the page.
     */
    private function getName($pages, $key)
    {
        $page = file_get_contents($pages[$key]);
        $match = array();
        if (preg_match("/={2,6}(.*?)={2,6}/", $page, $match)) {
            preg_match("/={2,6}(.*?)={2,6}/", $page, $match);
        } else {
            $match[1] = $this->getOrdernumber($pages, $key);
        }
        return $match[1];
    }

    /**
     * Gets the Summary and Description of the current page.
     *
     * @param array $pages An array that contains all directories to each Dokuwiki page.
     * @param integer $key The number of the loop.
     * @return string Returns the Summary of the page.
     */
    private function getSummaryAndDescription($pages, $key)
    {
        $page = file_get_contents($pages[$key]);
        $summaryAndDescription = $page;
        return $summaryAndDescription;
    }

    /**
     * Gets the Url of the current page.
     *
     * @param array $pages An array that contains all directories to each Dokuwiki page.
     * @param integer $key The number of the loop.
     * @return string Returns the Summary of the page.
     */
    private function getUrl($pages, $key)
    {
        $config = parse_ini_file("config.ini");
        $url = $config["url"] . "/doku.php?id=" . $this->getOrdernumber($pages, $key);
        return $url;
    }
}