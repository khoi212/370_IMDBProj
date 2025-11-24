<?php
$connection = new mysqli("localhost", "root", "", "imdb");

if ($connection->connect_error) {
    die("Cannot connect to database");
}
?>