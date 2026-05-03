@echo off
setlocal enabledelayedexpansion

title ARATIO - Servidor de Desarrollo Local
color 0B

echo ============================================================
echo   ARATIO - SISTEMA DE GESTION ELECTORAL (MODO LOCAL)
echo ============================================================
echo.
echo [1/3] Verificando entorno...

where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] No se encontro PHP en el PATH.
    echo Por favor, instala PHP y asegurate de que este en las variables de entorno.
    pause
    exit /b 1
)

for /f "tokens=2 delims= " %%v in ('php -v ^| findstr "PHP"') do (
    set PHP_VERSION=%%v
)
echo [OK] PHP Detectado: %PHP_VERSION%

echo.
echo [2/3] Configurando rutas y base de datos...
echo      - Base de Datos: Hostinger (aratio_v1)
echo      - URL Local: http://localhost:8000
echo      - Router: router.php (Emulando .htaccess de produccion)
echo.

echo [3/3] Iniciando servidor PHP...
echo.
echo ------------------------------------------------------------
echo  SERVIDOR ACTIVO: http://localhost:8000
echo  Presiona Ctrl+C para detener el servidor.
echo ------------------------------------------------------------
echo.

REM Intentar abrir el navegador (opcional, pausado un segundo)
timeout /t 2 /nobreak >nul
start http://localhost:8000

REM Iniciar servidor
php -S localhost:8000 router.php

if %ERRORLEVEL% neq 0 (
    echo.
    echo [!] El servidor se detuvo con errores.
)

pause
