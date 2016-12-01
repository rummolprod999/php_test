<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 01.12.16
 * Time: 9:04
 */

header('Content-type: application/xml; charset=utf-8');
header('Content-Disposition: attachment; filename="downloaded.xml"');
$file = file_get_contents('0000000025_purchaseNoticeEP_20161107_115924_002.xml');
$xml = new SimpleXMLElement($file);
//echo $xml->asXML();
readfile('0000000025_purchaseNoticeEP_20161107_115924_002.xml');
?>

