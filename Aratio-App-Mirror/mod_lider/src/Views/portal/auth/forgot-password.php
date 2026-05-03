<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - <?= APP_NAME ?></title>

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
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">¿Olvidó su contraseña?</h1>
                <p class="text-primary-100">Ingrese su email para recibir instrucciones</p>
            </div>

            <!-- Card de Recuperación -->
            <div class="bg-white rounded-lg shadow-xl p-8">

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
                <form action="/forgot-password" method="POST" class="space-y-6">
                    <?= \App\Utils\Security::csrfField() ?>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            Email
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-input"
                               placeholder="correo@ejemplo.com"
                               required
                               autofocus>
                        <p class="text-xs text-gray-500 mt-1">
                            Ingrese el email asociado a su cuenta
                        </p>
                    </div>

                    <!-- Botón Submit -->
                    <button type="submit" class="btn btn-primary w-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Enviar Instrucciones
                    </button>
                </form>

                <!-- Links -->
                <div class="mt-6 text-center">
                    <a href="/login" class="text-sm text-primary-600 hover:text-primary-700 inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Volver al inicio de sesión
                    </a>
                </div>

                <!-- Info -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Información</h3>
                            <p class="text-xs text-blue-700 mt-1">
                                Si el email existe en nuestro sistema, recibirá un enlace para
                                restablecer su contraseña. El enlace expira en 1 hora.
                            </p>
                        </div>
                    </div>
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
