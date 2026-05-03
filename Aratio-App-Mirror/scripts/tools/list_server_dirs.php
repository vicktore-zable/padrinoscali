<?php
header('Content-Type: text/plain');
echo "Directorio actual: " . __DIR__ . "\n";
echo "Listado de directorios:\n";
$it = new DirectoryIterator(__DIR__);
foreach ($it as $file) {
    if ($file->isDir() && !$file->isDot()) {
        echo "[DIR] " . $file->getFilename() . "\n";
    }
}
echo "\nSubiendo un nivel:\n";
$parent = dirname(__DIR__);
$it2 = new DirectoryIterator($parent);
foreach ($it2 as $file) {
    if ($file->isDir() && !$file->isDot()) {
        echo "[DIR] " . $file->getFilename() . "\n";
    }
}
