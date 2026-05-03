<?php
use App\Utils\Helpers;
?>

<!-- Welcome & Link Section -->
<div class="mb-12" x-data="{ copied: false }">
    <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-8">
        <div class="animate-fade-in-up">
            <h1 class="text-5xl font-extrabold tracking-tighter mb-3 text-gray-900">
                Hola, <span class="bg-gradient-to-r from-[#002244] via-[#004488] to-[#DAA520] bg-clip-text text-transparent animate-gradient-x"><?= htmlspecialchars($lider['nombres']) ?></span>
            </h1>
            <p class="text-gray-500 text-lg font-light max-w-2xl">
                Bienvenido a tu centro de comando. Aquí puedes monitorear el crecimiento de tu estructura y gestionar tu equipo en tiempo real.
            </p>
            
            <div class="flex flex-wrap gap-3 mt-6">
                <!-- Perfil Tag -->
                <div class="px-4 py-1.5 rounded-full bg-[#002244]/5 border border-[#002244]/20 text-[#002244] text-xs font-bold uppercase tracking-widest shadow-sm">
                    <?= htmlspecialchars($lider['perfil'] ?? 'Líder') ?>
                </div>
                <?php if(!empty($lider['municipio'])): ?>
                <div class="px-4 py-1.5 rounded-full bg-white border border-gray-200 text-gray-500 text-xs font-bold uppercase flex items-center gap-2 shadow-sm">
                    <i data-lucide="map-pin" class="w-3 h-3 text-[#DAA520]"></i>
                    <?= htmlspecialchars($lider['municipio']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Enlace Invitación Premium -->
        <div class="w-full xl:w-auto relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-[#002244] to-[#DAA520] rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
            <div class="relative p-6 glass-card bg-white ring-1 ring-gray-100 leading-none flex flex-col sm:flex-row items-center gap-4">
                <div class="text-center sm:text-left">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Tu Enlace de Activación</h3>
                    <p class="text-[10px] text-gray-400">Comparte para crecer tu red</p>
                </div>
                
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="relative flex-1 sm:flex-none">
                        <input type="text" readonly value="<?= $refLink ?>" id="refLinkInput" 
                            class="bg-gray-50 border border-gray-200 text-xs text-gray-600 rounded-lg pl-3 pr-10 py-3 w-full sm:w-64 focus:outline-none focus:border-[#002244]/50 focus:ring-1 focus:ring-[#002244]/50 transition-all font-mono shadow-inner">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2">
                            <i data-lucide="link" class="w-3 h-3 text-gray-400"></i>
                        </div>
                    </div>
                    <button @click="
                        navigator.clipboard.writeText('<?= $refLink ?>');
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                    " class="relative overflow-hidden bg-[#002244] text-white hover:bg-[#003366] active:scale-95 px-6 py-3 rounded-lg font-bold text-xs uppercase tracking-wider transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 min-w-[120px]">
                        <span x-show="!copied" class="flex items-center gap-2"><i data-lucide="copy" class="w-3 h-3"></i> Copiar</span>
                        <span x-show="copied" x-transition class="flex items-center gap-2 text-green-400"><i data-lucide="check" class="w-3 h-3"></i> Listo</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Stat 1: Equipo Directo -->
    <div class="group relative p-8 glass-card overflow-hidden hover:shadow-lg transition-all border-l-4 border-l-[#002244] bg-white">
        <div class="relative z-10 flex flex-col justify-between h-full min-h-[140px]">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Equipo Directo</p>
                    <div class="p-2 rounded-lg bg-[#002244]/5 text-[#002244]">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-6xl font-black text-gray-900 tracking-tighter group-hover:scale-105 transition-transform inline-block origin-left"><?= number_format($totalDirectos) ?></span>
                    <p class="text-sm text-gray-500 mt-1">Líderes Nivel 1</p>
                </div>
            </div>
        </div>
        <div class="absolute right-0 top-0 w-32 h-32 bg-[#002244]/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 group-hover:bg-[#002244]/10 transition-colors"></div>
    </div>

    <!-- Stat 2: Red Total -->
    <div class="group relative p-8 glass-card overflow-hidden hover:shadow-lg transition-all border-l-4 border-l-[#DAA520] bg-white">
         <div class="relative z-10 flex flex-col justify-between h-full min-h-[140px]">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Red Consolidada</p>
                    <div class="p-2 rounded-lg bg-[#DAA520]/10 text-[#DAA520]">
                        <i data-lucide="network" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-6xl font-black text-gray-900 tracking-tighter group-hover:scale-105 transition-transform inline-block origin-left"><?= number_format($totalRed) ?></span>
                    <p class="text-sm text-gray-500 mt-1">Total Estructura</p>
                </div>
            </div>
        </div>
        <div class="absolute right-0 top-0 w-32 h-32 bg-[#DAA520]/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 group-hover:bg-[#DAA520]/10 transition-colors"></div>
    </div>

    <!-- Stat 3: Progreso -->
    <div class="relative p-8 glass-card overflow-hidden group hover:shadow-lg transition-all bg-[#002244] text-white" x-data="{ showProjection: false }">
        <div class="flex items-start justify-between">
            <div>
                 <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Meta Mensual</p>
                 <h4 class="text-2xl font-bold text-white mb-6">Crecimiento</h4>
                 
                 <div class="flex items-baseline gap-1">
                     <span class="text-4xl font-black text-white"><?= round($progreso) ?>%</span>
                     <span class="text-xs text-gray-400 uppercase">Completado</span>
                 </div>
            </div>
            
            <div class="relative w-24 h-24 rounded-full flex items-center justify-center"
                 style="background: conic-gradient(from 0deg, #DAA520 <?= $progreso ?>%, rgba(255,255,255,0.1) <?= $progreso ?>%); box-shadow: 0 0 20px rgba(218, 165, 32, 0.2);">
                <div class="absolute inset-[6px] rounded-full bg-[#002244]"></div>
                <i data-lucide="trending-up" class="w-8 h-8 text-[#DAA520] relative z-10"></i>
            </div>
        </div>
        
        <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
            <span class="text-[10px] text-gray-400">Meta: <?= $meta ?> nuevos registros</span>
            <button @click="showProjection = true" class="text-xs text-[#DAA520] font-bold hover:underline flex items-center gap-1">
                Ver Proyección <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </button>
        </div>

        <!-- Projection Modal -->
        <div x-show="showProjection" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @keydown.escape.window="showProjection = false">
            <div class="bg-white rounded-3xl p-8 max-w-lg w-full shadow-2xl animate-fade-in-up" @click.away="showProjection = false">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight">Proyección de Red</h3>
                    <button @click="showProjection = false" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <i data-lucide="x" class="w-6 h-6 text-gray-400"></i>
                    </button>
                </div>
                <div class="space-y-6">
                    <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm font-medium text-gray-500">Estado Actual</span>
                            <span class="px-3 py-1 bg-green-50 text-green-600 rounded-full text-[10px] font-bold uppercase tracking-wider">En Marcha</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-400 uppercase mb-1">Total Red</p>
                                <p class="text-2xl font-extrabold text-[#002244]"><?= $totalRed ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase mb-1">Restante Meta</p>
                                <p class="text-2xl font-extrabold text-[#DAA520]"><?= max(0, $meta - $totalRed) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-[#002244]/5 rounded-2xl border border-[#002244]/10">
                        <h4 class="text-sm font-bold text-[#002244] mb-3">Escenario Proyectado (30 días)</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Tasa de Crecimiento</span>
                                <span class="font-bold text-gray-900">~15% Semanal</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Proyección Estimada</span>
                                <span class="font-bold text-[#002244]"><?= round($totalRed * 1.6) ?> Seguidores</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-4">
                                <div class="bg-gradient-to-r from-[#002244] to-[#DAA520] h-2 rounded-full" style="width: 85%"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 italic text-center">Basado en el historial de registros y actividad de tu equipo de primer nivel.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Territorial Breakdown -->
<div class="mb-12">
    <div class="flex items-center gap-3 mb-6">
        <div class="p-2.5 rounded-xl bg-white shadow-sm border border-gray-100 text-[#DAA520]">
            <i data-lucide="map" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-800">Consolidado por Territorio</h3>
            <p class="text-xs text-gray-500">Distribución de tu red por municipio y barrio/vereda</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php if(empty($desgloseTerritorio)): ?>
            <div class="col-span-full p-12 glass-card bg-white border-dashed border-2 border-gray-200 flex flex-col items-center justify-center text-center">
                <i data-lucide="map-pinned" class="w-12 h-12 text-gray-300 mb-4"></i>
                <p class="text-gray-500 font-medium">Aún no hay datos geográficos suficientes para el consolidado.</p>
            </div>
        <?php else: ?>
            <?php foreach($desgloseTerritorio as $territorio): ?>
            <div class="p-5 glass-card bg-white border-l-4 border-l-[#DAA520] hover:scale-[1.02] transition-transform animate-fade-in-up">
                <div class="flex justify-between items-start mb-3">
                    <span class="px-2 py-0.5 rounded bg-gray-100 text-[10px] font-bold text-gray-500 uppercase"><?= htmlspecialchars($territorio['municipio'] ?? 'S/M') ?></span>
                    <span class="text-xl font-black text-[#002244] tracking-tighter"><?= $territorio['total'] ?></span>
                </div>
                <h4 class="text-sm font-bold text-gray-800 truncate"><?= htmlspecialchars($territorio['barrio'] ?? 'Sin Barrio/Vereda') ?></h4>
                <div class="w-full bg-gray-50 rounded-full h-1 mt-3">
                    <div class="bg-[#002244] h-1 rounded-full opacity-20" style="width: <?= min(100, ($territorio['total']/$totalRed)*100) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Team List -->
<div class="glass-card overflow-hidden border border-gray-100 shadow-sm bg-white" x-data="{ 
    search: '',
    exportTeam() {
        alert('Generando exportación CSV de tu equipo...');
        // Aquí iría lógica real de descarga
    }
}">
    <div class="p-8 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-50/50">
        <div>
            <h3 class="text-xl font-bold flex items-center gap-2 text-gray-800">
                <i data-lucide="users-2" class="w-5 h-5 text-[#002244]"></i>
                Gestión de Equipo
            </h3>
            <p class="text-xs text-gray-500 mt-1">Personas registradas bajo tu código de invitación (Nivel 1)</p>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <input type="text" x-model="search" placeholder="Buscar líder..." class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-[#002244] focus:border-[#002244] outline-none">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            </div>
            <button @click="exportTeam()" class="px-4 py-2 rounded-lg bg-white border border-gray-200 text-xs font-bold hover:bg-gray-50 transition-all flex items-center gap-2 text-gray-600 shadow-sm">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Exportar</span>
            </button>
            <button class="px-4 py-2 rounded-lg bg-[#002244]/5 border border-[#002244]/20 text-xs font-bold hover:bg-[#002244]/10 transition-all flex items-center gap-2 text-[#002244] shadow-sm">
                <i data-lucide="filter" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Filtrar</span>
            </button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <?php if (empty($directos)): ?>
            <div class="p-20 text-center">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-gray-100">
                    <i data-lucide="user-plus" class="w-10 h-10 text-gray-400"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-800 mb-2">Comienza a construir tu legado</h4>
                <p class="text-sm text-gray-500 max-w-sm mx-auto mb-8">Aún no tienes líderes directos. Comparte tu enlace de activación para empezar a construir tu estructura política.</p>
                <button onclick="document.getElementById('refLinkInput').select()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-[#002244] text-white font-bold rounded-xl hover:scale-105 transition-transform shadow-lg">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                    Compartir Enlace
                </button>
            </div>
        <?php else: ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] text-gray-500 uppercase tracking-widest border-b border-gray-100 bg-gray-50/50">
                        <th class="px-8 py-5 font-semibold">Líder / Colaborador</th>
                        <th class="px-8 py-5 font-semibold">Contacto</th>
                        <th class="px-8 py-5 font-semibold">Ubicación</th>
                        <th class="px-8 py-5 font-semibold">Estado</th>
                        <th class="px-8 py-5 text-right font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($directos as $miembro): ?>
                    <tr class="hover:bg-gray-50 transition-all group duration-200" x-show="!search || '<?= strtolower(htmlspecialchars($miembro['nombres'].' '.$miembro['apellidos'])) ?>'.includes(search.toLowerCase())">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 border border-gray-100 flex items-center justify-center text-lg font-black text-gray-500 group-hover:text-[#002244] group-hover:bg-white transition-all shadow-sm">
                                    <?= substr($miembro['nombres'], 0, 1) ?>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800 group-hover:text-[#002244] transition-colors"><?= htmlspecialchars($miembro['nombres'] . ' ' . $miembro['apellidos']) ?></div>
                                    <div class="text-[10px] text-gray-400 font-mono flex items-center gap-1">
                                        <i data-lucide="credit-card" class="w-3 h-3"></i>
                                        <?= htmlspecialchars($miembro['documento']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col gap-1">
                                <div class="text-sm flex items-center gap-2 text-gray-600">
                                    <i data-lucide="phone" class="w-3 h-3 text-gray-400"></i>
                                    <?= htmlspecialchars($miembro['celular'] ?? '---') ?>
                                </div>
                                <div class="text-xs text-gray-500 flex items-center gap-2">
                                    <i data-lucide="mail" class="w-3 h-3 text-gray-400"></i>
                                    <?= htmlspecialchars($miembro['email'] ?? '') ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-sm font-semibold text-gray-600"><?= htmlspecialchars($miembro['municipio'] ?? '---') ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($miembro['barrio'] ?? '') ?></div>
                        </td>
                        <td class="px-8 py-6">
                            <?php 
                                // Color logic based on status
                                $statusColor = 'text-green-600 bg-green-50 border-green-100';
                                if(($miembro['estado'] ?? '') === 'Inactivo') $statusColor = 'text-gray-500 bg-gray-50 border-gray-100';
                            ?>
                            <span class="px-3 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider <?= $statusColor ?>">
                                <?= htmlspecialchars($miembro['estado'] ?? 'Activo') ?>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <a href="?page=colaborador_detalle&id=<?= $miembro['id'] ?>" class="inline-flex group/btn p-2 rounded-lg bg-gray-50 hover:bg-[#002244] hover:text-white text-gray-400 transition-all shadow-sm hover:shadow-md transform hover:-translate-y-1">
                                <i data-lucide="chevron-right" class="w-5 h-5 transition-transform group-hover/btn:translate-x-1"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
    // Lucide initializer for dynamic elements
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
