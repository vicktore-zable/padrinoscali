# -*- coding: utf-8 -*-
"""fix_admin_role.py — Ver usuarios y actualizar el admin correcto"""
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
DB_NAME="u577647812_aratio"; DB_USER="u577647812_aratio"; DB_PASS="v6xSHUWhjrxE"
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"
HASH="$2y$10$feFkFH3GCr446Us4AXEOqe0kThZh6gXMf93N1/Mb6T8t04dpPkdgi"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

def cmd(c, label=""):
    stdin, stdout, stderr = client.exec_command(c)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    if label: print(f"\n--- {label} ---")
    if out: print(out)
    return out

def mysql(query, label=""):
    return cmd(f"mysql -u{DB_USER} -p'{DB_PASS}' {DB_NAME} -e \"{query}\" 2>&1", label)

# Ver todos los usuarios
mysql("SELECT id, email, rol, estado FROM usuarios ORDER BY id LIMIT 10;", "Todos los usuarios")

# Resetear password del super-admin
mysql(f"UPDATE usuarios SET password='{HASH}', estado='activo' WHERE rol='super-admin';", "Update super-admin")
mysql("SELECT ROW_COUNT() as updated;", "Rows updated super-admin")

# Si no hay super-admin, actualizar el primer usuario
out = mysql("SELECT COUNT(*) as cnt FROM usuarios WHERE rol='super-admin';", "Count super-admin")
if "0" in out and "cnt" in out:
    print("\nNo hay super-admin — promoviendo primer usuario...")
    mysql(f"UPDATE usuarios SET rol='super-admin', password='{HASH}', estado='activo' ORDER BY id LIMIT 1;", "Promover primer usuario a super-admin")

# Verificacion final
mysql("SELECT id, email, rol, estado FROM usuarios WHERE rol='super-admin' LIMIT 3;", "Super-admins finales")

# Ver que hace Auth.php con los roles
cmd(f"grep -n 'super-admin\\|rol\\|login' {ARATIO}/includes/Auth.php | head -20", "Auth.php roles")

client.close()
print("\n=== DONE ===")
print("Password reseteado a: Admin123!")
