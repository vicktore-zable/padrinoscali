import paramiko

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'E=j$`01yHi^?XfpoM@|CD"5H4'

remote_base = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, port, user, password)
    
    script = """
<?php
require_once __DIR__ . '/config/config.php';
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, nombre, email, password FROM usuarios WHERE nombre LIKE '%jenny%' OR email LIKE '%jenny%'");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    foreach ($usuarios as $u) {
        echo "ID: {$u['id']} - Nombre: {$u['nombre']} - Email: {$u['email']}\\n";
        echo "Hash: {$u['password']}\\n";
        
        // Verificar si es una de las comunes
        $comunes = ['123456', '12345678', 'password', 'Jenny123*', 'jenny123'];
        foreach ($comunes as $c) {
            if (password_verify($c, $u['password'])) {
                echo "¡La contraseña es: $c!\\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\\n";
}
"""
    
    sftp = ssh.open_sftp()
    with sftp.file(f"{remote_base}/test_jenny.php", "w") as f:
        f.write(script)
    sftp.close()
    
    stdin, stdout, stderr = ssh.exec_command(f"php {remote_base}/test_jenny.php")
    print("STDOUT:\n", stdout.read().decode())
    print("STDERR:\n", stderr.read().decode())
    
finally:
    ssh.close()
