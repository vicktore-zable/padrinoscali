# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

def fix_api_file(filename):
    print(f"Fixing API {filename}...")
    sftp = client.open_sftp()
    with sftp.open(f"{ARATIO}/api/{filename}", 'r') as f:
        content = f.read().decode('utf-8')
    
    # Improve DELETE catch block to show FK errors
    # Note: Using % for formatting or concatenation to avoid f-string backslash issues
    fk_fix = """} catch (Exception $e) {
    error_log("Error en API: " . $e->getMessage());
    $msg = 'Error interno del servidor';
    if (strpos($e->getMessage(), '1451') !== false) {
        $msg = 'No se puede eliminar este registro porque tiene otros datos asociados (integridad referencial).';
    } else {
        $msg = 'Error: ' . $e->getMessage();
    }
    jsonResponse(['success' => false, 'message' => $msg], 500);
}"""
    
    import re
    content = re.sub(r'\} catch \(Exception \$e\) \{.*?\}', fk_fix, content, flags=re.DOTALL)
    
    with sftp.open(f"{ARATIO}/api/{filename}", 'w') as f:
        f.write(content)
    sftp.close()

def fix_page_file(filename):
    print(f"Fixing Page {filename}...")
    sftp = client.open_sftp()
    with sftp.open(f"{ARATIO}/pages/{filename}", 'r') as f:
        content = f.read().decode('utf-8')
    
    # Simple replacement for absolute paths
    content = content.replace("fetch('/api/", "fetch('/aratio/api/")
    content = content.replace("fetch(\"/api/", "fetch(\"/aratio/api/")
    content = content.replace("fetch(`/api/", "fetch(`/aratio/api/")
    
    # Logic to ensure X-Requested-With is in headers if not present
    # This is slightly complex via regex, so we'll do a simple check
    if 'X-Requested-With' not in content:
        # If it doesn't have it anywhere, we might need to add it to the generic ones
        content = content.replace("headers: {", "headers: { 'X-Requested-With': 'XMLHttpRequest', ")
        content = content.replace("headers:{", "headers:{'X-Requested-With':'XMLHttpRequest',")

    with sftp.open(f"{ARATIO}/pages/{filename}", 'w') as f:
        f.write(content)
    sftp.close()

p_targets = ["candidatos.php", "elecciones.php", "grupos.php", "jac.php", "usuarios.php"]
for f in p_targets:
    fix_api_file(f)
    fix_page_file(f)

client.close()
print("Fixes applied.")
