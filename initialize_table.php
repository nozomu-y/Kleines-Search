<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * create tables on initialization
 */

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

require(__DIR__ . '/core/dbconnect.php');

// create table if it does not exist
$query = "CREATE TABLE IF NOT EXISTS documents(
        id int(10) UNSIGNED ZEROFILL PRIMARY KEY AUTO_INCREMENT, 
        url varchar(256),
        title varchar(256),
        filetype varchar(32),
        last_index datetime
    )";
$result = $mysqli->query($query);
if (!$result) {
    print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
    $mysqli->close();
    exit();
}

$query = "CREATE TABLE IF NOT EXISTS inverted_index(word varchar(256))";
$result = $mysqli->query($query);
if (!$result) {
    print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
    $mysqli->close();
    exit();
}
$mysqli->close();

echo "Finished" . "\n";
