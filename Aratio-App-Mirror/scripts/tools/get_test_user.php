<?php
require_once __DIR__ . '/../../config/config.php';
$db = getDB();

// Ver usuarios para main login (donde entran admin, roles diad, etc)
$qUsuario = $db->query("SELECT id, email, rol, estado FROM usuarios WHERE rol IN ('admin', 'super-admin', 'colaborador', 'lider', 'lider_diad', 'supervisor_diad') LIMIT 5");
echo "Usuarios principales (login.php):\n";
print_r($qUsuario->fetchAll(PDO::FETCH_ASSOC));

// Ver colaboradores para portal_login
$qColab = $db->query("SELECT id, cedula, telefono, rol FROM colaboradores LIMIT 5");
echo "\nColaboradores (portal_login / colaboradores.aratio...):\n";
if ($qColab) {
    print_r($qColab->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo "Tabla colaboradores no disponible o error";
}
