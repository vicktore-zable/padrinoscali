<?php
header('Content-Type: text/plain');
$file = __DIR__ . '/mod_colab/src/Controllers/ColaboradorController.php';
if (file_exists($file)) {
    echo "--- ColaboradorController.php START ---\n";
    echo file_get_contents($file);
    echo "\n--- ColaboradorController.php END ---\n";
} else {
    echo "File not found: " . $file;
}
?>
