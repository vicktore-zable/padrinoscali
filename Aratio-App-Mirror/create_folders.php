<?php 
$dir = __DIR__ . "/mod_lider/src/Library/PHPMailer";
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
    echo "Directorio creado: $dir";
} else {
    echo "Directorio ya existe: $dir";
}
?>