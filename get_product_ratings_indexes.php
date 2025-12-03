<?php
require_once 'config/koneksi.php';

function getTableSchema($koneksi, $tableName) {
    $sql = "SHOW INDEX FROM " . $tableName;
    $result = $koneksi->query($sql);
    $schema = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $schema[] = $row;
        }
    }
    return $schema;
}

echo "Indexes for table 'product_ratings':\n";
print_r(getTableSchema($koneksi, 'product_ratings'));

$koneksi->close();
?>