<?php
// Verificar qué contraseña corresponde al hash actual

$hash = '$2y$12$1y8fQ8mihvScFWd/RmM0Q.nqJq.P.a9eKIWcGjnjlsfcAC6I4W13.';

$passwords = [
    'Admin123!',
    'admin',
    'password',
    '123456',
    'admin123',
    'Admin123',
    'aratio',
    'Aratio123',
    'Aratio123!',
    'test',
    'Test123!',
    'password123'
];

echo "Hash: $hash\n\n";
echo "Probando contraseñas:\n";
foreach ($passwords as $password) {
    $result = password_verify($password, $hash);
    if ($result) {
        echo "✅ ¡ENCONTRADA! La contraseña es: '$password'\n";
        exit(0);
    }
}

echo "❌ No se encontró la contraseña entre las opciones probadas\n";
echo "\nGenerando nuevo hash para 'admin' (más simple):\n";
$newHash = password_hash('admin', PASSWORD_DEFAULT);
echo "Hash para 'admin': $newHash\n";
echo "Longitud: " . strlen($newHash) . "\n";
