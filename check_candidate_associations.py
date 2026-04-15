# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

php_cmd = f"""<?php
require_once '{ARATIO}/root_config.php';
$db = getDB();
$sql = "SELECT c.id, c.nombre_completo, (SELECT COUNT(*) FROM campanas WHERE candidato_id = c.id) as num_campanas FROM candidatos c";
$stmt = $db->query($sql);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {{
    echo "$row[id]: $row[nombre_completo] ($row[num_campanas] campanas)\\n";
}}
"""

stdin, stdout, stderr = client.exec_command("php")
stdin.write(php_cmd)
stdin.channel.shutdown_write()
print(stdout.read().decode('utf-8'))
client.close()
