<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * insert word into database
 */

function insert_word(array $word, string $doc_id)
{
    require(__DIR__ . '/core/dbconnect.php');
    $word = $mysqli->real_escape_string($word['text']);
    // insert the word if not exists
    $query = "INSERT INTO inverted_index (word)
        SELECT * FROM (SELECT '$word') AS tmp
        WHERE NOT EXISTS (
            SELECT word FROM inverted_index WHERE word = '$word'
        ) LIMIT 1";
    $result = $mysqli->query($query);
    if (!$result) {
        print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
        $mysqli->close();
        exit();
    }

    // increment the number of words appeared
    $query = "UPDATE inverted_index SET `$doc_id` = `$doc_id` + 1 WHERE word = '$word'";
    $result = $mysqli->query($query);
    if (!$result) {
        print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
        $mysqli->close();
        exit();
    }

    $mysqli->close();
}
