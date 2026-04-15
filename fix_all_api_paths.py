# -*- coding: utf-8 -*-
"""
FIX EXHAUSTIVO: Corrige las rutas de fetch en todos los pages/*.php
El problema: fetch('/api/campanas.php') -> debe ser fetch('/aratio/api/campanas.php')
"""
import paramiko, sys, io, re
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

def run(cmd):
    _, out, err = client.exec_command(cmd)
    return out.read().decode('utf-8'), err.read().decode('utf-8')

sftp = client.open_sftp()

# Detect APP_SUBPATH
print("Detecting APP_SUBPATH...")
out, _ = run(f"grep -n \"define.*SUBPATH\\|define.*APP_URL\\|APP_SUBPATH\" {ARATIO}/root_config.php | head -5")
print(out)

def fix_fetch_paths(content, subpath):
    original = content
    content = re.sub(r"fetch\('(/api/)", f"fetch('{subpath}/api/", content)
    content = re.sub(r'fetch\("(/api/)', f'fetch("{subpath}/api/', content)
    content = re.sub(r"fetch\(`(/api/)", f"fetch(`{subpath}/api/", content)
    changed = content != original
    return content, changed

pages = ['campanas', 'candidatos', 'elecciones', 'grupos', 'usuarios', 'jac']
subpath = '/aratio'
total_fixed = 0

for page in pages:
    path = f"{ARATIO}/pages/{page}.php"
    try:
        with sftp.open(path, 'r') as f:
            content = f.read().decode('utf-8')
        if '/aratio/api/' in content:
            print(f"[OK] {page}.php - ya tiene /aratio/api/, sin cambios")
            continue
        new_content, changed = fix_fetch_paths(content, subpath)
        if changed:
            with sftp.open(path, 'w') as f:
                f.write(new_content)
            total_fixed += 1
            count = len(re.findall(r"fetch\(['\"`]/aratio/api/", new_content))
            print(f"[FIXED] {page}.php - {count} rutas corregidas")
        else:
            print(f"[SKIP] {page}.php - no se encontraron rutas /api/ en fetch()")
    except Exception as e:
        print(f"[ERROR] {page}.php: {e}")

print(f"\nTotal archivos corregidos: {total_fixed}")

# Check config
print("\nconfig/config.php:")
with sftp.open(f"{ARATIO}/config/config.php", 'r') as f:
    config_content = f.read().decode('utf-8')
print(config_content[:500])

sftp.close()
client.close()
