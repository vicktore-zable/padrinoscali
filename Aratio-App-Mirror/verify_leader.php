<?php
require_once 'config/config.php';
\Database::getInstance(); // Asegurar que la clase Database existe (si no, fallará)
require_once 'mod_lider/src/Models/Colaborador.php';

echo "--- BUSCANDO LIDER 1130665763 ---\n";
try {
     = getDB();
     = ->prepare("SELECT * FROM colaboradores WHERE documento = ?");
    ->execute(['1130665763']);
     = ->fetch(PDO::FETCH_ASSOC);
    if () {
        echo "Líder encontrado: " . ['nombres'] . " " . ['apellidos'] . "\n";
        echo "Teléfono en DB: " . ['telefono'] . "\n";
    } else {
        echo "Líder NO encontrado en la tabla colaboradores.\n";
    }

    echo "\n--- BUSCANDO USUARIO 1130665763 ---\n";
     = ->prepare("SELECT id, usuario, email, tipo_usuario FROM usuarios WHERE usuario = ?");
    ->execute(['1130665763']);
     = ->fetch(PDO::FETCH_ASSOC);
    if () {
        echo "Usuario ya existe: " . print_r(, true) . "\n";
    } else {
        echo "Usuario NO existe en la tabla usuarios.\n";
    }
} catch (Exception ) {
    echo "Error: " . ->getMessage() . "\n";
}
?>