SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `territorios`;
CREATE TABLE `territorios` (
  `id` bigint NOT NULL,
  `departamento` varchar(255) DEFAULT NULL,
  `municipio` varchar(255) DEFAULT NULL,
  `Tipo_territorio` varchar(255) DEFAULT NULL,
  `cod_mpio` varchar(255) DEFAULT NULL,
  `Código` varchar(255) DEFAULT NULL,
  `Territorio` varchar(255) DEFAULT NULL,
  `barrio` varchar(255) DEFAULT NULL,
  `geometria` GEOMETRY NOT NULL,
  PRIMARY KEY (`id`),
  SPATIAL INDEX (`geometria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;