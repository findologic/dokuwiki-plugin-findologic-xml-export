<?php

/**
 *  This is the dokuwiki export from D. Brader for FINDOLOGIC.
 *  If any bugs occour, please contact the support team (support@findologic.com).
 */

// Load the config.ini file.

$config = parse_ini_file("config.ini");

// Directories of all dokuwiki pages.
$pages = getDirContents('../data/pages');


/**
* This function checks if the first string ends with the second one.
* @param $string1 Just a random string.
* @param $string2 Another random string.
* @return boolean Returnes true if the first string ends with the second string. Else false.
*/

class SimpleXMLExtended extends SimpleXMLElement {
  public function addCData($cdata_text) {
    $node = dom_import_simplexml($this); 
    $no   = $node->ownerDocument; 
    $node->appendChild($no->createCDATASection($cdata_text)); 
  } 
}


function matching_ends($string1, $string2){
    return substr($string1, -strlen($string2)) == $string2 ? "true" : "false";
}

/**
* This function gets all dokuwiki pages without directories. Just .txt files.
* @param string $dir Directory of the dokuwiki pages.
* @return array $results All files that end with .txt (dokuwiki).
*/

function getDirContents($dir, &$results = array()){
    $files = scandir($dir);
    $fileend = ".txt";

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);

        if(!is_dir($path)) {
                $results[] = $path;

            } 
        else if($value != "." && $value != "..") { // Ignore the . and .. directories.
            getDirContents($path, $results);

            if (matching_ends($path, $fileend) === "true"){ // Ignore all directories.
                $results[] = $path;

            }
        }

    }

    return $results;

}

/*
 *  Get the url parameters count and start.
 *  Also check if they are set. If not then there will be a default value.
 */

if (!isset($_GET['count'])){
    $count = 20;
}
else {
    $count = htmlspecialchars($_GET["count"]);
}

if (!isset($_GET['start'])){
    $start = 0;
}
else {
    $start = htmlspecialchars($_GET["start"]);
}
// Get the total of all dokuwiki pages.
$total = 0;

foreach ($pages as $key => $value){
    $total++;
}

if ($count > $total){
    $count = $total;
}

if ($total == 0){
    print_r("It looks like something went wrong! Please make sure that the dokuwiki is in the correct direcotry and that there is at least one page avialable.");
    die;
}

if ($start+$count>$total){
    $count = 1;
}


// TODO: Only make one foreach for a faster export of the articledata..

// Create a xml with simplexmlelement.
$xml = new SimpleXMLExtended('<?xml version="1.0"?><findologic version="1.0"/>');

// Create xml structure for all items.
$items = $xml->addChild('items');

// Set all url parameters in the xml.
$items->addAttribute("start", "$start");
$items->addAttribute("count", "$count");
$items->addAttribute("total", "$total");

for ($i = 0; $i < $count; $i++) {

    // Items
    $item = $items->addChild('item');
    $item->addAttribute('id', $i+$start);

    // Ordernumbers
    $allOrdernumbers = $item->addChild('allOrdernumbers');
    $ordernumbers = $allOrdernumbers->addChild('ordernumbers');
    foreach ($pages as $key => $value){

        // Trim the pagename/directory like the dokuwiki structure. 
        $ordernumber[$key] = str_replace(($_SERVER["DOCUMENT_ROOT"]), "", $pages[$key]);
        $ordernumber[$key] = str_replace("/", ":", $ordernumber[$key]);
        $ordernumber[$key] = strstr($ordernumber[$key], "pages");
        $ordernumber[$key] = ltrim($ordernumber[$key], "pages");
        $ordernumber[$key] = ltrim($ordernumber[$key], ":");
        $ordernumber[$key] = rtrim($ordernumber[$key], "txt");
        $ordernumber[$key] = rtrim($ordernumber[$key], ".");
    }
    $ordernumberXML = $ordernumbers->addChild('ordernumber');
    $ordernumberXML->addCData($ordernumber[$i+$start]);

    // Names/Titles
    $names = $item->addChild('names');
    $page = file_get_contents($pages[$i+$start]);
    
    foreach($pages as $key => $value){
        $match;
        

        if (preg_match("/={2,6}(.*?)={2,6}/", $page, $match)){
            preg_match("/={2,6}(.*?)={2,6}/", $page, $match);
        }
        else{
            $match[1] = $ordernumber[$i+$start];
        }
    }
    $name = $names->addChild('name');
    $name->addCData($match[1]);
    
    // Summaries
    $summaries = $item->addChild('summaries');
    $summary = $summaries->addChild('summary');
    $summary->addCData($page);

    // Descriptions
    $descriptions = $item->addChild('descriptions');
    $description = $descriptions->addChild('description');
    $description->addCData($page);

    // Prices
    $prices = $item->addChild('prices');
    $price = $prices->addChild('price', "0.00");

    // URLs
    $urls = $item->addChild('urls');
    $url = $config["url"] . "/doku.php?id=" . $ordernumber[$i+$start];
    $XMLurl = $urls->addChild('url');
    $XMLurl->addCData($url);
    // Images
    $allImages = $item->addChild('allImages');
    // Attributes
    $allAttributes = $item->addChild('allAttributes');
    $attributes = $allAttributes->addChild('attributes');
    // Keywords
    $allKeywords = $item->addChild('allKeywords');
    // Usergroups
    $usergroups = $item->addChild('usergroups');
    // Bonuses
    $bonuses = $item->addChild('bonuses');
    // Salesfrequencies
    $salesFrequencies = $item->addChild('salesFrequencies');
    // dateAddeds
    $dateAddeds = $item->addChild('dateAddeds');
    // sorts
    $sorts = $item->addChild('sorts');
    // allProperties
    $allProperties = $item->addChild('allProperties');
    $properties = $allProperties->addChild('properties');


}

Header('Content-type: text/xml');
print($xml->asXML());
?>