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
 * @param string $url
 * @return string
 */
function file_to_text(string $url)
{
    require(__DIR__ . "/core/config.php");

    $data = http_build_query($_POST, "", "&");
    $header = array(
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic ' . base64_encode($basic_auth_password)
    );
    $options = array(
        'http' => array(
            'method' => 'POST',
            'content' => $data,
            'header' => implode("\r\n", $header),
        )
    );
    $options = stream_context_create($options);
    $content = file_get_contents($url, false, $options);
    if ($content === false) {
        return NULL;
    }

    return $content;
}
