<?php

/**
 *  This is the Dokuwiki export made by D. Brader for FINDOLOGIC.
 *  If any bugs occur, please contact the support team (support@findologic.com).
 */


// Load everything that is needed in order to create the export
require __DIR__ . '/../../vendor/autoload.php';

use FINDOLOGIC\Export\Exporter;
use FINDOLOGIC\Export\Data\Name;
use FINDOLOGIC\Export\Data\Summary;
use FINDOLOGIC\Export\Data\Description;
use FINDOLOGIC\Export\Data\Price;
use FINDOLOGIC\Export\Data\Url;
use FINDOLOGIC\Export\Data\Bonus;
use FINDOLOGIC\Export\Data\SalesFrequency;
use FINDOLOGIC\Export\Data\DateAdded;
use FINDOLOGIC\Export\Data\Sort;
use FINDOLOGIC\Export\Data\Image;
use FINDOLOGIC\Export\Data\Ordernumber;
use FINDOLOGIC\Export\Data\Keyword;

// No Property/Attribute Support

// use FINDOLOGIC\Export\Data\Property;
// use FINDOLOGIC\Export\Data\Attribute;

class DokuwikiXMLExport {

    /**
     * Generate the entire XML Export based on the Dokuwiki data.
     *
     * @param $start integer Determines the first item (offset) to be exported.
     * @param $count integer Determines the interval size / number of items to be exported.
     * @return string Returns the XML.
     */
    public function generateXMLExport($start, $count)
    {

        $config = parse_ini_file("config.ini");
        $pages = $this->getFilesAndDirectories($config["dataDir"]);
        $exporter = Exporter::create(Exporter::TYPE_XML, $count);

        $total = count($pages);

        // Prevent count being higher then total to prevent an exception when this happens.
        if ($count > $total) {
            $count = $total;
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
            $price->setValue("0.0");
            $item->setPrice($price);

            $Url = new Url();
            $Url->setValue($this->getUrl($pages, $i));
            $item->setUrl($Url);
            // Do not use those values, because we don't need them.
            $item->setBonus(new Bonus("0"));
            $item->setSalesFrequency(new SalesFrequency("0"));
            $item->setDateAdded(new DateAdded("0"));
            $item->setSort(new Sort("0"));

            $item->addOrdernumber(new Ordernumber($this->getOrdernumber($pages, $i)));
            $items[] = $item;
        }
        return $exporter->serializeItems($items, $start, $total);
    }

    /**
     * This function gets all dokuwiki pages without directories. Just .txt files.
     *
     * @param string $dir Directory of the dokuwiki pages.
     * @param array $results Ignore this parameter. It is just used for the function itself.
     * @return array $results All files that end with .txt (for the dokuwiki).
     */
    public function getFilesAndDirectories($dir, & $results = array()){
        $files = scandir($dir);
        $fileEnd = ".txt";

        foreach($files as $key => $value){
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);

            if(!is_dir($path)) {

                if ($this->fileEndsWith($path, $fileEnd)){
                    $results[] = $path;

                }

            }

            // Ignore the . and .. directories.
            else if(!in_array($value, array('.', '..'))){
                $this->getFilesAndDirectories($path, $results);

            }
        }
        return $results;
    }

    /**
     * This function checks if the first string ends with the second one.
     *
     * @param string $string1 Just some kind of string.
     * @param string $string2 Another string.
     * @return boolean Returns true if the first string ends with the second string.
     */
    public function fileEndsWith($string1, $string2){
        return substr($string1, -strlen($string2)) == $string2 ? true : false;
    }

    /**
     * Generates the ordernumber of the at the current page.
     *
     * @param array $pages An array that contains all directories to each Dokuwiki page.
     * @param integer $key The number of the loop.
     * @return string Returns the ordernumber.
     */
    public function getOrdernumber($pages, $key){

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
     * Generates the Name/Title of the at the current page.
     *
     * @param array $pages An array that contains all directories to each Dokuwiki page.
     * @param integer $key The number of the loop.
     * @return string Returns the Name/Title of the page.
     */
    public function getName($pages, $key){
        $page = file_get_contents($pages[$key]);
        $match = array();
        if (preg_match("/={2,6}(.*?)={2,6}/", $page, $match)){
            preg_match("/={2,6}(.*?)={2,6}/", $page, $match);
        }
        else{
            $match[1] = $this->getOrdernumber($pages, $key);
        }
        return $match[1];
    }

    /**
     * Generates the Summary of the at the current page.
     *
     * @param array $pages An array that contains all directories to each Dokuwiki page.
     * @param integer $key The number of the loop.
     * @return string Returns the Summary of the page.
     */
    public function getSummaryAndDescription($pages, $key){
        $page = file_get_contents($pages[$key]);
        $summaryAndDescription = $page;
        return $summaryAndDescription;
    }

    public function getUrl($pages, $key){
        $config = parse_ini_file("config.ini");
        $url = $config["url"] . "/doku.php?id=" . $this->getOrdernumber($pages, $key);
        return $url;
    }
}