<?php

/**
 *  This is the Dokuwiki export made by D. Brader for FINDOLOGIC.
 *  If any bugs occur, please contact the support team (support@findologic.com).
 */

// Get URL parameters start and count
if (isset($_GET["count"])) {
    $count = htmlspecialchars($_GET["count"]);
} else {
    $count = 20;
}

// Check if parameter is valid.
if (!is_numeric($count)){
    echo "Error, count/start value(s) is/are not valid.";
    return false;
}


if (isset($_GET["start"])) {
    $start = htmlspecialchars($_GET["start"]);
} else {
    $start = 0;
}

// Check if parameter is valid.
if (!is_numeric($start)){
    echo "Error, count/start value(s) is/are not valid.";
    return false;
}


require_once('DokuwikiXMLExport.php');
$DokuwikiXMLExport = new DokuwikiXMLExport();

Header('Content-type: text/xml');
echo($DokuwikiXMLExport->generateXMLExport($start, $count));
