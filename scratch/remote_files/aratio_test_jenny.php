
<?php
require_once __DIR__ . '/config/config.php';
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, nombre, email, password FROM usuarios WHERE nombre LIKE '%jenny%' OR email LIKE '%jenny%'");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    foreach ($usuarios as $u) {
        echo "ID: {$u['id']} - Nombre: {$u['nombre']} - Email: {$u['email']}\n";
        echo "Hash: {$u['password']}\n";
        
        // Verificar si es una de las comunes
        $comunes = ['123456', '12345678', 'password', 'Jenny123*', 'jenny123'];
        foreach ($comunes as $c) {
            if (password_verify($c, $u['password'])) {
                echo "¡La contraseña es: $c!\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
