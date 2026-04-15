# -*- coding: utf-8 -*-
"""
upload_missing.py
Sube los modulos y archivos faltantes al servidor en /aratio/
Fuente: Multi-Campaign Management System (local)
Destino: edisongiraldo.com/aratio/
"""

import paramiko
import os
import sys
import io

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')

# =====================================================================
# CONFIGURACION
# =====================================================================
HOST     = "157.173.208.254"
PORT     = 65002
USER     = "u577647812"
PASSWORD = 'E=j$`01yHi^?XfpoM@|CD"5H4'

REMOTE_ARATIO = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"
LOCAL_SOURCE  = r"H:\Mi unidad\2025\5d\app\Multi-Campaign Management System"

# Archivos y directorios a subir (los que faltaban)
ITEMS_FALTANTES = [
    "mod_diaD",
    "mod_lider",
    "registro-lider.php",
    "registro_simpatizante.php",
    "mapa_territorios.php",
    "registro_asistencia.php",
    "storage",
    "database",
    "router.php",
    "public.php",
    # Archivos PHP adicionales del root
    "mapa_standalone.php",
    "save_view.php",
]

# Directorios que necesitan existir vacios si no hay contenido
DIRS_EMPTY = ["uploads", "storage", "logs"]

def upload_dir(sftp, local_path, remote_path, client):
    """Sube un directorio recursivamente por SFTP."""
    # Crear directorio remoto
    try:
        sftp.mkdir(remote_path)
    except IOError:
        pass  # Ya existe

    for item in os.listdir(local_path):
        local_item = os.path.join(local_path, item)
        remote_item = f"{remote_path}/{item}"

        # Ignorar archivos de desarrollo
        if item in ['.git', '__pycache__', '.ruff_cache', 'node_modules', '.venv']:
            continue
        if item.endswith(('.py', '.ps1', '.sh', '.bat', '.sql', '.md')) and item not in ['index.php']:
            # Permitir solo ciertos tipos en subida
            pass

        if os.path.isdir(local_item):
            upload_dir(sftp, local_item, remote_item, client)
        else:
            try:
                sftp.put(local_item, remote_item)
                print(f"    [+] {remote_item.replace(REMOTE_ARATIO, '')}")
            except Exception as e:
                print(f"    [!] Error subiendo {item}: {e}")

def main():
    print()
    print("=" * 60)
    print("  UPLOAD: Modulos faltantes -> /aratio/")
    print("=" * 60)

    # ------------------------------------------------------------------
    # CONECTAR
    # ------------------------------------------------------------------
    print(f"\n[1] Conectando a {HOST}:{PORT}...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=30)
        print("  [OK] Conexion SSH establecida")
    except Exception as e:
        print(f"  [ERR] Error: {e}")
        sys.exit(1)

    sftp = client.open_sftp()

    # ------------------------------------------------------------------
    # CREAR DIRECTORIOS VACIOS NECESARIOS
    # ------------------------------------------------------------------
    print(f"\n[2] Creando directorios base...")
    for d in DIRS_EMPTY:
        remote_d = f"{REMOTE_ARATIO}/{d}"
        try:
            sftp.mkdir(remote_d)
            print(f"  [+] Creado: /aratio/{d}/")
        except IOError:
            print(f"  [=] Ya existe: /aratio/{d}/")

    # ------------------------------------------------------------------
    # SUBIR ITEMS FALTANTES
    # ------------------------------------------------------------------
    print(f"\n[3] Subiendo archivos faltantes...")
    total_ok = 0
    total_err = 0

    for item in ITEMS_FALTANTES:
        local_path = os.path.join(LOCAL_SOURCE, item)
        remote_path = f"{REMOTE_ARATIO}/{item}"

        if not os.path.exists(local_path):
            print(f"\n  [SKIP] {item} — no encontrado localmente")
            continue

        print(f"\n  Subiendo: {item}")

        if os.path.isdir(local_path):
            try:
                sftp.mkdir(remote_path)
            except IOError:
                pass
            # Contar archivos
            file_count = sum(len(files) for _, _, files in os.walk(local_path))
            print(f"    Directorio con {file_count} archivos...")
            upload_dir(sftp, local_path, remote_path, client)
            print(f"    [OK] {item}/ subido")
            total_ok += 1
        else:
            try:
                sftp.put(local_path, remote_path)
                print(f"    [OK] {item} subido")
                total_ok += 1
            except Exception as e:
                print(f"    [ERR] {item}: {e}")
                total_err += 1

    # ------------------------------------------------------------------
    # VERIFICAR ESTRUCTURA FINAL
    # ------------------------------------------------------------------
    print(f"\n[4] Estructura final en /aratio/...")
    stdin, stdout, stderr = client.exec_command(f"ls -la {REMOTE_ARATIO}/")
    print(stdout.read().decode('utf-8', errors='replace'))

    # Verificar que login funciona (config.php legible)
    print(f"\n[5] Verificando config.php en servidor...")
    stdin, stdout, stderr = client.exec_command(
        f"grep -E \"DB_NAME|DB_HOST|APP_URL\" {REMOTE_ARATIO}/config/config.php"
    )
    out = stdout.read().decode('utf-8', errors='replace')
    print(out)

    sftp.close()
    client.close()

    print()
    print("=" * 60)
    print(f"  UPLOAD COMPLETADO — OK: {total_ok} | Errores: {total_err}")
    print(f"  Verificar: https://edisongiraldo.com/aratio/")
    print(f"  Preview:   https://navajowhite-goose-984880.hostingersite.com/aratio/")
    print("=" * 60)
    print()

if __name__ == "__main__":
    main()
