<?php
require_once __DIR__ . '/../root_config.php';
$db = getDB();

echo "--- Verificando Usuario aratio@edisongiraldo.com ---\n";
$stmt = $db->prepare("SELECT id, nombre, email, password, rol, estado FROM usuarios WHERE email = ?");
$stmt->execute(['aratio@edisongiraldo.com']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "Usuario encontrado:\n";
    print_r($user);
    
    // Verificar si la contraseña coincide con Admin123!
    $pass = 'Admin123!';
    if (password_verify($pass, $user['password'])) {
        echo "\nCORRECTO: La contraseña 'Admin123!' coincide con el hash en DB.\n";
    } else {
        echo "\nERROR: La contraseña 'Admin123!' NO coincide con el hash.\n";
    }
} else {
    echo "Usuario NO encontrado en la tabla 'usuarios'.\n";
    
    // Listar todos los usuarios para ver qué hay
    $stmt = $db->query("SELECT email FROM usuarios LIMIT 10");
    echo "\nEmails existentes (primeros 10):\n";
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
}
