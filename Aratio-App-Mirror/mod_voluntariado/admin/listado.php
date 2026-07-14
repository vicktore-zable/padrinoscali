<?php
$db = getDB();

$filtro_comuna = $_GET['comuna'] ?? '';
$filtro_fase = $_GET['fase'] ?? '';
$filtro_padrino = $_GET['padrino'] ?? '';

$where = "WHERE c.perfil = 'Voluntario'";
$params = [];

if ($filtro_comuna) {
    $where .= " AND c.territorio = ?";
    $params[] = $filtro_comuna;
}
if ($filtro_fase) {
    $where .= " AND c.voluntario_fase = ?";
    $params[] = $filtro_fase;
}
if ($filtro_padrino) {
    $where .= " AND (c.lider_directo LIKE ? OR CONCAT(c.nombres, ' ', c.apellidos) LIKE ?)";
    $params[] = "%$filtro_padrino%";
}

$stmt = $db->prepare("
    SELECT c.id, c.nombres, c.documento, c.telefono, c.territorio,
           c.voluntario_fase, c.voluntario_intereses, c.voluntario_disponibilidad,
           c.lider_directo, c.asignado_por, c.created_at,
           CONCAT(p.nombres, ' ', p.apellidos) as padrino_nombre,
           p.telefono as padrino_telefono
    FROM colaboradores c
    LEFT JOIN colaboradores p ON p.documento = c.lider_directo
    $where
    ORDER BY c.voluntario_fase ASC, c.created_at DESC
");
$stmt->execute($params);
$voluntarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($voluntarios);
$por_fase = [];
foreach ($voluntarios as $v) {
    $f = $v['voluntario_fase'] ?? 'asignado';
    $por_fase[$f] = ($por_fase[$f] ?? 0) + 1;
}

$comunas = [];
for ($i = 1; $i <= 22; $i++) $comunas[] = 'Comuna ' . $i;

$fases = ['asignado','contactado','activado','comprometido','movilizado'];
$fases_labels = [
    'asignado' => 'Asignado',
    'contactado' => 'Contactado',
    'activado' => 'Activado',
    'comprometido' => 'Comprometido',
    'movilizado' => 'Movilizado'
];
$fases_colors = [
    'asignado' => 'bg-gray-100 text-gray-600',
    'contactado' => 'bg-blue-50 text-blue-700',
    'activado' => 'bg-amber-50 text-amber-700',
    'comprometido' => 'bg-green-50 text-green-700',
    'movilizado' => 'bg-purple-50 text-purple-700'
];
?>
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Listado de Voluntarios</h1>
            <p class="text-sm text-gray-500 mt-1"><?= $total ?> voluntarios registrados.</p>
        </div>
        <a href="?page=voluntario_asignacion" class="text-sm text-primary hover:underline">&larr; Ir a asignación</a>
    </div>

    <!-- Stats rápidas -->
    <div class="grid grid-cols-5 gap-4">
        <?php foreach ($fases as $f): ?>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 text-center">
            <p class="text-2xl font-bold text-gray-900"><?= $por_fase[$f] ?? 0 ?></p>
            <p class="text-xs text-gray-500 mt-1"><?= $fases_labels[$f] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <input type="hidden" name="page" value="voluntario_listado">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Comuna</label>
                <select name="comuna" class="px-4 py-2 rounded-xl border border-gray-200 text-sm outline-none">
                    <option value="">Todas</option>
                    <?php foreach ($comunas as $c): ?>
                    <option value="<?= $c ?>" <?= $filtro_comuna === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Fase</label>
                <select name="fase" class="px-4 py-2 rounded-xl border border-gray-200 text-sm outline-none">
                    <option value="">Todas</option>
                    <?php foreach ($fases as $f): ?>
                    <option value="<?= $f ?>" <?= $filtro_fase === $f ? 'selected' : '' ?>><?= $fases_labels[$f] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-slate-800 transition-all">Filtrar</button>
            <?php if ($filtro_comuna || $filtro_fase): ?>
            <a href="?page=voluntario_listado" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Voluntario</th>
                        <th class="px-6 py-4">Contacto</th>
                        <th class="px-6 py-4">Comuna</th>
                        <th class="px-6 py-4">Fase</th>
                        <th class="px-6 py-4">Padrino</th>
                        <th class="px-6 py-4">Registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($voluntarios)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">No se encontraron voluntarios.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($voluntarios as $v): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900"><?= htmlspecialchars($v['nombres']) ?></span>
                            <span class="block text-xs text-gray-400"><?= htmlspecialchars($v['documento']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($v['telefono']) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium"><?= htmlspecialchars($v['territorio']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-medium <?= $fases_colors[$v['voluntario_fase']] ?? 'bg-gray-100 text-gray-600' ?>">
                                <?= $fases_labels[$v['voluntario_fase']] ?? $v['voluntario_fase'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($v['padrino_nombre']): ?>
                            <span class="text-gray-900"><?= htmlspecialchars($v['padrino_nombre']) ?></span>
                            <?php else: ?>
                            <span class="text-gray-400 italic">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-xs"><?= date('d/m/Y', strtotime($v['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>lucide.createIcons();</script>
