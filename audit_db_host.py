# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

# Buscar cualquier mención de la base de datos externa en todo el proyecto
cmd = f"grep -r 'auth-db690.hstgr.io' {ARATIO}"
stdin, stdout, stderr = client.exec_command(cmd)
print("--- Ocurrencias de auth-db690.hstgr.io ---")
print(stdout.read().decode('utf-8'))

# Verificar root_config.php
stdin, stdout, stderr = client.exec_command(f"cat {ARATIO}/root_config.php")
print("--- root_config.php ---")
print(stdout.read().decode('utf-8'))

# Verificar configuración del módulo de líderes
stdin, stdout, stderr = client.exec_command(f"cat {ARATIO}/mod_lider/config/database.php")
print("--- mod_lider/config/database.php ---")
print(stdout.read().decode('utf-8'))

client.close()
