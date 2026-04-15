# -*- coding: utf-8 -*-
"""fix_500.py — Corrige el error 500: copia config correcto y parchea index.php"""
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST = "157.173.208.254"; PORT = 65002
USER = "u577647812"; PASSWORD = 'E=j$`01yHi^?XfpoM@|CD"5H4'
REMOTE = "/home/u577647812/domains/edisongiraldo.com/public_html"
ARATIO = f"{REMOTE}/aratio"

# Config correcto ya subido en /aratio/config/config.php
LOCAL_CONFIG = r"h:\Mi unidad\2026\Cali\Edisongiraldo.com\config\config.php"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=30)
sftp = client.open_sftp()
print("[OK] Conectado\n")

def cmd(c, label=""):
    stdin, stdout, stderr = client.exec_command(c)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    if label: print(f"--- {label} ---")
    if out: print(out)
    if err: print(f"[STDERR] {err}")
    print()
    return out

# ----------------------------------------------------------------
# FIX 1: Copiar config/config.php -> root_config.php en /aratio/
# Así index.php encuentra su require_once '/root_config.php'
# ----------------------------------------------------------------
print("[1] Copiando config.php -> root_config.php en /aratio/...")
cmd(f"cp {ARATIO}/config/config.php {ARATIO}/root_config.php",
    "cp config.php -> root_config.php")

# Verificar
out = cmd(f"head -5 {ARATIO}/root_config.php", "Verificar root_config.php")
if "ARATIO" in out or "php" in out.lower():
    print("[OK] root_config.php en /aratio/ creado correctamente\n")

# ----------------------------------------------------------------
# FIX 2: Parchear root_config.php para que el log apunte al lugar correcto
# ----------------------------------------------------------------
print("[2] Ajustando ruta del error_log en root_config.php...")
cmd(
    f"sed -i \"s|/home/u156469157/domains/aratio.mrmtech.net/public_html/php-errors.log"
    f"|/home/u577647812/domains/edisongiraldo.com/public_html/php-errors.log|g\" "
    f"{ARATIO}/root_config.php",
    "sed error_log path"
)

# ----------------------------------------------------------------
# FIX 3: Verificar que DatabaseManager.php y CacheManager.php existan
# ----------------------------------------------------------------
print("[3] Verificando dependencias de includes/...")
cmd(f"ls {ARATIO}/includes/", "ls includes/")

# ----------------------------------------------------------------
# FIX 4: Verificar sintaxis del root_config.php final
# ----------------------------------------------------------------
cmd(f"php -l {ARATIO}/root_config.php", "Sintaxis root_config.php")

# ----------------------------------------------------------------
# FIX 5: Habilitar errores temporalmente para ver el error real
# ----------------------------------------------------------------
print("[4] Habilitando display_errors temporalmente en root_config.php...")
cmd(
    f"sed -i \"s|ini_set('display_errors', 0)|ini_set('display_errors', 1)|g\" "
    f"{ARATIO}/root_config.php",
    "Habilitar display_errors"
)

# ----------------------------------------------------------------
# FIX 6: Verificar log de errores de Apache/Hostinger
# ----------------------------------------------------------------
cmd("find /home/u577647812 -name 'error.log' 2>/dev/null | head -5", "Buscar error.log Apache")
cmd("find /home/u577647812 -name '*.log' 2>/dev/null | head -10", "Todos los logs")

# ----------------------------------------------------------------
# VERIFICACION FINAL
# ----------------------------------------------------------------
print("[5] Verificacion final de /aratio/...")
cmd(f"ls -la {ARATIO}/ | grep root_config", "root_config.php en /aratio/")
cmd(
    f"grep -n 'DB_NAME\\|DB_HOST\\|APP_URL\\|display_errors\\|error_log' {ARATIO}/root_config.php | head -15",
    "Config activo en root_config.php"
)

sftp.close()
client.close()
print("\n[OK] Fix aplicado. Prueba https://edisongiraldo.com/aratio/")
print("     Si aun hay error, el mensaje aparecera en pantalla (display_errors=1)")
