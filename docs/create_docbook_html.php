<?php
/**
 * Generate the xhtml from the docbook
 *
 * PHP version 5
 *
 * @package phpmailer
 * @author  Lars Olesen <lsolesen@users.sourceforge.net>
 * @version @package-version@
 * @license Creative Commons by-sa
 */
$xml_file = 'docbook.xml';
$xsl_file = 'docbook-xsl/xhtml/docbook.xsl';

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    $xsl = new DomDocument();
    $xsl->load($xsl_file);

    $inputdom = new DomDocument();
    $inputdom->load($xml_file);

    $proc = new XsltProcessor();
    $xsl = $proc->importStylesheet($xsl);
    //$proc->setParameter(null, "titles", "Titles");

    $newdom = $proc->transformToDoc($inputdom);
    print $newdom->saveXML();
}
?>