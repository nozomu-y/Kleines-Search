<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * extract text from html
 */


/**
 * extract text from html
 * @param array $html
 * @return string
 */
function html_to_text(array $html)
{
    $content = strip_tags($html['html']);
    return $content;
}
