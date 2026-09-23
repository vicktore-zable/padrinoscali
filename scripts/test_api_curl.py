# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

# Simulate a request to the API
# We use curl with -H to simulate the AJAX header
cmd = "curl -s -i -H 'X-Requested-With: XMLHttpRequest' 'http://localhost/aratio/api/candidatos.php'"
stdin, stdout, stderr = client.exec_command(cmd)
print(stdout.read().decode('utf-8'))
client.close()
