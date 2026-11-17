<?php
require_once __DIR__ . '/../vendor/autoload.php';
function clean_description(string $html): string
{
    static $purifier;
    if (!$purifier) {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,strong,em,ul,ol,li,a[href]');
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
        $config->set('Cache.DefinitionImpl', null);
        $purifier = new HTMLPurifier($config);
    }
    $clean = $purifier->purify($html);
    // Purifier also accepts relative URLs; the project permits absolute HTTP(S) only.
    $dom = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $dom->loadHTML(
        '<?xml encoding="UTF-8"><div>' . $clean . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
    );
    foreach ($dom->getElementsByTagName('a') as $a) {
        $href = $a->getAttribute('href');
        if (!preg_match('~^https?://~i', $href) || !filter_var($href, FILTER_VALIDATE_URL)) {
            $a->removeAttribute('href');
        }
    }
    $out = '';
    $root = $dom->getElementsByTagName('div')->item(0);
    if ($root) {
        foreach ($root->childNodes as $node) {
            $out .= $dom->saveHTML($node);
        };
    }
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    return $out;
}
