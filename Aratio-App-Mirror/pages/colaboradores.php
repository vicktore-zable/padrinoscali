<?php
/**
 * MÓDULO: Colaboradores
 * CRUD completo con carga masiva CSV
 */

if (!$campanaActiva) {
    echo '<div class="card text-center py-12"><i data-lucide="users" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i><h2 class="text-xl font-bold text-gray-900 mb-2">Selecciona una campaña</h2><p class="text-gray-500">Debes seleccionar una campaña activa para ver sus colaboradores.</p></div>';
    return;
}

$db = getDB();
$campanaId = $campanaActiva['id'];

// Obtener colaboradores de esta campaña
$stmt = $db->prepare("
    SELECT c.*, u.nombre as usuario_nombre,
           CONCAT_WS(' ', l.nombres, l.apellidos) as lider_nombre,
           (SELECT COUNT(*) FROM colaboradores s WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id) as num_seguidores
    FROM colaboradores c
    LEFT JOIN usuarios u ON c.usuario_registro_id = u.id
    LEFT JOIN colaboradores l ON c.lider_directo = l.documento AND c.campana_id = l.campana_id
    WHERE c.campana_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$campanaId]);
$colaboradores = $stmt->fetchAll();

// Calcular campos virtuales (estado y grupo_etareo)
foreach ($colaboradores as &$c) {
    // Mapeo automático de perfiles antiguos y normalización
    if ($c['perfil'] === 'Lider Comunitario' || $c['perfil'] === 'Líder Comunitario') {
        $c['perfil'] = 'Líder / Coordinador';
    }

    // Estado
    if ($c['dato_potencial'] == 0) $c['estado'] = 'Nuevo';
    elseif ($c['dato_historico'] == 0) $c['estado'] = 'Desvinculado';
    elseif ($c['dato_historico'] > $c['dato_potencial']) $c['estado'] = 'Crecio';
    elseif ($c['dato_historico'] < $c['dato_potencial']) $c['estado'] = 'Decrece';
    else $c['estado'] = 'Igual';

    // Grupo etáreo
    $nacimiento = new DateTime($c['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($nacimiento)->y;
    if ($edad <= 17) $c['grupo_etareo'] = 'Joven (0-17)';
    elseif ($edad <= 28) $c['grupo_etareo'] = 'Juventud (18-28)';
    elseif ($edad <= 40) $c['grupo_etareo'] = 'Adulto Joven (29-40)';
    elseif ($edad <= 60) $c['grupo_etareo'] = 'Adulto (41-60)';
    else $c['grupo_etareo'] = 'Mayor (61+)';

    $c['nombre_completo'] = $c['nombres'] . ' ' . $c['apellidos'];
}
unset($c);

// Estadísticas
$total = count($colaboradores);
$porEstado = ['Nuevo' => 0, 'Desvinculado' => 0, 'Crecio' => 0, 'Decrece' => 0, 'Igual' => 0];
$porNivel = [];
foreach ($colaboradores as $c) {
    $porEstado[$c['estado']]++;
    $nivel = $c['nivel_participacion'] ?? 'Simpatizante';
    $porNivel[$nivel] = ($porNivel[$nivel] ?? 0) + 1;
}

// Listas para selectores
$perfiles = ['Líder / Coordinador','Simpatizante','Lider Ambiental','Lider Gremial','Lider Social','Lider Empresarial','Influencer','Lider Juvenil','Lider Poblacional','Lider Diferencial','Medios Tradicionales','Amigo','Familia'];
$niveles = ['Simpatizante', 'Multiplicador', 'Lider', 'Coordinador', 'Contratista'];
$tiposDocumento = ['CC' => 'Cédula', 'TI' => 'Tarjeta Identidad', 'CE' => 'Cédula Extranjería', 'PA' => 'Pasaporte', 'RC' => 'Registro Civil', 'NIT' => 'NIT'];
$generos = ['Masculino', 'Femenino', 'Otro', 'Prefiero no decir'];

// Documento del usuario logueado (para asignar como líder en importación)
$userDocumento = $_SESSION['user_documento'] ?? '';
?>

<div class="space-y-6" x-data="colaboradoresData()" x-init="init()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Colaboradores</h1>
            <p class="text-gray-600 mt-2"><?= htmlspecialchars($campanaActiva['nombre']) ?></p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="?page=colaboradores_red" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center">
                <i data-lucide="network" class="w-5 h-5 mr-2"></i>Ver Red
            </a>
            <a href="?page=colaboradores_reportes" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 flex items-center">
                <i data-lucide="bar-chart-3" class="w-5 h-5 mr-2"></i>Reportes
            </a>
            <a href="api/colaboradores.php?action=export&formato=xlsx&campana_id=<?= $campanaId ?>" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                <i data-lucide="download" class="w-5 h-5 mr-2"></i>Exportar Excel
            </a>
            <button @click="modalImportar = true" class="btn-ghost" title="Importar Excel o CSV">
                <i data-lucide="upload" class="w-5 h-5 inline mr-2"></i>Importar Excel/CSV
            </button>
            <a href="registro-lider.php" target="_blank" class="px-6 py-2 bg-fuchsia-600 text-white rounded-lg hover:bg-fuchsia-700 flex items-center shadow-lg shadow-fuchsia-600/30 transform hover:scale-105 transition-all text-base font-medium">
                <i data-lucide="user-plus" class="w-5 h-5 mr-2"></i>Registrar Líderes
            </a>
            <button @click="abrirModalNuevo()" class="btn-primary shadow-lg shadow-primary/30 transform hover:scale-105 transition-all text-base px-6 py-2">
                <i data-lucide="plus" class="w-5 h-5 inline mr-2"></i>Nuevo
            </button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="stat-card">
            <div class="p-3 bg-blue-100 rounded-lg mb-3"><i data-lucide="users" class="w-6 h-6 text-blue-600"></i></div>
            <h3 class="text-2xl font-bold text-gray-900"><?= number_format($total) ?></h3>
            <p class="text-sm text-gray-600">Total</p>
        </div>
        <div class="stat-card">
            <div class="p-3 bg-green-100 rounded-lg mb-3"><i data-lucide="user-plus" class="w-6 h-6 text-green-600"></i></div>
            <h3 class="text-2xl font-bold text-green-600"><?= number_format($porEstado['Nuevo'] ?? 0) ?></h3>
            <p class="text-sm text-gray-600">Nuevos</p>
        </div>
        <div class="stat-card">
            <div class="p-3 bg-purple-100 rounded-lg mb-3"><i data-lucide="trending-up" class="w-6 h-6 text-purple-600"></i></div>
            <h3 class="text-2xl font-bold text-purple-600"><?= number_format($porEstado['Crecio'] ?? 0) ?></h3>
            <p class="text-sm text-gray-600">Crecieron</p>
        </div>
        <div class="stat-card">
            <div class="p-3 bg-yellow-100 rounded-lg mb-3"><i data-lucide="minus" class="w-6 h-6 text-yellow-600"></i></div>
            <h3 class="text-2xl font-bold text-yellow-600"><?= number_format($porEstado['Igual'] ?? 0) ?></h3>
            <p class="text-sm text-gray-600">Igual</p>
        </div>
        <div class="stat-card">
            <div class="p-3 bg-red-100 rounded-lg mb-3"><i data-lucide="trending-down" class="w-6 h-6 text-red-600"></i></div>
            <h3 class="text-2xl font-bold text-red-600"><?= number_format($porEstado['Decrece'] ?? 0) ?></h3>
            <p class="text-sm text-gray-600">Decrecen</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <div class="relative">
                    <i data-lucide="search" class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" x-model="filtros.busqueda" @input="filtrar()" placeholder="Buscar por nombre o documento..." class="input pl-10">
                </div>
            </div>
            <div>
                <select x-model="filtros.perfil" @change="filtrar()" class="input">
                    <option value="">Todos los perfiles</option>
                    <?php foreach ($perfiles as $p): ?>
                    <option value="<?= $p ?>"><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select x-model="filtros.estado" @change="filtrar()" class="input">
                    <option value="">Todos los estados</option>
                    <option value="Nuevo">Nuevo</option>
                    <option value="Crecio">Crecio</option>
                    <option value="Igual">Igual</option>
                    <option value="Decrece">Decrece</option>
                    <option value="Desvinculado">Desvinculado</option>
                </select>
            </div>
            <div>
                <select x-model="filtros.nivel" @change="filtrar()" class="input">
                    <option value="">Todos los niveles</option>
                    <?php foreach ($niveles as $n): ?>
                    <option value="<?= $n ?>"><?= $n ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer hover:bg-gray-100 transition-colors group select-none" @click="ordenar('nombres')">
                            <div class="flex items-center gap-1">Colaborador <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors"></i></div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer hover:bg-gray-100 transition-colors group select-none" @click="ordenar('documento')">
                            <div class="flex items-center gap-1">Documento <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors"></i></div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer hover:bg-gray-100 transition-colors group select-none" @click="ordenar('lider_nombre')">
                            <div class="flex items-center gap-1">Líder Referente <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors"></i></div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer hover:bg-gray-100 transition-colors group select-none" @click="ordenar('perfil')">
                            <div class="flex items-center gap-1">Perfil <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors"></i></div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer hover:bg-gray-100 transition-colors group select-none" @click="ordenar('nivel_participacion')">
                            <div class="flex items-center gap-1">Nivel <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors"></i></div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer hover:bg-gray-100 transition-colors group select-none" @click="ordenar('municipio')">
                            <div class="flex items-center gap-1">Ubicacion <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors"></i></div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer hover:bg-gray-100 transition-colors group select-none" @click="ordenar('dato_historico')">
                            <div class="flex items-center gap-1">Progreso <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors"></i></div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer hover:bg-gray-100 transition-colors group select-none" @click="ordenar('estado')">
                            <div class="flex items-center gap-1">Estado <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400 group-hover:text-primary transition-colors"></i></div>
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="c in colaboradoresFiltrados" :key="c.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3 cursor-pointer group" @click="editar(c)" title="Ver detalles y editar">
                                    <template x-if="c.foto">
                                        <img :src="'<?= url('') ?>' + c.foto" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm ring-1 ring-gray-100">
                                    </template>
                                    <template x-if="!c.foto">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold mr-1" x-text="c.nombres.charAt(0) + (c.apellidos ? c.apellidos.charAt(0) : '')"></div>
                                    </template>
                                    <div>
                                        <div class="font-medium text-gray-900 group-hover:text-primary transition-colors flex items-center gap-2">
                                            <span x-text="c.nombres + ' ' + (c.apellidos || '')"></span>
                                            <i data-lucide="external-link" class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5" x-text="c.grupo_etareo + ' - ' + c.genero"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm">
                                <span class="text-gray-500" x-text="c.tipo_documento + ':'"></span>
                                <span class="font-mono" x-text="c.documento"></span>
                            </td>
                            <td class="px-4 py-4">
                                <div @click.stop="abrirModalLider(c)" class="inline-flex items-center p-2 -m-2 rounded-lg hover:bg-white border border-transparent hover:border-gray-200 hover:shadow-sm cursor-pointer transition-all group/lider" title="Asignar o Cambiar Líder">
                                    <template x-if="c.lider_nombre">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs mr-2" x-text="c.lider_nombre.charAt(0)"></div>
                                            <span class="text-sm font-medium text-gray-700 group-hover/lider:text-primary transition-colors" x-text="c.lider_nombre"></span>
                                        </div>
                                    </template>
                                    <template x-if="!c.lider_nombre">
                                        <span class="text-sm text-gray-400 group-hover/lider:text-primary italic flex items-center"><i data-lucide="user-plus" class="w-3.5 h-3.5 mr-1"></i> Sin líder</span>
                                    </template>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="badge badge-info cursor-pointer hover:opacity-80 transition-opacity shadow-sm" @click.stop="abrirQuickEdit(c)" title="Edición Rápida" x-text="c.perfil"></span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="badge cursor-pointer hover:opacity-80 transition-opacity shadow-sm" @click.stop="abrirQuickEdit(c)" title="Edición Rápida" :class="{
                                    'bg-emerald-100 text-emerald-700': c.nivel_participacion === 'Contratista',
                                    'bg-blue-100 text-blue-700': c.nivel_participacion === 'Simpatizante',
                                    'bg-purple-100 text-purple-700': c.nivel_participacion === 'Multiplicador',
                                    'bg-amber-100 text-amber-700': c.nivel_participacion === 'Lider',
                                    'bg-red-100 text-red-700': c.nivel_participacion === 'Coordinador'
                                }" x-text="c.nivel_participacion || 'Simpatizante'"></span>
                            </td>
                            <td class="px-4 py-4 text-sm">
                                <p x-text="c.municipio"></p>
                                <p class="text-xs text-gray-500" x-text="c.departamento"></p>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 w-20">
                                        <div class="bg-primary h-2 rounded-full" :style="'width: ' + Math.min(100, (c.dato_historico / Math.max(1, c.dato_potencial)) * 100) + '%'"></div>
                                    </div>
                                    <span class="text-xs font-medium" x-text="c.dato_historico + '/' + c.dato_potencial"></span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="badge cursor-pointer hover:opacity-80 transition-opacity shadow-sm" @click.stop="abrirQuickEdit(c)" title="Edición Rápida" :class="{
                                    'badge-success': c.estado === 'Nuevo' || c.estado === 'Crecio',
                                    'badge-warning': c.estado === 'Igual',
                                    'badge-error': c.estado === 'Decrece' || c.estado === 'Desvinculado'
                                }" x-text="c.estado"></span>
                            </td>
                             <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a :href="'?page=colaborador_detalle&id=' + c.id" class="p-2 text-gray-400 hover:text-blue-500 transition-colors" title="Detalle Completo" @click.stop>
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a :href="'?page=colaboradores_red&root_doc=' + c.documento" 
                                       x-show="c.num_seguidores > 0 || c.perfil.toLowerCase().includes('lider') || c.perfil.toLowerCase().includes('coordinador') || c.perfil.toLowerCase().includes('líder')"
                                       class="p-2 text-gray-400 hover:text-purple-500 transition-colors" title="Ver Red" @click.stop>
                                        <i data-lucide="network" class="w-4 h-4"></i>
                                    </a>
                                    <button @click.stop="editar(c)" class="p-2 text-gray-400 hover:text-primary transition-colors" title="Editar">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </button>
                                    <button @click.stop="eliminar(c.id)" class="p-2 text-gray-400 hover:text-red-500 transition-colors" title="Eliminar">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t bg-gray-50 text-sm text-gray-600">
            Mostrando <span x-text="colaboradoresFiltrados.length"></span> de <span x-text="colaboradores.length"></span> colaboradores
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    <div x-show="modalNuevo" @paste.window="handlePaste($event)" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]" style="display: none;">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto relative z-[10000]">
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-xl font-bold" x-text="form.id ? 'Editar Colaborador' : 'Nuevo Colaborador'"></h2>
                <button @click="cerrarModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form @submit.prevent="guardar()" class="p-6 space-y-6">
                <!-- Datos Personales -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                        <i data-lucide="user" class="w-5 h-5 mr-2 text-fuchsia-500"></i>Datos Personales
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Nombres *</label>
                            <input type="text" x-model="form.nombres" required class="input" placeholder="Nombres completos">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Apellidos *</label>
                            <input type="text" x-model="form.apellidos" required class="input" placeholder="Apellidos completos">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Fecha Nacimiento *</label>
                            <input type="date" x-model="form.fecha_nacimiento" required class="input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Tipo Documento</label>
                            <select x-model="form.tipo_documento" class="input">
                                <?php foreach ($tiposDocumento as $k => $v): ?>
                                <option value="<?= $k ?>"><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Documento *</label>
                            <input type="text" x-model="form.documento" required class="input" placeholder="Numero de documento">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Genero *</label>
                            <select x-model="form.genero" required class="input">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($generos as $g): ?>
                                <option value="<?= $g ?>"><?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Foto de Identidad (Mobile First) -->
                <div class="bg-slate-50 p-6 rounded-3xl border-2 border-dashed border-slate-200">
                    <h3 class="font-black text-gray-700 mb-4 flex items-center uppercase text-xs tracking-widest">
                        <i data-lucide="camera" class="w-4 h-4 mr-2 text-fuchsia-500"></i>Capa de Identidad Visual
                    </h3>
                    <div class="flex flex-col md:flex-row items-center gap-8">
                        <!-- Preview Circle -->
                        <div class="relative w-40 h-40 shrink-0">
                            <div class="w-full h-full rounded-[2rem] bg-white shadow-2xl border-4 border-white overflow-hidden relative group">
                                <template x-if="!mostrandoCamara">
                                    <div class="w-full h-full flex items-center justify-center bg-slate-100">
                                        <template x-if="form.foto || existingFoto">
                                            <img :src="form.foto || ('<?= url('') ?>' + existingFoto)" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!form.foto && !existingFoto">
                                            <div class="text-center p-4">
                                                <i data-lucide="user" class="w-12 h-12 text-slate-300 mx-auto mb-2"></i>
                                                <p class="text-[9px] font-black text-slate-400 uppercase">Sin Identidad</p>
                                                <p class="text-[8px] font-semibold text-slate-400 mt-1 lowercase">(o presiona Ctrl+V)</p>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="mostrandoCamara">
                                    <video x-ref="video" class="w-full h-full object-cover bg-black mirror" autoplay playsinline></video>
                                </template>
                            </div>
                            <!-- Live Badge -->
                            <template x-if="mostrandoCamara">
                                <div class="absolute -top-2 -right-2 bg-red-500 text-white text-[9px] font-black px-2 py-1 rounded-full animate-pulse shadow-lg uppercase">En Vivo</div>
                            </template>
                        </div>

                        <!-- Instructions & Actions -->
                        <div class="flex-1 space-y-4">
                            <div>
                                <h4 class="font-bold text-gray-900 text-sm">Familiarizar al Candidato</h4>
                                <p class="text-xs text-gray-500 leading-relaxed mt-1">Captura una foto clara de tu rostro. Esto permite que el candidato te identifique de inmediato en territorio, generando mayor confianza y recordación.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="abrirCamara()" x-show="!mostrandoCamara" class="px-5 py-2.5 bg-primary text-white rounded-xl font-bold text-xs flex items-center gap-2 hover:bg-slate-900 transition-all shadow-lg shadow-primary/20">
                                    <i data-lucide="aperture" class="w-4 h-4"></i> USAR CÁMARA
                                </button>
                                <button type="button" @click="capturarFoto()" x-show="mostrandoCamara" class="px-5 py-2.5 bg-green-600 text-white rounded-xl font-bold text-xs flex items-center gap-2 hover:bg-green-700 transition-all shadow-lg shadow-green-500/20">
                                    <i data-lucide="camera" class="w-4 h-4"></i> CAPTURAR AHORA
                                </button>
                                <button type="button" @click="detenerCamara()" x-show="mostrandoCamara" class="px-4 py-2 bg-white border border-red-100 text-red-500 rounded-xl font-bold text-xs hover:bg-red-50 transition-all">
                                    CANCELAR
                                </button>
                                
                                <input type="file" x-ref="fileInput" @change="handleFileUpload($event)" accept="image/*" class="hidden">
                                <button type="button" @click="$refs.fileInput.click()" x-show="!mostrandoCamara" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all">
                                    SUBIR ARCHIVO
                                </button>
                                <button type="button" @click="alert('Para pegar, simplemente haz clic en cualquier parte de este recuadro y presiona Ctrl+V (o Cmd+V).')" x-show="!mostrandoCamara" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all flex items-center gap-2">
                                    <i data-lucide="clipboard-paste" class="w-4 h-4"></i> PEGAR
                                </button>
                                
                                <template x-if="form.foto">
                                    <button type="button" @click="form.foto = null" class="px-3 py-2 text-red-400 hover:text-red-600 transition-colors" title="Eliminar foto">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Perfil del Colaborador -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                        <i data-lucide="briefcase" class="w-5 h-5 mr-2 text-fuchsia-500"></i>Perfil del Colaborador
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Perfil *</label>
                            <select x-model="form.perfil" required class="input">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($perfiles as $p): ?>
                                <option value="<?= $p ?>"><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Nivel Participacion</label>
                            <select x-model="form.nivel_participacion" class="input">
                                <?php foreach ($niveles as $n): ?>
                                <option value="<?= $n ?>"><?= $n ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">¿Quién te invitó? (Líder Referente)</label>
                            <div class="relative">
                                <input type="text" x-model="searchLider" @input="buscarLideres()" placeholder="Buscar por nombre o documento..." class="input">
                                <div x-show="lideresEncontrados.length > 0" class="absolute z-10 w-full bg-white border rounded-lg shadow-lg mt-1 max-h-40 overflow-y-auto">
                                    <template x-for="l in lideresEncontrados" :key="l.documento">
                                        <div @click="seleccionarLider(l)" class="p-2 hover:bg-gray-100 cursor-pointer">
                                            <p class="font-medium" x-text="l.nombres + ' ' + l.apellidos"></p>
                                            <p class="text-xs text-gray-500" x-text="l.documento + ' - ' + l.perfil"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1" x-show="form.lider_directo">Lider: <span x-text="form.lider_directo"></span></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Dato Potencial</label>
                            <input type="number" x-model="form.dato_potencial" min="0" class="input" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Dato Historico</label>
                            <input type="number" x-model="form.dato_historico" min="0" class="input" placeholder="0">
                        </div>
                    </div>
                </div>

                <!-- Ubicacion Geografica -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                        <i data-lucide="map-pin" class="w-5 h-5 mr-2 text-fuchsia-500"></i>Ubicacion Geografica
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Departamento *</label>
                            <select x-model="form.departamento" @change="cargarMunicipios()" required class="input">
                                <option value="">Seleccionar...</option>
                                <template x-for="dep in listas.departamentos" :key="dep">
                                    <option :value="dep" x-text="dep"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Municipio *</label>
                        <select x-model="municipio_raw" @change="selectMunicipio()" required class="input" :disabled="!form.departamento">
                            <option value="">Seleccionar municipio...</option>
                            <template x-for="mun in listas.municipios" :key="mun.cod_mpio">
                                <option :value="JSON.stringify(mun)" x-text="mun.municipio"></option>
                            </template>
                        </select>
                    </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Tipo Territorio</label>
                            <select x-model="form.tipo_territorio" @change="cargarTerritorios()" class="input" :disabled="!form.municipio">
                                <option value="">Seleccionar...</option>
                                <template x-for="tipo in listas.tipos_territorio" :key="tipo">
                                    <option :value="tipo" x-text="tipo"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Territorio</label>
                            <select x-model="form.territorio" @change="cargarBarrios()" class="input" :disabled="!form.tipo_territorio">
                                <option value="">Seleccionar...</option>
                                <template x-for="terr in listas.territorios" :key="terr">
                                    <option :value="terr" x-text="terr"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Barrio/Vereda</label>
                            <select x-model="form.barrio" class="input" :disabled="!form.territorio">
                                <option value="">Seleccionar...</option>
                                <template x-for="barrio in listas.barrios" :key="barrio">
                                    <option :value="barrio" x-text="barrio"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Informacion Electoral -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                        <i data-lucide="vote" class="w-5 h-5 mr-2 text-fuchsia-500"></i>Informacion Electoral
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Puesto de Votacion</label>
                            <select x-model="form.puesto_votacion" class="input" :disabled="!form.cod_mpio">
                                <option value="">Seleccionar puesto...</option>
                                <template x-for="p in listas.puestos" :key="p.id">
                                    <option :value="p.puesto" x-text="p.puesto"></option>
                                </template>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Lugar donde ejerce el voto</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Mesa</label>
                            <input type="text" x-model="form.mesa_votacion" class="input" placeholder="Ej: 14">
                        </div>
                    </div>
                </div>

                <!-- Contacto -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                        <i data-lucide="phone" class="w-5 h-5 mr-2 text-fuchsia-500"></i>Contacto
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Email</label>
                            <input type="email" x-model="form.email" class="input" placeholder="correo@ejemplo.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Telefono</label>
                            <input type="tel" x-model="form.telefono" class="input" placeholder="300 123 4567">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2">Observaciones</label>
                            <textarea x-model="form.observaciones" rows="2" class="input" placeholder="Notas adicionales..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-3 pt-4 border-t">
                    <button type="submit" class="btn-primary flex-1" :disabled="loading">
                        <i data-lucide="save" class="w-5 h-5 inline mr-2"></i>
                        <span x-text="loading ? 'Guardando...' : 'Guardar'"></span>
                    </button>
                    <button type="button" @click="cerrarModal()" class="btn-ghost">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Importar CSV -->
    <div x-show="modalImportar" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full relative z-[10000]">
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-xl font-bold">Importar desde Excel / CSV</h2>
                <button @click="modalImportar = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form @submit.prevent="importar()" class="p-6 space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-bold text-blue-800 mb-2">Instrucciones de Importación</h4>
                    <p class="text-sm text-blue-700">Puedes subir archivos <b>.xlsx</b> (Excel) o <b>.csv</b>.</p>
                    <div class="mt-3 flex gap-2">
                        <a href="api/colaboradores.php?action=plantilla_xlsx" class="text-xs bg-white px-2 py-1 rounded border border-blue-300 text-blue-600 hover:bg-blue-50 inline-flex items-center">
                            <i data-lucide="file-spreadsheet" class="w-3 h-3 mr-1"></i>Descargar Plantilla Excel
                        </a>
                        <a href="api/colaboradores.php?action=plantilla" class="text-xs bg-white px-2 py-1 rounded border border-blue-300 text-blue-600 hover:bg-blue-50 inline-flex items-center">
                            <i data-lucide="file-text" class="w-3 h-3 mr-1"></i>Plantilla CSV
                        </a>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Archivo (.xlsx o .csv) *</label>
                    <input type="file" @change="archivoSeleccionado = $event.target.files[0]" accept=".csv,.xlsx" required class="input">
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                        Los colaboradores importados seran asignados a la campana actual y tendran como lider directo al usuario que realiza la importacion.
                    </p>
                </div>

                <template x-if="resultadoImportacion">
                    <div class="rounded-lg p-4" :class="resultadoImportacion.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                        <h4 class="font-bold" :class="resultadoImportacion.success ? 'text-green-800' : 'text-red-800'" x-text="resultadoImportacion.message"></h4>
                        <template x-if="resultadoImportacion.data">
                            <div class="mt-2 text-sm">
                                <p>Procesados: <span x-text="resultadoImportacion.data.procesados"></span></p>
                                <p class="text-green-700">Exitosos: <span x-text="resultadoImportacion.data.exitosos"></span></p>
                                <p class="text-red-700">Fallidos: <span x-text="resultadoImportacion.data.fallidos"></span></p>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="btn-primary flex-1" :disabled="importando || !archivoSeleccionado">
                        <i data-lucide="upload" class="w-5 h-5 inline mr-2"></i>
                        <span x-text="importando ? 'Importando...' : 'Importar'"></span>
                    </button>
                    <button type="button" @click="modalImportar = false" class="btn-ghost">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Asignar Lider -->
    <div x-show="modalAsignarLider" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]" style="display: none;" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full relative z-[10000] overflow-hidden">
            <div class="p-6 border-b flex items-center justify-between bg-primary/5">
                <h2 class="text-xl font-bold flex items-center text-gray-900">
                    <i data-lucide="user-check" class="w-5 h-5 mr-2 text-primary"></i> Asignar Líder
                </h2>
                <button @click="cerrarModalLider()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="text-sm text-gray-500">Asignando líder para <b class="text-gray-900" x-text="colaboradorSeleccionado?.nombres + ' ' + colaboradorSeleccionado?.apellidos"></b></div>
                <div>
                    <label class="block text-sm font-medium mb-2">Buscar y Seleccionar Líder</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" x-model="searchLiderRapido" @input="buscarLideresRapido()" placeholder="Buscar por nombre o cédula..." class="input pl-9 border-primary/30 focus:border-primary">
                        <div x-show="lideresRapidosEncontrados.length > 0" class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg shadow-xl mt-2 max-h-60 overflow-y-auto" @click.away="lideresRapidosEncontrados = []">
                            <template x-for="l in lideresRapidosEncontrados" :key="l.documento">
                                <div @click="seleccionarLiderRapido(l)" class="p-3 hover:bg-primary/5 cursor-pointer border-b last:border-b-0 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold text-xs shrink-0" x-text="l.nombres.charAt(0)"></div>
                                    <div>
                                        <p class="font-bold text-gray-900 leading-tight" x-text="l.nombres + ' ' + l.apellidos"></p>
                                        <p class="text-xs text-gray-500" x-text="l.documento + ' • ' + l.perfil"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                
                <div x-show="nuevoLiderDocumento" class="bg-green-50 rounded-lg p-3 border border-green-200 flex items-start gap-3">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-green-600 mt-0.5"></i>
                    <div>
                        <p class="text-xs text-green-800 font-semibold uppercase">Líder Seleccionado</p>
                        <p class="text-sm font-bold text-gray-900" x-text="nuevoLiderNombre"></p>
                    </div>
                    <button type="button" @click="limpiarLiderRapido()" class="ml-auto text-gray-400 hover:text-red-500" title="Quitar">
                        <i data-lucide="x-circle" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="flex gap-3 pt-4 border-t mt-4">
                    <button type="button" @click="guardarAsignacionLider()" class="btn-primary flex-1" :disabled="loading || (colaboradorSeleccionado && colaboradorSeleccionado.lider_directo == nuevoLiderDocumento)">
                        <i data-lucide="save" class="w-5 h-5 inline mr-2"></i>
                        <span x-text="loading ? 'Guardando...' : 'Guardar Cambios'"></span>
                    </button>
                    <button type="button" @click="cerrarModalLider()" class="btn-secondary">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edición Rápida -->
    <div x-show="modalQuickEdit" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]" style="display: none;" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full relative z-[10000] overflow-hidden">
            <div class="p-6 border-b flex items-center justify-between bg-primary/5">
                <h2 class="text-xl font-bold flex items-center text-gray-900">
                    <i data-lucide="zap" class="w-5 h-5 mr-2 text-primary"></i> Edición Rápida
                </h2>
                <button @click="modalQuickEdit = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form @submit.prevent="guardarQuickEdit()" class="p-6 space-y-4">
                <div class="text-sm text-gray-500 mb-4">Actualizando datos para <b class="text-gray-900" x-text="qeForm.nombre"></b></div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Perfil</label>
                    <select x-model="qeForm.perfil" required class="input">
                        <?php foreach ($perfiles as $p): ?>
                        <option value="<?= $p ?>"><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Nivel de Participación</label>
                    <select x-model="qeForm.nivel_participacion" class="input">
                        <?php foreach ($niveles as $n): ?>
                        <option value="<?= $n ?>"><?= $n ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Estado</label>
                    <select x-model="qeForm.estado" required class="input">
                        <option value="Nuevo">Nuevo</option>
                        <option value="Creció">Creció</option>
                        <option value="Igual">Igual</option>
                        <option value="Decrece">Decrece</option>
                        <option value="Desvinculado">Desvinculado</option>
                    </select>
                    <p class="text-xs text-amber-600 mt-1"><i data-lucide="alert-triangle" class="w-3 h-3 inline mr-1"></i>Cambiar el estado recalculará el progreso.</p>
                </div>
                
                <div class="flex gap-3 pt-4 border-t mt-4">
                    <button type="submit" class="btn-primary flex-1" :disabled="loading">
                        <i data-lucide="save" class="w-5 h-5 inline mr-2"></i>
                        <span x-text="loading ? 'Guardando...' : 'Guardar Cambios'"></span>
                    </button>
                    <button type="button" @click="modalQuickEdit = false" class="btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function colaboradoresData() {
    return {
        modalNuevo: false,
        modalImportar: false,
        loading: false,
        importando: false,
        colaboradores: <?= json_encode(array_values($colaboradores), JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        colaboradoresFiltrados: [],
        filtros: {
            busqueda: '',
            perfil: '',
            estado: '',
            nivel: ''
        },
        ordenColumna: 'created_at',
        ordenAsc: false,
        modalAsignarLider: false,
        colaboradorSeleccionado: null,
        searchLiderRapido: '',
        lideresRapidosEncontrados: [],
        nuevoLiderDocumento: '',
        nuevoLiderNombre: '',
        modalQuickEdit: false,
        qeForm: {
            id: null,
            nombre: '',
            perfil: '',
            nivel_participacion: '',
            estado: ''
        },
        municipio_raw: '',
        form: {
            id: null,
            campana_id: <?= $campanaId ?>,
            nombres: '',
            apellidos: '',
            tipo_documento: 'CC',
            documento: '',
            fecha_nacimiento: '',
            genero: '',
            perfil: '',
            nivel_participacion: 'Simpatizante',
            departamento: '',
            municipio: '',
            tipo_territorio: '',
            territorio: '',
            barrio: '',
            lider_directo: '',
            puesto_votacion: '',
            mesa_votacion: '',
            dato_potencial: 0,
            dato_historico: 0,
            email: '',
            telefono: '',
            foto: null,
            observaciones: ''
        },
        existingFoto: null,
        mostrandoCamara: false,
        videoStream: null,
        listas: {
            departamentos: [],
            municipios: [],
            tipos_territorio: [],
            territorios: [],
            barrios: [],
            puestos: [],
            perfiles: <?= json_encode($perfiles) ?>,
            niveles: <?= json_encode($niveles) ?>
        },
        searchLider: '',
        lideresEncontrados: [],
        archivoSeleccionado: null,
        resultadoImportacion: null,

        async init() {
            this.colaboradoresFiltrados = [...this.colaboradores];
            await this.cargarDepartamentos();
            setTimeout(() => lucide.createIcons(), 100);
        },

        filtrar() {
            this.colaboradoresFiltrados = this.colaboradores.filter(c => {
                if (this.filtros.busqueda) {
                    const search = this.filtros.busqueda.toLowerCase();
                    const nombre = (c.nombres + ' ' + c.apellidos).toLowerCase();
                    const doc = c.documento.toLowerCase();
                    if (!nombre.includes(search) && !doc.includes(search)) return false;
                }
                if (this.filtros.perfil && c.perfil !== this.filtros.perfil) return false;
                if (this.filtros.estado && c.estado !== this.filtros.estado) return false;
                if (this.filtros.nivel && c.nivel_participacion !== this.filtros.nivel) return false;
                return true;
            });
            this.aplicarOrden();
        },

        ordenar(columna) {
            if (this.ordenColumna === columna) {
                this.ordenAsc = !this.ordenAsc;
            } else {
                this.ordenColumna = columna;
                this.ordenAsc = true;
            }
            this.aplicarOrden();
        },

        aplicarOrden() {
            this.colaboradoresFiltrados.sort((a, b) => {
                let valA, valB;
                
                if (this.ordenColumna === 'dato_historico') {
                    valA = parseFloat(a.dato_historico) || 0;
                    valB = parseFloat(b.dato_historico) || 0;
                } else if (this.ordenColumna === 'created_at') {
                    valA = new Date(a.created_at || 0).getTime();
                    valB = new Date(b.created_at || 0).getTime();
                } else {
                    valA = a[this.ordenColumna] ? a[this.ordenColumna].toString().toLowerCase() : '';
                    valB = b[this.ordenColumna] ? b[this.ordenColumna].toString().toLowerCase() : '';
                }

                if (valA < valB) return this.ordenAsc ? -1 : 1;
                if (valA > valB) return this.ordenAsc ? 1 : -1;
                return 0;
            });
        },

        abrirModalNuevo() {
            this.form = {
                id: null, campana_id: <?= $campanaId ?>, nombres: '', apellidos: '',
                tipo_documento: 'CC', documento: '', fecha_nacimiento: '', genero: '',
                perfil: '', nivel_participacion: 'Simpatizante', departamento: '', municipio: '',
                tipo_territorio: '', territorio: '', barrio: '', lider_directo: '',
                puesto_votacion: '', mesa_votacion: '', dato_potencial: 0, dato_historico: 0,
                email: '', telefono: '', foto: null, observaciones: ''
            };
            this.existingFoto = null;
            this.modalNuevo = true;
            this.$nextTick(() => lucide.createIcons());
        },

        async abrirCamara() {
            try {
                this.mostrandoCamara = true;
                this.videoStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: "user", width: 400, height: 400 } 
                });
                this.$nextTick(() => {
                    this.$refs.video.srcObject = this.videoStream;
                });
            } catch (err) {
                alert("No se pudo acceder a la cámara: " + err.message);
                this.mostrandoCamara = false;
            }
        },

        capturarFoto() {
            const video = this.$refs.video;
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            this.form.foto = canvas.toDataURL('image/jpeg', 0.8);
            this.detenerCamara();
        },

        detenerCamara() {
            if (this.videoStream) {
                this.videoStream.getTracks().forEach(track => track.stop());
                this.videoStream = null;
            }
            this.mostrandoCamara = false;
        },

        triggerFileInput() {
            this.$refs.fileInput.click();
        },

        handleFileUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.processImage(file);
        },

        async handlePaste(e) {
            if (!this.modalNuevo) return;
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (let index in items) {
                const item = items[index];
                if (item.kind === 'file' && item.type.indexOf('image/') !== -1) {
                    const file = item.getAsFile();
                    await this.processImage(file);
                    e.preventDefault();
                    break;
                }
            }
        },

        async processImage(file) {
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        const MAX_WIDTH = 800;
                        const MAX_HEIGHT = 800;
                        let width = img.width;
                        let height = img.height;

                        if (width > height) {
                            if (width > MAX_WIDTH) {
                                height *= MAX_WIDTH / width;
                                width = MAX_WIDTH;
                            }
                        } else {
                            if (height > MAX_HEIGHT) {
                                width *= MAX_HEIGHT / height;
                                height = MAX_HEIGHT;
                            }
                        }
                        
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        
                        // Compress to JPEG 70% quality to ensure lightweight base64
                        this.form.foto = canvas.toDataURL('image/jpeg', 0.7);
                        resolve();
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        },

        editar(c) {
            this.form = { ...c };
            this.existingFoto = c.foto;
            this.form.foto = null; // Reset para no reenviar si no cambia
            this.searchLider = c.lider_nombre || '';
            this.modalNuevo = true;
            this.municipio_raw = JSON.stringify({cod_mpio: c.cod_mpio, municipio: c.municipio});
            this.$nextTick(async () => {
                await this.cargarMunicipios();
                await this.cargarTerritorios();
                await this.cargarBarrios();
                await this.cargarPuestos();
                lucide.createIcons();
            });
        },

        cerrarModal() {
            this.detenerCamara();
            this.modalNuevo = false;
        },

        abrirQuickEdit(c) {
            this.qeForm = {
                id: c.id,
                nombre: c.nombres + ' ' + c.apellidos,
                perfil: c.perfil || 'Simpatizante',
                nivel_participacion: c.nivel_participacion || 'Simpatizante',
                estado: c.estado || 'Nuevo'
            };
            this.modalQuickEdit = true;
        },

        async guardarQuickEdit() {
            this.loading = true;
            try {
                const response = await fetch('/aratio/api_colaboradores_verified.php?action=quick_update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.qeForm)
                });
                const result = await response.json();
                
                if (result.success) {
                    const index = this.colaboradores.findIndex(c => c.id === this.qeForm.id);
                    if(index !== -1) {
                        this.colaboradores[index].perfil = this.qeForm.perfil;
                        this.colaboradores[index].nivel_participacion = this.qeForm.nivel_participacion;
                        this.colaboradores[index].estado = this.qeForm.estado;
                        this.filtrar();
                    }
                    this.modalQuickEdit = false;
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (e) {
                alert('Error de conexión al actualizar');
            }
            this.loading = false;
        },

        async abrirModalNuevo() {
            this.form = {
                id: null,
                campana_id: <?= $campanaId ?>,
                nombres: '',
                apellidos: '',
                tipo_documento: 'CC',
                documento: '',
                fecha_nacimiento: '',
                genero: '',
                perfil: '',
                nivel_participacion: 'Simpatizante',
                departamento: '',
                municipio: '',
                cod_mpio: '',
                tipo_territorio: '',
                territorio: '',
                barrio: '',
                puesto_votacion: '',
                mesa_votacion: '',
                lider_directo: '',
                dato_potencial: 0,
                dato_historico: 0,
                email: '',
                telefono: '',
                observaciones: ''
            };
            this.municipio_raw = '';
            this.searchLider = '';
            this.lideresEncontrados = [];
            if (this.listas.departamentos.length === 0) {
                await this.cargarDepartamentos();
            }
            this.modalNuevo = true;
            setTimeout(() => lucide.createIcons(), 100);
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
            this.form.cod_mpio = '';
            this.municipio_raw = '';
            this.form.tipo_territorio = '';
            this.form.territorio = '';
            this.form.barrio = '';
            this.form.puesto_votacion = '';
            this.listas.municipios = [];
            this.listas.tipos_territorio = [];
            this.listas.territorios = [];
            this.listas.barrios = [];
            this.listas.puestos = [];

            if (!this.form.departamento) return;

            try {
                const response = await fetch(`/aratio/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.form.departamento)}`);
                const result = await response.json();
                if (result.success) {
                    this.listas.municipios = result.data;
                }
            } catch (error) {
                console.error('Error cargando municipios:', error);
            }
        },

        async selectMunicipio() {
            if (!this.municipio_raw) return;
            try {
                const munObj = JSON.parse(this.municipio_raw);
                this.form.municipio = munObj.municipio;
                this.form.cod_mpio = munObj.cod_mpio;
                await Promise.all([
                    this.cargarTiposTerritorio(),
                    this.cargarPuestos()
                ]);
            } catch (e) {
                console.error('Error parsing municipio:', e);
            }
        },

        async cargarPuestos() {
            this.listas.puestos = [];
            if (!this.form.cod_mpio) return;
            try {
                const response = await fetch(`/aratio/api/territorios.php?accion=puestos&cod_mpio=${encodeURIComponent(this.form.cod_mpio)}`);
                const result = await response.json();
                if (result.success) {
                    this.listas.puestos = result.data;
                }
            } catch (error) {
                console.error('Error cargando puestos:', error);
            }
        },

        async cargarTiposTerritorio() {
            this.form.tipo_territorio = '';
            this.form.territorio = '';
            this.form.barrio = '';
            this.listas.tipos_territorio = [];
            this.listas.territorios = [];
            this.listas.barrios = [];

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

        async cargarTerritorios() {
            this.form.territorio = '';
            this.form.barrio = '';
            this.listas.territorios = [];
            this.listas.barrios = [];

            if (!this.form.tipo_territorio) return;

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

        async cargarBarrios() {
            this.form.barrio = '';
            this.listas.barrios = [];

            if (!this.form.territorio) return;

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

        async buscarLideres() {
            if (this.searchLider.length < 2) {
                this.lideresEncontrados = [];
                return;
            }

            try {
                const response = await fetch(`/aratio/api/colaboradores.php?action=lideres&campana_id=<?= $campanaId ?>&search=${encodeURIComponent(this.searchLider)}`);
                const result = await response.json();
                if (result.success) {
                    this.lideresEncontrados = result.data;
                }
            } catch (error) {
                console.error('Error buscando lideres:', error);
            }
        },

        seleccionarLider(lider) {
            this.form.lider_directo = lider.documento;
            this.searchLider = lider.nombres + ' ' + lider.apellidos;
            this.lideresEncontrados = [];
        },

        async guardar() {
            this.loading = true;
            // Usar siempre POST. Si es actualización, usar action=update
            const method = 'POST';
            const endpoint = this.form.id ? '/aratio/api/colaboradores.php?action=update' : '/aratio/api/colaboradores.php';

            try {
                const response = await fetch(endpoint, {
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
                    this.modalNuevo = false;
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error de conexion: ' + error.message);
            }
            this.loading = false;
        },

        async editar(colaborador) {
            this.form = {
                id: colaborador.id,
                campana_id: colaborador.campana_id,
                nombres: colaborador.nombres,
                apellidos: colaborador.apellidos,
                tipo_documento: colaborador.tipo_documento || 'CC',
                documento: colaborador.documento,
                fecha_nacimiento: colaborador.fecha_nacimiento,
                genero: colaborador.genero,
                perfil: colaborador.perfil || '',
                nivel_participacion: colaborador.nivel_participacion || 'Simpatizante',
                departamento: colaborador.departamento || '',
                municipio: '',
                cod_mpio: colaborador.cod_mpio || '',
                tipo_territorio: '',
                territorio: '',
                barrio: '',
                lider_directo: colaborador.lider_directo || '',
                puesto_votacion: '',
                mesa_votacion: colaborador.mesa_votacion || '',
                dato_potencial: colaborador.dato_potencial || 0,
                dato_historico: colaborador.dato_historico || 0,
                email: colaborador.email || '',
                telefono: colaborador.telefono || '',
                observaciones: colaborador.observaciones || ''
            };

            this.municipio_raw = '';
            this.searchLider = colaborador.lider_nombre || colaborador.lider_directo || '';

            // Cargar listas en cascada preservando los valores
            if (this.form.departamento) {
                await this.cargarMunicipios();
                if (colaborador.municipio) {
                    this.form.municipio = colaborador.municipio;
                    const munMatch = this.listas.municipios.find(m => m.municipio === colaborador.municipio);
                    if (munMatch) {
                        this.municipio_raw = JSON.stringify(munMatch);
                        this.form.cod_mpio = munMatch.cod_mpio;
                    } else if (this.form.cod_mpio) {
                        this.municipio_raw = JSON.stringify({ municipio: colaborador.municipio, cod_mpio: this.form.cod_mpio });
                    }

                    await Promise.all([
                        this.cargarTiposTerritorio(),
                        this.cargarPuestos()
                    ]);

                    if (colaborador.tipo_territorio) {
                        this.form.tipo_territorio = colaborador.tipo_territorio;
                        await this.cargarTerritorios();

                        if (colaborador.territorio) {
                            this.form.territorio = colaborador.territorio;
                            await this.cargarBarrios();

                            if (colaborador.barrio) {
                                this.form.barrio = colaborador.barrio;
                            }
                        }
                    }
                }
            }

            // Asignar los puestos electoral después de la carga
            this.form.puesto_votacion = colaborador.puesto_votacion || '';

            // Solucionar posibles diferencias de acentos y espacios en Perfil / Nivel de Participacion
            if (colaborador.perfil) {
                const normStr = str => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
                const p = this.listas.perfiles ? this.listas.perfiles.find(x => normStr(x) === normStr(colaborador.perfil)) : null;
                // Si existe un exact match después de normalizar, lo tomamos; sino, tomamos el que traiga la bd
                this.form.perfil = p ? p : colaborador.perfil;
            }

            if (colaborador.nivel_participacion) {
                const normStr = str => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
                const n = this.listas.niveles ? this.listas.niveles.find(x => normStr(x) === normStr(colaborador.nivel_participacion)) : null;
                this.form.nivel_participacion = n ? n : colaborador.nivel_participacion;
            }

            this.modalNuevo = true;
            setTimeout(() => lucide.createIcons(), 100);
        },

        async buscarLideresRapido() {
            if (this.searchLiderRapido.length < 2) {
                this.lideresRapidosEncontrados = [];
                return;
            }

            try {
                const response = await fetch(`/aratio/api/colaboradores.php?action=lideres&campana_id=<?= $campanaId ?>&search=${encodeURIComponent(this.searchLiderRapido)}`);
                const result = await response.json();
                if (result.success) {
                    this.lideresRapidosEncontrados = result.data;
                }
            } catch (error) {
                console.error('Error buscando lideres:', error);
            }
        },

        seleccionarLiderRapido(lider) {
            this.nuevoLiderDocumento = lider.documento;
            this.nuevoLiderNombre = lider.nombres + ' ' + lider.apellidos;
            this.searchLiderRapido = '';
            this.lideresRapidosEncontrados = [];
        },

        limpiarLiderRapido() {
            this.nuevoLiderDocumento = '';
            this.nuevoLiderNombre = '';
        },

        abrirModalLider(colaborador) {
            this.colaboradorSeleccionado = colaborador;
            this.nuevoLiderDocumento = colaborador.lider_directo || '';
            this.nuevoLiderNombre = colaborador.lider_nombre || '';
            this.searchLiderRapido = '';
            this.lideresRapidosEncontrados = [];
            this.modalAsignarLider = true;
            setTimeout(() => lucide.createIcons(), 100);
        },

        cerrarModalLider() {
            this.modalAsignarLider = false;
            this.colaboradorSeleccionado = null;
        },

        async guardarAsignacionLider() {
            if (!this.colaboradorSeleccionado) return;
            this.loading = true;

            const payload = {
                id: this.colaboradorSeleccionado.id,
                campana_id: this.colaboradorSeleccionado.campana_id,
                nombres: this.colaboradorSeleccionado.nombres,
                apellidos: this.colaboradorSeleccionado.apellidos,
                documento: this.colaboradorSeleccionado.documento,
                genero: this.colaboradorSeleccionado.genero,
                fecha_nacimiento: this.colaboradorSeleccionado.fecha_nacimiento,
                perfil: this.colaboradorSeleccionado.perfil,
                lider_directo: this.nuevoLiderDocumento
            };

            try {
                const response = await fetch('/aratio/api/colaboradores.php?action=update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Líder asignado correctamente', 'success');
                    this.cerrarModalLider();
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error de conexion: ' + error.message);
            }
            this.loading = false;
        },

        async eliminar(id) {
            if (!confirm('Estas seguro de eliminar este colaborador? Los colaboradores que lo tengan como lider quedaran sin lider asignado.')) return;

            try {
                const response = await fetch(`/aratio/api/colaboradores.php?id=${id}`, {
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
                alert('Error de conexion: ' + error.message);
            }
        },

        async importar() {
            if (!this.archivoSeleccionado) return;

            this.importando = true;
            this.resultadoImportacion = null;

            const formData = new FormData();
            formData.append('archivo', this.archivoSeleccionado);
            formData.append('campana_id', <?= $campanaId ?>);

            try {
                const response = await fetch('/aratio/api/colaboradores.php?action=import', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const result = await response.json();
                this.resultadoImportacion = result;

                if (result.success && result.data.exitosos > 0) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            } catch (error) {
                this.resultadoImportacion = {
                    success: false,
                    message: 'Error de conexion: ' + error.message
                };
            }
            this.importando = false;
        },

        cerrarModal() {
            this.modalNuevo = false;
            this.listas.municipios = [];
            this.listas.tipos_territorio = [];
            this.listas.territorios = [];
            this.listas.barrios = [];
            this.lideresEncontrados = [];
            this.searchLider = '';
        }
    }
}
</script>
