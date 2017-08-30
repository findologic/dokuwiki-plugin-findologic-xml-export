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


if (isset($_GET["start"])) {
    $start = htmlspecialchars($_GET["start"]);
} else {
    $start = 0;
}

// Check if parameters are valid.
if (!is_numeric($count) || !is_numeric($start) || $start < 0 || $count < 1) {
    echo "Error count/start value(s) is/are not valid.";
    return false;
}

require_once('DokuwikiXMLExport.php');
$DokuwikiXMLExport = new DokuwikiXMLExport();

$export = $DokuwikiXMLExport->generateXMLExport($start, $count);
if ($export == true) {
    Header('Content-type: text/xml');
}
echo($export);