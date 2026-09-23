# -*- coding: utf-8 -*-
"""debug_login.py — Debug the exact login process remotely"""
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

def cmd(c, label=""):
    stdin, stdout, stderr = client.exec_command(c)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    if label: print(f"\n--- {label} ---")
    if out: print(out)
    return out

# Execute a small PHP script to test Auth manually
php_script = f"""php -r "
require_once '{ARATIO}/root_config.php';
\$db = getDB();

\$email = 'aratio@edisongiraldo.com';
\$password = 'Admin123!';

\$stmt = \$db->prepare('SELECT * FROM usuarios WHERE email = ? AND estado = \\\'activo\\\'');
\$stmt->execute([\$email]);
\$user = \$stmt->fetch();

if (!\$user) {{
    echo 'Usuario no encontrado o no activo.';
}} else {{
    echo 'Usuario encontrado! ID=' . \$user['id'] . ' Estado=' . \$user['estado'] . \"\\n\";
    if (password_verify(\$password, \$user['password'])) {{
        echo 'Password coincide!';
    }} else {{
        echo 'Password NO coincide.\\n';
        echo 'Hash en DB: ' . \$user['password'] . \"\\n\";
        echo 'Nuevo hash si regeneramos: ' . password_hash(\$password, PASSWORD_BCRYPT) . \"\\n\";
    }}
}}
" 2>&1
"""

cmd(php_script, "Prueba Directa de Login en PHP")

client.close()
