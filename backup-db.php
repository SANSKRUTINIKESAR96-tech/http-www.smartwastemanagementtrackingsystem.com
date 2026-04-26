<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_role('admin');

/**
 * A simple PHP-based MySQL backup utility.
 */

$tables = [];
$res = db()->query("SHOW TABLES");
while ($row = $res->fetch_row()) { $tables[] = $row[0]; }

$sql = "-- EcoTrack Database Backup\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {
    // Structure
    $res = db()->query("SHOW CREATE TABLE `$table` text");
    $row = $res->fetch_assoc();
    $sql .= "\n\n" . $row['Create Table'] . ";\n\n";

    // Data
    $res = db()->query("SELECT * FROM `$table` text");
    while ($row = $res->fetch_assoc()) {
        $keys = array_map(fn($k) => "`$k`", array_keys($row));
        $vals = array_map(fn($v) => $v === null ? "NULL" : "'" . db()->real_escape_string($v) . "'", array_values($row));
        $sql .= "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $vals) . ");\n";
    }
}

$sql .= "\n\nSET FOREIGN_KEY_CHECKS=1;";

$filename = "backup_" . DB_NAME . "_" . date('Y-m-d_H-i-s') . ".sql";
$path = __DIR__ . '/../../backups/' . $filename;

// Save to server
file_put_contents($path, $sql);

// Force download to browser
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename=' . $filename);
echo $sql;
exit;
