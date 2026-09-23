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

$user = 'u577647812_aratio';
$pass = 'v6xSHUWhjrxE';
$db = 'u577647812_aratio';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Obtener nombres de columnas
    $cols = $pdo->query("SHOW COLUMNS FROM usuarios")->fetchAll(PDO::FETCH_COLUMN);
    $pass_col = in_array('password', $cols) ? 'password' : (in_array('contrasena', $cols) ? 'contrasena' : 'password_hash');
    
    // Generar hash
    $new_hash = password_hash("Admin123!", PASSWORD_BCRYPT);
    
    $update = $pdo->prepare("UPDATE usuarios SET $pass_col = :hash WHERE id = 1");
    $update->execute(['hash' => $new_hash]);
    
    echo "¡Contraseña de aratio@edisongiraldo.com (super-admin) reseteada a 'Admin123!' con éxito!\\n";
    
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
