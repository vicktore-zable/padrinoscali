<?php
header('Content-Type: text/plain');
$dir = __DIR__ . '/pages';
echo "Scanning $dir...\n";
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        echo "$file -> " . (file_exists($path) ? "EXISTS" : "NOT FOUND") . " | Perms: " . substr(sprintf('%o', fileperms($path)), -4) . "\n";
    }
} else {
    echo "$dir is NOT a directory\n";
}
?>
