<?php
$file = '/home/u156469157/domains/aratio.mrmtech.net/public_html/htaccess_new.txt';
$target = '/home/u156469157/domains/aratio.mrmtech.net/public_html/.htaccess';

if (file_exists($file)) {
    echo "FOUND htaccess_new.txt at $file\n";
    if (copy($file, $target)) {
        echo "SUCCESS: .htaccess updated\n";
        unlink($file);
    } else {
        echo "ERROR: Could not copy\n";
    }
} else {
    echo "NOT FOUND: $file\n";
    echo "Current CWD: " . getcwd() . "\n";
    echo "Listing CWD:\n";
    print_r(scandir(getcwd()));
}
?>
