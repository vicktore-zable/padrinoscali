<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/_security.php';
$db = getDB();

$eventoId = $_GET['evento_id'] ?? null;
$formato = $_GET['formato'] ?? 'xlsx';
$asistenteId = $_GET['asistente_id'] ?? null;

if (!$eventoId) {
    die('ID de evento requerido');
}

$stmt = $db->prepare("SELECT id, campana_id, nombre, fecha_inicio, fecha_fin, ubicacion, direccion, tipo, estado, responsable_id, asistentes_esperados, asistentes_confirmados FROM eventos WHERE id = ?");
$stmt->execute([$eventoId]);
$evento = $stmt->fetch();
if (!$evento) die('Evento no encontrado');

// Export contiene PII + firmas: exige sesión y acceso a la campaña del evento.
eventos_require_admin((int)$evento['campana_id']);

$stmt = $db->prepare("
    SELECT id, nombre, documento, tipo_documento, telefono, email, fecha_nacimiento,
           genero, grupo_etareo, departamento, municipio, barrio,
           areas_interes, habeas_data, acepta_comunicaciones, autorizacion_imagenes,
           firma_digital, notas, fecha_registro, metodo_registro, asistio
    FROM asistencia_eventos WHERE evento_id = ?" . ($asistenteId ? " AND id = ?" : "") . "
    ORDER BY nombre ASC
");
if ($asistenteId) {
    $stmt->execute([$eventoId, $asistenteId]);
} else {
    $stmt->execute([$eventoId]);
}
$asistentes = $stmt->fetchAll();

$logoPath = __DIR__ . '/../../assets/logo-small.png';
$logoBase64 = '';
if (file_exists($logoPath)) {
    $logoBase64 = base64_encode(file_get_contents($logoPath));
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$qrText = urlencode($scheme . '://' . $_SERVER['HTTP_HOST'] . '/aratio/mod_eventos/pages/qr_registro.php?evento=' . $eventoId);

if ($formato === 'xlsx') {
    if (!class_exists('Shuchkin\SimpleXLSXGen')) {
        die('Librería Excel no disponible');
    }

    $headers = ['#', 'Nombre', 'Documento', 'Tipo Doc', 'Teléfono', 'Email', 'Género', 'Grupo Etario',
                'Departamento', 'Municipio', 'Barrio', 'Habeas Data', 'Comunicaciones', 'Autorización Imagen',
                'Método', 'Fecha Registro', 'Notas'];

    $rows = [$headers];
    $idx = 1;
    foreach ($asistentes as $a) {
        $rows[] = [
            $idx++,
            $a['nombre'],
            $a['documento'],
            strtoupper($a['tipo_documento'] ?? 'CC'),
            $a['telefono'] ?? '',
            $a['email'] ?? '',
            $a['genero'] ?? '',
            $a['grupo_etareo'] ?? '',
            $a['departamento'] ?? '',
            $a['municipio'] ?? '',
            $a['barrio'] ?? '',
            $a['habeas_data'] ? 'Sí' : 'No',
            $a['acepta_comunicaciones'] ? 'Sí' : 'No',
            isset($a['autorizacion_imagenes']) && $a['autorizacion_imagenes'] ? 'Sí' : 'No',
            strtoupper($a['metodo_registro'] ?? 'QR'),
            $a['fecha_registro'] ?? '',
            $a['notas'] ?? ''
        ];
    }

    $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($rows);
    $filename = 'Asistencia_' . preg_replace('/[^a-zA-Z0-9]/', '_', $evento['nombre']) . '_' . date('Y-m-d') . '.xlsx';
    $xlsx->downloadAs($filename);
    exit;
}

// PDF/Print view
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planilla de Asistencia — <?= htmlspecialchars($evento['nombre']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        @media print {
            @page { margin: 15mm; size: A4 portrait; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        .firma-cell { max-width: 120px; height: 40px; }
        .firma-cell img { max-height: 36px; max-width: 110px; object-fit: contain; }
    </style>
</head>
<body class="bg-gray-50 text-sm" style="color: #2F2F35;">
    <!-- Botón imprimir -->
    <div class="no-print fixed top-4 right-4 z-50 flex gap-2">
        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl font-medium text-sm text-white shadow-lg flex items-center gap-2"
                style="background: linear-gradient(135deg, #E6007E, #5B2A86);">
            <i data-lucide="printer" class="w-4 h-4"></i> Imprimir / PDF
        </button>
        <button onclick="window.close()" class="px-5 py-2.5 rounded-xl font-medium text-sm shadow-lg border border-gray-200 bg-white flex items-center gap-2" style="color: #6B7280;">
            <i data-lucide="x" class="w-4 h-4"></i> Cerrar
        </button>
    </div>

    <div class="max-w-4xl mx-auto p-8">
        <!-- Header con logo -->
        <div class="flex items-center justify-between mb-6 pb-6 border-b-2" style="border-color: #5B2A86;">
            <div class="flex items-center gap-4">
                <?php if ($logoBase64): ?>
                <img src="data:image/png;base64,<?= $logoBase64 ?>" alt="Padrinos Cali" class="h-14 w-auto">
                <?php endif; ?>
                <div>
                    <h1 class="text-2xl font-black tracking-tight" style="color: #5B2A86;">PADRINOS CALI</h1>
                    <p class="text-xs font-semibold uppercase tracking-widest" style="color: #E6007E;">Programa de Liderazgo Social</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs font-bold uppercase" style="color: #9CA3AF;">Generado</div>
                <div class="text-sm font-semibold" style="color: #2F2F35;"><?= date('d/m/Y H:i') ?></div>
            </div>
        </div>

        <!-- Datos del Evento -->
        <div class="rounded-2xl p-5 mb-6" style="background: #FAFAFA; border: 1px solid #f0f0f0;">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div class="col-span-2">
                    <span class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Evento</span>
                    <p class="font-bold text-base" style="color: #5B2A86;"><?= htmlspecialchars($evento['nombre']) ?></p>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Tipo</span>
                    <p class="font-medium capitalize"><?= htmlspecialchars($evento['tipo']) ?></p>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Estado</span>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold"
                          style="background: <?= $evento['estado'] === 'finalizado' ? 'rgba(65,184,83,0.12)' : 'rgba(91,42,134,0.08)' ?>; color: <?= $evento['estado'] === 'finalizado' ? '#41B853' : '#5B2A86' ?>;">
                        <?= ucfirst($evento['estado']) ?>
                    </span>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Fecha</span>
                    <p class="font-medium"><?= date('d/m/Y H:i', strtotime($evento['fecha_inicio'])) ?></p>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Ubicación</span>
                    <p class="font-medium"><?= htmlspecialchars($evento['ubicacion'] ?? '—') ?></p>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Dirección</span>
                    <p class="font-medium"><?= htmlspecialchars($evento['direccion'] ?? '—') ?></p>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Asistentes</span>
                    <p class="font-bold" style="color: #E6007E;"><?= count($asistentes) ?> / <?= $evento['asistentes_esperados'] ?? '—' ?></p>
                </div>
            </div>
        </div>

        <!-- Tabla de Asistencia -->
        <h2 class="text-lg font-bold mb-3 flex items-center gap-2" style="color: #5B2A86;">
            <i data-lucide="list-checks" class="w-5 h-5" style="color: #E6007E;"></i>
            Lista de Asistencia
            <span class="text-sm font-normal" style="color: #9CA3AF;">(<?= count($asistentes) ?> registros)</span>
        </h2>

        <div class="overflow-x-auto rounded-xl border" style="border-color: #e5e7eb;">
            <table class="w-full text-xs">
                <thead>
                    <tr style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%);">
                        <th class="px-3 py-2.5 text-left text-white font-semibold">#</th>
                        <th class="px-3 py-2.5 text-left text-white font-semibold">Nombre</th>
                        <th class="px-3 py-2.5 text-left text-white font-semibold">Documento</th>
                        <th class="px-3 py-2.5 text-left text-white font-semibold">Género</th>
                        <th class="px-3 py-2.5 text-left text-white font-semibold">Municipio</th>
                        <th class="px-3 py-2.5 text-left text-white font-semibold">Barrio</th>
                        <th class="px-3 py-2.5 text-left text-white font-semibold">Teléfono</th>
                        <th class="px-3 py-2.5 text-center text-white font-semibold">Habeas Data</th>
                        <th class="px-3 py-2.5 text-center text-white font-semibold">Firma</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: #f0f0f0;">
                    <?php $i = 1; foreach ($asistentes as $a): ?>
                    <tr class="<?= $i % 2 === 0 ? 'bg-gray-50/50' : 'bg-white' ?>">
                        <td class="px-3 py-2 font-medium" style="color: #9CA3AF;"><?= $i++ ?></td>
                        <td class="px-3 py-2 font-medium" style="color: #2F2F35;"><?= htmlspecialchars($a['nombre']) ?></td>
                        <td class="px-3 py-2" style="color: #4B5563;"><?= strtoupper($a['tipo_documento'] ?? 'CC') ?> <?= htmlspecialchars($a['documento']) ?></td>
                        <td class="px-3 py-2 capitalize" style="color: #4B5563;"><?= htmlspecialchars($a['genero'] ?? '—') ?></td>
                        <td class="px-3 py-2" style="color: #4B5563;"><?= htmlspecialchars($a['municipio'] ?? '—') ?></td>
                        <td class="px-3 py-2" style="color: #4B5563;"><?= htmlspecialchars($a['barrio'] ?? '—') ?></td>
                        <td class="px-3 py-2" style="color: #4B5563;"><?= htmlspecialchars($a['telefono'] ?? '—') ?></td>
                        <td class="px-3 py-2 text-center">
                            <?php if ($a['habeas_data']): ?>
                                <span style="color: #41B853;">✓</span>
                            <?php else: ?>
                                <span style="color: #E6007E;">✗</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 text-center firma-cell">
                            <?php if ($a['firma_digital']): ?>
                                <img src="<?= htmlspecialchars($a['firma_digital']) ?>" alt="Firma" style="max-height: 32px; max-width: 100px;">
                            <?php else: ?>
                                <span style="color: #d1d5db;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($asistentes)): ?>
        <div class="text-center py-12" style="color: #9CA3AF;">
            <p>No hay registros de asistencia para este evento</p>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="mt-8 pt-4 text-center text-xs" style="border-top: 1px solid #f0f0f0; color: #9CA3AF;">
            <p>Padrinos Cali — Programa de Liderazgo Social</p>
            <p class="mt-1">Documento generado el <?= date('d/m/Y \a \l\a\s H:i') ?></p>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
