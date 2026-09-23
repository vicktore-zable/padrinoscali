<?php
$db = getDB();
$stmt = $db->prepare("
    SELECT e.id, e.nombre, e.fecha_inicio, e.tipo, e.ubicacion,
           (SELECT COUNT(*) FROM asistencia_eventos WHERE evento_id = e.id) as total_asistentes
    FROM eventos e
    WHERE e.campana_id = ?
    ORDER BY e.fecha_inicio DESC
");
$stmt->execute([$campanaId]);
$eventosConAsistencia = $stmt->fetchAll();
$apiBase = 'mod_eventos/api';
?>
<div class="space-y-6" x-data="asistenciaData()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold" style="color: #5B2A86;">Asistencia</h1>
            <p class="text-gray-600 mt-2">Registros de asistencia por evento</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%); color: white;">Evento</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%); color: white;">Fecha</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%); color: white;">Tipo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%); color: white;">Asistentes</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: linear-gradient(135deg, #5B2A86 0%, #E6007E 100%); color: white;">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($eventosConAsistencia)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center" style="color: #9CA3AF;">
                            <i data-lucide="users" class="w-12 h-12 mx-auto mb-3" style="color: #d1d5db;"></i>
                            <p class="text-sm font-medium">No hay eventos con asistencia registrada</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($eventosConAsistencia as $ev): ?>
                    <tr class="hover:bg-purple-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-semibold" style="color: #2F2F35;"><?= htmlspecialchars($ev['nombre']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm" style="color: #4B5563;">
                            <?= date('d/m/Y H:i', strtotime($ev['fecha_inicio'])) ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold px-3 py-1.5 rounded-full" style="background: rgba(230,0,126,0.08); color: #E6007E;">
                                <?= ucfirst($ev['tipo']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold" style="color: #5B2A86;"><?= $ev['total_asistentes'] ?></span>
                            <span class="text-xs" style="color: #9CA3AF;"> asistentes</span>
                        </td>
                        <td class="px-6 py-4">
                            <button @click="verAsistentes(<?= $ev['id'] ?>)" 
                                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
                                    style="background: rgba(91,42,134,0.08); color: #5B2A86;"
                                    onmouseover="this.style.background='rgba(91,42,134,0.15)'" onmouseout="this.style.background='rgba(91,42,134,0.08)'">
                                <i data-lucide="eye" class="w-3 h-3 inline mr-1"></i>
                                Ver Asistentes
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Asistentes -->
    <div x-show="modalAsistentes" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-[9999] backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[80vh] overflow-y-auto relative z-[10000] shadow-2xl" @click.away="modalAsistentes = false">
            <div class="p-6 flex items-center justify-between border-b" style="border-color: #f0f0f0;">
                <h2 class="text-xl font-bold" style="color: #5B2A86;">
                    <i data-lucide="users" class="w-5 h-5 inline mr-2" style="color: #E6007E;"></i>
                    Asistentes
                </h2>
                <button @click="modalAsistentes = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase" style="color: #5B2A86;">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase" style="color: #5B2A86;">Documento</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase" style="color: #5B2A86;">Género</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase" style="color: #5B2A86;">Municipio</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase" style="color: #5B2A86;">Registro</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: #f0f0f0;">
                            <template x-for="a in asistentesLista" :key="a.id">
                                <tr class="hover:bg-purple-50/50">
                                    <td class="px-4 py-3" x-text="a.nombre"></td>
                                    <td class="px-4 py-3" x-text="a.tipo_documento + ' ' + a.documento"></td>
                                    <td class="px-4 py-3 capitalize" x-text="a.genero"></td>
                                    <td class="px-4 py-3" x-text="a.municipio"></td>
                                    <td class="px-4 py-3 text-xs" x-text="new Date(a.fecha_registro).toLocaleDateString('es-CO')"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="asistentesLista.length === 0" class="text-center py-8" style="color: #9CA3AF;">
                    <p>No hay asistentes registrados</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function asistenciaData() {
        return {
            modalAsistentes: false,
            asistentesLista: [],
            async verAsistentes(id) {
                this.modalAsistentes = true;
                this.asistentesLista = [];
                try {
                    const res = await fetch(`/aratio/<?= $apiBase ?>/asistencia.php?evento_id=${id}`);
                    const result = await res.json();
                    if (result.success) this.asistentesLista = result.data;
                } catch(e) { console.error(e); }
                this.$nextTick(() => lucide.createIcons());
            }
        }
    }
    lucide.createIcons();
</script>
