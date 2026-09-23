# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

stdin, stdout, stderr = client.exec_command(f"cat {ARATIO}/root_config.php")
content = stdout.read().decode('utf-8')

# 1. Improved Environment Detection
old_isLocal = "$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', 'aratio.localhost', '127.0.0.1'])\n           || php_sapi_name() === 'cli';"

# Detection based on path as well
new_isLocal = """// Detección de entorno: localhost / 127.0.0.1 = desarrollo | else = producción Hostinger
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', 'aratio.localhost', '127.0.0.1']);

// Si estamos en CLI, verificar si es el entorno de Hostinger por la ruta absoluta
if (php_sapi_name() === 'cli') {
    $currentPath = realpath(__DIR__);
    if (strpos($currentPath, '/home/u577647812/') !== false) {
        $isLocal = false; // Estamos en el servidor de producción ejecutando CLI
    } else {
        $isLocal = true; // Estamos en PC local ejecutando CLI
    }
}"""

if old_isLocal in content:
    new_content = content.replace(old_isLocal, new_isLocal)
else:
    # Fallback to lines if spacing differs
    import re
    new_content = re.sub(r"\$isLocal = in_array\(.*?\)\s+\|\|\s+php_sapi_name\(\) === 'cli';", new_isLocal, content, flags=re.DOTALL)

# 2. Hard-cut auth-db690.hstgr.io
new_content = new_content.replace("'auth-db690.hstgr.io'", "'localhost'")

# 3. Add IP audit constant (optional but good for debugging)
if "define('DB_CHARSET'" in new_content:
    new_content = new_content.replace("define('DB_CHARSET'", "define('DB_EXPECTED_SERVER', '157.173.208.254');\ndefine('DB_CHARSET'", 1)

# Write back
sftp = client.open_sftp()
with sftp.open(f"{ARATIO}/root_config.php", "w") as f:
    f.write(new_content)
sftp.close()

print("root_config.php actualizado. Dependencia con host externo eliminada.")
client.close()
