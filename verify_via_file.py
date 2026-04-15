# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

php_content = f"""<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "--- INICIO TEST ---\\n";
require_once '{ARATIO}/root_config.php';
echo "Config cargada.\\n";
\$db = getDB();
if (\$db) echo "BD conectada.\\n";
if (class_exists('Auth')) {{
    echo "Clase Auth existe.\\n";
    \$auth = new Auth(\$db);
    \$campanas = \$auth->getUserCampanas(1);
    echo "Metodo getUserCampanas ejecutado. Encontradas: " . count(\$campanas) . "\\n";
}} else {{
    echo "ERROR: Clase Auth NO encontrada.\\n";
}}
echo "--- FIN TEST ---\\n";
?>"""

# Subir archivo temporal
sftp = client.open_sftp()
with sftp.open(f"{ARATIO}/test_fix.php", "w") as f:
    f.write(php_content)
sftp.close()

# Ejecutar y capturar
stdin, stdout, stderr = client.exec_command(f"php {ARATIO}/test_fix.php")
print("STDOUT:", stdout.read().decode('utf-8'))
print("STDERR:", stderr.read().decode('utf-8'))

# Limpiar
client.exec_command(f"rm {ARATIO}/test_fix.php")

client.close()
