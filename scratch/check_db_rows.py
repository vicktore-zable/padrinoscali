import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = "EDG$v6xSHUWhjrxE"

def check_db_rows():
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=15)

    php_script = """<?php
$user = 'u577647812_aratio';
$pass = 'v6xSHUWhjrxE';
$db = 'u577647812_aratio';
$host = 'localhost';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo str_pad("Tabla", 35) . " | " . "Registros\\n";
    echo str_repeat("-", 50) . "\\n";
    
    foreach ($tables as $table) {
        try {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $count = $countStmt->fetchColumn();
            echo str_pad($table, 35) . " | " . $count . "\\n";
        } catch (PDOException $e) {
            echo str_pad($table, 35) . " | " . "ERROR: " . $e->getMessage() . "\\n";
        }
    }
} catch(PDOException $e) {
    echo "ERROR DE CONEXION: " . $e->getMessage() . "\\n";
}
?>"""

    stdin, stdout, stderr = ssh.exec_command("php")
    stdin.write(php_script)
    stdin.channel.shutdown_write()
    
    out = stdout.read().decode('utf-8', errors='replace').strip()
    print(out)
    ssh.close()

if __name__ == "__main__":
    check_db_rows()
