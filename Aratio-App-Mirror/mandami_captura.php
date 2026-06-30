<?php
require_once __DIR__ . '/config/config.php';

$fbUserId = $_GET['fb_user_id'] ?? '';
$userName = $_GET['user_name'] ?? '';
$keyword = $_GET['keyword'] ?? '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres'] ?? '');
    $comuna = trim($_POST['comuna'] ?? '');
    $celular = trim($_POST['celular'] ?? '');
    $fbUserId = $_POST['fb_user_id'] ?? '';
    $keyword = $_POST['keyword'] ?? '';

    if (empty($nombres)) {
        $error = 'Por favor ingresa tu nombre';
    } else {
        require_once __DIR__ . '/includes/MessengerBot.php';
        $db = getDB();
        $bot = new MessengerBot($db);
        $result = $bot->registerCapture([
            'fb_user_id' => $fbUserId,
            'user_name' => $userName,
            'nombres' => $nombres,
            'comuna' => $comuna,
            'celular' => $celular,
            'keyword' => $keyword,
        ]);

        if ($result['success']) {
            $success = true;
        } else {
            $error = $result['message'] ?? 'Error al registrar';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sumate al Cambio — Padrinos Cali</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <?php if ($success): ?>
        <div class="bg-white rounded-3xl shadow-2xl p-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">¡Gracias por sumarte!</h1>
            <p class="text-gray-500">Un líder de tu comuna se va a comunicar pronto. Bienvenido a Padrinos Cali.</p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Sumate al Cambio</h1>
                <p class="text-gray-500 mt-1">Con Edison Giraldo, Concejal de Cali</p>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="fb_user_id" value="<?= htmlspecialchars($fbUserId) ?>">
                <input type="hidden" name="keyword" value="<?= htmlspecialchars($keyword) ?>">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre completo</label>
                    <input type="text" name="nombres" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                           placeholder="Tu nombre y apellido">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Comuna donde vives</label>
                    <select name="comuna" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all bg-white">
                        <option value="">Selecciona tu comuna</option>
                        <?php for ($i = 1; $i <= 22; $i++): ?>
                        <option value="Comuna <?= $i ?>">Comuna <?= $i ?></option>
                        <?php endfor; ?>
                        <option value="Corregimiento">Corregimiento</option>
                        <option value="Otro">Otro municipio</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Celular</label>
                    <input type="tel" name="celular"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                           placeholder="300 123 4567">
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-blue-200">
                    Quiero sumarme
                </button>
            </form>

            <p class="text-xs text-gray-400 text-center mt-6">Tus datos están seguros. Solo los usaremos para la campaña.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
