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
           CONCAT(l.nombres, ' ', l.apellidos) as nombre_lider
    FROM colaboradores c
    LEFT JOIN usuarios u ON c.usuario_registro_id = u.id
    LEFT JOIN colaboradores l ON c.lider_directo = l.documento AND l.campana_id = c.campana_id
    WHERE c.campana_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$campanaId]);
$colaboradores = $stmt->fetchAll();

// Calcular campos virtuales (estado y grupo_etareo)
foreach ($colaboradores as &$c) {
    // Estado
    if ($c['dato_potencial'] == 0)
        $c['estado'] = 'Nuevo';
    elseif ($c['dato_historico'] == 0)
        $c['estado'] = 'Desvinculado';
    elseif ($c['dato_historico'] > $c['dato_potencial'])
        $c['estado'] = 'Crecio';
    elseif ($c['dato_historico'] < $c['dato_potencial'])
        $c['estado'] = 'Decrece';
    else
        $c['estado'] = 'Igual';

    // Grupo etáreo
    $nacimiento = new DateTime($c['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($nacimiento)->y;
    if ($edad <= 17)
        $c['grupo_etareo'] = 'Joven (0-17)';
    elseif ($edad <= 28)
        $c['grupo_etareo'] = 'Juventud (18-28)';
    elseif ($edad <= 40)
        $c['grupo_etareo'] = 'Adulto Joven (29-40)';
    elseif ($edad <= 60)
        $c['grupo_etareo'] = 'Adulto (41-60)';
    else
        $c['grupo_etareo'] = 'Mayor (61+)';

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
$perfiles = ['Lider Comunitario', 'Lider Ambiental', 'Lider Gremial', 'Lider Social', 'Lider Empresarial', 'Influencer', 'Lider Juvenil', 'Lider Poblacional', 'Lider Diferencial', 'Medios Tradicionales', 'Amigo', 'Familia'];
$niveles = ['Simpatizante', 'Aportante', 'Activista de Opinion', 'Movilizador', 'Contradictor'];
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
            <a href="?page=colaboradores_red"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center">
                <i data-lucide="network" class="w-5 h-5 mr-2"></i>Ver Red
            </a>
            <a href="?page=colaboradores_reportes"
                class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 flex items-center">
                <i data-lucide="bar-chart-3" class="w-5 h-5 mr-2"></i>Reportes
            </a>
            <a href="api/colaboradores.php?action=export&formato=csv&campana_id=<?= $campanaId ?>"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                <i data-lucide="download" class="w-5 h-5 mr-2"></i>Exportar
            </a>
            <button @click="modalImportar = true" class="btn-ghost" title="Importar CSV local">
                <i data-lucide="upload" class="w-5 h-5 inline mr-2"></i>Importar CSV
            </button>
            <button @click="abrirModalNuevo()" class="btn-primary">
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
            <div class="p-3 bg-green-100 rounded-lg mb-3"><i data-lucide="user-plus" class="w-6 h-6 text-green-600"></i>
            </div>
            <h3 class="text-2xl font-bold text-green-600"><?= number_format($porEstado['Nuevo'] ?? 0) ?></h3>
            <p class="text-sm text-gray-600">Nuevos</p>
        </div>
        <div class="stat-card">
            <div class="p-3 bg-purple-100 rounded-lg mb-3"><i data-lucide="trending-up"
                    class="w-6 h-6 text-purple-600"></i></div>
            <h3 class="text-2xl font-bold text-purple-600"><?= number_format($porEstado['Crecio'] ?? 0) ?></h3>
            <p class="text-sm text-gray-600">Crecieron</p>
        </div>
        <div class="stat-card">
            <div class="p-3 bg-yellow-100 rounded-lg mb-3"><i data-lucide="minus" class="w-6 h-6 text-yellow-600"></i>
            </div>
            <h3 class="text-2xl font-bold text-yellow-600"><?= number_format($porEstado['Igual'] ?? 0) ?></h3>
            <p class="text-sm text-gray-600">Igual</p>
        </div>
        <div class="stat-card">
            <div class="p-3 bg-red-100 rounded-lg mb-3"><i data-lucide="trending-down" class="w-6 h-6 text-red-600"></i>
            </div>
            <h3 class="text-2xl font-bold text-red-600"><?= number_format($porEstado['Decrece'] ?? 0) ?></h3>
            <p class="text-sm text-gray-600">Decrecen</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <div class="relative">
                    <i data-lucide="search"
                        class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" x-model="filtros.busqueda" @input="filtrar()"
                        placeholder="Buscar por nombre o documento..." class="input pl-10">
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
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Colaborador</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Documento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perfil</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Líder</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ubicacion</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progreso</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <template x-for="c in colaboradoresFiltrados" :key="c.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-fuchsia-500 to-amber-500 flex items-center justify-center text-white font-bold mr-3"
                                        x-text="c.nombres.charAt(0) + c.apellidos.charAt(0)"></div>
                                    <div>
                                        <a :href="'index.php?page=colaborador_detalle&id=' + c.id"
                                            class="font-medium text-gray-900 hover:text-fuchsia-600 transition-colors"
                                            x-text="c.nombres + ' ' + c.apellidos"></a>
                                        <p class="text-xs text-gray-500" x-text="c.grupo_etareo + ' - ' + c.genero"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm">
                                <span class="text-gray-500" x-text="c.tipo_documento + ':'"></span>
                                <span class="font-mono" x-text="c.documento"></span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="badge badge-info" x-text="c.perfil"></span>
                            </td>
                            <td class="px-4 py-4 text-sm">
                                <div x-show="c.nombre_lider">
                                    <p class="font-medium text-gray-900" x-text="c.nombre_lider"></p>
                                    <p class="text-xs text-gray-500" x-text="c.lider_directo"></p>
                                </div>
                                <span x-show="!c.nombre_lider && c.lider_directo" class="text-gray-500"
                                    x-text="c.lider_directo"></span>
                                <span x-show="!c.lider_directo" class="text-gray-400">-</span>
                            </td>
                            <td class="px-4 py-4 text-sm">
                                <p x-text="c.municipio"></p>
                                <p class="text-xs text-gray-500" x-text="c.departamento"></p>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 w-20">
                                        <div class="bg-fuchsia-500 h-2 rounded-full"
                                            :style="'width: ' + Math.min(100, (c.dato_historico / Math.max(1, c.dato_potencial)) * 100) + '%'">
                                        </div>
                                    </div>
                                    <span class="text-xs font-medium"
                                        x-text="c.dato_historico + '/' + c.dato_potencial"></span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="badge" :class="{
                                    'badge-success': c.estado === 'Nuevo' || c.estado === 'Crecio',
                                    'badge-warning': c.estado === 'Igual',
                                    'badge-error': c.estado === 'Decrece' || c.estado === 'Desvinculado'
                                }" x-text="c.estado"></span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <a :href="'index.php?page=colaborador_detalle&id=' + c.id"
                                    class="text-fuchsia-600 hover:text-fuchsia-800 mr-2" title="Ver Perfil">
                                    <i data-lucide="eye" class="w-4 h-4 inline"></i>
                                </a>
                                <a :href="'index.php?page=colaboradores_red&focus=' + c.documento"
                                    class="text-purple-600 hover:text-purple-800 mr-2" title="Ver en Red">
                                    <i data-lucide="network" class="w-4 h-4 inline"></i>
                                </a>
                                <template x-if="c.perfil.includes('Lider')">
                                    <button @click="abrirCurriculum(c.id)"
                                        class="text-blue-600 hover:text-blue-800 mr-2" title="Gestionar Curriculum">
                                        <i data-lucide="file-text" class="w-4 h-4 inline"></i>
                                    </button>
                                </template>
                                <button @click="editar(c)" class="text-yellow-600 hover:text-yellow-800 mr-2"
                                    title="Editar">
                                    <i data-lucide="edit" class="w-4 h-4 inline"></i>
                                </button>
                                <button @click="eliminar(c.id)" class="text-red-600 hover:text-red-800"
                                    title="Eliminar">
                                    <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t bg-gray-50 text-sm text-gray-600">
            Mostrando <span x-text="colaboradoresFiltrados.length"></span> de <span
                x-text="colaboradores.length"></span> colaboradores
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    <div x-show="modalNuevo" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]"
        style="display: none;">
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
                            <input type="text" x-model="form.nombres" required class="input"
                                placeholder="Nombres completos">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Apellidos *</label>
                            <input type="text" x-model="form.apellidos" required class="input"
                                placeholder="Apellidos completos">
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
                            <input type="text" x-model="form.documento" required class="input"
                                placeholder="Numero de documento">
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

                <!-- Perfil Politico -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                        <i data-lucide="briefcase" class="w-5 h-5 mr-2 text-fuchsia-500"></i>Perfil Politico
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
                            <label class="block text-sm font-medium mb-2">Lider Directo</label>
                            <div class="relative">
                                <input type="text" x-model="searchLider" @input="buscarLideres()"
                                    placeholder="Buscar por nombre o documento..." class="input">
                                <div x-show="lideresEncontrados.length > 0"
                                    class="absolute z-10 w-full bg-white border rounded-lg shadow-lg mt-1 max-h-40 overflow-y-auto">
                                    <template x-for="l in lideresEncontrados" :key="l.documento">
                                        <div @click="seleccionarLider(l)" class="p-2 hover:bg-gray-100 cursor-pointer">
                                            <p class="font-medium" x-text="l.nombres + ' ' + l.apellidos"></p>
                                            <p class="text-xs text-gray-500" x-text="l.documento + ' - ' + l.perfil">
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1" x-show="form.lider_directo">Lider: <span
                                    x-text="form.lider_directo"></span></p>
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

                <!-- Áreas de Interés -->
                <div>
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                        <i data-lucide="heart" class="w-5 h-5 mr-2 text-fuchsia-500"></i>Áreas de Interés
                    </h3>
                    <div
                        class="h-32 overflow-y-auto border rounded-lg p-3 bg-white grid grid-cols-1 md:grid-cols-3 gap-2">
                        <?php
                        $areas = [
                            'Educación',
                            'Salud',
                            'Medio Ambiente',
                            'Economía',
                            'Seguridad',
                            'Cultura',
                            'Deportes',
                            'Tecnología',
                            'Derechos Humanos',
                            'Juventud',
                            'Comercio',
                            'Social',
                            'Proyectos'
                        ];
                        foreach ($areas as $area):
                            ?>
                            <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                                <input type="checkbox" value="<?= $area ?>" x-model="form.areas_interes"
                                    class="form-checkbox h-4 w-4 text-fuchsia-600 rounded">
                                <span class="text-sm text-gray-700"><?= $area ?></span>
                            </label>
                        <?php endforeach; ?>
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
                            <label class="block text-sm font-medium mb-2">Municipio *</label>
                            <select x-model="form.municipio" @change="cargarTiposTerritorio()" required class="input"
                                :disabled="!form.departamento">
                                <option value="">Seleccionar...</option>
                                <template x-for="mun in listas.municipios" :key="mun">
                                    <option :value="mun" x-text="mun"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Tipo Territorio</label>
                            <select x-model="form.tipo_territorio" @change="cargarTerritorios()" class="input"
                                :disabled="!form.municipio">
                                <option value="">Seleccionar...</option>
                                <template x-for="tipo in listas.tipos_territorio" :key="tipo">
                                    <option :value="tipo" x-text="tipo"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Territorio</label>
                            <select x-model="form.territorio" @change="cargarBarrios()" class="input"
                                :disabled="!form.tipo_territorio">
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


                        <!-- Dirección detallada -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Dirección</label>
                            <input type="text" x-model="form.direccion" class="input" placeholder="Ej: Cra 4 # 12-34">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Detalle Ubicación</label>
                            <input type="text" x-model="form.detalle_ubicacion" class="input"
                                placeholder="Ej: Casa esquinera, frente al parque">
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
                            <input type="text" x-model="form.puesto_votacion" class="input"
                                placeholder="Ej: Colegio San Jose">
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
                        <div>
                            <label class="block text-sm font-medium mb-2">WhatsApp</label>
                            <input type="tel" x-model="form.telefono_whatsapp" class="input" placeholder="300 123 4567">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2">Observaciones</label>
                            <textarea x-model="form.observaciones" rows="2" class="input"
                                placeholder="Notas adicionales..."></textarea>
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
    <div x-show="modalImportar"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]"
        style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full relative z-[10000]">
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-xl font-bold">Importar Colaboradores</h2>
                <button @click="modalImportar = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form @submit.prevent="importar()" class="p-6 space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-bold text-blue-800 mb-2">Formato del archivo CSV</h4>
                    <p class="text-sm text-blue-700">El archivo debe tener las siguientes columnas:</p>
                    <code class="text-xs block mt-2 bg-blue-100 p-2 rounded overflow-x-auto">
                        nombres, apellidos, tipo_documento, documento, fecha_nacimiento, genero, perfil, nivel_participacion, departamento, municipio, tipo_territorio, territorio, barrio, puesto_votacion, mesa_votacion, lider_documento, dato_potencial, dato_historico, observaciones
                    </code>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Archivo CSV *</label>
                    <input type="file" @change="archivoSeleccionado = $event.target.files[0]" accept=".csv" required
                        class="input">
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                        Los colaboradores importados seran asignados a la campana actual y tendran como lider directo al
                        usuario que realiza la importacion.
                    </p>
                </div>

                <template x-if="resultadoImportacion">
                    <div class="rounded-lg p-4"
                        :class="resultadoImportacion.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                        <h4 class="font-bold" :class="resultadoImportacion.success ? 'text-green-800' : 'text-red-800'"
                            x-text="resultadoImportacion.message"></h4>
                        <template x-if="resultadoImportacion.data">
                            <div class="mt-2 text-sm">
                                <p>Procesados: <span x-text="resultadoImportacion.data.procesados"></span></p>
                                <p class="text-green-700">Exitosos: <span
                                        x-text="resultadoImportacion.data.exitosos"></span></p>
                                <p class="text-red-700">Fallidos: <span
                                        x-text="resultadoImportacion.data.fallidos"></span></p>
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
        <!-- Modal Curriculum -->
        <div x-show="modalCurriculum"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]"
            style="display: none;">
            <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto relative z-[10000]">
                <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white z-10">
                    <h2 class="text-xl font-bold flex items-center">
                        <i data-lucide="file-text" class="w-6 h-6 mr-2 text-blue-600"></i>
                        Gestionar Curriculum
                    </h2>
                    <button @click="modalCurriculum = false" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div x-show="loadingCurriculum" class="p-12 text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-500">Cargando informacion...</p>
                </div>

                <form x-show="!loadingCurriculum" @submit.prevent="guardarCurriculum()" class="p-6 space-y-8">

                    <!-- Resumen Profesional -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">Resumen Profesional</h3>
                        <textarea x-model="curriculum.resumen_profesional" rows="4" class="input"
                            placeholder="Breve descripcion del perfil profesional y liderazgo..."></textarea>
                    </div>

                    <!-- Formacion Academica -->
                    <div>
                        <div class="flex justify-between items-center mb-4 pb-2 border-b">
                            <h3 class="text-lg font-bold text-gray-800">Formacion Academica</h3>
                            <button type="button" @click="agregarFormacion()" class="text-sm btn-ghost text-blue-600">
                                <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i>Agregar
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(item, index) in curriculum.formacion_academica" :key="index">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 relative group">
                                    <button type="button" @click="curriculum.formacion_academica.splice(index, 1)"
                                        class="absolute top-2 right-2 text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500">Titulo / Programa</label>
                                            <input type="text" x-model="item.titulo" class="input bg-white"
                                                placeholder="Ej: Administracion de Empresas">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500">Institucion</label>
                                            <input type="text" x-model="item.institucion" class="input bg-white"
                                                placeholder="Ej: Universidad del Valle">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500">Nivel</label>
                                            <select x-model="item.nivel" class="input bg-white">
                                                <option value="">Seleccionar...</option>
                                                <option value="Bachiller">Bachiller</option>
                                                <option value="Tecnico">Tecnico</option>
                                                <option value="Tecnologo">Tecnologo</option>
                                                <option value="Profesional">Profesional</option>
                                                <option value="Especializacion">Especializacion</option>
                                                <option value="Maestria">Maestria</option>
                                                <option value="Doctorado">Doctorado</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500">Año Finalizacion</label>
                                            <input type="number" x-model="item.anio" class="input bg-white"
                                                placeholder="Ej: 2020">
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="curriculum.formacion_academica.length === 0"
                                class="text-center py-4 text-gray-400 text-sm">
                                No hay registros de formacion academica
                            </div>
                        </div>
                    </div>

                    <!-- Experiencia Laboral -->
                    <div>
                        <div class="flex justify-between items-center mb-4 pb-2 border-b">
                            <h3 class="text-lg font-bold text-gray-800">Experiencia Laboral</h3>
                            <button type="button" @click="agregarExperiencia()" class="text-sm btn-ghost text-blue-600">
                                <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i>Agregar
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(item, index) in curriculum.experiencia_laboral" :key="index">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 relative group">
                                    <button type="button" @click="curriculum.experiencia_laboral.splice(index, 1)"
                                        class="absolute top-2 right-2 text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500">Cargo</label>
                                            <input type="text" x-model="item.cargo" class="input bg-white"
                                                placeholder="Ej: Gerente Comercial">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500">Empresa / Entidad</label>
                                            <input type="text" x-model="item.empresa" class="input bg-white"
                                                placeholder="Ej: Alcaldia de Yumbo">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500">Fecha Inicio</label>
                                            <input type="date" x-model="item.fecha_inicio" class="input bg-white">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500">Fecha Fin</label>
                                            <input type="date" x-model="item.fecha_fin" class="input bg-white">
                                            <div class="mt-1">
                                                <label class="inline-flex items-center text-xs">
                                                    <input type="checkbox" x-model="item.actual"
                                                        class="form-checkbox h-3 w-3 text-blue-600">
                                                    <span class="ml-2">Trabajo actual</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="text-xs font-semibold text-gray-500">Funciones /
                                                Logros</label>
                                            <textarea x-model="item.descripcion" rows="2"
                                                class="input bg-white"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="curriculum.experiencia_laboral.length === 0"
                                class="text-center py-4 text-gray-400 text-sm">
                                No hay registros de experiencia laboral
                            </div>
                        </div>
                    </div>

                    <!-- Participacion Politica / Social -->
                    <div>
                        <div class="flex justify-between items-center mb-4 pb-2 border-b">
                            <h3 class="text-lg font-bold text-gray-800">Trayectoria Politica / Social</h3>
                            <button type="button" @click="agregarTrayectoria()" class="text-sm btn-ghost text-blue-600">
                                <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i>Agregar
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(item, index) in curriculum.participacion_politica" :key="index">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 relative group">
                                    <button type="button" @click="curriculum.participacion_politica.splice(index, 1)"
                                        class="absolute top-2 right-2 text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500">Rol / Candidatura</label>
                                            <input type="text" x-model="item.rol" class="input bg-white"
                                                placeholder="Ej: Candidato al Concejo">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500">Organizacion /
                                                Partido</label>
                                            <input type="text" x-model="item.organizacion" class="input bg-white"
                                                placeholder="Ej: Partido Liberal">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500">Año</label>
                                            <input type="number" x-model="item.anio" class="input bg-white">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-gray-500">Resultado / Logro</label>
                                            <input type="text" x-model="item.resultado" class="input bg-white"
                                                placeholder="Ej: 500 votos">
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="curriculum.participacion_politica.length === 0"
                                class="text-center py-4 text-gray-400 text-sm">
                                No hay registros de trayectoria
                            </div>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">Habilidades</h3>
                            <textarea x-model="curriculum.habilidades" rows="3" class="input"
                                placeholder="Liderazgo, Oratoria, Gestion de Proyectos..."></textarea>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">Idiomas</h3>
                            <textarea x-model="curriculum.idiomas" rows="3" class="input"
                                placeholder="Ingles (B1), Frances (Basico)..."></textarea>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">Reconocimientos</h3>
                            <textarea x-model="curriculum.reconocimientos" rows="3" class="input"
                                placeholder="Premios, menciones honorificas..."></textarea>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">Referencias</h3>
                            <textarea x-model="curriculum.referencias" rows="3" class="input"
                                placeholder="Nombres y telefonos de contacto..."></textarea>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3 pt-4 border-t sticky bottom-0 bg-white p-4 -mx-6 -mb-6 shadow-lg">
                        <button type="submit" class="btn-primary flex-1" :disabled="savingCurriculum">
                            <i data-lucide="save" class="w-5 h-5 inline mr-2"></i>
                            <span x-text="savingCurriculum ? 'Guardando...' : 'Guardar Curriculum'"></span>
                        </button>
                        <button type="button" @click="modalCurriculum = false" class="btn-ghost">Cancelar</button>
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
                modalCurriculum: false,
                loading: false,
                loadingCurriculum: false,
                savingCurriculum: false,
                importando: false,
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
                    telefono_whatsapp: '',
                    direccion: '',
                    detalle_ubicacion: '',
                    areas_interes: [],
                    observaciones: ''
                },
                listas: {
                    departamentos: [],
                    municipios: [],
                    tipos_territorio: [],
                    territorios: [],
                    barrios: []
                },
                searchLider: '',
                lideresEncontrados: [],
                archivoSeleccionado: null,
                resultadoImportacion: null,

                // Estructura del Curriculum
                curriculum: {
                    colaborador_id: null,
                    resumen_profesional: '',
                    formacion_academica: [],
                    experiencia_laboral: [],
                    participacion_politica: [],
                    habilidades: '',
                    idiomas: '',
                    reconocimientos: '',
                    referencias: '',
                    observaciones: ''
                },

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
                        telefono_whatsapp: '',
                        direccion: '',
                        detalle_ubicacion: '',
                        areas_interes: [],
                        observaciones: ''
                    };
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
                        const response = await fetch('/api/territorios.php?accion=departamentos');
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
                    this.form.tipo_territorio = '';
                    this.form.territorio = '';
                    this.form.barrio = '';
                    this.listas.municipios = [];
                    this.listas.tipos_territorio = [];
                    this.listas.territorios = [];
                    this.listas.barrios = [];

                    if (!this.form.departamento) return;

                    try {
                        const response = await fetch(`/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.form.departamento)}`);
                        const result = await response.json();
                        if (result.success) {
                            this.listas.municipios = result.data;
                        }
                    } catch (error) {
                        console.error('Error cargando municipios:', error);
                    }
                },

                async cargarTiposTerritorio() {
                    this.form.tipo_territorio = '';
                    this.form.territorio = '';
                    this.form.barrio = '';
                    this.listas.tipos_territorio = [];
                    this.listas.territorios = [];
                    this.listas.barrios = [];

                    if (!this.form.municipio) return;

                    try {
                        const response = await fetch(`/api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}`);
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
                        const response = await fetch(`/api/territorios.php?accion=territorios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(this.form.tipo_territorio)}`);
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
                        const response = await fetch(`/api/territorios.php?accion=barrios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(this.form.tipo_territorio)}&territorio=${encodeURIComponent(this.form.territorio)}`);
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
                        const response = await fetch(`/api/colaboradores.php?action=lideres&campana_id=<?= $campanaId ?>&search=${encodeURIComponent(this.searchLider)}`);
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
                    const method = this.form.id ? 'PUT' : 'POST';

                    try {
                        const response = await fetch('/api/colaboradores.php', {
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
                        perfil: colaborador.perfil,
                        nivel_participacion: colaborador.nivel_participacion || 'Simpatizante',
                        departamento: colaborador.departamento,
                        municipio: colaborador.municipio,
                        tipo_territorio: colaborador.tipo_territorio || '',
                        territorio: colaborador.territorio || '',
                        barrio: colaborador.barrio || '',
                        lider_directo: colaborador.lider_directo || '',
                        puesto_votacion: colaborador.puesto_votacion || '',
                        mesa_votacion: colaborador.mesa_votacion || '',
                        dato_potencial: colaborador.dato_potencial || 0,
                        dato_historico: colaborador.dato_historico || 0,
                        email: colaborador.email || '',
                        telefono: colaborador.telefono || '',
                        telefono_whatsapp: colaborador.telefono_whatsapp || '',
                        direccion: colaborador.direccion || '',
                        detalle_ubicacion: colaborador.detalle_ubicacion || '',
                        areas_interes: [], // Se llenará abajo
                        observaciones: colaborador.observaciones || ''
                    };

                    // Parsear áreas de interés
                    if (colaborador.areas_interes) {
                        try {
                            const parsed = JSON.parse(colaborador.areas_interes);
                            this.form.areas_interes = Array.isArray(parsed) ? parsed : [];
                        } catch (e) {
                            console.error('Error parsing areas_interes', e);
                            this.form.areas_interes = [];
                        }
                    }

                    this.searchLider = colaborador.lider_directo || '';

                    // Cargar listas en cascada
                    if (this.form.departamento) {
                        await this.cargarMunicipios();
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

                    // Restaurar valores despues de cargar listas
                    this.form.municipio = colaborador.municipio;
                    this.form.tipo_territorio = colaborador.tipo_territorio || '';
                    this.form.territorio = colaborador.territorio || '';
                    this.form.barrio = colaborador.barrio || '';

                    this.modalNuevo = true;
                    setTimeout(() => lucide.createIcons(), 100);
                },

                async eliminar(id) {
                    if (!confirm('Estas seguro de eliminar este colaborador? Los colaboradores que lo tengan como lider quedaran sin lider asignado.')) return;

                    try {
                        const response = await fetch(`/api/colaboradores.php?id=${id}`, {
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
                        const response = await fetch('/api/colaboradores.php?action=import', {
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
                },

                // --- Logica de Curriculum ---

                async abrirCurriculum(colaboradorId) {
                    this.modalCurriculum = true;
                    this.loadingCurriculum = true;
                    this.curriculum.colaborador_id = colaboradorId;

                    try {
                        const response = await fetch(`/api/colaboradores.php?action=curriculum&id=${colaboradorId}`);
                        const result = await response.json();

                        if (result.success) {
                            this.curriculum = {
                                ...this.curriculum, // Mantener estructura base
                                ...result.data,     // Sobrescribir con datos
                                colaborador_id: colaboradorId // Asegurar ID
                            };

                            // Asegurar arrays
                            if (!Array.isArray(this.curriculum.formacion_academica)) this.curriculum.formacion_academica = [];
                            if (!Array.isArray(this.curriculum.experiencia_laboral)) this.curriculum.experiencia_laboral = [];
                            if (!Array.isArray(this.curriculum.participacion_politica)) this.curriculum.participacion_politica = [];
                        }
                    } catch (error) {
                        console.error('Error cargando curriculum:', error);
                        alert('Error al cargar datos del curriculum');
                    }

                    this.loadingCurriculum = false;
                    setTimeout(() => lucide.createIcons(), 100);
                },

                agregarFormacion() {
                    this.curriculum.formacion_academica.push({
                        titulo: '', institucion: '', nivel: '', anio: ''
                    });
                    setTimeout(() => lucide.createIcons(), 50);
                },

                agregarExperiencia() {
                    this.curriculum.experiencia_laboral.push({
                        cargo: '', empresa: '', fecha_inicio: '', fecha_fin: '', actual: false, descripcion: ''
                    });
                    setTimeout(() => lucide.createIcons(), 50);
                },

                agregarTrayectoria() {
                    this.curriculum.participacion_politica.push({
                        rol: '', organizacion: '', anio: '', resultado: ''
                    });
                    setTimeout(() => lucide.createIcons(), 50);
                },

                async guardarCurriculum() {
                    this.savingCurriculum = true;
                    try {
                        const response = await fetch('/api/colaboradores.php?action=curriculum', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(this.curriculum)
                        });

                        const result = await response.json();

                        if (result.success) {
                            alert('Curriculum guardado exitosamente');
                            this.modalCurriculum = false;
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        alert('Error de conexion: ' + error.message);
                    }
                    this.savingCurriculum = false;
                }
            }
        }
    </script>