# -*- coding: utf-8 -*-
"""debug_500.py — Diagnostica el error 500 en /aratio/"""
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST = "157.173.208.254"; PORT = 65002
USER = "u577647812"; PASSWORD = 'E=j$`01yHi^?XfpoM@|CD"5H4'
REMOTE = "/home/u577647812/domains/edisongiraldo.com/public_html"
ARATIO = f"{REMOTE}/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=30)
print("[OK] Conectado\n")

def cmd(c, label=""):
    stdin, stdout, stderr = client.exec_command(c)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    if label: print(f"=== {label} ===")
    if out: print(out)
    if err: print(f"[STDERR] {err}")
    print()
    return out

# 1. Log de errores PHP (raiz y aratio)
cmd(f"tail -50 {REMOTE}/php-errors.log 2>/dev/null || echo 'No log en raiz'", "PHP Error Log (raiz)")
cmd(f"tail -50 {ARATIO}/php-errors.log 2>/dev/null || echo 'No log en /aratio/'", "PHP Error Log (aratio)")

# 2. Ver que incluye index.php
cmd(f"head -30 {ARATIO}/index.php", "index.php (primeras 30 lineas)")

# 3. Ver si root_config.php existe en algun lugar
cmd(f"find {REMOTE} -name 'root_config.php' 2>/dev/null", "Buscar root_config.php")

# 4. Ver que hay en la raiz ahora
cmd(f"ls -la {REMOTE}/", "Contenido de public_html raiz")

# 5. Verificar htaccess activo
cmd(f"cat {ARATIO}/.htaccess", ".htaccess en /aratio/")

# 6. Verificar config.php
cmd(f"php -l {ARATIO}/config/config.php 2>&1", "Sintaxis config.php")
cmd(f"php -l {ARATIO}/index.php 2>&1", "Sintaxis index.php")

# 7. Ver si hay includes que fallan
cmd(f"grep -n 'require\|include' {ARATIO}/index.php | head -20", "includes en index.php")

client.close()
print("=== FIN DIAGNOSTICO ===")
