<?php
// cleanup_deployment.php

$files = [
    'create_diad_dir.php',
    'update_htaccess.php',
    'list_root.php',
    'read_htaccess_final.php',
    'cleanup_deployment.php' // suicide script
];

foreach ($files as $f) {
    if (file_exists(__DIR__ . '/' . $f)) {
        unlink(__DIR__ . '/' . $f);
        echo "Deleted $f\n";
    }
}
