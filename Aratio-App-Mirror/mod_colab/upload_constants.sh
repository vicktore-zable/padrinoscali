#!/bin/bash
# Script para subir constants.php a producción vía FTP

HOST="212.1.208.241"
USER="u156469157.aratio.mrmtech.net"
PASS="sthLX6bJPoGh"
REMOTE_DIR="/public_html/mod_colab/config"
LOCAL_FILE="h:/Mi unidad/2025/5d/app/colaboradores/config/constants.php"

echo "Subiendo constants.php a producción..."

ftp -n <<EOF
open $HOST
user $USER $PASS
cd $REMOTE_DIR
binary
put "$LOCAL_FILE" constants.php
bye
EOF

echo "Archivo subido exitosamente"
