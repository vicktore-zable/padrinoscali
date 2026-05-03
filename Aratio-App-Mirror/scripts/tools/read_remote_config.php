<?php
header("Content-Type: text/plain");
$file = __DIR__ . "/config/config.php";
if (file_exists($file)) {
    $content = file($file);
    foreach ($content as $line) {
        if (strpos($line, "SMTP_PASS") !== false) {
            echo $line;
        }
    }
}
?>