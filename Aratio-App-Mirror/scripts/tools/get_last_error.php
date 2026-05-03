<?php
header('Content-Type: text/plain');
$logFile = __DIR__ . '/mod_lider/storage/logs/app.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    foreach (array_reverse($lines) as $line) {
        if (strpos($line, '[ERROR]') !== false) {
            echo $line;
            break;
        }
    }
}
