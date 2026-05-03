<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();

echo "--- VIEWS IN DATABASE ---\n";
$stmt = $db->query("SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW'");
$views = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($views as $view) {
    $viewName = array_values($view)[0];
    echo "\n=== View: $viewName ===\n";
    try {
        $check = $db->query("SELECT * FROM $viewName LIMIT 1");
        echo "OK\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}
