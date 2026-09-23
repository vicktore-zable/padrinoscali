# -*- coding: utf-8 -*-
"""fix_paths.py — Corrige rutas de includes en root_config.php"""
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST = "157.173.208.254"; PORT = 65002
USER = "u577647812"; PASSWORD = 'E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

def cmd(c, label=""):
    stdin, stdout, stderr = client.exec_command(c)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    if label: print(f"\n--- {label} ---")
    if out: print(out)
    if err: print(f"[ERR] {err}")
    return out

print("=== Corrigiendo rutas en root_config.php ===\n")

# El problema: root_config.php usa __DIR__ . '/../includes/'
# Cuando esta en /aratio/, __DIR__ = /aratio/
# entonces /../includes/ = /public_html/includes/  (NO EXISTE)
# Debe ser: __DIR__ . '/includes/'

# Fix: cambiar '/../includes/' por '/includes/'
cmd(
    f"sed -i \"s|__DIR__ . '/../includes/|__DIR__ . '/includes/|g\" {ARATIO}/root_config.php",
    "Fix /../includes/ -> /includes/"
)

# Fix: cambiar '/../cache' por '/cache'
cmd(
    f"sed -i \"s|__DIR__ . '/../cache'|__DIR__ . '/cache'|g\" {ARATIO}/root_config.php",
    "Fix /../cache -> /cache"
)

# Fix: cambiar '/../uploads/' por '/uploads/'
cmd(
    f"sed -i \"s|__DIR__ . '/../uploads/'|__DIR__ . '/uploads/'|g\" {ARATIO}/root_config.php",
    "Fix /../uploads/ -> /uploads/"
)

# Tambien en config/config.php (copia de respaldo)
cmd(
    f"sed -i \"s|__DIR__ . '/../includes/|__DIR__ . '/includes/|g\" {ARATIO}/config/config.php",
    "Fix config.php /../includes/"
)
cmd(
    f"sed -i \"s|__DIR__ . '/../cache'|__DIR__ . '/cache'|g\" {ARATIO}/config/config.php",
    "Fix config.php /../cache"
)
cmd(
    f"sed -i \"s|__DIR__ . '/../uploads/'|__DIR__ . '/uploads/'|g\" {ARATIO}/config/config.php",
    "Fix config.php /../uploads/"
)

# Verificar los requires ahora
cmd(f"grep -n 'require_once\\|UPLOAD_PATH\\|cacheDir' {ARATIO}/root_config.php | grep '__DIR__'",
    "Rutas __DIR__ en root_config.php")

# Verificar sintaxis
out = cmd(f"php -l {ARATIO}/root_config.php", "Sintaxis root_config.php")

# Test mas completo via PHP CLI
print("\n--- PHP CLI test index.php ---")
test_out = cmd(
    f"cd {ARATIO} && php -r \""
    f"\$_SERVER['HTTP_HOST']='edisongiraldo.com';"
    f"\$_SERVER['REQUEST_URI']='/aratio/';"
    f"\$_SERVER['HTTP_X_REQUESTED_WITH']='';"
    f"define('TESTING',1);"
    f"ob_start();"
    f"try {{ include 'root_config.php'; echo 'CONFIG OK'; }} catch(Exception \$e) {{ echo 'ERROR: '.\$e->getMessage(); }}"
    f"\" 2>&1 | head -20"
)

client.close()
print("\n=== Fix aplicado. Prueba https://edisongiraldo.com/aratio/ ===")
