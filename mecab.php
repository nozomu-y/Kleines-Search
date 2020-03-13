<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * analyze text using mecab
 */

require 'vendor/autoload.php';

use Youaoi\MeCab\MeCab;

MeCab::setDefaults([
    // mecabのPATH
    'command' => posix_getpwuid(posix_geteuid())['dir'] . '/usr/local/bin/mecab',

    // 独自の辞書ディレクトリ
    'dictionaryDir' => posix_getpwuid(posix_geteuid())['dir'] . '/usr/local/lib/mecab/dic/ipadic/',

    // ユーザー辞書
    'dictionary' => posix_getpwuid(posix_geteuid())['dir'] . '/usr/local/lib/mecab/dic/user_dic/user_symbols.dic',
]);

/**
 * analyze the given string with mecab
 * @param string $text
 * @return array
 */
function analyze(string $text)
{
    $mecab = new MeCab();
    // change encoding from UTF-8 to EUC-JP
    $array = $mecab->analysis(mb_convert_encoding($text, 'EUC-JP', 'UTF-8'));

    $result = array();
    foreach ($array as $word_num => $a) {
        $a = (array) $a;
        // change encoding from EUC-JP to UTF-8
        mb_convert_variables('UTF-8', 'EUC-JP', $a);
        $result_word = array();
        foreach ($a as $key => $str) {
            if (strpos($key, "str")) {
                $result_word['str'] = $str;
            } else if (strpos($key, "text")) {
                $result_word['text'] = $str;
            } else if (strpos($key, "speechInfo")) {
                foreach ($str as $num => $s) {
                    if ($num == 0) {
                        $result_word['speechInfo1'] = $s;
                    } elseif ($num == 1) {
                        $result_word['speechInfo2'] = $s;
                    } elseif ($num == 2) {
                        $result_word['speechInfo3'] = $s;
                    }
                }
            } else if (strpos($key, "speech")) {
                $result_word['speech'] = $str;
            } else if (strpos($key, "conjugateType")) {
                $result_word['conjugateType'] = $str;
            } else if (strpos($key, "conjugate")) {
                $result_word['conjugate'] = $str;
            } else if (strpos($key, "original")) {
                $result_word['original'] = $str;
            } else if (strpos($key, "reading")) {
                $result_word['reading'] = $str;
            } else if (strpos($key, "pronunciation")) {
                $result_word['pronounciation'] = $str;
            }
        }
        $speech = $result_word['speech'];
        if ($speech == "助詞" || $speech == "記号" || $speech == "助動詞" || $speech == "その他,間投" || $speech == "フィラー" || $speech == "連体詞") {
            continue;
        }
        $result[$word_num] = $result_word;
    }
    return $result;
}
