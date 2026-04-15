# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

sftp = client.open_sftp()
with sftp.open(f"{ARATIO}/api/candidatos.php", 'r') as f:
    content = f.read().decode('utf-8')

# Add logging at the beginning of the try block
logging_code = """
try {
    error_log("API Candidatos Request: " . $method . " - ID: " . ($_GET['id'] ?? 'none'));
"""
content = content.replace("try {", logging_code, 1)

with sftp.open(f"{ARATIO}/api/candidatos.php", 'w') as f:
    f.write(content)
sftp.close()
client.close()
