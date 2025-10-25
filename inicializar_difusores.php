<?php
// Archivo para inicializar el sistema de difusores
$conn = new mysqli("localhost", "root", "", "vela");
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

echo "<h2>Inicializando sistema de gestión de precios para difusores...</h2>";

// 1. Insertar nuevos materiales para difusores
$sql_materiales = "INSERT INTO `precios_materiales` (`nombre`, `precio_kg`, `tipo`) VALUES 
('Alcohol', 1500.00, 'material'),
('Esencia_Difusor', 9000.00, 'esencia')
ON DUPLICATE KEY UPDATE 
precio_kg = IF(precio_kg = 0, VALUES(precio_kg), precio_kg)";

if ($conn->query($sql_materiales)) {
    echo "✓ Materiales para difusores agregados<br>";
} else {
    echo "✗ Error agregando materiales: " . $conn->error . "<br>";
}

// 2. Crear tabla tipos_frascos si no existe
$sql_frascos = "CREATE TABLE IF NOT EXISTS `tipos_frascos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `precio_unidad` decimal(10,2) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `actualizado` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql_frascos)) {
    echo "✓ Tabla tipos_frascos creada o ya existía<br>";
} else {
    echo "✗ Error creando tabla frascos: " . $conn->error . "<br>";
}

// 3. Insertar tipos de frascos de ejemplo
$sql_frascos_data = "INSERT INTO `tipos_frascos` (`nombre`, `precio_unidad`) VALUES 
('Frasco 50ml', 250.00),
('Frasco 100ml', 350.00),
('Frasco 200ml', 450.00),
('Frasco Premium 100ml', 500.00)
ON DUPLICATE KEY UPDATE 
precio_unidad = IF(precio_unidad = 0, VALUES(precio_unidad), precio_unidad)";

if ($conn->query($sql_frascos_data)) {
    echo "✓ Tipos de frascos de ejemplo insertados<br>";
} else {
    echo "✗ Error insertando frascos: " . $conn->error . "<br>";
}

// 4. Verificar si la columna tipo_frasco_id existe, si no, crearla
$result = $conn->query("SHOW COLUMNS FROM productos LIKE 'tipo_frasco_id'");
if ($result->num_rows == 0) {
    $sql_columna = "ALTER TABLE `productos` 
    ADD COLUMN `tipo_frasco_id` int(11) DEFAULT NULL AFTER `frascos_precio_unidad`,
    ADD INDEX `idx_tipo_frasco` (`tipo_frasco_id`)";
    
    if ($conn->query($sql_columna)) {
        echo "✓ Columna tipo_frasco_id agregada<br>";
    } else {
        echo "✗ Error agregando columna: " . $conn->error . "<br>";
    }
} else {
    echo "✓ Columna tipo_frasco_id ya existía<br>";
}

// 5. Migrar datos existentes
$sql_migrar = "UPDATE `productos` p 
LEFT JOIN `tipos_frascos` tf ON ABS(tf.precio_unidad - p.frascos_precio_unidad) = (
    SELECT MIN(ABS(tf2.precio_unidad - p.frascos_precio_unidad)) 
    FROM `tipos_frascos` tf2 
    WHERE tf2.activo = 1
)
SET p.tipo_frasco_id = tf.id 
WHERE p.tipo = 'difusor' AND p.frascos_precio_unidad > 0 AND tf.id IS NOT NULL AND p.tipo_frasco_id IS NULL";

if ($conn->query($sql_migrar)) {
    echo "✓ Datos de difusores migrados al nuevo sistema<br>";
    $affected = $conn->affected_rows;
    echo "→ $affected difusor(es) actualizados<br>";
} else {
    echo "✗ Error migrando datos: " . $conn->error . "<br>";
}

echo "<br><h3>✅ Sistema de difusores inicializado correctamente!</h3>";
echo "<p><a href='index.php'>Volver al inicio</a> | <a href='precios_difusores.php'>Gestionar precios de difusores</a></p>";

$conn->close();
?>
