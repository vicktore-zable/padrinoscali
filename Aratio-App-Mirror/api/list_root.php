<?php
// list_root.php

$files = scandir(__DIR__ . '/..');
foreach ($files as $f) {
    if ($f === '.' || $f === '..') continue;
    $full = __DIR__ . '/../' . $f;
    $type = is_dir($full) ? 'DIR' : 'FILE';
    $perms = substr(sprintf('%o', fileperms($full)), -4);
    echo "$f [$type] ($perms)\n";
}
