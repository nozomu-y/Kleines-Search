<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * search from query
 */

function search(string $search_query)
{
    require_once(__DIR__ . "/mecab.php");
    require(__DIR__ . '/core/dbconnect.php');
    $words = analyze($search_query);
    $cost = array();
    $title = array();
    $filetype = array();
    foreach ($words as $word) {
        $word = $word['text'];
        $query = "SELECT * FROM inverted_index WHERE word='$word'";
        $result = $mysqli->query($query);
        if (!$result) {
            print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
            $mysqli->close();
            exit();
        }
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            foreach ($row as $doc_id => $num) {
                $query = "SELECT * FROM documents WHERE id='$doc_id'";
                $result_2 = $mysqli->query($query);
                if (!$result_2) {
                    print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
                    $mysqli->close();
                    exit();
                }
                $row_2 = $result_2->fetch_assoc();
                $url = $row_2['url'];
                if ($num != 0) {
                    $cost[$url] += mb_strlen($word) * $num;
                    $title[$url] = $row_2['title'];
                    $filetype[$url] = $row_2['filetype'];
                }
            }
        }
    }
    arsort($cost);
    $search_result = array(
        "cost"      => $cost,
        "title"     => $title,
        "filetype"  => $filetype
    );
    return $search_result;
}
