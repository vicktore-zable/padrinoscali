<?php
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';

$db = Database::getInstance();
$email = 'dimartinezgomez@gmail.com';

try {
    $user = $db->fetchOne("SELECT id, usuario, documento_colaborador, activo, estado, nombres, apellidos FROM usuarios WHERE email = ?", [$email]);
    if ($user) {
        echo "VERIFICATION SUCCESS:\n";
        print_r($user);
    } else {
        echo "VERIFICATION FAILED: User not found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
