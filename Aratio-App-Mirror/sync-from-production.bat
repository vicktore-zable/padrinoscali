@echo off
echo ========================================
echo  SINCRONIZAR DESDE PRODUCCION
echo  Descargando archivos de Hostinger
echo ========================================
echo.
echo IMPORTANTE: Este script descargara los archivos de produccion
echo y los colocara en la carpeta 'produccion_backup'
echo.
echo Credenciales FTP:
echo Host: 212.1.208.241
echo Usuario: u156469157.aratio.mrmtech.net
echo.
pause

REM Crear carpeta de backup si no existe
if not exist "produccion_backup" mkdir produccion_backup

echo.
echo Conectando a servidor FTP...
echo.
echo NOTA: Necesitas un cliente FTP como WinSCP o FileZilla
echo.
echo Opcion 1: Usar WinSCP (Recomendado)
echo ------------------------------------------
echo 1. Descarga WinSCP: https://winscp.net/
echo 2. Conecta con:
echo    - Protocolo: FTP
echo    - Host: 212.1.208.241
echo    - Puerto: 21
echo    - Usuario: u156469157.aratio.mrmtech.net
echo    - Password: sthLX6bJPoGh
echo 3. Descarga todo el contenido de public_html
echo    a la carpeta: produccion_backup
echo.
echo Opcion 2: Usar PowerShell (Automatico)
echo ------------------------------------------
echo Presiona cualquier tecla para usar PowerShell...
pause > nul

powershell -ExecutionPolicy Bypass -File "%~dp0sync-ftp.ps1"

echo.
echo Sincronizacion completada!
pause
