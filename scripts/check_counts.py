# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

php_diag = f"""<?php
require_once '{ARATIO}/root_config.php';
$db = getDB();

$countElecciones = $db->query("SELECT COUNT(*) FROM elecciones")->fetchColumn();
$countCandidatos = $db->query("SELECT COUNT(*) FROM candidatos")->fetchColumn();

echo "Elecciones: $countElecciones\\n";
echo "Candidatos: $countCandidatos\\n";

if ($countElecciones == 0) {{
    echo "ALERTA: No hay elecciones creadas.\\n";
}}
if ($countCandidatos == 0) {{
    echo "ALERTA: No hay candidatos creados.\\n";
}}
?>"""

stdin, stdout, stderr = client.exec_command("php")
stdin.write(php_diag)
stdin.channel.shutdown_write()

print(stdout.read().decode('utf-8'))
print(stderr.read().decode('utf-8'))

client.close()
