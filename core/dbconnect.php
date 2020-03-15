<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 */

require(__DIR__ . "/config.php");

$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_error) {
    error_log($mysqli->connect_error);
    exit();
} else {
    $mysqli->set_charset('utf8');
}
