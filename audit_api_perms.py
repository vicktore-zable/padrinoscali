# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

stdin, stdout, stderr = client.exec_command(f"ls -la {ARATIO}/api/")
print("--- Permissions in api/ ---")
print(stdout.read().decode('utf-8'))

# También revisar si hay un .htaccess interno en api/
stdin, stdout, stderr = client.exec_command(f"ls -la {ARATIO}/api/.htaccess")
print("--- .htaccess in api/ ---")
print(stdout.read().decode('utf-8'))

client.close()
