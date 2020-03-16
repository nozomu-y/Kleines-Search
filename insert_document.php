<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * insert document into database
 */

function insert_document(string $url, string $title)
{
    require(__DIR__ . '/core/dbconnect.php');
    if (preg_match('/\.(pdf|docx?|csv|txt|mp3|midi|mid|wav|zip|tar|gz|tgz|jpe?g|png|xlsx?|pptx?|css|js)$/', $url)) {
        $filetype = pathinfo($url, PATHINFO_EXTENSION);
    } else {
        $filetype = '';
    }
    $url = $mysqli->real_escape_string($url);
    $title = $mysqli->real_escape_string($title);
    $filetype = $mysqli->real_escape_string($filetype);
    // insert the url if not exists
    $query = "INSERT INTO documents (url, title, filetype)
        SELECT * FROM (SELECT '$url', '$title', '$filetype') AS tmp
        WHERE NOT EXISTS (
            SELECT url FROM documents WHERE url = '$url'
        ) LIMIT 1";
    $result = $mysqli->query($query);
    if (!$result) {
        print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
        $mysqli->close();
        exit();
    }

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

    $col_exists = FALSE;
    foreach ($columns as $column) {
        if ($column == $doc_id) {
            $col_exists = TRUE;
        }
    }
    // add column if not exists
    if (!$col_exists) {
        $query = "ALTER TABLE inverted_index ADD COLUMN `$doc_id` int(5) DEFAULT '0'";
        $result = $mysqli->query($query);
        if (!$result) {
            print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
            $mysqli->close();
            exit();
        }
    }

    // initialize column with zero
    $query = "UPDATE inverted_index SET `$doc_id` = 0";
    $result = $mysqli->query($query);
    if (!$result) {
        print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
        $mysqli->close();
        exit();
    }

    $mysqli->close();
    return $doc_id;
}
