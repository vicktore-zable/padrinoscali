<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Agenda de Campaña</h1>
            <p class="text-gray-500">Próximos eventos y actividades</p>
        </div>
    </div>

    <!-- Filtros rápidos (Visuales por ahora) -->
    <div class="flex gap-2 overflow-x-auto pb-2">
        <button class="px-4 py-2 rounded-full bg-blue-900 text-white text-sm font-medium whitespace-nowrap">Todos</button>
        <button class="px-4 py-2 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium whitespace-nowrap">Reuniones</button>
        <button class="px-4 py-2 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium whitespace-nowrap">Caminatas</button>
        <button class="px-4 py-2 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium whitespace-nowrap">Capacitación</button>
    </div>

    <!-- Lista de Eventos -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <?php if (empty($events)): ?>
            <div class="col-span-full text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="calendar-off" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">No hay eventos programados</h3>
                <p class="text-gray-500 mt-1">Revisa más tarde para nuevas convocatorias.</p>
            </div>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="h-32 bg-gradient-to-r from-blue-900 to-blue-800 relative p-6">
                        <div class="absolute top-4 right-4">
                            <span class="px-2 py-1 bg-white/20 backdrop-blur-sm text-white text-xs rounded-lg uppercase font-bold tracking-wider">
                                <?= htmlspecialchars($event['tipo'] ?? 'Evento') ?>
                            </span>
                        </div>
                        <div class="text-white">
                            <p class="text-3xl font-bold"><?= date('d', strtotime($event['fecha_inicio'])) ?></p>
                            <p class="text-blue-200 uppercase text-sm"><?= strftime('%b', strtotime($event['fecha_inicio'])) ?></p>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2"><?= htmlspecialchars($event['nombre']) ?></h3>
                        
                        <div class="space-y-3 mb-4">
                            <div class="flex items-start gap-3 text-sm text-gray-600">
                                <i data-lucide="clock" class="w-4 h-4 mt-0.5 text-yellow-500"></i>
                                <span><?= date('h:i A', strtotime($event['fecha_inicio'])) ?></span>
                            </div>
                            <div class="flex items-start gap-3 text-sm text-gray-600">
                                <i data-lucide="map-pin" class="w-4 h-4 mt-0.5 text-yellow-500"></i>
                                <span class="line-clamp-1"><?= htmlspecialchars($event['ubicacion'] ?? 'Ubicación por definir') ?></span>
                            </div>
                        </div>

                        <p class="text-sm text-gray-500 line-clamp-3 mb-4">
                            <?= htmlspecialchars($event['descripcion'] ?? '') ?>
                        </p>

                        <button class="w-full py-2 px-4 border border-blue-900 text-blue-900 rounded-lg hover:bg-blue-50 font-medium text-sm transition-colors">
                            Ver Detalles
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
