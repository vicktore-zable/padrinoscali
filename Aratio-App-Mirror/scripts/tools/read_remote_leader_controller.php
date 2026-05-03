<?php
header('Content-Type: text/plain');
$file = __DIR__ . '/mod_colab/src/Controllers/LeaderPortalController.php';
if (file_exists($file)) {
    echo "--- LeaderPortalController.php START ---\n";
    echo file_get_contents($file);
    echo "\n--- LeaderPortalController.php END ---\n";
} else {
    echo "File not found: " . $file;
}
?>
