<?php
header("Content-Type: text/plain");
$file = "/home/u156469157/domains/aratio.mrmtech.net/public_html/php-errors.log";
if (file_exists($file)) {
    $data = file_get_contents($file);
    echo substr($data, -5000);
} else {
    echo "Log file not found.";
}
?>