<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Prohibido</title>
    <link rel="stylesheet" href="/css/output.css">
</head>
<body class="h-full bg-gray-50">

    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center">

            <!-- Error Code -->
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-red-600">403</h1>
            </div>

            <!-- Error Message -->
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                Acceso Prohibido
            </h2>
            <p class="text-gray-600 mb-8">
                No tiene permisos para acceder a esta página. Si cree que esto es un error, contacte al administrador.
            </p>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="javascript:history.back()" class="btn btn-outline">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Volver Atrás
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
                <svg class="w-64 h-64 mx-auto text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
        </div>
    </div>

</body>
</html>
