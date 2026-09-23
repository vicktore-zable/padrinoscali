# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

php_diag = f"""<?php
require_once '{ARATIO}/root_config.php';
$db = getDB();

$candidato_id = $db->query("SELECT id FROM candidatos LIMIT 1")->fetchColumn();
$eleccion_id = $db->query("SELECT id FROM elecciones LIMIT 1")->fetchColumn();

echo "Usando Candidato ID: $candidato_id, Eleccion ID: $eleccion_id\\n";

try {{
    $db->beginTransaction();
    
    $stmt = $db->prepare("
        INSERT INTO campanas (codigo, nombre, slogan, descripcion, estado, candidato_id, eleccion_id,
            departamento, municipio, meta_votos, presupuesto, fecha_inicio, fecha_fin, color_primario, color_secundario)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        'TEST-' . time(),
        'Campana de Prueba Antigravity',
        'Slogan de prueba',
        'Descripcion de prueba',
        'planificacion',
        $candidato_id,
        $eleccion_id,
        'VALLE DEL CAUCA',
        'CALI',
        1000,
        5000000,
        '2026-04-15',
        '2026-06-15',
        '#0d9488',
        '#FFD700'
    ]);
    
    $campanaId = $db->lastInsertId();
    echo "Campana creada ID: $campanaId\\n";
    
    $usuario_id = $db->query("SELECT id FROM usuarios WHERE rol='super-admin' LIMIT 1")->fetchColumn();
    if ($usuario_id) {{
        $stmtUC = $db->prepare("INSERT INTO usuarios_campanas (usuario_id, campana_id, rol_campana) VALUES (?, ?, 'administrador')");
        $stmtUC->execute([$usuario_id, $campanaId]);
        echo "Usuario $usuario_id asignado a campana $campanaId\\n";
    }}
    
    $db->commit();
    echo "TRANSACCION EXITOSA\\n";
    
}} catch (Exception $e) {{
    $db->rollBack();
    echo "ERROR: " . $e->getMessage() . "\\n";
}}
?>"""

stdin, stdout, stderr = client.exec_command("php")
stdin.write(php_diag)
stdin.channel.shutdown_write()

print(stdout.read().decode('utf-8'))
print(stderr.read().decode('utf-8'))

client.close()
