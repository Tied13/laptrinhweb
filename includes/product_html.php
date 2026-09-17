<?php
function cleanProductHtml($html) {
    $doc = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="content">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $root = $doc->getElementById('content');
    if (!$root) return '';
    $allowed = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'h2', 'h3', 'ul', 'ol', 'li',
        'blockquote', 'a', 'img', 'figure', 'figcaption', 'table', 'thead',
        'tbody', 'tfoot', 'tr', 'th', 'td'
    ];
    $clean = function ($node) use (&$clean, $allowed) {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (!$child instanceof DOMElement) continue;
            $tag = strtolower($child->tagName);
            if (!in_array($tag, $allowed, true)) { $child->parentNode->removeChild($child); continue; }
            foreach (iterator_to_array($child->attributes) as $attribute) {
                $key = strtolower($attribute->name);
                $value = trim($attribute->value);
                $safeUrl = preg_match('~^(https?://|assets/uploads/products/)~i', $value);
                if (!(($tag === 'a' && $key === 'href' && $safeUrl) || ($tag === 'img' && $key === 'src' && $safeUrl))) {
                    $child->removeAttributeNode($attribute);
                }
            }
            $clean($child);
        }
    };
    $clean($root);
    $output = '';
    foreach ($root->childNodes as $node) $output .= $doc->saveHTML($node);
    return $output;
}
