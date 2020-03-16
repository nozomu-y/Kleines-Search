<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kleines Search</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <style>
        body {
            min-height: 100vh;
            position: relative;
            display: flex;
            flex-flow: column;
        }

        footer {
            margin-top: auto;
            width: 100vw;
            bottom: 0;
        }
    </style>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-133192700-7"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-133192700-7');
    </script>

</head>

<body>
    <nav class="sticky-top navbar navbar-expand-md navbar-dark shadow bg-dark py-2">
        <a class="navbar-brand" href="./">Kleines Search</a>
        <button class="navbar-toggler navbar-button" type="button" data-toggle="collapse" data-target="#navbarToggleExternalContent" aria-controls="navbarToggleExternalContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarToggleExternalContent">
            <ul class="navbar-nav mr-auto"></ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item mr-2">
                    <a class="nav-link" href="/member/">団員ページ</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="text-center">
            <h2 class="mb-3">Kleines Search</h2>

            <form action="./detail.php" method="GET" enctype="multipart/form-data" class="mb-5">
                <div class="form-group">
                    <input type="text" class="form-control" name="search_query" maxlength="32" value="<? echo $_GET['search_query']; ?>">
                </div>
                <div class="form-group">
                    <select class="form-control" name="filetype">
                        <option value="">ファイルの種類</option>
                        <?php
                        require_once(__DIR__ . "/core/dbconnect.php");
                        $query = "SELECT filetype FROM documents GROUP BY filetype";
                        $result = $mysqli->query($query);
                        if (!$result) {
                            print('<strong>Query failed:</strong> ' . $mysqli->error . 'thrown in <strong>' . __FILE__ . '</strong> on line <strong>' . __LINE__ . '</strong>');
                            $mysqli->close();
                            exit();
                        }
                        while ($row = $result->fetch_assoc()) {
                            if ($row['filetype'] != NULL) {
                                if ($_GET['filetype'] == $row['filetype']) {
                                    echo '<option value="' . $row['filetype'] . '" selected>' . $row['filetype'] . '</option>';
                                } else {
                                    echo '<option value="' . $row['filetype'] . '">' . $row['filetype'] . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
                <button class="btn btn-dark" type="submit">検索</button>
            </form>
        </div>


        <?php
        if (isset($_GET['search_query'])) {
            if ($_GET['search_query'] != NULL || $_GET['filetype'] != NULL) {
                echo '<div class="list-group mb-5">';
                require_once(__DIR__ . "/search.php");
                $result = search_detail($_GET['search_query'], $_GET['filetype']);
                foreach ($result['cost'] as $url => $cost) {
                    if ($url != "") {
                        echo '<a href="' . $url . '" class="list-group-item list-group-item-action flex-column">';
                        echo '<div class="d-flex w-100 justify-content-between">';
                        echo '<h6 class="mb-1">' . $result['title'][$url] . '</h6>';
                        echo '<small class="text-nowrap">スコア：' . $cost . '</small>';
                        echo '</div>';
                        // echo '<p class="mb-1">' . $content . '</p>';
                        if (strpos($url, "ttps://www.") !== false) {
                            $url_short = explode("https://www.", $url)[1];
                        }
                        echo '</small><small class="d-block text-truncate">' . $url_short . '</small>';
                        echo '</a>';
                    }
                }
                echo '</div>';
            }
        }
        ?>

    </div>

    <footer class="footer">
        <div class="container">
            <p class="text-muted text-center">
                形態素ごとの一致文字数をスコアとしています
            </p>
            <p class="text-muted text-center">
                <strong>Kleines Search</strong> Ver 1.0
                <br>
                Hosted on <a href="https://github.com/nozomu-y/Kleines-Search" target="_blank">GitHub</a>
            </p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script>
        $(document).on('change', ':file', function() {
            var input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
            input.parent().parent().next(':text').val(label);
        });
    </script>
</body>

</html>