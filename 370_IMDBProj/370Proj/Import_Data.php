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
// If no form submission yet, just show the THREE import forms
if (!isset($_POST['submit'])): ?>

    <h2>Import 1: Movies</h2>
    <form action="Import_Data.php" method="post" enctype="multipart/form-data" class="form-group">
        <input type="hidden" name="table" value="movies">
        <div class="form-group">
            <label for="movies_csv">Movies CSV
                <small>(columns like: movie_id,movie_title,director_id,year_released,genre_code,rating_code,original_language_code,book_id,runtime,description)</small>
            </label>
            <input type="file" name="csv_file" id="movies_csv" accept=".csv" class="form-control" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Import Movies</button>
    </form>

    <hr>

    <h2>Import 2: Actors</h2>
    <form action="Import_Data.php" method="post" enctype="multipart/form-data" class="form-group">
        <input type="hidden" name="table" value="actor">
        <div class="form-group">
            <label for="actors_csv">Actors CSV
                <small>(columns: actor_id,fname,lname,minit,DOB)</small>
            </label>
            <input type="file" name="csv_file" id="actors_csv" accept=".csv" class="form-control" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Import Actors</button>
    </form>

    <hr>

    <h2>Import 3: Casting (Movie ↔ Actor)</h2>
    <form action="Import_Data.php" method="post" enctype="multipart/form-data" class="form-group">
        <input type="hidden" name="table" value="casting">
        <div class="form-group">
            <label for="casting_csv">Casting CSV
                <small>(columns: movie_id,actor_id)</small>
            </label>
            <input type="file" name="csv_file" id="casting_csv" accept=".csv" class="form-control" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Import Casting</button>
    </form>

<?php
// --- PROCESSING LOGIC ---
else:

    // 1. Validate table name (whitelist so nobody can inject)
    // Only allow the THREE tables required for the project imports
    $allowedTables = [
            "movies",
            "actor",
            "casting"
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