-- Estructura de la base de datos para SST
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
-- 1. Crear la tabla de reportes
CREATE TABLE IF NOT EXISTS `reportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo_usuario` enum('Interno','Contratista') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_contratista` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_hallazgo` enum('Acto Subestándar','Condición Subestándar') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel_riesgo` enum('Bajo','Medio','Alto') COLLATE utf8mb4_unicode_ci NOT NULL,
  `causa_especifica` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_nivel_riesgo` (`nivel_riesgo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- 2. Insertar datos de ejemplo
INSERT INTO `reportes` (`tipo_usuario`, `nombre`, `empresa_contratista`, `area`, `tipo_hallazgo`, `nivel_riesgo`, `causa_especifica`, `descripcion`) VALUES
('Interno', 'Juan Pérez', NULL, 'Producción', 'Acto Subestándar', 'Alto', 'No uso de EPP', 'Trabajador sin uso de EPP en zona de riesgo'),
('Contratista', 'María González', 'Constructora ABC', 'Mantenimiento', 'Condición Subestándar', 'Medio', 'Cables expuestos', 'Cables eléctricos expuestos en área de trabajo');
COMMIT;
