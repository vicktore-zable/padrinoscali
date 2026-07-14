<?php
/**
 * MÓDULO: Eventos
 * Gestión completa de eventos de campaña con mapas
 */

if (!$campanaActiva) {
    echo '<div class="card text-center py-12">
        <i data-lucide="flag" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Selecciona una campaña</h2>
        <p class="text-gray-600">Debes seleccionar una campaña para ver los eventos</p>
    </div>';
    return;
}

$db = getDB();
$campanaId = $campanaActiva['id'];
$userId = $_SESSION['user_id'];

// Obtener campañas del usuario para el selector
try {
    $stmt = $db->prepare("
        SELECT c.id, c.nombre, c.estado
        FROM campanas c
        INNER JOIN usuarios_campanas uc ON c.id = uc.campana_id
        WHERE uc.usuario_id = ? AND c.estado != 'eliminada'
        ORDER BY c.nombre ASC
    ");
    $stmt->execute([$userId]);
    $campanasUsuario = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error cargando campañas usuario: " . $e->getMessage());
    $campanasUsuario = [];
}

// Obtener eventos
try {
    $stmt = $db->prepare("
        SELECT e.*, u.nombre as responsable_nombre
        FROM eventos e
        LEFT JOIN usuarios u ON e.responsable_id = u.id
        WHERE e.campana_id = ?
        ORDER BY e.fecha_inicio DESC
    ");
    $stmt->execute([$campanaId]);
    $eventos = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error eventos list: " . $e->getMessage());
    $eventos = [];
}

// Estadísticas
try {
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(asistentes_confirmados) as total_asistentes,
            SUM(CASE WHEN estado = 'programado' THEN 1 ELSE 0 END) as programados,
            SUM(CASE WHEN estado = 'finalizado' THEN 1 ELSE 0 END) as finalizados
        FROM eventos 
        WHERE campana_id = ?
    ");
    $stmt->execute([$campanaId]);
    $stats = $stmt->fetch();
} catch (Exception $e) {
    error_log("Error eventos stats: " . $e->getMessage());
    $stats = ['total' => 0, 'total_asistentes' => 0, 'programados' => 0, 'finalizados' => 0];
}

// Pre-cargar departamentos para el modal
try {
    $stmt = $db->query("SELECT DISTINCT departamento FROM territorios WHERE departamento IS NOT NULL AND departamento != '' ORDER BY departamento");
    $departamentosSSR = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Fallback si no hay datos
    if (empty($departamentosSSR)) {
        $departamentosSSR = ['Valle del Cauca']; // Valor por defecto
    }
} catch (Exception $e) {
    error_log("Error SSR departamentos eventos: " . $e->getMessage());
    $departamentosSSR = ['Valle del Cauca']; // Valor por defecto en caso de error
}
?>

<div class="space-y-6" x-data="eventosData()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Eventos</h1>
            <p class="text-gray-600 mt-2"><?= htmlspecialchars($campanaActiva['nombre']) ?></p>
        </div>
        <button @click="abrirModalNuevo()" class="btn-primary">
            <i data-lucide="plus" class="w-5 h-5 inline mr-2"></i>
            Nuevo Evento
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i data-lucide="calendar" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">
                <?= number_format($stats['total'] ?? 0) ?>
            </h3>
            <p class="text-sm text-gray-600">Total Eventos</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i data-lucide="users" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">
                <?= number_format($stats['total_asistentes'] ?? 0) ?>
            </h3>
            <p class="text-sm text-gray-600">Total Asistentes</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <i data-lucide="clock" class="w-6 h-6 text-yellow-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">
                <?= number_format($stats['programados'] ?? 0) ?>
            </h3>
            <p class="text-sm text-gray-600">Programados</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i data-lucide="check-circle" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">
                <?= number_format($stats['finalizados'] ?? 0) ?>
            </h3>
            <p class="text-sm text-gray-600">Finalizados</p>
        </div>
    </div>

    <!-- Vista de Calendario y Mapa -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Mapa -->
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Mapa de Eventos</h3>
            <div id="mapaEventos" style="height: 400px; border-radius: 8px;"></div>
        </div>

        <!-- Próximos Eventos -->
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Próximos Eventos</h3>
            <div class="space-y-3 max-h-[400px] overflow-y-auto">
                <?php
                $stmt = $db->prepare("
                    SELECT e.*, u.nombre as responsable_nombre
                    FROM eventos e
                    LEFT JOIN usuarios u ON e.responsable_id = u.id
                    WHERE e.campana_id = ? AND e.fecha_inicio >= NOW()
                    ORDER BY e.fecha_inicio ASC
                    LIMIT 10
                ");
                $stmt->execute([$campanaId]);
                $proximos = $stmt->fetchAll();
                ?>
                <?php if (empty($proximos)): ?>
                    <p class="text-sm text-gray-500 text-center py-8">No hay eventos programados</p>
                <?php else: ?>
                    <?php foreach ($proximos as $evento): ?>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer">
                            <div class="flex-shrink-0 w-12 text-center">
                                <p class="font-bold text-lg text-gray-900">
                                    <?= formatDate($evento['fecha_inicio'], 'd') ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?= formatDate($evento['fecha_inicio'], 'M') ?>
                                </p>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($evento['nombre']) ?></p>
                                <p class="text-xs text-gray-500">
                                    <i data-lucide="clock" class="w-3 h-3 inline"></i>
                                    <?= formatDate($evento['fecha_inicio'], 'H:i') ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <i data-lucide="map-pin" class="w-3 h-3 inline"></i>
                                    <?= htmlspecialchars($evento['ubicacion'] ?? 'Sin ubicación') ?>
                                </p>
                            </div>
                            <span class="badge badge-info text-xs"><?= ucfirst($evento['tipo']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <input type="text" x-model="filtros.buscar" placeholder="Buscar evento..." class="input">
            <select x-model="filtros.tipo" class="input">
                <option value="">Todos los tipos</option>
                <option value="recorrido">Recorrido</option>
                <option value="reunion">Reunión</option>
                <option value="debate">Debate</option>
                <option value="asamblea">Asamblea</option>
                <option value="mitin">Mitin</option>
                <option value="jornada-firmas">Jornada de Firmas</option>
                <option value="capacitacion">Capacitación</option>
                <option value="evento-social">Evento Social</option>
            </select>
            <select x-model="filtros.estado" class="input">
                <option value="">Todos los estados</option>
                <option value="programado">Programado</option>
                <option value="en-curso">En Curso</option>
                <option value="finalizado">Finalizado</option>
                <option value="cancelado">Cancelado</option>
            </select>
            <input type="date" x-model="filtros.fecha" class="input">
            <button @click="exportar()" class="btn-ghost">
                <i data-lucide="download" class="w-5 h-5 inline mr-2"></i>
                Exportar
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha/Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ubicación</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asistentes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($eventos)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No hay eventos registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($eventos as $evento): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900"><?= htmlspecialchars($evento['nombre']) ?></p>
                                        <p class="text-xs text-gray-500">
                                            <?= htmlspecialchars($evento['responsable_nombre'] ?? 'Sin responsable') ?>
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="badge badge-info"><?= ucfirst($evento['tipo']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div>
                                        <p><?= formatDate($evento['fecha_inicio'], 'd/m/Y') ?></p>
                                        <p class="text-xs"><?= formatDate($evento['fecha_inicio'], 'H:i') ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= htmlspecialchars(substr($evento['ubicacion'] ?? 'Sin ubicación', 0, 30)) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= $evento['asistentes_confirmados'] ?> / <?= $evento['asistentes_esperados'] ?? 0 ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $estadoClass = [
                                        'programado' => 'badge-info',
                                        'en-curso' => 'badge-warning',
                                        'finalizado' => 'badge-success',
                                        'cancelado' => 'badge-error'
                                    ];
                                    ?>
                                    <span class="badge <?= $estadoClass[$evento['estado']] ?>">
                                        <?= ucfirst($evento['estado']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <!-- Menú desplegable de Asistencias -->
                                    <div class="relative inline-block" x-data="{ open: false }">
                                        <button @click="open = !open"
                                            class="text-green-600 hover:text-green-800 mr-2 px-2 py-1 rounded hover:bg-green-50"
                                            title="Gestionar Asistencias">
                                            <i data-lucide="users-2" class="w-4 h-4 inline"></i>
                                            <i data-lucide="chevron-down" class="w-3 h-3 inline"></i>
                                        </button>

                                        <div x-show="open" @click.away="open = false" x-transition
                                            class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                            <a @click="verQR(<?= $evento['id'] ?>); open = false"
                                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer rounded-t-lg">
                                                <i data-lucide="qr-code" class="w-4 h-4 inline mr-2"></i>
                                                Generar Código QR
                                            </a>
                                            <a @click="verAsistencia(<?= $evento['id'] ?>); open = false"
                                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">
                                                <i data-lucide="list-checks" class="w-4 h-4 inline mr-2"></i>
                                                Ver Lista de Asistentes
                                            </a>
                                            <a href="index.php?page=dashboard_asistencia&evento_id=<?= $evento['id'] ?>"
                                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-b-lg">
                                                <i data-lucide="bar-chart-3" class="w-4 h-4 inline mr-2"></i>
                                                Dashboard de Análisis
                                            </a>
                                        </div>
                                    </div>

                                    <button @click="editar(<?= $evento['id'] ?>)" class="text-yellow-600 hover:text-yellow-800 mr-2" title="Editar">
                                        <i data-lucide="edit" class="w-4 h-4 inline"></i>
                                    </button>
                                    <button class="text-red-600 hover:text-red-800">
                                        <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Código QR -->
    <div x-show="modalQR" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]"
        style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full relative z-[10000]" @click.away="modalQR = false">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Código QR - Registro de Asistencia</h2>
            </div>
            <div class="p-6 text-center">
                <div id="qrcode" class="inline-block mb-4"></div>
                <p class="text-sm text-gray-600 mb-4">Escanea este código QR para registrar tu asistencia</p>
                <p class="text-xs text-gray-500 mb-4 break-all" x-text="urlRegistro"></p>
                <div class="flex gap-3">
                    <button @click="descargarQR()" class="btn-primary flex-1">
                        <i data-lucide="download" class="w-4 h-4 inline mr-2"></i>
                        Descargar QR
                    </button>
                    <button @click="copiarURL()" class="btn-ghost flex-1">
                        <i data-lucide="copy" class="w-4 h-4 inline mr-2"></i>
                        Copiar URL
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Lista de Asistencia -->
    <div x-show="modalAsistencia" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]"
        style="display: none;">
        <div class="bg-white rounded-2xl max-w-6xl w-full max-h-[90vh] overflow-y-auto relative z-[10000]" @click.away="modalAsistencia = false">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Lista de Asistencia</h2>
                <button @click="exportarAsistencia()" class="btn-ghost text-sm">
                    <i data-lucide="download" class="w-4 h-4 inline mr-2"></i>
                    Exportar Excel
                </button>
            </div>

            <!-- Estadísticas -->
            <div class="p-6 bg-gray-50 border-b">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600" x-text="statsAsistencia.total || 0"></p>
                        <p class="text-xs text-gray-600">Total</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-purple-600" x-text="statsAsistencia.masculino || 0"></p>
                        <p class="text-xs text-gray-600">Masculino</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-pink-600" x-text="statsAsistencia.femenino || 0"></p>
                        <p class="text-xs text-gray-600">Femenino</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600" x-text="Math.round(statsAsistencia.edad_promedio || 0)"></p>
                        <p class="text-xs text-gray-600">Edad Promedio</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-orange-600" x-text="statsAsistencia.via_qr || 0"></p>
                        <p class="text-xs text-gray-600">Vía QR</p>
                    </div>
                </div>
            </div>

            <!-- Tabla de asistentes -->
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Documento</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Género</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Grupo Etario</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ubicación</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Habeas Data</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Registro</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="asistente in asistentes" :key="asistente.id">
                                <tr class="hover:bg-gray-50 cursor-pointer" @click="verDetalleAsistente(asistente)">
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-gray-900" x-text="asistente.nombre"></div>
                                        <div class="text-xs text-gray-500" x-text="asistente.email || 'Sin email'"></div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-gray-600" x-text="asistente.tipo_documento"></span>
                                        <span x-text="asistente.documento"></span>
                                    </td>
                                    <td class="px-3 py-2 capitalize" x-text="asistente.genero"></td>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs"
                                              x-text="asistente.grupo_etareo || '-'"></span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-xs">
                                            <div x-text="asistente.municipio"></div>
                                            <div class="text-gray-500" x-text="asistente.barrio || '-'"></div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-xs" x-text="asistente.telefono || '-'"></td>
                                    <td class="px-3 py-2 text-center">
                                        <span x-show="asistente.habeas_data" class="text-green-600">
                                            <i data-lucide="check-circle" class="w-4 h-4 inline"></i>
                                        </span>
                                        <span x-show="!asistente.habeas_data" class="text-red-600">
                                            <i data-lucide="x-circle" class="w-4 h-4 inline"></i>
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-xs">
                                            <div x-text="new Date(asistente.fecha_registro).toLocaleDateString('es-CO')"></div>
                                            <span class="px-2 py-0.5 rounded"
                                                  :class="asistente.metodo_registro === 'qr' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'"
                                                  x-text="asistente.metodo_registro?.toUpperCase()"></span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="asistentes.length === 0" class="text-center py-8 text-gray-500">
                    No hay registros de asistencia aún
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Evento -->
    <div x-show="modalNuevo" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]"
        style="display: none;">
        <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto relative z-[10000]" @click.away="cerrarModal()">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Nuevo Evento</h2>
            </div>
            <form @submit.prevent="guardar()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <!-- Nombre -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Evento *</label>
                        <input type="text" x-model="form.nombre" required class="input">
                    </div>

                    <!-- Campaña -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Campaña *</label>
                        <select x-model="form.campana_id" required class="input">
                            <option value="">Seleccionar campaña...</option>
                            <?php foreach ($campanasUsuario as $campana): ?>
                                <option value="<?= $campana['id'] ?>"
                                    <?= $campana['id'] == $campanaId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($campana['nombre']) ?>
                                    <?= $campana['estado'] != 'activa' ? ' (' . ucfirst($campana['estado']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <i data-lucide="info" class="w-3 h-3 inline"></i>
                            Selecciona la campaña a la que pertenece este evento
                        </p>
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Evento *</label>
                        <select x-model="form.tipo" required class="input">
                            <option value="">Seleccionar...</option>
                            <option value="recorrido">Recorrido</option>
                            <option value="reunion">Reunión</option>
                            <option value="debate">Debate</option>
                            <option value="asamblea">Asamblea</option>
                            <option value="mitin">Mitin</option>
                            <option value="jornada-firmas">Jornada de Firmas</option>
                            <option value="capacitacion">Capacitación</option>
                            <option value="evento-social">Evento Social</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado *</label>
                        <select x-model="form.estado" required class="input">
                            <option value="programado">Programado</option>
                            <option value="en-curso">En Curso</option>
                            <option value="finalizado">Finalizado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>

                    <!-- Fecha Inicio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha y Hora Inicio *</label>
                        <input type="datetime-local" x-model="form.fecha_inicio" required class="input">
                    </div>

                    <!-- Fecha Fin -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha y Hora Fin</label>
                        <input type="datetime-local" x-model="form.fecha_fin" class="input">
                    </div>

                    <!-- Ubicación -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ubicación *</label>
                        <input type="text" x-model="form.ubicacion" required class="input">
                    </div>

                    <!-- Dirección -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dirección Completa</label>
                        <textarea x-model="form.direccion" rows="2" class="input"></textarea>
                    </div>

                    <!-- Selectores Geográficos en Cascada -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Departamento</label>
                        <select x-model="form.departamento" @change="cargarMunicipios()" class="input">
                            <option value="">Seleccionar departamento...</option>
                            <template x-for="dep in listas.departamentos" :key="dep">
                                <option :value="dep" x-text="dep"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Municipio</label>
                        <select x-model="municipio_raw" @change="selectMunicipio()" class="input" :disabled="!form.departamento || loadingMunicipios">
                            <option value="">
                                <span x-show="!loadingMunicipios">Seleccionar municipio...</span>
                                <span x-show="loadingMunicipios">Cargando...</span>
                            </option>
                            <template x-for="mun in listas.municipios" :key="mun.cod_mpio">
                                <option :value="JSON.stringify(mun)" x-text="mun.municipio"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Territorio (Opcional)</label>
                        <select x-model="form.tipo_territorio" @change="cargarTerritorios()" class="input" :disabled="!form.municipio">
                            <option value="">Seleccionar tipo...</option>
                            <template x-for="tipo in listas.tipos_territorio" :key="tipo">
                                <option :value="tipo" x-text="tipo"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Territorio (Opcional)</label>
                        <select x-model="form.territorio" @change="cargarBarrios()" class="input" :disabled="!form.tipo_territorio">
                            <option value="">Seleccionar territorio...</option>
                            <template x-for="terr in listas.territorios" :key="terr">
                                <option :value="terr" x-text="terr"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Barrio/Vereda (Opcional)</label>
                        <select x-model="form.barrio" class="input" :disabled="!form.territorio">
                            <option value="">Seleccionar barrio...</option>
                            <template x-for="barr in listas.barrios" :key="barr">
                                <option :value="barr" x-text="barr"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Asistentes Esperados -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Asistentes Esperados</label>
                        <input type="number" x-model="form.asistentes_esperados" min="0" class="input">
                    </div>

                    <!-- Presupuesto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Presupuesto</label>
                        <input type="number" x-model="form.presupuesto" min="0" step="0.01" class="input">
                    </div>

                    <!-- Descripción -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                        <textarea x-model="form.descripcion" rows="3" class="input"></textarea>
                    </div>

                    <!-- Notas -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notas</label>
                        <textarea x-model="form.notas" rows="2" class="input"></textarea>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="btn-primary flex-1">
                        <i data-lucide="save" class="w-5 h-5 inline mr-2"></i>
                        Guardar Evento
                    </button>
                    <button type="button" @click="cerrarModal()" class="btn-ghost">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function eventosData() {
        return {
            modalNuevo: false,
            modalQR: false,
            modalAsistencia: false,
            eventoIdActual: null,
            urlRegistro: '',
            asistentes: [],
            statsAsistencia: {},
            mapa: null,
            loading: false,
            filtros: {
                fecha: ''
            },
            loadingMunicipios: false,
            form: {
                id: null,
                campana_id: <?= $campanaId ?>,
                nombre: '',
                tipo: '',
                descripcion: '',
                fecha_inicio: '',
                fecha_fin: '',
                ubicacion: '',
                departamento: '',
                municipio: '',
                tipo_territorio: '',
                territorio: '',
                barrio: '',
                direccion: '',
                asistentes_esperados: '',
                presupuesto: '',
                estado: 'programado',
                notas: ''
            },
            municipio_raw: '',
            listas: {
                departamentos: <?= json_encode($departamentosSSR) ?>,
                municipios: [],
                tipos_territorio: [],
                territorios: [],
                barrios: []
            },

            async init() {
                // Si la lista de departamentos está vacía, cargar desde API
                if (this.listas.departamentos.length <= 1) {
                    await this.cargarDepartamentos();
                }
                // Inicializar mapa
                this.initMapa();
            },

            initMapa() {
                setTimeout(() => {
                    this.mapa = L.map('mapaEventos').setView([4.570868, -74.297333], 6);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(this.mapa);

                    const markers = [];
                    // Agregar marcadores de eventos con coordenadas
                    <?php foreach ($eventos as $ev): ?>
                        <?php if ($ev['latitud'] && $ev['longitud']): ?>
                            const marker<?= $ev['id'] ?> = L.marker([<?= $ev['latitud'] ?>, <?= $ev['longitud'] ?>])
                                .addTo(this.mapa)
                                .bindPopup('<strong><?= addslashes($ev['nombre']) ?></strong><br><?= addslashes($ev['ubicacion']) ?>');
                            markers.push([<?= $ev['latitud'] ?>, <?= $ev['longitud'] ?>]);
                        <?php endif; ?>
                    <?php endforeach; ?>

                    // Centrar mapa si hay marcadores
                    if (markers.length > 0) {
                        this.mapa.fitBounds(markers);
                    }
                }, 100);
            },

            verQR(id) {
                this.eventoIdActual = id;
                this.urlRegistro = window.location.origin + '/registro_asistencia.php?evento=' + id;
                this.modalQR = true;

                document.getElementById('qrcode').innerHTML = '';

                this.$nextTick(() => {
                    if (typeof QRCode !== 'undefined') {
                        new QRCode(document.getElementById('qrcode'), {
                            text: this.urlRegistro,
                            width: 256,
                            height: 256,
                            colorDark: '#000000',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.H
                        });
                    } else {
                        document.getElementById('qrcode').innerHTML = '<p class="text-red-600">Librería QRCode no cargada. Agregar CDN en index.php</p>';
                    }
                    lucide.createIcons();
                });
            },

            descargarQR() {
                const canvas = document.querySelector('#qrcode canvas');
                if (canvas) {
                    const link = document.createElement('a');
                    link.download = 'qr-evento-' + this.eventoIdActual + '.png';
                    link.href = canvas.toDataURL();
                    link.click();
                }
            },

            copiarURL() {
                navigator.clipboard.writeText(this.urlRegistro).then(() => {
                    alert('URL copiada al portapapeles');
                });
            },

            async verAsistencia(id) {
                this.eventoIdActual = id;
                this.modalAsistencia = true;
                this.asistentes = [];
                this.statsAsistencia = {};

                try {
                    const response = await fetch(`/aratio/api/asistencia_eventos.php?evento_id=${id}`);
                    const result = await response.json();

                    if (result.success) {
                        this.asistentes = result.data;
                        this.statsAsistencia = result.stats;
                    } else {
                        alert('Error al cargar asistentes: ' + result.message);
                    }
                } catch (error) {
                    alert('Error de conexión: ' + error.message);
                }

                this.$nextTick(() => lucide.createIcons());
            },

            exportarAsistencia() {
                alert('Exportar asistencia del evento #' + this.eventoIdActual + ' a Excel');
            },

        async cargarDepartamentos() {
            try {
                const response = await fetch('/aratio/api/territorios.php?accion=departamentos');
                const result = await response.json();
                if (result.success) {
                    this.listas.departamentos = result.data;
                }
            } catch (error) {
                console.error('Error cargando departamentos:', error);
            }
        },

        async cargarMunicipios() {
            this.form.municipio = '';
            this.municipio_raw = '';
            this.form.tipo_territorio = '';
            this.form.barrio = '';
            this.listas.municipios = [];
            this.listas.tipos_territorio = [];
            this.listas.territorios = [];
            this.listas.barrios = [];

            if (!this.form.departamento) return;

            this.loadingMunicipios = true;
            try {
                const response = await fetch(`/aratio/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.form.departamento)}`);
                const result = await response.json();
                if (result.success) {
                    this.listas.municipios = result.data;
                }
            } catch (error) {
                console.error('Error cargando municipios:', error);
            } finally {
                this.loadingMunicipios = false;
            }
        },

        async selectMunicipio() {
            if (!this.municipio_raw) return;
            try {
                const munObj = JSON.parse(this.municipio_raw);
                this.form.municipio = munObj.municipio;
                await this.cargarTiposTerritorio();
            } catch (e) {
                console.error('Error parsing municipio:', e);
            }
        },

        async cargarTiposTerritorio(keepValue = false) {
            if (!keepValue) {
                this.form.tipo_territorio = '';
                this.form.territorio = '';
                this.form.barrio = '';
            }
            this.listas.tipos_territorio = [];
            if (!this.form.departamento || !this.form.municipio) return;

            try {
                const response = await fetch(`/aratio/api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}`);
                const result = await response.json();
                if (result.success) {
                    this.listas.tipos_territorio = result.data;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async cargarTerritorios(keepValue = false) {
            if (!keepValue) {
                this.form.territorio = '';
                this.form.barrio = '';
            }
            this.listas.territorios = [];
            if (!this.form.departamento || !this.form.municipio || !this.form.tipo_territorio) return;

            try {
                const response = await fetch(`/aratio/api/territorios.php?accion=territorios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(this.form.tipo_territorio)}`);
                const result = await response.json();
                if (result.success) {
                    this.listas.territorios = result.data;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async cargarBarrios(keepValue = false) {
            if (!keepValue) this.form.barrio = '';
            this.listas.barrios = [];
            if (!this.form.departamento || !this.form.municipio || !this.form.tipo_territorio || !this.form.territorio) return;

            try {
                const response = await fetch(`/aratio/api/territorios.php?accion=barrios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(this.form.tipo_territorio)}&territorio=${encodeURIComponent(this.form.territorio)}`);
                const result = await response.json();
                if (result.success) {
                    this.listas.barrios = result.data;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },
        async guardar() {
                this.loading = true;
                const method = this.form.id ? 'PUT' : 'POST';

                try {
                    const response = await fetch('/aratio/api/eventos.php', {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(this.form)
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert(result.message);
                        this.cerrarModal();
                        window.location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Error de conexión: ' + error.message);
                }
                this.loading = false;
            },

            async eliminar(id) {
                if (!confirm('¿Estás seguro de eliminar este evento?')) return;

                try {
                    const response = await fetch(`/aratio/api/eventos.php?id=${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const result = await response.json();

                    if (result.success) {
                        alert(result.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Error de conexión: ' + error.message);
                }
            },

            cerrarModal() {
                this.modalNuevo = false;
                this.form = {
                    id: null,
                    campana_id: <?= $campanaId ?>,
                    nombre: '',
                    tipo: '',
                    descripcion: '',
                    fecha_inicio: '',
                    fecha_fin: '',
                    ubicacion: '',
                departamento: '',
                municipio: '',
                tipo_territorio: '',
                territorio: '',
                barrio: '',
                    direccion: '',
                    asistentes_esperados: '',
                    presupuesto: '',
                    estado: 'programado',
                    notas: ''
                };
            },

            async editar(id) {
                try {
                    const response = await fetch(`/aratio/api/eventos.php?id=${id}`);
                    const result = await response.json();

                    if (result.success && result.data) {
                        const evento = result.data;
                        this.form = {
                            id: evento.id,
                            campana_id: evento.campana_id,
                            nombre: evento.nombre,
                            tipo: evento.tipo,
                            descripcion: evento.descripcion || '',
                            fecha_inicio: evento.fecha_inicio ? evento.fecha_inicio.replace(' ', 'T').substring(0, 16) : '',
                            fecha_fin: evento.fecha_fin ? evento.fecha_fin.replace(' ', 'T').substring(0, 16) : '',
                            ubicacion: evento.ubicacion || '',
                            departamento: evento.departamento || '',
                            municipio: evento.municipio || '',
                            tipo_territorio: evento.tipo_territorio || '',
                            territorio: evento.territorio || '',
                            barrio: evento.barrio || '',
                            direccion: evento.direccion || '',
                            asistentes_esperados: evento.asistentes_esperados || '',
                            presupuesto: evento.presupuesto || '',
                            estado: evento.estado || 'programado',
                            notas: evento.notas || ''
                        };

                        // Pre-cargar municipios si hay departamento
                if (this.form.departamento) {
                    await this.cargarMunicipios();
                    if (this.form.municipio) {
                        const munMatch = this.listas.municipios.find(m => m.municipio === this.form.municipio);
                        if (munMatch) {
                            this.municipio_raw = JSON.stringify(munMatch);
                        }
                    }
                    if (this.form.municipio) {
                        await this.cargarTiposTerritorio();
                        if (this.form.tipo_territorio) {
                            await this.cargarTerritorios();
                            if (this.form.territorio) {
                                await this.cargarBarrios();
                            }
                        }
                    }
                }
                
                // Restaurar valores
                this.form.municipio = evento.municipio || '';
                this.form.tipo_territorio = evento.tipo_territorio || '';
                this.form.territorio = evento.territorio || '';
                this.form.barrio = evento.barrio || '';
            }
                        this.modalNuevo = true;
                    } else {
                        alert('Error al cargar el evento');
                    }
                } catch (error) {
                    alert('Error de conexión: ' + error.message);
                }
            },

            exportar() {
                alert('Exportar eventos a Excel');
            }
        }
    }

    // Inicializar iconos
    lucide.createIcons();
</script>