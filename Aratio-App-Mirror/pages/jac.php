<?php
require_once __DIR__ . '/../root_config.php';

/**
 * Módulo JAC v1.7.0 — Admin CRUD con Planchas Legales
 * Acceso: index.php?page=jac
 */

$db = getDB();
$campanaId = $campanaActiva['id'] ?? null;
$user      = getSessionUser();
$userRol   = $user['rol'] ?? 'colaborador';
$isAdmin   = in_array($userRol, ['super-admin','admin-campana','admin','supervisor']);

// Stats rápidas
$stats = ['total'=>0,'activas'=>0,'total_votos'=>0,'territorios_cubiertos'=>0];
if ($campanaId) {
    try {
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total,
                   SUM(CASE WHEN estado='activa' THEN 1 ELSE 0 END) AS activas,
                   COALESCE(SUM(votos_comprometidos),0) AS total_votos,
                   COUNT(DISTINCT territorio_valle) AS territorios_cubiertos
            FROM jac_registros WHERE id_campana = ?
        ");
        $stmt->execute([$campanaId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: $stats;
    } catch (Exception $e) {}
}
?>
<style>
.jac-card{background:rgba(255,255,255,.97);backdrop-filter:blur(10px);border:1px solid rgba(212,175,55,.15);box-shadow:0 8px 32px rgba(30,58,95,.06)}
.bloque-header{background:linear-gradient(90deg, #1e3a5f 0%, #2c5282 100%);color:white;padding:6px 12px;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:1px}
[x-cloak]{display:none!important}
.spin{animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
</style>

<div class="space-y-6 pb-20" x-data="jacAdmin()" x-init="init()">

  <!-- ── Header ───────────────────────────────────────────────────────── -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <h1 class="text-3xl font-black text-[#1e3a5f] tracking-tight flex items-center gap-3">
        <div class="w-12 h-12 bg-[#1e3a5f] rounded-2xl flex items-center justify-center shadow-lg">
          <i data-lucide="building-2" class="w-6 h-6 text-[#d4af37]"></i>
        </div>
        Entidades / Organizaciones
      </h1>
      <p class="text-gray-500 font-medium mt-1 ml-15 italic">Gestión de Entidades v1.7.0 — Jerarquía Territorial & Estructura</p>
    </div>
    <?php if ($campanaActiva): ?>
    <div class="flex items-center gap-3">
      <a href="?page=mod_jac" 
         class="flex items-center gap-2 px-5 py-2.5 bg-[#0d9488] text-white rounded-xl font-black text-sm hover:bg-[#0f766e] transition shadow-lg">
        <i data-lucide="map" class="w-4 h-4"></i> Dashboard JAC
      </a>
      <?php if ($isAdmin): ?>
      <button @click="openJacModal()"
              class="flex items-center gap-2 px-6 py-2.5 bg-[#1e3a5f] text-white rounded-xl font-black text-sm hover:bg-[#d4af37] hover:text-[#1e3a5f] transition shadow-xl">
        <i data-lucide="plus" class="w-5 h-5"></i> Nueva Entidad / Organización
      </button>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($campanaActiva): ?>

  <!-- ── Filtros ──────────────────────────────────────────────────────── -->
  <div class="jac-card rounded-3xl overflow-hidden p-6 flex flex-wrap gap-4 items-center">
    <div class="relative flex-1 min-w-[300px]">
        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-3.5 text-gray-400"></i>
        <input type="text" x-model="filters.search" @input.debounce.400ms="loadData()"
               placeholder="Buscar por Organización, Presidente o Barrio..."
               class="pl-10 pr-4 py-3 border border-gray-100 rounded-2xl text-sm focus:ring-2 focus:ring-[#1e3a5f] w-full bg-gray-50/50">
    </div>
    <select x-model="filters.municipio" @change="loadData()"
            class="px-4 py-3 border border-gray-100 rounded-2xl text-sm bg-gray-50/50 focus:ring-2 focus:ring-[#1e3a5f]">
        <option value="">Todos los Municipios</option>
        <template x-for="m in municipios" :key="m"><option :value="m" x-text="m"></option></template>
    </select>
    <div class="ml-auto text-[10px] font-black text-gray-400 uppercase tracking-widest" x-text="`${rows.length} Entidades Registradas`"></div>
  </div>

  <!-- ── Lista ────────────────────────────────────────────────────────── -->
  <div class="grid grid-cols-1 gap-4">
    <template x-if="loading">
        <div class="py-20 text-center"><i data-lucide="loader-2" class="w-10 h-10 spin mx-auto text-[#1e3a5f]"></i></div>
    </template>
    
    <template x-for="row in rows" :key="row.id">
        <div class="jac-card rounded-3xl overflow-hidden group">
            <div class="p-6 transition-colors hover:bg-gray-50/50 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 bg-[#1e3a5f]/5 rounded-2xl flex items-center justify-center group-hover:bg-[#1e3a5f] transition-all">
                        <i data-lucide="building-2" class="w-6 h-6 text-[#1e3a5f] group-hover:text-[#d4af37]"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-[#1e3a5f] uppercase tracking-tight" x-text="row.nombre_jac"></h4>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-black rounded" x-text="row.tipo_organizacion || 'Entidad'"></span>
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-black rounded" x-text="row.municipio"></span>
                            <span class="text-[10px] text-gray-400 font-bold" x-text="row.sector || 'Sin Barrio'"></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 text-sm flex-1 max-w-xl">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Presidente / Coordinador</p>
                        <p class="font-bold text-[#1e3a5f]" x-text="row.presidente || 'No asignado'"></p>
                        <p class="text-[10px] text-gray-400" x-text="row.telefono || ''"></p>
                    </div>
                    <div class="hidden lg:block">
                        <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Carga Electoral</p>
                        <div class="flex items-center gap-3">
                            <div><p class="font-black text-gray-700" x-text="row.afiliados_count || 0"></p><p class="text-[8px] uppercase text-gray-400">Afiliados</p></div>
                            <div class="w-px h-6 bg-gray-100"></div>
                            <div><p class="font-black text-[#d4af37]" x-text="row.votos_comprometidos || 0"></p><p class="text-[8px] uppercase text-gray-400">Votos</p></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2" @click.stop>
                    <button @click="editJac(row)" class="p-2 border border-gray-100 rounded-xl text-gray-400 hover:bg-gray-100 transition"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                    <button @click="deleteJac(row.id, row.nombre_jac)" class="p-2 border border-gray-100 rounded-xl text-red-300 hover:bg-red-50 hover:text-red-500 transition"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                </div>
            </div>

            <!-- Panel Expandible de Miembros -->
            <div x-show="expanded === row.id" x-cloak x-transition class="border-t border-gray-50 bg-gray-50/30 p-8 pt-6">
                <div class="flex items-center justify-between mb-6">
                    <h5 class="text-sm font-black text-[#1e3a5f] uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-magenta"></i>
                        Estructura Organizacional
                    </h5>
                    <div class="flex gap-2">
                      <button @click="openNewPlancha(row)" class="px-4 py-2 bg-[#1e3a5f] text-white text-[10px] font-black rounded-lg hover:bg-magenta transition">CREAR NUEVA PLANCHA</button>
                    </div>
                </div>

                <template x-if="loadingPlanchas === row.id">
                    <p class="text-xs text-center py-4">Cargando...</p>
                </template>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <template x-for="pl in (planchasCache[row.id] || [])" :key="pl.id">
                        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                            <div class="p-4 bg-gray-50/50 flex items-center justify-between border-b border-gray-50">
                                <div>
                                    <span class="font-black text-[#1e3a5f] text-sm uppercase mr-2" x-text="pl.nombre"></span>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-black" :class="pl.estado==='activa'?'bg-green-100 text-green-700':'bg-gray-200 text-gray-500'" x-text="pl.estado.toUpperCase()"></span>
                                </div>
                                <div class="flex gap-1">
                                    <button @click="editPlancha(pl, row)" class="p-1.5 text-gray-400 hover:text-[#1e3a5f]"><i data-lucide="edit-3" class="w-3 h-3"></i></button>
                                    <button @click="deletePlancha(pl.id, pl.nombre, row.id)" class="p-1.5 text-red-300 hover:text-red-500"><i data-lucide="trash-2" class="w-3 h-3"></i></button>
                                    <template x-if="pl.estado !== 'activa'">
                                        <button @click="activarPlancha(pl.id, row.id)" class="ml-2 px-3 py-1 bg-green-500 text-white text-[9px] font-black rounded hover:bg-green-600">ACTIVAR</button>
                                    </template>
                                </div>
                            </div>
                            <!-- Tabla Miembros (Agrupados por Bloque) -->
                            <div class="flex-1 overflow-y-auto max-h-[300px]">
                                <template x-for="bloq in bloquesLegales" :key="bloq">
                                    <div>
                                        <div class="bloque-header" x-text="bloq"></div>
                                        <table class="w-full text-[11px]">
                                            <template x-for="m in (pl.miembros || []).filter(mb => mb.bloque === bloq)" :key="m.id">
                                                <tr class="border-b border-gray-50">
                                                    <td class="px-4 py-2 font-bold text-gray-700 w-1/2" x-text="m.nombre"></td>
                                                    <td class="px-4 py-2 text-gray-400 italic" x-text="m.cargo"></td>
                                                </tr>
                                            </template>
                                            <template x-if="(pl.miembros || []).filter(mb => mb.bloque === bloq).length === 0">
                                                <tr><td colspan="2" class="px-4 py-1.5 text-[10px] text-gray-300 italic">No asignados</td></tr>
                                            </template>
                                        </table>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
  </div>

  <?php endif; ?>

  <!-- ── MODAL JAC 1.7.0 (Geografía Completa) ─────────────────────────── -->
  <div x-show="showJacModal" class="fixed inset-0 bg-black/70 backdrop-blur-md flex items-center justify-center p-4 z-50" x-cloak>
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl max-h-[92vh] overflow-hidden flex flex-col">
        <div class="p-8 border-b border-gray-50 flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black text-[#1e3a5f]" x-text="isEditingJac ? 'Actualizar Entidad' : 'Nueva Entidad / Organización'"></h3>
                <p class="text-xs text-gray-400 font-bold mt-1">Configuración de Jerarquía Geográfica y Pertenencia</p>
            </div>
            <button type="button" @click="showJacModal=false" class="p-3 bg-gray-50 rounded-2xl hover:bg-gray-100 transition"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        
        <form @submit.prevent="saveJac()" class="p-8 space-y-6 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2 space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Nombre del Grupo / Organización *</label>
                    <input type="text" x-model="jacForm.nombre_jac" required class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-[#1e3a5f]" placeholder="Ej: Club Deportivo X / JAC Barrio Popular">
                </div>
                

                
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Departamento *</label>
                    <select x-model="jacForm.depto" required @change="onDeptoChange()" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-[#1e3a5f]">
                        <option value="">Seleccione...</option>
                        <template x-for="(d, index) in geos.deptos" :key="'adm-dep-'+index"><option :value="d" x-text="d"></option></template>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Municipio *</label>
                    <select x-model="jacForm.municipio" required @change="onMpioChange()" :disabled="!jacForm.depto" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-[#1e3a5f] disabled:opacity-50">
                        <option value="">Seleccione...</option>
                        <template x-for="(m, index) in geos.mpios" :key="'adm-mun-'+index"><option :value="m" x-text="m"></option></template>
                    </select>
                </div>
                
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Tipo Territorio *</label>
                    <select x-model="jacForm.tipo_territorio" required @change="onTipoChange()" :disabled="!jacForm.municipio" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-[#1e3a5f] disabled:opacity-50">
                        <option value="">Seleccione...</option>
                        <template x-for="(t, index) in geos.tipos" :key="'adm-tipo-'+index"><option :value="t" x-text="t"></option></template>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Comuna / Corregimiento *</label>
                    <select x-model="jacForm.territorio_valle_nombre" required @change="onSectorChange()" :disabled="!jacForm.tipo_territorio" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-[#1e3a5f] disabled:opacity-50">
                        <option value="">Seleccione...</option>
                        <template x-for="(s, index) in geos.sectores" :key="'adm-sec-'+index"><option :value="s" x-text="s"></option></template>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Barrio / Vereda *</label>
                    <select x-model="jacForm.sector" required :disabled="!jacForm.territorio_valle_nombre" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-[#1e3a5f] disabled:opacity-50">
                        <option value="">Seleccione...</option>
                        <template x-for="(b, index) in geos.barrios" :key="'adm-bar-'+index"><option :value="b" x-text="b"></option></template>
                    </select>
                </div>

                <div class="md:col-span-2 space-y-1 mt-2 p-4 bg-blue-50/50 rounded-2xl border border-blue-100/50">
                    <label class="text-[10px] font-black text-[#1e3a5f] uppercase tracking-widest pl-1">Clasificación Específica *</label>
                    <select x-model="jacForm.tipo_organizacion" required class="w-full px-5 py-4 bg-white border-none shadow-sm rounded-xl text-sm font-bold focus:ring-2 focus:ring-[#1e3a5f] text-[#1e3a5f]">
                        <option value="JAC">✅ Junta de Acción Comunal</option>
                        <option value="Deporte">⚽ Grupo Deportivo</option>
                        <option value="Cultura">🎭 Gestor Cultural</option>
                        <option value="Ambiente">🌱 Gestor Ambiental</option>
                        <option value="Social">🤝 Gestor Social</option>
                        <option value="Otro">📌 Otro</option>
                    </select>
                </div>

                <hr class="md:col-span-2 border-gray-50 my-2">

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Presidente Actual</label>
                    <input type="text" x-model="jacForm.presidente" class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-2xl text-sm font-bold" placeholder="Nombre completo">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Teléfono Contacto</label>
                    <input type="text" x-model="jacForm.telefono" class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-2xl text-sm font-bold" placeholder="Ej: 300 000 0000">
                </div>
                
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Dirección / Dirección de Reunión</label>
                    <input type="text" x-model="jacForm.direccion" class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-2xl text-sm font-bold" placeholder="Ej: Calle 10 # 5-20">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Correo Electrónico</label>
                    <input type="email" x-model="jacForm.email" class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-2xl text-sm font-bold" placeholder="ejemplo@entidad.com">
                </div>
                
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Metas Votos</label>
                    <input type="number" x-model.number="jacForm.votos_comprometidos" class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-2xl text-sm font-bold">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Total Afiliados</label>
                    <input type="number" x-model.number="jacForm.afiliados_count" class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-2xl text-sm font-bold">
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Observaciones / Notas Adicionales</label>
                    <textarea x-model="jacForm.observaciones" class="w-full px-5 py-3.5 bg-gray-50 border-none rounded-2xl text-sm font-bold min-h-[80px]" placeholder="Información relevante sobre la entidad..."></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-4">
                <button type="button" @click="showJacModal=false" class="px-6 py-3 text-sm font-black text-gray-400 uppercase">Cerrar</button>
                <button type="submit" class="px-8 py-3 bg-[#1e3a5f] text-white rounded-2xl text-sm font-black shadow-xl hover:bg-[#d4af37] transition">GUARDAR</button>
            </div>
        </form>
    </div>
  </div>

  <!-- ── MODAL PLANCHA LEGAL 1.7.0 (Jerárquico) ───────────────────────── -->
  <div x-show="showPlanchaModal" class="fixed inset-0 bg-black/80 backdrop-blur-xl flex items-center justify-center p-4 z-[60]" x-cloak>
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-5xl max-h-[95vh] overflow-hidden flex flex-col">
        <div class="p-8 bg-[#1e3a5f] text-white flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black italic tracking-tight" x-text="isEditingPlancha ? 'Editar Estructura Entidad' : 'Nueva Estructura Entidad'"></h3>
                <p class="text-[10px] font-black text-white/50 uppercase tracking-[0.2em] mt-1" x-text="'Barrio: ' + (currentJac ? currentJac.nombre_jac : '')"></p>
            </div>
            <button @click="showPlanchaModal=false" class="p-3 bg-white/10 rounded-2xl hover:bg-white/20 transition"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>

        <div class="p-8 flex-1 overflow-y-auto space-y-8 bg-gray-50/50">
            <!-- Nombre de Plancha -->
            <div class="max-w-md">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Título de la Plancha *</label>
                <input type="text" x-model="planchaForm.nombre" class="w-full px-6 py-4 bg-white border-2 border-gray-100 rounded-2xl text-lg font-black text-[#1e3a5f] outline-none focus:border-[#d4af37] transition-all" placeholder="Ej: Plancha 1 - Mi Barrio Crece">
            </div>

            <div class="grid grid-cols-1 gap-12">
                <template x-for="bloq in bloquesLegales" :key="bloq">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-magenta rounded-lg flex items-center justify-center text-white"><i data-lucide="shield" class="w-4 h-4"></i></div>
                            <h4 class="text-sm font-black text-[#1e3a5f] uppercase tracking-wider" x-text="bloq"></h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Template para Miembros del Bloque -->
                            <template x-for="(m, idx) in planchaForm.miembros.filter(item => item.bloque === bloq)" :key="idx">
                                <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-3 relative group">
                                    <button @click="removeMiembro(m)" class="absolute top-3 right-3 p-1 text-gray-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100"><i data-lucide="minus-circle" class="w-4 h-4"></i></button>
                                    
                                    <input type="text" x-model="m.nombre" class="w-full bg-transparent border-b border-gray-100 py-1 font-black text-[#1e3a5f] text-sm focus:border-magenta outline-none" placeholder="Nombre Completo">
                                    <div class="flex gap-2">
                                        <input type="text" x-model="m.cedula" class="flex-1 bg-gray-50 border-none rounded-lg px-3 py-1.5 text-[10px] font-bold" placeholder="Cédula">
                                        <input type="text" x-model="m.cargo" class="flex-1 bg-gray-50 border-none rounded-lg px-3 py-1.5 text-[10px] font-bold" placeholder="Cargo">
                                    </div>
                                    <div class="flex gap-2">
                                        <select x-model="m.genero" class="bg-gray-50 border-none rounded-lg px-3 py-1.5 text-[9px] font-black">
                                            <option value="M">Masc</option>
                                            <option value="F">Fem</option>
                                            <option value="O">Otro</option>
                                        </select>
                                        <input type="text" x-model="m.telefono" class="flex-1 bg-gray-50 border-none rounded-lg px-3 py-1.5 text-[10px] font-bold" placeholder="Teléfono">
                                    </div>
                                </div>
                            </template>
                            
                            <!-- Botón Añadir al Bloque -->
                            <button @click="addMiembro(bloq)" class="p-5 border-2 border-dashed border-gray-200 rounded-3xl flex flex-col items-center justify-center gap-2 hover:border-magenta hover:bg-magenta/5 transition group">
                                <i data-lucide="plus" class="w-6 h-6 text-gray-300 group-hover:text-magenta"></i>
                                <span class="text-[9px] font-black text-gray-400 group-hover:text-magenta uppercase">Añadir a <span x-text="bloq"></span></span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="p-8 border-t border-gray-100 flex items-center justify-between bg-white">
            <div class="flex items-center gap-6">
                 <div><p class="text-lg font-black text-[#1e3a5f]" x-text="planchaForm.miembros.length"></p><p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Postulados</p></div>
                 <div class="w-px h-10 bg-gray-100"></div>
                 <div><p class="text-lg font-black text-magenta" x-text="Math.round((planchaForm.miembros.filter(m=>m.genero==='F').length / (planchaForm.miembros.length||1)) * 100) + '%'"></p><p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Cuota Género Fem</p></div>
            </div>
            <div class="flex gap-4">
                <button type="button" @click="showPlanchaModal=false" class="px-8 py-3 bg-gray-100 rounded-2xl text-xs font-black text-gray-600">CANCELAR</button>
                <button @click="savePlancha()" class="px-12 py-4 bg-[#1e3a5f] text-white rounded-2xl text-sm font-black shadow-2xl hover:bg-magenta transition">GUARDAR & CERRAR</button>
            </div>
        </div>
    </div>
  </div>

</div>
<script>
function jacAdmin() {
  return {
    campanaId: <?= intval($campanaId ?? 0) ?>,
    rows: [], municipios: [], loading: false, saving: false,
    expanded: null, planchasCache: {}, loadingPlanchas: null,
    filters: { search:'', municipio:'' },
    showJacModal: false, isEditingJac: false, jacForm: {},
    showPlanchaModal: false, isEditingPlancha: false, planchaForm: { miembros:[] },
    currentJac: null,
    
    // Config Legales
    bloquesLegales: ['Directivo','Vigilancia','Conciliacion','Delegados','Ejecucion'],
    geos: { deptos:[], mpios:[], tipos:[], sectores:[], barrios:[] },

    init() {
      if (this.campanaId) { this.loadData(); this.loadMunicipios(); }
      this.$nextTick(() => lucide.createIcons());
    },

    async loadData() {
      this.loading = true;
      const params = new URLSearchParams({ action:'list', campana_id:this.campanaId, ...this.filters });
      const r = await fetch(`/aratio/api/jac.php?${params}`);
      const d = await r.json();
      if(d.success) this.rows = d.data;
      this.loading = false;
      this.$nextTick(() => lucide.createIcons());
    },

    async loadMunicipios() {
      const r = await fetch(`/aratio/api/jac.php?action=municipios&campana_id=${this.campanaId}`);
      const d = await r.json(); if(d.success) this.municipios = d.data;
    },

    toggleExpand(id) {
      if(this.expanded === id) this.expanded = null;
      else { 
        this.expanded = id; if(!this.planchasCache[id]) this.loadPlanchas(id); 
      }
      this.$nextTick(() => lucide.createIcons());
    },

    async loadPlanchas(jacId) {
      this.loadingPlanchas = jacId;
      const r = await fetch(`/aratio/api/jac.php?action=planchas&jac_id=${jacId}`);
      const d = await r.json(); if(d.success) this.planchasCache[jacId] = d.data;
      this.loadingPlanchas = null;
      this.$nextTick(() => lucide.createIcons());
    },

    // ── GESTIÓN GEOGRÁFICA (CASCADA) ──
    async loadGeoDeptos() {
        const r = await fetch(`/aratio/api/territorios.php?accion=departamentos`);
        const d = await r.json(); if(d.success) this.geos.deptos = d.data;
    },
    async onDeptoChange() {
        this.geos.mpios=[]; this.geos.tipos=[]; this.geos.sectores=[]; this.geos.barrios=[];
        this.jacForm.municipio=''; this.jacForm.tipo_territorio=''; this.jacForm.territorio_valle_nombre=''; this.jacForm.sector='';
        const r = await fetch(`/aratio/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.jacForm.depto)}`);
        const d = await r.json(); if(d.success) this.geos.mpios = [...new Set(d.data.map(m => (typeof m === 'object' ? m.municipio : m)))];
    },
    async onMpioChange() {
        this.geos.tipos=[]; this.geos.sectores=[]; this.geos.barrios=[];
        this.jacForm.tipo_territorio=''; this.jacForm.territorio_valle_nombre=''; this.jacForm.sector='';
        const r = await fetch(`/aratio/api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(this.jacForm.depto)}&municipio=${encodeURIComponent(this.jacForm.municipio)}`);
        const d = await r.json(); if(d.success) this.geos.tipos = d.data;
    },
    async onTipoChange() {
        this.geos.sectores=[]; this.geos.barrios=[];
        this.jacForm.territorio_valle_nombre=''; this.jacForm.sector='';
        const r = await fetch(`/aratio/api/territorios.php?accion=territorios&departamento=${encodeURIComponent(this.jacForm.depto)}&municipio=${encodeURIComponent(this.jacForm.municipio)}&tipo_territorio=${encodeURIComponent(this.jacForm.tipo_territorio)}`);
        const d = await r.json(); if(d.success) this.geos.sectores = d.data;
    },
    async onSectorChange() {
        this.geos.barrios=[];
        this.jacForm.sector='';
        const r = await fetch(`/aratio/api/territorios.php?accion=barrios&departamento=${encodeURIComponent(this.jacForm.depto)}&municipio=${encodeURIComponent(this.jacForm.municipio)}&tipo_territorio=${encodeURIComponent(this.jacForm.tipo_territorio)}&territorio=${encodeURIComponent(this.jacForm.territorio_valle_nombre)}`);
        const d = await r.json(); if(d.success) this.geos.barrios = d.data;
    },

    // ── CRUD JAC ──
    openJacModal() {
        this.isEditingJac = false; 
        this.jacForm = { id_campana:this.campanaId, depto:'VALLE DEL CAUCA', tipo_organizacion: 'JAC' }; 
        this.showJacModal = true;
        this.loadGeoDeptos().then(() => this.onDeptoChange());
        this.$nextTick(() => lucide.createIcons());
    },
    async editJac(row) {
        this.isEditingJac = true; 
        
        // Mantener una copia en memoria
        const currentData = {...row};
        if(!currentData.depto) currentData.depto = 'VALLE DEL CAUCA';
        
        this.jacForm = {...currentData}; 
        this.showJacModal = true;
        
        // Cargar listas previas para edición
        await this.loadGeoDeptos();
        const r1 = await fetch(`/aratio/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(currentData.depto)}`);
        const d1 = await r1.json(); if(d1.success) this.geos.mpios = [...new Set(d1.data.map(m => (typeof m === 'object' ? m.municipio : m)))];
        
        const r2 = await fetch(`/aratio/api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(currentData.depto)}&municipio=${encodeURIComponent(currentData.municipio)}`);
        const d2 = await r2.json(); if(d2.success) this.geos.tipos = d2.data;
        
        const r3 = await fetch(`/aratio/api/territorios.php?accion=territorios&departamento=${encodeURIComponent(currentData.depto)}&municipio=${encodeURIComponent(currentData.municipio)}&tipo_territorio=${encodeURIComponent(currentData.tipo_territorio)}`);
        const d3 = await r3.json(); if(d3.success) this.geos.sectores = d3.data;
        
        const r4 = await fetch(`/aratio/api/territorios.php?accion=barrios&departamento=${encodeURIComponent(currentData.depto)}&municipio=${encodeURIComponent(currentData.municipio)}&tipo_territorio=${encodeURIComponent(currentData.tipo_territorio)}&territorio=${encodeURIComponent(currentData.territorio_valle_nombre)}`);
        const d4 = await r4.json(); if(d4.success) this.geos.barrios = d4.data;
        
        // ¡RESTAURAR VALORES DE NUEVO! Alpine.js pudo haber limpiado los x-model
        // mientras los select estaban sin opciones cargadas.
        this.$nextTick(() => {
             this.jacForm = {...currentData};
             this.$nextTick(() => lucide.createIcons());
        });
    },
    async saveJac() {
        const method = this.isEditingJac ? 'PUT' : 'POST';
        const r = await fetch('/aratio/api/jac.php', { method, headers:{'X-Requested-With':'XMLHttpRequest','Content-Type':'application/json'}, body:JSON.stringify(this.jacForm) });
        const d = await r.json();
        if(d.success) { this.showJacModal = false; this.loadData(); }
    },
    async deleteJac(id, name) {
        if(!confirm(`¿Eliminar Organización ${name}?`)) return;
        await fetch(`/aratio/api/jac.php?id=${id}`, { method:'DELETE' }); this.loadData();
    },

    // ── CRUD PLANCHAS ──
    openPlanchaModal(jac) { 
        this.currentJac = jac; 
        if(!this.planchasCache[jac.id]) this.loadPlanchas(jac.id);
        this.expanded = jac.id; 
    },
    openNewPlancha(jac) {
        this.currentJac = jac; this.isEditingPlancha = false;
        this.planchaForm = { jac_id:jac.id, nombre:'', miembros:[] };
        this.showPlanchaModal = true;
        this.$nextTick(() => lucide.createIcons());
    },
    editPlancha(pl, jac) {
        this.currentJac = jac; this.isEditingPlancha = true;
        this.planchaForm = JSON.parse(JSON.stringify(pl));
        this.showPlanchaModal = true;
        this.$nextTick(() => lucide.createIcons());
    },
    addMiembro(bloque) {
        const count = this.planchaForm.miembros.filter(m => m.bloque === bloque).length + 1;
        this.planchaForm.miembros.push({ bloque, orden:count, nombre:'', cedula:'', cargo:'', telefono:'', genero:'O' });
        this.$nextTick(() => lucide.createIcons());
    },
    removeMiembro(m) {
        this.planchaForm.miembros = this.planchaForm.miembros.filter(item => item !== m);
    },
    async savePlancha() {
        const method = this.isEditingPlancha ? 'PUT' : 'POST';
        const r = await fetch('/aratio/api/jac.php?action=plancha', { method, headers:{'X-Requested-With':'XMLHttpRequest','Content-Type':'application/json'}, body:JSON.stringify(this.planchaForm) });
        const d = await r.json();
        if(d.success) { 
            this.showPlanchaModal = false; 
            this.loadPlanchas(this.currentJac.id); 
            this.loadData();
        }
    },
    async activarPlancha(planchaId, jacId) {
        await fetch(`/aratio/api/jac.php?action=activar_plancha&id=${planchaId}`, { method:'PUT' });
        this.loadPlanchas(jacId); this.loadData();
    },
    async deletePlancha(pid, name, jid) {
        if(!confirm(`¿Eliminar plancha ${name}?`)) return;
        await fetch(`/aratio/api/jac.php?action=plancha&id=${pid}`, { method:'DELETE' });
        this.loadPlanchas(jid); this.loadData();
    }
  }
}
</script>
