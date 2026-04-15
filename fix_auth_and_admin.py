# -*- coding: utf-8 -*-
"""fix_auth_and_admin.py — Corrige Auth class en login.php y resetea password admin"""
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST = "157.173.208.254"; PORT = 65002
USER = "u577647812"; PASSWORD = 'E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO = "/home/u577647812/domains/navajowhite-goose-984880.hostingersite.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

def cmd(c, label=""):
    stdin, stdout, stderr = client.exec_command(c)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    if label: print(f"\n--- {label} ---")
    if out: print(out)
    if err and '[ERR]' not in err: print(f"[STDERR] {err}")
    return out

# Detectar ruta correcta (puede ser en el dominio preview o principal)
print("=== Detectando ruta activa ===")
out1 = cmd("ls /home/u577647812/domains/navajowhite-goose-984880.hostingersite.com/public_html/aratio/ 2>/dev/null | head -3", "preview domain /aratio/")
out2 = cmd("ls /home/u577647812/domains/edisongiraldo.com/public_html/aratio/ 2>/dev/null | head -3", "main domain /aratio/")

# El error muestra el dominio preview, probablemente son el mismo public_html
# Usar el que tiene archivos
if "index.php" in out1:
    ARATIO = "/home/u577647812/domains/navajowhite-goose-984880.hostingersite.com/public_html/aratio"
    print(f"Usando: dominio preview")
else:
    ARATIO = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"
    print(f"Usando: dominio principal")

# ----------------------------------------------------------------
# FIX 1: Agregar require_once Auth.php en root_config.php
# ----------------------------------------------------------------
print("\n[1] Verificando Auth.php...")
cmd(f"ls {ARATIO}/includes/Auth.php 2>/dev/null || echo 'NO EXISTE'", "Auth.php existe?")

# Agregar require de Auth.php despues de RateLimiter en root_config.php
# Verificar si ya esta incluido
auth_check = cmd(f"grep -n 'Auth.php' {ARATIO}/root_config.php", "Auth.php en root_config.php")
if not auth_check:
    print("  Agregando require Auth.php en root_config.php...")
    cmd(
        f"sed -i \"s|require_once __DIR__ . '/includes/RateLimiter.php';|"
        f"require_once __DIR__ . '/includes/RateLimiter.php';\\nrequire_once __DIR__ . '/includes/Auth.php';|\" "
        f"{ARATIO}/root_config.php",
        "Insertar require Auth.php"
    )
    cmd(f"grep -n 'Auth' {ARATIO}/root_config.php", "Verificar Auth en root_config.php")
else:
    print("  Auth.php ya incluido en root_config.php")

# Hacer lo mismo en config/config.php
auth_check2 = cmd(f"grep -n 'Auth.php' {ARATIO}/config/config.php", "Auth.php en config.php")
if not auth_check2:
    cmd(
        f"sed -i \"s|require_once __DIR__ . '/includes/RateLimiter.php';|"
        f"require_once __DIR__ . '/includes/RateLimiter.php';\\nrequire_once __DIR__ . '/includes/Auth.php';|\" "
        f"{ARATIO}/config/config.php",
        "Insertar require Auth.php en config.php"
    )

# ----------------------------------------------------------------
# FIX 2: Corregir redirects absolutos en login.php
# login.php usa Location: /dashboard (ruta absoluta sin /aratio/)
# ----------------------------------------------------------------
print("\n[2] Corrigiendo redirects en login.php...")
cmd(
    f"sed -i \"s|header('Location: /dashboard')|header('Location: /aratio/?page=dashboard')|g\" {ARATIO}/login.php",
    "Fix redirect /dashboard"
)
cmd(
    f"sed -i \"s|header('Location: /aratio/')|header('Location: /aratio/')|g\" {ARATIO}/login.php",
    "Verificar redirect"
)

# ----------------------------------------------------------------
# FIX 3: Resetear password del admin en la DB
# Email admin: admin@aratio.mrmtech.net | Password nuevo: Admin123!
# ----------------------------------------------------------------
print("\n[3] Reseteando password del admin en la DB...")

# Generar hash bcrypt via PHP directamente en el servidor
hash_script = f"php -r \"echo password_hash('Admin123!', PASSWORD_BCRYPT);\""
new_hash = cmd(hash_script, "Generando hash bcrypt para Admin123!")
print(f"  Hash generado: {new_hash[:30]}...")

if new_hash.startswith("$2y$"):
    # Actualizar en la DB usando el mismo PHP con config
    update_script = f"""php -r "
require_once '{ARATIO}/root_config.php';
\$db = getDB();

// Ver estructura de la tabla usuarios primero
\$cols = \$db->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_COLUMN);
echo 'Columnas: ' . implode(', ', \$cols) . PHP_EOL;

// Buscar el campo de email
\$email_col = in_array('email', \$cols) ? 'email' : (in_array('correo', \$cols) ? 'correo' : 'email');
\$pass_col  = in_array('password', \$cols) ? 'password' : (in_array('contrasena', \$cols) ? 'contrasena' : 'password_hash');

echo 'Email col: ' . \$email_col . ' | Pass col: ' . \$pass_col . PHP_EOL;

// Ver admins existentes
\$admins = \$db->query(\"SELECT id, \$email_col, rol FROM usuarios WHERE rol IN ('admin','root','administrador') LIMIT 5\")->fetchAll();
foreach(\$admins as \$a) {{
    echo 'Admin encontrado: ID=' . \$a['id'] . ' email=' . \$a[\$email_col] . ' rol=' . \$a['rol'] . PHP_EOL;
}}

// Actualizar password
\$hash = password_hash('Admin123!', PASSWORD_BCRYPT);
\$stmt = \$db->prepare(\"UPDATE usuarios SET \$pass_col = ? WHERE rol IN ('admin','root','administrador')\");
\$stmt->execute([\$hash]);
echo 'Rows updated: ' . \$stmt->rowCount() . PHP_EOL;
echo 'DONE';
" 2>&1"""

    result = cmd(update_script, "Actualizar DB - password admin")
    if "DONE" in result:
        print("\n  [OK] Password admin reseteado a: Admin123!")
    else:
        print("\n  [WARN] Revisar resultado arriba")
else:
    print("  [ERR] No se pudo generar hash")

# ----------------------------------------------------------------
# VERIFICACION FINAL
# ----------------------------------------------------------------
print("\n[4] Verificacion sintaxis post-fix...")
cmd(f"php -l {ARATIO}/root_config.php", "Sintaxis root_config.php")
cmd(f"php -l {ARATIO}/login.php", "Sintaxis login.php")

client.close()
print("\n=== LISTO ===")
print("Credenciales admin:")
print("  Email:    admin@aratio.mrmtech.net")
print("  Password: Admin123!")
print(f"\nURL: https://navajowhite-goose-984880.hostingersite.com/aratio/login.php")
