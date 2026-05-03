<?php
header('Content-Type: text/plain');
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "OPCache reset successful\n";
    } else {
        echo "OPCache reset failed\n";
    }
} else {
    echo "OPCache NOT enabled/available\n";
}
?>
