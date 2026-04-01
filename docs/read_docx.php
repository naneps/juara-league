<?php
$file = $argv[1] ?? 'juara-league-srs-modul3.docx';
$zip = new ZipArchive;
if ($zip->open($file) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $paragraphs = $dom->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'p');
    
    foreach ($paragraphs as $p) {
        $texts = $p->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 't');
        $line = '';
        foreach ($texts as $t) {
            $line .= $t->nodeValue;
        }
        $line = trim($line);
        if ($line) {
            echo $line . "\n";
        }
    }
} else {
    echo "Failed to open $file";
}
