<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * Crawler Class
 */

// $url = "https://www.chorkleines.com/member/";
$url = "https://www.chorkleines.com/member/download/18/past_member's_page";
$crawler = new Crawler($url);

class Crawler
{
    private $memory;
    private $file = "./log.txt";
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
        $this->crawler($url, 0);
    }

    /**
     * crawler
     * @param string $url
     * @param int $depth
     */
    private function crawler(string $url, int $depth)
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
        }

        $html = get_html($url);
        if ($html == NULL) {
            return;
        }

        if (!in_array($html['url'], $this->memory, true)) {
            $text = html_to_text($html);
            $doc_id = insert_document($html['url'], $html['title']);
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
        }


        if ($depth < 1) {
            $absolute_paths = get_absolute_paths($html);
            foreach ($absolute_paths as $absolute_path) {
                $absolute_path = "https://www.chorkleines.com" . $absolute_path;
                if (preg_match('/\.pdf$/', $rel_path)) { // if the url ends with '.pdf'
                    file_put_contents($this->file, 'depth:' . $depth . 'matched pdf: ' . $absolute_path . "\n", FILE_APPEND | LOCK_EX);
                    echo 'depth:' . $depth . 'matched pdf: ' . $absolute_path . "\n";
                    $text = pdf_to_text($absolute_path);
                    $doc_id = insert_document($absolute_path, "");
                    $lines = explode("\n", $text);
                    foreach ($lines as $line) {
                        if ($line != NULL) {
                            $words = analyze($line);
                            foreach ($words as $word) {
                                insert_word($word, $doc_id);
                            }
                        }
                    }
                } else {
                    file_put_contents($this->file, 'depth:' . $depth .  $absolute_path . "\n", FILE_APPEND | LOCK_EX);
                    echo 'depth:' . $depth .  $absolute_path . "\n";
                    $this->crawler($absolute_path, $depth + 1);
                }
            }
        }
        return 'done\n';
    }
}
