-- Script para actualizar la base de datos con la nueva estructura

-- 1. Crear tabla para gestionar precios de materiales y esencias
CREATE TABLE IF NOT EXISTS `precios_materiales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `precio_kg` decimal(10,2) NOT NULL,
  `tipo` enum('material','esencia') NOT NULL,
  `actualizado` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre_tipo` (`nombre`, `tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Insertar precios iniciales (puedes ajustar estos valores)
INSERT INTO `precios_materiales` (`nombre`, `precio_kg`, `tipo`) VALUES 
('APF', 2500.00, 'material'),
('VPF', 3000.00, 'material'),
('Esencias', 8000.00, 'esencia')
ON DUPLICATE KEY UPDATE 
precio_kg = VALUES(precio_kg);

-- 3. Modificar tabla productos para incluir campos de dos materiales
ALTER TABLE `productos` 
ADD COLUMN `material_apf_g` decimal(10,2) DEFAULT 0 AFTER `material_g`,
ADD COLUMN `material_vpf_g` decimal(10,2) DEFAULT 0 AFTER `material_apf_g`;

-- 4. Actualizar registros existentes: mover el valor de material_g a material_apf_g
UPDATE `productos` SET `material_apf_g` = `material_g` WHERE `material_g` > 0;

-- 5. Opcional: Puedes eliminar las columnas antiguas después de verificar que todo funciona
-- ALTER TABLE `productos` DROP COLUMN `material_precio_kg`;
-- ALTER TABLE `productos` DROP COLUMN `escencias_precio_kg`;
-- Pero las mantendremos por compatibilidad inicial
