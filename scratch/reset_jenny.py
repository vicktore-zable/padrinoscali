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
    $newPass = password_hash('Edison2027*', PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE email = 'jennybb2686@gmail.com'");
    $stmt->execute([$newPass]);
    echo "Contraseña actualizada exitosamente.\\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\\n";
}
"""
    
    sftp = ssh.open_sftp()
    with sftp.file(f"{remote_base}/reset_jenny.php", "w") as f:
        f.write(script)
    sftp.close()
    
    stdin, stdout, stderr = ssh.exec_command(f"php {remote_base}/reset_jenny.php")
    print("STDOUT:\n", stdout.read().decode())
    
finally:
    ssh.close()
