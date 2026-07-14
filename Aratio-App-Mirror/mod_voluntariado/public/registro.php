<?php
require_once __DIR__ . '/../../config/config.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $comuna = trim($_POST['comuna'] ?? '');
    $intereses = $_POST['intereses'] ?? [];
    $disponibilidad_dias = $_POST['disponibilidad_dias'] ?? [];
    $disponibilidad_jornada = trim($_POST['disponibilidad_jornada'] ?? '');

    if (empty($nombres) || empty($documento) || empty($telefono) || empty($comuna)) {
        $error = 'Por favor completa todos los campos obligatorios.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT id FROM colaboradores WHERE documento = ?");
            $stmt->execute([$documento]);
            if ($stmt->fetch()) {
                $error = 'Ya existe un registro con este documento.';
            } else {
                $stmt = $db->prepare("
                    INSERT INTO colaboradores 
                        (nombres, documento, telefono, municipio, territorio, perfil, 
                         voluntario_fase, voluntario_intereses, voluntario_disponibilidad, 
                         created_at, updated_at, tipo_documento, fecha_nacimiento, genero, nivel_participacion)
                    VALUES (?, ?, ?, 'Cali', ?, 'Voluntario', 'asignado', ?, ?, NOW(), NOW(), 
                            'CC', '2000-01-01', 'Prefiero no decir', 'Simpatizante')
                ");
                $stmt->execute([
                    $nombres,
                    $documento,
                    $telefono,
                    $comuna,
                    json_encode($intereses),
                    json_encode([
                        'dias' => $disponibilidad_dias,
                        'jornada' => $disponibilidad_jornada
                    ])
                ]);
                $success = true;
            }
        } catch (Exception $e) {
            $error = 'Error al registrar. Intenta de nuevo.';
        }
    }
}

$comunas = [];
for ($i = 1; $i <= 22; $i++) {
    $comunas[] = 'Comuna ' . $i;
}
$comunas[] = 'Corregimiento';
$comunas[] = 'Otro municipio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiero ser Voluntario — Padrinos Cali</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <?php if ($success): ?>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl p-10 max-w-md w-full text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="font-display text-2xl font-extrabold text-gray-900 mb-2">¡Gracias por sumarte!</h1>
            <p class="text-gray-500 mb-6">Un padrino de tu comuna se comunicará contigo pronto para darte la bienvenida.</p>
            <a href="/aratio/" class="inline-block bg-primary text-white px-6 py-3 rounded-xl font-semibold hover:bg-slate-800 transition-all">
                Volver al inicio
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10 max-w-lg w-full">
            <div class="text-center mb-8">
                <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="heart-handshake" class="w-8 h-8 text-primary"></i>
                </div>
                <h1 class="font-display text-3xl font-extrabold text-gray-900">Quiero ser Voluntario</h1>
                <p class="text-gray-500 mt-2">Únete a la red de voluntarios de Padrinos Cali y transforma tu comuna.</p>
            </div>

            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre completo *</label>
                    <input type="text" name="nombres" required value="<?= htmlspecialchars($_POST['nombres'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                           placeholder="Tu nombre y apellido">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Documento de identidad *</label>
                    <input type="text" name="documento" required value="<?= htmlspecialchars($_POST['documento'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                           placeholder="CC 123456789">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono / WhatsApp *</label>
                    <input type="tel" name="telefono" required value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                           placeholder="300 123 4567">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Comuna donde vives *</label>
                    <select name="comuna" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all bg-white">
                        <option value="">Selecciona tu comuna</option>
                        <?php foreach ($comunas as $c): ?>
                        <option value="<?= $c ?>" <?= ($_POST['comuna'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">¿En qué áreas te gustaría ayudar?</label>
                    <div class="grid grid-cols-2 gap-3">
                        <?php
                        $areas = [
                            'brigadas' => 'Brigadas',
                            'talleres' => 'Talleres',
                            'logistica' => 'Logística',
                            'formacion' => 'Formación',
                            'ambiental' => 'Ambiental'
                        ];
                        $selected = $_POST['intereses'] ?? [];
                        foreach ($areas as $val => $label): ?>
                        <label class="flex items-center gap-2 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-primary/5 transition-colors">
                            <input type="checkbox" name="intereses[]" value="<?= $val ?>"
                                   <?= in_array($val, $selected) ? 'checked' : '' ?>
                                   class="w-4 h-4 text-primary focus:ring-primary rounded">
                            <span class="text-sm text-gray-700"><?= $label ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Disponibilidad</label>
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <?php
                        $dias = ['lun' => 'Lun', 'mar' => 'Mar', 'mie' => 'Mié', 'jue' => 'Jue', 'vie' => 'Vie', 'sab' => 'Sáb'];
                        $selDias = $_POST['disponibilidad_dias'] ?? [];
                        foreach ($dias as $val => $label): ?>
                        <label class="flex items-center gap-1.5 p-2 rounded-lg border border-gray-200 cursor-pointer hover:bg-primary/5 transition-colors text-sm">
                            <input type="checkbox" name="disponibilidad_dias[]" value="<?= $val ?>"
                                   <?= in_array($val, $selDias) ? 'checked' : '' ?>
                                   class="w-3.5 h-3.5 text-primary focus:ring-primary rounded">
                            <?= $label ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <select name="disponibilidad_jornada"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all bg-white">
                        <option value="">Jornada preferida</option>
                        <option value="manana" <?= ($_POST['disponibilidad_jornada'] ?? '') === 'manana' ? 'selected' : '' ?>>Mañana</option>
                        <option value="tarde" <?= ($_POST['disponibilidad_jornada'] ?? '') === 'tarde' ? 'selected' : '' ?>>Tarde</option>
                        <option value="noche" <?= ($_POST['disponibilidad_jornada'] ?? '') === 'noche' ? 'selected' : '' ?>>Noche</option>
                        <option value="flexible" <?= ($_POST['disponibilidad_jornada'] ?? '') === 'flexible' ? 'selected' : '' ?>>Flexible</option>
                    </select>
                </div>

                <button type="submit"
                        class="w-full bg-primary text-white py-3.5 rounded-xl font-bold text-lg hover:bg-slate-800 transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-3">
                    <i data-lucide="heart-handshake" class="w-5 h-5"></i>
                    Quiero ser Voluntario
                </button>

                <p class="text-xs text-gray-400 text-center">Tus datos están seguros. Solo los usaremos para contactarte y asignarte un padrino.</p>
            </form>

            <div class="mt-6 text-center">
                <a href="/aratio/" class="text-sm text-gray-500 hover:text-primary transition-colors">&larr; Volver al inicio</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>lucide.createIcons();</script>
</body>
</html>
