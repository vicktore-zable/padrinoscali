<?php
// Script simple para generar hash de contraseña

$password = 'Admin123!';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Contraseña: $password\n";
echo "Hash: $hash\n";
echo "Verificación: " . (password_verify($password, $hash) ? "✅ OK" : "❌ FAIL") . "\n";
