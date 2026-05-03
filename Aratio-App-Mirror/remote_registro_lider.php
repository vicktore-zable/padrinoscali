<?php
/**
 * PÁGINA PÚBLICA: Registro de Líderes - Versión Premium V2.6.7 Gold
 * UX/UI basado en registro-simpatizante.php
 * Versión: v.2.6.7-gold
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/config.php';

$db = getDB();
$error = null;
$success = false;
$version = '2.6.7-gold';

// Configuración de headers para AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json; charset=utf-8');
}

// === API INTERNA ===

// 1. Obtener Líderes Filtrados por Campaña (AJAX)
if (isset($_GET['action']) && $_GET['action'] === 'lideres') {
    $campanaId = $_GET['campana_id'] ?? null;
    $search = $_GET['search'] ?? '';
    
    if (!$campanaId) {
        echo json_encode(['success' => false, 'message' => 'ID de campaña requerido']);
        exit;
    }

    try {
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

// 2. Obtener Municipios (AJAX)
if (isset($_GET['action']) && $_GET['action'] === 'municipios') {
    $depto = $_GET['departamento'] ?? '';
    try {
        $stmt = $db->prepare("SELECT DISTINCT municipio, cod_mpio FROM territorios WHERE departamento = ? AND municipio != '' ORDER BY municipio");
        $stmt->execute([$depto]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) { echo json_encode(['success' => false]); }
    exit;
}

// 3. Cascada Geográfica Completa (AJAX)
if (isset($_GET['action']) && $_GET['action'] === 'geo_cascada') {
    $cod_mpio = $_GET['cod_mpio'] ?? '';
    $tipo = $_GET['tipo'] ?? null;
    $territorio = $_GET['territorio'] ?? null;
    try {
        if (!$tipo && !$territorio) {
            $stmt = $db->prepare("SELECT DISTINCT Tipo_territorio FROM territorios WHERE cod_mpio = ? AND Tipo_territorio != '' ORDER BY Tipo_territorio");
            $stmt->execute([$cod_mpio]);
        } elseif ($tipo && !$territorio) {
            $stmt = $db->prepare("SELECT DISTINCT Territorio FROM territorios WHERE cod_mpio = ? AND Tipo_territorio = ? AND Territorio != '' ORDER BY Territorio");
            $stmt->execute([$cod_mpio, $tipo]);
        } elseif ($tipo && $territorio) {
            $stmt = $db->prepare("SELECT DISTINCT barrio FROM territorios WHERE cod_mpio = ? AND Tipo_territorio = ? AND Territorio = ? AND barrio != '' ORDER BY barrio");
            $stmt->execute([$cod_mpio, $tipo, $territorio]);
        }
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
    } catch (Exception $e) { echo json_encode(['success' => false]); }
    exit;
}

// 4. Obtener Puestos por cod_mpio (AJAX)
if (isset($_GET['action']) && $_GET['action'] === 'puestos') {
    $cod_mpio = $_GET['cod_mpio'] ?? '';
    try {
        $stmt = $db->prepare("SELECT DISTINCT puesto FROM puestos_votacion WHERE cod_mpio = ? AND estado = 'Activo' ORDER BY puesto");
        $stmt->execute([$cod_mpio]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
    } catch (Exception $e) { echo json_encode(['success' => false]); }
    exit;
}

// 5. Registro Final (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    try {
        if (empty($data['habeas_data'])) throw new Exception("Debe aceptar el tratamiento de datos personales.");
        
        // Validaciones básicas
        $required = ['campana_id', 'nombres', 'apellidos', 'documento', 'municipio', 'departamento'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("El campo $field es obligatorio.");
            }
        }

        // Verificar duplicidad
        $stmt = $db->prepare("SELECT id FROM colaboradores WHERE documento = ? AND campana_id = ?");
        $stmt->execute([$data['documento'], $data['campana_id']]);
        if ($stmt->fetch()) {
            throw new Exception("Ya estás registrado en esta campaña con este documento.");
        }

        $db->beginTransaction();
        
        $stmt = $db->prepare("
            INSERT INTO colaboradores (
                campana_id, nombres, apellidos, tipo_documento, documento,
                fecha_nacimiento, genero, perfil, nivel_participacion,
                departamento, municipio, tipo_territorio, territorio, barrio,
                puesto_votacion, mesa_votacion,
                lider_directo, email, telefono, telefono_whatsapp,
                areas_interes, observaciones, created_at, estado
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, 'Líder / Coordinador', ?,
                ?, ?, ?, ?, ?,
                ?, ?,
                ?, ?, ?, ?,
                ?, ?, NOW(), 'Activo'
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
            $data['nivel_participacion'] ?? 'Lider',
            sanitize($data['departamento']), 
            sanitize($data['municipio']),
            sanitize($data['tipo_territorio']), 
            sanitize($data['territorio']), 
            sanitize($data['barrio']),
            sanitize($data['puesto_votacion']), 
            sanitize($data['mesa_votacion']),
            sanitize($data['lider_directo']), 
            sanitize($data['email']),
            sanitize($data['telefono']), 
            sanitize($data['telefono_whatsapp'] ?? $data['telefono']),
            json_encode($data['areas_interes'] ?? []), 
            "Registro Líder Premium v2.6.7",
        ]);
        
        $cid = $db->lastInsertId();
        
        // Insertar curriculum si existe
        $stmtCV = $db->prepare("INSERT INTO curriculum (colaborador_id, resumen_profesional, formacion_academica, experiencia_laboral, habilidades, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmtCV->execute([
            $cid, 
            sanitize($data['resumen_profesional']), 
            json_encode($data['formaciones'] ?? []), 
            json_encode($data['experiencias'] ?? []), 
            sanitize($data['habilidades'] ?? '')
        ]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => '¡Registro exitoso! Gracias por unirte como líder.']);
        
    } catch (Exception $e) { 
        if ($db->inTransaction()) $db->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]); 
    }
    exit;
}

// Carga Inicial de Datos
try {
    $stmtC = $db->query("SELECT id, nombre, color_primario, color_secundario FROM campanas WHERE estado = 'Activa' ORDER BY nombre");
    $campanas = $stmtC->fetchAll(PDO::FETCH_ASSOC);
    
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
    <title>Únete a la Campaña - Registro de Líderes</title>
    
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
<body class="bg-gray-50 min-h-screen" x-data="liderForm()" x-init="init()">

    <!-- Header / Hero -->
    <div class="gradient-top text-white pb-24 pt-12 px-4 shadow-lg">
        <div class="container mx-auto max-w-4xl text-center">
            <h1 class="text-4xl font-extrabold mb-4">¡Únete al Equipo Ganador!</h1>
            <p class="text-xl opacity-90 mb-6">Registro profesional para líderes y coordinadores de territorio.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="/" class="inline-flex items-center text-white/80 hover:text-white transition-colors border border-white/30 rounded-full px-4 py-2 text-sm font-medium hover:bg-white/10">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Volver al Inicio
                </a>
                <a href="/registro-simpatizante" class="inline-flex items-center bg-white text-purple-700 hover:bg-gray-100 transition-colors rounded-full px-4 py-2 text-sm font-medium shadow-lg">
                    <i data-lucide="users" class="w-4 h-4 mr-2"></i>
                    ¿Eres Simpatizante? Regístrate Aquí
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Campaña de Destino *</label>
                            <select x-model="form.campana_id" @change="resetLider()" required 
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

                        <!-- Líder Referente -->
                        <div x-show="form.campana_id" x-transition.opacity class="mt-6 pt-6 border-t border-gray-200">
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
                                <button type="button" @click="resetLider()" class="ml-3 hover:text-blue-900">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Datos Personales (solo mostrar si hay campaña) -->
                    <div x-show="form.campana_id" x-transition.opacity>
                        
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono / WhatsApp *</label>
                                <input type="tel" x-model="form.telefono" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary" placeholder="300 000 0000">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" x-model="form.email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
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
                                        <template x-for="p in listas.puestos" :key="p.id || p.puesto">
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

                        <!-- Perfil Profesional (Solo para Líderes) -->
                        <div class="bg-purple-50 p-6 rounded-xl border border-purple-100 mb-6">
                            <h3 class="text-lg font-bold text-purple-900 mb-4 flex items-center">
                                <i data-lucide="briefcase" class="w-5 h-5 mr-2 text-purple-600"></i> Perfil Profesional
                            </h3>
                            
                            <!-- Áreas de Interés -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Áreas de Interés / Especialidades</label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="area in allAreas" :key="area">
                                        <button type="button" 
                                                @click="toggleArea(area)" 
                                                class="px-4 py-2 rounded-full text-sm font-medium border-2 transition-all"
                                                :class="form.areas_interes.includes(area) 
                                                    ? 'bg-purple-600 border-purple-600 text-white shadow-lg' 
                                                    : 'bg-white border-gray-200 text-gray-600 hover:border-purple-300'">
                                            <span x-text="area"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Resumen Profesional -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Resumen Profesional</label>
                                <textarea x-model="form.resumen_profesional" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary" placeholder="Describe tu experiencia y capacidades..."></textarea>
                            </div>

                            <!-- Formación Académica -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between border-b border-purple-100 pb-2">
                                    <h4 class="text-sm font-bold text-gray-700">Formación Académica</h4>
                                    <button type="button" @click="addFormacion()" class="text-xs font-bold text-primary">+ Añadir</button>
                                </div>
                                <template x-for="(edu, index) in form.formaciones" :key="index">
                                    <div class="p-4 bg-white rounded-xl border border-gray-200 relative">
                                        <button type="button" @click="removeFormacion(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <input type="text" x-model="edu.titulo" placeholder="Título / Carrera" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                            <input type="text" x-model="edu.institucion" placeholder="Institución" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Experiencia Laboral -->
                            <div class="space-y-4 mt-6">
                                <div class="flex items-center justify-between border-b border-purple-100 pb-2">
                                    <h4 class="text-sm font-bold text-gray-700">Experiencia Laboral</h4>
                                    <button type="button" @click="addExperiencia()" class="text-xs font-bold text-primary">+ Añadir</button>
                                </div>
                                <template x-for="(exp, index) in form.experiencias" :key="'exp'+index">
                                    <div class="p-4 bg-white rounded-xl border border-gray-200 relative">
                                        <button type="button" @click="removeExperiencia(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                        <div class="grid grid-cols-1 gap-3">
                                            <input type="text" x-model="exp.cargo" placeholder="Cargo / Rol" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                            <input type="text" x-model="exp.empresa" placeholder="Empresa / Organización" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Habilidades -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Habilidades Adicionales</label>
                                <input type="text" x-model="form.habilidades" placeholder="Ej. Oratoria, Gestión de equipos, Community Management..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            </div>
                        </div>

                        <!-- Checkbox y Submit -->
                        <div class="mt-8">
                            <label class="flex items-start mb-6 cursor-pointer">
                                <input type="checkbox" x-model="form.habeas_data" required class="mt-1 w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-3 text-sm text-gray-600">
                                    Acepto el tratamiento de mis datos personales conforme a la política de privacidad y autorizo el contacto por parte de la campaña.
                                </span>
                            </label>

                            <button type="submit" 
                                    :disabled="loading || !form.habeas_data" 
                                    class="w-full py-4 px-6 rounded-xl text-white font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed gradient-top">
                                <span x-show="!loading">Finalizar Registro</span>
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
            <p class="text-gray-600 mb-6">Gracias por unirte como líder. Tus datos han sido registrados correctamente.</p>
            <button @click="resetForm()" class="w-full py-3 px-6 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition">
                Cerrar
            </button>
        </div>
    </div>

<script>
function liderForm() {
    return {
        loading: false,
        successModal: false,
        searchLider: '',
        lideresEncontrados: [],
        nombreLiderSeleccionado: '',
        allAreas: ['Gestión Territorial', 'Logística', 'Comunicaciones', 'Redes Sociales', 'Electoral', 'Jurídico', 'Sistemas IT', 'Finanzas', 'Prensa', 'Poblacional', 'Publicidad'],
        
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
            telefono_whatsapp: '',
            departamento: '', 
            municipio: '', 
            cod_mpio: '',
            tipo_territorio: '', 
            territorio: '', 
            barrio: '',
            puesto_votacion: '', 
            mesa_votacion: '',
            lider_directo: '', 
            nivel_participacion: 'Lider',
            resumen_profesional: '', 
            formaciones: [], 
            experiencias: [], 
            areas_interes: [], 
            habeas_data: false, 
            habilidades: ''
        },
        municipio_raw: '',
        
        listas: { 
            municipios: [], 
            tipos: [], 
            territorios: [], 
            barrios: [],
            puestos: [] 
        },

        async init() {
            lucide.createIcons();
            
            // 1. Detección de Parámetros URL (Referidos)
            const urlParams = new URLSearchParams(window.location.search);
            const refLider = urlParams.get('lider');
            const refCampana = urlParams.get('campana');

            // 2. Pre-seleccionar campaña if any
            if (refCampana) {
                this.form.campana_id = refCampana;
            } else {
                // Auto-seleccionar si hay solo una campaña activa
                const select = document.querySelector('select[x-model="form.campana_id"]');
                if (select && select.options.length === 2) {
                    this.form.campana_id = select.options[1].value;
                }
            }

            // 3. Cargar info del líder si viene en URL
            if (refLider) {
                try {
                    // Reutilizamos el endpoint interno para obtener la info
                    const res = await fetch(`registro_simpatizante.php?action=leader_info&documento=${refLider}`);
                    const data = await res.json();
                    if (data.success) {
                        this.form.lider_directo = data.data.documento;
                        this.nombreLiderSeleccionado = data.data.nombre;
                        
                        // Si no especificaron campaña en URL, usar la del líder
                        if (!refCampana && data.data.campana_id) {
                            this.form.campana_id = data.data.campana_id;
                        }
                    }
                } catch (e) { console.error('Error cargando referido:', e); }
            }
        },
        
        async buscarLideres() {
            if (this.searchLider.length < 3) { 
                this.lideresEncontrados = []; 
                return; 
            }
            const res = await fetch(`?action=lideres&campana_id=${this.form.campana_id}&search=${encodeURIComponent(this.searchLider)}`);
            const data = await res.json();
            if (data.success) { 
                this.lideresEncontrados = data.data; 
                setTimeout(() => lucide.createIcons(), 10); 
            }
        },
        
        seleccionarLider(l) { 
            this.form.lider_directo = l.documento; 
            this.nombreLiderSeleccionado = `${l.nombres} ${l.apellidos}`; 
            this.searchLider = ''; 
            this.lideresEncontrados = []; 
        },
        
        resetLider() { 
            this.form.lider_directo = ''; 
            this.nombreLiderSeleccionado = ''; 
            this.searchLider = ''; 
            this.lideresEncontrados = []; 
        },
        
        async cargarMunicipios() {
            this.listas.municipios = []; 
            this.municipio_raw = ''; 
            this.resetGeo();
            
            if(!this.form.departamento) return;
            
            const res = await fetch(`/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.form.departamento)}`);
            const data = await res.json();
            if(data.success) this.listas.municipios = data.data;
        },
        
        resetGeo() {
            this.form.municipio = ''; 
            this.form.cod_mpio = ''; 
            this.form.tipo_territorio = ''; 
            this.form.territorio = ''; 
            this.form.barrio = '';
            this.form.puesto_votacion = '';
            this.form.mesa_votacion = '';
            this.listas.tipos = []; 
            this.listas.territorios = []; 
            this.listas.barrios = [];
            this.listas.puestos = [];
        },
        
        async selectMunicipio() {
            if(!this.municipio_raw) return;
            try {
                const munObj = JSON.parse(this.municipio_raw);
                this.form.municipio = munObj.municipio;
                this.form.cod_mpio = munObj.cod_mpio;
                this.cargarTerritorios();
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
        
        async cargarTerritorios() {
            this.form.territorio = '';
            this.listas.territorios = [];
            
            if(!this.form.municipio) return;
            
            // Cargar tipos de territorio y luego los territorios
            const resTipos = await fetch(`/api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}`);
            const dataTipos = await resTipos.json();
            
            if(dataTipos.success && dataTipos.data.length > 0) {
                // Cargar territorios de todos los tipos y combinarlos
                let allTerritorios = [];
                for(let tipo of dataTipos.data) {
                    const r = await fetch(`/api/territorios.php?accion=territorios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(tipo)}`);
                    const d = await r.json();
                    if(d.success) {
                        d.data.forEach(t => allTerritorios.push({nombre: t, tipo: tipo}));
                    }
                }
                this.listas.territorios = allTerritorios.map(t => t.nombre);
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
            this.form.tipo_territorio = tipo;

            const res = await fetch(`/api/territorios.php?accion=barrios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(tipo)}&territorio=${encodeURIComponent(this.form.territorio)}`);
            const data = await res.json();
            if(data.success) this.listas.barrios = data.data;
        },
        
        toggleArea(area) {
            if(this.form.areas_interes.includes(area)) {
                this.form.areas_interes = this.form.areas_interes.filter(a => a !== area);
            } else {
                this.form.areas_interes.push(area);
            }
        },
        
        addFormacion() { 
            this.form.formaciones.push({titulo: '', institucion: ''}); 
            setTimeout(() => lucide.createIcons(), 10); 
        },
        
        removeFormacion(i) { 
            this.form.formaciones.splice(i, 1); 
        },
        
        addExperiencia() { 
            this.form.experiencias.push({cargo: '', empresa: ''}); 
            setTimeout(() => lucide.createIcons(), 10); 
        },
        
        removeExperiencia(i) { 
            this.form.experiencias.splice(i, 1); 
        },
        
        async submit() {
            this.loading = true;
            try {
                const res = await fetch('', { method: 'POST', body: JSON.stringify(this.form) });
                const data = await res.json();
                if (data.success) {
                    this.successModal = true;
                } else {
                    alert(data.message || 'Error al registrar');
                }
            } catch (e) { 
                alert('Error de red'); 
            }
            this.loading = false;
        },
        
        resetForm() {
            this.successModal = false;
            this.form = { 
                campana_id: '', 
                nombres: '', 
                apellidos: '', 
                tipo_documento: 'CC', 
                documento: '', 
                fecha_nacimiento: '',
                genero: '',
                email: '',
                telefono: '',
                telefono_whatsapp: '',
                departamento: '', 
                municipio: '', 
                cod_mpio: '',
                tipo_territorio: '', 
                territorio: '', 
                barrio: '',
                puesto_votacion: '', 
                mesa_votacion: '',
                lider_directo: '', 
                nivel_participacion: 'Lider',
                resumen_profesional: '', 
                formaciones: [], 
                experiencias: [], 
                areas_interes: [], 
                habeas_data: false, 
                habilidades: ''
            };
            this.listas = { municipios: [], tipos: [], territorios: [], barrios: [], puestos: [] };
            this.municipio_raw = '';
            this.resetLider();
            lucide.createIcons();
        }
    }
}
</script>

</body>
</html>
