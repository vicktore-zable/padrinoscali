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
    $stmt = $db->query("SELECT id, codigo, nombre, municipio, departamento FROM campanas LIMIT 1");
    print_r($stmt->fetchAll());
    echo "Campanas OK!\\n";
} catch (Exception $e) {
    echo "Error campanas: " . $e->getMessage() . "\\n";
}

try {
    $db = getDB();
    $stmt = $db->query("SELECT id, nombre, email, telefono, rol, estado, ultimo_acceso, created_at FROM usuarios LIMIT 1");
    print_r($stmt->fetchAll());
    echo "Usuarios OK!\\n";
} catch (Exception $e) {
    echo "Error usuarios: " . $e->getMessage() . "\\n";
}
"""
    
    sftp = ssh.open_sftp()
    with sftp.file(f"{remote_base}/test_queries.php", "w") as f:
        f.write(script)
    sftp.close()
    
    stdin, stdout, stderr = ssh.exec_command(f"php {remote_base}/test_queries.php")
    print("STDOUT:", stdout.read().decode())
    print("STDERR:", stderr.read().decode())
    
finally:
    ssh.close()
