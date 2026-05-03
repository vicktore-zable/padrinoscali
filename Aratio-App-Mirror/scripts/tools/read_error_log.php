<?php
header('Content-Type: text/plain');
$log = __DIR__ . '/php-errors.log';
if (file_exists($log)) {
    echo "--- LAST 20 LINES OF php-errors.log ---\n";
    $lines = file($log);
    $last = array_slice($lines, -20);
    echo implode('', $last);
} else {
    echo "Log not found at $log";
}
?>
