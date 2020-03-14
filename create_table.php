<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * Create tables on initialization
 */

require_once(__DIR__ . '/core/dbconnect.php');

// create table if it does not exist
$query = "CREATE TABLE IF NOT EXISTS documents(id int(10), url varchar(256))";
$result = $mysqli->query($query);
if (!$result) {
    print('Query failed on line ' . __LINE__ . ' in ' . __FILE__ . ': ' . $mysqli->error);
    $mysqli->close();
    exit();
}
$result->close();

$query = "CREATE TABLE IF NOT EXISTS inverted_index(keyword varchar(256))";
$result = $mysqli->query($query);
if (!$result) {
    print('Query failed on line ' . __LINE__ . ' in ' . __FILE__ . ': ' . $mysqli->error);
    $mysqli->close();
    exit();
}
$result->close();
