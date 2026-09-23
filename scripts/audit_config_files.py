# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

files_to_check = [
    f"{ARATIO}/root_config.php",
    f"{ARATIO}/config/database.php",
    f"{ARATIO}/mod_lider/config/database.php",
    f"{ARATIO}/mod_lider/src/bootstrap.php"
]

for f in files_to_check:
    print(f"--- Checking {f} ---")
    stdin, stdout, stderr = client.exec_command(f"cat {f}")
    content = stdout.read().decode('utf-8')
    if "auth-db690.hstgr.io" in content:
        print(f"ALERTA: Se encontró 'auth-db690.hstgr.io' en {f}")
    else:
        print(f"OK: No se encontró 'auth-db690.hstgr.io' en {f}")
    
    # Mostrar extracto de DB_HOST
    for line in content.splitlines():
        if "DB_HOST" in line or "host" in line.lower():
            if "'" in line or '"' in line:
                print(f"  Line: {line.strip()}")

client.close()
