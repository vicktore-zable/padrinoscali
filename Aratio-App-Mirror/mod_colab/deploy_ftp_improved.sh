#!/bin/bash

# Script de Deployment via FTP a Hostinger
# Usa curl (disponible en Git Bash)
# Uso: bash deploy_ftp_improved.sh

set -e  # Exit on error

echo "========================================"
echo " DEPLOYMENT A HOSTINGER VIA FTP"
echo " Sistema Colaboradores A Ratio"
echo "========================================"
echo ""

# Variables
FTP_HOST="212.1.208.241"
FTP_USER="u156469157.aratio.mrmtech.net"
FTP_PASS="sthLX6bJPoGh"
REMOTE_DIR="/mod_colab"
LOCAL_DIR="."

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Función para subir archivo
upload_file() {
    local local_file=$1
    local remote_path=$2

    echo -n "  Subiendo: $local_file... "

    if curl -s -u "${FTP_USER}:${FTP_PASS}" \
         -T "$local_file" \
         "ftp://${FTP_HOST}${remote_path}" > /dev/null 2>&1; then
        echo -e "${GREEN}✓${NC}"
        return 0
    else
        echo -e "${RED}✗${NC}"
        return 1
    fi
}

# Función para crear directorio
create_dir() {
    local dir=$1
    echo -n "  Creando directorio: $dir... "

    if curl -s -u "${FTP_USER}:${FTP_PASS}" \
         --ftp-create-dirs \
         "ftp://${FTP_HOST}${dir}/" > /dev/null 2>&1; then
        echo -e "${GREEN}✓${NC}"
        return 0
    else
        echo -e "${YELLOW}⚠${NC} (puede ya existir)"
        return 0
    fi
}

# Verificar conexión FTP
echo "1. Verificando conexión FTP..."
if curl -s -u "${FTP_USER}:${FTP_PASS}" "ftp://${FTP_HOST}/" > /dev/null 2>&1; then
    echo -e "   ${GREEN}✓ Conexión exitosa${NC}"
else
    echo -e "   ${RED}✗ Error de conexión${NC}"
    exit 1
fi

echo ""
echo "2. Creando estructura de directorios..."
create_dir "${REMOTE_DIR}"
create_dir "${REMOTE_DIR}/config"
create_dir "${REMOTE_DIR}/database"
create_dir "${REMOTE_DIR}/public"
create_dir "${REMOTE_DIR}/public/assets"
create_dir "${REMOTE_DIR}/public/assets/css"
create_dir "${REMOTE_DIR}/public/assets/js"
create_dir "${REMOTE_DIR}/public/assets/images"
create_dir "${REMOTE_DIR}/public/uploads"
create_dir "${REMOTE_DIR}/routes"
create_dir "${REMOTE_DIR}/src"
create_dir "${REMOTE_DIR}/src/Controllers"
create_dir "${REMOTE_DIR}/src/Core"
create_dir "${REMOTE_DIR}/src/Middleware"
create_dir "${REMOTE_DIR}/src/Models"
create_dir "${REMOTE_DIR}/src/Utils"
create_dir "${REMOTE_DIR}/src/Views"
create_dir "${REMOTE_DIR}/storage"
create_dir "${REMOTE_DIR}/storage/cache"
create_dir "${REMOTE_DIR}/storage/logs"

echo ""
echo "3. Subiendo archivos de configuración..."
upload_file ".env.production" "${REMOTE_DIR}/.env"
upload_file "config/config.php" "${REMOTE_DIR}/config/config.php"
upload_file "config/constants.php" "${REMOTE_DIR}/config/constants.php"
upload_file "config/database.php" "${REMOTE_DIR}/config/database.php"

echo ""
echo "4. Subiendo archivos públicos..."
upload_file "public/index.php" "${REMOTE_DIR}/public/index.php"
if [ -f "public/.htaccess" ]; then
    upload_file "public/.htaccess" "${REMOTE_DIR}/public/.htaccess"
fi

echo ""
echo "5. Subiendo assets CSS..."
for file in public/assets/css/*.css; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        upload_file "$file" "${REMOTE_DIR}/public/assets/css/${filename}"
    fi
done

echo ""
echo "6. Subiendo assets JS..."
for file in public/assets/js/*.js; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        upload_file "$file" "${REMOTE_DIR}/public/assets/js/${filename}"
    fi
done

echo ""
echo "7. Subiendo rutas..."
for file in routes/*.php; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        upload_file "$file" "${REMOTE_DIR}/routes/${filename}"
    fi
done

echo ""
echo "8. Subiendo controllers..."
for file in src/Controllers/*.php; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        upload_file "$file" "${REMOTE_DIR}/src/Controllers/${filename}"
    fi
done

echo ""
echo "9. Subiendo core..."
for file in src/Core/*.php; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        upload_file "$file" "${REMOTE_DIR}/src/Core/${filename}"
    fi
done

echo ""
echo "10. Subiendo middleware..."
for file in src/Middleware/*.php; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        upload_file "$file" "${REMOTE_DIR}/src/Middleware/${filename}"
    fi
done

echo ""
echo "11. Subiendo models..."
for file in src/Models/*.php; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        upload_file "$file" "${REMOTE_DIR}/src/Models/${filename}"
    fi
done

echo ""
echo "12. Subiendo utils..."
for file in src/Utils/*.php; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        upload_file "$file" "${REMOTE_DIR}/src/Utils/${filename}"
    fi
done

echo ""
echo "13. Subiendo views (esto puede tardar)..."
echo "   Subiendo layouts..."
for file in src/Views/layouts/*.php; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        create_dir "${REMOTE_DIR}/src/Views/layouts"
        upload_file "$file" "${REMOTE_DIR}/src/Views/layouts/${filename}"
    fi
done

echo "   Subiendo components..."
for file in src/Views/components/*.php; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        create_dir "${REMOTE_DIR}/src/Views/components"
        upload_file "$file" "${REMOTE_DIR}/src/Views/components/${filename}"
    fi
done

# Subir vistas de cada módulo
for module in auth colaboradores curriculum dashboard logs reports usuarios; do
    if [ -d "src/Views/$module" ]; then
        echo "   Subiendo vistas de $module..."
        create_dir "${REMOTE_DIR}/src/Views/$module"
        for file in src/Views/$module/*.php; do
            if [ -f "$file" ]; then
                filename=$(basename "$file")
                upload_file "$file" "${REMOTE_DIR}/src/Views/$module/${filename}"
            fi
        done
    fi
done

echo ""
echo "14. Subiendo archivos de base de datos..."
upload_file "database/schema.sql" "${REMOTE_DIR}/database/schema.sql"
upload_file "database/seeds.sql" "${REMOTE_DIR}/database/seeds.sql"
upload_file "database/triggers.sql" "${REMOTE_DIR}/database/triggers.sql"

echo ""
echo "========================================"
echo -e " ${GREEN}DEPLOYMENT COMPLETADO${NC}"
echo "========================================"
echo ""
echo "PRÓXIMOS PASOS:"
echo ""
echo "1. Importar base de datos:"
echo "   - Opción A: phpMyAdmin (desde hPanel)"
echo "   - Opción B: MySQL CLI"
echo "     mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio < database/seeds.sql"
echo ""
echo "2. Configurar permisos desde hPanel File Manager:"
echo "   - storage/: 755"
echo "   - storage/logs/: 755"
echo "   - storage/cache/: 755"
echo "   - public/uploads/: 755"
echo ""
echo "3. Verificar sitio web:"
echo "   https://colaboradores.aratio.mrmtech.net/"
echo ""
echo "4. Login inicial:"
echo "   Usuario: admin"
echo "   Password: Admin123!"
echo ""
echo "========================================"
