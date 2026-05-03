<?php
header('Content-Type: text/plain');
echo "CWD: " . getcwd() . "\n";
echo "Files in CWD:\n";
$files = scandir('.');
foreach ($files as $file) {
    echo $file . " (" . filesize($file) . " bytes)\n";
}
?>
