# -*- coding: utf-8 -*-
"""read_logs.py — Lee el log de errores PHP del servidor"""
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST = "157.173.208.254"; PORT = 65002
USER = "u577647812"; PASSWORD = 'E=j$`01yHi^?XfpoM@|CD"5H4'
REMOTE = "/home/u577647812/domains/edisongiraldo.com/public_html"
ARATIO = f"{REMOTE}/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

def cmd(c, label=""):
    stdin, stdout, stderr = client.exec_command(c)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    if label: print(f"\n=== {label} ===")
    print(out if out else "(vacio)")
    return out

# Log principal de PHP
cmd(f"tail -40 {REMOTE}/php-errors.log", "php-errors.log (raiz)")

# Logs de módulos
cmd(f"tail -20 {ARATIO}/storage/logs/app.log 2>/dev/null", "storage/logs/app.log")
cmd(f"tail -20 {ARATIO}/mod_colab/debug_route.log 2>/dev/null", "mod_colab debug_route.log")

# Intentar ejecutar index.php directamente via PHP CLI para ver el error exacto
cmd(f"cd {ARATIO} && php -r \"chdir('{ARATIO}'); \$_SERVER['HTTP_HOST']='edisongiraldo.com'; \$_SERVER['REQUEST_URI']='/aratio/'; include 'index.php';\" 2>&1 | head -40",
    "PHP CLI test de index.php")

# Verificar que DatabaseManager.php tenga la clase correcta
cmd(f"grep -n 'class Database' {ARATIO}/includes/DatabaseManager.php | head -5", "DatabaseManager class")

# Ver si hay public_html anidado (encontré uno raro en el ls anterior)
cmd(f"ls -la {REMOTE}/public_html/", "public_html anidado (raro)")

client.close()
print("\n=== FIN ===")
