# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

def cmd(c, label=""):
    stdin, stdout, stderr = client.exec_command(c)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    if label: print(f"\n--- {label} ---")
    if out: print(out)
    return out

# Leer configuracion de la DB
cmd(f"cat {ARATIO}/root_config.php | grep -i 'DB_' -A 2 -B 2", "Contenido de root_config.php (variables de DB)")
cmd(f"cat {ARATIO}/includes/DatabaseManager.php | grep -i 'password' -A 4 -B 4", "Contenido de DatabaseManager.php (variables de DB)")

client.close()
