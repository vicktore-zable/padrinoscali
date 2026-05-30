<?php
/**
 * Aratio - Landing Page Pública
 * Perfil: Edison Giraldo - Concejal de Cali
 */
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edison Giraldo | El Concejal de los Caleños</title>
    <meta name="description" content="Edison Giraldo, Concejal de Cali 2024-2027. Liderando la transformación del Centro Histórico y el desarrollo económico de Cali.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e3a5f',
                        secondary: '#d4af37',
                        accent: '#2c5282',
                        dark: '#0f172a',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .glass-dark {
            background: rgba(30, 58, 95, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .gradient-text {
            background: linear-gradient(135deg, #1e3a5f 0%, #d4af37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-shape {
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0% 100%);
        }

        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900 overflow-x-hidden">

    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between glass rounded-2xl px-6 py-3 shadow-lg">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-6 h-6 text-secondary"></i>
                </div>
                <span class="font-display font-extrabold text-xl tracking-tight text-primary uppercase">Edison Giraldo</span>
            </div>
            
            <div class="hidden md:flex items-center gap-8 font-medium text-sm text-slate-600">
                <a href="#inicio" class="hover:text-primary transition-colors">Inicio</a>
                <a href="#perfil" class="hover:text-primary transition-colors">Perfil</a>
                <a href="#gestion" class="hover:text-primary transition-colors">Gestión</a>
                <a href="#territorio" class="hover:text-primary transition-colors">Territorio</a>
                <a href="?page=dashboard_organizaciones_publico&campana_id=2" class="hover:text-primary transition-colors">Mapa Social</a>
            </div>

            <div class="flex items-center gap-4">
                <a href="login.php" class="bg-primary text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-slate-800 transition-all flex items-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    Acceso Interno
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="relative pt-32 pb-20 md:pt-48 md:pb-32 bg-primary hero-shape overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-96 h-96 bg-secondary rounded-full blur-[100px]"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-accent rounded-full blur-[80px]"></div>
        </div>

        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center relative z-10">
            <div>
                <span class="inline-block px-4 py-1 rounded-full bg-secondary/20 text-secondary text-xs font-bold uppercase tracking-widest mb-6">Concejal de Cali 2024 - 2027</span>
                <h1 class="font-display text-5xl md:text-7xl font-extrabold text-white leading-tight mb-6">
                    El concejal de los <span class="text-secondary">camelladores</span>
                </h1>
                <p class="text-slate-300 text-lg md:text-xl mb-10 max-w-lg leading-relaxed">
                    Caleño por adopción y decisión. Emprendedor enfocado en transformar Cali desde el corazón: nuestro Centro Histórico y cada barrio donde haya un ciudadano con ganas de salir adelante.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="#gestion" class="bg-secondary text-primary px-8 py-4 rounded-2xl font-bold hover:scale-105 transition-all shadow-xl shadow-secondary/20">
                        Conoce mi Gestión
                    </a>
                    <a href="https://www.instagram.com/edison_concejal/" target="_blank" class="glass-dark text-white px-8 py-4 rounded-2xl font-bold flex items-center gap-3 hover:bg-primary/50 transition-all">
                        <i data-lucide="instagram" class="w-5 h-5"></i>
                        @edison_concejal
                    </a>
                </div>
                
                <div class="mt-12 flex items-center gap-8">
                    <div>
                        <p class="text-white font-bold text-2xl">25K+</p>
                        <p class="text-slate-400 text-xs uppercase tracking-wider">Seguidores</p>
                    </div>
                    <div class="w-px h-10 bg-slate-700"></div>
                    <div>
                        <p class="text-white font-bold text-2xl">670+</p>
                        <p class="text-slate-400 text-xs uppercase tracking-wider">Publicaciones</p>
                    </div>
                    <div class="w-px h-10 bg-slate-700"></div>
                    <div>
                        <p class="text-white font-bold text-2xl">34K+</p>
                        <p class="text-slate-400 text-xs uppercase tracking-wider">Interacciones FB</p>
                    </div>
                </div>
            </div>
            
            <div class="relative hidden md:block">
                <div class="relative z-10 animate-float">
                    <img src="https://aratio.mrmtech.net/public_html/assets/img/edison_profile.png" alt="Edison Giraldo" class="w-full max-w-md mx-auto drop-shadow-2xl grayscale hover:grayscale-0 transition-all duration-500 cursor-pointer">
                </div>
                <!-- Circle Background -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] border border-white/5 rounded-full"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[140%] h-[140%] border border-white/5 rounded-full"></div>
            </div>
        </div>
    </section>

    <!-- Stats Bar -->
    <section class="py-12 bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-6 flex flex-wrap justify-center md:justify-between gap-8 md:gap-4">
            <div class="flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all">
                    <i data-lucide="trending-up" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Crecimiento Económico</h4>
                    <p class="text-xs text-slate-500">Apoyo a emprendedores</p>
                </div>
            </div>
            <div class="flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all">
                    <i data-lucide="map" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Centro Histórico</h4>
                    <p class="text-xs text-slate-500">Revitalización Urbana</p>
                </div>
            </div>
            <div class="flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Gestión Social</h4>
                    <p class="text-xs text-slate-500">Liderazgo territorial</p>
                </div>
            </div>
            <div class="flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all">
                    <i data-lucide="megaphone" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Rendición de Cuentas</h4>
                    <p class="text-xs text-slate-500">Transparencia Total</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfil Section -->
    <section id="perfil" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div class="order-2 md:order-1">
                    <h2 class="font-display text-4xl font-extrabold text-primary mb-6">
                        Liderazgo con <span class="gradient-text">visión estratégica</span>
                    </h2>
                    <div class="space-y-6 text-slate-600 leading-relaxed">
                        <p>
                            Edison Giraldo se perfila como un político local pragmático y emprendedor. Como ponente del <strong>Plan de Desarrollo Cali (2024-2027)</strong>, ha defendido proyectos vitales para la superación del "valle de la muerte" empresarial y el fortalecimiento de la seguridad urbana.
                        </p>
                        <p>
                            Su enfoque principal es la revitalización del <strong>Centro Histórico</strong>, promoviendo el empleo, el embellecimiento urbano y el apoyo a los comerciantes que son el motor de nuestra economía local.
                        </p>
                        <ul class="space-y-4 pt-4">
                            <li class="flex items-start gap-3">
                                <div class="mt-1 w-5 h-5 rounded-full bg-secondary/20 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="check" class="w-3 h-3 text-secondary"></i>
                                </div>
                                <span>Impulsor de la <strong>Semipeatonalización</strong> del centro.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="mt-1 w-5 h-5 rounded-full bg-secondary/20 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="check" class="w-3 h-3 text-secondary"></i>
                                </div>
                                <span>Defensor del <strong>Espacio Público</strong> organizado.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="mt-1 w-5 h-5 rounded-full bg-secondary/20 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="check" class="w-3 h-3 text-secondary"></i>
                                </div>
                                <span>Líder de la iniciativa <strong>"Cali Nos Inspira"</strong>.</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="order-1 md:order-2 grid grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <img src="https://images.unsplash.com/photo-1577415124269-fc1140a69e91?q=80&w=1000&auto=format&fit=crop" class="w-full h-64 object-cover rounded-3xl shadow-lg" alt="Cali Centro">
                        <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=1000&auto=format&fit=crop" class="w-full h-48 object-cover rounded-3xl shadow-lg" alt="Gestión Social">
                    </div>
                    <div class="space-y-4 pt-8">
                        <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?q=80&w=1000&auto=format&fit=crop" class="w-full h-48 object-cover rounded-3xl shadow-lg" alt="Reunión Trabajo">
                        <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=1000&auto=format&fit=crop" class="w-full h-64 object-cover rounded-3xl shadow-lg" alt="Liderazgo">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gestion Section -->
    <section id="gestion" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-secondary font-bold text-sm uppercase tracking-widest">Líneas de Acción</span>
                <h2 class="font-display text-4xl md:text-5xl font-extrabold text-primary mt-4">Proyectos Estratégicos</h2>
                <div class="w-24 h-1 bg-secondary mx-auto mt-6"></div>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm card-hover border border-slate-100">
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-6">
                        <i data-lucide="landmark" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-display text-xl font-bold text-slate-800 mb-4 text-primary">Centro Histórico</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Revitalización integral: semipeatonalización, sistema de parqueos y embellecimiento de plazas como Santa Rosa, San Nicolás y la Plazoleta San Francisco.
                    </p>
                </div>
                
                <!-- Card 2 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm card-hover border border-slate-100">
                    <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 mb-6">
                        <i data-lucide="briefcase" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-display text-xl font-bold text-slate-800 mb-4 text-primary">Economía Popular</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Políticas públicas para vendedores informales y comerciantes del centro (Greco Centro). Fomento al empleo y superación del valle de la muerte empresarial.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm card-hover border border-slate-100">
                    <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-6">
                        <i data-lucide="map-pinned" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-display text-xl font-bold text-slate-800 mb-4 text-primary">Renovación Urbana</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Impulso a <strong>Ciudad Paraíso</strong> y la Estación Central del MÍO. Apoyo a la finalización del Búnker de la Fiscalía como eje de seguridad.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Territorio Section -->
    <section id="territorio" class="py-24 bg-primary relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0 100 C 20 0 50 0 100 100" stroke="white" fill="transparent" stroke-width="0.1"/>
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="font-display text-4xl font-extrabold text-white mb-6">
                        Más allá del centro: <br><span class="text-secondary">Cali es una sola</span>
                    </h2>
                    <p class="text-slate-300 text-lg mb-10">
                        Nuestra gestión ha extendido sus fronteras al Sur y Oriente de Cali. Porque cada caleño, desde Ciudad Jardín hasta el Bulevar del Oriente, merece un representante que trabaje por su bienestar.
                    </p>
                    
                    <div class="space-y-6">
                        <div class="flex gap-5">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="navigation" class="w-6 h-6 text-secondary"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold mb-1">Ciudad Jardín (Sur)</h4>
                                <p class="text-slate-400 text-sm">Encuentros territoriales para fortalecer el espacio público y la seguridad residencial.</p>
                            </div>
                        </div>
                        <div class="flex gap-5">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="move-right" class="w-6 h-6 text-secondary"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold mb-1">Bulevar del Oriente (Comunas 12-13)</h4>
                                <p class="text-slate-400 text-sm">Apoyo a la renovación urbana y la inclusión social en Alfonso Bonilla y Marroquín.</p>
                            </div>
                        </div>
                        <div class="flex gap-5">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="music" class="w-6 h-6 text-secondary"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold mb-1">Barrio Obrero</h4>
                                <p class="text-slate-400 text-sm">Impulso a la Ruta de la Salsa y el Museo de la Salsa como patrimonio cultural.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white/5 p-8 rounded-[2rem] backdrop-blur-md border border-white/10">
                    <div class="text-center mb-8">
                        <i data-lucide="map-pin" class="w-12 h-12 text-secondary mx-auto mb-4"></i>
                        <h3 class="text-white font-display text-2xl font-bold">Mapa Social Aratio</h3>
                        <p class="text-slate-400 text-sm mt-2">Visualiza el impacto de nuestra red ciudadana en Cali.</p>
                    </div>
                    <div class="aspect-video bg-slate-900 rounded-2xl overflow-hidden relative group">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent z-10"></div>
                        <img src="https://images.unsplash.com/photo-1526778545894-6297aa496432?q=80&w=1000&auto=format&fit=crop" class="w-full h-full object-cover opacity-50 group-hover:scale-110 transition-transform duration-700" alt="Mapa">
                        <div class="absolute inset-0 z-20 flex flex-col items-center justify-center p-6 text-center">
                            <p class="text-white font-medium mb-4">Consulta la distribución territorial de nuestras organizaciones aliadas.</p>
                            <a href="?page=dashboard_organizaciones_publico&campana_id=2" class="bg-white text-primary px-6 py-3 rounded-xl font-bold hover:bg-secondary transition-colors">
                                Ver Mapa Interactivo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center border border-white/10">
                            <i data-lucide="shield-check" class="w-8 h-8 text-secondary"></i>
                        </div>
                        <span class="font-display font-extrabold text-2xl text-white uppercase tracking-tighter">Edison Giraldo</span>
                    </div>
                    <p class="text-slate-400 max-w-sm leading-relaxed">
                        "Edison Giraldo: El concejal de todos los caleños que quieren salir adelante. De emprendedores y camelladores en el centro de Cali... ¡hacia toda la ciudad!"
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Enlaces</h4>
                    <ul class="space-y-4 text-slate-400 text-sm">
                        <li><a href="#inicio" class="hover:text-secondary transition-colors">Inicio</a></li>
                        <li><a href="#perfil" class="hover:text-secondary transition-colors">Perfil Político</a></li>
                        <li><a href="#gestion" class="hover:text-secondary transition-colors">Gestión Municipal</a></li>
                        <li><a href="?page=dashboard_organizaciones_publico&campana_id=2" class="hover:text-secondary transition-colors">Territorios</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Redes Sociales</h4>
                    <div class="flex gap-4">
                        <a href="https://www.instagram.com/edison_concejal/" target="_blank" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-white hover:bg-secondary hover:text-primary transition-all">
                            <i data-lucide="instagram" class="w-5 h-5"></i>
                        </a>
                        <a href="https://www.facebook.com/edisonconcejal/" target="_blank" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-white hover:bg-secondary hover:text-primary transition-all">
                            <i data-lucide="facebook" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-white hover:bg-secondary hover:text-primary transition-all">
                            <i data-lucide="twitter" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="pt-8 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500 uppercase tracking-widest">
                <p>&copy; 2026 Aratio Platform - Edison Giraldo Concejal.</p>
                <p>Cali Nos Inspira</p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
