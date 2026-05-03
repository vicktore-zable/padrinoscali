<?php
// create_diad_dir.php

$dir = __DIR__ . '/../mod_diaD';

if (!file_exists($dir)) {
    if (mkdir($dir, 0755, true)) {
        echo "OK: Directory created.";
    } else {
        echo "ERROR: Failed to create directory.";
    }
} else {
    echo "OK: Directory already exists.";
}

// Also check permissions of api directory just in case
echo "\nAPI DIR PERMS: " . substr(sprintf('%o', fileperms(__DIR__)), -4);
