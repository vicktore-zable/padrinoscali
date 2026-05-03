#!/bin/bash

# Script para verificar errores en producción
# Ejecutar: bash check_errors.sh

SSH_HOST="212.1.208.241"
SSH_PORT="65002"
SSH_USER="u156469157"
REMOTE_DIR="/home/u156469157/domains/aratio.mrmtech.net/public_html/mod_colab"

echo "========================================="
echo " VERIFICAR ERRORES EN PRODUCCIÓN"
echo "========================================="
echo ""

ssh -p ${SSH_PORT} ${SSH_USER}@${SSH_HOST} << 'ENDSSH'
cd /home/u156469157/domains/aratio.mrmtech.net/public_html/mod_colab

echo "1. LOGS DE LA APLICACIÓN"
echo "========================================="
if [ -f "storage/logs/app.log" ]; then
    echo "Últimas 30 líneas:"
    tail -30 storage/logs/app.log
else
    echo "✗ No hay archivo storage/logs/app.log"
fi

echo ""
echo "2. LOGS DE PHP DEL DOMINIO"
echo "========================================="
if [ -f "/home/u156469157/domains/aratio.mrmtech.net/logs/error_log" ]; then
    echo "Últimas 30 líneas:"
    tail -30 /home/u156469157/domains/aratio.mrmtech.net/logs/error_log
else
    echo "✗ No hay error_log del dominio"
fi

echo ""
echo "3. VERIFICAR SINTAXIS PHP"
echo "========================================="
cd public
php -l index.php

echo ""
echo "4. VERIFICAR ARCHIVOS CRÍTICOS"
echo "========================================="
echo -n "index.php: "
[ -f "index.php" ] && echo "✓ Existe" || echo "✗ NO EXISTE"

cd ..
echo -n ".env: "
[ -f ".env" ] && echo "✓ Existe" || echo "✗ NO EXISTE"

echo -n "config/config.php: "
[ -f "config/config.php" ] && echo "✓ Existe" || echo "✗ NO EXISTE"

echo -n "config/database.php: "
[ -f "config/database.php" ] && echo "✓ Existe" || echo "✗ NO EXISTE"

echo -n "src/Core/Router.php: "
[ -f "src/Core/Router.php" ] && echo "✓ Existe" || echo "✗ NO EXISTE"

echo ""
echo "5. PERMISOS"
echo "========================================="
ls -la public/index.php
ls -la .env
ls -la storage/logs/

echo ""
echo "6. PROBAR CONEXIÓN A BASE DE DATOS"
echo "========================================="
php << 'PHPTEST'
<?php
$env = file_get_contents('.env');
preg_match('/^DB_HOST=(.*)$/m', $env, $host);
preg_match('/^DB_USER=(.*)$/m', $env, $user);
preg_match('/^DB_PASS=(.*)$/m', $env, $pass);
preg_match('/^DB_NAME=(.*)$/m', $env, $name);

$host = trim($host[1] ?? '');
$user = trim($user[1] ?? '');
$pass = trim($pass[1] ?? '');
$name = trim($name[1] ?? '');

echo "Host: $host\n";
echo "User: $user\n";
echo "Database: $name\n";

try {
    $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    echo "✓ Conexión a base de datos EXITOSA\n";
} catch (PDOException $e) {
    echo "✗ Error de conexión: " . $e->getMessage() . "\n";
}
?>
PHPTEST

ENDSSH

echo ""
echo "========================================="
echo " VERIFICACIÓN COMPLETADA"
echo "========================================="
