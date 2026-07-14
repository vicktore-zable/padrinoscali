<?php
require_once __DIR__ . '/config/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $result = $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');
    
    if ($result['success']) {
        $rol = $_SESSION['user_rol'] ?? '';
        
        header('Location: index.php');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Padrinos Cali</title>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'aratio-blue': '#002244',
                        'aratio-navy': '#004488',
                        'aratio-gold': '#FFD700',
                    }
                }
            }
        }
    </script>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; }
        [x-cloak] { display: none !important; }
        .btn-premium-gold {
            background: linear-gradient(135deg, #FFD700 0%, #DAA520 100%);
            color: #002244;
            font-weight: 800;
            transition: all 0.3s ease;
        }
        .btn-premium-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(218, 165, 32, 0.4);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#002244] to-[#004488] font-(['Outfit']">

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">

            <div class="text-center mb-8">
                <h1 class="text-5x font-black text-white mb-2 tracking-tighter">A RATIO</h1>
                <p class="text-[#FFD700] uppercase tracking-[0.2em] font-bold text-xs">
                    Padrinos Cali &mdash; Programa de Liderazgo Social
                </p>
            </div>

            <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl p-10 border border-white/20" x-data="loginForm()">

                <h2 class="text-3xl font-extrabold text-[#002244] mb-8 text-center tracking-tight">
                    Iniciar Sesión
                </h2>

                <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-600 border border-red-100 flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span class="text-sm font-medium"><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="" @submit="validateForm" class="space-y-6">

                    <div>
                        <label class="block text-xs font-bold text-[#002244] uppercase tracking-wider mb-2">
                            Correo Electrónico
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="mail" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="email"
                                   name="email"
                                   x-model="email"
                                   required
                                   class="block w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-2xl text-gray-900 focus:ring-2 focus:ring-[#FFD700] focus:border-[#FFD700] transition-all outline-none"
                                   placeholder="tu@email.com">
                        </div>
                        <p x-show="errors.email" x-text="errors.email" class="text-sm text-red-600 mt-1"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#002244] uppercase tracking-wider mb-2">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'"
                                   name="password"
                                   x-model="password"
                                   required
                                   class="block w-full pl-12 pr-12 py-4 bg-white border border-gray-200 rounded-2xl text-gray-900 focus:ring-2 focus:ring-[#FFD700] focus:border-[#FFD700] transition-all outline-none"
                                   placeholder="Ingrese su contraseña">
                        </div>
                        <p x-show="errors.password" x-text="errors.password" class="text-sm text-red-600 mt-1"></p>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember"
                                   class="h-4 w-4 text-[#002244] focus:ring-[#FFD700] border-gray-300 rounded focus:ring-offset-0">
                            <span class="ml-2 text-sm text-gray-600">Recordar sesión</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full btn-premium-gold py-4 rounded-2xl shadow-lg flex items-center justify-center gap-3">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        <span>Iniciar Sesión</span>
                    </button>

                    <div class="p-4 bg-[#FFD700]/10 border border-[#FFD700]/30 rounded-2xl text-center">
                        <p class="text-sm text-[#002244] font-medium">
                            ¿No tienes credenciales? Solicítalas a tu <strong>Padrino</strong> de campaña
                        </p>
                    </div>

                    <div class="text-center border-t border-gray-100 pt-6">
                        <a href="<?= url('') ?>"
                           class="inline-block mt-4 text-xs text-gray-400 hover:text-gray-600 transition-colors">
                            &larr; Volver al Inicio
                        </a>
                    </div>
                </form>
            </div>

            <div class="mt-8 text-center">
                <p class="text-white/50 text-sm">&copy; <?= date('Y') ?> Padrinos Cali &mdash; Programa de Liderazgo Social</p>
                <p class="text-white/30 text-xs mt-1">Creado con Aratio PRO by MRM Tech</p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function loginForm() {
            return {
                email: '',
                password: '',
                showPassword: false,
                errors: {},

                validateForm(e) {
                    this.errors = {};

                    if (!this.email) {
                        this.errors.email = 'El email es requerido';
                    } else if (!this.isValidEmail(this.email)) {
                        this.errors.email = 'Email inválido';
                    }

                    if (!this.password) {
                        this.errors.password = 'La contraseña es requerida';
                    } else if (this.password.length < 6) {
                        this.errors.password = 'La contraseña debe tener al menos 6 caracteres';
                    }

                    if (Object.keys(this.errors).length > 0) {
                        e.preventDefault();
                    }
                },

                isValidEmail(email) {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
                }
            }
        }
    </script>
</body>
</html>
