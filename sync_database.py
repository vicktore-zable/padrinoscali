# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

# SQL statements to create missing tables
sql_statements = [
    # 1. usuarios_campanas
    """
    CREATE TABLE IF NOT EXISTS `usuarios_campanas` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `usuario_id` INT(11) NOT NULL,
      `campana_id` INT(11) NOT NULL,
      `rol_campana` ENUM('administrador','coordinador','colaborador','veedor') NOT NULL DEFAULT 'colaborador',
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_usuario_campana` (`usuario_id`, `campana_id`),
      KEY `idx_campana` (`campana_id`),
      CONSTRAINT `fk_uc_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
      CONSTRAINT `fk_uc_campana` FOREIGN KEY (`campana_id`) REFERENCES `campanas` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    """,
    # 2. historial_cambios_lider
    """
    CREATE TABLE IF NOT EXISTS historial_cambios_lider (
        id INT AUTO_INCREMENT PRIMARY KEY,
        colaborador_id INT NOT NULL,
        colaborador_documento VARCHAR(20) NOT NULL,
        lider_anterior VARCHAR(20) COMMENT 'Documento del lider anterior',
        lider_nuevo VARCHAR(20) COMMENT 'Documento del nuevo lider',
        motivo TEXT COMMENT 'Razon del cambio',
        usuario_cambio INT NOT NULL COMMENT 'Usuario que realizo el cambio',
        campana_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_cambio) REFERENCES usuarios(id),
        FOREIGN KEY (campana_id) REFERENCES campanas(id),
        INDEX idx_colaborador (colaborador_id),
        INDEX idx_campana (campana_id),
        INDEX idx_fecha (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    """,
    # 3. historial_estados
    """
    CREATE TABLE IF NOT EXISTS historial_estados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        colaborador_id INT NOT NULL,
        campana_id INT NOT NULL,
        dato_potencial_anterior INT,
        dato_potencial_nuevo INT,
        dato_historico_anterior INT,
        dato_historico_nuevo INT,
        estado_anterior VARCHAR(20),
        estado_nuevo VARCHAR(20),
        motivo TEXT COMMENT 'Razon del cambio o reevaluacion',
        tipo_cambio ENUM('creacion', 'actualizacion', 'reevaluacion') DEFAULT 'actualizacion',
        usuario_id INT NOT NULL COMMENT 'Usuario que realizo el cambio',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
        FOREIGN KEY (campana_id) REFERENCES campanas(id),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
        INDEX idx_colaborador (colaborador_id),
        INDEX idx_campana (campana_id),
        INDEX idx_fecha (created_at),
        INDEX idx_tipo (tipo_cambio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    """
]

# PHP script to run SQL
php_wrapper = """
<?php
$user = 'u577647812_aratio';
$pass = 'v6xSHUWhjrxE';
$db = 'u577647812_aratio';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $queries = [
        %s
    ];

    foreach($queries as $i => $sql) {
        echo "[QUERY " . ($i+1) . "] Ejecutando... ";
        $pdo->exec($sql);
        echo "OK\\n";
    }
    echo "¡Sincronización de base de datos completada!\\n";

} catch(PDOException $e) {
    echo "ERROR DB: " . $e->getMessage() . "\\n";
}
?>
"""

# Format all queries safely
query_list = []
for q in sql_statements:
    # Escape quotes for PHP string
    escaped = q.replace('"', '\\"').strip()
    query_list.append(f'"{escaped}"')

full_php = php_wrapper % (",\n        ".join(query_list))

stdin, stdout, stderr = client.exec_command("php")
stdin.write(full_php)
stdin.channel.shutdown_write()

print(stdout.read().decode('utf-8'))
print(stderr.read().decode('utf-8'))

client.close()
