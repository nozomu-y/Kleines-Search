<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * create tables on initialization
 */

require_once(__DIR__ . '/core/dbconnect.php');

// create table if it does not exist
$query = "CREATE TABLE IF NOT EXISTS documents(id int(10) UNSIGNED ZEROFILL PRIMARY KEY, url varchar(256))";
$result = $mysqli->query($query);
if (!$result) {
    print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
    $mysqli->close();
    exit();
}
$result->close();

$query = "CREATE TABLE IF NOT EXISTS inverted_index(word varchar(256))";
$result = $mysqli->query($query);
if (!$result) {
    print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
    $mysqli->close();
    exit();
}
$result->close();
