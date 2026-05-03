<?php
// Script para actualizar password del admin
$newPassword = 'Admin123!';
$hash = password_hash($newPassword, PASSWORD_BCRYPT);
echo "Hash generado para 'Admin123!':\n";
echo $hash . "\n";
