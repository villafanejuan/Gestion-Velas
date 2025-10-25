-- Script para agregar gestión de precios para difusores

-- 1. Agregar nuevos materiales para difusores en la tabla precios_materiales
INSERT INTO `precios_materiales` (`nombre`, `precio_kg`, `tipo`) VALUES 
('Alcohol', 1500.00, 'material'),
('Esencia_Difusor', 9000.00, 'esencia')
ON DUPLICATE KEY UPDATE 
precio_kg = IF(precio_kg = 0, VALUES(precio_kg), precio_kg);

-- 2. Crear tabla para tipos de frascos
CREATE TABLE IF NOT EXISTS `tipos_frascos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `precio_unidad` decimal(10,2) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `actualizado` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Insertar algunos tipos de frascos de ejemplo
INSERT INTO `tipos_frascos` (`nombre`, `precio_unidad`) VALUES 
('Frasco 50ml', 250.00),
('Frasco 100ml', 350.00),
('Frasco 200ml', 450.00),
('Frasco Premium 100ml', 500.00)
ON DUPLICATE KEY UPDATE 
precio_unidad = IF(precio_unidad = 0, VALUES(precio_unidad), precio_unidad);

-- 4. Agregar campo para referenciar el tipo de frasco en productos
ALTER TABLE `productos` 
ADD COLUMN `tipo_frasco_id` int(11) DEFAULT NULL AFTER `frascos_precio_unidad`,
ADD INDEX `idx_tipo_frasco` (`tipo_frasco_id`);

-- 5. Migrar datos existentes: buscar el frasco más cercano al precio actual
UPDATE `productos` p 
LEFT JOIN `tipos_frascos` tf ON ABS(tf.precio_unidad - p.frascos_precio_unidad) = (
    SELECT MIN(ABS(tf2.precio_unidad - p.frascos_precio_unidad)) 
    FROM `tipos_frascos` tf2 
    WHERE tf2.activo = 1
)
SET p.tipo_frasco_id = tf.id 
WHERE p.tipo = 'difusor' AND p.frascos_precio_unidad > 0 AND tf.id IS NOT NULL;
