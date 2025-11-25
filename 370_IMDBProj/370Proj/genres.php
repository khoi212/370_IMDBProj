<?php
require "database.php";
?>

<!DOCTYPE html>
<link rel="stylesheet" href="lux.min.css">
<html>
<head>
    <title>Genres</title>
</head>
<body>
<p><a href="index.php">Back to Home</a></p>

<?php
if (isset($_GET['id'])) {
    $code = $_GET['id'];



    //Show genre desc.
    $g = $connection->query("SELECT genre_description FROM Genre WHERE genre_code = $code")->fetch_array();

    echo "<h1>" . htmlspecialchars($g['genre_description']) . "</h1>";

    // Shows all movies in specified genre
    echo "<h2>Movies</h2><ul>";
    $m = $connection->query("SELECT movie_id, movie_title, year_released 
 FROM Movies 
 WHERE genre_code = $code 
ORDER BY movie_title");

    // CONVERT SELECTIONS INTO CLICKABLE LINK
    while ($x = $m->fetch_array()) {
        echo "<li><a href='movies.php?id=" . $x['movie_id'] . "'>"
                . htmlspecialchars($x['movie_title']) . " </a></li>";
    }
    echo "</ul>";

} else
{
    //IF NO GENRE ID GIVE, THEN SHOW ALL GENRES
    echo "<h1>All Genres</h1><ul>";

    // Gets all genres from the DB, ordered in alphabetical order
    $all = $connection->query("SELECT genre_code, genre_description 
        FROM Genre 
        ORDER BY genre_description");

    // Displays each genre as a link
    while ($g = $all->fetch_array()) {
        echo "<li><a href='genres.php?id=" . $g['genre_code'] . "'>"
                . htmlspecialchars($g['genre_description']) . "</a></li>";
    }
    echo "</ul>";
}
?>

</body>
</html>





<?php
$connection->close();
?>


