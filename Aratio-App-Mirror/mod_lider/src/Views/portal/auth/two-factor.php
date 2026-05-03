<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticación de Dos Factores - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/css/output.css">
</head>
<body class="h-full bg-gradient-to-br from-primary-600 to-primary-800">

    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">

            <!-- Logo y Título -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Autenticación de Dos Factores</h1>
                <p class="text-primary-100">Ingrese el código de su aplicación de autenticación</p>
            </div>

            <!-- Card de 2FA -->
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
                <form action="/login" method="POST" class="space-y-6">
                    <?= \App\Utils\Security::csrfField() ?>

                    <input type="hidden" name="2fa_step" value="1">

                    <!-- Código 2FA -->
                    <div class="form-group">
                        <label for="codigo_2fa" class="form-label text-center block">
                            Código de 6 dígitos
                        </label>
                        <input type="text"
                               id="codigo_2fa"
                               name="codigo_2fa"
                               class="form-input text-center text-2xl font-mono tracking-widest"
                               placeholder="000000"
                               pattern="[0-9]{6}"
                               maxlength="6"
                               required
                               autofocus
                               autocomplete="off">
                        <p class="text-xs text-gray-500 text-center mt-2">
                            Ingrese el código de 6 dígitos de su aplicación
                        </p>
                    </div>

                    <!-- Botón Submit -->
                    <button type="submit" class="btn btn-primary w-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Verificar Código
                    </button>
                </form>

                <!-- Info adicional -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 mb-2">
                        ¿No puede acceder a su código?
                    </p>
                    <a href="/login" class="text-sm text-primary-600 hover:text-primary-700">
                        ← Volver al inicio de sesión
                    </a>
                </div>

                <!-- Help -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Ayuda</h3>
                            <p class="text-xs text-blue-700 mt-1">
                                Abra su aplicación de autenticación (Google Authenticator, Authy, etc.)
                                e ingrese el código de 6 dígitos que aparece para <?= APP_NAME ?>.
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

    <script>
    // Auto-submit cuando se ingresan 6 dígitos
    document.getElementById('codigo_2fa').addEventListener('input', function(e) {
        // Solo números
        this.value = this.value.replace(/[^0-9]/g, '');

        // Auto-submit cuando tiene 6 dígitos
        if (this.value.length === 6) {
            this.form.submit();
        }
    });
    </script>

</body>
</html>
