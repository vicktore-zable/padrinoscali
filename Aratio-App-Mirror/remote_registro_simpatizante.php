<?php
/**
 * PÁGINA PÚBLICA: Registro de Simpatizantes
 * Accesible sin autenticación
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/config.php';

$db = getDB();
$error = null;
$success = false;
$version = '2.6.6-gold';

// Configuración de headers para AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json; charset=utf-8');
}

// === API INTERNA ===

// 1. Obtener Líderes (AJAX)
if (isset($_GET['action']) && $_GET['action'] === 'lideres') {
    $campanaId = $_GET['campana_id'] ?? null;
    $search = $_GET['search'] ?? '';

    if (!$campanaId) {
        echo json_encode(['success' => false, 'message' => 'ID de campaña requerido']);
        exit;
    }

    try {
        // Verificar si existe tabla de relación campana_colaboradores para filtrar mejor
        // Si no, usar campana_id de la tabla colaboradores
        
        $sql = "
            SELECT documento, nombres, apellidos, perfil
            FROM colaboradores
            WHERE campana_id = ?
            AND perfil LIKE '%Lider%'
            AND estado != 'Desvinculado'
        ";
        $params = [$campanaId];

        if ($search) {
            $sql .= " AND (nombres LIKE ? OR apellidos LIKE ? OR documento LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY nombres, apellidos LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $lideres = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $lideres]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// 2. Procesar Registro (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Validaciones Básicas
        $required = ['campana_id', 'nombres', 'apellidos', 'documento', 'municipio', 'departamento'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("El campo $field es obligatorio.");
            }
        }

        // Verificar duplicidad en la campaña
        $stmt = $db->prepare("SELECT id FROM colaboradores WHERE documento = ? AND campana_id = ?");
        $stmt->execute([$data['documento'], $data['campana_id']]);
        if ($stmt->fetch()) {
            throw new Exception("Ya estás registrado en esta campaña con este documento.");
        }

        // Insertar Colaborador
        // Usamos valores por defecto para Simpatizante
        $stmt = $db->prepare("
            INSERT INTO colaboradores (
                campana_id, nombres, apellidos, tipo_documento, documento,
                fecha_nacimiento, genero, perfil, nivel_participacion,
                departamento, municipio, tipo_territorio, territorio, barrio,
                puesto_votacion, mesa_votacion,
                lider_directo, email, telefono,
                dato_potencial, dato_historico,
                observaciones, created_at
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, 'Simpatizante', 'Simpatizante',
                ?, ?, ?, ?, ?,
                ?, ?,
                ?, ?, ?,
                1, 0,
                ?, NOW()
            )
        ");

        $stmt->execute([
            $data['campana_id'],
            sanitize($data['nombres']),
            sanitize($data['apellidos']),
            $data['tipo_documento'] ?? 'CC',
            sanitize($data['documento']),
            $data['fecha_nacimiento'] ?? null,
            $data['genero'] ?? null,
            sanitize($data['departamento']),
            sanitize($data['municipio']),
            sanitize($data['tipo_territorio'] ?? null),
            sanitize($data['territorio'] ?? null),
            sanitize($data['barrio'] ?? null),
            sanitize($data['puesto_votacion'] ?? null),
            sanitize($data['mesa_votacion'] ?? null),
            sanitize($data['lider_directo'] ?? null),
            sanitize($data['email'] ?? null),
            sanitize($data['telefono'] ?? null),
            "Registro Público Web"
        ]);
        
        $newId = $db->lastInsertId();

        // Intentar vincular a la tabla campana_colaboradores si existe
        // Esto es para soportar la lógica futura de Múltiples Campañas
        try {
            $checkTable = $db->query("SHOW TABLES LIKE 'campana_colaboradores'");
            if ($checkTable->rowCount() > 0) {
                $stmtLink = $db->prepare("
                    INSERT INTO campana_colaboradores (campana_id, colaborador_id, rol, estado, fecha_vinculacion)
                    VALUES (?, ?, 'Simpatizante', 'Activo', NOW())
                ");
                $stmtLink->execute([$data['campana_id'], $newId]);
            }
        } catch (Exception $e) {
            // Ignorar error de vinculación opcional
        }

        echo json_encode(['success' => true, 'message' => '¡Registro exitoso! Gracias por unirte.']);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// === Carga Inicial de Datos (GET Normal) ===
try {
    // Cargar campañas activas
    $stmtC = $db->query("SELECT id, nombre, color_primario, color_secundario FROM campanas WHERE estado = 'Activa' ORDER BY nombre");
    $campanas = $stmtC->fetchAll(PDO::FETCH_ASSOC);
    
    // Cargar departamentos para inicializar el select
    $stmtD = $db->query("SELECT DISTINCT departamento FROM territorios WHERE departamento IS NOT NULL AND departamento != '' ORDER BY departamento");
    $departamentos = $stmtD->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    $error = "Error cargando sistema: " . $e->getMessage();
    $campanas = [];
    $departamentos = [];
}



?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Únete a la Campaña - Registro Simpatizante</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF00FF', // Magenta Aratio
                        secondary: '#FFD700', // Dorado Aratio
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        .gradient-top {
            background: linear-gradient(135deg, #FF00FF 0%, #FFD700 100%);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="simpatizanteForm()" x-init="init()">

    <!-- Header / Hero -->
    <div class="gradient-top text-white pb-24 pt-12 px-4 shadow-lg">
        <div class="container mx-auto max-w-4xl text-center">
            <h1 class="text-4xl font-extrabold mb-4">¡Únete al Cambio!</h1>
            <p class="text-xl opacity-90 mb-6">Regístrate como simpatizante y sé parte de nuestro movimiento.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="/" class="inline-flex items-center text-white/80 hover:text-white transition-colors border border-white/30 rounded-full px-4 py-2 text-sm font-medium hover:bg-white/10">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Volver al Inicio
                </a>
                <a href="/registro-lider" class="inline-flex items-center bg-white text-purple-700 hover:bg-gray-100 transition-colors rounded-full px-4 py-2 text-sm font-medium shadow-lg">
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                    ¿Eres Líder? Registra tu Hoja de Vida
                </a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container mx-auto max-w-3xl px-4 -mt-16 mb-12 relative z-10">
        
        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow" role="alert">
                <p class="font-bold">Error</p>
                <p><?= $error ?></p>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-8">
                
                <form @submit.prevent="submit()" class="space-y-6">

                    <!-- Selección de Campaña -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i data-lucide="flag" class="w-5 h-5 mr-2 text-primary"></i> Selecciona tu Campaña
                        </h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Campaña a la que apoyas *</label>
                            <select x-model="form.campana_id" @change="cargarLideres()" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white shadow-sm">
                                <option value="">-- Selecciona una campaña --</option>
                                <?php foreach ($campanas as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-2" x-show="form.campana_id">
                                <i data-lucide="info" class="w-3 h-3 inline"></i> Al seleccionar la campaña, cargaremos los líderes disponibles.
                            </p>
                        </div>
                    </div>

                    <div x-show="form.campana_id" x-transition.opacity>
                        
                        <!-- Referido Por (Líder) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">¿Quién te invitó? (Líder Referente)</label>
                            <div class="relative">
                                <i data-lucide="search" class="w-5 h-5 absolute left-3 top-3.5 text-gray-400"></i>
                                <input type="text" x-model="searchLider" @input="buscarLideres()" 
                                       placeholder="Busca por nombre del líder..." 
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                
                                <!-- Dropdown Resultados -->
                                <div x-show="lideresEncontrados.length > 0" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                                    <template x-for="l in lideresEncontrados" :key="l.documento">
                                        <div @click="seleccionarLider(l)" class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-0 transition-colors">
                                            <p class="font-bold text-gray-800" x-text="l.nombres + ' ' + l.apellidos"></p>
                                            <p class="text-xs text-gray-500">
                                                <span x-text="l.perfil"></span>
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <!-- Líder Seleccionado Chip -->
                            <div x-show="form.lider_directo" class="mt-3 flex items-center bg-blue-50 text-blue-700 px-4 py-2 rounded-full inline-flex border border-blue-100">
                                <i data-lucide="user-check" class="w-4 h-4 mr-2"></i>
                                <span class="font-medium mr-2">Referido por:</span>
                                <span x-text="nombreLiderSeleccionado" class="font-bold"></span>
                                <button type="button" @click="limpiarLider()" class="ml-3 hover:text-blue-900">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 my-6"></div>

                        <!-- Datos Personales -->
                        <div class="md:grid md:grid-cols-2 md:gap-6 space-y-4 md:space-y-0 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombres *</label>
                                <input type="text" x-model="form.nombres" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos *</label>
                                <input type="text" x-model="form.apellidos" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Documento</label>
                                <select x-model="form.tipo_documento" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                    <option value="CC">Cédula de Ciudadanía</option>
                                    <option value="TI">Tarjeta Identidad</option>
                                    <option value="CE">Cédula Extranjería</option>
                                    <option value="PA">Pasaporte</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Documento Número *</label>
                                <input type="text" x-model="form.documento" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono / WhatsApp</label>
                                <input type="tel" x-model="form.telefono" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Nacimiento</label>
                                <input type="date" x-model="form.fecha_nacimiento" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                                <select x-model="form.genero" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                    <option value="">Prefiero no decir</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 mb-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                <i data-lucide="map-pin" class="w-5 h-5 mr-2 text-primary"></i> Ubicación
                            </h3>
                            <div class="md:grid md:grid-cols-2 md:gap-6 space-y-4 md:space-y-0">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Departamento *</label>
                                    <select x-model="form.departamento" @change="cargarMunicipios()" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($departamentos as $dep): ?>
                                            <option value="<?= htmlspecialchars($dep) ?>"><?= htmlspecialchars($dep) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Municipio *</label>
                                    <select x-model="municipio_raw" @change="selectMunicipio()" required :disabled="!form.departamento" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary disabled:bg-gray-100">
                                        <option value="">Seleccionar...</option>
                                        <template x-for="mun in listas.municipios" :key="mun.cod_mpio">
                                            <option :value="JSON.stringify(mun)" x-text="mun.municipio"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Comuna / Corregimiento</label>
                                    <select x-model="form.territorio" @change="cargarBarrios()" :disabled="!form.municipio" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary disabled:bg-gray-100">
                                        <option value="">Seleccionar...</option>
                                        <template x-for="terr in listas.territorios" :key="terr">
                                            <option :value="terr" x-text="terr"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Barrio / Vereda</label>
                                    <select x-model="form.barrio" :disabled="!form.territorio" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary disabled:bg-gray-100">
                                        <option value="">Seleccionar...</option>
                                        <template x-for="b in listas.barrios" :key="b">
                                            <option :value="b" x-text="b"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Info Electoral -->
                        <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 mb-6">
                            <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center">
                                <i data-lucide="vote" class="w-5 h-5 mr-2 text-blue-600"></i> Información de Votación
                            </h3>
                            <div class="md:grid md:grid-cols-2 md:gap-6 space-y-4 md:space-y-0">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Puesto de Votación *</label>
                                    <select x-model="form.puesto_votacion" required :disabled="!form.municipio" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary disabled:bg-gray-100 shadow-sm">
                                        <option value="">Seleccionar...</option>
                                        <template x-for="p in listas.puestos" :key="p.id">
                                            <option :value="p.puesto" x-text="p.puesto"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mesa de Votación</label>
                                    <input type="text" x-model="form.mesa_votacion" placeholder="Ej. 14" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                                </div>
                            </div>
                        </div>

                        <!-- Checkbox y Submit -->
                        <div class="mt-8">
                            <label class="flex items-start mb-6 cursor-pointer">
                                <input type="checkbox" required class="mt-1 w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-3 text-sm text-gray-600">
                                    Acepto el tratamiento de mis datos personales conforme a la política de privacidad y autorizo el contacto por parte de la campaña.
                                </span>
                            </label>

                            <button type="submit" 
                                    :disabled="loading" 
                                    class="w-full py-4 px-6 rounded-xl text-white font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed gradient-top">
                                <span x-show="!loading">Confirmar Registro</span>
                                <span x-show="loading" class="flex items-center justify-center">
                                    <i data-lucide="loader-2" class="animate-spin w-5 h-5 mr-2"></i> Procesando...
                                </span>
                            </button>
                        </div>

                    </div>
                    
                </form>
            </div>
        </div>
    </div>

    <footer class="mt-10 py-12 text-center relative z-20">
        <p class="text-[10px] font-black uppercase text-slate-300 tracking-[0.4em] mb-4">Aratio Intelligent Systems</p>
        <span class="px-5 py-2 bg-white border border-slate-100 text-slate-400 text-[10px] font-bold rounded-full shadow-sm">
            Digital Version: <?= $version ?>
        </span>
    </footer>

    <!-- Modal Success -->
    <div x-show="successModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-60 backdrop-blur-sm" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full text-center transform scale-100 transition-transform">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="check" class="w-10 h-10 text-green-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">¡Registro Exitoso!</h2>
            <p class="text-gray-600 mb-6">Gracias por unirte a nuestra campaña. Tus datos han sido registrados correctamente.</p>
            <button @click="resetForm()" class="w-full py-3 px-6 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition">
                Cerrar
            </button>
        </div>
    </div>

<script>
function simpatizanteForm() {
    return {
        loading: false,
        successModal: false,
        searchLider: '',
        lideresEncontrados: [],
        nombreLiderSeleccionado: '',
        
        form: {
            campana_id: '',
            nombres: '',
            apellidos: '',
            tipo_documento: 'CC',
            documento: '',
            fecha_nacimiento: '',
            genero: '',
            email: '',
            telefono: '',
            departamento: '',
            municipio: '',
            tipo_territorio: '', // Comuna/Corr
            territorio: '', // Nombre Comuna
            barrio: '', 
            puesto_votacion: '',
            mesa_votacion: '',
            lider_directo: '',
            cod_mpio: ''
        },
        municipio_raw: '',

        listas: {
            municipios: [],
            territorios: [], // Comunas
            barrios: [],
            puestos: []
        },

        init() {
            lucide.createIcons();
            // Pre-seleccionar si hay solo 1 campaña
            const select = document.querySelector('select[x-model="form.campana_id"]');
            if (select && select.options.length === 2) {
                this.form.campana_id = select.options[1].value;
            }
        },

        async buscarLideres() {
            if (this.searchLider.length < 3) {
                this.lideresEncontrados = [];
                return;
            }
            try {
                const res = await fetch(`?action=lideres&campana_id=${this.form.campana_id}&search=${encodeURIComponent(this.searchLider)}`);
                const data = await res.json();
                if (data.success) {
                    this.lideresEncontrados = data.data;
                }
            } catch (e) { console.error(e); }
        },

        seleccionarLider(l) {
            this.form.lider_directo = l.documento;
            this.nombreLiderSeleccionado = l.nombres + ' ' + l.apellidos;
            this.searchLider = '';
            this.lideresEncontrados = [];
        },

        limpiarLider() {
            this.form.lider_directo = '';
            this.nombreLiderSeleccionado = '';
        },

        async cargarMunicipios() {
            // Reset
            this.listas.municipios = [];
            this.form.municipio = '';
            this.form.cod_mpio = '';
            this.municipio_raw = '';
            this.form.puesto_votacion = '';
            this.listas.puestos = [];
            
            if(!this.form.departamento) return;
            
            const res = await fetch(`/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.form.departamento)}`);
            const data = await res.json();
            if(data.success) this.listas.municipios = data.data;
        },

        async selectMunicipio() {
            if(!this.municipio_raw) return;
            try {
                const munObj = JSON.parse(this.municipio_raw);
                this.form.municipio = munObj.municipio;
                this.form.cod_mpio = munObj.cod_mpio;
                this.cargarTiposTerritorio();
                this.cargarPuestos();
            } catch(e) { console.error(e); }
        },

        async cargarPuestos() {
            this.listas.puestos = [];
            this.form.puesto_votacion = '';
            
            if(!this.form.cod_mpio) return;
            
            const res = await fetch(`/api/territorios.php?accion=puestos&cod_mpio=${encodeURIComponent(this.form.cod_mpio)}`);
            const data = await res.json();
            if(data.success) this.listas.puestos = data.data;
        },

        async cargarTiposTerritorio() {
             // this.cargarPuestos(); // Ya se llama en selectMunicipio
             // En Aratio, la carga de territorios (comunas) depende del tipo
             // Aquí simplificamos: cargamos primero los tipos (Urbano/Rural) y luego pedimos los territorios
             // Pero para UX rápida, intentaremos cargar 'territorios' asumiendo Urbano/Rural iterate?
             // Mejor seguimos el flujo: -> Cargar Tipos -> Usuario elige -> Cargar Territorios
             
             // NOTA: Para este formulario simplificado, saltaremos "Tipo Territorio" explícito si podemos
             // Pero la API de territorios requiere tipo.
             // Vamos a intentar inferir o cargar "territorios" de todos los tipos? No, la API restringe.
             // Agregaremos select oculto o automático si se requiere.
             // Vamos a cargar Tipos disponibles primero, si hay 1 solo lo seleccionamos.
             
             this.form.territorio = '';
             this.listas.territorios = [];
             
             if(!this.form.municipio) return;

             // Hack: vamos a pedir "tipos_territorio" y si existe, iteramos para cargar sus territorios? 
             // Mejor implementamos la cascada completa simplificada en UI
             
             // Fetch tipos
             const res = await fetch(`/api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}`);
             const data = await res.json();
             
             if(data.success && data.data.length > 0) {
                 // Por defecto tomamos el primero si hay, o pedimos al usuario?
                 // El formulario original tenía 5 niveles. Aquí tratamos de simplificar a 4.
                 // Vamos a cargar todos los territorios de todos los tipos encontrados?
                 // No, haremos cascada real detrás de escena o pedimos al usuario.
                 // Para simplificar, asumimos que cargamos territorios del primer tipo encontrado (ej Urbano)
                 // O mejor: agregamos el campo Tipo Territorio hidden o visible
                 
                 // Visible:
                 // Reutilizamos la lógica del legacy form
                 
                 // Para este script, vamos a cargar COMUNAS directamente si es posible? No.
                 // Ok, cargamos territorios del primer tipo disponible para no complicar al usuario?
                 // MALA IDEA. El usuario vive en corregimiento.
                 
                 // Solución: Cargamos los territorios de "Urbano" y "Rural" y los mezclamos?
                 // La API filtra por tipo.
                 
                 // Vamos a hacer un fetch secuencial de todos los tipos y unir los territorios?
                 let allTerritorios = [];
                 for(let tipo of data.data) {
                     const r = await fetch(`/api/territorios.php?accion=territorios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(tipo)}`);
                     const d = await r.json();
                     if(d.success) {
                         // Agregamos prefijo tipo para distinguir?
                         d.data.forEach(t => allTerritorios.push({nombre: t, tipo: tipo}));
                     }
                 }
                 this.listas.territorios = allTerritorios.map(t => t.nombre); 
                 // Guardamos el tipo asociado en un mapa auxiliar o inferimos al seleccionar
                 this._territorioTipoMap = allTerritorios.reduce((acc, item) => { acc[item.nombre] = item.tipo; return acc; }, {});
             }
        },
        
        _territorioTipoMap: {},

        async cargarBarrios() {
            this.listas.barrios = [];
            this.form.barrio = '';
            
            if(!this.form.territorio) return;
            
            // Inferir tipo
            const tipo = this._territorioTipoMap[this.form.territorio];
            this.form.tipo_territorio = tipo; // Guardar el tipo oculto

            const res = await fetch(`/api/territorios.php?accion=barrios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(tipo)}&territorio=${encodeURIComponent(this.form.territorio)}`);
            const data = await res.json();
            if(data.success) this.listas.barrios = data.data;
        },

        async submit() {
            this.loading = true;
            try {
                const res = await fetch('', { // Post to self
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                
                if (data.success) {
                    this.successModal = true;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                alert('Error de conexión');
            }
            this.loading = false;
        },

        resetForm() {
            this.successModal = false;
            // Reset fields but keep campaign?
            this.form.nombres = '';
            this.form.apellidos = '';
            this.form.documento = '';
            this.form.email = '';
            this.form.telefono = '';
            this.form.puesto_votacion = '';
            this.form.mesa_votacion = '';
            window.scrollTo(0, 0);
        }
    }
}
</script>
</body>
</html>
