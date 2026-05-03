@echo off
echo ========================================
echo Reiniciando Servidor PHP
echo ========================================
echo.

echo 1. Deteniendo procesos PHP existentes...
taskkill /F /IM php.exe 2>nul
if %ERRORLEVEL% == 0 (
    echo    [OK] Procesos PHP detenidos
) else (
    echo    [INFO] No hay procesos PHP corriendo
)
echo.

echo 2. Esperando 2 segundos...
timeout /t 2 /nobreak >nul
echo.

echo 3. Iniciando servidor PHP en localhost:8000...
cd /d "H:\Mi unidad\2025\5d\app\colaboradores"
start "Servidor PHP - ARatio" cmd /k "php -S localhost:8000 -t public"
echo.

echo ========================================
echo Servidor iniciado correctamente
echo ========================================
echo.
echo URL: http://localhost:8000
echo.
echo Presiona cualquier tecla para cerrar esta ventana...
pause >nul
