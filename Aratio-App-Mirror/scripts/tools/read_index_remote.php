<?php
header('Content-Type: text/plain');
$index = __DIR__ . '/index.php';
if (file_exists($index)) {
    echo "--- index.php START ---\n";
    echo file_get_contents($index);
    echo "\n--- index.php END ---\n";
} else {
    echo "index.php not found";
}
?>
