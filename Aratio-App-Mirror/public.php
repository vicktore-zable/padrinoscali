<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $title ?? 'Inscripción' ?> - <?= APP_NAME ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#FF00FF', // Magenta Aratio
                        secondary: '#FFD700', // Dorado Aratio
                        // Mantener compatibilidad con colores usados en V1 si es necesario, mapeándolos
                        'primary-700': '#FF00FF', 
                        'primary-600': '#d02e47', // Fallback
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Custom CSS Styles -->
    <style>
        .gradient-top {
            background: linear-gradient(135deg, #FF00FF 0%, #FFD700 100%);
        }
        [x-cloak] { display: none !important; }
        
        /* Overrides for form elements to match new style */
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem; /* rounded-lg */
            border: 1px solid #d1d5db;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            ring: 2px solid #FF00FF;
            border-color: transparent;
            outline: none;
            box-shadow: 0 0 0 2px #FF00FF;
        }
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.25rem;
            display: block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #FF00FF 0%, #FFD700 100%);
            color: white;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
    </style>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gray-50" x-init="lucide.createIcons()">
    
    <!-- Hero Header (Matches Sympathizer Page) -->
    <div class="gradient-top text-white pb-24 pt-12 px-4 shadow-lg">
        <div class="container mx-auto max-w-5xl text-center">
            <h1 class="text-4xl font-extrabold mb-4"><?= APP_NAME ?></h1>
            <p class="text-xl opacity-90 mb-6"><?= $title ?? 'Gestión Electoral' ?></p>
            <div class="flex justify-center gap-4">
                <a href="/" class="inline-flex items-center text-white/80 hover:text-white transition-colors border border-white/30 rounded-full px-4 py-2 text-sm font-medium hover:bg-white/10">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Volver al Inicio
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Container (Overlap) -->
    <main class="container mx-auto max-w-5xl px-4 -mt-16 mb-12 relative z-10">
        <?php
        if (isset($viewContent)) {
            echo $viewContent;
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-50 text-center py-8">
        <p class="text-gray-500 text-sm">
            &copy; <?= date('Y') ?> <?= APP_NAME ?>. Todos los derechos reservados.
        </p>
    </footer>
</body>
</html>
