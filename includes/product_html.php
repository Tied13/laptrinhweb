<?php
function cleanProductHtml($html) {
    if (!is_string($html) || trim($html) === '') return '';
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
    $blocked = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math',
        'form', 'input', 'button', 'select', 'textarea', 'template', 'canvas'];
    $clean = function ($node) use (&$clean, $allowed, $blocked) {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMComment) {
                $node->removeChild($child);
                continue;
            }
            if (!$child instanceof DOMElement) continue;
            $tag = strtolower($child->tagName);
            if (in_array($tag, $blocked, true)) {
                $node->removeChild($child);
                continue;
            }
            $clean($child);
            if (!in_array($tag, $allowed, true)) {
                while ($child->firstChild) $node->insertBefore($child->firstChild, $child);
                $node->removeChild($child);
                continue;
            }
            foreach (iterator_to_array($child->attributes) as $attribute) {
                $key = strtolower($attribute->name);
                $value = trim($attribute->value);
                $safeUrl = preg_match('~^(https?://|assets/uploads/products/)~i', $value);
                if (!(($tag === 'a' && $key === 'href' && $safeUrl) || ($tag === 'img' && $key === 'src' && $safeUrl))) {
                    $child->removeAttributeNode($attribute);
                }
            }
        }
    };
    $clean($root);
    $output = '';
    foreach ($root->childNodes as $node) $output .= $doc->saveHTML($node);
    return $output;
}
