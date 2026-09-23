# -*- coding: utf-8 -*-
"""reset_admin_via_file.py — Sube un PHP que resetea el admin y lo ejecuta via curl"""
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST = "157.173.208.254"; PORT = 65002
USER = "u577647812"; PASSWORD = 'E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

# Script PHP a subir temporalmente
RESET_PHP = """<?php
// Script temporal para resetear admin - ELIMINAR DESPUES
require_once __DIR__ . '/root_config.php';
$db = getDB();

// Ver estructura
$cols = $db->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_COLUMN);
echo "<pre>Columnas: " . implode(', ', $cols) . "\\n";

// Detectar campos
$email_col = in_array('email', $cols) ? 'email' : 'correo';
$pass_col  = in_array('password', $cols) ? 'password' : (in_array('password_hash', $cols) ? 'password_hash' : 'contrasena');

echo "email_col=$email_col | pass_col=$pass_col\\n";

// Ver admins
$admins = $db->query("SELECT id, $email_col, rol, estado FROM usuarios WHERE rol IN ('admin','root','administrador') LIMIT 5")->fetchAll();
if (empty($admins)) {
    // Si no hay por rol, buscar por email
    $admins = $db->query("SELECT id, $email_col, rol, estado FROM usuarios LIMIT 5")->fetchAll();
    echo "Sin rol admin - mostrando primeros 5:\\n";
}
foreach ($admins as $a) {
    echo "ID={$a['id']} | {$email_col}={$a[$email_col]} | rol={$a['rol']} | estado={$a['estado']}\\n";
}

// Resetear password de TODOS los admins
$hash = password_hash('Admin123!', PASSWORD_BCRYPT);
$stmt = $db->prepare("UPDATE usuarios SET $pass_col = ?, estado = 'activo' WHERE rol IN ('admin','root','administrador')");
$stmt->execute([$hash]);
$rows = $stmt->rowCount();
echo "Rows updated: $rows\\n";

if ($rows == 0) {
    // Intentar por el primer usuario
    $first = $db->query("SELECT id, $email_col FROM usuarios LIMIT 1")->fetch();
    if ($first) {
        $stmt2 = $db->prepare("UPDATE usuarios SET $pass_col = ?, estado = 'activo' WHERE id = ?");
        $stmt2->execute([$hash, $first['id']]);
        echo "Updated first user ID={$first['id']} email={$first[$email_col]}\\n";
    }
}

echo "\\nNuevo password: Admin123!";
echo "\\n</pre>";
echo "<script>setTimeout(()=>fetch('/aratio/reset_admin.php?delete=1'),3000)</script>";

// Auto-eliminar
if (isset($_GET['delete'])) { unlink(__FILE__); echo "Eliminado."; exit; }
?>
"""

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)
sftp = client.open_sftp()

def cmd(c, label=""):
    stdin, stdout, stderr = client.exec_command(c)
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    if label: print(f"\n--- {label} ---")
    if out: print(out)
    if err: print(f"[STDERR] {err}")
    return out

# Subir script PHP
print("[1] Subiendo reset_admin.php al servidor...")
remote_file = f"{ARATIO}/reset_admin.php"
with sftp.open(remote_file, 'w') as f:
    f.write(RESET_PHP)
print("  [OK] Subido")

# Ejecutar via PHP CLI directamente (mas confiable que HTTP)
print("\n[2] Ejecutando reset via PHP CLI...")
result = cmd(
    f"cd {ARATIO} && php -r \""
    f"\$_SERVER['HTTP_HOST']='edisongiraldo.com';"
    f"\$_SERVER['REQUEST_URI']='/aratio/';"
    f"include 'reset_admin.php';"
    f"\" 2>&1",
    "PHP CLI reset_admin.php"
)

# Eliminar el archivo temporal
print("\n[3] Eliminando script temporal...")
cmd(f"rm -f {remote_file}", "rm reset_admin.php")

# Verificar en DB directamente
print("\n[4] Verificando en DB via PHP...")
verify = cmd(
    f"cd {ARATIO} && php -r \""
    f"\$_SERVER['HTTP_HOST']='edisongiraldo.com';"
    f"\$_SERVER['REQUEST_URI']='/aratio/';"
    f"require_once 'root_config.php';"
    f"\$db = getDB();"
    f"\$u = \$db->query(\\\"SELECT id,email,rol,estado FROM usuarios WHERE rol IN ('admin','root','administrador') LIMIT 3\\\");"
    f"foreach(\$u->fetchAll() as \$r) echo \$r['id'].'|'.\$r['email'].'|'.\$r['rol'].'|'.\$r['estado'].PHP_EOL;"
    f"\" 2>&1",
    "Usuarios admin en DB"
)

sftp.close()
client.close()
print("\n=== COMPLETADO ===")
print("Credenciales: admin@aratio.mrmtech.net / Admin123!")
print("URL login: https://edisongiraldo.com/aratio/login.php")
