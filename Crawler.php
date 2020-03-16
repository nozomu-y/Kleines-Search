<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * Crawler Class
 */

// $url = "https://www.chorkleines.com/member/";

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

// $url = "https://www.chorkleines.com/member/";
$url = "https://www.chorkleines.com/member/download/18/presta/";
$crawler = new Crawler($url);

class Crawler
{
    private $memory;
    function __construct($url)
    {
        // memorize the document which are already indexed and also new
        $this->memory = array();
        require(__DIR__ . '/core/dbconnect.php');
        $target = date("Y/m/d H:i:s", strtotime("-30 days", strtotime(date("Y/m/d"))));
        $query = "SELECT * FROM documents WHERE last_index IS NOT NULL AND last_index > '$target'";
        $result = $mysqli->query($query);
        if (!$result) {
            print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
            $mysqli->close();
            exit();
        }
        while ($row = $result->fetch_assoc()) {
            array_push($this->memory, $row['url']);
        }
        $this->crawler($url, "", 0);
    }

    /**
     * crawler
     * @param string $url
     * @param int $depth
     */
    private function crawler(string $url, string $title, int $depth)
    {
        require_once(__DIR__ . "/get_html.php");
        require_once(__DIR__ . "/mecab.php");
        require_once(__DIR__ . "/pdf_to_text.php");
        require_once(__DIR__ . "/html_to_text.php");
        require_once(__DIR__ . "/insert_document.php");
        require_once(__DIR__ . "/insert_word.php");
        require_once(__DIR__ . "/index_finished.php");
        require_once(__DIR__ . "/get_absolute_paths.php");
        require(__DIR__ . '/core/dbconnect.php');

        if (strpos($url, "chorkleines.com/member/") === false) {
            return;
        } elseif (strpos($url, "chorkleines.com/member/bbs/") !== false) {
            return;
        } elseif (strpos($url, "chorkleines.com/member/download/18/pdf_search/") !== false) {
            return;
        } elseif (strpos($url, "chorkleines.com/member/download/18/scoredb/") !== false) {
            return;
        } elseif (strpos($url, "chorkleines.com/member/download/18/past_exam/") !== false) {
            return;
        } elseif (strpos($url, "chorkleines.com/member/wiki/") !== false) {
            return;
        } elseif (strpos($url, "chorkleines.com/member/kleines_search/") !== false) {
            return;
        }

        // when the file is pdf
        if (preg_match('/\.pdf$/', $url)) {
            if (!in_array($url, $this->memory, true)) {
                $text = pdf_to_text($url);
                $doc_id = insert_document($url, $title);
                $lines = explode("\n", $text);
                foreach ($lines as $line) {
                    if ($line != NULL) {
                        $words = analyze($line);
                        foreach ($words as $word) {
                            insert_word($word, $doc_id);
                        }
                    }
                }
                // update last_index datetime
                index_finished($doc_id);
                echo 'done: ' . $depth . ' ' . $url . "\n";
            } else {
                echo 'pass: ' . $depth . ' ' . $url . "\n";
            }
            return;
        }

        $html = get_html($url);
        if ($html == NULL) {
            return;
        }

        if (!in_array($html['url'], $this->memory, true)) {
            $text = html_to_text($html);
            if ($html['title'] != "") {
                $title = $html['title'];
            }
            $doc_id = insert_document($html['url'], $title);
            array_push($this->memory, $html['url']);

            $lines = explode("\n", $text);
            foreach ($lines as $line) {
                if ($line != NULL) {
                    $words = analyze($line);
                    foreach ($words as $word) {
                        insert_word($word, $doc_id);
                    }
                }
            }
            // update last_index datetime
            index_finished($doc_id);
            echo 'done: ' . $depth . ' ' .  $html['url'] . "\n";
        } else {
            echo 'pass: ' . $depth . ' ' .  $html['url'] . "\n";
        }


        if ($depth < 1) {
            $absolute_paths = get_absolute_paths($html);
            foreach ($absolute_paths as $absolute_path) {
                $url = "https://www.chorkleines.com" . $absolute_path['path'];
                $this->crawler($url, $absolute_path['text'], $depth + 1);
            }
        }
        return;
    }
}
