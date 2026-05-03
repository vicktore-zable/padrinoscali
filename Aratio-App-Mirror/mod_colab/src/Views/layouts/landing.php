<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $title ?? 'Sistema de Gestión de Colaboradores' ?> - A Ratio</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Tailwind CSS (CDN - temporal) -->
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
                            950: '#3a0a16',
                        },
                        roman: {
                            marble: '#faf9f7',
                            gold: '#d4af37',
                            wine: '#8b1538',
                            navy: '#1e3a5f',
                            bronze: '#6b4423',
                            stone: '#a8a5a0',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/output.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-white">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-primary-600">A Ratio</h1>
                    <span class="ml-4 text-sm text-gray-500">Sistema de Inteligencia Política</span>
                </div>
                <div class="flex items-center">
                    <a href="/login" class="btn btn-primary">
                        Iniciar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <?= $content ?>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> A Ratio. Todos los derechos reservados.</p>
                <p class="mt-2 text-sm text-gray-400">Sistema de Gestión Integral de Colaboradores</p>
            </div>
        </div>
    </footer>

</body>
</html>
