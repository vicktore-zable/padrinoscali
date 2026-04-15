# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

apis_to_test = [
    f"{ARATIO}/api/usuarios.php",
    f"{ARATIO}/api/grupos.php",
    f"{ARATIO}/api/jac.php",
    f"{ARATIO}/api/candidatos.php"
]

for api in apis_to_test:
    print(f"--- Testing {api} ---")
    # We use -r to ignore the Auth logic which might exit, but we want to see if the require works.
    cmd = f"php -r \"require_once '{api}'; echo 'OK: Loaded successfully' . PHP_EOL;\""
    stdin, stdout, stderr = client.exec_command(cmd)
    
    out = stdout.read().decode('utf-8')
    err = stderr.read().decode('utf-8')
    
    if "Fatal error" in err or "Error" in err:
        print(f"FAILED: {api}")
        print("STDERR:", err)
    elif "No autorizado" in out or "Debe iniciar sesión" in out or "OK: Loaded successfully" in out:
        print(f"PASSED: {api} (Loaded without Fatal Error)")
    else:
        print(f"RESULT: {out} {err}")

client.close()
