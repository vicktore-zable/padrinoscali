# -*- coding: utf-8 -*-
import paramiko, sys, io
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

# The CRUD is embedded inline in the PHP page files using Alpine.js x-data
# Let's extract the JS fetch calls from campanas.php, candidatos.php, grupos.php, elecciones.php
pages = ['campanas', 'candidatos', 'elecciones', 'grupos']
for page in pages:
    print(f"\n{'='*60}")
    print(f"fetch calls in pages/{page}.php")
    print(f"{'='*60}")
    out, _ = run(f"grep -n 'fetch\\|X-Requested\\|Content-Type\\|ajax\\|headers' {ARATIO}/pages/{page}.php | head -30")
    print(out if out.strip() else "(no fetch/header lines found)")

client.close()
