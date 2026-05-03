<?php
require_once __DIR__ . '/config/config.php';

// Si ya está autenticado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . url(''));
    exit;
}

// Procesar login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $result = $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');
    
    if ($result['success']) {
        $rol = $_SESSION['user_rol'] ?? '';
        
        // Redirección hacia el dashboard central
        header('Location: ' . url(''));
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
    <title>Login - Aratio</title>
    
    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e3a5f',
                        secondary: '#d4af37',
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a5f 0%, #d4af37 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo y Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 gradient-bg rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Aratio</h1>
                <p class="text-gray-600 mt-2">Sistema de Gestión Electoral</p>
            </div>

            <!-- Formulario de Login -->
            <div class="bg-white rounded-2xl shadow-xl p-8" x-data="loginForm()">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Iniciar Sesión</h2>
                
                <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600"><?= htmlspecialchars($error) ?></p>
                </div>
                <?php endif; ?>

                <form method="POST" action="" @submit="validateForm">
                    <!-- Email -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Correo Electrónico
                        </label>
                        <input
                            type="email"
                            name="email"
                            x-model="email"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            placeholder="tu@email.com"
                        >
                        <p x-show="errors.email" x-text="errors.email" class="text-sm text-red-600 mt-1"></p>
                    </div>

                    <!-- Password -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <div class="relative">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                x-model="password"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                placeholder="••••••••"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        <p x-show="errors.password" x-text="errors.password" class="text-sm text-red-600 mt-1"></p>
                    </div>

                    <!-- Recordar sesión -->
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <span class="ml-2 text-sm text-gray-600">Recordar sesión</span>
                        </label>
                        <a href="#" class="text-sm text-primary hover:underline">¿Olvidaste tu contraseña?</a>
                    </div>

                    <!-- Botón de Login -->
                    <button
                        type="submit"
                        class="w-full gradient-bg text-white py-3 rounded-lg font-medium hover:opacity-90 transition"
                    >
                        Iniciar Sesión
                    </button>

                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-600">
                            <a href="<?= url('registro-simpatizante.php') ?>" class="font-medium text-primary hover:text-secondary transition block">
                                Regístrate como Simpatizante
                            </a>
                        </p>
                        <a href="<?= url('') ?>" class="block mt-2 text-xs text-gray-400 hover:text-gray-600">
                            ← Volver al Inicio
                        </a>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-sm text-gray-600">
                    © <?= date('Y') ?> Aratio - Sistema de Gestión Electoral
                </p>
            </div>
        </div>
    </div>

    <script>
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
