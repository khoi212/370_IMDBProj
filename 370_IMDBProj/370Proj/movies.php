<?php
global $connection;
require "database.php";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Movies</title>
    <link rel="stylesheet" href="lux.min.css">
</head>
<body>

<p><a href="index.php">Return to Home</a></p>

<?php

// helper function to handle null or empty values
function display_value($val) {
    return htmlspecialchars(($val === null || $val === '') ? 'N/A' : $val);
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // cast to int for safety

    // Basic movie info (title, year, runtime, description, etc.)
    $m_result = $connection->query("SELECT * FROM Movies WHERE movie_id = $id");
    $m = $m_result ? $m_result->fetch_array(MYSQLI_ASSOC) : null;

    if (!$m) {
        echo "<p>Movie not found.</p>";
        exit;
    }

    ?>
    <h1><?php echo display_value($m['movie_title']); ?></h1>
    <p>Year: <?php echo display_value($m['year_released']); ?></p>
    <p>Runtime: <?php echo display_value($m['runtime']); ?> minutes</p>
    <p>Description:<br><?php echo display_value($m['description']); ?></p>

    <?php
    // Director's name
    $director_id = $m['director_id'] ?? null;
    if ($director_id) {
        $d_result = $connection->query("SELECT director_fname, director_lname FROM Director WHERE director_id = $director_id");
        $d = $d_result ? $d_result->fetch_array(MYSQLI_ASSOC) : null;
        if ($d) {
            echo "<p>Director: " . display_value($d['director_fname']) . " " . display_value($d['director_lname']) . "</p>";
        }
    }

    // Genre description
    $genre_code = $m['genre_code'] ?? null;
    if ($genre_code) {
        $g_result = $connection->query("SELECT genre_description FROM Genre WHERE genre_code = '" . $connection->real_escape_string($genre_code) . "'");
        $g = $g_result ? $g_result->fetch_array(MYSQLI_ASSOC) : null;
        if ($g) {
            echo "<p>Genre: " . display_value($g['genre_description']) . "</p>";
        }
    }

    // Where you can stream the movie
    echo "<h3>Watch on:</h3><ul>";
    $p_result = $connection->query("
        SELECT s.streaming_platform
        FROM Movie_Streaming ms
        JOIN Streaming s ON ms.streaming_code = s.streaming_code
        WHERE ms.movie_id = $id
    ");
    if ($p_result) {
        while ($x = $p_result->fetch_array(MYSQLI_ASSOC)) {
            echo "<li>" . display_value($x['streaming_platform']) . "</li>";
        }
    }
    echo "</ul>";

} else {

    // NO MOVIE SPECIFIED in the URL, then show the full list of movies
    echo "<h1>All Movies</h1><ul>";
    $all_result = $connection->query("SELECT movie_id, movie_title FROM Movies ORDER BY movie_title");
    if ($all_result) {
        while ($m = $all_result->fetch_array(MYSQLI_ASSOC)) {
            // Clickable link
            echo "<li><a href='movies.php?id=" . (int)$m['movie_id'] . "'>" . display_value($m['movie_title']) . "</a></li>";
        }
    }
    echo "</ul>";
}

$connection->close();

?>

</body>
</html>
