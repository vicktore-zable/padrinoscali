<?php /** MÓDULO: Centro de Ayuda */ ?>
<div class="space-y-6" x-data="{seccion: 'inicio'}">
    <div><h1 class="text-3xl font-bold">Centro de Ayuda</h1><p class="text-gray-600 mt-2">Documentación y soporte del sistema Aratio</p></div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1">
            <div class="card space-y-2">
                <button @click="seccion = 'inicio'" :class="seccion === 'inicio' ? 'bg-primary/10 text-primary' : 'hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-lg transition"><i data-lucide="home" class="w-4 h-4 inline mr-2"></i>Inicio Rápido</button>
                <button @click="seccion = 'donaciones'" :class="seccion === 'donaciones' ? 'bg-primary/10 text-primary' : 'hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-lg transition"><i data-lucide="dollar-sign" class="w-4 h-4 inline mr-2"></i>Donaciones</button>
                <button @click="seccion = 'eventos'" :class="seccion === 'eventos' ? 'bg-primary/10 text-primary' : 'hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-lg transition"><i data-lucide="calendar" class="w-4 h-4 inline mr-2"></i>Eventos</button>
                <button @click="seccion = 'acciones'" :class="seccion === 'acciones' ? 'bg-primary/10 text-primary' : 'hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-lg transition"><i data-lucide="map-pin" class="w-4 h-4 inline mr-2"></i>Acciones Comunitarias</button>
                <button @click="seccion = 'compromisos'" :class="seccion === 'compromisos' ? 'bg-primary/10 text-primary' : 'hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-lg transition"><i data-lucide="handshake" class="w-4 h-4 inline mr-2"></i>Compromisos</button>
                <button @click="seccion = 'reportes'" :class="seccion === 'reportes' ? 'bg-primary/10 text-primary' : 'hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-lg transition"><i data-lucide="bar-chart-3" class="w-4 h-4 inline mr-2"></i>Reportes</button>
                <button @click="seccion = 'faq'" :class="seccion === 'faq' ? 'bg-primary/10 text-primary' : 'hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-lg transition"><i data-lucide="help-circle" class="w-4 h-4 inline mr-2"></i>Preguntas Frecuentes</button>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div x-show="seccion === 'inicio'" class="card">
                <h2 class="text-2xl font-bold mb-4">Bienvenido a Aratio</h2>
                <div class="prose max-w-none">
                    <p class="text-gray-600">Aratio es un sistema integral de gestión electoral diseñado para administrar campañas políticas de manera eficiente.</p>
                    <h3 class="text-xl font-bold mt-6 mb-3">Primeros Pasos</h3>
                    <ol class="space-y-2 text-gray-600">
                        <li><strong>1. Selecciona tu campaña</strong> - Usa el selector en el header para cambiar entre campañas</li>
                        <li><strong>2. Configura tu perfil</strong> - Actualiza tu información personal en Configuración</li>
                        <li><strong>3. Explora el dashboard</strong> - Visualiza las estadísticas principales de tu campaña</li>
                        <li><strong>4. Registra donaciones</strong> - Gestiona los aportes económicos de la campaña</li>
                        <li><strong>5. Programa eventos</strong> - Organiza recorridos, reuniones y actividades</li>
                        <li><strong>6. Documenta acciones</strong> - Registra el trabajo territorial puerta a puerta</li>
                        <li><strong>7. Gestiona compromisos</strong> - Usa la metodología de las 5 preguntas</li>
                        <li><strong>8. Genera reportes</strong> - Analiza el progreso con gráficos interactivos</li>
                    </ol>
                    <h3 class="text-xl font-bold mt-6 mb-3">Características Principales</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li>✅ <strong>Multi-tenant:</strong> Gestiona múltiples campañas simultáneamente</li>
                        <li>✅ <strong>Jerarquía territorial:</strong> Sistema de 5 niveles geográficos</li>
                        <li>✅ <strong>Metodología de compromisos:</strong> Las 5 preguntas estructuradas</li>
                        <li>✅ <strong>Mapas interactivos:</strong> Visualización geográfica con Leaflet</li>
                        <li>✅ <strong>Reportes dinámicos:</strong> Gráficos y análisis en tiempo real</li>
                        <li>✅ <strong>Gestión de donaciones:</strong> Control completo de aportes</li>
                    </ul>
                </div>
            </div>

            <div x-show="seccion === 'faq'" class="space-y-4">
                <div class="card">
                    <h3 class="font-bold text-gray-900 mb-2">¿Cómo cambio de campaña?</h3>
                    <p class="text-gray-600">Usa el selector de campañas ubicado en el header, al centro de la pantalla.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold text-gray-900 mb-2">¿Cómo registro una donación?</h3>
                    <p class="text-gray-600">Ve al módulo de Donaciones, haz clic en "Nueva Donación" y completa el formulario con los datos del donante y el monto.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold text-gray-900 mb-2">¿Qué son las 5 preguntas de compromisos?</h3>
                    <p class="text-gray-600">Es una metodología estructurada: ¿Qué?, ¿Quién?, ¿Cuándo?, ¿Dónde?, ¿Cómo? para documentar compromisos comunitarios.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold text-gray-900 mb-2">¿Puedo exportar los reportes?</h3>
                    <p class="text-gray-600">Sí, todos los módulos tienen botón de exportación a Excel/PDF.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-gradient-to-r from-primary to-secondary text-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold mb-2">¿Necesitas más ayuda?</h3>
                <p class="opacity-90">Contacta con nuestro equipo de soporte</p>
            </div>
            <div class="flex gap-3">
                <a href="mailto:soporte@aratio.com" class="bg-white text-primary px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition"><i data-lucide="mail" class="w-5 h-5 inline mr-2"></i>Email</a>
                <a href="https://wa.me/573001234567" target="_blank" class="bg-white text-primary px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition"><i data-lucide="phone" class="w-5 h-5 inline mr-2"></i>WhatsApp</a>
            </div>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
