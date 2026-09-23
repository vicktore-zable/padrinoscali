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

old_is_ajax = """function isAjaxRequest()
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}"""

new_is_ajax = """function isAjaxRequest()
{
    // Detectar si es AJAX por headers o si es una peticion a la API
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
           (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false);
}"""

if old_is_ajax in content:
    new_content = content.replace(old_is_ajax, new_is_ajax)
    sftp = client.open_sftp()
    with sftp.open(f"{ARATIO}/root_config.php", "w") as f:
        f.write(new_content)
    sftp.close()
    print("isAjaxRequest actualizado correctamente.")
else:
    # Intento con variaciones de saltos de linea
    if "function isAjaxRequest()" in content:
        print("Encontrado pero no coincide el bloque exacto. Intentando reemplazo parcial.")
        import re
        new_content = re.sub(r'function isAjaxRequest\(\)\s*\{.*?\}(?=\s*(?:\/\*\*|function))', new_is_ajax, content, flags=re.DOTALL)
        if new_content != content:
            sftp = client.open_sftp()
            with sftp.open(f"{ARATIO}/root_config.php", "w") as f:
                f.write(new_content)
            sftp.close()
            print("Reemplazo por Regex exitoso.")
        else:
            print("Fallo el reemplazo por Regex.")
    else:
        print("No se encontro la funcion isAjaxRequest.")

client.close()
