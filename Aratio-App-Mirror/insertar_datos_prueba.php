<?php
/**
 * Script para insertar datos de prueba
 */
require_once 'config/config.php';

try {
    $db = getDB();
    echo "Conectado a la base de datos: " . DB_NAME . "\n";
    
    // Insertar usuario administrador si no existe
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = 'admin@aratio.mrmtech.net'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $passwordHash = password_hash('Admin123!', PASSWORD_BCRYPT);
        $stmt = $db->prepare("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Administrador', 'admin@aratio.mrmtech.net', ?, 'super-admin', 'activo')
        ");
        $stmt->execute([$passwordHash]);
        echo "✅ Usuario administrador creado\n";
    } else {
        echo "ℹ️ Usuario administrador ya existe\n";
    }
    
    // Insertar elección de prueba si no existe
    $stmt = $db->prepare("SELECT id FROM elecciones WHERE codigo = 'ELEC-2027-001'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("
            INSERT INTO elecciones (codigo, nombre, tipo, ambito, fecha_eleccion, periodo_inicio, periodo_fin, estado, descripcion) 
            VALUES ('ELEC-2027-001', 'Elecciones Locales 2027', 'alcaldia', 'municipal', '2027-10-28', '2027-01-01', '2027-12-31', 'programada', 'Elecciones municipales de Bogotá 2027')
        ");
        $stmt->execute();
        echo "✅ Elección de prueba creada\n";
    } else {
        echo "ℹ️ Elección ya existe\n";
    }
    
    // Insertar candidato de prueba si no existe
    $stmt = $db->prepare("SELECT id FROM candidatos WHERE documento = '80123456'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        // Obtener ID de la elección
        $stmt = $db->prepare("SELECT id FROM elecciones WHERE codigo = 'ELEC-2027-001'");
        $stmt->execute();
        $eleccion = $stmt->fetch();
        $eleccionId = $eleccion['id'];
        
        $stmt = $db->prepare("
            INSERT INTO candidatos (nombres, apellidos, nombre_completo, documento, tipo_documento, cargo_aspira, eleccion_id, email, telefono, departamento, municipio, estado) 
            VALUES ('Juan', 'Pérez', 'Juan Pérez González', '80123456', 'CC', 'Alcalde de Bogotá', ?, 'juan.perez@aratio.mrmtech.net', '3001234567', 'Cundinamarca', 'Bogotá', 'activo')
        ");
        $stmt->execute([$eleccionId]);
        echo "✅ Candidato de prueba creado\n";
    } else {
        echo "ℹ️ Candidato ya existe\n";
    }
    
    // Insertar campaña de prueba si no existe
    $stmt = $db->prepare("SELECT id FROM campanas WHERE codigo = 'CAMP-2027-001'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        // Obtener IDs del candidato y elección
        $stmt = $db->prepare("SELECT id FROM candidatos WHERE documento = '80123456'");
        $stmt->execute();
        $candidato = $stmt->fetch();
        $candidatoId = $candidato['id'];
        
        $stmt = $db->prepare("SELECT id FROM elecciones WHERE codigo = 'ELEC-2027-001'");
        $stmt->execute();
        $eleccion = $stmt->fetch();
        $eleccionId = $eleccion['id'];
        
        $stmt = $db->prepare("
            INSERT INTO campanas (codigo, nombre, slogan, descripcion, estado, candidato_id, eleccion_id, departamento, municipio, meta_votos, presupuesto, fecha_inicio, fecha_fin, color_primario, color_secundario) 
            VALUES ('CAMP-2027-001', 'Alcaldía Bogotá 2027', 'Un futuro para todos', 'Campaña por un Bogotá inclusivo y próspero', 'activa', ?, ?, 'Cundinamarca', 'Bogotá', 50000, 100000000, '2027-01-01', '2027-10-28', '#FF00FF', '#FFD700')
        ");
        $stmt->execute([$candidatoId, $eleccionId]);
        $campanaId = $db->lastInsertId();
        echo "✅ Campaña de prueba creada\n";
        
        // Asignar usuario a la campaña
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = 'admin@aratio.mrmtech.net'");
        $stmt->execute();
        $usuario = $stmt->fetch();
        $usuarioId = $usuario['id'];
        
        $stmt = $db->prepare("
            INSERT INTO usuarios_campanas (usuario_id, campana_id, rol_campana) 
            VALUES (?, ?, 'administrador')
        ");
        $stmt->execute([$usuarioId, $campanaId]);
        echo "✅ Usuario asignado a la campaña\n";
    } else {
        echo "ℹ️ Campaña ya existe\n";
    }
    
    echo "\n🎉 ¡Datos de prueba insertados correctamente!\n";
    echo "\n📊 Resumen:\n";
    
    // Contar registros
    $tables = ['usuarios', 'elecciones', 'candidatos', 'campanas', 'usuarios_campanas'];
    foreach ($tables as $table) {
        $stmt = $db->query("SELECT COUNT(*) as total FROM $table");
        $count = $stmt->fetch();
        echo "- $table: " . $count['total'] . " registros\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Verifica que la base de datos exista y que el servidor MySQL esté corriendo.\n";
}