# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

print("--- index.php first 5 lines ---")
stdin, stdout, stderr = client.exec_command(f"head -n 5 {ARATIO}/index.php")
print(stdout.read().decode('utf-8'))

print("--- api/usuarios.php first 10 lines ---")
stdin, stdout, stderr = client.exec_command(f"head -n 10 {ARATIO}/api/usuarios.php")
print(stdout.read().decode('utf-8'))

print("--- api/grupos.php first 10 lines ---")
stdin, stdout, stderr = client.exec_command(f"head -n 10 {ARATIO}/api/grupos.php")
print(stdout.read().decode('utf-8'))

# Check end of config/config.php for broken paths
stdin, stdout, stderr = client.exec_command(f"tail -n 20 {ARATIO}/config/config.php")
print("--- config/config.php (Tail) ---")
print(stdout.read().decode('utf-8'))

client.close()
