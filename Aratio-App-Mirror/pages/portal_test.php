<?php
// Bypass auth for setup
$file = __DIR__ . '/../ZA_htaccess_new.txt';
$target = __DIR__ . '/../.htaccess';

echo "<pre>";
echo "Current File: " . __FILE__ . "\n";
if (file_exists($file)) {
    echo "FOUND htaccess_new.txt at $file\n";
    if (copy($file, $target)) {
        echo "SUCCESS: .htaccess updated from htaccess_new.txt\n";
        unlink($file);
    } else {
        $err = error_get_last();
        echo "ERROR: Could not copy. " . ($err ? $err['message'] : "") . "\n";
    }
} else {
    echo "NOT FOUND: $file\n";
    echo "Files in parent dir:\n";
    print_r(scandir(__DIR__ . '/..'));
}
echo "</pre>";
?>
