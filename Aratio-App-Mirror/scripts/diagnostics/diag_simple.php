<?php
// Diagnostico simple - sin dependencias
echo "PHP funciona correctamente\n";
echo "Version PHP: " . PHP_VERSION . "\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "Servidor: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'desconocido') . "\n";
