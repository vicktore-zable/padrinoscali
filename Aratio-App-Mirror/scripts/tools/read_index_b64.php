<?php
header('Content-Type: text/plain');
$file = __DIR__ . '/index.php';
if (file_exists($file)) {
    echo "index.php:\n";
    echo base64_encode(file_get_contents($file));
} else {
    echo "index.php NOT FOUND";
}
?>
