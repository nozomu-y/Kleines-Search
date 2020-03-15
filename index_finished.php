<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * update last_index datetime
 */

function index_finished(string $doc_id)
{
    require(__DIR__ . '/core/dbconnect.php');
    $query = "UPDATE documents SET last_index = NOW() WHERE id=$doc_id";
    $result = $mysqli->query($query);
    if (!$result) {
        print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
        $mysqli->close();
        exit();
    }
}
