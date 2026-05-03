#!/bin/bash

# Script para corregir el archivo .env en producción
# Ejecutar: bash fix_env.sh

SSH_HOST="212.1.208.241"
SSH_PORT="65002"
SSH_USER="u156469157"
REMOTE_DIR="/home/u156469157/domains/aratio.mrmtech.net/public_html/mod_colab"

echo "========================================="
echo " CORREGIR .ENV EN PRODUCCIÓN"
echo "========================================="
echo ""

echo "1. Subiendo .env.production correcto..."
scp -P ${SSH_PORT} .env.production ${SSH_USER}@${SSH_HOST}:${REMOTE_DIR}/.env

echo ""
echo "2. Verificando contenido del .env..."
ssh -p ${SSH_PORT} ${SSH_USER}@${SSH_HOST} << ENDSSH
cd ${REMOTE_DIR}

echo "--- Primeras 20 líneas de .env ---"
head -20 .env

echo ""
echo "--- Verificando variables críticas ---"
echo "APP_ENV: \$(grep '^APP_ENV=' .env)"
echo "APP_DEBUG: \$(grep '^APP_DEBUG=' .env)"
echo "APP_URL: \$(grep '^APP_URL=' .env)"
echo "DB_HOST: \$(grep '^DB_HOST=' .env)"

echo ""
echo "--- Permisos del .env ---"
chmod 600 .env
ls -l .env

ENDSSH

echo ""
echo "========================================="
echo " CORRECCIÓN COMPLETADA"
echo "========================================="
echo ""
echo "Ahora prueba acceder a:"
echo "https://colaboradores.aratio.mrmtech.net/"
echo ""
echo "Si aún hay error, ejecuta:"
echo "bash check_errors.sh"
echo ""
