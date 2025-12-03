<?php
require_once 'config/koneksi.php';

$sql_file = 'update_db_schema.sql';
$sql_content = file_get_contents($sql_file);

if ($sql_content === false) {
    die("Error reading SQL file: " . $sql_file);
}

// Split SQL statements by semicolon, but be careful with semicolons inside comments or strings
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

foreach ($statements as $statement) {
    if (!empty($statement)) {
        if ($koneksi->query($statement) === TRUE) {
            echo "Query executed successfully: " . substr($statement, 0, 50) . "...\n";
        } else {
            echo "Error executing query: " . $koneksi->error . " in statement: " . substr($statement, 0, 50) . "...\n";
            // Optionally, stop on first error
            // die();
        }
    }
}

$koneksi->close();
echo "Database schema update complete.\n";
?>
