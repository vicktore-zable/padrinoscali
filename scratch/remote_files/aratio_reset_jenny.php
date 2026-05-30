
<?php
require_once __DIR__ . '/config/config.php';
try {
    $db = getDB();
    $newPass = password_hash('Edison2027*', PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE email = 'jennybb2686@gmail.com'");
    $stmt->execute([$newPass]);
    echo "Contraseña actualizada exitosamente.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
