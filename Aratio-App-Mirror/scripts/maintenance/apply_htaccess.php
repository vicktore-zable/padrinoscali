<?php
if (file_exists('htaccess_new.txt')) {
    if (copy('htaccess_new.txt', '.htaccess')) {
        echo "SUCCESS: .htaccess updated from htaccess_new.txt\n";
        unlink('htaccess_new.txt');
    } else {
        echo "ERROR: Could not copy htaccess_new.txt to .htaccess\n";
    }
} else {
    echo "ERROR: htaccess_new.txt not found\n";
}
?>
