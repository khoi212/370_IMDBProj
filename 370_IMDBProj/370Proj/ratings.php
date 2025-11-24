<?php require "database.php"; ?>

    <!DOCTYPE html>
    <html>
    <head><title>Age Ratings</title></head>
    <body>
    <p><a href="index.php">Back to Home</a></p>

    <?php
    if (isset($_GET['id'])) {
        $code = $_GET['id'];

        // get the rating name
        $r = $connection->query("SELECT rating_description FROM Rating WHERE rating_code = $code")->fetch_array();
        echo "<h1>" . $r['rating_description'] . "</h1>";

        // check movies with this rating
        echo "<h2>Movies</h2>";
        $m = $connection->query("SELECT movie_id, movie_title, year_released FROM Movies WHERE rating_code = $code ORDER BY movie_title");

        if ($m->num_rows > 0) {
            echo "<ul>";
            while ($x = $m->fetch_array())
                echo "<li><a href='movies.php?id=" . $x['movie_id'] . "'>" . $x['movie_title'] . "</a></li>";
            echo "</ul>";
        } else {
            echo "<p>- no movies with this rating in database</p>";
        }

    } else {
        // show all ratings
        echo "<h1>All Age Ratings</h1><ul>";
        $all = $connection->query("SELECT rating_code, rating_description FROM Rating ORDER BY rating_description");
        while ($r = $all->fetch_array())
            echo "<li><a href='ratings.php?id=" . $r['rating_code'] . "'>" . $r['rating_description'] . "</a></li>";
        echo "</ul>";
    }
    ?>

    </body>
    </html>
<?php $connection->close(); ?>