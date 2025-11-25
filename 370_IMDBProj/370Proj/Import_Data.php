<?php
// Import_Data.php
require "Database.php";   // uses $connection from your existing file
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
<h1>Import CSV Data</h1>

<?php
// If no form submission yet, just show the form
if (!isset($_POST['submit'])): ?>

    <form action="Import_Data.php" method="post" enctype="multipart/form-data" class="form-group">
        <div class="form-group">
            <label for="table">Select table to import into:</label>
            <select name="table" id="table" class="form-control" required>
                <option value="">-- Choose Table --</option>
                <!-- values are the *actual* table names from the DB -->
                <option value="actor">Actors</option>
                <option value="director">Directors</option>
                <option value="genre">Genres</option>
                <option value="rating">Ratings</option>
                <option value="streaming">Streaming Platforms</option>
                <option value="movies">Movies</option>
                <option value="casting">Casting (movie ↔ actor)</option>
                <option value="movie_streaming">Movie_Streaming (movie ↔ streaming)</option>
                <option value="award">Awards</option>
                <option value="movie_awards">Movie_Awards (movie ↔ award)</option>
                <option value="language">Language</option>
                <option value="based_on_book">Based_On_Book</option>
            </select>
        </div>

        <div class="form-group">
            <label for="csv_file">CSV file:</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-control" required>
        </div>

        <button type="submit" name="submit" class="btn btn-primary">Import</button>
    </form>

<?php
// --- PROCESSING LOGIC ---
else:

    // 1. Validate table name (whitelist so nobody can inject)
    $allowedTables = [
        "actor",
        "director",
        "genre",
        "rating",
        "streaming",
        "movies",
        "casting",
        "movie_streaming",
        "award",
        "movie_awards",
        "language",
        "based_on_book"
    ];

    $table = $_POST['table'] ?? '';

    if (!in_array($table, $allowedTables, true)) {
        echo "<div class='alert alert-danger'>Invalid table selected.</div>";
        exit;
    }

    // 2. Check for upload errors
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        echo "<div class='alert alert-danger'>Error uploading file.</div>";
        exit;
    }

    $filename = $_FILES['csv_file']['name'];
    $tmpPath  = $_FILES['csv_file']['tmp_name'];

    // 3. Ensure file is CSV
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        echo "<div class='alert alert-danger'>Only .csv files are allowed.</div>";
        exit;
    }

    // 4. Open the CSV
    if (($handle = fopen($tmpPath, 'r')) === false) {
        echo "<div class='alert alert-danger'>Could not open uploaded file.</div>";
        exit;
    }

    // 5. Read header row – must contain column names matching your DB
    $header = fgetcsv($handle);
    if ($header === false || count($header) === 0) {
        fclose($handle);
        echo "<div class='alert alert-danger'>CSV file appears to be empty or has no header row.</div>";
        exit;
    }

    // Clean & validate column names
    $columns = [];
    foreach ($header as $col) {
        $col = trim($col);
        if ($col === '') continue;

        // only allow simple names like movie_id, rating_code, etc.
        if (!preg_match('/^[A-Za-z0-9_]+$/', $col)) {
            fclose($handle);
            echo "<div class='alert alert-danger'>Invalid column name in header: " . htmlspecialchars($col) . "</div>";
            exit;
        }
        $columns[] = $col;
    }

    if (empty($columns)) {
        fclose($handle);
        echo "<div class='alert alert-danger'>No valid columns found in header row.</div>";
        exit;
    }

    // Build query pieces
    $colList      = implode(",", $columns);
    $placeholders = implode(",", array_fill(0, count($columns), "?"));

    // ON DUPLICATE KEY UPDATE: update all non-key columns to new values
    $updates = [];
    foreach ($columns as $col) {
        $updates[] = "$col = VALUES($col)";
    }
    $updateClause = implode(", ", $updates);

    $sql = "INSERT INTO $table ($colList) VALUES ($placeholders)
            ON DUPLICATE KEY UPDATE $updateClause";

    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        fclose($handle);
        echo "<div class='alert alert-danger'>Failed to prepare statement: " . htmlspecialchars($connection->error) . "</div>";
        exit;
    }

    // Bind everything as strings; MySQL will cast to int/year/date where needed
    $types = str_repeat('s', count($columns));

    $inserted = 0;
    $updated  = 0;
    $skipped  = 0;
    $lineNum  = 1; // header already read

    while (($row = fgetcsv($handle)) !== false) {
        $lineNum++;

        if (count($row) == 1 && trim($row[0]) === '') {
            // skip completely empty lines
            continue;
        }

        // Ensure row has at least as many columns; pad with nulls if short
        $row = array_pad($row, count($columns), null);

        // Trim values
        $values = [];
        foreach ($row as $val) {
            $values[] = $val === null ? null : trim($val);
        }

        // Set up bind_param arguments (needs references)
        $bindParams = [$types];
        foreach ($values as $key => $val) {
            $bindParams[] = &$values[$key];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindParams);

        if (!$stmt->execute()) {
            // row failed – count as skipped
            $skipped++;
            continue;
        }

        // 1 row = insert, 2 rows = insert+update existing
        if ($stmt->affected_rows === 1) {
            $inserted++;
        } elseif ($stmt->affected_rows === 2) {
            $updated++;
        } else {
            // 0 rows affected = data identical or nothing changed
            $skipped++;
        }
    }

    fclose($handle);
    $stmt->close();
    $connection->close();

    echo "<div class='alert alert-success'><strong>Import complete for table <code>$table</code>.</strong></div>";
    echo "<ul>";
    echo "<li>Inserted: $inserted</li>";
    echo "<li>Updated (existing rows): $updated</li>";
    echo "<li>Skipped / unchanged / errors: $skipped</li>";
    echo "</ul>";

endif;
?>

</body>
</html>