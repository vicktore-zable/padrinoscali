<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - <?= APP_NAME ?></title>

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
                <p class="text-primary-100">Registro de Nueva Cuenta</p>
            </div>

            <!-- Card de Registro -->
            <div class="bg-white rounded-lg shadow-xl p-8">

                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                    Crear Cuenta
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
                <form action="/register" method="POST" class="space-y-6">
                    <?= \App\Utils\Security::csrfField() ?>

                    <!-- Usuario -->
                    <div class="form-group">
                        <label for="usuario" class="form-label">
                            Usuario *
                        </label>
                        <input type="text"
                               id="usuario"
                               name="usuario"
                               class="form-input"
                               placeholder="Nombre de usuario"
                               value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                               required
                               autofocus
                               minlength="4"
                               maxlength="50"
                               pattern="[a-zA-Z0-9_]+"
                               title="Solo letras, números y guión bajo">
                        <p class="mt-1 text-xs text-gray-500">4-50 caracteres, solo letras, números y guión bajo</p>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            Email *
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-input"
                               placeholder="correo@ejemplo.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required>
                    </div>

                    <!-- Nombres -->
                    <div class="form-group">
                        <label for="nombres" class="form-label">
                            Nombres
                        </label>
                        <input type="text"
                               id="nombres"
                               name="nombres"
                               class="form-input"
                               placeholder="Sus nombres"
                               value="<?= htmlspecialchars($_POST['nombres'] ?? '') ?>">
                    </div>

                    <!-- Apellidos -->
                    <div class="form-group">
                        <label for="apellidos" class="form-label">
                            Apellidos
                        </label>
                        <input type="text"
                               id="apellidos"
                               name="apellidos"
                               class="form-input"
                               placeholder="Sus apellidos"
                               value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>">
                    </div>

                    <!-- Contraseña -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            Contraseña *
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-input"
                               placeholder="Ingrese una contraseña segura"
                               required
                               minlength="8">
                        <p class="mt-1 text-xs text-gray-500">
                            Mínimo 8 caracteres, debe incluir mayúscula, número y carácter especial
                        </p>
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">
                            Confirmar Contraseña *
                        </label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               class="form-input"
                               placeholder="Confirme su contraseña"
                               required
                               minlength="8">
                    </div>

                    <!-- Documento Colaborador (opcional) -->
                    <div class="form-group">
                        <label for="colaborador_documento" class="form-label">
                            Documento de Colaborador
                        </label>
                        <input type="text"
                               id="colaborador_documento"
                               name="colaborador_documento"
                               class="form-input"
                               placeholder="Asociar con colaborador existente (opcional)"
                               value="<?= htmlspecialchars($_POST['colaborador_documento'] ?? '') ?>">
                        <p class="mt-1 text-xs text-gray-500">
                            Si ya está registrado como colaborador, ingrese su documento
                        </p>
                    </div>

                    <!-- Términos y Condiciones -->
                    <div class="flex items-start">
                        <input type="checkbox"
                               id="accept_terms"
                               name="accept_terms"
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded mt-1"
                               required>
                        <label for="accept_terms" class="ml-2 block text-sm text-gray-900">
                            Acepto los términos y condiciones de uso del sistema *
                        </label>
                    </div>

                    <!-- Botón Submit -->
                    <button type="submit" class="btn btn-primary w-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Crear Cuenta
                    </button>
                </form>

                <!-- Links adicionales -->
                <div class="mt-6 text-center text-sm text-gray-600">
                    <p>¿Ya tiene una cuenta?
                        <a href="/login" class="font-medium text-primary-600 hover:text-primary-700">
                            Inicie sesión aquí
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
