<?php require "database.php"; ?>

    <!DOCTYPE html>
    <link rel="stylesheet" href="lux.min.css">
    <html>
    <head><title>Directors </title></head>
    <body>
    <p><a href="index.php">Back to Home</a></p>

    <?php
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Get director name
        $d = $connection->query("SELECT director_fname, director_lname, director_minit FROM Director WHERE director_id = $id")->fetch_array();
        $name = $d['director_fname'] . " " . ($d['director_minit'] ? $d['director_minit']." " : "") . $d['director_lname'];

        echo "<h1>$name</h1>";

        // Show all movies by this director
        echo "<h2>Movies Directed</h2><ul>";
        $m = $connection->query("SELECT movie_id, movie_title, year_released FROM Movies WHERE director_id = $id ORDER BY year_released DESC");
        while ($x = $m->fetch_array())
            echo "<li><a href='movies.php?id=" . $x['movie_id'] . "'>" . $x['movie_title'] . "</a> (" . $x['year_released'] . ")</li>";
        echo "</ul>";

    } else {
        // Show list of all directors
        echo "<h1>All Directors</h1><ul>";
        $all = $connection->query("SELECT director_id, director_fname, director_lname, director_minit FROM Director ORDER BY director_lname");
        while ($d = $all->fetch_array()) {
            $name = $d['director_fname'] . " " . ($d['director_minit'] ? $d['director_minit']." " : "") . $d['director_lname'];
            echo "<li><a href='directors.php?id=" . $d['director_id'] . "'>$name</a></li>";
        }
        echo "</ul>";
    }
    ?>

    </body>
    </html>

<?php  $connection->close();?>
