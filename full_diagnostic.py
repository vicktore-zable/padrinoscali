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

print("="*60)
print("1. config/config.php actual")
print("="*60)
out, _ = run(f"cat {ARATIO}/config/config.php")
print(out)

print("="*60)
print("2. JS files in /assets/ or /js/")
print("="*60)
out, _ = run(f"ls {ARATIO}/assets/ 2>/dev/null || ls {ARATIO}/js/ 2>/dev/null || find {ARATIO} -maxdepth 2 -name '*.js' | head -20")
print(out)

print("="*60)
print("3. Grep for fetch calls in admin JS")
print("="*60)
out, _ = run(f"find {ARATIO} -name '*.js' -not -path '*/node_modules/*' | xargs grep -l 'fetch\\|axios' 2>/dev/null | head -10")
print(out)

print("="*60)
print("4. Current isAjaxRequest in root_config.php")
print("="*60)
out, _ = run(f"grep -n 'isAjaxRequest\\|HTTP_X_REQUESTED\\|HTTP_ACCEPT\\|REQUEST_URI' {ARATIO}/root_config.php")
print(out)

print("="*60)
print("5. PHP Errors from last 30min")
print("="*60)
out, _ = run("tail -n 80 /home/u577647812/domains/edisongiraldo.com/public_html/php-errors.log")
print(out)

client.close()
