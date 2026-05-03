<?php
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';

$db = Database::getInstance();
$email = 'dimartinezgomez@gmail.com';
$correctDoc = '1006054676';
$correctId = 559;

try {
    echo "Correcting user record for email: $email\n";
    
    // 1. Identify the record
    $user = $db->fetchOne("SELECT id FROM usuarios WHERE email = ?", [$email]);
    
    if (!$user) {
        die("Error: No user found with email $email. Access cannot be fixed this way.\n");
    }

    $userId = $user['id'];
    echo "Found user ID: $userId. Updating...\n";
    
    // 2. Perform the update
    $data = [
        'usuario' => $correctDoc,
        'documento_colaborador' => $correctDoc,
        'colaborador_id' => $correctId,
        'nombres' => 'Dara',
        'apellidos' => 'Martínez',
        'nombre' => 'Dara Martínez',
        'password' => password_hash($correctDoc, PASSWORD_BCRYPT, ['cost' => 12]),
        'tipo_usuario' => 'lider',
        'estado' => 'activo',
        'activo' => 1,
        'intentos_fallidos' => 0,
        'bloqueado_hasta' => null
    ];
    
    $result = $db->update('usuarios', $data, 'id = ?', [$userId]);
    
    if ($result > 0) {
        echo "SUCCESS: User record for Dara Martínez updated correctly.\n";
    } else {
        echo "WARNING: No rows affected. Maybe the data was already set?\n";
    }

} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
}
