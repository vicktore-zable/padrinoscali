<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/../../config/config.php';

try {
    $db = getDB();
    $stmt = $db->query("DESCRIBE eventos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
