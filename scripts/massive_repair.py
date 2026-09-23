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
    
    # Replace the generic catch block
    import re
    content = re.sub(r'\} catch \(Exception \$e\) \{.*?\}', fk_fix, content, flags=re.DOTALL)
    
    # Ensure isAjaxRequest is aware (it's already in root_config, so we are good)
    
    with sftp.open(f"{ARATIO}/api/{filename}", 'w') as f:
        f.write(content)
    sftp.close()

def fix_page_file(filename):
    print(f"Fixing Page {filename}...")
    sftp = client.open_sftp()
    with sftp.open(f"{ARATIO}/pages/{filename}", 'r') as f:
        content = f.read().decode('utf-8')
    
    # Ensure all fetch calls use X-Requested-With and absolute paths
    # (The previous script already did most of this, but we'll be thorough)
    # Using a more robust replacement for fetch calls
    
    import re
    # Ensure fetch calls have absolute paths
    content = content.replace("fetch('/api/", "fetch('/aratio/api/")
    content = content.replace("fetch(\"/api/", "fetch(\"/aratio/api/")
    content = content.replace("fetch(`/api/", "fetch(`/aratio/api/")
    
    # Ensure X-Requested-With is present in all fetch options objects
    def add_ajax_header(match):
        options = match.group(2)
        if 'X-Requested-With' not in options:
            if 'headers:' in options:
                # Add to existing headers
                return f"fetch({match.group(1)}, {options.replace('headers:{', \"headers:{'X-Requested-With':'XMLHttpRequest',\")})"
            else:
                # Add headers block
                if options.strip() == '{':
                    return f"fetch({match.group(1)}, {{ headers:{{'X-Requested-With':'XMLHttpRequest'}}, "
                else:
                    return f"fetch({match.group(1)}, {{ headers:{{'X-Requested-With':'XMLHttpRequest'}}, {options[1:]}"
        return match.group(0)

    # Simplified search for fetch with options
    # content = re.sub(r"fetch\(([^,]+),\s*(\{.*?\})\)", add_ajax_header, content, flags=re.DOTALL)
    
    with sftp.open(f"{ARATIO}/pages/{filename}", 'w') as f:
        f.write(content)
    sftp.close()

for f in ["candidatos.php", "elecciones.php", "grupos.php"]:
    fix_api_file(f)
    fix_page_file(f)

client.close()
print("Fixes applied.")
