<?php
if (!$campanaActiva) {
    echo '<div class="card text-center py-12">
        <i data-lucide="flag" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Selecciona una campaña</h2>
        <p class="text-gray-600">Debes seleccionar una campaña para ver los eventos</p>
    </div>';
    return;
}

$db = getDB();
$userId = $_SESSION['user_id'];

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

$eventosJson = json_encode(array_map(function($ev) {
    return [
        'id' => $ev['id'],
        'nombre' => $ev['nombre'],
        'tipo' => $ev['tipo'],
        'fecha_inicio' => $ev['fecha_inicio'],
        'fecha_fin' => $ev['fecha_fin'],
        'ubicacion' => $ev['ubicacion'],
        'direccion' => $ev['direccion'],
        'latitud' => $ev['latitud'] ? (float)$ev['latitud'] : null,
        'longitud' => $ev['longitud'] ? (float)$ev['longitud'] : null,
        'departamento' => $ev['departamento'],
        'municipio' => $ev['municipio'],
        'barrio' => $ev['barrio'],
        'descripcion' => $ev['descripcion'],
        'asistentes_esperados' => $ev['asistentes_esperados'],
        'asistentes_confirmados' => $ev['asistentes_confirmados'],
        'estado' => $ev['estado'],
        'responsable_nombre' => $ev['responsable_nombre'] ?? '',
        'responsable_telefono' => $ev['responsable_telefono'] ?? '',
        'telefono_contacto' => $ev['telefono_contacto'] ?? '',
    ];
}, $eventos));

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

try {
    $stmt = $db->query("SELECT DISTINCT departamento FROM territorios WHERE departamento IS NOT NULL AND departamento != '' ORDER BY departamento");
    $departamentosSSR = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($departamentosSSR)) {
        $departamentosSSR = ['Valle del Cauca'];
    }
} catch (Exception $e) {
    error_log("Error SSR departamentos eventos: " . $e->getMessage());
    $departamentosSSR = ['Valle del Cauca'];
}
$apiBase = 'mod_eventos/api';
?>

<div class="space-y-6" x-data="eventosData()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold" style="color: #5B2A86;">Eventos</h1>
            <p class="text-gray-600 mt-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full inline-block" style="background: #E6007E;"></span>
                <?= htmlspecialchars($campanaActiva['nombre']) ?>
            </p>
        </div>
        <button @click="abrirModalNuevo()" class="btn-primary shadow-lg" style="box-shadow: 0 4px 14px rgba(230,0,126,0.3);">
            <i data-lucide="plus" class="w-5 h-5 inline mr-2"></i>
            Nuevo Evento
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-2xl p-5 border border-gray-100 bg-white shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background: rgba(230,0,126,0.1);">
                    <i data-lucide="calendar" class="w-6 h-6" style="color: #E6007E;"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold" style="color: #2F2F35;"><?= number_format($stats['total'] ?? 0) ?></p>
                    <p class="text-xs font-medium uppercase tracking-wider" style="color: #E6007E;">Total Eventos</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl p-5 border border-gray-100 bg-white shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background: rgba(249,176,0,0.1);">
                    <i data-lucide="users" class="w-6 h-6" style="color: #F9B000;"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold" style="color: #2F2F35;"><?= number_format($stats['total_asistentes'] ?? 0) ?></p>
                    <p class="text-xs font-medium uppercase tracking-wider" style="color: #F9B000;">Total Asistentes</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl p-5 border border-gray-100 bg-white shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background: rgba(91,42,134,0.1);">
                    <i data-lucide="clock" class="w-6 h-6" style="color: #5B2A86;"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold" style="color: #2F2F35;"><?= number_format($stats['programados'] ?? 0) ?></p>
                    <p class="text-xs font-medium uppercase tracking-wider" style="color: #5B2A86;">Programados</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl p-5 border border-gray-100 bg-white shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background: rgba(65,184,83,0.1);">
                    <i data-lucide="check-circle" class="w-6 h-6" style="color: #41B853;"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold" style="color: #2F2F35;"><?= number_format($stats['finalizados'] ?? 0) ?></p>
                    <p class="text-xs font-medium uppercase tracking-wider" style="color: #41B853;">Finalizados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Vista de Calendario y Mapa -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl p-6 border border-gray-100 bg-white shadow-sm">
            <h3 class="font-bold mb-4 flex items-center gap-2" style="color: #5B2A86;">
                <i data-lucide="map" class="w-5 h-5" style="color: #E6007E;"></i>
                Mapa de Eventos
            </h3>
            <div id="mapaEventos" style="height: 400px; border-radius: 12px; border: 2px solid #f0f0f0;"></div>
        </div>

        <div class="rounded-2xl p-6 border border-gray-100 bg-white shadow-sm">
            <h3 class="font-bold mb-4 flex items-center gap-2" style="color: #5B2A86;">
                <i data-lucide="calendar-clock" class="w-5 h-5" style="color: #F9B000;"></i>
                Próximos Eventos
            </h3>
            <div class="space-y-3 max-h-[400px] overflow-y-auto pr-1">
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
                    <div class="text-center py-8">
                        <i data-lucide="calendar-x" class="w-12 h-12 mx-auto mb-2" style="color: #d1d5db;"></i>
                        <p class="text-sm" style="color: #9CA3AF;">No hay eventos programados</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($proximos as $evento): ?>
                        <div class="flex items-start gap-3 p-4 rounded-xl transition-all cursor-pointer"
                             style="background: #FAFAFA; border-left: 3px solid #E6007E;"
                             onmouseover="this.style.background='#F3F0FF'" onmouseout="this.style.background='#FAFAFA'">
                            <div class="flex-shrink-0 w-12 text-center">
                                <p class="font-bold text-lg" style="color: #5B2A86;">
                                    <?= date('d', strtotime($evento['fecha_inicio'])) ?>
                                </p>
                                <p class="text-xs font-medium" style="color: #E6007E;">
                                    <?= date('M', strtotime($evento['fecha_inicio'])) ?>
                                </p>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold" style="color: #2F2F35;"><?= htmlspecialchars($evento['nombre']) ?></p>
                                <p class="text-xs mt-1" style="color: #6B7280;">
                                    <i data-lucide="clock" class="w-3 h-3 inline"></i>
                                    <?= date('H:i', strtotime($evento['fecha_inicio'])) ?>
                                </p>
                                <p class="text-xs" style="color: #6B7280;">
                                    <i data-lucide="map-pin" class="w-3 h-3 inline"></i>
                                    <?= htmlspecialchars($evento['ubicacion'] ?? 'Sin ubicación') ?>
                                </p>
                            </div>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full h-fit whitespace-nowrap" style="background: rgba(230,0,126,0.08); color: #E6007E;">
                                <?= ucfirst($evento['tipo']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="rounded-2xl p-5 border border-gray-100 bg-white shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <input type="text" x-model="filtros.buscar" placeholder="Buscar evento..." 
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-all"
                   style="focus-ring-color: #E6007E;">
            <select x-model="filtros.tipo" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-all">
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
            <select x-model="filtros.estado" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-all">
                <option value="">Todos los estados</option>
                <option value="programado">Programado</option>
                <option value="en-curso">En Curso</option>
                <option value="finalizado">Finalizado</option>
                <option value="cancelado">Cancelado</option>
            </select>
            <input type="date" x-model="filtros.fecha" 
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-all">
            <button @click="exportar()" class="w-full px-4 py-2.5 rounded-xl font-medium text-sm transition-all" 
                    style="border: 1.5px solid #5B2A86; color: #5B2A86; background: white;"
                    onmouseover="this.style.background='rgba(91,42,134,0.05)'" onmouseout="this.style.background='white'">
                <i data-lucide="download" class="w-4 h-4 inline mr-2"></i>
                Exportar
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%); color: white;">Evento</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%); color: white;">Tipo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%); color: white;">Fecha/Hora</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%); color: white;">Ubicación</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%); color: white;">Asistentes</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%); color: white;">Estado</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%); color: white;">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(e, idx) in eventosFiltrados" :key="e.id">
                        <tr :class="idx % 2 === 0 ? 'bg-white' : 'bg-gray-50/50'" class="hover:bg-purple-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-semibold" style="color: #2F2F35;" x-text="e.nombre"></p>
                                    <p class="text-xs" style="color: #9CA3AF;" x-text="e.responsable_nombre || 'Sin responsable'"></p>
                                    <p class="text-xs mt-1" style="color: #6B7280; max-width: 250px; white-space: normal; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" x-show="e.descripcion" x-text="e.descripcion"></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold px-3 py-1.5 rounded-full whitespace-nowrap"
                                      style="background: rgba(230,0,126,0.08); color: #E6007E;"
                                      x-text="e.tipo.charAt(0).toUpperCase() + e.tipo.slice(1)"></span>
                            </td>
                            <td class="px-6 py-4 text-sm" style="color: #4B5563;">
                                <div>
                                    <p x-text="new Date(e.fecha_inicio).toLocaleDateString('es-CO')"></p>
                                    <p class="text-xs" style="color: #9CA3AF;" x-text="new Date(e.fecha_inicio).toLocaleTimeString('es-CO', {hour:'2-digit', minute:'2-digit'})"></p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm" style="color: #4B5563;">
                                <p x-text="(e.ubicacion || 'Sin ubicación').substring(0, 30)"></p>
                                <p class="text-xs" style="color: #9CA3AF;" x-show="e.barrio" x-text="'Barrio: ' + e.barrio"></p>
                            </td>
                            <td class="px-6 py-4 text-sm" style="color: #4B5563;">
                                <span x-text="(e.asistentes_confirmados || 0) + ' / ' + (e.asistentes_esperados || 0)"></span>
                                <span class="text-xs ml-1" style="color: #9CA3AF;">conf/esp</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold px-3 py-1.5 rounded-full whitespace-nowrap"
                                      :style="{
                                          'background': e.estado === 'programado' ? 'rgba(91,42,134,0.08)' : e.estado === 'en-curso' ? 'rgba(249,176,0,0.15)' : e.estado === 'finalizado' ? 'rgba(65,184,83,0.12)' : 'rgba(239,68,68,0.1)',
                                          'color': e.estado === 'programado' ? '#5B2A86' : e.estado === 'en-curso' ? '#b8860b' : e.estado === 'finalizado' ? '#41B853' : '#dc2626'
                                      }"
                                      x-text="e.estado.charAt(0).toUpperCase() + e.estado.slice(1).replace('-', ' ')"></span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <button @click="verDetalle(e)" class="px-3 py-1.5 rounded-lg transition-colors mr-1 text-xs font-medium inline-flex items-center gap-1"
                                        style="color: #5B2A86; background: rgba(91,42,134,0.05);" onmouseover="this.style.background='rgba(91,42,134,0.1)'" onmouseout="this.style.background='rgba(91,42,134,0.05)'"
                                        title="Ver Detalles">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> Detalles
                                </button>
                                <button @click="editar(e.id)" class="px-3 py-1.5 rounded-lg transition-colors mr-1 text-xs font-medium inline-flex items-center gap-1"
                                        style="color: #F9B000; background: rgba(249,176,0,0.05);" onmouseover="this.style.background='rgba(249,176,0,0.1)'" onmouseout="this.style.background='rgba(249,176,0,0.05)'"
                                        title="Editar">
                                    <i data-lucide="edit" class="w-3.5 h-3.5"></i> Editar
                                </button>
                                <button @click="eliminar(e.id)" class="px-3 py-1.5 rounded-lg transition-colors text-xs font-medium inline-flex items-center gap-1"
                                        style="color: #E6007E; background: rgba(230,0,126,0.05);" onmouseover="this.style.background='rgba(230,0,126,0.1)'" onmouseout="this.style.background='rgba(230,0,126,0.05)'"
                                        title="Eliminar">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="eventosFiltrados.length === 0">
                        <td colspan="7" class="px-6 py-16 text-center" style="color: #9CA3AF;">
                            <i data-lucide="calendar-off" class="w-12 h-12 mx-auto mb-3" style="color: #d1d5db;"></i>
                            <p class="text-sm font-medium">No hay eventos registrados</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal QR -->
    <div x-show="modalQR" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-[9999] backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full relative z-[10000] shadow-2xl" @click.away="modalQR = false">
            <div class="p-6 text-center">
                <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center" style="background: rgba(91,42,134,0.08);">
                    <i data-lucide="qr-code" class="w-8 h-8" style="color: #5B2A86;"></i>
                </div>
                <h2 class="text-xl font-bold" style="color: #5B2A86;">Registro de Asistencia</h2>
                <p class="text-sm mt-1" style="color: #6B7280;">Escanea para registrar tu asistencia</p>
            </div>
            <div class="p-6 pt-0 text-center">
                <div id="qrcode" class="inline-block mb-4 p-4 bg-white rounded-2xl shadow-sm border border-gray-100"></div>
                <p class="text-xs mb-4 break-all select-all" style="color: #9CA3AF;" x-text="urlRegistro"></p>
                <div class="flex gap-3">
                    <button @click="descargarQR()" class="flex-1 px-4 py-2.5 rounded-xl font-medium text-sm text-white transition-all shadow-lg"
                            style="background: linear-gradient(135deg, #E6007E, #5B2A86);"
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <i data-lucide="download" class="w-4 h-4 inline mr-2"></i>
                        Descargar QR
                    </button>
                    <button @click="copiarURL()" class="flex-1 px-4 py-2.5 rounded-xl font-medium text-sm transition-all"
                            style="border: 1.5px solid #E6007E; color: #E6007E; background: white;"
                            onmouseover="this.style.background='rgba(230,0,126,0.05)'" onmouseout="this.style.background='white'">
                        <i data-lucide="copy" class="w-4 h-4 inline mr-2"></i>
                        Copiar URL
                    </button>
                </div>
                <button @click="compartirWhatsApp(eventosData.find(e => e.id === eventoIdActual))" 
                        class="mt-2 w-full px-4 py-2.5 rounded-xl font-medium text-sm transition-all"
                        style="border: 1.5px solid #25D366; color: #25D366; background: white;"
                        onmouseover="this.style.background='rgba(37,211,102,0.05)'" onmouseout="this.style.background='white'">
                    <i data-lucide="message-circle" class="w-4 h-4 inline mr-2"></i>
                    Enviar QR vía WhatsApp al Responsable
                </button>
                <button @click="modalQR = false" class="mt-3 text-xs" style="color: #9CA3AF;">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal Asistencia -->
    <div x-show="modalAsistencia" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-[9999] backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white rounded-2xl max-w-6xl w-full max-h-[90vh] overflow-y-auto relative z-[10000] shadow-2xl" @click.away="modalAsistencia = false">
            <div class="p-6 flex items-center justify-between border-b" style="border-color: #f0f0f0;">
                <h2 class="text-xl font-bold flex items-center gap-2" style="color: #5B2A86;">
                    <i data-lucide="list-checks" class="w-5 h-5" style="color: #E6007E;"></i>
                    Lista de Asistencia
                </h2>
                <div class="flex gap-2">
                    <button @click="exportarAsistenciaPDF()" class="px-4 py-2 rounded-xl text-sm font-medium transition-all flex items-center gap-1.5"
                            style="border: 1.5px solid #E6007E; color: #E6007E; background: white;"
                            onmouseover="this.style.background='rgba(230,0,126,0.05)'" onmouseout="this.style.background='white'">
                        <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                    </button>
                    <button @click="exportarAsistencia()" class="px-4 py-2 rounded-xl text-sm font-medium transition-all flex items-center gap-1.5"
                            style="border: 1.5px solid #5B2A86; color: #5B2A86; background: white;"
                            onmouseover="this.style.background='rgba(91,42,134,0.05)'" onmouseout="this.style.background='white'">
                        <i data-lucide="download" class="w-4 h-4"></i> Excel
                    </button>
                </div>
            </div>

            <div class="p-6 border-b" style="background: #FAFAFA; border-color: #f0f0f0;">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="text-center p-3 rounded-xl bg-white shadow-sm">
                        <p class="text-2xl font-bold" style="color: #E6007E;" x-text="statsAsistencia.total || 0"></p>
                        <p class="text-xs font-medium uppercase tracking-wider" style="color: #9CA3AF;">Total</p>
                    </div>
                    <div class="text-center p-3 rounded-xl bg-white shadow-sm">
                        <p class="text-2xl font-bold" style="color: #5B2A86;" x-text="statsAsistencia.masculino || 0"></p>
                        <p class="text-xs font-medium uppercase tracking-wider" style="color: #9CA3AF;">Masculino</p>
                    </div>
                    <div class="text-center p-3 rounded-xl bg-white shadow-sm">
                        <p class="text-2xl font-bold" style="color: #E6007E;" x-text="statsAsistencia.femenino || 0"></p>
                        <p class="text-xs font-medium uppercase tracking-wider" style="color: #9CA3AF;">Femenino</p>
                    </div>
                    <div class="text-center p-3 rounded-xl bg-white shadow-sm">
                        <p class="text-2xl font-bold" style="color: #41B853;" x-text="Math.round(statsAsistencia.edad_promedio || 0)"></p>
                        <p class="text-xs font-medium uppercase tracking-wider" style="color: #9CA3AF;">Edad Promedio</p>
                    </div>
                    <div class="text-center p-3 rounded-xl bg-white shadow-sm">
                        <p class="text-2xl font-bold" style="color: #F9B000;" x-text="statsAsistencia.via_qr || 0"></p>
                        <p class="text-xs font-medium uppercase tracking-wider" style="color: #9CA3AF;">Vía QR</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #5B2A86;">Nombre</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #5B2A86;">Documento</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #5B2A86;">Género</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #5B2A86;">Grupo Etario</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #5B2A86;">Ubicación</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #5B2A86;">Teléfono</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #5B2A86;">Habeas Data</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #5B2A86;">Firma</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #5B2A86;">Registro</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: #f0f0f0;">
                            <template x-for="(asistente, aidx) in asistentes" :key="asistente.id">
                                <tr :class="aidx % 2 === 0 ? 'bg-white' : 'bg-gray-50/50'" class="hover:bg-purple-50/50 transition-colors cursor-pointer" @click="verDetalleAsistente(asistente)">
                                    <td class="px-3 py-3">
                                        <div class="font-medium" style="color: #2F2F35;" x-text="asistente.nombre"></div>
                                        <div class="text-xs" style="color: #9CA3AF;" x-text="asistente.email || 'Sin email'"></div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span style="color: #4B5563;" x-text="asistente.tipo_documento"></span>
                                        <span style="color: #4B5563;" x-text="asistente.documento"></span>
                                    </td>
                                    <td class="px-3 py-3 capitalize" style="color: #4B5563;" x-text="asistente.genero"></td>
                                    <td class="px-3 py-3">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium"
                                              style="background: rgba(91,42,134,0.08); color: #5B2A86;"
                                              x-text="asistente.grupo_etareo || '-'"></span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="text-xs" style="color: #4B5563;">
                                            <div x-text="asistente.municipio"></div>
                                            <div style="color: #9CA3AF;" x-text="asistente.barrio || '-'"></div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-xs" style="color: #4B5563;" x-text="asistente.telefono || '-'"></td>
                                    <td class="px-3 py-3 text-center">
                                        <span x-show="asistente.habeas_data" style="color: #41B853;">
                                            <i data-lucide="check-circle" class="w-4 h-4 inline"></i>
                                        </span>
                                        <span x-show="!asistente.habeas_data" style="color: #E6007E;">
                                            <i data-lucide="x-circle" class="w-4 h-4 inline"></i>
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <img x-show="asistente.firma_digital"
                                             :src="asistente.firma_digital"
                                             class="h-8 w-20 object-contain rounded border" style="border-color: #f0f0f0;"
                                             alt="Firma"
                                             @error="$el.style.display='none'"
                                             @click.stop="verDetalleAsistente(asistente)"
                                             title="Ver detalle">
                                        <span x-show="!asistente.firma_digital" class="text-xs" style="color: #d1d5db;">—</span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="text-xs" style="color: #4B5563;">
                                            <div x-text="new Date(asistente.fecha_registro).toLocaleDateString('es-CO')"></div>
                                            <span class="px-2 py-0.5 rounded text-xs font-medium"
                                                  :style="asistente.metodo_registro === 'qr' ? 'background: rgba(91,42,134,0.08); color: #5B2A86;' : 'background: rgba(249,176,0,0.1); color: #b8860b;'"
                                                  x-text="asistente.metodo_registro?.toUpperCase()"></span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="asistentes.length === 0" class="text-center py-12" style="color: #9CA3AF;">
                    <i data-lucide="users" class="w-12 h-12 mx-auto mb-2" style="color: #d1d5db;"></i>
                    <p>No hay registros de asistencia aún</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Asistente -->
    <div x-show="modalDetalleAsistente" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-[9999] backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full relative z-[10000] shadow-2xl" @click.away="modalDetalleAsistente = false">
            <div class="p-6 rounded-t-2xl" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%);">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="user" class="w-5 h-5"></i>
                        Detalle del Asistente
                    </h2>
                    <button @click="modalDetalleAsistente = false" class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                        <i data-lucide="x" class="w-4 h-4 text-white"></i>
                    </button>
                </div>
            </div>
            <template x-if="asistenteDetalle">
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Nombre</p>
                        <p class="font-semibold" style="color: #2F2F35;" x-text="asistenteDetalle.nombre"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Documento</p>
                        <p style="color: #4B5563;" x-text="asistenteDetalle.tipo_documento?.toUpperCase() + ' ' + asistenteDetalle.documento"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Teléfono</p>
                        <p style="color: #4B5563;" x-text="asistenteDetalle.telefono || '—'"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Email</p>
                        <p style="color: #4B5563;" x-text="asistenteDetalle.email || '—'"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Género</p>
                        <p class="capitalize" style="color: #4B5563;" x-text="asistenteDetalle.genero"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Grupo Etario</p>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium" style="background: rgba(91,42,134,0.08); color: #5B2A86;" x-text="asistenteDetalle.grupo_etareo || '—'"></span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Departamento</p>
                        <p style="color: #4B5563;" x-text="asistenteDetalle.departamento || '—'"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Municipio</p>
                        <p style="color: #4B5563;" x-text="asistenteDetalle.municipio || '—'"></p>
                    </div>
                    <div class="col-span-2" x-show="asistenteDetalle.barrio">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Barrio</p>
                        <p style="color: #4B5563;" x-text="asistenteDetalle.barrio"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Habeas Data</p>
                        <span x-show="asistenteDetalle.habeas_data" style="color: #41B853;"><i data-lucide="check-circle" class="w-4 h-4 inline"></i> Autorizado</span>
                        <span x-show="!asistenteDetalle.habeas_data" style="color: #E6007E;"><i data-lucide="x-circle" class="w-4 h-4 inline"></i> No autorizado</span>
                    </div>
                    <div x-show="asistenteDetalle.acepta_comunicaciones">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Comunicaciones</p>
                        <span x-show="asistenteDetalle.acepta_comunicaciones" style="color: #41B853;"><i data-lucide="check-circle" class="w-4 h-4 inline"></i> Acepta</span>
                        <span x-show="!asistenteDetalle.acepta_comunicaciones" style="color: #9CA3AF;">—</span>
                    </div>
                    <div x-show="asistenteDetalle.autorizacion_imagenes !== undefined">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Autorización Imagen</p>
                        <span x-show="asistenteDetalle.autorizacion_imagenes" style="color: #41B853;"><i data-lucide="check-circle" class="w-4 h-4 inline"></i> Autorizado</span>
                        <span x-show="!asistenteDetalle.autorizacion_imagenes" style="color: #E6007E;"><i data-lucide="x-circle" class="w-4 h-4 inline"></i> No autorizado</span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Método Registro</p>
                        <span class="px-2 py-0.5 rounded text-xs font-medium"
                              :style="asistenteDetalle.metodo_registro === 'qr' ? 'background: rgba(91,42,134,0.08); color: #5B2A86;' : 'background: rgba(249,176,0,0.1); color: #b8860b;'"
                              x-text="asistenteDetalle.metodo_registro?.toUpperCase()"></span>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Fecha de Registro</p>
                        <p style="color: #4B5563;" x-text="new Date(asistenteDetalle.fecha_registro).toLocaleString('es-CO')"></p>
                    </div>
                    <div class="col-span-2" x-show="asistenteDetalle.firma_digital">
                        <p class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: #9CA3AF;">Firma Digital</p>
                        <div class="rounded-xl overflow-hidden border" style="border-color: #f0f0f0; background: #FAFAFA; max-width: 300px;">
                            <img :src="asistenteDetalle.firma_digital" alt="Firma" class="w-full h-auto p-3" @error="$el.style.display='none'">
                        </div>
                    </div>
                    <div class="col-span-2" x-show="asistenteDetalle.notas">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Notas</p>
                        <p style="color: #4B5563;" x-text="asistenteDetalle.notas"></p>
                    </div>
                </div>
                <div class="pt-4 border-t flex gap-2" style="border-color: #f0f0f0;">
                    <button @click="modalDetalleAsistente = false"
                            class="flex-1 px-4 py-2.5 rounded-xl font-medium text-sm text-white transition-all"
                            style="background: linear-gradient(135deg, #E6007E, #5B2A86);">
                        Cerrar
                    </button>
                    <button @click="exportarAsistenteIndividual(asistenteDetalle)"
                            class="px-4 py-2.5 rounded-xl font-medium text-sm transition-all flex items-center gap-1"
                            style="border: 1.5px solid #5B2A86; color: #5B2A86; background: white;"
                            onmouseover="this.style.background='rgba(91,42,134,0.05)'" onmouseout="this.style.background='white'">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i> PDF
                    </button>
                </div>
            </div>
            </template>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Evento -->
    <div x-show="modalNuevo" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-[9999] backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto relative z-[10000] shadow-2xl" @click.away="cerrarModal()">
            <div class="p-6 rounded-t-2xl flex items-center gap-3" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%);">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <i data-lucide="calendar-plus" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white" x-text="form.id ? 'Editar Evento' : 'Nuevo Evento'"></h2>
                    <p class="text-xs text-white/70">Completa los datos del evento</p>
                </div>
                <button @click="cerrarModal()" class="ml-auto w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i data-lucide="x" class="w-4 h-4 text-white"></i>
                </button>
            </div>
            <form @submit.prevent="guardar()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Nombre del Evento *</label>
                        <input type="text" x-model="form.nombre" required 
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Campaña *</label>
                        <select x-model="form.campana_id" required 
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                            <option value="">Seleccionar campaña...</option>
                            <?php foreach ($campanasUsuario as $campana): ?>
                                <option value="<?= $campana['id'] ?>"
                                    <?= $campana['id'] == $campanaId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($campana['nombre']) ?>
                                    <?= $campana['estado'] != 'activa' ? ' (' . ucfirst($campana['estado']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs mt-1 flex items-center gap-1" style="color: #9CA3AF;">
                            <i data-lucide="info" class="w-3 h-3 inline"></i>
                            Selecciona la campaña a la que pertenece este evento
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Tipo de Evento *</label>
                        <select x-model="form.tipo" required 
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
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

                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Estado *</label>
                        <select x-model="form.estado" required 
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                            <option value="programado">Programado</option>
                            <option value="en-curso">En Curso</option>
                            <option value="finalizado">Finalizado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Fecha y Hora Inicio *</label>
                        <input type="datetime-local" x-model="form.fecha_inicio" required 
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Fecha y Hora Fin</label>
                        <input type="datetime-local" x-model="form.fecha_fin" 
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Ubicación *</label>
                        <input type="text" x-model="form.ubicacion" required 
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Dirección Completa</label>
                        <textarea x-model="form.direccion" rows="2" 
                                  class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Departamento</label>
                        <select x-model="form.departamento" @change="cargarMunicipios()" 
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                            <option value="">Seleccionar departamento...</option>
                            <template x-for="dep in listas.departamentos" :key="dep">
                                <option :value="dep" x-text="dep"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Municipio</label>
                        <select x-model="municipio_raw" @change="selectMunicipio()" 
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all" :disabled="!form.departamento || loadingMunicipios">
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
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Tipo de Territorio</label>
                        <select x-model="form.tipo_territorio" @change="cargarTerritorios()" 
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all" :disabled="!form.municipio">
                            <option value="">Seleccionar tipo...</option>
                            <template x-for="tipo in listas.tipos_territorio" :key="tipo">
                                <option :value="tipo" x-text="tipo"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Territorio</label>
                        <select x-model="form.territorio" @change="cargarBarrios()" 
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all" :disabled="!form.tipo_territorio">
                            <option value="">Seleccionar territorio...</option>
                            <template x-for="terr in listas.territorios" :key="terr">
                                <option :value="terr" x-text="terr"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Barrio/Vereda</label>
                        <select x-model="form.barrio" @change="geocodificarUbicacion()" 
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all" :disabled="!form.territorio">
                            <option value="">Seleccionar barrio...</option>
                            <template x-for="barr in listas.barrios" :key="barr">
                                <option :value="barr" x-text="barr"></option>
                            </template>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">
                            Ubicación en Mapa
                            <span class="text-xs font-normal" style="color: #9CA3AF;">
                                (selecciona barrio para centrar, haz clic en el mapa para ajustar)
                            </span>
                        </label>
                        <div id="mapaModal" style="height: 250px; border-radius: 12px; border: 2px solid #f0f0f0; z-index: 1;"></div>
                        <div class="flex gap-4 mt-2 text-xs" style="color: #6B7280;">
                            <span>Lat: <strong x-text="form.latitud || '—'"></strong></span>
                            <span>Lng: <strong x-text="form.longitud || '—'"></strong></span>
                            <template x-if="form.latitud">
                                <span class="text-green-500">✅ Coordenadas capturadas</span>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Responsable *</label>
                        <select x-model="form.responsable_id" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                            <option value="">Seleccionar responsable...</option>
                            <template x-for="r in listas.responsables" :key="r.id">
                                <option :value="r.id" x-text="r.nombre + (r.telefono ? ' - ' + r.telefono : '') + (r.tipo === 'colaborador' ? ' (Líder)' : '')"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Teléfono de Contacto</label>
                        <input type="tel" x-model="form.telefono_contacto" placeholder="Ej: 3001234567"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                        <p class="text-xs mt-1" style="color: #9CA3AF;">Número al que se enviará el QR de asistencia</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Asistentes Esperados</label>
                        <input type="number" x-model="form.asistentes_esperados" min="0" 
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Presupuesto</label>
                        <input type="number" x-model="form.presupuesto" min="0" step="0.01" 
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Descripción</label>
                        <textarea x-model="form.descripcion" rows="3" 
                                  class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all"></textarea>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Notas</label>
                        <textarea x-model="form.notas" rows="2" 
                                  class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all"></textarea>
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t" style="border-color: #f0f0f0;">
                    <button type="submit" class="flex-1 px-6 py-3 rounded-xl font-medium text-sm text-white transition-all shadow-lg"
                            style="background: linear-gradient(135deg, #E6007E, #5B2A86);"
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <i data-lucide="save" class="w-5 h-5 inline mr-2"></i>
                        <span x-text="form.id ? 'Actualizar Evento' : 'Guardar Evento'"></span>
                    </button>
                    <button type="button" @click="cerrarModal()" 
                            class="px-6 py-3 rounded-xl font-medium text-sm transition-all"
                            style="border: 1.5px solid #E6007E; color: #E6007E; background: white;"
                            onmouseover="this.style.background='rgba(230,0,126,0.05)'" onmouseout="this.style.background='white'">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detalles -->
    <div x-show="modalDetalle" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-[9999] backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full relative z-[10000] shadow-2xl" @click.away="modalDetalle = false">
            <div class="p-6 rounded-t-2xl" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%);">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="info" class="w-5 h-5"></i>
                        Detalles del Evento
                    </h2>
                    <button @click="modalDetalle = false" class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors">
                        <i data-lucide="x" class="w-4 h-4 text-white"></i>
                    </button>
                </div>
            </div>
            <template x-if="detalleEvento">
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Nombre</p>
                        <p class="font-semibold" style="color: #2F2F35;" x-text="detalleEvento.nombre"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Tipo</p>
                        <p class="capitalize" x-text="detalleEvento.tipo"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Estado</p>
                        <span class="text-xs font-semibold px-3 py-1.5 rounded-full"
                              :style="{
                                  'background': detalleEvento.estado === 'programado' ? 'rgba(91,42,134,0.08)' : detalleEvento.estado === 'finalizado' ? 'rgba(65,184,83,0.12)' : 'rgba(239,68,68,0.1)',
                                  'color': detalleEvento.estado === 'programado' ? '#5B2A86' : detalleEvento.estado === 'finalizado' ? '#41B853' : '#dc2626'
                              }"
                              x-text="detalleEvento.estado.charAt(0).toUpperCase() + detalleEvento.estado.slice(1).replace('-', ' ')"></span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Fecha Inicio</p>
                        <p x-text="new Date(detalleEvento.fecha_inicio).toLocaleString('es-CO')"></p>
                    </div>
                    <div x-show="detalleEvento.fecha_fin">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Fecha Fin</p>
                        <p x-text="new Date(detalleEvento.fecha_fin).toLocaleString('es-CO')"></p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Ubicación</p>
                        <p x-text="detalleEvento.ubicacion || '—'"></p>
                    </div>
                    <div class="col-span-2" x-show="detalleEvento.direccion">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Dirección</p>
                        <p x-text="detalleEvento.direccion"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Departamento</p>
                        <p x-text="detalleEvento.departamento || '—'"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Municipio</p>
                        <p x-text="detalleEvento.municipio || '—'"></p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Responsable</p>
                        <p x-text="detalleEvento.responsable_nombre || '—'"></p>
                    </div>
                    <div class="col-span-2" x-show="detalleEvento.telefono_contacto">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Teléfono Contacto</p>
                        <p x-text="detalleEvento.telefono_contacto"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Asistentes</p>
                        <p x-text="(detalleEvento.asistentes_confirmados || 0) + ' / ' + (detalleEvento.asistentes_esperados || 0) + ' conf/esp'"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Coordenadas</p>
                        <p x-show="detalleEvento.latitud" x-text="detalleEvento.latitud + ', ' + detalleEvento.longitud"></p>
                        <p x-show="!detalleEvento.latitud" style="color: #9CA3AF;">—</p>
                    </div>
                    <div class="col-span-2" x-show="detalleEvento.descripcion">
                        <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9CA3AF;">Descripción</p>
                        <p class="text-sm" style="color: #4B5563;" x-text="detalleEvento.descripcion"></p>
                    </div>
                </div>
                <div class="space-y-3 pt-4 border-t" style="border-color: #f0f0f0;">
                    <button @click="modalDetalle = false; editar(detalleEvento.id)"
                            class="w-full px-4 py-2.5 rounded-xl font-medium text-sm text-white transition-all shadow-sm"
                            style="background: linear-gradient(135deg, #E6007E, #5B2A86);">
                        <i data-lucide="edit" class="w-4 h-4 inline mr-2"></i> Editar Evento
                    </button>
                    <div class="grid grid-cols-2 gap-2">
                        <button @click="modalDetalle = false; verQR(detalleEvento.id)"
                                class="px-3 py-2.5 rounded-xl font-medium text-xs transition-all flex items-center justify-center gap-1.5"
                                style="background: rgba(91,42,134,0.08); color: #5B2A86;"
                                onmouseover="this.style.background='rgba(91,42,134,0.15)'" onmouseout="this.style.background='rgba(91,42,134,0.08)'">
                            <i data-lucide="qr-code" class="w-3.5 h-3.5"></i> Código QR
                        </button>
                        <button @click="compartirWhatsApp(detalleEvento); modalDetalle = false"
                                class="px-3 py-2.5 rounded-xl font-medium text-xs transition-all flex items-center justify-center gap-1.5"
                                style="background: rgba(37,211,102,0.1); color: #1da851;"
                                onmouseover="this.style.background='rgba(37,211,102,0.2)'" onmouseout="this.style.background='rgba(37,211,102,0.1)'">
                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> WhatsApp
                        </button>
                        <button @click="modalDetalle = false; verAsistencia(detalleEvento.id)"
                                class="px-3 py-2.5 rounded-xl font-medium text-xs transition-all flex items-center justify-center gap-1.5"
                                style="background: rgba(230,0,126,0.08); color: #E6007E;"
                                onmouseover="this.style.background='rgba(230,0,126,0.15)'" onmouseout="this.style.background='rgba(230,0,126,0.08)'">
                            <i data-lucide="list-checks" class="w-3.5 h-3.5"></i> Asistentes
                        </button>
                        <button @click="modalDetalle = false; verMapaSolo(detalleEvento)"
                                class="px-3 py-2.5 rounded-xl font-medium text-xs transition-all flex items-center justify-center gap-1.5"
                                style="background: rgba(249,176,0,0.1); color: #b8860b;"
                                onmouseover="this.style.background='rgba(249,176,0,0.2)'" onmouseout="this.style.background='rgba(249,176,0,0.1)'">
                            <i data-lucide="map" class="w-3.5 h-3.5"></i> Ver Mapa
                        </button>
                    </div>
                    <a :href="'index.php?page=dashboard_asistencia&evento_id=' + detalleEvento.id"
                       class="block w-full px-4 py-2.5 rounded-xl font-medium text-xs transition-all text-center"
                       style="background: rgba(91,42,134,0.04); color: #5B2A86; border: 1px solid rgba(91,42,134,0.15);"
                       onmouseover="this.style.background='rgba(91,42,134,0.08)'" onmouseout="this.style.background='rgba(91,42,134,0.04)'">
                        <i data-lucide="bar-chart-3" class="w-3.5 h-3.5 inline mr-1"></i> Dashboard de Análisis
                    </a>
                </div>
            </div>
            </template>
        </div>
    </div>

    <!-- Modal Mapa Solo -->
    <div x-show="modalMapa" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-[9999] backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white rounded-2xl max-w-3xl w-full relative z-[10000] shadow-2xl" @click.away="modalMapa = false">
            <div class="p-4 flex items-center justify-between border-b" style="border-color: #f0f0f0;">
                <h3 class="font-bold flex items-center gap-2" style="color: #5B2A86;">
                    <i data-lucide="map" class="w-5 h-5" style="color: #E6007E;"></i>
                    <span x-text="eventoSolo?.nombre || 'Mapa'"></span>
                </h3>
                <button @click="modalMapa = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="p-4">
                <div id="mapaSolo" style="height: 450px; border-radius: 12px;"></div>
                <div class="flex justify-between mt-3 text-xs" style="color: #6B7280;">
                    <span x-text="eventoSolo?.ubicacion || ''"></span>
                    <span x-show="eventoSolo?.latitud" x-text="'📍 ' + (eventoSolo?.latitud ?? '') + ', ' + (eventoSolo?.longitud ?? '')"></span>
                </div>
            </div>
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
            modalDetalleAsistente: false,
            asistenteDetalle: null,
            mapa: null,
            loading: false,
            eventosData: <?= $eventosJson ?>,

            filtros: {
                buscar: '',
                tipo: '',
                estado: '',
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
                notas: '',
                responsable_id: '',
                telefono_contacto: '',
                latitud: '',
                longitud: ''
            },
            municipio_raw: '',
            listas: {
                departamentos: <?= json_encode($departamentosSSR) ?>,
                municipios: [],
                tipos_territorio: [],
                territorios: [],
                barrios: [],
                responsables: []
            },
            mapaModal: null,
            markerModal: null,
            modalDetalle: false,
            detalleEvento: null,
            modalMapa: false,
            eventoSolo: null,

            get eventosFiltrados() {
                return this.eventosData.filter(e => {
                    if (this.filtros.buscar && !e.nombre.toLowerCase().includes(this.filtros.buscar.toLowerCase()))
                        return false;
                    if (this.filtros.tipo && e.tipo !== this.filtros.tipo)
                        return false;
                    if (this.filtros.estado && e.estado !== this.filtros.estado)
                        return false;
                    if (this.filtros.fecha) {
                        const dateEv = e.fecha_inicio.substring(0, 10);
                        if (dateEv !== this.filtros.fecha)
                            return false;
                    }
                    return true;
                });
            },

            abrirModalNuevo() {
                this.modalNuevo = true;
                this.$nextTick(() => this.initMapaModal());
            },

            async init() {
                if (this.listas.departamentos.length <= 1) {
                    await this.cargarDepartamentos();
                }
                await this.cargarResponsables();
                this.initMapaPrincipal();
                
                setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 100);
                this.$watch('filtros', () => {
                    this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                }, { deep: true });
            },

            async cargarResponsables() {
                try {
                    const res = await fetch('/aratio/<?= $apiBase ?>/eventos.php?action=listar_responsables');
                    const json = await res.json();
                    if (json.success) this.listas.responsables = json.data;
                } catch(e) { console.error('Error cargando responsables:', e); }
            },

            initMapaPrincipal() {
                setTimeout(() => {
                    this.mapa = L.map('mapaEventos').setView([4.570868, -74.297333], 6);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(this.mapa);

                    const markers = [];
                    <?php foreach ($eventos as $ev): ?>
                        <?php if ($ev['latitud'] && $ev['longitud']): ?>
                            const marker<?= $ev['id'] ?> = L.marker([<?= $ev['latitud'] ?>, <?= $ev['longitud'] ?>])
                                .addTo(this.mapa)
                                .bindPopup('<strong><?= addslashes($ev['nombre']) ?></strong><br><?= addslashes($ev['ubicacion']) ?>');
                            markers.push([<?= $ev['latitud'] ?>, <?= $ev['longitud'] ?>]);
                        <?php endif; ?>
                    <?php endforeach; ?>

                    if (markers.length > 0) {
                        this.mapa.fitBounds(markers);
                    }
                }, 100);
            },

            verQR(id) {
                this.eventoIdActual = id;
                this.urlRegistro = window.location.origin + '/aratio/mod_eventos/pages/qr_registro.php?evento=' + id;
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
                        document.getElementById('qrcode').innerHTML = '<p class="text-red-600">Librería QRCode no cargada</p>';
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
                    const response = await fetch(`/aratio/<?= $apiBase ?>/asistencia.php?evento_id=${id}`);
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
                if (this.eventoIdActual) {
                    window.open('/aratio/mod_eventos/api/exportar_asistencia.php?evento_id=' + this.eventoIdActual + '&formato=xlsx', '_blank');
                }
            },

            exportarAsistenciaPDF() {
                if (this.eventoIdActual) {
                    window.open('/aratio/mod_eventos/api/exportar_asistencia.php?evento_id=' + this.eventoIdActual + '&formato=pdf', '_blank');
                }
            },

            exportarAsistenteIndividual(a) {
                window.open('/aratio/mod_eventos/api/exportar_asistencia.php?evento_id=' + this.eventoIdActual + '&formato=pdf&asistente_id=' + a.id, '_blank');
            },

            verDetalleAsistente(a) {
                this.asistenteDetalle = a;
                this.modalDetalleAsistente = true;
                this.$nextTick(() => lucide.createIcons());
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
        async geocodificarUbicacion() {
                const q = [this.form.barrio, this.form.territorio, this.form.municipio, this.form.departamento, 'Colombia'].filter(Boolean).join(', ');
                if (!q || q === 'Colombia') return;
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=1`);
                    const data = await res.json();
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        this.form.latitud = lat;
                        this.form.longitud = lng;
                        if (this.mapaModal) {
                            this.mapaModal.setView([lat, lng], 15);
                            if (this.markerModal) this.mapaModal.removeLayer(this.markerModal);
                            this.markerModal = L.marker([lat, lng], { draggable: true }).addTo(this.mapaModal);
                            this.markerModal.on('dragend', (e) => {
                                const pos = e.target.getLatLng();
                                this.form.latitud = pos.lat;
                                this.form.longitud = pos.lng;
                            });
                        }
                    }
                } catch(e) { console.error('Geocoding error:', e); }
            },

            initMapaModal() {
                this.$nextTick(() => {
                    const container = document.getElementById('mapaModal');
                    if (!container) return;
                    if (this.mapaModal) { this.mapaModal.invalidateSize(); return; }
                    this.mapaModal = L.map('mapaModal').setView([3.4516, -76.5320], 11);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap'
                    }).addTo(this.mapaModal);

                    if (this.form.latitud && this.form.longitud) {
                        const lat = parseFloat(this.form.latitud);
                        const lng = parseFloat(this.form.longitud);
                        this.mapaModal.setView([lat, lng], 15);
                        this.markerModal = L.marker([lat, lng], { draggable: true }).addTo(this.mapaModal);
                        this.markerModal.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.form.latitud = pos.lat;
                            this.form.longitud = pos.lng;
                        });
                    }

                    this.mapaModal.on('click', (e) => {
                        if (this.markerModal) this.mapaModal.removeLayer(this.markerModal);
                        this.markerModal = L.marker([e.latlng.lat, e.latlng.lng], { draggable: true }).addTo(this.mapaModal);
                        this.form.latitud = e.latlng.lat;
                        this.form.longitud = e.latlng.lng;
                        this.markerModal.on('dragend', (ev) => {
                            const pos = ev.target.getLatLng();
                            this.form.latitud = pos.lat;
                            this.form.longitud = pos.lng;
                        });
                    });

                    setTimeout(() => this.mapaModal.invalidateSize(), 300);
                });
            },

            async guardar() {
                this.loading = true;
                const payload = { ...this.form };
                if (this.form.id) payload._method = 'PUT';

                try {
                    const response = await fetch('/aratio/<?= $apiBase ?>/eventos.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
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
                    const response = await fetch('/aratio/<?= $apiBase ?>/eventos.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ id, _method: 'DELETE' })
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
                if (this.mapaModal) {
                    this.mapaModal.remove();
                    this.mapaModal = null;
                    this.markerModal = null;
                }
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
                    notas: '',
                    responsable_id: '',
                    telefono_contacto: '',
                    latitud: '',
                    longitud: ''
                };
            },

            async editar(id) {
                try {
                    const response = await fetch(`/aratio/<?= $apiBase ?>/eventos.php?id=${id}`);
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
                            notas: evento.notas || '',
                            responsable_id: evento.responsable_id || '',
                            telefono_contacto: evento.telefono_contacto || '',
                            latitud: evento.latitud || '',
                            longitud: evento.longitud || ''
                        };

                        const h = { 'X-Requested-With': 'XMLHttpRequest' };
                        const fetchJSON = (url) => fetch(url, { headers: h }).then(r => r.json()).catch(() => ({ success: false, data: [] }));
                        const base = '/aratio/api/territorios.php';

                        if (this.form.departamento) {
                            await this.cargarMunicipios();
                            if (this.form.municipio) {
                                const munMatch = this.listas.municipios.find(m => m.municipio === this.form.municipio);
                                if (munMatch) this.municipio_raw = JSON.stringify(munMatch);
                                await this.cargarTiposTerritorio();
                                if (this.form.tipo_territorio) {
                                    await this.cargarTerritorios();
                                    if (this.form.territorio) {
                                        await this.cargarBarrios();
                                    }
                                }
                            }
                        }

                        this.form.municipio = evento.municipio || '';
                        this.form.tipo_territorio = evento.tipo_territorio || '';
                        this.form.territorio = evento.territorio || '';
                        this.form.barrio = evento.barrio || '';

                        this.modalNuevo = true;
                        this.$nextTick(() => this.initMapaModal());
                    } else {
                        alert('Error al cargar el evento');
                    }
                } catch (error) {
                    alert('Error de conexión: ' + error.message);
                }
            },

            verDetalle(e) {
                this.detalleEvento = e;
                this.modalDetalle = true;
                this.$nextTick(() => lucide.createIcons());
            },

            verMapaSolo(e) {
                this.eventoSolo = e;
                this.modalMapa = true;
                this.$nextTick(() => {
                    setTimeout(() => {
                        const container = document.getElementById('mapaSolo');
                        if (!container) return;
                        const mapa = L.map('mapaSolo').setView(
                            (e.latitud && e.longitud) ? [e.latitud, e.longitud] : [3.4516, -76.5320],
                            (e.latitud && e.longitud) ? 15 : 11
                        );
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap'
                        }).addTo(mapa);
                        if (e.latitud && e.longitud) {
                            L.marker([e.latitud, e.longitud]).addTo(mapa)
                                .bindPopup('<strong>' + e.nombre + '</strong><br>' + (e.ubicacion || ''))
                                .openPopup();
                        }
                        setTimeout(() => mapa.invalidateSize(), 300);
                    }, 200);
                });
            },

            compartirWhatsApp(e) {
                const evento = e.id ? e : this.eventosData.find(ev => ev.id === this.eventoIdActual);
                if (!evento) { alert('Evento no encontrado'); return; }
                const tel = evento.telefono_contacto || evento.responsable_telefono || '';
                if (!tel) {
                    alert('No hay teléfono de contacto para este evento. Agrega uno en la edición del evento.');
                    return;
                }
                const url = window.location.origin + '/aratio/mod_eventos/pages/qr_registro.php?evento=' + evento.id;
                const msg = encodeURIComponent('Planilla de asistencia: ' + evento.nombre + ' ' + url);
                const numero = tel.replace(/[^0-9]/g, '');
                window.open('https://wa.me/57' + numero + '?text=' + msg, '_blank');
            },

            exportar() {
                alert('Exportar eventos a Excel');
            }
        }
    }

    lucide.createIcons();
</script>
