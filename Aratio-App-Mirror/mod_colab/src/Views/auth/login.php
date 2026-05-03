<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?= APP_NAME ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fef2f3',
                            100: '#fde6e8',
                            200: '#fbd0d5',
                            300: '#f7aab2',
                            400: '#f17a89',
                            500: '#e74c60',
                            600: '#d02e47',
                            700: '#8b1538',
                            800: '#7a1530',
                            900: '#6a142b',
                        },
                        roman: {
                            marble: '#faf9f7',
                            gold: '#d4af37',
                            wine: '#8b1538',
                            navy: '#1e3a5f',
                            bronze: '#6b4423',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/output.css">
</head>
<body class="h-full bg-gradient-to-br from-primary-600 to-primary-800">

    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">

            <!-- Logo y Título -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-white mb-2">A Ratio</h1>
                <p class="text-primary-100">Sistema de Gestión de Colaboradores</p>
            </div>

            <!-- Card de Login -->
            <div class="bg-white rounded-lg shadow-xl p-8">

                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                    Iniciar Sesión
                </h2>

                <!-- Flash Messages -->
                <?php if (isset($_SESSION['flash'])): ?>
                    <?php
                        $flash = $_SESSION['flash'];
                        unset($_SESSION['flash']);
                    ?>
                    <div class="mb-6 alert alert-<?= $flash['type'] ?>">
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario -->
                <form action="/login" method="POST" class="space-y-6">
                    <?= \App\Utils\Security::csrfField() ?>

                    <!-- Usuario -->
                    <div class="form-group">
                        <label for="usuario" class="form-label">
                            Usuario
                        </label>
                        <input type="text"
                               id="usuario"
                               name="usuario"
                               class="form-input"
                               placeholder="Ingrese su usuario"
                               required
                               autofocus>
                    </div>

                    <!-- Contraseña -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            Contraseña
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-input"
                               placeholder="Ingrese su contraseña"
                               required>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox"
                                   id="remember"
                                   name="remember"
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-900">
                                Recordarme
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="/forgot-password" class="text-primary-600 hover:text-primary-700">
                                ¿Olvidó su contraseña?
                            </a>
                        </div>
                    </div>

                    <!-- Botón Submit -->
                    <button type="submit" class="btn btn-primary w-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Iniciar Sesión
                    </button>
                </form>

                <!-- Info adicional -->
                <div class="mt-6 text-center text-sm text-gray-600">
                    <p>¿No tiene una cuenta?
                        <a href="/register" class="font-medium text-primary-600 hover:text-primary-700">
                            Regístrese aquí
                        </a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-primary-100">
                <p>&copy; <?= date('Y') ?> A Ratio. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>

</body>
</html>
