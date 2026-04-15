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
$cols = $db->query("SHOW FULL COLUMNS FROM campanas")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {{
    echo "$col[Field] | Null: $col[Null] | Default: $col[Default] | Extra: $col[Extra]\\n";
}}
?>"""

stdin, stdout, stderr = client.exec_command("php")
stdin.write(php_diag)
stdin.channel.shutdown_write()

print(stdout.read().decode('utf-8'))
print(stderr.read().decode('utf-8'))

client.close()
