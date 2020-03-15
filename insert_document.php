<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * insert document into database
 */

function insert_document(string $url)
{
    require_once(__DIR__ . '/core/dbconnect.php');
    $url = $mysqli->real_escape_string($url);
    // insert the url if not exists
    $query = "INSERT INTO documents (url)
        SELECT * FROM (SELECT '$url') AS tmp
        WHERE NOT EXISTS (
            SELECT url FROM documents WHERE url = '$url'
        ) LIMIT 1";
    $result = $mysqli->query($query);
    if (!$result) {
        print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
        $mysqli->close();
        exit();
    }
    $result->close();

    // get document id
    $query = "SELECT id FROM documents WHERE url = '$url'";
    $result = $mysqli->query($query);
    if (!$result) {
        print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
        $mysqli->close();
        exit();
    }
    while ($row = $result->fetch_assoc()) {
        $doc_id = $row['id'];
    }
    $result->close();
    $doc_id = str_pad((string) $doc_id, 10, 0, STR_PAD_LEFT);

    // check whether the column exists
    $query = "SHOW COLUMNS FROM inverted_index";
    $result = $mysqli->query($query);
    if (!$result) {
        print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
        $mysqli->close();
        exit();
    }
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    $result->close();

    $col_exists = FALSE;
    foreach ($columns as $column) {
        if ($column == $doc_id) {
            $col_exists = TRUE;
        }
    }
    // add column if not exists
    if (!$col_exists) {
        $query = "ALTER TABLE inverted_index ADD COLUMN $doc_id int(5) DEFAULT 0";
        $result = $mysqli->query($query);
        if (!$result) {
            print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
            $mysqli->close();
            exit();
        }
        $result->close();
    }

    // initialize column with zero
    $query = "UPDATE inverted_index SET $doc_id = 0";
    $result = $mysqli->query($query);
    if (!$result) {
        print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
        $mysqli->close();
        exit();
    }
    $result->close();

    $mysqli->close();
}
