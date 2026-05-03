<?php
$logFile = __DIR__ . '/mod_colab/storage/logs/app.log';
if (file_exists($logFile)) {
    echo "<h1>Last 50 lines of app.log:</h1>";
    echo "<pre>";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    echo implode("", $lastLines);
    echo "</pre>";
} else {
    echo "<h1>Log file not found</h1>";
}
