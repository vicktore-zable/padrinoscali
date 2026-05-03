-- =============================================
-- ARATIO - Sistema de Gestión Electoral
-- Base de Datos MySQL
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =============================================
-- TABLA: usuarios
-- =============================================
CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `rol` ENUM('super-admin','admin-campana','coordinador','colaborador','veedor') NOT NULL DEFAULT 'colaborador',
  `avatar_url` VARCHAR(500) DEFAULT NULL,
  `estado` ENUM('activo','inactivo','suspendido') NOT NULL DEFAULT 'activo',
  `ultimo_acceso` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_rol` (`rol`),
  KEY `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: elecciones
-- =============================================
CREATE TABLE `elecciones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(50) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `tipo` ENUM('presidencial','senado','camara','gobernacion','alcaldia','asamblea','concejo','jal') NOT NULL,
  `ambito` ENUM('nacional','departamental','municipal','local') NOT NULL,
  `fecha_eleccion` DATE NOT NULL,
  `periodo_inicio` DATE NOT NULL,
  `periodo_fin` DATE NOT NULL,
  `estado` ENUM('programada','en-campana','finalizada','cancelada') NOT NULL DEFAULT 'programada',
  `descripcion` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha_eleccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: grupos_politicos
-- =============================================
CREATE TABLE `grupos_politicos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `sigla` VARCHAR(20) NOT NULL,
  `tipo` ENUM('partido','movimiento','coalicion','grupo-significativo') NOT NULL,
  `color` VARCHAR(7) NOT NULL,
  `logo_url` VARCHAR(500) DEFAULT NULL,
  `fecha_fundacion` DATE DEFAULT NULL,
  `representante_legal` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `web` VARCHAR(255) DEFAULT NULL,
  `numero_afiliados` INT(11) DEFAULT NULL,
  `activo` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sigla` (`sigla`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: candidatos
-- =============================================
CREATE TABLE `candidatos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombres` VARCHAR(255) NOT NULL,
  `apellidos` VARCHAR(255) NOT NULL,
  `nombre_completo` VARCHAR(255) NOT NULL,
  `documento` VARCHAR(50) NOT NULL,
  `tipo_documento` ENUM('CC','CE','PA','NIT') NOT NULL DEFAULT 'CC',
  `cargo_aspira` VARCHAR(255) NOT NULL,
  `grupo_politico_id` INT(11) DEFAULT NULL,
  `eleccion_id` INT(11) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `foto_url` VARCHAR(500) DEFAULT NULL,
  `departamento` VARCHAR(100) DEFAULT NULL,
  `municipio` VARCHAR(100) DEFAULT NULL,
  `biografia` TEXT DEFAULT NULL,
  `propuestas` TEXT DEFAULT NULL,
  `estado` ENUM('inscrito','activo','retirado','elegido') NOT NULL DEFAULT 'inscrito',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documento` (`documento`),
  KEY `idx_grupo_politico` (`grupo_politico_id`),
  KEY `idx_eleccion` (`eleccion_id`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `fk_candidato_grupo` FOREIGN KEY (`grupo_politico_id`) REFERENCES `grupos_politicos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_candidato_eleccion` FOREIGN KEY (`eleccion_id`) REFERENCES `elecciones` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: campanas
-- =============================================
CREATE TABLE `campanas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(50) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `slogan` VARCHAR(255) DEFAULT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `estado` ENUM('planificacion','activa','finalizada','suspendida') NOT NULL DEFAULT 'planificacion',
  `candidato_id` INT(11) NOT NULL,
  `eleccion_id` INT(11) NOT NULL,
  `departamento` VARCHAR(100) NOT NULL,
  `municipio` VARCHAR(100) NOT NULL,
  `meta_votos` INT(11) DEFAULT NULL,
  `votos_actuales` INT(11) DEFAULT 0,
  `presupuesto` DECIMAL(15,2) DEFAULT NULL,
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin` DATE NOT NULL,
  `color_primario` VARCHAR(7) DEFAULT '#FF00FF',
  `color_secundario` VARCHAR(7) DEFAULT '#FFD700',
  `logo_url` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_candidato` (`candidato_id`),
  KEY `idx_eleccion` (`eleccion_id`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `fk_campana_candidato` FOREIGN KEY (`candidato_id`) REFERENCES `candidatos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_campana_eleccion` FOREIGN KEY (`eleccion_id`) REFERENCES `elecciones` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: usuarios_campanas (relación many-to-many)
-- =============================================
CREATE TABLE `usuarios_campanas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` INT(11) NOT NULL,
  `campana_id` INT(11) NOT NULL,
  `rol_campana` ENUM('administrador','coordinador','colaborador','veedor') NOT NULL DEFAULT 'colaborador',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario_campana` (`usuario_id`, `campana_id`),
  KEY `idx_campana` (`campana_id`),
  CONSTRAINT `fk_uc_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_uc_campana` FOREIGN KEY (`campana_id`) REFERENCES `campanas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: donaciones
-- =============================================
CREATE TABLE `donaciones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `campana_id` INT(11) NOT NULL,
  `tipo_donante` ENUM('persona-natural','persona-juridica','anonimo') NOT NULL,
  `nombre_donante` VARCHAR(255) NOT NULL,
  `documento_donante` VARCHAR(50) DEFAULT NULL,
  `email_donante` VARCHAR(255) DEFAULT NULL,
  `telefono_donante` VARCHAR(20) DEFAULT NULL,
  `direccion_donante` TEXT DEFAULT NULL,
  `monto` DECIMAL(15,2) NOT NULL,
  `metodo_pago` ENUM('efectivo','transferencia','especie') NOT NULL,
  `referencia_pago` VARCHAR(255) DEFAULT NULL,
  `descripcion_especie` TEXT DEFAULT NULL,
  `estado` ENUM('pendiente','confirmada','rechazada') NOT NULL DEFAULT 'pendiente',
  `fecha_donacion` DATETIME NOT NULL,
  `fecha_confirmacion` DATETIME DEFAULT NULL,
  `recaudador_id` INT(11) DEFAULT NULL,
  `comprobante_url` VARCHAR(500) DEFAULT NULL,
  `notas` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campana` (`campana_id`),
  KEY `idx_recaudador` (`recaudador_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha_donacion`),
  CONSTRAINT `fk_donacion_campana` FOREIGN KEY (`campana_id`) REFERENCES `campanas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_donacion_recaudador` FOREIGN KEY (`recaudador_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: eventos
-- =============================================
CREATE TABLE `eventos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `campana_id` INT(11) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `tipo` ENUM('recorrido','reunion','debate','asamblea','mitin','jornada-firmas','capacitacion','evento-social','otro') NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `fecha_inicio` DATETIME NOT NULL,
  `fecha_fin` DATETIME DEFAULT NULL,
  `ubicacion` VARCHAR(500) DEFAULT NULL,
  `direccion` TEXT DEFAULT NULL,
  `latitud` DECIMAL(10,8) DEFAULT NULL,
  `longitud` DECIMAL(11,8) DEFAULT NULL,
  `departamento` VARCHAR(100) DEFAULT NULL,
  `municipio` VARCHAR(100) DEFAULT NULL,
  `asistentes_esperados` INT(11) DEFAULT NULL,
  `asistentes_confirmados` INT(11) DEFAULT 0,
  `responsable_id` INT(11) DEFAULT NULL,
  `codigo_qr` VARCHAR(255) DEFAULT NULL,
  `estado` ENUM('programado','en-curso','finalizado','cancelado') NOT NULL DEFAULT 'programado',
  `presupuesto` DECIMAL(15,2) DEFAULT NULL,
  `notas` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campana` (`campana_id`),
  KEY `idx_responsable` (`responsable_id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha_inicio`),
  CONSTRAINT `fk_evento_campana` FOREIGN KEY (`campana_id`) REFERENCES `campanas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_evento_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: asistencia_eventos
-- =============================================
CREATE TABLE `asistencia_eventos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `evento_id` INT(11) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `documento` VARCHAR(50) DEFAULT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `fecha_registro` DATETIME NOT NULL,
  `metodo_registro` ENUM('qr','manual','web') NOT NULL DEFAULT 'manual',
  `asistio` BOOLEAN NOT NULL DEFAULT TRUE,
  `notas` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_evento` (`evento_id`),
  CONSTRAINT `fk_asistencia_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: acciones_comunitarias
-- =============================================
CREATE TABLE `acciones_comunitarias` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `campana_id` INT(11) NOT NULL,
  `usuario_id` INT(11) NOT NULL,
  `tipo` ENUM('puerta-puerta','brigada-salud','jornada-social','recoleccion-firmas','encuesta','entrega-volantes','reunion-comunitaria','otro') NOT NULL,
  `descripcion` TEXT NOT NULL,
  `fecha_accion` DATETIME NOT NULL,
  `departamento` VARCHAR(100) NOT NULL,
  `municipio` VARCHAR(100) NOT NULL,
  `tipo_territorio` VARCHAR(100) DEFAULT NULL,
  `territorio` VARCHAR(100) DEFAULT NULL,
  `barrio` VARCHAR(100) DEFAULT NULL,
  `latitud` DECIMAL(10,8) DEFAULT NULL,
  `longitud` DECIMAL(11,8) DEFAULT NULL,
  `personas_contactadas` INT(11) DEFAULT 0,
  `compromisos_obtenidos` INT(11) DEFAULT 0,
  `observaciones` TEXT DEFAULT NULL,
  `evidencia_url` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campana` (`campana_id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_fecha` (`fecha_accion`),
  KEY `idx_ubicacion` (`departamento`, `municipio`),
  CONSTRAINT `fk_accion_campana` FOREIGN KEY (`campana_id`) REFERENCES `campanas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_accion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: compromisos
-- =============================================
CREATE TABLE `compromisos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `campana_id` INT(11) NOT NULL,
  `usuario_id` INT(11) NOT NULL,
  `tipo` ENUM('infraestructura','servicios-publicos','salud','educacion','seguridad','deporte','cultura','medio-ambiente','empleo','vivienda','movilidad','otro') NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `lider_nombre` VARCHAR(255) NOT NULL,
  `lider_telefono` VARCHAR(20) NOT NULL,
  `lider_email` VARCHAR(255) DEFAULT NULL,
  `lider_cargo` VARCHAR(255) DEFAULT NULL,
  `fecha_compromiso` DATE NOT NULL,
  `fecha_cumplimiento_estimada` DATE DEFAULT NULL,
  `fecha_cumplimiento_real` DATE DEFAULT NULL,
  `departamento` VARCHAR(100) NOT NULL,
  `municipio` VARCHAR(100) NOT NULL,
  `tipo_territorio` VARCHAR(100) DEFAULT NULL,
  `territorio` VARCHAR(100) DEFAULT NULL,
  `barrio` VARCHAR(100) DEFAULT NULL,
  `latitud` DECIMAL(10,8) DEFAULT NULL,
  `longitud` DECIMAL(11,8) DEFAULT NULL,
  `metodologia` TEXT DEFAULT NULL,
  `presupuesto_estimado` DECIMAL(15,2) DEFAULT NULL,
  `beneficiarios_estimados` INT(11) DEFAULT NULL,
  `prioridad` ENUM('alta','media','baja') NOT NULL DEFAULT 'media',
  `estado` ENUM('pendiente','en-gestion','cumplido','incumplido') NOT NULL DEFAULT 'pendiente',
  `avance_porcentaje` DECIMAL(5,2) DEFAULT 0.00,
  `observaciones` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campana` (`campana_id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_estado` (`estado`),
  KEY `idx_prioridad` (`prioridad`),
  KEY `idx_fecha` (`fecha_compromiso`),
  CONSTRAINT `fk_compromiso_campana` FOREIGN KEY (`campana_id`) REFERENCES `campanas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_compromiso_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: sesiones
-- =============================================
CREATE TABLE `sesiones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` INT(11) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_sesion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DATOS INICIALES
-- =============================================

-- Insertar grupos políticos
INSERT INTO `grupos_politicos` (`nombre`, `sigla`, `tipo`, `color`, `fecha_fundacion`, `representante_legal`, `activo`) VALUES
('Partido Liberal Colombiano', 'PLC', 'partido', '#FF0000', '1848-07-16', 'César Gaviria Trujillo', TRUE),
('Partido Conservador Colombiano', 'PCC', 'partido', '#0000FF', '1849-10-04', 'Omar Yepes Alzate', TRUE),
('Pacto Histórico', 'PH', 'coalicion', '#00A859', '2021-11-15', 'Gustavo Petro', TRUE),
('Centro Democrático', 'CD', 'partido', '#005BAA', '2013-07-09', 'Álvaro Uribe Vélez', TRUE),
('Cambio Radical', 'CR', 'partido', '#FF6B00', '1998-02-27', 'Germán Vargas Lleras', TRUE),
('Alianza Verde', 'AV', 'partido', '#00B140', '2009-10-07', 'Claudia López', TRUE);

-- Insertar elección
INSERT INTO `elecciones` (`codigo`, `nombre`, `tipo`, `ambito`, `fecha_eleccion`, `periodo_inicio`, `periodo_fin`, `estado`) VALUES
('REG-2027', 'Elecciones Regionales 2027 - Período 2028-2031', 'concejo', 'municipal', '2027-10-29', '2028-01-01', '2031-12-31', 'programada');

-- Insertar usuario administrador (password: Admin123!)
INSERT INTO `usuarios` (`nombre`, `email`, `password`, `rol`, `estado`) VALUES
('Administrador Sistema', 'admin@aratio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super-admin', 'activo');

COMMIT;
