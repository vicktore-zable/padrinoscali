<?php
// Bypass auth for setup
$file = __DIR__ . '/../htaccess_new.txt';
$target = __DIR__ . '/../.htaccess';

echo "<pre>";
if (file_exists($file)) {
    echo "FOUND htaccess_new.txt\n";
    if (copy($file, $target)) {
        echo "SUCCESS: .htaccess updated\n";
        unlink($file);
    } else {
        echo "ERROR: Could not copy\n";
    }
} else {
    echo "NOT FOUND: $file\n";
    echo "Files in parent dir:\n";
    print_r(scandir(__DIR__ . '/..'));
}
echo "</pre>";
?>
