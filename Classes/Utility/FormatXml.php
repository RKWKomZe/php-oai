<?php

namespace RKW\OaiConnector\Utility;

use DOMDocument;

/**
 * FormatXml
 *
 */
class FormatXml
{

    public static function formatXmlForDisplay(string $xml): string
    {
        // Strip BOM and trim
        $xml = preg_replace('/^\xEF\xBB\xBF/', '', $xml ?? '');
        $xml = trim($xml);

        libxml_use_internal_errors(true);

        // First try: well-formed XML with a single root
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;   // collapse insignificant whitespace
        $dom->formatOutput = true;          // pretty print

        if ($xml !== '' && $dom->loadXML($xml, LIBXML_NOBLANKS)) {
            // Save only the root element (omit XML declaration)
            $pretty = $dom->saveXML($dom->documentElement);
            libxml_clear_errors();
            return htmlspecialchars($pretty, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        // Second try: handle XML fragments (multiple top-level nodes)
        $wrapped = "<__wrap>{$xml}</__wrap>";
        $dom2 = new DOMDocument('1.0', 'UTF-8');
        $dom2->preserveWhiteSpace = false;
        $dom2->formatOutput = true;

        if ($xml !== '' && $dom2->loadXML($wrapped, LIBXML_NOBLANKS)) {
            $parts = [];
            foreach ($dom2->documentElement->childNodes as $node) {
                // Save elements with proper indentation; keep text as-is
                if ($node->nodeType === XML_ELEMENT_NODE) {
                    $parts[] = $dom2->saveXML($node);
                } elseif ($node->nodeType === XML_TEXT_NODE) {
                    $text = trim($node->textContent);
                    if ($text !== '') {
                        $parts[] = $text;
                    }
                } elseif ($node->nodeType === XML_COMMENT_NODE) {
                    $parts[] = $dom2->saveXML($node);
                }
            }
            libxml_clear_errors();
            $pretty = implode("\n", $parts);
            return htmlspecialchars($pretty, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        // Fallback: show raw (escaped) and include first libxml error for debugging
        $err = libxml_get_errors();
        libxml_clear_errors();
        $msg = $err ? trim($err[0]->message) : 'Invalid XML';
        $fallback = "<!-- Pretty-print failed: {$msg} -->\n" . $xml;
        return htmlspecialchars($fallback, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
