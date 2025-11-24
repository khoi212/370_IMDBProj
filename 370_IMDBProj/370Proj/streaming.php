<?php require "database.php"; ?>

    <!DOCTYPE html>
    <html>
    <head><title>Streaming Services</title></head>
    <body>
    <p><a href="index.php">← Back to Home</a></p>

    <?php
    if (isset($_GET['id'])) {
        $code = $_GET['id'];                 // get the code from the URL

        // pull the name of streaming platform
        $s = $connection->query("SELECT streaming_platform FROM Streaming WHERE streaming_code = $code")->fetch_array();
        echo "<h1>" . htmlspecialchars($s['streaming_platform']) . "</h1>"; // show it big!

        // list every movie you can watch on this service
        echo "<h2>Available Movies</h2><ul>";
        $m = $connection->query(
       "SELECT m.movie_id, m.movie_title, m.year_released 
    FROM Movie_Streaming ms 
    JOIN Movies m ON ms.movie_id = m.movie_id 
    WHERE ms.streaming_code = $code");

        while ($x = $m->fetch_array())

        {
            // each movie links to its own detail page (like if clicking "movie" from main page
            echo "<li><a href='movies.php?id=" . $x['movie_id'] . "'>"
                    . htmlspecialchars($x['movie_title']) . " </a></li>";
        }
        echo "</ul>";

    } else {              // no id provided shows the full list of services
        echo "<h1>All Streaming Services</h1><ul>";
        $all = $connection->query("SELECT streaming_code, streaming_platform 
                               FROM Streaming 
                               ORDER BY streaming_platform");

        while ($s = $all->fetch_array()) {
            // clicking a a link will reload this page with ?id=code(from URL)
            echo "<li><a href='streaming.php?id=" . $s['streaming_code'] . "'>"
                    . htmlspecialchars($s['streaming_platform']) . "</a></li>";
        }


        echo "</ul>";
    }
    ?>

    </body>
    </html>

<?php $connection->close(); ?>