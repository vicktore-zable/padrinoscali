# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

stdin, stdout, stderr = client.exec_command(f"ls {ARATIO}/mod_lider/src/Views/layouts")
print("--- Layouts ---")
print(stdout.read().decode('utf-8'))

# Read one layout to see color config
stdin, stdout, stderr = client.exec_command(f"head -n 50 {ARATIO}/mod_lider/src/Views/layouts/main.php")
print("--- main.php head ---")
print(stdout.read().decode('utf-8'))

client.close()
