<?php
/**
 * Aratio - Landing Page Pública
 * Perfil: Padrinos Cali - Programa de Liderazgo Social
 */
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrinos Cali | Programa de Liderazgo Social</title>
    <meta name="description" content="Padrinos Cali es un programa de liderazgo social que conecta padrinos con líderes comunitarios para transformar Cali desde los barrios.">
    
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
                <span class="font-display font-extrabold text-xl tracking-tight text-primary uppercase">Padrinos Cali</span>
            </div>
            
            <div class="hidden md:flex items-center gap-8 font-medium text-sm text-slate-600">
                <a href="#inicio" class="hover:text-primary transition-colors">Inicio</a>
                <a href="#perfil" class="hover:text-primary transition-colors">Perfil</a>
                <a href="#gestion" class="hover:text-primary transition-colors">Gestión</a>
                <a href="#plataforma" class="hover:text-primary transition-colors">Plataforma</a>
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
                <span class="inline-block px-4 py-1 rounded-full bg-secondary/20 text-secondary text-xs font-bold uppercase tracking-widest mb-6">Programa de Liderazgo Social</span>
                <h1 class="font-display text-5xl md:text-7xl font-extrabold text-white leading-tight mb-6">
                    Construye comunidad, <span class="text-secondary">transforma Cali</span>
                </h1>
                <p class="text-slate-300 text-lg md:text-xl mb-10 max-w-lg leading-relaxed">
                    Padrinos Cali conecta ciudadanos comprometidos con líderes barriales para fortalecer el tejido social, gestionar territorio y generar oportunidades reales en cada comuna de Cali.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="#gestion" class="bg-secondary text-primary px-8 py-4 rounded-2xl font-bold hover:scale-105 transition-all shadow-xl shadow-secondary/20">
                        Conoce mi Gestión
                    </a>
                    <a href="#territorio" class="glass-dark text-white px-8 py-4 rounded-2xl font-bold flex items-center gap-3 hover:bg-primary/50 transition-all">
                        <i data-lucide="map" class="w-5 h-5"></i>
                        Ver Mapa Social
                    </a>
                    <a href="?page=cp_captura" class="bg-green-500 text-white px-8 py-4 rounded-2xl font-bold hover:bg-green-600 transition-all shadow-xl shadow-green-500/20 flex items-center gap-3">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                        Quiero sumarme
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
                    <img src="https://images.unsplash.com/photo-1559027615-cd4628902d4a?q=80&w=1000&auto=format&fit=crop" alt="Padrinos Cali" class="w-full max-w-md mx-auto drop-shadow-2xl">
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
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Red de Padrinos</h4>
                    <p class="text-xs text-slate-500">Compromiso ciudadano</p>
                </div>
            </div>
            <div class="flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all">
                    <i data-lucide="map" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Cobertura Territorial</h4>
                    <p class="text-xs text-slate-500">16 comunas de Cali</p>
                </div>
            </div>
            <div class="flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all">
                    <i data-lucide="heart-handshake" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Gestión Social</h4>
                    <p class="text-xs text-slate-500">Liderazgo territorial</p>
                </div>
            </div>
            <div class="flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all">
                    <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Impacto Medible</h4>
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
                        Liderazgo social con <span class="gradient-text">visión comunitaria</span>
                    </h2>
                    <div class="space-y-6 text-slate-600 leading-relaxed">
                        <p>
                            Padrinos Cali articula ciudadanos comprometidos —padrinos— con líderes barriales para gestionar territorio, impulsar proyectos comunitarios y construir una red de apoyo real en cada rincón de Cali.
                        </p>
                        <p>
                            Creemos en el poder de la comunidad organizada. Cada padrino, cada líder, cada simpatizante suma para hacer de Cali una ciudad con más oportunidades, más gestión y más corazón.
                        </p>
                        <ul class="space-y-4 pt-4">
                            <li class="flex items-start gap-3">
                                <div class="mt-1 w-5 h-5 rounded-full bg-secondary/20 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="check" class="w-3 h-3 text-secondary"></i>
                                </div>
                                <span><strong>Red de padrinos</strong> comprometidos con el territorio.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="mt-1 w-5 h-5 rounded-full bg-secondary/20 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="check" class="w-3 h-3 text-secondary"></i>
                                </div>
                                <span>Gestión de <strong>proyectos comunitarios</strong> medibles.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="mt-1 w-5 h-5 rounded-full bg-secondary/20 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="check" class="w-3 h-3 text-secondary"></i>
                                </div>
                                <span>Más de <strong>50 padrinos activos</strong> en 16 comunas.</span>
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
                        <i data-lucide="heart-handshake" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-display text-xl font-bold text-slate-800 mb-4 text-primary">Red de Padrinos</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Conectamos ciudadanos comprometidos con padrinos barriales para apadrinar proyectos comunitarios, gestionar recursos y amplificar el impacto social en cada territorio.
                    </p>
                </div>
                
                <!-- Card 2 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm card-hover border border-slate-100">
                    <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 mb-6">
                        <i data-lucide="users" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-display text-xl font-bold text-slate-800 mb-4 text-primary">Formación de Líderes</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Estructuramos redes territoriales con metas claras, evaluación de participación y herramientas de gestión para que cada padrino potencie su comunidad.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm card-hover border border-slate-100">
                    <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-6">
                        <i data-lucide="map-pinned" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-display text-xl font-bold text-slate-800 mb-4 text-primary">Gestión Territorial</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Dashboard en tiempo real con cobertura por comuna, seguimiento de proyectos comunitarios y alertas tempranas para la toma de decisiones basadas en datos.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Plataforma Section -->
    <section id="plataforma" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-secondary font-bold text-sm uppercase tracking-widest">Tecnología Cívica</span>
                <h2 class="font-display text-4xl md:text-5xl font-extrabold text-primary mt-4">Aratio <span class="gradient-text">Platform</span></h2>
                <p class="text-slate-500 text-lg mt-4 max-w-2xl mx-auto">El sistema de gestión de campaña que integra redes sociales, territorio y organización en un solo lugar.</p>
                <div class="w-24 h-1 bg-secondary mx-auto mt-6"></div>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- CP Pulso -->
                <div class="bg-gradient-to-br from-slate-50 to-white p-8 rounded-3xl border border-slate-100 card-hover group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-200">
                            <i data-lucide="activity" class="w-8 h-8"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">Nuevo</span>
                            <h3 class="font-display text-xl font-bold text-slate-800">CP — Pulso de Campaña</h3>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Automatización social: cada comentario con una palabra clave activa un DM automático que captura datos y crea un colaborador. Conversión de redes a organización territorial sin fricción.
                    </p>
                    <div class="flex items-center gap-4 text-xs text-slate-400">
                        <span class="flex items-center gap-1"><i data-lucide="message-circle" class="w-3.5 h-3.5 text-blue-400"></i> Keywords gatillo</span>
                        <span class="flex items-center gap-1"><i data-lucide="users" class="w-3.5 h-3.5 text-blue-400"></i> Captura automática</span>
                        <span class="flex items-center gap-1"><i data-lucide="bar-chart-3" class="w-3.5 h-3.5 text-blue-400"></i> Embudo medible</span>
                    </div>
                </div>

                <!-- Social CRM -->
                <div class="bg-gradient-to-br from-slate-50 to-white p-8 rounded-3xl border border-slate-100 card-hover group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-purple-200">
                            <i data-lucide="share-2" class="w-8 h-8"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-purple-500 uppercase tracking-wider">Integración</span>
                            <h3 class="font-display text-xl font-bold text-slate-800">Social CRM</h3>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Integración con Facebook e Instagram. Matching difuso por nombre para identificar automáticamente seguidores y convertirlos en colaboradores del CRM con timeline social unificado.
                    </p>
                    <div class="flex items-center gap-4 text-xs text-slate-400">
                        <span class="flex items-center gap-1"><i data-lucide="facebook" class="w-3.5 h-3.5 text-purple-400"></i> Facebook API</span>
                        <span class="flex items-center gap-1"><i data-lucide="instagram" class="w-3.5 h-3.5 text-purple-400"></i> Instagram Graph</span>
                        <span class="flex items-center gap-1"><i data-lucide="git-merge" class="w-3.5 h-3.5 text-purple-400"></i> Fuzzy matching</span>
                    </div>
                </div>

                <!-- Portal Líder -->
                <div class="bg-gradient-to-br from-slate-50 to-white p-8 rounded-3xl border border-slate-100 card-hover group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-amber-200">
                            <i data-lucide="crown" class="w-8 h-8"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider">Liderazgo</span>
                            <h3 class="font-display text-xl font-bold text-slate-800">Portal del Líder</h3>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Dashboard personal para cada líder territorial con ranking de actividad, feed de acción social en tiempo real, notificaciones de cumpleaños y métricas de gestión.
                    </p>
                    <div class="flex items-center gap-4 text-xs text-slate-400">
                        <span class="flex items-center gap-1"><i data-lucide="trophy" class="w-3.5 h-3.5 text-amber-400"></i> Rankings</span>
                        <span class="flex items-center gap-1"><i data-lucide="bell" class="w-3.5 h-3.5 text-amber-400"></i> Notificaciones</span>
                        <span class="flex items-center gap-1"><i data-lucide="activity" class="w-3.5 h-3.5 text-amber-400"></i> Feed en vivo</span>
                    </div>
                </div>

                <!-- Dashboard Territorial -->
                <div class="bg-gradient-to-br from-slate-50 to-white p-8 rounded-3xl border border-slate-100 card-hover group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-200">
                            <i data-lucide="globe" class="w-8 h-8"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Territorio</span>
                            <h3 class="font-display text-xl font-bold text-slate-800">Dashboard Territorial</h3>
                        </div>
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Mapa interactivo con clustering de publicaciones geoetiquetadas, gráficos de categorías y tendencias temporales. Filtro por comuna para visualizar la actividad social en cada territorio.
                    </p>
                    <div class="flex items-center gap-4 text-xs text-slate-400">
                        <span class="flex items-center gap-1"><i data-lucide="map" class="w-3.5 h-3.5 text-emerald-400"></i> Leaflet map</span>
                        <span class="flex items-center gap-1"><i data-lucide="bar-chart-3" class="w-3.5 h-3.5 text-emerald-400"></i> Chart.js</span>
                        <span class="flex items-center gap-1"><i data-lucide="layers" class="w-3.5 h-3.5 text-emerald-400"></i> MarkerCluster</span>
                    </div>
                </div>
            </div>

            <!-- Tech Stack Bar -->
            <div class="mt-16 text-center">
                <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold mb-4">Stack Tecnológico</p>
                <div class="flex flex-wrap justify-center gap-6 text-xs text-slate-500">
                    <span class="px-4 py-2 bg-slate-50 rounded-xl font-medium">PHP 8+</span>
                    <span class="px-4 py-2 bg-slate-50 rounded-xl font-medium">MySQL</span>
                    <span class="px-4 py-2 bg-slate-50 rounded-xl font-medium">Alpine.js</span>
                    <span class="px-4 py-2 bg-slate-50 rounded-xl font-medium">Facebook Graph API</span>
                    <span class="px-4 py-2 bg-slate-50 rounded-xl font-medium">WhatsApp Cloud API</span>
                    <span class="px-4 py-2 bg-slate-50 rounded-xl font-medium">Leaflet</span>
                    <span class="px-4 py-2 bg-slate-50 rounded-xl font-medium">Chart.js</span>
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
                        Cali es una sola: <br><span class="text-secondary">16 comunas, un solo propósito</span>
                    </h2>
                    <p class="text-slate-300 text-lg mb-10">
                        Nuestra red de padrinos y líderes territoriales trabaja en las 16 comunas de Cali. Desde la Ladera hasta el Oriente, pasando por el Sur y el Centro — cada territorio cuenta con un padrino que gestiona, escucha y transforma.
                    </p>
                    
                    <div class="space-y-6">
                        <div class="flex gap-5">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="navigation" class="w-6 h-6 text-secondary"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold mb-1">Comunas 1-6 (Ladera)</h4>
                                <p class="text-slate-400 text-sm">Gestión comunitaria y proyectos sociales en territorio de ladera.</p>
                            </div>
                        </div>
                        <div class="flex gap-5">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="move-right" class="w-6 h-6 text-secondary"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold mb-1">Comunas 12-15 (Oriente)</h4>
                                <p class="text-slate-400 text-sm">Red de padrinos activa en el oriente de Cali con proyectos de inclusión social.</p>
                            </div>
                        </div>
                        <div class="flex gap-5">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="map-pin" class="w-6 h-6 text-secondary"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold mb-1">Comunas 17-22 (Sur)</h4>
                                <p class="text-slate-400 text-sm">Fortalecimiento del tejido social y acompañamiento a líderes juveniles.</p>
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
                            <i data-lucide="heart-handshake" class="w-8 h-8 text-secondary"></i>
                        </div>
                        <span class="font-display font-extrabold text-2xl text-white uppercase tracking-tighter">Padrinos Cali</span>
                    </div>
                    <p class="text-slate-400 max-w-sm leading-relaxed">
                        "Padrinos Cali: Una red de ciudadanos comprometidos con el liderazgo social y la transformación de Cali desde sus barrios."
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Enlaces</h4>
                    <ul class="space-y-4 text-slate-400 text-sm">
                        <li><a href="#inicio" class="hover:text-secondary transition-colors">Inicio</a></li>
                        <li><a href="#perfil" class="hover:text-secondary transition-colors">Nosotros</a></li>
                        <li><a href="#gestion" class="hover:text-secondary transition-colors">Gestión Social</a></li>
                        <li><a href="#plataforma" class="hover:text-secondary transition-colors">Plataforma</a></li>
                        <li><a href="?page=cp_captura" class="hover:text-secondary transition-colors">Sumarme</a></li>
                        <li><a href="?page=dashboard_organizaciones_publico&campana_id=2" class="hover:text-secondary transition-colors">Territorios</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Redes</h4>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-white hover:bg-secondary hover:text-primary transition-all">
                            <i data-lucide="instagram" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-white hover:bg-secondary hover:text-primary transition-all">
                            <i data-lucide="facebook" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="pt-8 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500 uppercase tracking-widest">
                <div>
                    <p>&copy; 2026 Padrinos Cali — Programa de Liderazgo Social.</p>
                    <p class="text-slate-600 normal-case tracking-normal mt-1">Creado con <a href="https://aratio.mrmtech.net" class="text-secondary hover:underline" target="_blank">Aratio PRO</a> by MRM Tech</p>
                </div>
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
