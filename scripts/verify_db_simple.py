# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

php_test = """<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user = 'u577647812_aratio';
$pass = 'v6xSHUWhjrxE';
$db = 'u577647812_aratio';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=$db;charset=utf8mb4", $user, $pass);
    echo "Conexión OK.\\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios_campanas");
    $count = $stmt->fetchColumn();
    echo "Tabla 'usuarios_campanas' existe. Conteos: $count\\n";
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\\n";
}
?>"""

stdin, stdout, stderr = client.exec_command("php")
stdin.write(php_test)
stdin.channel.shutdown_write()

print("STDOUT:", stdout.read().decode('utf-8'))
print("STDERR:", stderr.read().decode('utf-8'))

client.close()
