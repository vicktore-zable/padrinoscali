#!/bin/bash

# Subir archivo diagnostico.php via FTP

FTP_HOST="ftp.aratio.mrmtech.net"
FTP_USER="u156469157"
FTP_PASS="Aratio2025!"
REMOTE_DIR="/mod_colab/public"

echo "Subiendo diagnostico.php..."

lftp -c "
set ftp:ssl-allow no
open ftp://$FTP_USER:$FTP_PASS@$FTP_HOST
cd $REMOTE_DIR
put public/diagnostico.php
put public/info.php
put public/test-simple.php
ls -la *.php
bye
"

echo ""
echo "Archivos subidos. Prueba:"
echo "https://colaboradores.aratio.mrmtech.net/diagnostico.php"
echo "https://colaboradores.aratio.mrmtech.net/info.php"
echo "https://colaboradores.aratio.mrmtech.net/test-simple.php"
