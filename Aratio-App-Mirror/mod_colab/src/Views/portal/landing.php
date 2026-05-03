<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Líderes - Aratio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #002244 0%, #004488 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .btn-primary {
            background: linear-gradient(90deg, #FFD700 0%, #DAA520 100%);
            color: #002244;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .icon-bg {
             background: linear-gradient(135deg, #002244 0%, #004488 100%);
        }
        .text-portal-blue {
            color: #002244;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Hero Section -->
    <div class="relative bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                <svg class="hidden lg:block absolute right-0 inset-y-0 h-full w-48 text-white transform translate-x-1/2" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                    <polygon points="50,0 100,0 50,100 0,100" />
                </svg>

                <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                    <div class="sm:text-center lg:text-left">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                            <span class="block xl:inline">Empodera tu Liderazgo</span>
                            <span class="block gradient-text">Gestiona tu Red</span>
                        </h1>
                        <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                            Bienvenido al Portal de Líderes. Una herramienta exclusiva diseñada para potenciar tu trabajo en la campaña. Visualiza tu estructura, coordina acciones y mantente informado.
                        </p>
                        <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                            <div class="rounded-md shadow">
                                <a href="?page=portal_login" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white btn-primary md:py-4 md:text-lg md:px-10">
                                    Ingresar al Portal
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 bg-gray-900 flex items-center justify-center">
            <div class="text-center p-8">
                <i data-lucide="network" class="w-48 h-48 text-white opacity-20 mx-auto"></i>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:text-center">
                <h2 class="text-base text-portal-blue font-semibold tracking-wide uppercase">Funcionalidades</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Todo lo que necesitas en un solo lugar
                </p>
            </div>

            <div class="mt-10">
                <dl class="space-y-10 md:space-y-0 md:grid md:grid-cols-3 md:gap-x-8 md:gap-y-10">
                    <!-- Feature 1 -->
                    <div class="relative">
                        <dt>
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md icon-bg text-white">
                                <i data-lucide="users" class="w-6 h-6"></i>
                            </div>
                            <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Gestión de Red</p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-gray-500">
                            Visualiza tu estructura jerárquica, conoce a tu equipo y gestiona el crecimiento de tu red de colaboradores de forma intuitiva.
                        </dd>
                    </div>

                    <!-- Feature 2 -->
                    <div class="relative">
                        <dt>
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md icon-bg text-white">
                                <i data-lucide="calendar" class="w-6 h-6"></i>
                            </div>
                            <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Eventos de Campaña</p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-gray-500">
                            Mantente al día con la agenda oficial. Consulta fechas, lugares y detalles de los próximos eventos y convocatorias.
                        </dd>
                    </div>

                    <!-- Feature 3 -->
                    <div class="relative">
                        <dt>
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md icon-bg text-white">
                                <i data-lucide="bell" class="w-6 h-6"></i>
                            </div>
                            <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Anuncios e Instrucciones</p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-gray-500">
                            Recibe comunicaciones oficiales, instrucciones estratégicas y actualizaciones importantes directamente en tu panel.
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
