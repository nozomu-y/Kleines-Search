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
    $html = @file_get_contents($url, false, $options);

    if (preg_match("/<title>(.*?)<\/title>/i", $html, $matches)) {
        $title = $matches[1];
    } else {
        $title = "";
    }

    $location = '';
    if ($http_response_header[0] == "HTTP/1.1 404 Not Found") {
        return NULL;
    } elseif ($http_response_header[0] != "HTTP/1.1 200 OK") {
        foreach ($http_response_header as $response) {
            if (strpos($response, "Location") !== false) {
                $location = $response;
                $url_tmp = explode("Location: ", $location)[1];
                $url_tmp = rel_to_url($url, $url_tmp);
                return get_html($url_tmp);
            }
        }
    }
    $response = array(
        "html"  => $html,
        "title" => $title,
        "url"   => $url
    );
    return $response;
}
