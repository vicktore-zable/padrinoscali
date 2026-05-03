<?php
$logFile = __DIR__ . '/route_log.txt';
if (file_exists($logFile)) {
    echo "<pre>" . htmlspecialchars(file_get_contents($logFile)) . "</pre>";
} else {
    echo "Log file not found at: $logFile";
}
?>
