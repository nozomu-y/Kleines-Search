<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * get html from the given url
 */


/**
 * get html from the given url
 * @param string $url
 * @return array
 */
function get_html(string $url)
{
    // $basic_auth_password defined in the following file
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
    $html = file_get_contents($url, false, $options);

    $location = '';
    if ($http_response_header[0] != "HTTP/1.1 200 OK") {
        foreach ($http_response_header as $response) {
            if (strpos($response, "Location") !== false) {
                $location = $response;
            }
        }
        if (strpos($location, "https://www.chorkleines.com/member/") !== false) {
            $url = explode("Location: ", $location)[1];
        }
    }
    $response = array(
        "html"  => $html,
        "url"   => $url
    );
    return $response;
}
