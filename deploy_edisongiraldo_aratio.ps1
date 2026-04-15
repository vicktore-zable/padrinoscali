# =====================================================================
# DEPLOY ARATIO → edisongiraldo.com/aratio/
# Version: 2.0.0 | 2026-04-14
#
# PREREQUISITO: tener plink.exe y pscp.exe (PuTTY tools) instalados
# o usar Git Bash con openssh disponible.
#
# USO: Ejecutar desde PowerShell en el directorio del proyecto
# .\deploy_edisongiraldo_aratio.ps1
# =====================================================================

# =====================================================================
# CONFIGURACIÓN
# =====================================================================
$SERVER_HOST  = "157.173.208.254"
$SERVER_PORT  = "65002"
$SERVER_USER  = "u577647812"
$SERVER_PASS  = 'E=j$`01yHi^?XfpoM@|CD"5H4'
$REMOTE_BASE  = "/home/u577647812/domains/edisongiraldo.com/public_html"
$REMOTE_ARATIO = "$REMOTE_BASE/aratio"

# Fuente: usar el workspace nuevo Edison Giraldo (tiene los configs corregidos)
$LOCAL_CONFIG  = "H:\Mi unidad\2026\Cali\Edisongiraldo.com"
# Fuente: el código fuente completo original
$LOCAL_SOURCE  = "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System"

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "   DEPLOY ARATIO → edisongiraldo.com/aratio/              " -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# =====================================================================
# PASO 1: CREAR DIRECTORIO /aratio/ EN EL SERVIDOR
# =====================================================================
Write-Host "[1/5] Creando directorio /aratio/ en el servidor..." -ForegroundColor Yellow
Write-Host ""
Write-Host "Ejecuta este comando en Git Bash o WSL:" -ForegroundColor White
Write-Host "  ssh -p $SERVER_PORT ${SERVER_USER}@${SERVER_HOST} 'mkdir -p $REMOTE_ARATIO'" -ForegroundColor Green
Write-Host ""
Write-Host "Password SSH: $SERVER_PASS" -ForegroundColor DarkGray
Write-Host ""

# =====================================================================
# PASO 2: SUBIR CONFIGS CORREGIDOS (desde workspace nuevo)
# =====================================================================
Write-Host "[2/5] Archivos de configuración corregidos a subir:" -ForegroundColor Yellow
Write-Host "  Origen:  $LOCAL_CONFIG" -ForegroundColor White
Write-Host "  Destino: $REMOTE_ARATIO" -ForegroundColor White
Write-Host ""
Write-Host "Archivos críticos (SUBIR PRIMERO — tienen la DB correcta):" -ForegroundColor Magenta
Write-Host "  - config/config.php    (DB: u577647812_aratio)" -ForegroundColor Green
Write-Host "  - .htaccess            (RewriteBase /aratio/)" -ForegroundColor Green
Write-Host ""
Write-Host "Comando SCP para configs:" -ForegroundColor White
Write-Host "  scp -P $SERVER_PORT '$LOCAL_CONFIG/config/config.php' ${SERVER_USER}@${SERVER_HOST}:${REMOTE_ARATIO}/config/" -ForegroundColor Green
Write-Host "  scp -P $SERVER_PORT '$LOCAL_CONFIG/.htaccess' ${SERVER_USER}@${SERVER_HOST}:${REMOTE_ARATIO}/" -ForegroundColor Green
Write-Host ""

# =====================================================================
# PASO 3: SUBIR CÓDIGO FUENTE COMPLETO (desde fuente original)
# =====================================================================
Write-Host "[3/5] Subir código fuente completo:" -ForegroundColor Yellow
Write-Host "  Origen:  $LOCAL_SOURCE" -ForegroundColor White
Write-Host "  Destino: ${REMOTE_ARATIO}/" -ForegroundColor White
Write-Host ""
Write-Host "Comando SCP (directorios principales):" -ForegroundColor White
$archivosASubir = @(
    "index.php", "login.php", "logout.php", "registro-lider.php",
    "registro_simpatizante.php", "mapa_territorios.php",
    "api", "includes", "pages", "public",
    "mod_colab", "mod_diaD", "mod_elecciones", "mod_jac", "mod_lider",
    "cache", "uploads", "storage"
)
foreach ($archivo in $archivosASubir) {
    Write-Host "  scp -rP $SERVER_PORT '$LOCAL_SOURCE/$archivo' ${SERVER_USER}@${SERVER_HOST}:${REMOTE_ARATIO}/" -ForegroundColor DarkGreen
}
Write-Host ""

# =====================================================================
# PASO 4: LIMPIAR RAÍZ (mover lo que quedó en public_html a /aratio/)
# =====================================================================
Write-Host "[4/5] Limpiar raíz de public_html:" -ForegroundColor Yellow
Write-Host ""
Write-Host "IMPORTANTE: El código actual está en la RAÍZ. Después del deploy en /aratio/," -ForegroundColor Red
Write-Host "debes eliminar los archivos de la raíz (conservar solo index.html landing)." -ForegroundColor Red
Write-Host ""
Write-Host "Ejecutar por SSH:" -ForegroundColor White
Write-Host @"
  ssh -p $SERVER_PORT ${SERVER_USER}@${SERVER_HOST}
  # Una vez conectado, ejecutar:
  cd /home/u577647812/domains/edisongiraldo.com/public_html
  # Verificar que /aratio funciona primero, luego limpiar raíz:
  rm -f index.php login.php logout.php
  rm -rf api includes pages mod_colab mod_diaD mod_elecciones mod_jac mod_lider
  rm -f .htaccess  # Remover el viejo de la raíz
"@ -ForegroundColor DarkYellow
Write-Host ""

# =====================================================================
# PASO 5: VERIFICAR FUNCIONALIDAD
# =====================================================================
Write-Host "[5/5] Verificación post-deploy:" -ForegroundColor Yellow
Write-Host ""
Write-Host "URLs a verificar:" -ForegroundColor White
Write-Host "  ✅ https://edisongiraldo.com/aratio/         (index.php - debería redirigir a login)" -ForegroundColor Cyan
Write-Host "  ✅ https://edisongiraldo.com/aratio/login    (login form)" -ForegroundColor Cyan
Write-Host "  ✅ https://edisongiraldo.com/aratio/JAC      (portal JAC)" -ForegroundColor Cyan
Write-Host "  ✅ https://edisongiraldo.com/aratio/mapa-puestos (mapa)" -ForegroundColor Cyan
Write-Host "  ❌ https://edisongiraldo.com/               (no debe mostrar la app — solo landing)" -ForegroundColor DarkRed
Write-Host ""

# =====================================================================
# RESUMEN FINAL
# =====================================================================
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "   CAMBIOS CRÍTICOS EN config.php (ya corregidos localmente)" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "  DB_HOST: localhost" -ForegroundColor Green
Write-Host "  DB_NAME: u577647812_aratio   (era: u156469157_aratio_v1)" -ForegroundColor Green
Write-Host "  DB_USER: u577647812_aratio   (era: u156469157_aratio_v1)" -ForegroundColor Green
Write-Host "  APP_URL: https://edisongiraldo.com/aratio  (era: aratio.mrmtech.net)" -ForegroundColor Green
Write-Host "  RewriteBase: /aratio/        (era: /)" -ForegroundColor Green
Write-Host ""
Write-Host "  Archivos corregidos ubicados en:" -ForegroundColor Yellow
Write-Host "  $LOCAL_CONFIG" -ForegroundColor White
Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
