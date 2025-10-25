<?php
// Archivo para inicializar la base de datos con las nuevas tablas y campos
$conn = new mysqli("localhost", "root", "", "vela");
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

echo "<h2>Inicializando sistema de gestión de precios...</h2>";

// 1. Crear tabla precios_materiales si no existe
$sql_tabla = "CREATE TABLE IF NOT EXISTS `precios_materiales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `precio_kg` decimal(10,2) NOT NULL,
  `tipo` enum('material','esencia') NOT NULL,
  `actualizado` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre_tipo` (`nombre`, `tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql_tabla)) {
    echo "✓ Tabla precios_materiales creada o ya existía<br>";
} else {
    echo "✗ Error creando tabla: " . $conn->error . "<br>";
}

// 2. Insertar precios iniciales
$sql_precios = "INSERT INTO `precios_materiales` (`nombre`, `precio_kg`, `tipo`) VALUES 
('APF', 2500.00, 'material'),
('VPF', 3000.00, 'material'),
('Esencias', 8000.00, 'esencia')
ON DUPLICATE KEY UPDATE 
precio_kg = IF(precio_kg = 0, VALUES(precio_kg), precio_kg)";

if ($conn->query($sql_precios)) {
    echo "✓ Precios iniciales insertados<br>";
} else {
    echo "✗ Error insertando precios: " . $conn->error . "<br>";
}

// 3. Verificar si las columnas nuevas existen, si no, crearlas
$result = $conn->query("SHOW COLUMNS FROM productos LIKE 'material_apf_g'");
if ($result->num_rows == 0) {
    $sql_columnas = "ALTER TABLE `productos` 
    ADD COLUMN `material_apf_g` decimal(10,2) DEFAULT 0 AFTER `material_g`,
    ADD COLUMN `material_vpf_g` decimal(10,2) DEFAULT 0 AFTER `material_apf_g`";
    
    if ($conn->query($sql_columnas)) {
        echo "✓ Columnas material_apf_g y material_vpf_g agregadas<br>";
    } else {
        echo "✗ Error agregando columnas: " . $conn->error . "<br>";
    }
} else {
    echo "✓ Columnas de materiales ya existían<br>";
}

// 4. Migrar datos existentes si hay productos con material_g > 0 pero material_apf_g = 0
$sql_migrar = "UPDATE `productos` SET `material_apf_g` = `material_g` 
WHERE `material_g` > 0 AND `material_apf_g` = 0 AND `tipo` = 'vela'";
$conn->query($sql_migrar);
echo "✓ Datos migrados a nuevo formato<br>";

echo "<br><h3>✅ Sistema inicializado correctamente!</h3>";
echo "<p><a href='index.php'>Volver al inicio</a> | <a href='precios_materiales.php'>Gestionar precios</a></p>";

$conn->close();
?>
