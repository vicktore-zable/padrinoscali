#!/bin/bash

# Script para corregir ERROR 500
# El problema es el .htaccess con RewriteBase incorrecto

SSH_HOST="212.1.208.241"
SSH_PORT="65002"
SSH_USER="u156469157"
REMOTE_DIR="/home/u156469157/domains/aratio.mrmtech.net/public_html/mod_colab"

echo "========================================="
echo " CORRIGIENDO ERROR 500"
echo "========================================="
echo ""

echo "1. Creando .htaccess correcto..."
cat > /tmp/htaccess_correcto << 'EOF'
# Configuración Apache para Colaboradores
RewriteEngine On

# IMPORTANTE: RewriteBase es / porque el dominio apunta directamente aquí
RewriteBase /

# Si el archivo o directorio existe, servirlo directamente
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Todo lo demás va a index.php
RewriteRule ^(.*)$ index.php [QSA,L]

# Charset UTF-8
AddDefaultCharset UTF-8

# Proteger archivos sensibles
<Files .env>
    Require all denied
</Files>

<FilesMatch "^\.">
    Require all denied
</FilesMatch>

# Deshabilitar listado de directorios
Options -Indexes

# PHP Settings
<IfModule mod_php.c>
    php_value upload_max_filesize 5M
    php_value post_max_size 5M
    php_flag display_errors Off
    php_flag log_errors On
</IfModule>
EOF

echo "✓ .htaccess creado en /tmp/htaccess_correcto"

echo ""
echo "2. Subiendo .htaccess corregido al servidor..."
scp -P ${SSH_PORT} /tmp/htaccess_correcto ${SSH_USER}@${SSH_HOST}:${REMOTE_DIR}/public/.htaccess

echo ""
echo "3. Creando archivo de prueba..."
cat > /tmp/test.php << 'EOF'
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<!DOCTYPE html><html><head><title>Test OK</title></head><body>";
echo "<h1 style='color:green'>✓ PHP FUNCIONA!</h1>";
echo "<p>Si ves esto, el servidor funciona correctamente.</p>";
echo "<p><a href='/login'>→ Ir al Login</a></p>";
echo "</body></html>";
EOF

scp -P ${SSH_PORT} /tmp/test.php ${SSH_USER}@${SSH_HOST}:${REMOTE_DIR}/public/

echo ""
echo "4. Verificando permisos..."
ssh -p ${SSH_PORT} ${SSH_USER}@${SSH_HOST} << ENDSSH
cd ${REMOTE_DIR}/public
chmod 644 .htaccess
chmod 644 index.php
chmod 644 test.php
ls -la .htaccess index.php test.php
ENDSSH

echo ""
echo "========================================="
echo " CORRECCIÓN COMPLETADA"
echo "========================================="
echo ""
echo "Prueba estas URLs:"
echo ""
echo "1. https://colaboradores.aratio.mrmtech.net/test.php"
echo "   (Si funciona, PHP está OK)"
echo ""
echo "2. https://colaboradores.aratio.mrmtech.net/"
echo "   (Debería funcionar ahora)"
echo ""
echo "Si SIGUE dando error 500, ejecuta:"
echo "bash check_errors.sh"
echo ""
