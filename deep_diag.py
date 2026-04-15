# -*- coding: utf-8 -*-
import paramiko, sys, io, re
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

def run(cmd):
    _, out, err = client.exec_command(cmd)
    return out.read().decode('utf-8'), err.read().decode('utf-8')

sftp = client.open_sftp()

# 1. Get admin users from DB
php = f"""<?php
require_once '{ARATIO}/root_config.php';
$db = getDB();
$users = $db->query("SELECT id, nombre, email, rol FROM usuarios ORDER BY id LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {{
    echo "$u[id] | $u[email] | $u[rol] | $u[nombre]\\n";
}}
?>"""
stdin, out, err = client.exec_command("php")
stdin.write(php); stdin.channel.shutdown_write()
print("=== USUARIOS EN BD ===")
print(out.read().decode('utf-8'))

# 2. Verify what campanas.php actually sends now (after the fix)
print("\n=== FETCH CALLS EN campanas.php ===")
out, _ = run(f"grep -n 'fetch(' {ARATIO}/pages/campanas.php")
print(out)

# 3. Verify what candidatos.php sends now
print("\n=== FETCH CALLS EN candidatos.php ===")
out, _ = run(f"grep -n 'fetch(' {ARATIO}/pages/candidatos.php")
print(out)

# 4. Let's also check pages for Alpine.js or other errors
print("\n=== Alpine.js / JS errors - script tags in campanas.php ===")
out, _ = run(f"grep -n 'alpine\\|Alpine\\|x-data\\|\\.js' {ARATIO}/pages/campanas.php | head -20")
print(out)

# 5. Check index.php to understand how pages are loaded
print("\n=== index.php structure (head 50 lines) ===")
out, _ = run(f"head -n 50 {ARATIO}/index.php")
print(out)

sftp.close()
client.close()
