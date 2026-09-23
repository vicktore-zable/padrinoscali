# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

stdin, stdout, stderr = client.exec_command(f"ls -F {ARATIO}/api")
print("--- API files ---")
print(stdout.read().decode('utf-8'))

stdin, stdout, stderr = client.exec_command(f"cat {ARATIO}/pages/usuarios.php | head -n 100")
print("--- pages/usuarios.php (Logic) ---")
print(stdout.read().decode('utf-8'))

client.close()
