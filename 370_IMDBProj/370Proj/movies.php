<?php require "database.php"; ?>


    <!DOCTYPE html>
    <link rel="stylesheet" href="lux.min.css">
    <html>
    <head><title>Movies</title></head>
    <body>

    <p><a href="index.php">Return to Home</a></p>


    <?php

    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Basic movie info (title, year, runtime, description, etc.)
        $m = $connection->query("SELECT * FROM Movies WHERE movie_id = $id")->fetch_array();
        ?>
        <h1><?php echo $m['movie_title']; ?></h1>
        <p>Year: <?php echo $m['year_released']; ?></p>
        <p>Runtime: <?php echo $m['runtime']; ?> minutes</p>
        <p>Description:<br><?php echo  ($m['description']); ?></p>

        <?php



        //Directors name
        $d = $connection->query("SELECT director_fname, director_lname FROM Director WHERE director_id = " . $m['director_id'])->fetch_array();
        echo "<p>Director: " . $d['director_fname'] . " " . $d['director_lname'] . "</p>";


        // Get the genre description
        $g = $connection->query("SELECT genre_description FROM Genre WHERE genre_code = " . $m['genre_code'])->fetch_array();
        echo "<p>Genre: " . $g['genre_description'] . "</p>";


        // where you can stream  movie
        echo "<h3>Watch on:</h3><ul>";
        $p = $connection->query("SELECT streaming_platform 
  FROM Movie_Streaming ms 
      JOIN Streaming s ON ms.streaming_code = s.streaming_code 
                             WHERE ms.movie_id = $id");
        while ($x = $p->fetch_array()) {
            echo "<li>" . $x['streaming_platform'] . "</li>";
        }
        echo "</ul>";


        // List the actors

        echo "<h3>Cast</h3><ul>";
        $c = $connection->query("SELECT fname, lname, minit 
                             FROM Casting c 
                             JOIN Actor a ON c.actor_id = a.actor_id 
                             WHERE c.movie_id = $id");
        while ($a = $c->fetch_array()) {
            echo "<li>" . $a['fname'] . " " . ($a['minit'] ? $a['minit']." " : "") . $a['lname'] . "</li>";
        }
        echo "</ul>";

    } else {

        //NO MOVIE SPECIFIED in the URL thenn show the full list of movies

        echo "<h1>All Movies</h1><ul>";
        $all = $connection->query("SELECT movie_id, movie_title FROM Movies ORDER BY movie_title");
        while ($m = $all->fetch_array()) {
            // Clickable link
            echo "<li><a href='movies.php?id=" . $m['movie_id'] . "'>" . $m['movie_title'] . "</a></li>";
        }
        echo "</ul>";
    }
    ?>

    </body>
    </html>

<?php

$connection->close();

?>
