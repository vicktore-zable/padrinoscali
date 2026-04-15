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

echo "--- Auditoría de Conexión ---\\n";
echo "Config DB_HOST: " . DB_HOST . "\\n";
echo "Detectado isLocal: " . (isset(\$isLocal) && \$isLocal ? 'SÍ' : 'NO') . "\\n";
echo "Entorno (APP_ENV): " . APP_ENV . "\\n";

try {{
    \$db = getDB();
    \$hostinfo = \$db->query("SELECT @@hostname as host, DATABASE() as db")->fetch(PDO::FETCH_ASSOC);
    echo "MySQL Server Hostname: " . \$hostinfo['host'] . "\\n";
    echo "Base de Datos Activa: " . \$hostinfo['db'] . "\\n";
    echo "Conexión RESULTADO: EXITOSA (Local)\\n";
}} catch(Exception \$e) {{
    echo "ERROR: " . \$e->getMessage() . "\\n";
}}
?>"""

stdin, stdout, stderr = client.exec_command("php")
stdin.write(php_diag)
stdin.channel.shutdown_write()

print(stdout.read().decode('utf-8'))
print(stderr.read().decode('utf-8'))

client.close()
