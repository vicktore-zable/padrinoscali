#!/usr/bin/env bash
# ==============================================================================
# sync-xampp-to-mirror.sh
# Sincroniza el código fuente de XAMPP (localhost) → Aratio-App-Mirror
# Ejecutar: bash sync-xampp-to-mirror.sh
# ==============================================================================

set -euo pipefail

XAMPP_ROOT="F:/xampp2/htdocs/aratio"
MIRROR_ROOT="H:/Mi unidad/2026/Cali/Edisongiraldo.com/Aratio-App-Mirror"
TIMESTAMP=$(date "+%Y-%m-%d %H:%M:%S")

# Directorios del core de la aplicación (código activo)
APP_DIRS=("pages" "api" "includes" "cron" "config" "mod_colab" "mod_lider" "mod_elecciones" "mod_jac" "mod_organizaciones" "mod_diaD" "mod_voluntariado")

echo "=== Sync XAMPP → Mirror ==="
echo "Origen:  $XAMPP_ROOT"
echo "Destino: $MIRROR_ROOT"
echo ""

# 1. Sincronizar directorios del core
for dir in "${APP_DIRS[@]}"; do
    src="$XAMPP_ROOT/$dir"
    dst="$MIRROR_ROOT/$dir"
    if [ -d "$src" ]; then
        mkdir -p "$dst"
        rsync -a --delete "$src/" "$dst/" 2>/dev/null || cp -r "$src/"* "$dst/"
        echo "✅ $dir/"
    fi
done

# 2. Sincronizar archivos raíz del core de la aplicación
ROOT_FILES=(
    "index.php" "login.php" "logout.php" "landing.php"
    "root_config.php" "router.php" ".htaccess" "public.php"
    "cp_captura.php" "registro_simpatizante.php" "registro-lider.php"
    "registro_asistencia.php" "composer.json"
)

for f in "${ROOT_FILES[@]}"; do
    if [ -f "$XAMPP_ROOT/$f" ]; then
        cp "$XAMPP_ROOT/$f" "$MIRROR_ROOT/$f"
        echo "✅ $f"
    fi
done

# 3. Actualizar estado en DIAGNOSTICO_SINCRONIZACION.md
DIAG="$MIRROR_ROOT/DIAGNOSTICO_SINCRONIZACION.md"
if [ -f "$DIAG" ]; then
    # Registrar la sincronización
    echo "" >> "$DIAG"
    echo "### Sync: $TIMESTAMP" >> "$DIAG"
    echo "- Sincronización manual completada" >> "$DIAG"
fi

echo ""
echo "=== Sync completo: $TIMESTAMP ==="