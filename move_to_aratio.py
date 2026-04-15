# -*- coding: utf-8 -*-
"""
move_to_aratio.py
Mueve los archivos del servidor de /public_html/ raiz -> /public_html/aratio/
y sube los configs corregidos.
"""

import paramiko
import os
import sys

# Fix encoding en consola Windows
import io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')

# =====================================================================
# CONFIGURACIÓN
# =====================================================================
HOST     = "157.173.208.254"
PORT     = 65002
USER     = "u577647812"
PASSWORD = 'E=j$`01yHi^?XfpoM@|CD"5H4'

REMOTE_ROOT   = "/home/u577647812/domains/edisongiraldo.com/public_html"
REMOTE_ARATIO = f"{REMOTE_ROOT}/aratio"

# Configs locales corregidos (workspace Edison Giraldo)
LOCAL_CONFIG_PHP  = r"h:\Mi unidad\2026\Cali\Edisongiraldo.com\config\config.php"
LOCAL_HTACCESS    = r"h:\Mi unidad\2026\Cali\Edisongiraldo.com\.htaccess"

def run_cmd(client, cmd, label=""):
    """Ejecuta un comando SSH e imprime resultado."""
    print(f"\n  {'[CMD]':8} {label or cmd}")
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode("utf-8", errors="replace").strip()
    err = stderr.read().decode("utf-8", errors="replace").strip()
    if out:
        print(f"  {'[OUT]':8} {out}")
    if err:
        print(f"  {'[ERR]':8} {err}")
    return out, err

def main():
    print()
    print("=" * 60)
    print("  DEPLOY: Mover archivos -> /aratio/ en edisongiraldo.com")
    print("=" * 60)

    # ------------------------------------------------------------------
    # CONECTAR
    # ------------------------------------------------------------------
    print(f"\n[1/6] Conectando a {HOST}:{PORT}...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=30)
        print("  ✅ Conexión SSH establecida")
    except Exception as e:
        print(f"  [ERR] Error de conexión: {e}")
        sys.exit(1)

    sftp = client.open_sftp()

    # ------------------------------------------------------------------
    # VER QUÉ HAY EN public_html
    # ------------------------------------------------------------------
    print(f"\n[2/6] Listando contenido actual de {REMOTE_ROOT}...")
    out, _ = run_cmd(client, f"ls -la {REMOTE_ROOT}/", "ls public_html")

    # ------------------------------------------------------------------
    # CREAR DIRECTORIO /aratio/
    # ------------------------------------------------------------------
    print(f"\n[3/6] Creando directorio /aratio/...")
    run_cmd(client, f"mkdir -p {REMOTE_ARATIO}", "mkdir -p /aratio/")
    run_cmd(client, f"chmod 755 {REMOTE_ARATIO}", "chmod 755 /aratio/")
    print("  ✅ Directorio /aratio/ listo")

    # ------------------------------------------------------------------
    # MOVER ARCHIVOS DE LA RAÍZ -> /aratio/
    # Mueve todo EXCEPTO /aratio/ mismo y archivos raíz especiales
    # ------------------------------------------------------------------
    print(f"\n[4/6] Moviendo archivos de la raíz -> /aratio/...")

    # Lista de items a mover (los que son de la app)
    items_app = [
        "index.php", "login.php", "logout.php",
        "registro-lider.php", "registro_simpatizante.php",
        "mapa_territorios.php", "registro_asistencia.php",
        "api", "includes", "pages", "public",
        "mod_colab", "mod_diaD", "mod_elecciones", "mod_jac", "mod_lider",
        "cache", "uploads", "storage", "config", "database",
        "router.php", "public.php",
    ]

    moved = 0
    skipped = 0
    for item in items_app:
        src = f"{REMOTE_ROOT}/{item}"
        dst = f"{REMOTE_ARATIO}/{item}"
        # Verificar si existe en raíz
        check_out, _ = run_cmd(client, f"test -e '{src}' && echo EXISTS || echo MISSING", f"check {item}")
        if "EXISTS" in check_out:
            run_cmd(client, f"mv '{src}' '{REMOTE_ARATIO}/'", f"mv {item} -> /aratio/")
            moved += 1
        else:
            print(f"  [SKIP] {item} — no encontrado en raíz")
            skipped += 1

    print(f"\n  ✅ Movidos: {moved} | Saltados (no existían): {skipped}")

    # ------------------------------------------------------------------
    # SUBIR CONFIGS CORREGIDOS
    # ------------------------------------------------------------------
    print(f"\n[5/6] Subiendo archivos de configuración corregidos...")

    # Crear directorio config si no existe en /aratio/
    run_cmd(client, f"mkdir -p {REMOTE_ARATIO}/config", "mkdir -p /aratio/config/")

    # Subir config.php corregido
    print(f"  Subiendo config.php (DB: u577647812_aratio)...")
    try:
        sftp.put(LOCAL_CONFIG_PHP, f"{REMOTE_ARATIO}/config/config.php")
        print(f"  ✅ config.php subido correctamente")
    except Exception as e:
        print(f"  [ERR] Error subiendo config.php: {e}")

    # Subir .htaccess corregido
    print(f"  Subiendo .htaccess (RewriteBase /aratio/)...")
    try:
        sftp.put(LOCAL_HTACCESS, f"{REMOTE_ARATIO}/.htaccess")
        print(f"  ✅ .htaccess subido correctamente")
    except Exception as e:
        print(f"  [ERR] Error subiendo .htaccess: {e}")

    # ------------------------------------------------------------------
    # VERIFICAR RESULTADO
    # ------------------------------------------------------------------
    print(f"\n[6/6] Verificando estructura final en /aratio/...")
    run_cmd(client, f"ls -la {REMOTE_ARATIO}/", "ls /aratio/")

    print(f"\n  Verificando DB en config.php:")
    run_cmd(client, f"grep -n 'DB_NAME\|DB_HOST\|APP_URL' {REMOTE_ARATIO}/config/config.php", "grep config")

    # ------------------------------------------------------------------
    # CIERRE
    # ------------------------------------------------------------------
    sftp.close()
    client.close()

    print()
    print("=" * 60)
    print("  ✅ DEPLOY COMPLETADO")
    print(f"  [URL] Verificar: https://edisongiraldo.com/aratio/")
    print(f"  [URL] Preview:   https://navajowhite-goose-984880.hostingersite.com/aratio/")
    print("=" * 60)
    print()

if __name__ == "__main__":
    main()
