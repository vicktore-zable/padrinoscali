<?php
// update_htaccess.php

$file = __DIR__ . '/../.htaccess';
if (!file_exists($file)) {
    die("ERROR: .htaccess not found at $file");
}

$content = file_get_contents($file);

// Rules to add
$newRules = "\n# Dia D Module Routes\nRewriteRule ^reporte-diaD/?$ mod_diaD/index.php [L,QSA]\nRewriteRule ^dashboard-diaD/?$ mod_diaD/dashboard.php [L,QSA]\n";

// Check if already present
if (strpos($content, 'reporte-diaD') === false) {
    // Insert after RewriteBase / or at the beginning of routing section
    if (preg_match('/RewriteBase\s+\//i', $content)) {
        $content = preg_replace('/(RewriteBase\s+\/)/i', "$1\n$newRules", $content);
    } else {
        $content .= $newRules;
    }
    
    if (file_put_contents($file, $content)) {
        echo "OK: .htaccess updated.";
    } else {
        echo "ERROR: Failed to write .htaccess.";
    }
} else {
    echo "OK: .htaccess already has Dia D rules.";
}
