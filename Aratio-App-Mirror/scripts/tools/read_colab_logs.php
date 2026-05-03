<?php
header('Content-Type: text/plain');
$logFile = __DIR__ . '/mod_colab/storage/logs/app.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    echo implode("", array_slice($lines, -100));
} else {
    echo "Log file not found at $logFile";
}
