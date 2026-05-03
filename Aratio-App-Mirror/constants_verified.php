<?php
/**
 * Constantes del Sistema
 * Enumeraciones y valores predefinidos
 *
 * @package Config
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

// =====================================================
// TIPOS DE DOCUMENTO
// =====================================================

define('TIPOS_DOCUMENTO', [
    'CC' => 'Cédula de Ciudadanía',
    'TI' => 'Tarjeta de Identidad',
    'CE' => 'Cédula de Extranjería',
    'PA' => 'Pasaporte',
    'RC' => 'Registro Civil',
    'NIT' => 'NIT'
]);

// =====================================================
// PERFILES DE COLABORADORES
// =====================================================

define('PERFILES', [
    'Lider Comunitario / Social' => 'Líder Comunitario / Social',
    'Lider Ambiental' => 'Líder Ambiental',
    'Lider Gremial / Empresarial' => 'Líder Gremial / Empresarial',
    'Lider Juvenil / Deportivo' => 'Líder Juvenil / Deportivo',
    'Lider Poblacional / Diferencial' => 'Líder Poblacional / Diferencial',
    'Lider Religioso' => 'Líder Religioso',
    'Influencer / Medios' => 'Influencer / Medios',
    'Vinculo Personal' => 'Vínculo Personal'
]);

// =====================================================
// NIVELES DE PARTICIPACIÓN
// =====================================================

define('NIVELES_PARTICIPACION', [
    'Simpatizante' => 'Simpatizante',
    'Aportante' => 'Aportante',
    'Activista de Opinión' => 'Activista de Opinión',
    'Movilizador' => 'Movilizador',
    'Contradictor' => 'Contradictor',
    'Contratista' => 'Contratista'
]);

// =====================================================
// ÁREAS DE INTERÉS
// =====================================================

define('AREAS_INTERES', [
    'Educación' => 'Educación',
    'Salud' => 'Salud',
    'Medio Ambiente' => 'Medio Ambiente',
    'Economía' => 'Economía',
    'Seguridad' => 'Seguridad',
    'Cultura' => 'Cultura',
    'Deportes' => 'Deportes',
    'Tecnología' => 'Tecnología',
    'Derechos Humanos' => 'Derechos Humanos',
    'Juventud' => 'Juventud',
    'Comercio' => 'Comercio',
    'Social' => 'Social',
    'Proyectos' => 'Proyectos'
]);

// =====================================================
// GÉNEROS
// =====================================================

define('GENEROS', [
    'Masculino' => 'Masculino',
    'Femenino' => 'Femenino',
    'Otro' => 'Otro',
    'Prefiero no decir' => 'Prefiero no decir'
]);

// =====================================================
// GRUPOS ETÁREOS
// =====================================================

define('GRUPOS_ETAREOS', [
    'Infancia (0-12 años)' => 'Infancia (0-12 años)',
    'Adolescencia (13-17 años)' => 'Adolescencia (13-17 años)',
    'Juventud (18-28 años)' => 'Juventud (18-28 años)',
    'Adultez Joven (29-40 años)' => 'Adultez Joven (29-40 años)',
    'Adultez Media (41-60 años)' => 'Adultez Media (41-60 años)',
    'Adultez Mayor (61+ años)' => 'Adultez Mayor (61+ años)'
]);

// =====================================================
// ESTADOS DE COLABORADOR
// =====================================================

define('ESTADOS_COLABORADOR', [
    'Nuevo' => 'Nuevo',
    'Desvinculado' => 'Desvinculado',
    'Creció' => 'Creció',
    'Decrece' => 'Decrece',
    'Igual' => 'Igual'
]);

// =====================================================
// TIPOS DE USUARIO
// =====================================================

define('TIPOS_USUARIO', [
    'admin' => 'Administrador',
    'lider' => 'Líder',
    'consulta' => 'Consulta'
]);

// =====================================================
// PERMISOS POR TIPO DE USUARIO
// =====================================================

define('PERMISOS', [
    'admin' => [
        'dashboard' => ['view', 'export'],
        'colaboradores' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
        'red' => ['view', 'analyze', 'export'],
        'lideres' => ['view', 'assign', 'change', 'history'],
        'reportes' => ['view', 'create', 'export'],
        'curriculum' => ['view', 'create', 'edit', 'delete'],
        'usuarios' => ['view', 'create', 'edit', 'delete'],
        'configuracion' => ['view', 'edit'],
        'logs' => ['view', 'export']
    ],
    'lider' => [
        'dashboard' => ['view'],
        'colaboradores' => ['view', 'export'],
        'red' => ['view'],
        'reportes' => ['view'],
        'curriculum' => ['view'],
        'configuracion' => ['view']
    ],
    'consulta' => [
        'colaboradores' => ['view'],
        'reportes' => ['view']
    ]
]);

// =====================================================
// TIPOS DE TERRITORIO
// =====================================================

define('TIPOS_TERRITORIO', [
    'Urbano' => 'Urbano',
    'Rural' => 'Rural',
    'Urbano-Rural' => 'Urbano-Rural'
]);

// =====================================================
// COLORES POR ESTADO (para visualización)
// =====================================================

define('COLORES_ESTADO', [
    'Nuevo' => '#3b82f6',      // Azul
    'Desvinculado' => '#6b7280', // Gris
    'Creció' => '#10b981',     // Verde
    'Decrece' => '#ef4444',    // Rojo
    'Igual' => '#f59e0b'       // Amarillo/Ámbar
]);

// =====================================================
// COLORES POR PERFIL (para visualización de grafo)
// =====================================================

define('COLORES_PERFIL', [
    'Lider Comunitario / Social' => '#3b82f6',     // Azul
    'Lider Ambiental' => '#10b981',                // Verde
    'Lider Gremial / Empresarial' => '#f59e0b',    // Ámbar
    'Lider Juvenil / Deportivo' => '#14b8a6',      // Teal
    'Lider Poblacional / Diferencial' => '#6366f1', // Índigo
    'Lider Religioso' => '#facc15',                // Dorado
    'Influencer / Medios' => '#ef4444',            // Rojo
    'Vinculo Personal' => '#ec4899'                // Rosa
]);

// =====================================================
// FORMAS POR NIVEL DE PARTICIPACIÓN (para grafo)
// =====================================================

define('FORMAS_NIVEL', [
    'Simpatizante' => 'dot',
    'Aportante' => 'diamond',
    'Activista de Opinión' => 'square',
    'Movilizador' => 'star',
    'Contradictor' => 'triangle'
]);

// =====================================================
// DEPARTAMENTOS DE COLOMBIA
// =====================================================

define('DEPARTAMENTOS_COLOMBIA', [
    'Amazonas', 'Antioquia', 'Arauca', 'Atlántico', 'Bolívar', 'Boyacá',
    'Caldas', 'Caquetá', 'Casanare', 'Cauca', 'Cesar', 'Chocó', 'Córdoba',
    'Cundinamarca', 'Guainía', 'Guaviare', 'Huila', 'La Guajira', 'Magdalena',
    'Meta', 'Nariño', 'Norte de Santander', 'Putumayo', 'Quindío', 'Risaralda',
    'San Andrés y Providencia', 'Santander', 'Sucre', 'Tolima', 'Valle del Cauca',
    'Vaupés', 'Vichada', 'Bogotá D.C.'
]);

// =====================================================
// FORMATOS DE EXPORTACIÓN
// =====================================================

define('FORMATOS_EXPORTACION', [
    'excel' => 'Microsoft Excel (.xlsx)',
    'csv' => 'CSV (.csv)',
    'pdf' => 'PDF (.pdf)',
    'json' => 'JSON (.json)'
]);

// =====================================================
// TIPOS DE ACCIONES (para logs)
// =====================================================

define('TIPOS_ACCION', [
    'CREATE' => 'Crear',
    'READ' => 'Leer',
    'UPDATE' => 'Actualizar',
    'DELETE' => 'Eliminar',
    'LOGIN' => 'Iniciar Sesión',
    'LOGOUT' => 'Cerrar Sesión',
    'IMPORT' => 'Importar',
    'EXPORT' => 'Exportar',
    'CHANGE_LEADER' => 'Cambiar Líder',
    'CHANGE_PASSWORD' => 'Cambiar Contraseña',
    'ENABLE_2FA' => 'Activar 2FA',
    'DISABLE_2FA' => 'Desactivar 2FA'
]);

// =====================================================
// NIVELES DE LOG
// =====================================================

define('LOG_LEVELS', [
    'DEBUG' => 'debug',
    'INFO' => 'info',
    'WARNING' => 'warning',
    'ERROR' => 'error',
    'CRITICAL' => 'critical'
]);

// =====================================================
// MENSAJES DEL SISTEMA
// =====================================================

define('MENSAJES', [
    'success' => [
        'create' => 'Registro creado exitosamente',
        'update' => 'Registro actualizado exitosamente',
        'delete' => 'Registro eliminado exitosamente',
        'import' => 'Importación completada exitosamente',
        'export' => 'Exportación completada exitosamente',
        'login' => 'Inicio de sesión exitoso',
        'logout' => 'Sesión cerrada correctamente'
    ],
    'error' => [
        'generic' => 'Ha ocurrido un error. Por favor intente nuevamente',
        'not_found' => 'Registro no encontrado',
        'unauthorized' => 'No tiene permisos para realizar esta acción',
        'validation' => 'Por favor corrija los errores en el formulario',
        'database' => 'Error de conexión con la base de datos',
        'file_upload' => 'Error al subir el archivo',
        'file_size' => 'El archivo excede el tamaño máximo permitido',
        'file_type' => 'Tipo de archivo no permitido',
        'login_failed' => 'Usuario o contraseña incorrectos',
        'session_expired' => 'Su sesión ha expirado. Por favor inicie sesión nuevamente',
        'rate_limit' => 'Demasiados intentos. Por favor espere unos minutos',
        'csrf' => 'Token de seguridad inválido. Recargue la página e intente nuevamente'
    ],
    'warning' => [
        'unsaved_changes' => 'Tiene cambios sin guardar',
        'delete_confirm' => '¿Está seguro que desea eliminar este registro?',
        'no_results' => 'No se encontraron resultados'
    ]
]);

// =====================================================
// PAGINACIÓN
// =====================================================

define('PAGINATION', [
    'per_page' => 25,
    'per_page_options' => [10, 25, 50, 100],
    'max_links' => 5
]);

// =====================================================
// EXPRESIONES REGULARES
// =====================================================

define('REGEX_PATTERNS', [
    'email' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
    'phone' => '/^[\d\s\-\+\(\)]{7,20}$/',
    'documento_cc' => '/^[0-9]{6,12}$/',
    'documento_ti' => '/^[0-9]{10,11}$/',
    'password_strong' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
    'alphanumeric' => '/^[a-zA-Z0-9]+$/',
    'alpha' => '/^[a-zA-Z\s]+$/',
    'numeric' => '/^[0-9]+$/'
]);

// =====================================================
// LÍMITES DEL SISTEMA
// =====================================================

define('LIMITES', [
    'max_seguidores_directos' => 1000,
    'max_nivel_jerarquico' => 12,
    'max_areas_interes' => 5,
    'max_experiencias_laborales' => 20,
    'max_formaciones' => 10,
    'max_participaciones_politicas' => 20,
    'max_importacion_registros' => 10000
]);

// =====================================================
// CONFIGURACIÓN DE GRAFOS
// =====================================================

define('GRAFO_CONFIG', [
    'max_nodes_display' => 500,
    'default_layout' => 'hierarchical',
    'physics_enabled' => true,
    'node_size_min' => 10,
    'node_size_max' => 50,
    'edge_width_min' => 1,
    'edge_width_max' => 5
]);

// =====================================================
// FUNCIONES HELPER PARA CONSTANTES
// =====================================================

/**
 * Obtener label de un enum
 */
function get_enum_label(string $type, string $value): string {
    $enums = [
        'tipo_documento' => TIPOS_DOCUMENTO,
        'perfil' => PERFILES,
        'nivel_participacion' => NIVELES_PARTICIPACION,
        'genero' => GENEROS,
        'estado' => ESTADOS_COLABORADOR,
        'tipo_usuario' => TIPOS_USUARIO,
        'tipo_territorio' => TIPOS_TERRITORIO
    ];

    return $enums[$type][$value] ?? $value;
}

/**
 * Obtener color por estado
 */
function get_color_estado(string $estado): string {
    return COLORES_ESTADO[$estado] ?? '#6b7280';
}

/**
 * Obtener color por perfil
 */
function get_color_perfil(string $perfil): string {
    return COLORES_PERFIL[$perfil] ?? '#3b82f6';
}

/**
 * Verificar permiso
 */
function has_permission(string $tipo_usuario, string $modulo, string $accion): bool {
    return isset(PERMISOS[$tipo_usuario][$modulo]) &&
           in_array($accion, PERMISOS[$tipo_usuario][$modulo]);
}

/**
 * Validar con regex
 */
function validate_pattern(string $pattern_name, string $value): bool {
    $pattern = REGEX_PATTERNS[$pattern_name] ?? null;
    if (!$pattern) return false;
    return preg_match($pattern, $value) === 1;
}

// =====================================================
// FIN DE CONSTANTES
// =====================================================
