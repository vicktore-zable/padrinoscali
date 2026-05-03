<?php
use App\Utils\Helpers;
use App\Utils\Security;
?>

<!-- Actions Bar -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">

    <!-- Search -->
    <div class="w-full md:w-96">
        <form action="/colaboradores/search" method="GET">
            <div class="relative">
                <input type="text"
                       name="q"
                       value="<?= htmlspecialchars($filtros['search'] ?? '') ?>"
                       placeholder="Buscar por nombre, documento, email..."
                       class="form-input pl-10 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </form>
    </div>

    <!-- Actions -->
    <div class="flex gap-2">
        <?php if (has_permission($user['tipo_usuario'] ?? null, 'colaboradores', 'create')): ?>
        <a href="/colaboradores/create<?= !empty($filtros['campana_id']) ? '?campana_id=' . $filtros['campana_id'] : '' ?>" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Colaborador
        </a>
        <?php endif; ?>

        <button onclick="showImportModal()" class="btn btn-secondary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            Importar
        </button>

        <a href="/colaboradores/export" class="btn btn-outline">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Exportar
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-6" x-data="{ showFilters: false }">
    <div class="flex items-center justify-between cursor-pointer" @click="showFilters = !showFilters">
        <h3 class="text-lg font-semibold text-gray-900">Filtros</h3>
        <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{ 'rotate-180': showFilters }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <form action="/colaboradores" method="GET" x-show="showFilters" x-transition class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <?php if (!empty($filtros['campana_id'])): ?>
            <input type="hidden" name="campana_id" value="<?= htmlspecialchars($filtros['campana_id']) ?>">
        <?php endif; ?>
        <?php if (!empty($filtros['campana_nombre'])): ?>
            <input type="hidden" name="campana_nombre" value="<?= htmlspecialchars($filtros['campana_nombre']) ?>">
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label">Perfil</label>
            <select name="perfil" class="form-input">
                <option value="">Todos</option>
                <?php foreach ($perfiles as $key => $label): ?>
                <option value="<?= $key ?>" <?= ($filtros['perfil'] ?? '') === $key ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-input">
                <option value="">Todos</option>
                <option value="Nuevo" <?= ($filtros['estado'] ?? '') === 'Nuevo' ? 'selected' : '' ?>>Nuevo</option>
                <option value="Creció" <?= ($filtros['estado'] ?? '') === 'Creció' ? 'selected' : '' ?>>Creció</option>
                <option value="Igual" <?= ($filtros['estado'] ?? '') === 'Igual' ? 'selected' : '' ?>>Igual</option>
                <option value="Decrece" <?= ($filtros['estado'] ?? '') === 'Decrece' ? 'selected' : '' ?>>Decrece</option>
                <option value="Desvinculado" <?= ($filtros['estado'] ?? '') === 'Desvinculado' ? 'selected' : '' ?>>Desvinculado</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Departamento</label>
            <select name="departamento" class="form-input">
                <option value="">Todos</option>
                <?php foreach ($departamentos as $dept): ?>
                <option value="<?= $dept ?>" <?= ($filtros['departamento'] ?? '') === $dept ? 'selected' : '' ?>>
                    <?= $dept ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="btn btn-primary flex-1">
                Aplicar
            </button>
            <a href="/colaboradores" class="btn btn-outline">
                Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Stats Summary -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg p-4 border-l-4 border-primary-500">
        <p class="text-sm text-gray-600">Total</p>
        <p class="text-2xl font-bold"><?= number_format($pagination['totalRecords']) ?></p>
    </div>
    <div class="bg-white rounded-lg p-4 border-l-4 border-green-500">
        <p class="text-sm text-gray-600">Activos</p>
        <p class="text-2xl font-bold"><?= number_format($pagination['totalRecords'] - 0) ?></p>
    </div>
    <div class="bg-white rounded-lg p-4 border-l-4 border-yellow-500">
        <p class="text-sm text-gray-600">Página Actual</p>
        <p class="text-2xl font-bold"><?= $pagination['current'] ?> de <?= $pagination['total'] ?></p>
    </div>
    <div class="bg-white rounded-lg p-4 border-l-4 border-blue-500">
        <p class="text-sm text-gray-600">Mostrando</p>
        <p class="text-2xl font-bold"><?= count($colaboradores) ?></p>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>Celular</th>
                    <th>Territorio</th>
                    <th>Perfil</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($colaboradores)): ?>
                <tr>
                    <td colspan="8" class="text-center text-gray-500 py-8">
                        No se encontraron colaboradores
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($colaboradores as $col): ?>
                    <tr>
                        <td class="font-mono"><?= htmlspecialchars($col['documento']) ?></td>
                        <td>
                            <a href="/colaboradores/<?= $col['id'] ?>" class="text-primary-600 hover:text-primary-700 font-medium">
                                <?= Helpers::formatFullName($col['nombres'], $col['apellidos']) ?>
                            </a>
                        </td>
                        <td class="text-sm text-gray-600"><?= htmlspecialchars($col['email'] ?? '') ?></td>
                        <td class="text-sm text-gray-600"><?= htmlspecialchars($col['telefono'] ?? '') ?></td>
                        <td class="text-sm"><?= htmlspecialchars($col['municipio'] ?? '') ?><?= !empty($col['municipio']) && !empty($col['departamento']) ? ', ' : '' ?><?= htmlspecialchars($col['departamento'] ?? '') ?></td>
                        <td>
                            <button type="button" 
                                    class="hover:opacity-80 transition-opacity flex items-center gap-1 focus:outline-none"
                                    onclick="openQuickEdit(
                                        <?= $col['id'] ?>, 
                                        '<?= htmlspecialchars(addslashes($col['nombres'] . ' ' . $col['apellidos'])) ?>', 
                                        '<?= htmlspecialchars($col['perfil']) ?>', 
                                        '<?= htmlspecialchars($col['estado']) ?>', 
                                        '<?= htmlspecialchars($col['nivel_participacion'] ?? 'Simpatizante') ?>'
                                    )"
                                    title="Edición Rápida">
                                <?= Helpers::estadoBadge($col['perfil']) ?>
                            </button>
                        </td>
                        <td>
                            <button type="button" 
                                    class="hover:opacity-80 transition-opacity flex items-center gap-1 focus:outline-none"
                                    onclick="openQuickEdit(
                                        <?= $col['id'] ?>, 
                                        '<?= htmlspecialchars(addslashes($col['nombres'] . ' ' . $col['apellidos'])) ?>', 
                                        '<?= htmlspecialchars($col['perfil']) ?>', 
                                        '<?= htmlspecialchars($col['estado']) ?>', 
                                        '<?= htmlspecialchars($col['nivel_participacion'] ?? 'Simpatizante') ?>'
                                    )"
                                    title="Edición Rápida">
                                <?= Helpers::estadoBadge($col['estado']) ?>
                            </button>
                        </td>
                        <td>
                            <div class="flex justify-end gap-2">
                                <a href="/colaboradores/<?= $col['id'] ?>"
                                   class="text-blue-600 hover:text-blue-700"
                                   title="Ver">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                <?php if (has_permission($user['tipo_usuario'] ?? null, 'colaboradores', 'edit')): ?>
                                <a href="/colaboradores/<?= $col['id'] ?>/edit"
                                   class="text-yellow-600 hover:text-yellow-700"
                                   title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <?php endif; ?>

                                <a href="/colaboradores/<?= $col['id'] ?>/network"
                                   class="text-green-600 hover:text-green-700"
                                   title="Ver Red">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                    </svg>
                                </a>

                                <?php if (has_permission($user['tipo_usuario'] ?? null, 'colaboradores', 'delete')): ?>
                                <button onclick="deleteColaborador(<?= $col['id'] ?>)"
                                        class="text-red-600 hover:text-red-700"
                                        title="Eliminar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total'] > 1): ?>
    <div class="border-t border-gray-200 px-4 py-3 sm:px-6">
        <?= Helpers::pagination($pagination['current'], $pagination['total'], '/colaboradores') ?>
    </div>
    <?php endif; ?>
</div>

<!-- Quick Edit Modal -->
<div id="quickEditModal" class="modal-backdrop hidden" x-data="{ show: false }">
    <div class="modal" x-show="show" @click.away="show = false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Edición Rápida: <span id="qeNombre" class="font-normal text-gray-600"></span></h3>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form id="quickEditForm" onsubmit="submitQuickEdit(event)">
                    <?= Security::csrfField() ?>
                    <input type="hidden" id="qeId" name="id">
                    
                    <div class="modal-body space-y-4">
                        <div class="form-group">
                            <label class="form-label">Perfil</label>
                            <select id="qePerfil" name="perfil" class="form-input" required>
                                <?php foreach (PERFILES as $key => $label): ?>
                                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nivel de Participación</label>
                            <select id="qeNivel" name="nivel_participacion" class="form-input" required>
                                <?php foreach (NIVELES_PARTICIPACION as $key => $label): ?>
                                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Estado</label>
                            <select id="qeEstado" name="estado" class="form-input" required>
                                <option value="Nuevo">Nuevo</option>
                                <option value="Creció">Creció</option>
                                <option value="Igual">Igual</option>
                                <option value="Decrece">Decrece</option>
                                <option value="Desvinculado">Desvinculado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" @click="show = false" class="btn btn-outline">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="qeSubmitBtn">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="modal-backdrop hidden" x-data="{ show: false }">
    <div class="modal" x-show="show" @click.away="show = false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Importar Colaboradores</h3>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form action="/colaboradores/import" method="POST" enctype="multipart/form-data" id="importForm">
                    <?= Security::csrfField() ?>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Archivo Excel</label>
                            <input type="file" name="archivo" accept=".xlsx,.xls,.csv" class="form-input" required>
                            <p class="text-sm text-gray-500 mt-1">
                                Formatos soportados: Excel (.xlsx, .xls) o CSV
                            </p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                            <p class="text-sm text-blue-800 font-medium mb-2">Formato requerido:</p>
                            <p class="text-xs text-blue-700">
                                El archivo debe contener las columnas: documento, tipo_documento, nombres, apellidos,
                                fecha_nacimiento, genero, email, celular, departamento, municipio, direccion,
                                perfil, nivel_participacion, dato_potencial, dato_historico
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="show = false" class="btn btn-outline">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Importar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
    Alpine.raw(document.getElementById('importModal').__x.$data).show = true;
}

function deleteColaborador(id) {
    App.confirm('¿Está seguro de eliminar este colaborador?', async () => {
        try {
            const response = await App.fetch(`/colaboradores/${id}/delete`, {
                method: 'POST'
            });

            App.toast('Colaborador eliminado', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } catch (error) {
            console.error('Error:', error);
        }
    });
}

// Submit import form
document.getElementById('importForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('/colaboradores/import', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            App.toast(`Importados: ${data.data.exitosos}, Errores: ${data.data.errores.length}`, 'success');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            App.toast(data.message, 'error');
        }
    } catch (error) {
        App.toast('Error en la importación', 'error');
    }
});

// Quick Edit Logic
function openQuickEdit(id, nombre, perfil, estado, nivel) {
    document.getElementById('qeId').value = id;
    document.getElementById('qeNombre').textContent = nombre;
    document.getElementById('qePerfil').value = perfil || 'Lider Comunitario';
    document.getElementById('qeEstado').value = estado || 'Nuevo';
    document.getElementById('qeNivel').value = nivel || 'Simpatizante';
    
    document.getElementById('quickEditModal').classList.remove('hidden');
    Alpine.raw(document.getElementById('quickEditModal').__x.$data).show = true;
}

async function submitQuickEdit(e) {
    e.preventDefault();
    
    const id = document.getElementById('qeId').value;
    const form = document.getElementById('quickEditForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('qeSubmitBtn');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Guardando...';
    
    try {
        const response = await App.fetch(`/colaboradores/${id}/quick-update`, {
            method: 'POST',
            body: formData // Form data handles CSRF automatically if included in form
        });
        
        App.toast('Colaborador actualizado', 'success');
        
        // Hide modal
        Alpine.raw(document.getElementById('quickEditModal').__x.$data).show = false;
        
        // Reload table data
        setTimeout(() => window.location.reload(), 500);
        
    } catch (error) {
        App.toast(error.message || 'Error al actualizar', 'error');
        console.error('Quick edit error:', error);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Guardar Cambios';
    }
}
</script>
