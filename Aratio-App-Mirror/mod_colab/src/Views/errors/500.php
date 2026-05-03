<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Error del Servidor</title>
    <link rel="stylesheet" href="/css/output.css">
</head>
<body class="h-full bg-gray-50">

    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center">

            <!-- Error Code -->
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-yellow-600">500</h1>
            </div>

            <!-- Error Message -->
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                Error del Servidor
            </h2>
            <p class="text-gray-600 mb-8">
                Ha ocurrido un error inesperado. Nuestro equipo ha sido notificado y está trabajando para solucionarlo.
            </p>

            <?php if (APP_DEBUG && isset($error)): ?>
            <div class="text-left bg-red-50 border border-red-200 rounded-lg p-4 mb-8">
                <p class="text-sm text-red-800 font-mono break-all">
                    <?= htmlspecialchars($error) ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="javascript:location.reload()" class="btn btn-outline">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reintentar
                </a>
                <a href="/dashboard" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Ir al Inicio
                </a>
            </div>

            <!-- Illustration -->
            <div class="mt-12">
                <svg class="w-64 h-64 mx-auto text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
    </div>

</body>
</html>
