import paramiko
import sys

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = "EDG$v6xSHUWhjrxE"

def test_ssh_and_db():
    print("=== TESTING SSH CONNECTION ===")
    print(f"Connecting to {USER}@{HOST}:{PORT}...")
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=15)
        print("SSH connection SUCCESSFUL!")
    except Exception as e:
        print(f"SSH connection FAILED: {e}")
        return False

    print("\n=== RUNNING REMOTE DIAGNOSTIC COMMANDS ===")
    commands = [
        ("Whoami", "whoami"),
        ("Active Directory", "pwd"),
        ("Remote Disk Usage / Aratio Dir", "ls -ld /home/u577647812/domains/edisongiraldo.com/public_html/aratio"),
    ]
    
    for label, cmd in commands:
        stdin, stdout, stderr = ssh.exec_command(cmd)
        out = stdout.read().decode('utf-8', errors='replace').strip()
        err = stderr.read().decode('utf-8', errors='replace').strip()
        print(f"{label} ({cmd}):")
        if out:
            print(f"  STDOUT: {out}")
        if err:
            print(f"  STDERR: {err}")

    print("\n=== TESTING DB CONNECTION FROM REMOTE SERVER ===")
    php_test = """<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user = 'u577647812_aratio';
$pass = 'v6xSHUWhjrxE';
$db = 'u577647812_aratio';
$host = 'localhost';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    echo "SUCCESS: PDO Connected to database successfully!\\n";
    
    // Check tables and table count
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "SUCCESS: Found " . count($tables) . " tables in database.\\n";
    echo "Tables: " . implode(', ', $tables) . "\\n";
    
    // Fetch a user count
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    $count = $stmt->fetchColumn();
    echo "SUCCESS: Found $count users in 'usuarios' table.\\n";
    
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\\n";
}
?>"""

    stdin, stdout, stderr = ssh.exec_command("php")
    stdin.write(php_test)
    stdin.channel.shutdown_write()
    
    out = stdout.read().decode('utf-8', errors='replace').strip()
    err = stderr.read().decode('utf-8', errors='replace').strip()
    
    print("PHP Script Output:")
    print(out)
    if err:
        print("PHP Script Errors:")
        print(err)

    ssh.close()
    return True

if __name__ == "__main__":
    test_ssh_and_db()
