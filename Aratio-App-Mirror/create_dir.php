<?php
$dir = __DIR__ . '/mod_elecciones/api';
if (!is_dir($dir)) {
    if (mkdir($dir, 0755, true)) {
        echo "Directorio creado: $dir";
    } else {
        echo "Error al crear el directorio: $dir";
    }
} else {
    echo "El directorio ya existe: $dir";
}
unlink(__FILE__); // Autodelete
?>
