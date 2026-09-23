# -*- coding: utf-8 -*-
"""
Auditoria y reparacion de todos los archivos de paginas afectados por el script de rutas.
Busca el patron roto en candidatos, elecciones, grupos, usuarios, jac.
"""
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

# Check each page for signs of the Alpine.js data object being broken
pages = ['candidatos', 'elecciones', 'grupos', 'usuarios', 'jac']

for page in pages:
    path = f"{ARATIO}/pages/{page}.php"
    try:
        with sftp.open(path, 'r') as f:
            content = f.read().decode('utf-8')

        # Look for the function definition and the return block
        # A healthy Alpine data function has: function xxxData() { return { form: {
        # A broken one will have the form properties dangling outside
        
        # Check 1: Does it have a function definition?
        func_match = re.search(r'function \w+Data\(\)', content)
        if not func_match:
            print(f"[SKIP] {page}.php - no Alpine data function found")
            continue
        func_name = func_match.group(0)
        
        # Check 2: Does it have 'form:' property inside return?
        has_form = 'form: {' in content or "form:{" in content
        
        # Check 3: Look for the broken pattern - form properties outside return block
        # e.g.: "loading: false,\n    color_primario: '#FF00FF'" without a form: { wrapper
        
        # Extract the JS section
        script_start = content.find('<script>')
        script_end = content.find('</script>', script_start)
        if script_start == -1:
            print(f"[SKIP] {page}.php - no <script> tag found")
            continue
        
        js_content = content[script_start:script_end]
        
        # Check for broken form state (properties floating outside form:{})
        broken_indicators = [
            "color_primario: '#FF00FF', color_secundario: '#FFD700'\n            },\n            municipio_raw",
            "color_primario: '#FF00FF', color_secundario: '#FFD700'\n            },\n            form:",
        ]
        
        is_broken = any(bi in js_content for bi in broken_indicators)
        
        print(f"\n=== {page}.php ===")
        print(f"  Has Alpine func: {bool(func_match)}")
        print(f"  Has form object: {has_form}")
        print(f"  Broken pattern:  {is_broken}")
        
        # Show the area around 'loading: false' in the return block
        idx = js_content.find('loading: false,')
        if idx != -1:
            print(f"  Context around 'loading':")
            print(repr(js_content[idx:idx+200]))
    except Exception as e:
        print(f"[ERROR] {page}.php: {e}")

sftp.close()
client.close()
