# -*- coding: utf-8 -*-
import paramiko, sys, io
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

# Get JS files that call the API
print("="*60)
print("JS files that call the API")
print("="*60)
out, _ = run(f"find {ARATIO} -name '*.js' -not -path '*/node_modules/*' | xargs grep -l 'fetch\\|/api/' 2>/dev/null | head -15")
print(out)

print("="*60)
print("admin.js o main.js location")
print("="*60)
out, _ = run(f"find {ARATIO} -name 'admin.js' -o -name 'main.js' -o -name 'app.js' 2>/dev/null")
print(out)

print("="*60)
print("pages dir content")
print("="*60)
out, _ = run(f"ls {ARATIO}/pages/")
print(out)

print("="*60)
print("campanas page content (head 60 lines)")
print("="*60)
out, _ = run(f"head -n 60 {ARATIO}/pages/campanas.php")
print(out)

client.close()
