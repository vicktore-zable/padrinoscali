<?php
header('Content-Type: text/plain');
$dir = __DIR__ . '/..';
echo "Listing DIR: $dir\n";
$files = scandir($dir);
foreach ($files as $file) {
    if (strpos($file, 'htaccess') !== false) {
        echo "FOUND: $file - Permissions: " . substr(sprintf('%o', fileperms($dir . '/' . $file)), -4) . "\n";
    }
}

$file_to_copy = $dir . '/ZA_htaccess_new.txt';
if (file_exists($file_to_copy)) {
    echo "file_exists CHECK: YES\n";
    if (copy($file_to_copy, $dir . '/.htaccess')) {
        echo "COPY SUCCESS\n";
    } else {
        echo "COPY FAILED: " . print_r(error_get_last(), true) . "\n";
    }
} else {
    echo "file_exists CHECK: NO for $file_to_copy\n";
}
?>
