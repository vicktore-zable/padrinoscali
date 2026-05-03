<?php
header('Content-Type: text/plain');
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    echo $file . " (" . filesize($file) . " bytes)\n";
}
?>
