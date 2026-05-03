<?php
// run_fix_views.php
// Script para crear las vistas necesarias en la base de datos remota
// Basado en mod_colab/fix_database_views.php pero ajustado para ejecutar desde la raíz

// 1. Cargar configuración
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/DatabaseManager.php';

// 2. Obtener instancia de base de datos
try {
    $db = DatabaseManager::getInstance()->getConnection();
    echo "Conexión exitosa a la base de datos hostinger.\n";
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage() . "\n");
}

// 3. Definir las sentencias SQL (Vistas y Tablas faltantes)
$sqlStatements = [
    // Vista: Colaboradores con información completa
    "CREATE OR REPLACE VIEW v_colaboradores_completo AS
    SELECT
        c.*,
        CONCAT(c.nombres, ' ', c.apellidos) AS nombre_completo,
        TIMESTAMPDIFF(YEAR, c.fecha_nacimiento, CURDATE()) AS edad,
        CONCAT(l.nombres, ' ', l.apellidos) AS nombre_lider,
        l.documento AS documento_lider_completo,
        COUNT(DISTINCT s.id) AS total_seguidores
    FROM colaboradores c
    LEFT JOIN colaboradores l ON c.lider_directo = l.documento
    LEFT JOIN colaboradores s ON s.lider_directo = c.documento
    GROUP BY c.id",

    // Vista: Estadísticas por perfil (CRÍTICA PARA EL DASHBOARD)
    "CREATE OR REPLACE VIEW v_estadisticas_perfil AS
    SELECT
        perfil,
        COUNT(*) AS total,
        COUNT(CASE WHEN estado = 'Creció' THEN 1 END) AS crecio,
        COUNT(CASE WHEN estado = 'Decrece' THEN 1 END) AS decrece,
        COUNT(CASE WHEN estado = 'Nuevo' THEN 1 END) AS nuevos,
        AVG(dato_potencial) AS promedio_potencial
    FROM colaboradores
    GROUP BY perfil",

    // Vista: Estadísticas por territorio
    "CREATE OR REPLACE VIEW v_estadisticas_territorio AS
    SELECT
        territorio,
        tipo_territorio,
        COUNT(*) AS total_colaboradores,
        COUNT(DISTINCT lider_directo) AS total_lideres,
        AVG(dato_potencial) AS promedio_potencial
    FROM colaboradores
    WHERE territorio IS NOT NULL
    GROUP BY territorio, tipo_territorio",

    // Vista: Líderes con sus métricas
    "CREATE OR REPLACE VIEW v_lideres_metricas AS
    SELECT
        c.id,
        c.documento,
        CONCAT(c.nombres, ' ', c.apellidos) AS nombre_completo,
        c.perfil,
        c.territorio,
        COUNT(DISTINCT s.id) AS total_seguidores_directos,
        AVG(s.dato_potencial) AS promedio_potencial_seguidores,
        SUM(CASE WHEN s.estado = 'Creció' THEN 1 ELSE 0 END) AS seguidores_creciendo
    FROM colaboradores c
    LEFT JOIN colaboradores s ON s.lider_directo = c.documento
    GROUP BY c.id, c.documento, c.nombres, c.apellidos, c.perfil, c.territorio
    HAVING total_seguidores_directos > 0
    ORDER BY total_seguidores_directos DESC",
    
    // Tabla: Historial Estados (Requerida para v_trazabilidad_colaborador)
    "CREATE TABLE IF NOT EXISTS historial_estados (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        colaborador_id INT UNSIGNED NOT NULL,
        fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        dato_potencial INT NOT NULL DEFAULT 0,
        dato_historico INT NOT NULL DEFAULT 0,
        estado VARCHAR(20) NOT NULL,
        usuario_id INT UNSIGNED NULL,
        motivo VARCHAR(255) NULL,
        tipo_cambio ENUM('inicial', 'actualizacion', 'reevaluacion') DEFAULT 'actualizacion',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_colaborador (colaborador_id),
        INDEX idx_fecha (fecha_registro)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Vista: Trazabilidad completa por colaborador
    "CREATE OR REPLACE VIEW v_trazabilidad_colaborador AS
    SELECT
        h.id AS historial_id,
        h.colaborador_id,
        c.documento,
        CONCAT(c.nombres, ' ', c.apellidos) AS nombre_completo,
        c.perfil,
        h.fecha_registro,
        h.dato_potencial,
        h.dato_historico,
        h.estado,
        h.tipo_cambio,
        h.motivo,
        h.usuario_id,
        u.usuario AS usuario_cambio,
        LAG(h.dato_potencial) OVER (PARTITION BY h.colaborador_id ORDER BY h.fecha_registro) AS dato_potencial_anterior,
        h.dato_potencial - LAG(h.dato_potencial) OVER (PARTITION BY h.colaborador_id ORDER BY h.fecha_registro) AS variacion_potencial,
        LAG(h.estado) OVER (PARTITION BY h.colaborador_id ORDER BY h.fecha_registro) AS estado_anterior
    FROM historial_estados h
    INNER JOIN colaboradores c ON h.colaborador_id = c.id
    LEFT JOIN usuarios u ON h.usuario_id = u.id",

    // Vista: Resumen de evolución de estados
    "CREATE OR REPLACE VIEW v_evolucion_estados AS
    SELECT
        DATE(fecha_registro) AS fecha,
        estado,
        COUNT(*) AS total_cambios,
        AVG(dato_potencial) AS promedio_potencial
    FROM historial_estados
    GROUP BY DATE(fecha_registro), estado
    ORDER BY fecha DESC, estado"
];

// 4. Ejecutar sentencias
foreach ($sqlStatements as $index => $sql) {
    try {
        $db->exec($sql);
        echo "Sentencia " . ($index + 1) . " ejecutada correctamente.\n";
    } catch (PDOException $e) {
        echo "Error ejecutando sentencia " . ($index + 1) . ": " . $e->getMessage() . "\n";
        // Ignoramos el error si es que la vista ya existe o hay un problema específico,
        // pero mostramos el mensaje para depuración.
    }
}

echo "Proceso finalizado. Intenta recargar el Dashboard.\n";
