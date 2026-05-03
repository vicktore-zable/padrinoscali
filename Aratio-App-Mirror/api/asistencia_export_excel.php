<?php
/**
 * API: Exportar Asistencia a Excel (CSV)
 */
require_once __DIR__ . '/../config/config.php';
requireAuth();

$db = getDB();
$evento_id = $_GET['evento_id'] ?? null;

if (!$evento_id) {
    die("ID de evento requerido");
}

// Obtener información del evento
$stmt = $db->prepare("SELECT nombre FROM eventos WHERE id = ?");
$stmt->execute([$evento_id]);
$evento = $stmt->fetch();

if (!$evento) {
    die("Evento no encontrado");
}

// Obtener asistentes
$stmt = $db->prepare("
    SELECT 
        id, nombre, documento, tipo_documento, telefono, email, 
        fecha_nacimiento, genero, departamento, municipio, 
        barrio, notas, fecha_registro, metodo_registro
    FROM asistencia_eventos 
    WHERE evento_id = ? 
    ORDER BY fecha_registro ASC
");
$stmt->execute([$evento_id]);
$asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "Asistencia_" . str_replace(' ', '_', $evento['nombre']) . "_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// BOM para Excel (UTF-8)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Encabezados
fputcsv($output, [
    'ID', 'Nombre', 'Documento', 'Tipo Doc', 'Teléfono', 'Email', 
    'Fecha Nacimiento', 'Género', 'Departamento', 'Municipio', 
    'Barrio', 'Notas', 'Fecha Registro', 'Método'
]);

// Datos
foreach ($asistentes as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
