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
$version = '2.6.8-gold';

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

// 1.1 Obtener info de un Líder por documento (Referidos)
if (isset($_GET['action']) && $_GET['action'] === 'leader_info') {
    $doc = $_GET['documento'] ?? '';
    try {
        $stmt = $db->prepare("SELECT documento, nombres, apellidos, campana_id FROM colaboradores WHERE documento = ? LIMIT 1");
        $stmt->execute([$doc]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo json_encode([
                'success' => true, 
                'data' => [
                    'documento' => $row['documento'],
                    'nombre' => $row['nombres'] . ' ' . $row['apellidos'],
                    'campana_id' => $row['campana_id']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Líder no encontrado']);
        }
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
        $required = ['campana_id', 'nombres', 'apellidos', 'documento', 'municipio', 'departamento', 'genero'];
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

        // Procesar foto si existe
        $fotoPath = null;
        if (!empty($data['foto'])) {
            $fotoPath = saveBase64Image($data['foto'], 'simp_');
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
                observaciones, created_at, foto
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, 'Simpatizante', 'Simpatizante',
                ?, ?, ?, ?, ?,
                ?, ?,
                ?, ?, ?,
                1, 0,
                ?, NOW(), ?
            )
        ");

        $stmt->execute([
            $data['campana_id'],
            sanitize($data['nombres']),
            sanitize($data['apellidos']),
            $data['tipo_documento'] ?? 'CC',
            sanitize($data['documento']),
            $data['fecha_nacimiento'] ?? null,
            $data['genero'],
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
            "Registro Público Web",
            $fotoPath
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
    <title>Registro de Simpatizantes | Padrinos Cali</title>
    <meta name="description" content="Súmate como simpatizante a la red Padrinos Cali. Juntos transformaremos Cali.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e3a5f',
                        secondary: '#d4af37',
                        accent: '#2c5282',
                        dark: '#0f172a',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
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
        .glass {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .glass-dark {
            background: rgba(30, 58, 95, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .gradient-text {
            background: linear-gradient(135deg, #1e3a5f 0%, #d4af37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-shape {
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0% 100%);
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900 min-h-screen" x-data="simpatizanteForm()" x-init="init()">

    <!-- Header / Hero -->
    <div class="bg-primary text-white pb-32 pt-16 px-6 hero-shape relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-64 h-64 bg-secondary rounded-full blur-[100px]"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-accent rounded-full blur-[80px]"></div>
        </div>

        <div class="w-full mx-auto text-center relative z-10 px-4 md:px-12">
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-md border border-white/20">
                    <i data-lucide="heart" class="w-10 h-10 text-secondary"></i>
                </div>
            </div>
            <h1 class="font-display text-4xl md:text-5xl font-extrabold mb-4 tracking-tight">
                ¡Sé un <span class="text-secondary uppercase">Simpatizante</span> Activo!
            </h1>
            <p class="text-lg md:text-xl text-slate-300 font-light max-w-2xl mx-auto mb-8">
                Regístrate y apoya la visión de Padrinos Cali para transformar nuestra ciudad.
            </p>
            
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/" class="glass-dark text-white hover:bg-white/10 transition-all px-6 py-3 rounded-2xl text-sm font-bold flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-secondary"></i>
                    Volver al Inicio
                </a>
                <a href="registro-lider.php" class="bg-secondary text-primary hover:scale-105 transition-all px-6 py-3 rounded-2xl text-sm font-bold shadow-xl shadow-secondary/20 flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    ¿Eres Padrino? Regístrate Aquí
                </a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="w-full px-4 md:px-12 -mt-16 mb-12 relative z-10">
        
        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-2xl shadow-sm" role="alert">
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <div>
                        <p class="font-bold">Error</p>
                        <p class="text-sm"><?= $error ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl shadow-2xl shadow-slate-200/50 overflow-hidden border border-slate-100">
            <div class="p-6 md:p-10">
                
                <form @submit.prevent="submit()" class="space-y-6">

                    <!-- Selección de Campaña -->
                    <div class="bg-slate-50 p-6 md:p-8 rounded-2xl border border-slate-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-5">
                            <i data-lucide="flag" class="w-24 h-24"></i>
                        </div>
                        
                        <h3 class="text-xl font-display font-bold text-primary mb-6 flex items-center gap-3">
                            <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                <i data-lucide="flag" class="w-4 h-4 text-primary"></i>
                            </span> 
                            Selecciona tu Campaña
                        </h3>
                        
                        <div class="space-y-4">
                            <label class="block text-sm font-semibold text-slate-700">Campaña a la que apoyas *</label>
                            <div class="relative">
                                <select x-model="form.campana_id" @change="cargarLideres()" required 
                                        class="w-full px-4 py-4 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all appearance-none shadow-sm">
                                    <option value="">-- Selecciona una campaña --</option>
                                    <?php foreach ($campanas as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <i data-lucide="chevron-down" class="w-5 h-5"></i>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 flex items-center gap-1.5" x-show="form.campana_id">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-primary"></i> Al seleccionar la campaña, cargaremos los líderes disponibles.
                            </p>
                        </div>
                    </div>

                    <div x-show="form.campana_id" x-transition.opacity>
                        
                        <!-- Referido Por (Líder) -->
                        <div class="mt-8 mb-8 pt-8 border-t border-slate-200">
                            <label class="block text-sm font-semibold text-slate-700 mb-3">¿Quién te invitó? (Líder Referente)</label>
                            <div class="relative group">
                                <i data-lucide="search" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"></i>
                                <input type="text" x-model="searchLider" @input="buscarLideres()" 
                                       placeholder="Busca por nombre del líder..." 
                                       class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all shadow-sm">
                                
                                <!-- Dropdown Resultados -->
                                <div x-show="lideresEncontrados.length > 0" class="absolute z-50 w-full mt-2 bg-white border border-slate-100 rounded-2xl shadow-2xl max-h-64 overflow-y-auto p-2">
                                    <template x-for="l in lideresEncontrados" :key="l.documento">
                                        <div @click="seleccionarLider(l)" class="p-4 hover:bg-slate-50 cursor-pointer rounded-xl transition-all border-b border-slate-50 last:border-0 group/item">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-bold text-slate-800 group-hover/item:text-primary transition-colors" x-text="l.nombres + ' ' + l.apellidos"></p>
                                                    <p class="text-xs text-slate-500" x-text="l.perfil"></p>
                                                </div>
                                                <i data-lucide="user-plus" class="w-4 h-4 text-slate-300 group-hover/item:text-primary transition-all"></i>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Líder Seleccionado Chip -->
                            <div x-show="form.lider_directo" 
                                 class="mt-4 flex items-center justify-between bg-primary/5 text-primary px-5 py-3 rounded-2xl border border-primary/10 animate-fade-in">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center font-bold text-xs" x-text="nombreLiderSeleccionado.charAt(0)"></div>
                                    <div>
                                        <p class="text-[10px] uppercase tracking-wider font-bold opacity-60">Referido por</p>
                                        <p x-text="nombreLiderSeleccionado" class="font-bold"></p>
                                    </div>
                                </div>
                                <button type="button" @click="limpiarLider()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50 hover:text-red-600 transition-all">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 my-6"></div>

                        <!-- Capa de Identidad Visual (Cámara/Subida) -->
                        <div class="bg-slate-50 p-6 md:p-8 rounded-2xl border border-slate-100 mb-8 relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-5">
                                <i data-lucide="camera" class="w-20 h-20"></i>
                            </div>
                            
                            <h3 class="text-xl font-display font-bold text-primary mb-6 flex items-center gap-3">
                                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <i data-lucide="camera" class="w-4 h-4 text-primary"></i>
                                </span> 
                                Foto de Identificación
                            </h3>
                            
                            <div class="flex flex-col items-center">
                                <!-- Vista previa / Placeholder -->
                                <div class="relative w-56 h-56 bg-slate-200 rounded-3xl overflow-hidden mb-6 border-8 border-white shadow-2xl ring-1 ring-slate-100">
                                    <template x-if="!form.foto">
                                        <div class="flex flex-col items-center justify-center h-full text-slate-400 bg-slate-100">
                                            <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center shadow-sm mb-3">
                                                <i data-lucide="user" class="w-10 h-10 opacity-30"></i>
                                            </div>
                                            <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-40">Perfil Sin Imagen</span>
                                        </div>
                                    </template>
                                    <template x-if="form.foto">
                                        <img :src="form.foto" class="w-full h-full object-cover">
                                    </template>
                                    
                                    <!-- Overlay Cámara Activa -->
                                    <div x-show="mostrandoCamara" class="absolute inset-0 bg-dark z-20">
                                        <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                                        <div class="absolute inset-0 border-[20px] border-dark/20 rounded-full pointer-events-none"></div>
                                    </div>
                                </div>

                                <!-- Botones de Acción -->
                                <div class="flex flex-wrap justify-center gap-3 w-full">
                                    <template x-if="!mostrandoCamara">
                                        <button type="button" @click="abrirCamara()" 
                                                class="flex items-center px-6 py-3 bg-primary text-white rounded-2xl hover:bg-accent transition-all text-sm font-bold shadow-lg shadow-primary/20">
                                            <i data-lucide="camera" class="w-4 h-4 mr-2 text-secondary"></i> Abrir Cámara
                                        </button>
                                    </template>
                                    
                                    <template x-if="mostrandoCamara">
                                        <div class="flex gap-2">
                                            <button type="button" @click="capturarFoto()" 
                                                    class="flex items-center px-6 py-3 bg-secondary text-primary rounded-2xl hover:scale-105 transition-all text-sm font-bold shadow-lg shadow-secondary/20">
                                                <i data-lucide="aperture" class="w-4 h-4 mr-2"></i> Capturar
                                            </button>
                                            <button type="button" @click="detenerCamara()" 
                                                    class="flex items-center px-6 py-3 bg-red-500 text-white rounded-2xl hover:bg-red-600 transition-all text-sm font-bold shadow-lg shadow-red-500/20">
                                                <i data-lucide="square" class="w-4 h-4 mr-2"></i> Cancelar
                                            </button>
                                        </div>
                                    </template>

                                    <label class="flex items-center px-6 py-3 bg-white text-slate-700 border border-slate-200 rounded-2xl hover:bg-slate-50 transition-all text-sm font-bold shadow-sm cursor-pointer">
                                        <i data-lucide="upload" class="w-4 h-4 mr-2 text-primary"></i> Subir Foto
                                        <input type="file" class="hidden" @change="handleFileUpload" accept="image/*">
                                    </label>
                                    
                                    <template x-if="form.foto">
                                        <button type="button" @click="form.foto = null" 
                                                class="flex items-center px-6 py-3 text-red-600 hover:bg-red-50 rounded-2xl transition-all text-sm font-bold">
                                            <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Eliminar
                                        </button>
                                    </template>
                                </div>
                                <p class="text-xs text-slate-500 mt-6 text-center max-w-xs leading-relaxed">
                                    <i data-lucide="shield" class="w-3 h-3 inline mr-1 text-primary"></i> Tu foto es tratada de forma confidencial para fines de identificación en la campaña.
                                </p>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 my-6"></div>

                        <!-- Datos Personales -->
                        <div class="bg-slate-50 p-6 md:p-8 rounded-2xl border border-slate-100 mb-8">
                            <h3 class="text-xl font-display font-bold text-primary mb-6 flex items-center gap-3">
                                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <i data-lucide="user" class="w-4 h-4 text-primary"></i>
                                </span> 
                                Datos Personales
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Nombres *</label>
                                    <input type="text" x-model="form.nombres" required 
                                           class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all shadow-sm">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Apellidos *</label>
                                    <input type="text" x-model="form.apellidos" required 
                                           class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all shadow-sm">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Tipo Documento</label>
                                    <select x-model="form.tipo_documento" 
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all appearance-none shadow-sm">
                                        <option value="CC">Cédula de Ciudadanía</option>
                                        <option value="TI">Tarjeta Identidad</option>
                                        <option value="CE">Cédula Extranjería</option>
                                        <option value="PA">Pasaporte</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Documento Número *</label>
                                    <input type="text" x-model="form.documento" required 
                                           class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all shadow-sm">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Teléfono / WhatsApp</label>
                                    <input type="tel" x-model="form.telefono" 
                                           class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all shadow-sm">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Fecha Nacimiento</label>
                                    <input type="date" x-model="form.fecha_nacimiento" 
                                           class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all shadow-sm text-slate-600">
                                </div>
                                <div class="space-y-2 md:col-span-2">
                                    <label class="text-sm font-semibold text-slate-700">Género *</label>
                                    <select x-model="form.genero" required 
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all appearance-none shadow-sm">
                                        <option value="">Seleccionar...</option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <div class="bg-slate-50 p-6 md:p-8 rounded-2xl border border-slate-100 mb-8 relative">
                            <h3 class="text-xl font-display font-bold text-primary mb-6 flex items-center gap-3">
                                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-primary"></i>
                                </span> 
                                Ubicación Geográfica
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Departamento *</label>
                                    <div class="relative">
                                        <select x-model="form.departamento" @change="cargarMunicipios()" required 
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all appearance-none shadow-sm">
                                            <option value="">Seleccionar...</option>
                                            <?php foreach ($departamentos as $dep): ?>
                                                <option value="<?= htmlspecialchars($dep) ?>"><?= htmlspecialchars($dep) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Municipio *</label>
                                    <div class="relative">
                                        <select x-model="municipio_raw" @change="selectMunicipio()" required :disabled="!form.departamento" 
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all appearance-none shadow-sm disabled:bg-slate-100 disabled:text-slate-400">
                                            <option value="">Seleccionar...</option>
                                            <template x-for="mun in listas.municipios" :key="mun.cod_mpio">
                                                <option :value="JSON.stringify(mun)" x-text="mun.municipio"></option>
                                            </template>
                                        </select>
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Comuna / Corregimiento</label>
                                    <div class="relative">
                                        <select x-model="form.territorio" @change="cargarBarrios()" :disabled="!form.municipio" 
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all appearance-none shadow-sm disabled:bg-slate-100">
                                            <option value="">Seleccionar...</option>
                                            <template x-for="terr in listas.territorios" :key="terr">
                                                <option :value="terr" x-text="terr"></option>
                                            </template>
                                        </select>
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Barrio / Vereda</label>
                                    <div class="relative">
                                        <select x-model="form.barrio" :disabled="!form.territorio" 
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all appearance-none shadow-sm disabled:bg-slate-100">
                                            <option value="">Seleccionar...</option>
                                            <template x-for="b in listas.barrios" :key="b">
                                                <option :value="b" x-text="b"></option>
                                            </template>
                                        </select>
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Electoral -->
                        <div class="bg-primary/5 p-6 md:p-8 rounded-2xl border border-primary/10 mb-8">
                            <h3 class="text-xl font-display font-bold text-primary mb-6 flex items-center gap-3">
                                <span class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <i data-lucide="vote" class="w-4 h-4 text-primary"></i>
                                </span> 
                                Información Electoral
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Puesto de Votación *</label>
                                    <div class="relative">
                                        <select x-model="form.puesto_votacion" required :disabled="!form.municipio" 
                                                class="w-full px-4 py-4 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all appearance-none shadow-sm disabled:bg-slate-100">
                                            <option value="">Seleccionar...</option>
                                            <template x-for="p in listas.puestos" :key="p.id">
                                                <option :value="p.puesto" x-text="p.puesto"></option>
                                            </template>
                                        </select>
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Mesa de Votación</label>
                                    <input type="text" x-model="form.mesa_votacion" placeholder="Ej. 14" 
                                           class="w-full px-4 py-4 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all shadow-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Checkbox y Submit -->
                        <div class="mt-12 bg-slate-50 p-6 md:p-8 rounded-3xl border border-slate-100">
                            <label class="flex items-start mb-8 cursor-pointer group">
                                <div class="relative flex items-center justify-center">
                                    <input type="checkbox" required 
                                           class="peer w-6 h-6 text-primary border-slate-300 rounded-lg focus:ring-primary/20 transition-all cursor-pointer">
                                    <i data-lucide="check" class="w-4 h-4 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                </div>
                                <span class="ml-4 text-sm text-slate-600 leading-relaxed group-hover:text-slate-900 transition-colors">
                                    Acepto el tratamiento de mis datos personales conforme a la <span class="font-bold text-primary underline">política de privacidad</span> y autorizo el contacto por parte de la campaña.
                                </span>
                            </label>

                            <button type="submit" 
                                    :disabled="loading" 
                                    class="w-full py-5 px-8 rounded-2xl text-primary font-extrabold text-xl shadow-xl shadow-secondary/20 hover:shadow-secondary/40 transform hover:-translate-y-1 transition-all disabled:opacity-50 disabled:cursor-not-allowed bg-secondary flex items-center justify-center gap-3">
                                <span x-show="!loading">Confirmar Mi Registro</span>
                                <i x-show="!loading" data-lucide="arrow-right" class="w-6 h-6"></i>
                                <span x-show="loading" class="flex items-center justify-center">
                                    <i data-lucide="loader-2" class="animate-spin w-6 h-6 mr-2"></i> Procesando...
                                </span>
                            </button>
                        </div>

                    </div>
                    
                </form>
            </div>
        </div>
    </div>

    <footer class="mt-12 py-12 text-center relative z-20 border-t border-slate-100">
        <div class="flex flex-col items-center gap-6">
            <div class="flex items-center gap-4 opacity-50 grayscale hover:grayscale-0 transition-all">
                <img src="assets/images/logo-aratio.png" alt="Aratio" class="h-6">
            </div>
            <p class="text-[10px] font-bold uppercase text-slate-400 tracking-[0.4em]">Aratio Intelligent Systems • 2024</p>
            <div class="flex gap-2">
                <span class="px-4 py-1.5 bg-white border border-slate-100 text-slate-400 text-[10px] font-bold rounded-full shadow-sm">
                    Versión <?= $version ?>
                </span>
                <span class="px-4 py-1.5 bg-primary/5 text-primary text-[10px] font-bold rounded-full border border-primary/10">
                    Premium Interface
                </span>
            </div>
        </div>
    </footer>

    <!-- Modal Success -->
    <div x-show="successModal" 
         style="display: none;" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-primary/40 backdrop-blur-md" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-[2.5rem] shadow-2xl p-10 max-w-md w-full text-center relative overflow-hidden"
             @click.away="resetForm()"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="scale-90 opacity-0 translate-y-10"
             x-transition:enter-end="scale-100 opacity-100 translate-y-0">
            
            <!-- Background Decoration -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-secondary/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-primary/5 rounded-full blur-3xl"></div>

            <div class="relative">
                <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-8 shadow-xl shadow-green-200">
                    <i data-lucide="check" class="w-12 h-12 text-white"></i>
                </div>
                
                <h2 class="text-3xl font-display font-black text-primary mb-4">¡Registro Exitoso!</h2>
                <p class="text-slate-600 mb-10 leading-relaxed">
                    Gracias por unirte a la red de <span class="font-bold text-primary">Padrinos Cali</span>. Tus datos han sido registrados correctamente como simpatizante.
                </p>
                
                <button @click="resetForm()" 
                        class="w-full py-4 px-8 bg-primary text-secondary rounded-2xl font-black text-lg hover:bg-slate-800 transition-all shadow-lg shadow-primary/20">
                    Finalizar y Volver
                </button>
            </div>
        </div>
    </div>

<script>
function simpatizanteForm() {
    return {
        loading: false,
        successModal: false,
        mostrandoCamara: false,
        videoStream: null,
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
            cod_mpio: '',
            foto: null
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

        async abrirCamara() {
            try {
                this.mostrandoCamara = true;
                this.videoStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: "user", width: 480, height: 480 } 
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

        handleFileUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (event) => {
                this.form.foto = event.target.result;
            };
            reader.readAsDataURL(file);
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
