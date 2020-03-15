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
</head>

<body>
    <nav class="navbar navbar-dark bg-dark mb-3">
        <a class="navbar-brand" href="./">Kleines Search</a>
    </nav>

    <div class="container">
        <div class="text-center">
            <h2 class="mb-3">Kleines Search</h2>

            <form action="./" method="GET" enctype="multipart/form-data" class="mb-5">
                <div class="form-group">
                    <input type="text" class="form-control" name="search_query" maxlength="32" value="<? echo $_GET['search_query']; ?>">
                </div>
                <button type="submit" class="btn btn-dark">検索</button>
            </form>
        </div>


        <?php
        if (isset($_GET['search_query'])) {
            if ($_GET['search_query'] != NULL) {
                echo '<div class="list-group mb-5">';
                require_once(__DIR__ . "/search.php");
                $result = search($_GET['search_query']);
                foreach ($result['cost'] as $url => $cost) {
                    echo '<a href="' . $url . '" class="list-group-item list-group-item-action flex-column">';
                    echo '<div class="d-flex w-100 justify-content-between">';
                    echo '<h5 class="mb-1">' . $result['title'][$url] . '</h5>';
                    echo '<small>コスト：' . $cost . '</small>';
                    echo '</div>';
                    // echo '<p class="mb-1">' . $content . '</p>';
                    $url = explode("https://www.", $url)[1];
                    echo '<small class="d-block text-truncate">' . $url . '</small>';
                    echo '</a>';
                }
                echo '</div>';
            }
        }
        ?>

    </div>

    <footer class="footer">
        <div class="container">
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