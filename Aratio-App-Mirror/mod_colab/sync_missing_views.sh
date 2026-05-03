#!/bin/bash

# Script para sincronizar vistas faltantes
# FTP credentials
FTP_HOST="212.1.208.241"
FTP_USER="u156469157.aratio.mrmtech.net"
FTP_PASS="sthLX6bJPoGh"
REMOTE_DIR="/mod_colab/src/Views"

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo "========================================="
echo "  SINCRONIZACIÓN DE VISTAS FALTANTES"
echo "========================================="
echo ""

# Función para subir archivo
upload_if_missing() {
    local local_file=$1
    local remote_path=$2
    local remote_url="ftp://${FTP_HOST}${remote_path}"
    
    echo -n "  Verificando: $(basename "$local_file")... "
    
    # Verificar si existe remoto
    if curl -s -u "${FTP_USER}:${FTP_PASS}" "${remote_url}" > /dev/null 2>&1; then
        echo -e "${YELLOW}Existe${NC}"
        return 1
    else
        echo -e "${RED}Falta - Subiendo...${NC}"
        if curl -s -u "${FTP_USER}:${FTP_PASS}" -T "$local_file" "${remote_url}" > /dev/null 2>&1; then
            echo -e "    ${GREEN}✓ Subido${NC}"
            return 0
        else
            echo -e "    ${RED}✗ Error al subir${NC}"
            return 1
        fi
    fi
}

# Array de directorios a revisar
DIRS=("auth" "colaboradores" "components" "curriculum" "dashboard" "errors" "home" "layouts" "logs" "profile" "reports" "settings" "usuarios")

for dir in "${DIRS[@]}"; do
    echo "Revisando directorio: $dir"
    
    # Verificar si directorio local existe
    if [ ! -d "src/Views/$dir" ]; then
        echo -e "  ${RED}Directorio local no existe: src/Views/$dir${NC}"
        echo ""
        continue
    fi
    
    # Crear directorio remoto si no existe
    curl -s -u "${FTP_USER}:${FTP_PASS}" --ftp-create-dirs "ftp://${FTP_HOST}${REMOTE_DIR}/${dir}/" > /dev/null 2>&1
    
    # Procesar archivos
    for file in src/Views/$dir/*.php; do
        if [ -f "$file" ]; then
            remote_file="${REMOTE_DIR}/${dir}/$(basename "$file")"
            upload_if_missing "$file" "$remote_file"
        fi
    done
    
    echo ""
done

echo "========================================="
echo -e "${GREEN}SINCRONIZACIÓN COMPLETADA${NC}"
echo "========================================="