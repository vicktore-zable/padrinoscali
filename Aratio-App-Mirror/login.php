<?php
require_once __DIR__ . '/config/config.php';

if (isset($_SESSION['user_id'])) {
    $rol = $_SESSION['user_rol'] ?? '';
    if ($rol === 'lider') {
        header('Location: index.php?page=portal_dashboard');
    } else {
        header('Location: index.php');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo_login'] ?? 'admin';
    $auth = new Auth();

    if ($tipo === 'lider') {
        $result = $auth->loginColaborador($_POST['documento'] ?? '', $_POST['telefono'] ?? '');
        if ($result['success']) {
            header('Location: index.php?page=portal_dashboard');
            exit;
        }
    } else {
        $result = $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($result['success']) {
            header('Location: index.php');
            exit;
        }
    }
    $error = $result['message'];
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
        .tab-active {
            border-bottom: 3px solid #FFD700;
            color: #002244;
            font-weight: 700;
        }
        .tab-inactive {
            color: #6B7280;
            font-weight: 500;
        }
        .tab-inactive:hover {
            color: #002244;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#002244] to-[#004488]">

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">

            <div class="text-center mb-8">
                <h1 class="text-5xl font-black text-white mb-2 tracking-tighter">A RATIO</h1>
                <p class="text-[#FFD700] uppercase tracking-[0.2em] font-bold text-xs">
                    Padrinos Cali &mdash; Programa de Liderazgo Social
                </p>
            </div>

            <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl p-10 border border-white/20" x-data="loginApp()">

                <h2 class="text-3xl font-extrabold text-[#002244] mb-6 text-center tracking-tight">
                    Iniciar Sesión
                </h2>

                <!-- Tabs -->
                <div class="flex border-b border-gray-200 mb-6">
                    <button @click="tab = 'admin'" :class="tab === 'admin' ? 'tab-active' : 'tab-inactive'"
                            class="flex-1 pb-3 text-center text-sm transition-all">
                        <i data-lucide="shield" class="w-4 h-4 inline mr-1"></i>
                        Administrador
                    </button>
                    <button @click="tab = 'lider'" :class="tab === 'lider' ? 'tab-active' : 'tab-inactive'"
                            class="flex-1 pb-3 text-center text-sm transition-all">
                        <i data-lucide="users" class="w-4 h-4 inline mr-1"></i>
                        Líder Social
                    </button>
                </div>

                <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-600 border border-red-100 flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                    <span class="text-sm font-medium"><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>

                <!-- Admin Form -->
                <form method="POST" action="" x-show="tab === 'admin'" @submit="validateAdmin" class="space-y-6">
                    <input type="hidden" name="tipo_login" value="admin">

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
                                   placeholder="••••••••">
                            <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <p x-show="errors.password" x-text="errors.password" class="text-sm text-red-600 mt-1"></p>
                    </div>

                    <button type="submit"
                            class="w-full btn-premium-gold py-4 rounded-2xl shadow-lg flex items-center justify-center gap-3">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        <span>Ingresar como Admin</span>
                    </button>
                </form>

                <!-- Leader Form -->
                <form method="POST" action="" x-show="tab === 'lider'" @submit="validateLider" class="space-y-6" x-cloak>
                    <input type="hidden" name="tipo_login" value="lider">

                    <div>
                        <label class="block text-xs font-bold text-[#002244] uppercase tracking-wider mb-2">
                            Documento de Identidad
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="credit-card" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="text"
                                   name="documento"
                                   x-model="documento"
                                   required
                                   class="block w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-2xl text-gray-900 focus:ring-2 focus:ring-[#FFD700] focus:border-[#FFD700] transition-all outline-none"
                                   placeholder="Ingrese su documento">
                        </div>
                        <p x-show="errors.documento" x-text="errors.documento" class="text-sm text-red-600 mt-1"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#002244] uppercase tracking-wider mb-2">
                            Teléfono (Contraseña)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="smartphone" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="tel"
                                   name="telefono"
                                   x-model="telefono"
                                   required
                                   class="block w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-2xl text-gray-900 focus:ring-2 focus:ring-[#FFD700] focus:border-[#FFD700] transition-all outline-none"
                                   placeholder="Ingrese su número de teléfono">
                        </div>
                        <p x-show="errors.telefono" x-text="errors.telefono" class="text-sm text-red-600 mt-1"></p>
                    </div>

                    <button type="submit"
                            class="w-full btn-premium-gold py-4 rounded-2xl shadow-lg flex items-center justify-center gap-3">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        <span>Ingresar al Portal</span>
                    </button>
                </form>

                <!-- Info -->
                <div class="mt-6 p-4 bg-[#FFD700]/10 border border-[#FFD700]/30 rounded-2xl text-center">
                    <p class="text-sm text-[#002244] font-medium">
                        ¿No tienes credenciales? Solicítalas a tu <strong>Padrino</strong> de campaña
                    </p>
                </div>

                <div class="text-center border-t border-gray-100 pt-6 mt-6">
                    <p class="text-sm text-gray-500">
                        <a href="<?= url('registro-simpatizante.php') ?>"
                           class="font-bold text-[#002244] hover:underline block">
                            Regístrate como Simpatizante
                        </a>
                    </p>
                    <a href="<?= url('') ?>"
                       class="inline-block mt-4 text-xs text-gray-400 hover:text-gray-600 transition-colors">
                        &larr; Volver al Inicio
                    </a>
                </div>
            </div>

            <div class="mt-8 text-center">
                <p class="text-white/50 text-sm">&copy; <?= date('Y') ?> Padrinos Cali &mdash; Programa de Liderazgo Social</p>
                <p class="text-white/30 text-xs mt-1">Creado con Aratio PRO by MRM Tech</p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function loginApp() {
            return {
                tab: 'admin',
                email: '',
                password: '',
                documento: '',
                telefono: '',
                showPassword: false,
                errors: {},

                validateAdmin(e) {
                    this.errors = {};
                    if (!this.email) {
                        this.errors.email = 'El email es requerido';
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) {
                        this.errors.email = 'Email inválido';
                    }
                    if (!this.password) {
                        this.errors.password = 'La contraseña es requerida';
                    }
                    if (Object.keys(this.errors).length > 0) {
                        e.preventDefault();
                    }
                },

                validateLider(e) {
                    this.errors = {};
                    if (!this.documento) {
                        this.errors.documento = 'El documento es requerido';
                    }
                    if (!this.telefono) {
                        this.errors.telefono = 'El teléfono es requerido';
                    } else if (this.telefono.length < 7) {
                        this.errors.telefono = 'Teléfono inválido';
                    }
                    if (Object.keys(this.errors).length > 0) {
                        e.preventDefault();
                    }
                }
            }
        }
    </script>
</body>
</html>
