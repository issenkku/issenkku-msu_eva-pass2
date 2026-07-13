<?php

test('handbook PDF metadata uses the public document title', function () {
    $pdfPath = dirname(__DIR__, 2).'/public/downloads/handbook.pdf';
    $pdfMetadataBlock = file_get_contents($pdfPath, false, null, 0, 1024 * 1024);
    $title = 'คู่มือการใช้งาน ระบบประเมินบุคลากร';

    preg_match('/\/Title\s*<([0-9A-Fa-f\s]+)>/', $pdfMetadataBlock, $pdfInfo);
    $pdfInfoTitle = mb_convert_encoding(
        hex2bin(preg_replace('/\s+/', '', $pdfInfo[1])),
        'UTF-8',
        'UTF-16',
    );

    preg_match('/<\?xpacket begin=.*?<\?xpacket end="w"\?>/s', $pdfMetadataBlock, $xmpPacket);
    $xmp = new DOMDocument();
    $xmp->loadXML($xmpPacket[0]);
    $xpath = new DOMXPath($xmp);
    $xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');
    $xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
    $xmpTitles = array_map(
        static fn (DOMNode $node): string => $node->textContent,
        iterator_to_array($xpath->query('//dc:title/rdf:Alt/rdf:li')),
    );

    expect($pdfMetadataBlock)->not->toBeFalse();
    expect(str_contains($pdfMetadataBlock, 'please 2'))->toBeFalse('The obsolete PDF title prefix is still present.');
    expect($pdfInfoTitle)->toBe($title);
    expect($xmpTitles)->not->toBeEmpty()->each->toBe($title);
});
