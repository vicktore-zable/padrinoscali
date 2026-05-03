<?php
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';

$procedures = [
    'sp_obtener_red_jerarquica' => "
        CREATE PROCEDURE sp_obtener_red_jerarquica(IN p_documento_lider VARCHAR(20))
        BEGIN
            WITH RECURSIVE red_jerarquica AS (
                -- Caso base: el líder inicial
                SELECT 
                    id, documento, nombres, apellidos, perfil,
                    nivel_participacion, lider_directo, dato_potencial,
                    0 AS nivel, documento AS raiz
                FROM colaboradores
                WHERE documento = p_documento_lider
                
                UNION ALL
                
                -- Caso recursivo: seguidores
                SELECT 
                    c.id, c.documento, c.nombres, c.apellidos, c.perfil,
                    c.nivel_participacion, c.lider_directo, c.dato_potencial,
                    rj.nivel + 1, rj.raiz
                FROM colaboradores c
                INNER JOIN red_jerarquica rj ON c.lider_directo = rj.documento
                WHERE rj.nivel < 10  -- Límite de profundidad
            )
            SELECT * FROM red_jerarquica ORDER BY nivel, nombres;
        END",
    'sp_cambiar_lider' => "
        CREATE PROCEDURE sp_cambiar_lider(
            IN p_documento_colaborador VARCHAR(20),
            IN p_documento_nuevo_lider VARCHAR(20),
            IN p_motivo TEXT,
            IN p_usuario_id INT
        )
        BEGIN
            DECLARE v_lider_anterior VARCHAR(20);
            
            -- Obtener líder anterior
            SELECT lider_directo INTO v_lider_anterior
            FROM colaboradores
            WHERE documento = p_documento_colaborador;
            
            -- Actualizar líder
            UPDATE colaboradores
            SET lider_directo = p_documento_nuevo_lider
            WHERE documento = p_documento_colaborador;
            
            -- Registrar en historial
            INSERT INTO historial_cambios_lider (
                colaborador_documento, lider_anterior, lider_nuevo, motivo, usuario_cambio
            ) VALUES (
                p_documento_colaborador, v_lider_anterior, p_documento_nuevo_lider, p_motivo, p_usuario_id
            );
        END",
    'sp_limpiar_sesiones_expiradas' => "
        CREATE PROCEDURE sp_limpiar_sesiones_expiradas()
        BEGIN
            DELETE FROM sesiones WHERE expira_en < NOW();
        END",
    'sp_estadisticas_generales' => "
        CREATE PROCEDURE sp_estadisticas_generales()
        BEGIN
            SELECT 
                (SELECT COUNT(*) FROM colaboradores) AS total_colaboradores,
                (SELECT COUNT(DISTINCT lider_directo) FROM colaboradores WHERE lider_directo IS NOT NULL) AS total_lideres,
                (SELECT COUNT(DISTINCT territorio) FROM colaboradores WHERE territorio IS NOT NULL) AS total_territorios,
                (SELECT COUNT(*) FROM colaboradores WHERE estado = 'Nuevo') AS colaboradores_nuevos,
                (SELECT COUNT(*) FROM colaboradores WHERE estado = 'Creció') AS colaboradores_creciendo,
                (SELECT COUNT(*) FROM usuarios WHERE activo = TRUE) AS usuarios_activos;
        END"
];

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Iniciando creación de procedimientos almacenados...\n\n";
    
    foreach ($procedures as $name => $sql) {
        echo "Verificando $name... ";
        $exists = $conn->query("SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = '$name'")->fetch();
        
        if ($exists) {
            echo "YA EXISTE. Saltando.\n";
            continue;
        }
        
        echo "No existe. Creando... ";
        try {
            $conn->exec("DROP PROCEDURE IF EXISTS $name");
            $conn->exec($sql);
            echo "✅ CREADO.\n";
        } catch (PDOException $e) {
            echo "❌ ERROR: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nProceso finalizado.\n";

} catch (Exception $e) {
    echo "\n❌ ERROR GENERAL: " . $e->getMessage() . "\n";
}
