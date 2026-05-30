# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='EDG$v6xSHUWhjrxE'

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

$hosts = ['localhost', '127.0.0.1'];

foreach($hosts as $host) {
    echo "Probando con host: $host\\n";
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3
        ]);
        echo " -> EXITO!\\n";
    } catch(PDOException $e) {
        echo " -> ERROR: " . $e->getMessage() . "\\n";
    }
}
?>
"""

stdin, stdout, stderr = client.exec_command("php")
stdin.write(php_test)
stdin.channel.shutdown_write()

out = stdout.read().decode('utf-8', errors='replace').strip()
err = stderr.read().decode('utf-8', errors='replace').strip()
print("STDOUT:", out)
print("STDERR:", err)

client.close()
