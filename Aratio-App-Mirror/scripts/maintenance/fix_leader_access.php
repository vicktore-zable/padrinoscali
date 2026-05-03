<?php
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';

$db = Database::getInstance();
$documento = '1006054676';

try {
    // 1. Fetch colaborador data
    $colab = $db->fetchOne("SELECT * FROM colaboradores WHERE documento = ?", [$documento]);
    if (!$colab) {
        die("Error: Colaborador not found.\n");
    }

    // 2. Check existing user
    $user = $db->fetchOne("SELECT * FROM usuarios WHERE documento_colaborador = ? OR usuario = ?", [$documento, $documento]);
    
    if ($user) {
        echo "User already exists. Updating/Unblocking...\n";
        $db->update('usuarios', [
            'activo' => 1,
            'estado' => 'activo',
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
            'tipo_usuario' => 'lider'
        ], 'id = ?', [$user['id']]);
        echo "User updated successfully.\n";
    } else {
        echo "User does not exist. Creating...\n";
        
        $data = [
            'usuario' => $documento,
            'nombre' => $colab['nombres'] . ' ' . $colab['apellidos'],
            'email' => $colab['email'] ?: "lider_$documento@aratio.tmp",
            'nombres' => $colab['nombres'],
            'apellidos' => $colab['apellidos'],
            'password' => password_hash($documento, PASSWORD_BCRYPT, ['cost' => 12]),
            'telefono' => $colab['telefono'],
            'rol' => 'colaborador', // Based on enum check
            'tipo_usuario' => 'lider',
            'colaborador_id' => $colab['id'],
            'documento_colaborador' => $colab['documento'],
            'estado' => 'activo',
            'activo' => 1,
            'intentos_fallidos' => 0
        ];
        
        $res = $db->insert('usuarios', $data);
        if ($res) {
            echo "User created successfully with ID: $res\n";
        } else {
            echo "FAILED to create user.\n";
            // Show errors if possible? Database class usually throws exception
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
