<?php require "database.php"; ?>

    <!DOCTYPE html>
    <link rel="stylesheet" href="lux.min.css">
    <html>
    <head><title>Actors</title></head>
    <body>
    <p><a href="index.php">Back to Home</a></p>

    <?php
    if (isset($_GET['id'])) {
        $id = $_GET['id'];        // which actor is selected

        // grab the actor basic info
        $a = $connection->query("SELECT fname, lname, minit, DOB FROM Actor WHERE actor_id = $id")->fetch_array();

        // show full name (handles missing middle initial)
        echo "<h1>" . $a['fname'] . " " . ($a['minit'] ? $a['minit']." " : "") . $a['lname'] . "</h1>";
        echo "<p>Born: " . $a['DOB'] . "</p>";

        // list all movies this actor was in
        echo "<h2>Movies</h2><ul>";
        $m = $connection->query("SELECT m.movie_id, m.movie_title, m.year_released 
      FROM Casting c 
      JOIN Movies m ON c.movie_id = m.movie_id 
      WHERE c.actor_id = $id 
      ORDER BY m.year_released DESC");
        while ($x = $m->fetch_array()) {
            echo "<li><a href='movies.php?id=" . $x['movie_id'] . "'>"
                    . htmlspecialchars($x['movie_title']) . " </a></li>";
        }
        echo "</ul>";

    } else {
        // no id given willshow every actor
        echo "<h1>All Actors</h1><ul>";
        $all = $connection->query("SELECT actor_id, fname, lname, minit FROM Actor ORDER BY lname");
        while ($a = $all->fetch_array()) {
            $name = $a['fname'] . " " . ($a['minit'] ? $a['minit']." " : "") . $a['lname'];
            echo "<li><a href='actors.php?id=" . $a['actor_id'] . "'>"
                    . htmlspecialchars($name) . "</a></li>";
        }
        echo "</ul>";
    }
    ?>

    </body>
    </html>


<?php $connection->close(); ?>
