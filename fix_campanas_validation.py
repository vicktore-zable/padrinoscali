# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

stdin, stdout, stderr = client.exec_command(f"cat {ARATIO}/api/campanas.php")
content = stdout.read().decode('utf-8')

# Improved validation loop
old_validation = """            // Validar campos requeridos
            $required = ['codigo', 'nombre', 'estado', 'departamento', 'municipio', 'fecha_inicio', 'fecha_fin', 'candidato_id', 'eleccion_id'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    jsonResponse(['success' => false, 'message' => "El campo $field es requerido"], 400);
                }
            }"""

new_validation = """            // Validar campos requeridos
            $required = ['codigo', 'nombre', 'estado', 'departamento', 'municipio', 'fecha_inicio', 'fecha_fin', 'candidato_id', 'eleccion_id'];
            foreach ($required as $field) {
                // Usamos isset y comparamos con cadena vacía para evitar que "0" falle (pitfall común de empty)
                if (!isset($data[$field]) || $data[$field] === '') {
                    jsonResponse(['success' => false, 'message' => "El campo $field es requerido"], 400);
                }
            }"""

if old_validation in content:
    new_content = content.replace(old_validation, new_validation)
else:
    # Fallback si hay espacios diferentes
    new_content = content.replace("if (empty($data[$field])) {", "if (!isset($data[$field]) || $data[$field] === '') {")

# Write back
sftp = client.open_sftp()
with sftp.open(f"{ARATIO}/api/campanas.php", "w") as f:
    f.write(new_content)
sftp.close()

print("api/campanas.php actualizado con validación robusta.")
client.close()
