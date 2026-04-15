# -*- coding: utf-8 -*-
"""reset_admin_mysql.py — Reset password admin directo via MySQL + fix Auth en login"""
import paramiko, sys, io, subprocess
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST = "157.173.208.254"; PORT = 65002
USER = "u577647812"; PASSWORD = 'E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

# Credenciales DB de DEPLOY_EDISONGIRALDO.md
DB_HOST = "localhost"         # en el servidor es localhost
DB_NAME = "u577647812_aratio"
DB_USER = "u577647812_aratio"
DB_PASS = "v6xSHUWhjrxE"

# Hash bcrypt de Admin123! (generado en paso anterior)
ADMIN_HASH = "$2y$10$feFkFH3GCr446Us4AXEOqe0kThZh6gXMf93N1/Mb6T8t04dpPkdgi"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

def cmd(c, label=""):
    stdin, stdout, stderr = client.exec_command(c)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    if label: print(f"\n--- {label} ---")
    if out: print(out)
    if err: print(f"[STDERR] {err}")
    return out

print("=== Reset Admin Password via MySQL ===\n")

# 1. Ver estructura tabla usuarios
print("[1] Estructura tabla usuarios...")
cmd(
    f"mysql -u{DB_USER} -p'{DB_PASS}' {DB_NAME} -e 'DESCRIBE usuarios;' 2>&1 | head -30",
    "DESCRIBE usuarios"
)

# 2. Ver admins actuales
print("\n[2] Admins actuales...")
cmd(
    f"mysql -u{DB_USER} -p'{DB_PASS}' {DB_NAME} -e "
    f"\"SELECT id, email, rol, estado FROM usuarios WHERE rol IN ('admin','root','administrador') LIMIT 5;\" 2>&1",
    "SELECT admins"
)

# 3. Actualizar password
print("\n[3] Actualizando password admin...")
result = cmd(
    f"mysql -u{DB_USER} -p'{DB_PASS}' {DB_NAME} -e "
    f"\"UPDATE usuarios SET password='{ADMIN_HASH}', estado='activo' WHERE rol IN ('admin','root','administrador');\" 2>&1",
    "UPDATE password"
)

cmd(
    f"mysql -u{DB_USER} -p'{DB_PASS}' {DB_NAME} -e "
    f"\"SELECT ROW_COUNT() as rows_updated;\" 2>&1",
    "Filas actualizadas"
)

# 4. Verificar resultado
print("\n[4] Verificacion final...")
cmd(
    f"mysql -u{DB_USER} -p'{DB_PASS}' {DB_NAME} -e "
    f"\"SELECT id, email, rol, estado, LEFT(password,20) as pass_preview FROM usuarios WHERE rol IN ('admin','root','administrador');\" 2>&1",
    "Admin actualizado"
)

# 5. Tambien corregir redirect en login.php (de /dashboard a /aratio/?page=dashboard)
print("\n[5] Verificando redirect en login.php...")
cmd(f"grep -n 'Location' {ARATIO}/login.php", "Redirects en login.php")

client.close()
print("\n=== DONE ===")
print("Credenciales admin:")
print("  Email:    admin@aratio.mrmtech.net")
print("  Password: Admin123!")
print("\nURL login: https://edisongiraldo.com/aratio/login.php")
print("URL login: https://navajowhite-goose-984880.hostingersite.com/aratio/login.php")
