#!/bin/bash

# Script de Deployment via SSH a Hostinger
# REQUISITO: SSH debe estar habilitado en hPanel primero
# Ver: ENABLE_SSH.md
# Uso: bash deploy_ssh.sh

set -e  # Exit on error

echo "========================================="
echo " DEPLOYMENT A HOSTINGER VIA SSH"
echo " Sistema Colaboradores A Ratio"
echo "========================================="
echo ""

# Variables
SSH_HOST="212.1.208.241"
SSH_PORT="65002"
SSH_USER="u156469157"
REMOTE_DIR="/home/u156469157/domains/aratio.mrmtech.net/public_html/mod_colab"
LOCAL_DIR="."

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar conexión SSH
echo "1. Verificando conexión SSH..."
echo -n "   Conectando a ${SSH_HOST}:${SSH_PORT}... "

if timeout 10 ssh -p ${SSH_PORT} -o ConnectTimeout=5 -o StrictHostKeyChecking=no ${SSH_USER}@${SSH_HOST} "echo 'OK'" > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Conexión exitosa${NC}"
else
    echo -e "${RED}✗ Error de conexión${NC}"
    echo ""
    echo "POSIBLES CAUSAS:"
    echo "1. SSH no está habilitado en hPanel"
    echo "   → Ir a: hPanel → SSH Access → Enable"
    echo ""
    echo "2. Password SSH incorrecto"
    echo "   → Verificar en: hPanel → SSH Access"
    echo ""
    echo "3. Puerto incorrecto (debe ser 65002)"
    echo ""
    echo "Ver: ENABLE_SSH.md para instrucciones detalladas"
    echo ""
    exit 1
fi

echo ""
echo "2. Creando estructura de directorios en servidor..."
ssh -p ${SSH_PORT} ${SSH_USER}@${SSH_HOST} << 'ENDSSH'
cd /home/u156469157/domains/aratio.mrmtech.net/public_html

# Crear estructura completa
mkdir -p mod_colab/{config,database,public/{assets/{css,js,images},uploads},routes,src/{Controllers,Core,Middleware,Models,Utils,Views/{layouts,components,auth,colaboradores,curriculum,dashboard,logs,reports,usuarios}},storage/{cache,logs}}

echo "✓ Estructura de directorios creada"
ENDSSH

echo ""
echo "3. Copiando .env de producción..."
scp -P ${SSH_PORT} .env.production ${SSH_USER}@${SSH_HOST}:${REMOTE_DIR}/.env
echo -e "${GREEN}✓ .env copiado${NC}"

echo ""
echo "4. Sincronizando archivos con rsync..."
rsync -avz --progress \
  -e "ssh -p ${SSH_PORT}" \
  --exclude='.git' \
  --exclude='.env' \
  --exclude='.env.example' \
  --exclude='.env.production' \
  --exclude='storage/logs/*.log' \
  --exclude='storage/cache/*' \
  --exclude='cache/*' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.claude' \
  --exclude='*.md' \
  --exclude='deploy*.sh' \
  --exclude='quick_start.bat' \
  --exclude='database/production_dump.sql' \
  --exclude='database/production_export.sql' \
  --exclude='nul' \
  --exclude='public/fix-passwords.php' \
  --exclude='SESSION_REPORT*.md' \
  --exclude='DEPLOYMENT*.md' \
  --exclude='DEPLOYMENT*.txt' \
  --exclude='ENABLE_SSH.md' \
  --exclude='verify_deployment.sh' \
  ${LOCAL_DIR}/ ${SSH_USER}@${SSH_HOST}:${REMOTE_DIR}/

echo ""
echo "5. Configurando permisos..."
ssh -p ${SSH_PORT} ${SSH_USER}@${SSH_HOST} << ENDSSH
cd ${REMOTE_DIR}

# Permisos generales
chmod -R 755 .

# Permisos de escritura para storage
chmod -R 775 storage
chmod -R 775 storage/logs
chmod -R 775 storage/cache

# Cache si existe
if [ -d "cache" ]; then
    chmod -R 775 cache
fi

# Uploads
chmod -R 775 public/uploads

# .env seguro
chmod 600 .env

echo "✓ Permisos configurados"
ENDSSH

echo ""
echo "========================================"
echo -e " ${GREEN}DEPLOYMENT COMPLETADO VIA SSH${NC}"
echo "========================================"
echo ""
echo "PRÓXIMOS PASOS:"
echo ""
echo "1. Importar base de datos:"
echo ""
echo "   Opción A - Desde servidor (más rápido):"
echo "   scp -P ${SSH_PORT} database/seeds.sql ${SSH_USER}@${SSH_HOST}:/tmp/"
echo "   ssh -p ${SSH_PORT} ${SSH_USER}@${SSH_HOST} \\"
echo "     'mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio < /tmp/seeds.sql'"
echo ""
echo "   Opción B - Desde local:"
echo "   mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR \\"
echo "     u156469157_aratio < database/seeds.sql"
echo ""
echo "2. Verificar sitio web:"
echo "   https://colaboradores.aratio.mrmtech.net/"
echo ""
echo "3. Login inicial:"
echo "   Usuario: admin"
echo "   Password: Admin123!"
echo ""
echo "4. Ver logs (si hay errores):"
echo "   ssh -p ${SSH_PORT} ${SSH_USER}@${SSH_HOST} \\"
echo "     'tail -50 ${REMOTE_DIR}/storage/logs/app.log'"
echo ""
echo "========================================"
