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
            <input type="file" name="csv_file" id="import3_csv" accept=".csv" class="form-control" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Run Import 3</button>
    </form>

<?php
else:

    $importType = $_POST['import_type'] ?? '';
    if (!in_array($importType, ['1', '2', '3'], true)) {
        echo "<div class='alert alert-danger'>Invalid import type.</div>";
        exit;
    }

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        echo "<div class='alert alert-danger'>Error uploading file.</div>";
        exit;
    }

    $filename = $_FILES['csv_file']['name'];
    $tmpPath  = $_FILES['csv_file']['tmp_name'];

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        echo "<div class='alert alert-danger'>Only .csv files are allowed.</div>";
        exit;
    }

    if (($handle = fopen($tmpPath, 'r')) === false) {
        echo "<div class='alert alert-danger'>Could not open uploaded file.</div>";
        exit;
    }

    $summary = [];

    /* ========== IMPORT TYPE 1: Directors, Actors, Movies ========== */
    if ($importType === '1') {
        global $connection;

        $stmtDirector = $connection->prepare(
                "INSERT INTO Director (director_id, director_fname, director_lname, director_minit, avg_rating)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                director_fname = VALUES(director_fname),
                director_lname = VALUES(director_lname),
                director_minit = VALUES(director_minit),
                avg_rating     = VALUES(avg_rating)"
        );

        $stmtActor = $connection->prepare(
                "INSERT INTO Actor (actor_id, fname, lname, minit, DOB)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                fname = VALUES(fname),
                lname = VALUES(lname),
                minit = VALUES(minit),
                DOB   = VALUES(DOB)"
        );

        $stmtMovie = $connection->prepare(
                "INSERT INTO Movies (movie_id, movie_title, director_id, year_released,
                                 genre_code, rating_code, original_language_code,
                                 book_id, runtime, description)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                movie_title            = VALUES(movie_title),
                director_id            = VALUES(director_id),
                year_released          = VALUES(year_released),
                genre_code             = VALUES(genre_code),
                rating_code            = VALUES(rating_code),
                original_language_code = VALUES(original_language_code),
                book_id                = VALUES(book_id),
                runtime                = VALUES(runtime),
                description            = VALUES(description)"
        );

        if (!$stmtDirector || !$stmtActor || !$stmtMovie) {
            fclose($handle);
            echo "<div class='alert alert-danger'>Failed to prepare SQL statements for Import 1.</div>";
            exit;
        }

        $section = null;
        $summary = [
                'director_inserted' => 0,
                'director_updated'  => 0,
                'director_skipped'  => 0,
                'actor_inserted'    => 0,
                'actor_updated'     => 0,
                'actor_skipped'     => 0,
                'movies_inserted'   => 0,
                'movies_updated'    => 0,
                'movies_skipped'    => 0,
        ];

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (row_is_empty($row)) continue;

            $norm = normalize_header($row);

            if ($norm === ['director_id','director_fname','director_lname','director_minit','AVG_rating']) {
                $section = 'director';
                continue;
            }
            if ($norm === ['actor_id','fname','lname','minit','DOB']) {
                $section = 'actor';
                continue;
            }
            if ($norm === ['movie_id','movie_title','director_id','year_released',
                            'genre_code','rating_code','language_code','book_id','runtime','description']) {
                $section = 'movies';
                continue;
            }

            if ($section === 'director') {
                if (count($row) < 5) { $summary['director_skipped']++; continue; }

                $director_id    = (int)trim((string)$row[0]);
                $director_fname = trim((string)$row[1]);
                $director_lname = trim((string)$row[2]);
                $director_minit = substr(trim((string)$row[3]), 0, 1);
                $avg_rating     = (float)trim((string)$row[4]);

                if ($director_id === 0) { $summary['director_skipped']++; continue; }

                $stmtDirector->bind_param(
                        "isssd",
                        $director_id,
                        $director_fname,
                        $director_lname,
                        $director_minit,
                        $avg_rating
                );

                if (!$stmtDirector->execute()) {
                    $summary['director_skipped']++;
                    continue;
                }

                if ($stmtDirector->affected_rows === 1) {
                    $summary['director_inserted']++;
                } elseif ($stmtDirector->affected_rows === 2) {
                    $summary['director_updated']++;
                } else {
                    $summary['director_skipped']++;
                }

            } elseif ($section === 'actor') {
                if (count($row) < 5) { $summary['actor_skipped']++; continue; }

                $actor_id = (int)trim((string)$row[0]);
                $fname    = trim((string)$row[1]);
                $lname    = trim((string)$row[2]);
                $minit    = substr(trim((string)$row[3]), 0, 1);

                $dobRaw = trim((string)$row[4]);
                if ($dobRaw === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dobRaw)) {
                    $dob = null;
                } else {
                    $dob = $dobRaw;
                }

                if ($actor_id === 0) { $summary['actor_skipped']++; continue; }

                $stmtActor->bind_param(
                        "issss",
                        $actor_id,
                        $fname,
                        $lname,
                        $minit,
                        $dob
                );

                if (!$stmtActor->execute()) {
                    $summary['actor_skipped']++;
                    continue;
                }

                if ($stmtActor->affected_rows === 1) {
                    $summary['actor_inserted']++;
                } elseif ($stmtActor->affected_rows === 2) {
                    $summary['actor_updated']++;
                } else {
                    $summary['actor_skipped']++;
                }

            } elseif ($section === 'movies') {
                if (count($row) < 10) { $summary['movies_skipped']++; continue; }

                $movie_id    = (int)trim((string)$row[0]);
                $title       = trim((string)$row[1]);
                $director_id = (int)trim((string)$row[2]);
                $year        = trim((string)$row[3]);
                $genre_code  = trim((string)$row[4]);
                $rating_code = trim((string)$row[5]);
                $lang_code   = trim((string)$row[6]);
                $book_id_raw = trim((string)$row[7]);
                $runtime     = (int)trim((string)$row[8]);
                $desc        = trim((string)$row[9]);

                if ($movie_id === 0) { $summary['movies_skipped']++; continue; }

                $book_id_param = ($book_id_raw === '') ? null : (int)$book_id_raw;

                $stmtMovie->bind_param(
                        "isisiisiis",
                        $movie_id,
                        $title,
                        $director_id,
                        $year,
                        $genre_code,
                        $rating_code,
                        $lang_code,
                        $book_id_param,
                        $runtime,
                        $desc
                );

                if (!$stmtMovie->execute()) {
                    $summary['movies_skipped']++;
                    continue;
                }

                if ($stmtMovie->affected_rows === 1) {
                    $summary['movies_inserted']++;
                } elseif ($stmtMovie->affected_rows === 2) {
                    $summary['movies_updated']++;
                } else {
                    $summary['movies_skipped']++;
                }
            }
        }

        fclose($handle);
        $stmtDirector->close();
        $stmtActor->close();
        $stmtMovie->close();

        echo "<div class='alert alert-success'><strong>Import 1 complete.</strong></div>";
        echo "<ul>";
        echo "<li>Directors – Inserted: {$summary['director_inserted']}, Updated: {$summary['director_updated']}, Skipped: {$summary['director_skipped']}</li>";
        echo "<li>Actors – Inserted: {$summary['actor_inserted']}, Updated: {$summary['actor_updated']}, Skipped: {$summary['actor_skipped']}</li>";
        echo "<li>Movies – Inserted: {$summary['movies_inserted']}, Updated: {$summary['movies_updated']}, Skipped: {$summary['movies_skipped']}</li>";
        echo "</ul>";

    /* ========== IMPORT TYPE 2: Movie_awards, Movie_streaming ========== */
    } elseif ($importType === '2') {
        global $connection;

        $stmtMovieAwards = $connection->prepare(
                "INSERT INTO Movie_awards (movie_id, award_id)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE
                award_id = VALUES(award_id)"
        );

        $stmtMovieStreaming = $connection->prepare(
                "INSERT INTO Movie_streaming (movie_id, streaming_code)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE
                streaming_code = VALUES(streaming_code)"
        );

        if (!$stmtMovieAwards || !$stmtMovieStreaming) {
            fclose($handle);
            echo "<div class='alert alert-danger'>Failed to prepare SQL statements for Import 2.</div>";
            exit;
        }

        $section = null;
        $summary = [
                'awards_inserted'    => 0,
                'awards_updated'     => 0,
                'awards_skipped'     => 0,
                'streaming_inserted' => 0,
                'streaming_updated'  => 0,
                'streaming_skipped'  => 0,
        ];

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (row_is_empty($row)) continue;

            $norm = normalize_header($row);

            if ($norm === ['id','movie_id','award_id']) {
                $section = 'movie_awards';
                continue;
            }
            if ($norm === ['id','movie_id','streaming_code']) {
                $section = 'movie_streaming';
                continue;
            }

            if ($section === 'movie_awards') {
                if (count($row) < 3) { $summary['awards_skipped']++; continue; }

                $movie_id = (int)trim((string)$row[1]);
                $award_id = (int)trim((string)$row[2]);

                if ($movie_id === 0 || $award_id === 0) {
                    $summary['awards_skipped']++;
                    continue;
                }

                $stmtMovieAwards->bind_param("ii", $movie_id, $award_id);

                if (!$stmtMovieAwards->execute()) {
                    $summary['awards_skipped']++;
                    continue;
                }

                if ($stmtMovieAwards->affected_rows === 1) {
                    $summary['awards_inserted']++;
                } elseif ($stmtMovieAwards->affected_rows === 2) {
                    $summary['awards_updated']++;
                } else {
                    $summary['awards_skipped']++;
                }

            } elseif ($section === 'movie_streaming') {
                if (count($row) < 3) { $summary['streaming_skipped']++; continue; }

                $movie_id       = (int)trim((string)$row[1]);
                $streaming_code = trim((string)$row[2]);

                if ($movie_id === 0 || $streaming_code === '') {
                    $summary['streaming_skipped']++;
                    continue;
                }

                $stmtMovieStreaming->bind_param("is", $movie_id, $streaming_code);

                if (!$stmtMovieStreaming->execute()) {
                    $summary['streaming_skipped']++;
                    continue;
                }

                if ($stmtMovieStreaming->affected_rows === 1) {
                    $summary['streaming_inserted']++;
                } elseif ($stmtMovieStreaming->affected_rows === 2) {
                    $summary['streaming_updated']++;
                } else {
                    $summary['streaming_skipped']++;
                }
            }
        }

        fclose($handle);
        $stmtMovieAwards->close();
        $stmtMovieStreaming->close();

        echo "<div class='alert alert-success'><strong>Import 2 complete.</strong></div>";
        echo "<ul>";
        echo "<li>Movie Awards – Inserted: {$summary['awards_inserted']}, Updated: {$summary['awards_updated']}, Skipped: {$summary['awards_skipped']}</li>";
        echo "<li>Movie Streaming – Inserted: {$summary['streaming_inserted']}, Updated: {$summary['streaming_updated']}, Skipped: {$summary['streaming_skipped']}</li>";
        echo "</ul>";

    /* ========== IMPORT TYPE 3: Genre, Rating, Language ========== */
    } elseif ($importType === '3') {
        global $connection;

        $stmtGenre = $connection->prepare(
                "INSERT INTO Genre (genre_code, genre_description)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE
                genre_description = VALUES(genre_description)"
        );

        $stmtRating = $connection->prepare(
                "INSERT INTO Rating (rating_code, rating_description)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE
                rating_description = VALUES(rating_description)"
        );

        $stmtLanguage = $connection->prepare(
                "INSERT INTO Language (original_language_code, language_description)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE
                language_description = VALUES(language_description)"
        );

        if (!$stmtGenre || !$stmtRating || !$stmtLanguage) {
            fclose($handle);
            echo "<div class='alert alert-danger'>Failed to prepare SQL statements for Import 3.</div>";
            exit;
        }

        $section = null;
        $summary = [
                'genre_inserted'    => 0,
                'genre_updated'     => 0,
                'genre_skipped'     => 0,
                'rating_inserted'   => 0,
                'rating_updated'    => 0,
                'rating_skipped'    => 0,
                'language_inserted' => 0,
                'language_updated'  => 0,
                'language_skipped'  => 0,
        ];

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (row_is_empty($row)) continue;

            $norm = normalize_header($row);

            if ($norm === ['genre_code','genre_description']) {
                $section = 'genre';
                continue;
            }
            if ($norm === ['rating_code','rating_description']) {
                $section = 'rating';
                continue;
            }
            if ($norm === ['language_code','language_description']) {
                $section = 'language';
                continue;
            }

            if ($section === 'genre') {
                if (count($row) < 2) { $summary['genre_skipped']++; continue; }

                $code = trim((string
