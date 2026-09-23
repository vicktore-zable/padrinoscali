# -*- coding: utf-8 -*-
"""force_reset.py — Fuerza el reset del usuario ID=1"""
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
DB_NAME="u577647812_aratio"; DB_USER="u577647812_aratio"; DB_PASS="v6xSHUWhjrxE"

HASH="$2y$10$feFkFH3GCr446Us4AXEOqe0kThZh6gXMf93N1/Mb6T8t04dpPkdgi"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

def mysql(q, label=""):
    stdin, stdout, stderr = client.exec_command(f"mysql -u{DB_USER} -p'{DB_PASS}' {DB_NAME} -e \"{q}\" 2>&1")
    out = stdout.read().decode('utf-8', errors='replace').strip()
    if label: print(f"\n--- {label} ---")
    print(out if out else "(OK sin output)")
    return out

# Ver password actual (primeros 10 chars)
mysql("SELECT id, email, LEFT(password,15) as pass_preview FROM usuarios WHERE id=1;", "Password actual")

# Forzar update por ID con hash nuevo
mysql(f"UPDATE usuarios SET password='{HASH}', estado='activo', intentos_fallidos=0, bloqueado_hasta=NULL WHERE id=1;", "Force UPDATE id=1")
mysql("SELECT ROW_COUNT() as updated;", "Filas actualizadas")

# Verificar
mysql("SELECT id, email, rol, estado, LEFT(password,15) as pass_preview FROM usuarios WHERE id=1;", "Resultado final")

client.close()
print("\n=== LISTO ===")
print("Usuario: aratio@edisongiraldo.com")
print("Password: Admin123!")
print("\nURL: https://edisongiraldo.com/aratio/login.php")
print("URL: https://navajowhite-goose-984880.hostingersite.com/aratio/login.php")
