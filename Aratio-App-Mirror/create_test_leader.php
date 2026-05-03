<?php
// Desactivar output buffering por si acaso
if (ob_get_level()) ob_end_clean();

// Definir rutas base si no están definidas, asumiendo que el script corre en Root
define('ROOT_PATH', __DIR__);

// Cargar configuración principal
require_once __DIR__ . '/mod_colab/config/config.php';

// Cargar clase Database (que no tiene namespace y está en config)
require_once __DIR__ . '/mod_colab/config/database.php';

try {
    $db = Database::getInstance();
    
    // 1. Buscar un colaborador 'Líder' para vincular
    $stmt = $db->query("
        SELECT c.id, c.nombres, c.apellidos, c.documento 
        FROM colaboradores c 
        WHERE c.perfil LIKE '%Lider%' 
        ORDER BY c.id ASC 
        LIMIT 1
    ");
    $lider = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lider) {
        // Fallback: Buscar cualquier colaborador
        $stmt = $db->query("SELECT id, nombres, apellidos, documento FROM colaboradores LIMIT 1");
        $lider = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$lider) {
        die("Error: No se encontraron colaboradores en la base de datos.\n");
    }

    echo "Colaborador encontrado: " . $lider['nombres'] . " " . $lider['apellidos'] . " (ID: " . $lider['id'] . ")\n";

    // 2. Verificar/Crear usuario 'lider' con email
    $usuario = 'lider';
    $email = 'lider@aratio.com';
    $rawPass = '123456';
    $password = password_hash($rawPass, PASSWORD_DEFAULT);

    // Verificar si existe por usuario O email
    $stmtUser = $db->query("SELECT id FROM usuarios WHERE usuario = ? OR email = ?", [$usuario, $email]);
    $existingUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // Actualizar
        $sql = "UPDATE usuarios SET password = ?, email = ?, colaborador_id = ?, tipo_usuario = 'lider', estado = 'Activo' WHERE id = ?";
        $db->query($sql, [$password, $email, $lider['id'], $existingUser['id']]);
        echo "Usuario 'lider' actualizado.\n";
    } else {
        // Crear
        $sql = "INSERT INTO usuarios (usuario, email, password, tipo_usuario, colaborador_id, estado, created_at) VALUES (?, ?, ?, 'lider', ?, 'Activo', NOW())";
        $db->query($sql, [$usuario, $email, $password, $lider['id']]);
        echo "Usuario 'lider' creado.\n";
    }

    echo "\n=== CREDENCIALES DE PRUEBA ===\n";
    echo "Email: $email\n";
    echo "Usuario (opcional): $usuario\n";
    echo "Contraseña: $rawPass\n";
    echo "==============================\n";

} catch (Exception $e) {
    echo "Excepción: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
