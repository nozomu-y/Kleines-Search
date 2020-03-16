<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * extract absolute paths from html
 */


/**
 * extract absolute paths from html
 * @param array $html
 * @return array
 */
function get_absolute_paths(array $html)
{
    $links = find_links($html['html']);
    $absolute_paths = array();
    foreach ($links as $link) {
        $rel = $link["href"];
        $absolute_path = url_to_abs(rel_to_url($html['url'], $rel));
        if ($absolute_path != NULL) {
            array_push($absolute_paths, array(
                "path"  => $absolute_path,
                "text"  => $link['text']
            ));
        }
    }
    return $absolute_paths;
}


/**
 * find links from html
 * @param string $html
 * @return array
 */
function find_links(string $html)
{
    $dom = new DOMDocument;
    @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($dom);

    $links = [];
    $query = '//a[
    @href != ""
    and not(starts-with(@href, "#"))
    and normalize-space() != ""
    ]';
    foreach ($xpath->query($query) as $node) {
        $links[] = [
            'href' => $xpath->evaluate('string(@href)', $node),
            'text' => $xpath->evaluate('normalize-space()', $node),
        ];
    }
    return $links;
}


/**
 * convert relative path to url
 * @param string $base 
 * @param string $rel_path 
 * @return string
 */
function rel_to_url(string $base = '', string $rel_path = '')
{
    $base = preg_replace('/\/[^\/]+$/', '/', $base);
    $parse = parse_url($base);
    if (preg_match('/^https*\:\/\//', $rel_path)) { // if the url starts with 'http(s)://'
        return $rel_path;
    } elseif (preg_match('/^\/\//', $rel_path)) {   // if the url starts with '//'
        $out = $parse['scheme'] . ':' . $rel_path;
        return $out;
    } elseif (preg_match('/^javascript:/', $rel_path)) {    // if the url starts with 'javascript:'
        return NULL;
    } elseif (preg_match('/^mailto:/', $rel_path)) {    // if the url starts with 'mailto:'
        return NULL;
    } elseif (preg_match('/^\/.+/', $rel_path)) {   // if the url is a relative path
        $out = $parse['scheme'] . '://' . $parse['host'] . $rel_path;
        return $out;
    }
    $tmp = array();
    $a = array();
    $b = array();
    $tmp = preg_split("/\//", $parse['path']);
    foreach ($tmp as $v) {
        if ($v) {
            array_push($a, $v);
        }
    }
    $b = preg_split("/\//", $rel_path);
    foreach ($b as $v) {
        if (strcmp($v, '') == 0) {
            continue;
        } elseif ($v == '.') {
        } elseif ($v == '..') {
            array_pop($a);
        } else {
            array_push($a, $v);
        }
    }
    $path = join('/', $a);
    $out = $parse['scheme'] . '://' . $parse['host'] . '/' . $path;
    return $out;
}


/**
 * convert url to absolute path
 * @param $url
 * @return string
 */
function url_to_abs($url)
{
    $domain = "chorkleines.com";
    // check if url is given
    if (preg_match('/^https*\:\/\//', $url)) {
        $parse = parse_url($url);
        if (strpos($parse['host'], $domain) !== false) {
            if (isset($parse['query'])) {
                return $parse['path'] . '?' . $parse['query'];
            }
            return $parse['path'];
        }
    }
    return NULL;
}
