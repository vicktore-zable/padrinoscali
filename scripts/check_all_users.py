# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

php_test = """
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user = 'u577647812_aratio';
$pass = 'v6xSHUWhjrxE';
$db = 'u577647812_aratio';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Obtener nombres de columnas
    $cols = $pdo->query("SHOW COLUMNS FROM usuarios")->fetchAll(PDO::FETCH_COLUMN);
    $email_col = in_array('email', $cols) ? 'email' : (in_array('correo', $cols) ? 'correo' : 'email');
    $pass_col = in_array('password', $cols) ? 'password' : (in_array('contrasena', $cols) ? 'contrasena' : 'password_hash');
    $rol_col = in_array('rol', $cols) ? 'rol' : (in_array('role', $cols) ? 'role' : 'rol');
    
    // Listar todos los usuarios
    $stmt = $pdo->query("SELECT id, $email_col as email, $rol_col as rol, $pass_col as password FROM usuarios LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total usuarios en la tabla: " . count($users) . "\\n";
    foreach($users as $u) {
        echo "- ID: {$u['id']} | Email: {$u['email']} | Rol: {$u['rol']}\\n";
        if (password_verify("Admin123!", $u['password'])) {
            echo "  [OK] Hash = Admin123!\\n";
        } else {
            echo "  [WARN] Falló validacion de hash\\n";
        }
    }
    
    if (count($users) == 0) {
        echo "LA TABLA DE USUARIOS ESTA VACIA.\\n";
    }
    
} catch(PDOException $e) {
    echo "ERROR DB: " . $e->getMessage() . "\\n";
}
?>
"""

stdin, stdout, stderr = client.exec_command("php")
stdin.write(php_test)
stdin.channel.shutdown_write()

print(stdout.read().decode('utf-8', errors='replace').strip())
print(stderr.read().decode('utf-8', errors='replace').strip())

client.close()
