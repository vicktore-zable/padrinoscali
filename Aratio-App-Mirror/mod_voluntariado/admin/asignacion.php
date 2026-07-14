<?php
$db = getDB();
$user = getSessionUser();

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignar'])) {
    $voluntario_id = (int)$_POST['voluntario_id'];
    $padrino_documento = trim($_POST['padrino_documento']);

    if (empty($padrino_documento)) {
        $error = 'Selecciona un padrino.';
    } else {
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("UPDATE colaboradores SET lider_directo = ?, asignado_por = ? WHERE id = ? AND perfil = 'Voluntario'");
            $stmt->execute([$padrino_documento, $user['id'], $voluntario_id]);

            // Registrar en actividad_colaborador
            $stmt2 = $db->prepare("
                INSERT INTO actividad_colaborador (colaborador_id, tipo, descripcion, metadata_json, creado_por, creado_en)
                SELECT id, 'voluntario_asignado', 'Voluntario asignado a padrino', 
                       JSON_OBJECT('padrino_documento', ?, 'asignado_por', ?), ?, NOW()
                FROM colaboradores WHERE id = ?
            ");
            $stmt2->execute([$padrino_documento, $user['id'], $user['id'], $voluntario_id]);

            $db->commit();
            $mensaje = 'Voluntario asignado exitosamente.';
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Error al asignar: ' . $e->getMessage();
        }
    }
}

$filtro_comuna = $_GET['comuna'] ?? '';
$filtro_interes = $_GET['interes'] ?? '';

$where = "WHERE c.perfil = 'Voluntario' AND c.lider_directo IS NULL";
$params = [];

if ($filtro_comuna) {
    $where .= " AND c.territorio = ?";
    $params[] = $filtro_comuna;
}

// Voluntarios pendientes
$stmt = $db->prepare("
    SELECT c.id, c.nombres, c.documento, c.telefono, c.territorio, 
           c.voluntario_fase, c.voluntario_intereses, c.created_at
    FROM colaboradores c
    $where
    ORDER BY c.created_at DESC
    LIMIT 100
");
$stmt->execute($params);
$pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Padrinos disponibles (líderes sociales con territorio)
$stmtPad = $db->prepare("
    SELECT c.documento, c.nombres, c.apellidos, c.territorio, c.telefono,
           (SELECT COUNT(*) FROM colaboradores sub WHERE sub.lider_directo = c.documento AND sub.perfil = 'Voluntario') as total_voluntarios
    FROM colaboradores c
    WHERE c.perfil IN ('Lider Social', 'Lider Comunitario', 'Lider Juvenil', 'Lider Poblacional')
    ORDER BY c.nombres ASC
");
$stmtPad->execute();
$padrinos = $stmtPad->fetchAll(PDO::FETCH_ASSOC);

// Agrupar padrinos por territorio
$padrinos_por_comuna = [];
foreach ($padrinos as $p) {
    $com = $p['territorio'] ?? 'Sin comuna';
    $padrinos_por_comuna[$com][] = $p;
}

// Comunas para filtro
$comunas = [];
for ($i = 1; $i <= 22; $i++) $comunas[] = 'Comuna ' . $i;
?>
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Asignación de Voluntarios</h1>
            <p class="text-sm text-gray-500 mt-1">Asigna manualmente voluntarios a padrinos de su misma comuna.</p>
        </div>
        <a href="?page=voluntario_listado" class="text-sm text-primary hover:underline">Ver listado completo &rarr;</a>
    </div>

    <?php if ($mensaje): ?>
    <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        <?= htmlspecialchars($mensaje) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5"></i>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <input type="hidden" name="page" value="voluntario_asignacion">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Comuna</label>
                <select name="comuna" class="px-4 py-2 rounded-xl border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">Todas</option>
                    <?php foreach ($comunas as $c): ?>
                    <option value="<?= $c ?>" <?= $filtro_comuna === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-slate-800 transition-all">
                Filtrar
            </button>
            <?php if ($filtro_comuna): ?>
            <a href="?page=voluntario_asignacion" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Limpiar filtro</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Pendientes -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-gray-900">
                Voluntarios Pendientes
                <span class="ml-2 px-2 py-0.5 text-xs bg-amber-100 text-amber-700 rounded-full"><?= count($pendientes) ?></span>
            </h2>
        </div>
        <?php if (empty($pendientes)): ?>
        <div class="p-10 text-center text-gray-400">
            <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
            <p>No hay voluntarios pendientes de asignación.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Voluntario</th>
                        <th class="px-6 py-4">Documento</th>
                        <th class="px-6 py-4">Comuna</th>
                        <th class="px-6 py-4">Intereses</th>
                        <th class="px-6 py-4">Registro</th>
                        <th class="px-6 py-4">Asignar Padrino</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($pendientes as $v):
                        $intereses = json_decode($v['voluntario_intereses'] ?? '[]', true) ?: [];
                        $intereses_labels = [
                            'brigadas' => 'Brigadas', 'talleres' => 'Talleres',
                            'logistica' => 'Logística', 'formacion' => 'Formación',
                            'ambiental' => 'Ambiental'
                        ];
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900"><?= htmlspecialchars($v['nombres']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($v['documento']) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium"><?= htmlspecialchars($v['territorio']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                <?php foreach ($intereses as $int): ?>
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs"><?= $intereses_labels[$int] ?? $int ?></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-xs"><?= date('d/m/Y', strtotime($v['created_at'])) ?></td>
                        <td class="px-6 py-4">
                            <form method="POST" class="flex gap-2 items-center" onsubmit="return confirm('¿Asignar este voluntario al padrino seleccionado?')">
                                <input type="hidden" name="voluntario_id" value="<?= $v['id'] ?>">
                                <select name="padrino_documento" required class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs outline-none">
                                    <option value="">Seleccionar...</option>
                                    <?php
                                    $comuna_vol = $v['territorio'];
                                    $padrinos_comuna = $padrinos_por_comuna[$comuna_vol] ?? [];
                                    // Mostrar padrinos de la misma comuna primero, luego otros
                                    foreach ($padrinos as $p):
                                        $es_misma = $p['territorio'] === $comuna_vol;
                                    ?>
                                    <option value="<?= $p['documento'] ?>" <?= $es_misma ? 'class="font-semibold"' : '' ?>>
                                        <?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?>
                                        (<?= htmlspecialchars($p['territorio'] ?? 'Sin comuna') ?>)
                                        — <?= $p['total_voluntarios'] ?> vol.
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="asignar" class="px-4 py-1.5 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition-all whitespace-nowrap">
                                    Asignar
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>lucide.createIcons();</script>
