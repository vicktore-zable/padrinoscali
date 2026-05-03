#!/bin/bash

# Script para solucionar error 500 en Hostinger
# Despliega .htaccess y verifica configuración

set -e

SSH_HOST="212.1.208.241"
SSH_PORT="65002"
SSH_USER="u156469157"
REMOTE_DIR="/home/u156469157/domains/aratio.mrmtech.net/public_html/mod_colab"

echo "========================================="
echo " FIX ERROR 500 - HOSTINGER"
echo "========================================="
echo ""

echo "1. Copiando .htaccess a producción..."
scp -P ${SSH_PORT} public/.htaccess ${SSH_USER}@${SSH_HOST}:${REMOTE_DIR}/public/.htaccess
echo "✓ .htaccess copiado"

echo ""
echo "2. Verificando configuración en servidor..."
ssh -p ${SSH_PORT} ${SSH_USER}@${SSH_HOST} << 'ENDSSH'
cd /home/u156469157/domains/aratio.mrmtech.net/public_html/mod_colab

echo "--- Estructura de directorios ---"
ls -la | grep "^d"

echo ""
echo "--- Archivos en raíz ---"
ls -la | grep "^-" | head -10

echo ""
echo "--- Archivos en public/ ---"
ls -la public/ | grep "^-" | head -10

echo ""
echo "--- Verificando .htaccess ---"
if [ -f "public/.htaccess" ]; then
    echo "✓ public/.htaccess existe"
    echo "Primeras 10 líneas:"
    head -10 public/.htaccess
else
    echo "✗ public/.htaccess NO EXISTE"
fi

echo ""
echo "--- Verificando .env ---"
if [ -f ".env" ]; then
    echo "✓ .env existe"
    echo "APP_ENV=$(grep APP_ENV .env)"
    echo "APP_DEBUG=$(grep APP_DEBUG .env)"
else
    echo "✗ .env NO EXISTE"
fi

echo ""
echo "--- Verificando permisos ---"
echo "Directorio mod_colab:"
ls -ld .
echo "Directorio public:"
ls -ld public/
echo "Archivo index.php:"
ls -l public/index.php 2>/dev/null || echo "✗ public/index.php NO EXISTE"

echo ""
echo "--- Últimos 20 errores PHP ---"
if [ -f "storage/logs/app.log" ]; then
    tail -20 storage/logs/app.log
else
    echo "(No hay archivo de log todavía)"
fi

ENDSSH

echo ""
echo "========================================="
echo " VERIFICACIÓN COMPLETADA"
echo "========================================="
echo ""
echo "PRÓXIMOS PASOS:"
echo ""
echo "1. Acceder al sitio:"
echo "   https://colaboradores.aratio.mrmtech.net/"
echo ""
echo "2. Si sigue dando error 500, ver logs:"
echo "   ssh -p ${SSH_PORT} ${SSH_USER}@${SSH_HOST} \\"
echo "     'tail -50 ${REMOTE_DIR}/storage/logs/app.log'"
echo ""
echo "3. Ver error log de PHP (si está habilitado):"
echo "   ssh -p ${SSH_PORT} ${SSH_USER}@${SSH_HOST} \\"
echo "     'tail -50 /home/u156469157/domains/aratio.mrmtech.net/logs/error_log'"
echo ""
echo "========================================="
