<?php
$fases_labels = [
    'asignado' => 'Asignado',
    'contactado' => 'Contactado',
    'activado' => 'Activado',
    'comprometido' => 'Comprometido',
    'movilizado' => 'Movilizado'
];
$fases_colors = [
    'asignado' => 'bg-gray-100 text-gray-600',
    'contactado' => 'bg-blue-50 text-blue-700',
    'activado' => 'bg-amber-50 text-amber-700',
    'comprometido' => 'bg-green-50 text-green-700',
    'movilizado' => 'bg-purple-50 text-purple-700'
];
$intereses_labels = [
    'brigadas' => 'Brigadas', 'talleres' => 'Talleres',
    'logistica' => 'Logística', 'formacion' => 'Formación',
    'ambiental' => 'Ambiental'
];
?>
<div x-data="voluntariadoApp()" class="space-y-8">
    <div>
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Mis Voluntarios</h1>
        <p class="text-gray-500 mt-1">Gestiona el seguimiento de los voluntarios asignados a tu red.</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-5 gap-4">
        <div class="glass-card p-4 rounded-xl text-center animate-fade-in-up" style="animation-delay: 0s">
            <p class="text-2xl font-black text-gray-900"><?= $total ?></p>
            <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Total</p>
        </div>
        <?php foreach ($fases as $f): ?>
        <div class="glass-card p-4 rounded-xl text-center animate-fade-in-up" style="animation-delay: 0.1s">
            <p class="text-2xl font-black text-gray-900"><?= $por_fase[$f] ?? 0 ?></p>
            <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold"><?= $fases_labels[$f] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($total === 0): ?>
    <div class="glass-card p-12 text-center animate-fade-in">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="users" class="w-10 h-10 text-gray-400"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-700 mb-2">No tienes voluntarios asignados</h2>
        <p class="text-gray-400 max-w-md mx-auto">Cuando el administrador te asigne voluntarios, aparecerán aquí para que puedas contactarlos y hacerles seguimiento.</p>
    </div>
    <?php else: ?>
    <!-- Lista de voluntarios -->
    <div class="space-y-4">
        <?php foreach ($voluntarios as $v):
            $intereses = json_decode($v['voluntario_intereses'] ?? '[]', true) ?: [];
            $disponibilidad = json_decode($v['voluntario_disponibilidad'] ?? '{}', true);
            $dias = $disponibilidad['dias'] ?? [];
            $jornada = $disponibilidad['jornada'] ?? '';
            $dias_labels = ['lun'=>'Lun','mar'=>'Mar','mie'=>'Mié','jue'=>'Jue','vie'=>'Vie','sab'=>'Sáb'];
        ?>
        <div class="glass-card p-6 rounded-2xl animate-fade-in-up" x-data="{ open: false }">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-aratio-blue to-aratio-navy flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                        <?= strtoupper(substr($v['nombres'], 0, 1)) ?>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($v['nombres']) ?></h3>
                        <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                            <span class="flex items-center gap-1"><i data-lucide="phone" class="w-3.5 h-3.5"></i> <?= htmlspecialchars($v['telefono']) ?></span>
                            <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i> <?= htmlspecialchars($v['territorio']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider <?= $fases_colors[$v['voluntario_fase']] ?? 'bg-gray-100 text-gray-600' ?>">
                        <?= $fases_labels[$v['voluntario_fase']] ?? $v['voluntario_fase'] ?>
                    </span>
                    <button @click="open = !open" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>

            <div x-show="open" x-collapse class="mt-6 pt-6 border-t border-gray-100">
                <!-- Intereses -->
                <?php if (!empty($intereses)): ?>
                <div class="mb-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Áreas de interés</p>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($intereses as $int): ?>
                        <span class="px-3 py-1 bg-aratio-blue/5 text-aratio-blue rounded-lg text-xs font-medium"><?= $intereses_labels[$int] ?? $int ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Disponibilidad -->
                <?php if (!empty($dias) || $jornada): ?>
                <div class="mb-6">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Disponibilidad</p>
                    <div class="flex items-center gap-3">
                        <?php if (!empty($dias)): ?>
                        <div class="flex gap-1">
                            <?php foreach ($dias as $d): ?>
                            <span class="px-2 py-1 bg-gray-100 rounded text-xs font-medium text-gray-600"><?= $dias_labels[$d] ?? $d ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($jornada): ?>
                        <span class="text-xs text-gray-500">· <?= $jornada === 'flexible' ? 'Horario flexible' : 'Jornada ' . ucfirst($jornada) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Acciones -->
                <div class="flex items-center gap-3">
                    <a href="tel:<?= htmlspecialchars($v['telefono']) ?>" class="px-4 py-2 bg-green-50 text-green-700 rounded-xl text-sm font-semibold hover:bg-green-100 transition-colors flex items-center gap-2">
                        <i data-lucide="phone-call" class="w-4 h-4"></i>
                        Llamar
                    </a>
                    <a href="https://wa.me/57<?= preg_replace('/[^0-9]/', '', $v['telefono']) ?>" target="_blank" class="px-4 py-2 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-semibold hover:bg-emerald-100 transition-colors flex items-center gap-2">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        WhatsApp
                    </a>

                    <!-- Avanzar fase -->
                    <?php
                    $siguiente_fase = '';
                    $orden = ['asignado' => 'contactado', 'contactado' => 'activado', 'activado' => 'comprometido', 'comprometido' => 'movilizado'];
                    if ($v['voluntario_fase'] !== 'movilizado'):
                        $siguiente_fase = $orden[$v['voluntario_fase']] ?? '';
                    endif;
                    ?>
                    <?php if ($siguiente_fase): ?>
                    <button @click="avanzarFase(<?= $v['id'] ?>, '<?= $siguiente_fase ?>')"
                            :disabled="loading"
                            class="px-4 py-2 bg-aratio-blue text-white rounded-xl text-sm font-semibold hover:bg-aratio-navy transition-all flex items-center gap-2 disabled:opacity-50">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        Avanzar a «<?= $fases_labels[$siguiente_fase] ?>»
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Fecha registro -->
                <p class="mt-4 text-xs text-gray-400">Registrado el <?= date('d/m/Y', strtotime($v['created_at'])) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Toast -->
    <div x-show="toast.show" x-transition class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl text-white text-sm font-medium shadow-lg flex items-center gap-3"
         :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'">
        <i :class="toast.type === 'success' ? 'lucide-check-circle' : 'lucide-x-circle'" class="w-5 h-5"></i>
        <span x-text="toast.message"></span>
        <button @click="toast.show = false" class="ml-auto text-white/70 hover:text-white">&times;</button>
    </div>
</div>

<script>
function voluntariadoApp() {
    return {
        loading: false,
        toast: { show: false, message: '', type: 'success' },
        async avanzarFase(id, fase) {
            this.loading = true;
            try {
                const form = new FormData();
                form.append('id', id);
                form.append('fase', fase);
                const res = await fetch('?page=portal_mis_voluntarios&action=avanzar_fase', {
                    method: 'POST',
                    body: form
                });
                const json = await res.json();
                if (json.success) {
                    this.toast = { show: true, message: json.message, type: 'success' };
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.toast = { show: true, message: json.message, type: 'error' };
                }
            } catch(e) {
                this.toast = { show: true, message: 'Error de conexión', type: 'error' };
            } finally {
                this.loading = false;
                setTimeout(() => this.toast.show = false, 4000);
            }
        }
    };
}
</script>
