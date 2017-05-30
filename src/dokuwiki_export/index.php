<?php

/**
 *  This is the Dokuwiki export made by D. Brader for FINDOLOGIC.
 *  If any bugs occur, please contact the support team (support@findologic.com).
 */

error_reporting(0);
// Get URL parameters start and count
if (htmlspecialchars($_GET["count"])) {
    $count = htmlspecialchars($_GET["count"]);
}

else {$count = 20;}
if (htmlspecialchars($_GET["start"])) {
    $start = htmlspecialchars($_GET["start"]);
}

else {$start = 0;}

require_once ('exporter.php');

$DokuwikiXMLExport = new DokuwikiXMLExport();
Header('Content-type: text/xml');
echo ($DokuwikiXMLExport->generateXMLExport($start, $count));
