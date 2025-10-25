<?php
$conn = new mysqli("localhost", "root", "", "vela");
if ($conn->connect_error) die("Error de conexión");

// Normaliza cadenas numéricas: acepta '1.234,56' o '1234.56' y devuelve float 1234.56
function parse_number($s) {
	$s = trim((string)$s);
	if ($s === '') return 0.0;
	// Si contiene ',' entonces probablemente usa coma decimal. Si también contiene '.', asumimos '.' es separador de miles.
	if (strpos($s, ',') !== false) {
		$s = str_replace('.', '', $s); // quitar separadores de miles
		$s = str_replace(',', '.', $s); // coma -> punto decimal
	}
	// ahora floatval puede parsear correctamente
	return floatval($s);
}

// Obtener precios actuales de materiales y esencias
$precios = [];
$result = $conn->query("SELECT nombre, precio_kg FROM precios_materiales");
while ($row = $result->fetch_assoc()) {
    $precios[$row['nombre']] = $row['precio_kg'];
}

$tipo = $_POST['tipo'];
$nombre = $_POST['nombre'];
$ganancia_porcentaje = intval($_POST['ganancia_porcentaje']);

// Inicializar todos los campos
$material_g = $material_precio_kg = $esc_g = $escencias_precio_kg = $costo_insumos = 0;
$material_apf_g = $material_vpf_g = 0;
$alcohol_ml = $alcohol_precio_l = $esencia_g = $esencia_precio_kg = $frascos_precio_unidad = 0;
$pack_extras = 0;

if ($tipo === 'vela') {
	// Nuevos campos de materiales
	$material_apf_g = parse_number($_POST['material_apf_g'] ?? 0);
	$material_vpf_g = parse_number($_POST['material_vpf_g'] ?? 0);
	$esc_g = parse_number($_POST['esc_g'] ?? 0);
	$pack_extras = parse_number($_POST['pack_extras'] ?? 0);
	
	// Calcular costos usando precios centralizados
	$costo_apf = ($precios['APF'] / 1000) * $material_apf_g;
	$costo_vpf = ($precios['VPF'] / 1000) * $material_vpf_g;
	$costo_escencias = ($precios['Esencias'] / 1000) * $esc_g;
	
	// Para mantener compatibilidad, guardar los precios usados
	$material_precio_kg = $precios['APF']; // Precio por defecto para compatibilidad
	$escencias_precio_kg = $precios['Esencias'];
	$material_g = $material_apf_g + $material_vpf_g; // Total de material para compatibilidad
	
	$costo_insumos = $costo_apf + $costo_vpf + $costo_escencias + $pack_extras;
	$costo_total = $costo_insumos;
} else {
	// Difusores con nuevo sistema
	$alcohol_ml = parse_number($_POST['alcohol_ml'] ?? 0);
	$esencia_g = parse_number($_POST['esencia_g'] ?? 0);
	$tipo_frasco_id = intval($_POST['tipo_frasco_id'] ?? 0);
	$pack_extras = parse_number($_POST['pack_extras_difusor'] ?? 0);
	
	// Obtener precio del frasco seleccionado
	$frasco_result = $conn->query("SELECT precio_unidad FROM tipos_frascos WHERE id = $tipo_frasco_id");
	$frasco_precio = 0;
	if ($frasco_result && $frasco_row = $frasco_result->fetch_assoc()) {
		$frasco_precio = $frasco_row['precio_unidad'];
	}
	
	// Calcular costos usando precios centralizados
	$precio_por_ml = $precios['Alcohol'] / 1000;
	$costo_alcohol = $alcohol_ml * $precio_por_ml;
	$costo_esencia = ($precios['Esencia_Difusor'] / 1000) * $esencia_g;
	
	// Para mantener compatibilidad con campos antiguos
	$alcohol_precio_l = $precios['Alcohol'];
	$esencia_precio_kg = $precios['Esencia_Difusor'];
	$frascos_precio_unidad = $frasco_precio;
	
	$costo_insumos = $costo_alcohol + $costo_esencia + $frasco_precio + $pack_extras;
	$costo_total = $costo_insumos;
}

$precio_venta = $costo_total * (1 + $ganancia_porcentaje / 100);

$stmt = $conn->prepare("INSERT INTO productos (tipo, nombre, material_g, material_precio_kg, esc_g, escencias_precio_kg, costo_insumos, alcohol_ml, alcohol_precio_l, esencia_g, esencia_precio_kg, frascos_precio_unidad, pack_extras, costo_total, ganancia_porcentaje, precio_venta, material_apf_g, material_vpf_g, tipo_frasco_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssdddddddddddiidddi", $tipo, $nombre, $material_g, $material_precio_kg, $esc_g, $escencias_precio_kg, $costo_insumos, $alcohol_ml, $alcohol_precio_l, $esencia_g, $esencia_precio_kg, $frascos_precio_unidad, $pack_extras, $costo_total, $ganancia_porcentaje, $precio_venta, $material_apf_g, $material_vpf_g, $tipo_frasco_id);
$stmt->execute();

header("Location: index.php");
exit;