#!/bin/bash

# Script Router de Deployment a Hostinger
# Sistema Colaboradores A Ratio
# Uso: bash deploy.sh

echo "========================================="
echo " DEPLOYMENT A HOSTINGER"
echo " Sistema Colaboradores A Ratio"
echo "========================================="
echo ""
echo "Seleccione el método de deployment:"
echo ""
echo "1. SSH + rsync (Recomendado - más rápido)"
echo "   → Requiere SSH habilitado en hPanel"
echo "   → Puerto 65002"
echo "   → Ver: ENABLE_SSH.md"
echo ""
echo "2. FTP con curl (Funciona siempre)"
echo "   → No requiere configuración adicional"
echo "   → Más lento que SSH"
echo ""
echo "3. FTP con lftp (Requiere lftp instalado)"
echo "   → Más rápido que curl"
echo "   → Requiere lftp en sistema"
echo ""
echo "========================================="
echo ""
read -p "Ingrese su opción (1/2/3): " option

case $option in
    1)
        echo ""
        echo "Usando: SSH + rsync (Puerto 65002)"
        echo ""
        if [ -f "deploy_ssh.sh" ]; then
            bash deploy_ssh.sh
        else
            echo "Error: deploy_ssh.sh no encontrado"
            exit 1
        fi
        ;;
    2)
        echo ""
        echo "Usando: FTP con curl"
        echo ""
        if [ -f "deploy_ftp_improved.sh" ]; then
            bash deploy_ftp_improved.sh
        else
            echo "Error: deploy_ftp_improved.sh no encontrado"
            exit 1
        fi
        ;;
    3)
        echo ""
        echo "Usando: FTP con lftp"
        echo ""
        if [ -f "deploy_ftp.sh" ]; then
            bash deploy_ftp.sh
        else
            echo "Error: deploy_ftp.sh no encontrado"
            exit 1
        fi
        ;;
    *)
        echo ""
        echo "Opción inválida. Use 1, 2 o 3"
        exit 1
        ;;
esac
