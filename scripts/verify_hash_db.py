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
    
    // Verificar si existe la tabla
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if($stmt->rowCount() == 0) {
        echo "ERROR: La tabla 'usuarios' no existe\\n";
        exit;
    }
    
    // Consultar columnas
    $cols = $pdo->query("SHOW COLUMNS FROM usuarios")->fetchAll(PDO::FETCH_COLUMN);
    $email_col = in_array('email', $cols) ? 'email' : (in_array('correo', $cols) ? 'correo' : 'email');
    $pass_col = in_array('password', $cols) ? 'password' : (in_array('contrasena', $cols) ? 'contrasena' : 'password_hash');
    
    echo "Columnas detectadas: EM [$email_col] PW [$pass_col]\\n\\n";
    
    // Listar admin users
    $stmt = $pdo->query("SELECT id, $email_col as email, $pass_col as password, rol, estado FROM usuarios WHERE rol IN ('admin', 'root', 'administrador') LIMIT 5");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($admins as $admin) {
        echo "[USUARIO] ID: {$admin['id']} | Email: {$admin['email']} | Rol: {$admin['rol']} | Estado: {$admin['estado']}\\n";
        echo "          Hash: {$admin['password']}\\n";
        
        // Verificar contra la contraseña que estamos probando: "Admin123!"
        if (password_verify("Admin123!", $admin['password'])) {
            echo "          -> VALIDO PARA 'Admin123!'\\n";
        } else {
            echo "          -> NO VALIDO PARA 'Admin123!'\\n";
            // Si el nombre de usuario de la DB no hace hash match
            // Generar nuevo hash
            $new_hash = password_hash("Admin123!", PASSWORD_BCRYPT);
            echo "          -> FORZANDO RESET A 'Admin123!'... ";
            
            $update = $pdo->prepare("UPDATE usuarios SET $pass_col = :hash WHERE id = :id");
            $update->execute(['hash' => $new_hash, 'id' => $admin['id']]);
            echo "Hecho.\\n";
        }
        echo "\\n";
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
