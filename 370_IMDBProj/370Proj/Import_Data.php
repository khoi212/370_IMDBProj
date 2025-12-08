<?php
// Import_Data.php
require "Database.php";   // provides $connection (mysqli)
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Import Data</title>
    <link rel="stylesheet" href="lux.min.css">
</head>
<body>
<p><a href="index.php">Back to Home</a></p>
<h1>IMPORT CSV DATA</h1>

<?php
// ----------------- Helper functions -----------------

function row_is_empty(array $row): bool {
    foreach ($row as $cell) {
        if (trim((string)$cell) !== '') {
            return false;
        }
    }
    return true;
}

function normalize_header(array $row): array {
    $out = [];
    foreach ($row as $cell) {
        $out[] = trim((string)$cell);
    }
    return $out;
}

// ----------------- Show forms -----------------
if (!isset($_POST['submit'])): ?>

    <h2>Import 1: Directors, Actors, Movies</h2>
    <form action="Import_Data.php" method="post" enctype="multipart/form-data" class="form-group">
        <input type="hidden" name="import_type" value="1">
        <div class="form-group">
            <label for="import1_csv">Import 1 CSV (CS370-Project1-Import1.csv)</label>
            <input type="file" name="csv_file" id="import1_csv" accept=".csv" class="form-control" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Run Import 1</button>
    </form>

    <hr>

    <h2>Import 2: Movie Awards, Movie Streaming</h2>
    <form action="Import_Data.php" method="post" enctype="multipart/form-data" class="form-group">
        <input type="hidden" name="import_type" value="2">
        <div class="form-group">
            <label for="import2_csv">Import 2 CSV</label>
            <input type="file" name="csv_file" id="import2_csv" accept=".csv" class="form-control" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Run Import 2</button>
    </form>

    <hr>

    <h2>Import 3: Genres, Ratings, Languages</h2>
    <form action="Import_Data.php" method="post" enctype="multipart/form-data" class="form-group">
        <input type="hidden" name="import_type" value="3">
        <div class="form-group">
            <label for="import3_csv">Import 3 CSV</label>
            <input type="file" name="csv_file" id_
