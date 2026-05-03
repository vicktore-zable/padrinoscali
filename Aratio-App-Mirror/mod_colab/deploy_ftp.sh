#!/bin/bash

# Script de deployment via FTP a Hostinger
# Uso: bash deploy_ftp.sh

echo "========================================="
echo "DEPLOYMENT A HOSTINGER VIA FTP"
echo "========================================="

FTP_HOST="212.1.208.241"
FTP_USER="u156469157.aratio.mrmtech.net"
FTP_PASS="sthLX6bJPoGh"
REMOTE_DIR="/mod_colab"

echo ""
echo "1. Creando carpeta mod_colab..."
lftp -u ${FTP_USER},${FTP_PASS} ${FTP_HOST} << FTPEOF
mkdir -p ${REMOTE_DIR}
bye
FTPEOF

echo ""
echo "2. Subiendo archivos..."
lftp -u ${FTP_USER},${FTP_PASS} ${FTP_HOST} << FTPEOF
mirror -R \
  --exclude='.git/' \
  --exclude='node_modules/' \
  --exclude='vendor/' \
  --exclude='.claude/' \
  --exclude='storage/logs/*.log' \
  --exclude='storage/cache/*' \
  --exclude='cache/*' \
  --exclude='*.md' \
  --exclude='deploy*.sh' \
  --exclude='quick_start.bat' \
  --exclude='.env' \
  --exclude='.env.example' \
  --exclude='database/production_dump.sql' \
  --exclude='database/production_export.sql' \
  ./ ${REMOTE_DIR}/
bye
FTPEOF

echo ""
echo "3. Subiendo .env de producción..."
lftp -u ${FTP_USER},${FTP_PASS} ${FTP_HOST} << FTPEOF
put .env.production -o ${REMOTE_DIR}/.env
bye
FTPEOF

echo ""
echo "========================================="
echo "DEPLOYMENT COMPLETADO VIA FTP"
echo "========================================="
echo ""
echo "Próximos pasos:"
echo "1. Configurar permisos manualmente via panel de Hostinger"
echo "2. Importar base de datos desde phpMyAdmin"
echo "3. Verificar acceso en: https://colaboradores.aratio.mrmtech.net"
echo ""
