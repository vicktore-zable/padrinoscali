<!-- Vista Detallada de Colaborador -->
<div class="container mx-auto px-4 py-8">

    <!-- Header con Acciones -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <!-- Avatar -->
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-2xl font-bold">
                    <?= strtoupper(substr($colaborador['nombres'], 0, 1) . substr($colaborador['apellidos'], 0, 1)) ?>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <?= htmlspecialchars(\App\Utils\Helpers::formatFullName($colaborador['nombres'], $colaborador['apellidos'])) ?>
                    </h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-sm text-gray-600"><?= htmlspecialchars($colaborador['documento']) ?></span>
                        <?= \App\Utils\Helpers::estadoBadge($colaborador['estado'], 'estado') ?>
                        <?= \App\Utils\Helpers::estadoBadge($colaborador['perfil'], 'perfil') ?>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex flex-wrap gap-2">
                <?php if (has_permission($user['tipo_usuario'] ?? null, 'colaboradores', 'edit')): ?>
                    <a href="/colaboradores/<?= $colaborador['id'] ?>/edit" class="btn btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                    </a>
                <?php endif; ?>
                <button @click="tab = 'seguidores'; $dispatch('tab-changed', 'seguidores'); setTimeout(() => { document.getElementById('network-graph')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 100);" class="btn btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Ver Red
                </button>
                <a href="/colaboradores" class="btn btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </div>

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

    <!-- Tabs -->
    <div x-data="{ tab: 'general', graphInitialized: false }"
         @tab-changed.window="if ($event.detail === 'seguidores' && !graphInitialized) { setTimeout(() => { initializeNetwork(); graphInitialized = true; }, 100); }"
         class="space-y-6">

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button @click="tab = 'general'"
                        :class="tab === 'general' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Información General
                </button>
                <button @click="tab = 'curriculum'"
                        :class="tab === 'curriculum' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Currículum
                </button>
                <button @click="tab = 'seguidores'; $dispatch('tab-changed', 'seguidores')"
                        :class="tab === 'seguidores' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Red de Seguidores (<?= $totalSeguidores ?? 0 ?>)
                </button>
                <button @click="tab = 'trazabilidad'; $dispatch('tab-changed', 'trazabilidad')"
                        :class="tab === 'trazabilidad' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Trazabilidad
                </button>
                <button @click="tab = 'historial'"
                        :class="tab === 'historial' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Historial Padrino
                </button>
            </nav>
        </div>

        <!-- Tab Content: Información General -->
        <div x-show="tab === 'general'" x-cloak class="space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Información Personal -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Datos Básicos -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-xl font-semibold text-gray-800">Datos Personales</h2>
                        </div>
                        <div class="card-body">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nombres</dt>
                                    <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($colaborador['nombres']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Apellidos</dt>
                                    <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($colaborador['apellidos']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Documento</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <?= htmlspecialchars($colaborador['tipo_documento']) ?> <?= htmlspecialchars($colaborador['documento']) ?>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Fecha de Nacimiento</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <?= \App\Utils\Helpers::formatDate($colaborador['fecha_nacimiento']) ?>
                                        <span class="text-gray-500">(<?= \App\Utils\Helpers::getAge($colaborador['fecha_nacimiento']) ?> años)</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Género</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <?php
                                            $generos = ['M' => 'Masculino', 'F' => 'Femenino', 'O' => 'Otro'];
                                            echo $generos[$colaborador['genero']] ?? $colaborador['genero'];
                                        ?>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Grupo Etario</dt>
                                    <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($colaborador['grupo_etareo'] ?? 'N/A') ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-xl font-semibold text-gray-800">Contacto</h2>
                        </div>
                        <div class="card-body">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <?php if (!empty($colaborador['email'])): ?>
                                            <a href="mailto:<?= htmlspecialchars($colaborador['email']) ?>" class="text-primary-600 hover:text-primary-700">
                                                <?= htmlspecialchars($colaborador['email']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">No registrado</span>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <?php if (!empty($colaborador['telefono'])): ?>
                                            <a href="tel:<?= htmlspecialchars($colaborador['telefono']) ?>" class="text-primary-600 hover:text-primary-700">
                                                <?= htmlspecialchars($colaborador['telefono']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">No registrado</span>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">WhatsApp</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <?php if (!empty($colaborador['telefono_whatsapp'])): ?>
                                            <a href="https://wa.me/57<?= htmlspecialchars($colaborador['telefono_whatsapp']) ?>" class="text-green-600 hover:text-green-700" target="_blank">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                </svg>
                                                <?= htmlspecialchars($colaborador['telefono_whatsapp']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">No registrado</span>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Ubicación</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <?= htmlspecialchars($colaborador['municipio'] ?? '') ?><?= !empty($colaborador['municipio']) && !empty($colaborador['departamento']) ? ', ' : '' ?><?= htmlspecialchars($colaborador['departamento'] ?? '') ?>
                                        <?= !empty($colaborador['barrio']) ? ' - ' . htmlspecialchars($colaborador['barrio']) : '' ?>
                                        <?php if (!empty($colaborador['tipo_territorio'])): ?>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colaborador['tipo_territorio'] === 'Urbano' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                                <?= htmlspecialchars($colaborador['tipo_territorio']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <?php if (!empty($colaborador['direccion'])): ?>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($colaborador['direccion']) ?></dd>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($colaborador['detalle_ubicacion'])): ?>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Detalles de Ubicación</dt>
                                        <dd class="mt-1 text-sm text-gray-900 bg-blue-50 p-3 rounded">
                                            <svg class="w-4 h-4 inline-block mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <?= nl2br(htmlspecialchars($colaborador['detalle_ubicacion'])) ?>
                                        </dd>
                                    </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                    <!-- Perfil Político -->
                    <div class="card">
                        <div class="card-header flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-gray-800">Perfil Político</h2>
                            <button onclick="openReevaluarModal()" class="btn btn-sm bg-purple-600 hover:bg-purple-700 text-white">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reevaluar
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Datos Potencial e Histórico destacados -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-100">
                                <div class="text-center">
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Dato Potencial</dt>
                                    <dd class="mt-1">
                                        <span class="text-3xl font-bold text-blue-600"><?= number_format($colaborador['dato_potencial'] ?? 0) ?></span>
                                    </dd>
                                </div>
                                <div class="text-center border-l border-r border-gray-200">
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Dato Histórico</dt>
                                    <dd class="mt-1">
                                        <span class="text-3xl font-bold text-green-600"><?= number_format($colaborador['dato_historico'] ?? 0) ?></span>
                                    </dd>
                                </div>
                                <div class="text-center">
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</dt>
                                    <dd class="mt-2">
                                        <?= \App\Utils\Helpers::estadoBadge($colaborador['estado'] ?? 'Nuevo', 'estado') ?>
                                    </dd>
                                </div>
                            </div>

                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Perfil</dt>
                                    <dd class="mt-1"><?= \App\Utils\Helpers::estadoBadge($colaborador['perfil'], 'perfil') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nivel de Participación</dt>
                                    <dd class="mt-1"><?= \App\Utils\Helpers::estadoBadge($colaborador['nivel_participacion'], 'participacion') ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Padrino Directo</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <?php if (!empty($liderDirecto)): ?>
                                            <a href="/colaboradores/<?= $liderDirecto['documento'] ?>" class="text-primary-600 hover:text-primary-700">
                                                <?= htmlspecialchars(\App\Utils\Helpers::formatFullName($liderDirecto['nombres'], $liderDirecto['apellidos'])) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-purple-100 text-purple-800">Padrino Principal</span>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <?php if (!empty($colaborador['areas_interes'])): ?>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 mb-2">Áreas de Interés</dt>
                                        <dd class="flex flex-wrap gap-2">
                                            <?php
                                                $areas = is_string($colaborador['areas_interes'])
                                                    ? json_decode($colaborador['areas_interes'], true)
                                                    : $colaborador['areas_interes'];
                                                foreach ($areas as $area):
                                            ?>
                                                <span class="badge bg-blue-100 text-blue-800"><?= htmlspecialchars($area) ?></span>
                                            <?php endforeach; ?>
                                        </dd>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($colaborador['observaciones'])): ?>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Observaciones</dt>
                                        <dd class="mt-1 text-sm text-gray-900"><?= nl2br(htmlspecialchars($colaborador['observaciones'])) ?></dd>
                                    </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                </div>

                <!-- Sidebar con Estadísticas -->
                <div class="space-y-6">

                    <!-- Métricas de Red -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold text-gray-800">Métricas de Red</h3>
                        </div>
                        <div class="card-body space-y-4">
                            <div class="text-center p-4 bg-primary-50 rounded-lg">
                                <div class="text-3xl font-bold text-primary-600"><?= $totalSeguidores ?? 0 ?></div>
                                <div class="text-sm text-gray-600 mt-1">Seguidores Directos</div>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <div class="text-3xl font-bold text-green-600"><?= $totalRedCompleta ?? 0 ?></div>
                                <div class="text-sm text-gray-600 mt-1">Red Completa</div>
                            </div>
                            <?php if (isset($nivelJerarquia)): ?>
                                <div class="text-center p-4 bg-purple-50 rounded-lg">
                                    <div class="text-3xl font-bold text-purple-600"><?= $nivelJerarquia ?></div>
                                    <div class="text-sm text-gray-600 mt-1">Nivel en Jerarquía</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Estado del Sistema -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold text-gray-800">Sistema</h3>
                        </div>
                        <div class="card-body space-y-3">
                            <div>
                                <div class="text-sm font-medium text-gray-500">Estado</div>
                                <div class="mt-1"><?= \App\Utils\Helpers::estadoBadge($colaborador['estado'], 'estado') ?></div>
                            </div>
                            <?php if (!empty($colaborador['created_at'])): ?>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Fecha de Registro</div>
                                <div class="mt-1 text-sm text-gray-900"><?= \App\Utils\Helpers::formatDateTime($colaborador['created_at']) ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($colaborador['updated_at'])): ?>
                                <div>
                                    <div class="text-sm font-medium text-gray-500">Última Actualización</div>
                                    <div class="mt-1 text-sm text-gray-900"><?= \App\Utils\Helpers::formatDateTime($colaborador['updated_at']) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- Tab Content: Currículum -->
        <div x-show="tab === 'curriculum'" x-cloak>
            <?php
            // Verificar si tiene curriculum con datos
            $tieneCurriculum = isset($curriculum) && !empty($curriculum);
            $tieneContenido = false;
            if ($tieneCurriculum) {
                $experiencias = !empty($curriculum['experiencia_laboral']) ? (is_string($curriculum['experiencia_laboral']) ? json_decode($curriculum['experiencia_laboral'], true) : $curriculum['experiencia_laboral']) : [];
                $formaciones = !empty($curriculum['formacion_academica']) ? (is_string($curriculum['formacion_academica']) ? json_decode($curriculum['formacion_academica'], true) : $curriculum['formacion_academica']) : [];
                $participaciones = !empty($curriculum['participacion_politica']) ? (is_string($curriculum['participacion_politica']) ? json_decode($curriculum['participacion_politica'], true) : $curriculum['participacion_politica']) : [];
                $tieneContenido = !empty($curriculum['resumen_profesional']) || !empty($curriculum['habilidades']) || !empty($experiencias) || !empty($formaciones) || !empty($participaciones);
            }
            ?>

            <?php if ($tieneCurriculum && $tieneContenido): ?>
                <!-- Botón de Editar -->
                <div class="flex justify-end mb-4">
                    <a href="/curriculum/<?= $colaborador['id'] ?>/edit" class="btn btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar Curriculum
                    </a>
                </div>

                <div class="space-y-6">
                    <!-- Resumen Profesional -->
                    <?php if (!empty($curriculum['resumen_profesional'])): ?>
                    <div class="card">
                        <div class="card-header bg-purple-700 text-white">
                            <h3 class="text-lg font-semibold flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Perfil Profesional
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-gray-700"><?= nl2br(htmlspecialchars($curriculum['resumen_profesional'])) ?></p>
                            <?php if (!empty($curriculum['habilidades']) || !empty($curriculum['idiomas'])): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t">
                                <?php if (!empty($curriculum['habilidades'])): ?>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-600 mb-2">Habilidades</h4>
                                    <p class="text-gray-700 text-sm"><?= nl2br(htmlspecialchars($curriculum['habilidades'])) ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($curriculum['idiomas'])): ?>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-600 mb-2">Idiomas</h4>
                                    <p class="text-gray-700 text-sm"><?= nl2br(htmlspecialchars($curriculum['idiomas'])) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Experiencia Laboral -->
                    <?php if (!empty($experiencias)): ?>
                    <div class="card">
                        <div class="card-header bg-indigo-700 text-white">
                            <h3 class="text-lg font-semibold flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Experiencia Laboral (<?= count($experiencias) ?>)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <?php foreach ($experiencias as $exp): ?>
                                <div class="border-l-4 border-indigo-500 pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($exp['cargo'] ?? '') ?></h4>
                                            <p class="text-indigo-600 font-medium"><?= htmlspecialchars($exp['empresa'] ?? '') ?></p>
                                        </div>
                                        <div class="text-right text-sm text-gray-500">
                                            <?= htmlspecialchars($exp['fecha_inicio'] ?? '') ?>
                                            <?php if (!empty($exp['actual'])): ?>
                                                - <span class="text-green-600 font-medium">Actual</span>
                                            <?php elseif (!empty($exp['fecha_fin'])): ?>
                                                - <?= htmlspecialchars($exp['fecha_fin']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($exp['descripcion'])): ?>
                                    <p class="text-gray-600 text-sm mt-2"><?= nl2br(htmlspecialchars($exp['descripcion'])) ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Formación Académica -->
                    <?php if (!empty($formaciones)): ?>
                    <div class="card">
                        <div class="card-header bg-teal-700 text-white">
                            <h3 class="text-lg font-semibold flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                                </svg>
                                Formación Académica (<?= count($formaciones) ?>)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <?php foreach ($formaciones as $form): ?>
                                <div class="border-l-4 border-teal-500 pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($form['titulo'] ?? '') ?></h4>
                                            <p class="text-teal-600 font-medium"><?= htmlspecialchars($form['institucion'] ?? '') ?></p>
                                            <?php if (!empty($form['nivel'])): ?>
                                            <span class="inline-block mt-1 px-2 py-1 bg-teal-100 text-teal-800 text-xs rounded"><?= htmlspecialchars($form['nivel']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-right text-sm text-gray-500">
                                            <?php if (!empty($form['en_curso'])): ?>
                                                <span class="text-green-600 font-medium">En curso</span>
                                            <?php elseif (!empty($form['fecha_fin'])): ?>
                                                <?= htmlspecialchars($form['fecha_fin']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Participación Política y Social -->
                    <?php if (!empty($participaciones)): ?>
                    <div class="card">
                        <div class="card-header bg-orange-600 text-white">
                            <h3 class="text-lg font-semibold flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Participación Política y Social (<?= count($participaciones) ?>)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <?php foreach ($participaciones as $part): ?>
                                <div class="border-l-4 border-orange-500 pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($part['cargo'] ?? '') ?></h4>
                                            <p class="text-orange-600 font-medium"><?= htmlspecialchars($part['organizacion'] ?? '') ?></p>
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                <?php if (!empty($part['tipo'])): ?>
                                                <span class="inline-block px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded"><?= htmlspecialchars($part['tipo']) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($part['ambito'])): ?>
                                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded"><?= htmlspecialchars($part['ambito']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-right text-sm text-gray-500">
                                            <?= htmlspecialchars($part['fecha_inicio'] ?? '') ?>
                                            <?php if (!empty($part['actual'])): ?>
                                                - <span class="text-green-600 font-medium">Actual</span>
                                            <?php elseif (!empty($part['fecha_fin'])): ?>
                                                - <?= htmlspecialchars($part['fecha_fin']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($part['poblacion_beneficiada'])): ?>
                                    <p class="text-gray-600 text-sm mt-2"><strong>Población beneficiada:</strong> <?= htmlspecialchars($part['poblacion_beneficiada']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($part['logros'])): ?>
                                    <p class="text-gray-600 text-sm mt-1"><strong>Logros:</strong> <?= nl2br(htmlspecialchars($part['logros'])) ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- No tiene curriculum - Mostrar mensaje para crear -->
                <div class="card">
                    <div class="card-body text-center py-12">
                        <div class="w-24 h-24 mx-auto mb-6 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Curriculum No Disponible</h3>
                        <p class="text-gray-600 mb-6 max-w-md mx-auto">
                            Este colaborador aún no tiene información en su curriculum vitae.
                            <br>
                            <span class="text-yellow-600 font-medium">¡Créalo ahora para completar su perfil!</span>
                        </p>
                        <a href="/curriculum/<?= $colaborador['id'] ?>/edit" class="btn btn-primary px-8 py-3 text-lg">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Crear Curriculum
                        </a>
                        <p class="text-gray-500 text-sm mt-4">
                            Podrás agregar: experiencia laboral, formación académica y participación política
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Content: Seguidores -->
        <div x-show="tab === 'seguidores'" x-cloak>
            <?php if (isset($seguidores) && !empty($seguidores)): ?>

                <!-- Estadísticas de Red -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
                        <p class="text-sm text-blue-800 font-medium">Seguidores Directos</p>
                        <p class="text-2xl font-bold text-blue-900"><?= $totalSeguidores ?></p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
                        <p class="text-sm text-green-800 font-medium">Red Completa</p>
                        <p class="text-2xl font-bold text-green-900"><?= $totalRedCompleta ?></p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4 border-l-4 border-purple-500">
                        <p class="text-sm text-purple-800 font-medium">Niveles de Profundidad</p>
                        <p class="text-2xl font-bold text-purple-900"><?= $nivelesJerarquia ?></p>
                    </div>
                    <div class="bg-orange-50 rounded-lg p-4 border-l-4 border-orange-500">
                        <p class="text-sm text-orange-800 font-medium">Factor de Crecimiento</p>
                        <p class="text-2xl font-bold text-orange-900"><?= $totalSeguidores > 0 ? number_format($totalRedCompleta / $totalSeguidores, 1) : '0' ?>x</p>
                    </div>
                </div>

                <!-- Visualización del Grafo -->
                <div class="card mb-6">
                    <div class="card-header">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Red Jerárquica Multi-Nivel</h3>
                                <p class="text-sm text-gray-500 mt-1">Visualización de hasta 3 niveles de profundidad</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <!-- Controles de Layout -->
                                <div class="flex gap-1">
                                    <button onclick="setLayout('hierarchical')" class="btn btn-sm btn-secondary" title="Vista de Árbol">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    </button>
                                    <button onclick="setLayout('radial')" class="btn btn-sm btn-secondary" title="Vista Radial">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                                        </svg>
                                    </button>
                                    <button onclick="setLayout('force')" class="btn btn-sm btn-secondary" title="Vista de Fuerza">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Controles de Zoom -->
                                <div class="flex gap-1">
                                    <button onclick="zoomIn()" class="btn btn-sm btn-secondary" title="Acercar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                                        </svg>
                                    </button>
                                    <button onclick="zoomOut()" class="btn btn-sm btn-secondary" title="Alejar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 7h-6"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Controles de Vista -->
                                <div class="flex gap-1">
                                    <button onclick="expandNetwork()" class="btn btn-sm btn-secondary" title="Ajustar Vista">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 1v4m0 0h-4m4 0l-5-5"/>
                                        </svg>
                                    </button>
                                    <button onclick="resetNetwork()" class="btn btn-sm btn-outline" title="Centrar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Leyenda de Colores -->
                        <div class="flex flex-wrap gap-4 mb-4 p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded" style="background-color: #3b82f6;"></div>
                                <span class="text-xs text-gray-700">Nivel 0 (Raíz)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full" style="background-color: #10b981;"></div>
                                <span class="text-xs text-gray-700">Nivel 1 (Directos)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full" style="background-color: #f59e0b;"></div>
                                <span class="text-xs text-gray-700">Nivel 2</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full" style="background-color: #8b5cf6;"></div>
                                <span class="text-xs text-gray-700">Nivel 3</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full" style="background-color: #ec4899;"></div>
                                <span class="text-xs text-gray-700">Nivel 4+</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-gray-500"></div>
                                <span class="text-xs text-gray-700">Sin seguidores</span>
                            </div>
                            <div class="mt-2 pt-2 border-t border-gray-300">
                                <p class="text-xs text-gray-600 font-medium">💡 Tamaño = Seguidores</p>
                                <p class="text-xs text-gray-500">Pasa el mouse para ver nombres</p>
                            </div>
                        </div>

                        <div id="network-graph" style="height: 600px; border: 1px solid #e5e7eb; border-radius: 0.5rem; background-color: #fafafa;">
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    <p class="text-gray-500 text-lg font-medium">Cargando visualización de red...</p>
                                    <p class="text-gray-400 text-sm mt-2">Esto puede tomar unos segundos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Force Graph D3.js - Red Simplificada -->
                <div class="card mb-6">
                    <div class="card-header">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">
                                    <svg class="w-5 h-5 inline-block mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    Red de Fuerza Interactiva
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">Visualización con física de fuerzas - Haz clic en un nodo para ver sus detalles</p>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="resetForceGraph()" class="btn btn-sm btn-secondary" title="Reiniciar posiciones">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Leyenda Force Graph -->
                        <div class="flex flex-wrap gap-4 mb-4 p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full" style="background-color: #1e40af;"></div>
                                <span class="text-xs text-gray-700">Colaborador Principal</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded-full" style="background-color: #3b82f6;"></div>
                                <span class="text-xs text-gray-700">&gt;10 seguidores</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full" style="background-color: #10b981;"></div>
                                <span class="text-xs text-gray-700">1-10 seguidores</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" style="background-color: #94a3b8;"></div>
                                <span class="text-xs text-gray-700">Sin seguidores</span>
                            </div>
                            <div class="ml-auto text-xs text-gray-500">
                                <span class="font-medium">💡 Tamaño = Seguidores</span> | Arrastra los nodos | Clic para ver detalle
                            </div>
                        </div>

                        <div id="force-graph-container" style="height: 500px; border: 1px solid #e5e7eb; border-radius: 0.5rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); position: relative;">
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    <p class="text-gray-500">Inicializando grafo de fuerzas...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Seguidores -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-800">Lista de Seguidores Directos</h3>
                    </div>
                    <div class="card-body">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Colaborador</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perfil</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sus Seguidores</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($seguidores as $seguidor): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-sm font-bold">
                                                        <?= strtoupper(substr($seguidor['nombres'], 0, 1) . substr($seguidor['apellidos'], 0, 1)) ?>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?= htmlspecialchars(\App\Utils\Helpers::formatFullName($seguidor['nombres'], $seguidor['apellidos'])) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?= htmlspecialchars($seguidor['documento']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?= \App\Utils\Helpers::estadoBadge($seguidor['perfil'], 'perfil') ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= htmlspecialchars($seguidor['municipio'] ?? '') ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                        </svg>
                                                        <?= $seguidor['total_subseguidores'] ?? 0 ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?= \App\Utils\Helpers::estadoBadge($seguidor['estado'], 'estado') ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="/colaboradores/<?= $seguidor['id'] ?>" class="text-primary-600 hover:text-primary-900">
                                                    Ver detalle
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No hay seguidores directos</h3>
                        <p class="mt-2 text-sm text-gray-500">Este colaborador aún no tiene seguidores en su red.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Content: Trazabilidad de Estados -->
        <div x-show="tab === 'trazabilidad'" x-cloak>
            <?php
            // Datos del historial de estados
            $historialEstados = $historialEstados ?? [];
            $totalCambios = count($historialEstados);
            ?>

            <!-- Estadísticas de Trazabilidad -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
                    <p class="text-sm text-blue-800 font-medium">Dato Potencial Actual</p>
                    <p class="text-2xl font-bold text-blue-900"><?= number_format($colaborador['dato_potencial'] ?? 0) ?></p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
                    <p class="text-sm text-green-800 font-medium">Dato Histórico</p>
                    <p class="text-2xl font-bold text-green-900"><?= number_format($colaborador['dato_historico'] ?? 0) ?></p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border-l-4 border-purple-500">
                    <p class="text-sm text-purple-800 font-medium">Estado Actual</p>
                    <p class="text-lg font-bold text-purple-900"><?= htmlspecialchars($colaborador['estado'] ?? 'N/A') ?></p>
                </div>
                <div class="bg-orange-50 rounded-lg p-4 border-l-4 border-orange-500">
                    <p class="text-sm text-orange-800 font-medium">Total de Cambios</p>
                    <p class="text-2xl font-bold text-orange-900"><?= $totalCambios ?></p>
                </div>
            </div>

            <?php if ($totalCambios > 0): ?>
                <!-- Gráfico de Líneas -->
                <div class="card mb-6">
                    <div class="card-header">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">
                                    <svg class="w-5 h-5 inline-block mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                                    </svg>
                                    Evolución del Dato Potencial
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">Trayectoria histórica del colaborador</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="chart-trazabilidad" style="height: 350px;"></div>
                    </div>
                </div>

                <!-- Tabla de Historial Detallado -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <svg class="w-5 h-5 inline-block mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Historial Detallado de Estados
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dato Potencial</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dato Histórico</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($historialEstados as $index => $registro): ?>
                                        <tr class="hover:bg-gray-50 <?= $index === count($historialEstados) - 1 ? 'bg-green-50' : '' ?>">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                <?= $index + 1 ?>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                <?= \App\Utils\Helpers::formatDateTime($registro['fecha_registro']) ?>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="text-lg font-bold text-primary-600">
                                                    <?= number_format($registro['dato_potencial']) ?>
                                                </span>
                                                <?php
                                                // Calcular variación
                                                if ($index > 0) {
                                                    $anterior = $historialEstados[$index - 1]['dato_potencial'];
                                                    $variacion = $registro['dato_potencial'] - $anterior;
                                                    if ($variacion > 0): ?>
                                                        <span class="ml-2 text-xs text-green-600">+<?= number_format($variacion) ?></span>
                                                    <?php elseif ($variacion < 0): ?>
                                                        <span class="ml-2 text-xs text-red-600"><?= number_format($variacion) ?></span>
                                                    <?php endif;
                                                }
                                                ?>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                                <?= number_format($registro['dato_historico']) ?>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <?= \App\Utils\Helpers::estadoBadge($registro['estado'], 'estado') ?>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <?php
                                                $tipoBadge = [
                                                    'inicial' => 'bg-blue-100 text-blue-800',
                                                    'actualizacion' => 'bg-gray-100 text-gray-800',
                                                    'reevaluacion' => 'bg-purple-100 text-purple-800'
                                                ];
                                                $badgeClass = $tipoBadge[$registro['tipo_cambio']] ?? 'bg-gray-100 text-gray-800';
                                                ?>
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?= $badgeClass ?>">
                                                    <?= ucfirst($registro['tipo_cambio'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate" title="<?= htmlspecialchars($registro['motivo'] ?? '') ?>">
                                                <?= htmlspecialchars($registro['motivo'] ?? '-') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Sin historial -->
                <div class="card">
                    <div class="card-body text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Sin historial de trazabilidad</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            No hay registros históricos de cambios en el dato potencial o estado de este colaborador.
                        </p>
                        <p class="mt-2 text-xs text-gray-400">
                            Los cambios futuros se registrarán automáticamente.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Content: Historial -->
        <div x-show="tab === 'historial'" x-cloak>
            <?php if (isset($historialCambios) && !empty($historialCambios)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <?php foreach ($historialCambios as $index => $cambio): ?>
                                    <li>
                                        <div class="relative pb-8">
                                            <?php if ($index < count($historialCambios) - 1): ?>
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            <?php endif; ?>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-900">
                                                            Cambio de padrino:
                                                            <span class="font-medium"><?= htmlspecialchars($cambio['lider_anterior'] ?? 'Sin padrino') ?></span>
                                                            →
                                                            <span class="font-medium"><?= htmlspecialchars($cambio['lider_nuevo'] ?? 'Sin padrino') ?></span>
                                                        </p>
                                                        <?php if (!empty($cambio['motivo'])): ?>
                                                            <p class="mt-1 text-sm text-gray-500"><?= htmlspecialchars($cambio['motivo']) ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        <?= \App\Utils\Helpers::formatDateTime($cambio['fecha_cambio']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Sin historial de cambios</h3>
                        <p class="mt-2 text-sm text-gray-500">No se han registrado cambios de padrino para este colaborador.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<!-- Vis.js Network - usando CDN de jsDelivr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vis-network@9.1.9/styles/vis-network.min.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/vis-network@9.1.9/standalone/umd/vis-network.min.js"></script>

<!-- D3.js para Force Graph -->
<script src="https://cdn.jsdelivr.net/npm/d3@7"></script>

<!-- Alpine.js -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
let network = null;
let networkData = {
    nodes: [],
    edges: []
};
let initRetries = 0;
const MAX_RETRIES = 10;

function initializeNetwork() {
    console.log('Initializing network graph... (attempt ' + (initRetries + 1) + ')');

    // Verificar que Vis.js esté cargado
    if (typeof vis === 'undefined') {
        initRetries++;
        if (initRetries >= MAX_RETRIES) {
            console.error('Failed to load Vis.js after ' + MAX_RETRIES + ' attempts. Please check your internet connection and refresh the page.');
            alert('Error: No se pudo cargar la librería de visualización de grafos. Por favor recarga la página.');
            return;
        }
        console.warn('Vis.js is not loaded yet. Retrying in 500ms... (' + initRetries + '/' + MAX_RETRIES + ')');
        setTimeout(initializeNetwork, 500);
        return;
    }

    console.log('Vis.js loaded successfully! Version:', vis.Network ? 'OK' : 'ERROR');

    // Limpiar datos anteriores si existen
    networkData.nodes = [];
    networkData.edges = [];

    // Preparar datos del colaborador actual
    const colaborador = <?= json_encode($colaborador) ?>;
    const seguidores = <?= json_encode($seguidores) ?>;
    const nivelesJerarquia = <?= $nivelesJerarquia ?>;

    console.log('Colaborador:', colaborador);
    console.log('Seguidores:', seguidores);
    console.log('Niveles jerarquía:', nivelesJerarquia);

    // Colores por nivel con más variedad
    const coloresPorNivel = [
        { bg: '#3b82f6', border: '#1e40af', highlight: '#2563eb', hover: '#1d4ed8' }, // Nivel 0 - Azul
        { bg: '#10b981', border: '#059669', highlight: '#047857', hover: '#065f46' }, // Nivel 1 - Verde
        { bg: '#f59e0b', border: '#d97706', highlight: '#b45309', hover: '#92400e' }, // Nivel 2 - Naranja
        { bg: '#8b5cf6', border: '#7c3aed', highlight: '#6d28d9', hover: '#5b21b6' }, // Nivel 3 - Púrpura
        { bg: '#ec4899', border: '#db2777', highlight: '#be185d', hover: '#9d174d' }, // Nivel 4 - Rosa
        { bg: '#06b6d4', border: '#0891b2', highlight: '#0e7490', hover: '#0c6478' }, // Nivel 5 - Cyan
        { bg: '#84cc16', border: '#65a30d', highlight: '#4d7c0f', hover: '#3f6212' }, // Nivel 6 - Lime
        { bg: '#f97316', border: '#ea580c', highlight: '#c2410c', hover: '#9a3412' }, // Nivel 7 - Orange
        { bg: '#6366f1', border: '#4f46e5', highlight: '#4338ca', hover: '#3730a3' }, // Nivel 8 - Indigo
        { bg: '#ef4444', border: '#dc2626', highlight: '#b91c1c', hover: '#991b1b' }  // Nivel 9+ - Rojo
    ];

    // Nodo raíz (colaborador actual)
    networkData.nodes.push({
        id: colaborador.id,
        label: `${colaborador.nombres} ${colaborador.apellidos}\n${colaborador.documento}`,
        title: `${colaborador.nombres} ${colaborador.apellidos}\nDocumento: ${colaborador.documento}\nPerfil: ${colaborador.perfil}\nSeguidores directos: ${<?= $totalSeguidores ?>}\nRed completa: ${<?= $totalRedCompleta ?>}\nTamaño del nodo: ${Math.max(30, Math.min(50, 30 + (<?= $totalSeguidores ?> * 1.5)))}`,
        level: 0,
        shape: 'box',
        color: {
            background: coloresPorNivel[0].bg,
            border: coloresPorNivel[0].border,
            highlight: {
                background: coloresPorNivel[0].highlight,
                border: coloresPorNivel[0].border
            }
        },
        font: { color: '#ffffff', size: 13, face: 'arial', bold: true, multi: true, background: 'rgba(0, 0, 0, 0.6)', strokeWidth: 2, strokeColor: 'rgba(0, 0, 0, 0.8)' },
        borderWidth: 3,
        size: Math.max(30, Math.min(50, 30 + (<?= $totalSeguidores ?> * 1.5)))
    });

    // Función recursiva para agregar nodos y edges
    function agregarNodos(seguidores, nivelActual, parentId) {
        if (!seguidores || seguidores.length === 0) return;

        const colorNivel = coloresPorNivel[Math.min(nivelActual, coloresPorNivel.length - 1)];

        seguidores.forEach(seguidor => {
            const subseguidores = seguidor.total_subseguidores || 0;
            const tieneSubseguidores = subseguidores > 0;

            // Agregar nodo
            networkData.nodes.push({
                id: seguidor.id,
                label: `${seguidor.nombres} ${seguidor.apellidos}\n${seguidor.documento}`,
                title: `${seguidor.nombres} ${seguidor.apellidos}\nNivel ${nivelActual}\nDocumento: ${seguidor.documento}\nPerfil: ${seguidor.perfil}\nSus seguidores: ${subseguidores}\nTamaño del nodo: ${Math.max(20, Math.min(60, 20 + (subseguidores * 3)))}`,
                level: nivelActual,
                shape: tieneSubseguidores ? 'ellipse' : 'dot',
                color: {
                    background: tieneSubseguidores ? colorNivel.bg : '#6b7280',
                    border: tieneSubseguidores ? colorNivel.border : '#4b5563',
                    highlight: {
                        background: tieneSubseguidores ? colorNivel.highlight : '#374151',
                        border: tieneSubseguidores ? colorNivel.border : '#1f2937'
                    }
                },
                font: {
                    color: '#ffffff',
                    size: Math.max(9, 12 - nivelActual),
                    multi: true,
                    background: 'rgba(0, 0, 0, 0.6)',
                    strokeWidth: 2,
                    strokeColor: 'rgba(0, 0, 0, 0.8)'
                },
                borderWidth: tieneSubseguidores ? 2 : 1,
                size: Math.max(20, Math.min(60, 20 + (subseguidores * 3)))
            });

            // Agregar edge
            networkData.edges.push({
                from: parentId,
                to: seguidor.id,
                arrows: 'to',
                color: {
                    color: tieneSubseguidores ? colorNivel.border : '#9ca3af',
                    highlight: colorNivel.bg
                },
                width: tieneSubseguidores ? 2 : 1,
                smooth: { type: 'cubicBezier' }
            });

            // Recursivamente agregar subseguidores
            if (seguidor.subseguidores && seguidor.subseguidores.length > 0) {
                agregarNodos(seguidor.subseguidores, nivelActual + 1, seguidor.id);
            }
        });
    }

    // Agregar todos los niveles
    agregarNodos(seguidores, 1, colaborador.id);

    // Configuración del grafo
    const options = {
        nodes: {
            shadow: true,
            font: {
                multi: 'html',
                align: 'center'
            },
            margin: {
                top: 10,
                bottom: 10,
                left: 15,
                right: 15
            },
            scaling: {
                min: 10,
                max: 50,
                label: {
                    enabled: true,
                    min: 8,
                    max: 14
                }
            }
        },
        edges: {
            shadow: true,
            smooth: {
                type: 'cubicBezier',
                forceDirection: 'vertical',
                roundness: 0.4
            },
            arrows: {
                to: {
                    enabled: true,
                    scaleFactor: 0.5
                }
            }
        },
        layout: {
            hierarchical: {
                direction: 'UD',  // Up-Down
                sortMethod: 'directed',
                nodeSpacing: 180,
                levelSeparation: 250,
                treeSpacing: 220
            }
        },
        physics: {
            enabled: false,
            stabilization: {
                enabled: true,
                iterations: 100
            }
        },
        interaction: {
            hover: true,
            tooltipDelay: 100,
            navigationButtons: true,
            keyboard: true,
            dragNodes: true,
            dragView: true,
            zoomView: true,
            selectConnectedEdges: false
        },
        manipulation: {
            enabled: false
        }
    };

    // Crear red
    const container = document.getElementById('network-graph');
    if (container) {
        console.log('Container found, creating network with', networkData.nodes.length, 'nodes and', networkData.edges.length, 'edges');
        network = new vis.Network(container, networkData, options);

        // Evento click en nodo
        network.on('click', function(params) {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                if (nodeId !== colaborador.id) {
                    window.location.href = `/colaboradores/${nodeId}`;
                }
            }
        });

        // Ajustar vista con mejor configuración
        setTimeout(() => {
            network.fit({
                animation: {
                    duration: 1000,
                    easingFunction: 'easeInOutQuad'
                }
            });
        }, 500);

        console.log('Network graph initialized successfully!');
    } else {
        console.error('Container #network-graph not found!');
    }
}

function expandNetwork() {
    if (network) {
        network.fit({
            animation: {
                duration: 800,
                easingFunction: 'easeInOutQuad'
            }
        });
    }
}

function zoomIn() {
    if (network) {
        const scale = network.getScale();
        network.moveTo({
            scale: scale * 1.2,
            animation: {
                duration: 300,
                easingFunction: 'easeInOutQuad'
            }
        });
    }
}

function zoomOut() {
    if (network) {
        const scale = network.getScale();
        network.moveTo({
            scale: scale * 0.8,
            animation: {
                duration: 300,
                easingFunction: 'easeInOutQuad'
            }
        });
    }
}

function resetNetwork() {
    if (network) {
        network.moveTo({
            position: {x: 0, y: 0},
            scale: 1,
            animation: {
                duration: 500,
                easingFunction: 'easeInOutQuad'
            }
        });
    }
}

function setLayout(layoutType) {
    if (!network) return;

    let newOptions = {};

    switch(layoutType) {
        case 'hierarchical':
            newOptions = {
                layout: {
                    hierarchical: {
                        direction: 'UD',
                        sortMethod: 'directed',
                        nodeSpacing: 180,
                        levelSeparation: 250,
                        treeSpacing: 220
                    }
                },
                physics: { enabled: false }
            };
            break;

        case 'radial':
            newOptions = {
                layout: {
                    improvedLayout: false,
                    hierarchical: false
                },
                physics: {
                    enabled: true,
                    barnesHut: {
                        gravitationalConstant: -2000,
                        centralGravity: 0.1,
                        springLength: 150,
                        springConstant: 0.05
                    },
                    stabilization: {
                        enabled: true,
                        iterations: 100
                    }
                }
            };
            break;

        case 'force':
            newOptions = {
                layout: {
                    improvedLayout: false,
                    hierarchical: false
                },
                physics: {
                    enabled: true,
                    barnesHut: {
                        gravitationalConstant: -8000,
                        centralGravity: 0.3,
                        springLength: 200,
                        springConstant: 0.04,
                        damping: 0.09
                    },
                    stabilization: {
                        enabled: true,
                        iterations: 200
                    }
                }
            };
            break;
    }

    network.setOptions(newOptions);

    // Re-estabilizar después de cambiar layout
    setTimeout(() => {
        network.fit({
            animation: {
                duration: 1000,
                easingFunction: 'easeInOutQuad'
            }
        });
    }, 100);
}

// ============================================
// D3.js Force Graph - Red Simplificada
// ============================================
let forceSimulation = null;
let forceGraphInitialized = false;

function initializeForceGraph() {
    if (forceGraphInitialized) return;
    if (typeof d3 === 'undefined') {
        console.warn('D3.js not loaded yet, retrying...');
        setTimeout(initializeForceGraph, 500);
        return;
    }

    console.log('Initializing D3.js Force Graph...');

    const container = document.getElementById('force-graph-container');
    if (!container) {
        console.error('Force graph container not found');
        return;
    }

    // Limpiar contenedor
    container.innerHTML = '';

    // Datos del colaborador y seguidores
    const colaborador = <?= json_encode($colaborador) ?>;
    const seguidores = <?= json_encode($seguidores) ?>;

    // Preparar nodos y enlaces
    const nodes = [];
    const links = [];

    // Función recursiva para contar total de seguidores en subniveles
    function contarTotalSeguidores(seg) {
        let total = 0;
        if (seg.subseguidores && seg.subseguidores.length > 0) {
            total = seg.subseguidores.length;
            seg.subseguidores.forEach(sub => {
                total += contarTotalSeguidores(sub);
            });
        }
        return total;
    }

    // Nodo principal (colaborador actual)
    nodes.push({
        id: colaborador.id,
        nombre: `${colaborador.nombres} ${colaborador.apellidos}`,
        documento: colaborador.documento,
        perfil: colaborador.perfil || 'N/A',
        seguidores: <?= $totalSeguidores ?? 0 ?>,
        isRoot: true
    });

    // Función recursiva para agregar nodos
    function agregarNodos(lista, parentId, nivel) {
        if (!lista || lista.length === 0) return;

        lista.forEach(seg => {
            const totalSub = seg.total_subseguidores || contarTotalSeguidores(seg);

            nodes.push({
                id: seg.id,
                nombre: `${seg.nombres} ${seg.apellidos}`,
                documento: seg.documento,
                perfil: seg.perfil || 'N/A',
                seguidores: totalSub,
                nivel: nivel,
                isRoot: false
            });

            links.push({
                source: parentId,
                target: seg.id
            });

            // Recursivamente agregar subseguidores
            if (seg.subseguidores && seg.subseguidores.length > 0) {
                agregarNodos(seg.subseguidores, seg.id, nivel + 1);
            }
        });
    }

    agregarNodos(seguidores, colaborador.id, 1);

    console.log('Force Graph - Nodes:', nodes.length, 'Links:', links.length);

    if (nodes.length === 0) {
        container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No hay datos para visualizar</div>';
        return;
    }

    // Dimensiones
    const width = container.clientWidth;
    const height = container.clientHeight;

    // Escala de tamaño exponencial
    const maxSeguidores = Math.max(...nodes.map(n => n.seguidores), 1);
    const sizeScale = d3.scalePow()
        .exponent(0.6)
        .domain([0, maxSeguidores])
        .range([8, 45]);

    // Color por cantidad de seguidores
    function getNodeColor(d) {
        if (d.isRoot) return '#1e40af'; // Azul oscuro para raíz
        if (d.seguidores > 10) return '#3b82f6'; // Azul
        if (d.seguidores > 5) return '#22c55e';  // Verde
        if (d.seguidores > 0) return '#10b981';  // Verde claro
        return '#94a3b8'; // Gris
    }

    // Crear SVG
    const svg = d3.select(container)
        .append('svg')
        .attr('width', width)
        .attr('height', height)
        .style('cursor', 'grab');

    // Definir marcador de flecha
    svg.append('defs').append('marker')
        .attr('id', 'arrowhead-force')
        .attr('viewBox', '-0 -5 10 10')
        .attr('refX', 20)
        .attr('refY', 0)
        .attr('orient', 'auto')
        .attr('markerWidth', 6)
        .attr('markerHeight', 6)
        .append('path')
        .attr('d', 'M 0,-5 L 10,0 L 0,5')
        .attr('fill', '#94a3b8');

    // Grupo para zoom
    const g = svg.append('g');

    // Zoom
    const zoom = d3.zoom()
        .scaleExtent([0.3, 3])
        .on('zoom', (event) => {
            g.attr('transform', event.transform);
        });
    svg.call(zoom);

    // Tooltip
    const tooltip = d3.select('body').append('div')
        .attr('class', 'force-tooltip')
        .style('position', 'fixed')
        .style('visibility', 'hidden')
        .style('background', 'white')
        .style('border', '1px solid #e5e7eb')
        .style('border-radius', '8px')
        .style('padding', '10px 14px')
        .style('font-size', '12px')
        .style('box-shadow', '0 4px 12px rgba(0,0,0,0.15)')
        .style('z-index', '9999')
        .style('pointer-events', 'none')
        .style('max-width', '250px');

    // Simulación de fuerzas
    forceSimulation = d3.forceSimulation(nodes)
        .force('link', d3.forceLink(links).id(d => d.id).distance(100))
        .force('charge', d3.forceManyBody().strength(-400))
        .force('center', d3.forceCenter(width / 2, height / 2))
        .force('collision', d3.forceCollide().radius(d => sizeScale(d.seguidores) + 10));

    // Enlaces
    const link = g.append('g')
        .selectAll('line')
        .data(links)
        .join('line')
        .attr('stroke', '#cbd5e1')
        .attr('stroke-width', 1.5)
        .attr('stroke-opacity', 0.6)
        .attr('marker-end', 'url(#arrowhead-force)');

    // Nodos
    const node = g.append('g')
        .selectAll('circle')
        .data(nodes)
        .join('circle')
        .attr('r', d => sizeScale(d.seguidores))
        .attr('fill', d => getNodeColor(d))
        .attr('stroke', d => d.isRoot ? '#1e3a8a' : '#fff')
        .attr('stroke-width', d => d.isRoot ? 4 : 2)
        .style('cursor', 'pointer')
        .call(d3.drag()
            .on('start', dragstarted)
            .on('drag', dragged)
            .on('end', dragended));

    // Etiquetas
    const label = g.append('g')
        .selectAll('text')
        .data(nodes)
        .join('text')
        .text(d => d.nombre.split(' ').slice(0, 2).join(' '))
        .attr('font-size', d => d.isRoot ? '11px' : '9px')
        .attr('font-weight', d => d.isRoot ? 'bold' : 'normal')
        .attr('fill', '#374151')
        .attr('text-anchor', 'middle')
        .attr('dy', d => sizeScale(d.seguidores) + 12)
        .style('pointer-events', 'none');

    // Eventos de nodo
    node.on('mouseover', function(event, d) {
        d3.select(this)
            .transition().duration(200)
            .attr('r', sizeScale(d.seguidores) * 1.2)
            .attr('stroke-width', 4);

        tooltip.html(`
            <div style="font-weight: bold; color: #1e40af; margin-bottom: 5px;">${d.nombre}</div>
            <div style="color: #6b7280; font-size: 11px; margin-bottom: 3px;">Doc: ${d.documento}</div>
            <div style="color: #6b7280; font-size: 11px; margin-bottom: 3px;">Perfil: ${d.perfil}</div>
            <div style="color: #059669; font-weight: bold;">Seguidores: ${d.seguidores}</div>
            ${!d.isRoot ? '<div style="margin-top: 6px; padding-top: 6px; border-top: 1px solid #e5e7eb; color: #3b82f6; font-size: 10px;">🖱️ Clic para ver detalle</div>' : '<div style="margin-top: 6px; color: #8b5cf6; font-size: 10px;">👑 Colaborador Principal</div>'}
        `)
            .style('visibility', 'visible')
            .style('left', (event.clientX + 15) + 'px')
            .style('top', (event.clientY - 10) + 'px');
    })
    .on('mousemove', function(event) {
        tooltip
            .style('left', (event.clientX + 15) + 'px')
            .style('top', (event.clientY - 10) + 'px');
    })
    .on('mouseout', function(event, d) {
        d3.select(this)
            .transition().duration(200)
            .attr('r', sizeScale(d.seguidores))
            .attr('stroke-width', d.isRoot ? 4 : 2);

        tooltip.style('visibility', 'hidden');
    })
    .on('click', function(event, d) {
        // Navegar al detalle del colaborador (excepto si es el nodo actual)
        if (!d.isRoot) {
            window.location.href = '/colaboradores/' + d.id;
        }
    });

    // Tick de simulación
    forceSimulation.on('tick', () => {
        link
            .attr('x1', d => d.source.x)
            .attr('y1', d => d.source.y)
            .attr('x2', d => d.target.x)
            .attr('y2', d => d.target.y);

        node
            .attr('cx', d => d.x)
            .attr('cy', d => d.y);

        label
            .attr('x', d => d.x)
            .attr('y', d => d.y);
    });

    // Funciones de arrastre
    function dragstarted(event, d) {
        if (!event.active) forceSimulation.alphaTarget(0.3).restart();
        d.fx = d.x;
        d.fy = d.y;
        svg.style('cursor', 'grabbing');
    }

    function dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
    }

    function dragended(event, d) {
        if (!event.active) forceSimulation.alphaTarget(0);
        d.fx = null;
        d.fy = null;
        svg.style('cursor', 'grab');
    }

    // Centrar vista inicial
    setTimeout(() => {
        svg.transition().duration(500).call(
            zoom.transform,
            d3.zoomIdentity.translate(0, 0).scale(0.9)
        );
    }, 500);

    forceGraphInitialized = true;
    console.log('Force Graph initialized successfully!');
}

function resetForceGraph() {
    if (forceSimulation) {
        forceSimulation.alpha(1).restart();
    }
}

// Inicializar Force Graph cuando se cambia a la pestaña de seguidores
document.addEventListener('tab-changed', function(e) {
    if (e.detail === 'seguidores') {
        setTimeout(initializeForceGraph, 300);
    }
});

// También intentar inicializar si ya está visible
window.addEventListener('load', function() {
    setTimeout(() => {
        const container = document.getElementById('force-graph-container');
        if (container && container.offsetParent !== null) {
            initializeForceGraph();
        }
    }, 1000);
});
</script>

<!-- Modal de Reevaluación -->
<div id="modalReevaluar" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeReevaluarModal()"></div>

        <!-- Modal Content -->
        <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
            <form id="formReevaluar" onsubmit="submitReevaluacion(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center mb-4">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Reevaluar Colaborador
                        </h3>
                    </div>

                    <div class="space-y-4">
                        <!-- Info actual -->
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-600">
                                <strong><?= htmlspecialchars($colaborador['nombres'] . ' ' . $colaborador['apellidos']) ?></strong>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Dato Potencial actual: <span class="font-bold text-blue-600"><?= number_format($colaborador['dato_potencial'] ?? 0) ?></span> |
                                Estado: <span class="font-bold"><?= $colaborador['estado'] ?? 'N/A' ?></span>
                            </p>
                        </div>

                        <!-- Nuevo Dato Potencial -->
                        <div>
                            <label for="nuevo_dato_potencial" class="block text-sm font-medium text-gray-700">
                                Nuevo Dato Potencial <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="nuevo_dato_potencial" id="nuevo_dato_potencial"
                                   value="<?= $colaborador['dato_potencial'] ?? 0 ?>"
                                   min="0" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-lg font-bold">
                            <p class="mt-1 text-xs text-gray-500">Ingrese el nuevo valor del dato potencial</p>
                        </div>

                        <!-- Motivo -->
                        <div>
                            <label for="motivo_reevaluacion" class="block text-sm font-medium text-gray-700">
                                Motivo de la Reevaluación <span class="text-red-500">*</span>
                            </label>
                            <select name="motivo_reevaluacion" id="motivo_reevaluacion" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Seleccione un motivo...</option>
                                <option value="Reevaluación periódica">Reevaluación periódica</option>
                                <option value="Actualización de datos">Actualización de datos</option>
                                <option value="Cambio de actividad">Cambio de actividad</option>
                                <option value="Verificación en campo">Verificación en campo</option>
                                <option value="Corrección de error">Corrección de error</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <!-- Detalle adicional -->
                        <div>
                            <label for="detalle_motivo" class="block text-sm font-medium text-gray-700">
                                Detalle adicional (opcional)
                            </label>
                            <textarea name="detalle_motivo" id="detalle_motivo" rows="2"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                      placeholder="Agregue detalles si es necesario..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="btnSubmitReevaluar"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Guardar Reevaluación
                    </button>
                    <button type="button" onclick="closeReevaluarModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>

                <input type="hidden" name="colaborador_id" value="<?= $colaborador['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            </form>
        </div>
    </div>
</div>

<!-- Script para Modal de Reevaluación -->
<script>
function openReevaluarModal() {
    document.getElementById('modalReevaluar').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeReevaluarModal() {
    document.getElementById('modalReevaluar').classList.add('hidden');
    document.body.style.overflow = '';
}

async function submitReevaluacion(event) {
    event.preventDefault();

    const form = document.getElementById('formReevaluar');
    const btn = document.getElementById('btnSubmitReevaluar');
    const originalText = btn.innerHTML;

    // Construir motivo completo
    const motivoSelect = document.getElementById('motivo_reevaluacion').value;
    const detalleMotivo = document.getElementById('detalle_motivo').value;
    const motivoCompleto = detalleMotivo ? `${motivoSelect}: ${detalleMotivo}` : motivoSelect;

    // Disable button
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Guardando...';

    try {
        const response = await fetch('/colaboradores/<?= $colaborador['id'] ?>/reevaluar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
            },
            body: JSON.stringify({
                nuevo_dato_potencial: document.getElementById('nuevo_dato_potencial').value,
                motivo: motivoCompleto
            })
        });

        const result = await response.json();

        if (result.success) {
            // Mostrar mensaje de éxito y recargar
            alert('✅ Reevaluación guardada correctamente');
            window.location.reload();
        } else {
            alert('❌ Error: ' + (result.message || 'No se pudo guardar la reevaluación'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error de conexión. Intente nuevamente.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Cerrar modal con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReevaluarModal();
    }
});
</script>

<!-- ApexCharts para Trazabilidad -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
// Inicializar gráfico de trazabilidad cuando se cambia a esa pestaña
let chartTrazabilidad = null;
let trazabilidadInitialized = false;

document.addEventListener('alpine:init', () => {
    Alpine.effect(() => {
        // Escuchar cambio de pestaña
    });
});

// Evento para inicializar el gráfico cuando se muestra la pestaña
document.addEventListener('tab-changed', function(e) {
    if (e.detail === 'trazabilidad' && !trazabilidadInitialized) {
        setTimeout(initChartTrazabilidad, 100);
        trazabilidadInitialized = true;
    }
});

// También inicializar si ya está en la pestaña
window.addEventListener('load', function() {
    // Si la URL tiene #trazabilidad o si Alpine ya está en esa pestaña
    if (window.location.hash === '#trazabilidad') {
        setTimeout(initChartTrazabilidad, 500);
        trazabilidadInitialized = true;
    }
});

function initChartTrazabilidad() {
    const container = document.getElementById('chart-trazabilidad');
    if (!container) {
        console.log('Container chart-trazabilidad not found');
        return;
    }

    // Datos del historial de estados
    const historialEstados = <?= json_encode($historialEstados ?? []) ?>;

    if (historialEstados.length === 0) {
        console.log('No hay datos de historial');
        return;
    }

    // Preparar datos para el gráfico
    const fechas = historialEstados.map(h => h.fecha_registro);
    const datosPotencial = historialEstados.map(h => parseInt(h.dato_potencial) || 0);
    const datosHistorico = historialEstados.map(h => parseInt(h.dato_historico) || 0);

    // Mapear estados a valores numéricos para visualización
    const estadoMap = {
        'Nuevo': 1,
        'Creció': 2,
        'Igual': 3,
        'Decrece': 4,
        'Desvinculado': 5
    };
    const estadosNum = historialEstados.map(h => estadoMap[h.estado] || 0);

    const options = {
        series: [
            {
                name: 'Dato Potencial',
                type: 'line',
                data: datosPotencial
            },
            {
                name: 'Dato Histórico',
                type: 'line',
                data: datosHistorico
            }
        ],
        chart: {
            height: 350,
            type: 'line',
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
                }
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        colors: ['#3b82f6', '#10b981'],
        stroke: {
            width: [3, 2],
            curve: 'smooth',
            dashArray: [0, 5]
        },
        markers: {
            size: [6, 4],
            strokeWidth: 2,
            hover: {
                size: 8
            }
        },
        xaxis: {
            categories: fechas,
            type: 'datetime',
            labels: {
                datetimeFormatter: {
                    year: 'yyyy',
                    month: "MMM 'yy",
                    day: 'dd MMM',
                    hour: 'HH:mm'
                },
                style: {
                    fontSize: '11px'
                }
            },
            title: {
                text: 'Fecha'
            }
        },
        yaxis: [
            {
                title: {
                    text: 'Dato Potencial',
                    style: {
                        color: '#3b82f6'
                    }
                },
                labels: {
                    formatter: function(val) {
                        return val.toFixed(0);
                    },
                    style: {
                        colors: '#3b82f6'
                    }
                },
                min: 0
            },
            {
                opposite: true,
                title: {
                    text: 'Dato Histórico',
                    style: {
                        color: '#10b981'
                    }
                },
                labels: {
                    formatter: function(val) {
                        return val.toFixed(0);
                    },
                    style: {
                        colors: '#10b981'
                    }
                },
                min: 0
            }
        ],
        tooltip: {
            shared: true,
            intersect: false,
            x: {
                format: 'dd MMM yyyy HH:mm'
            },
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const registro = historialEstados[dataPointIndex];
                return `
                    <div class="p-3 bg-white shadow-lg rounded-lg border">
                        <div class="font-bold text-gray-800 mb-2">${new Date(registro.fecha_registro).toLocaleString('es-CO')}</div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                            <span class="text-sm">Potencial: <strong>${registro.dato_potencial}</strong></span>
                        </div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-3 h-3 rounded-full bg-green-500"></span>
                            <span class="text-sm">Histórico: <strong>${registro.dato_historico}</strong></span>
                        </div>
                        <div class="text-sm text-gray-600 mt-2 pt-2 border-t">
                            Estado: <strong>${registro.estado}</strong><br>
                            Tipo: ${registro.tipo_cambio || 'N/A'}
                            ${registro.motivo ? '<br>Motivo: ' + registro.motivo : ''}
                        </div>
                    </div>
                `;
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center'
        },
        grid: {
            borderColor: '#e7e7e7',
            row: {
                colors: ['#f3f3f3', 'transparent'],
                opacity: 0.5
            }
        },
        annotations: {
            points: historialEstados.map((h, i) => {
                if (h.tipo_cambio === 'reevaluacion') {
                    return {
                        x: new Date(h.fecha_registro).getTime(),
                        y: parseInt(h.dato_potencial),
                        marker: {
                            size: 8,
                            fillColor: '#8b5cf6',
                            strokeColor: '#fff',
                            radius: 2
                        },
                        label: {
                            borderColor: '#8b5cf6',
                            text: 'Reevaluación',
                            style: {
                                fontSize: '10px',
                                background: '#8b5cf6',
                                color: '#fff'
                            }
                        }
                    };
                }
                return null;
            }).filter(Boolean)
        }
    };

    // Destruir chart anterior si existe
    if (chartTrazabilidad) {
        chartTrazabilidad.destroy();
    }

    chartTrazabilidad = new ApexCharts(container, options);
    chartTrazabilidad.render();

    console.log('Chart trazabilidad initialized with', historialEstados.length, 'data points');
}

// Agregar listener para el evento de Alpine.js
window.addEventListener('DOMContentLoaded', function() {
    // Verificar si la pestaña ya está activa
    setTimeout(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'trazabilidad') {
            initChartTrazabilidad();
            trazabilidadInitialized = true;
        }
    }, 500);
});
</script>

<style>
[x-cloak] { display: none !important; }
</style>
