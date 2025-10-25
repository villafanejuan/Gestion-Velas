<?php
$conn = new mysqli("localhost", "root", "", "vela");
if ($conn->connect_error) die("Error de conexión");

// Normaliza cadenas numéricas: acepta '1.234,56' o '1234.56' y devuelve float 1234.56
function parse_number($s) {
    $s = trim((string)$s);
    if ($s === '') return 0.0;
    if (strpos($s, ',') !== false) {
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
    }
    return floatval($s);
}

// Obtener precios actuales de materiales y esencias
$precios = [];
$result = $conn->query("SELECT nombre, precio_kg FROM precios_materiales");
while ($row = $result->fetch_assoc()) {
    $precios[$row['nombre']] = $row['precio_kg'];
}

// Obtener tipos de frascos
$frascos = $conn->query("SELECT * FROM tipos_frascos WHERE activo = 1 ORDER BY nombre");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar producto
    $id = intval($_POST['id']);
    $nombre = $_POST['nombre'];
    $ganancia_porcentaje = intval($_POST['ganancia_porcentaje']);
    
    // Obtener el producto actual para saber su tipo
    $res = $conn->query("SELECT tipo FROM productos WHERE id = $id");
    $prod = $res->fetch_assoc();
    
    if ($prod['tipo'] === 'vela') {
        // Nuevos campos de materiales
        $material_apf_g = parse_number($_POST['material_apf_g'] ?? 0);
        $material_vpf_g = parse_number($_POST['material_vpf_g'] ?? 0);
        $esc_g = parse_number($_POST['esc_g'] ?? 0);
        $pack_extras = parse_number($_POST['pack_extras'] ?? 0);
        
        // Calcular costos usando precios centralizados
        $costo_apf = ($precios['APF'] / 1000) * $material_apf_g;
        $costo_vpf = ($precios['VPF'] / 1000) * $material_vpf_g;
        $costo_escencias = ($precios['Esencias'] / 1000) * $esc_g;
        
        // Para mantener compatibilidad
        $material_precio_kg = $precios['APF'];
        $escencias_precio_kg = $precios['Esencias'];
        $material_g = $material_apf_g + $material_vpf_g;
        
        $costo_insumos = $costo_apf + $costo_vpf + $costo_escencias + $pack_extras;
        $costo_total = $costo_insumos;
        $precio_venta = $costo_total * (1 + $ganancia_porcentaje / 100);
        
        $stmt = $conn->prepare("UPDATE productos SET nombre=?, material_g=?, material_precio_kg=?, esc_g=?, escencias_precio_kg=?, costo_insumos=?, pack_extras=?, costo_total=?, ganancia_porcentaje=?, precio_venta=?, material_apf_g=?, material_vpf_g=? WHERE id=?");
        $stmt->bind_param("sdddddddiiddi", $nombre, $material_g, $material_precio_kg, $esc_g, $escencias_precio_kg, $costo_insumos, $pack_extras, $costo_total, $ganancia_porcentaje, $precio_venta, $material_apf_g, $material_vpf_g, $id);
        
    } else {
        // Para difusores con nuevo sistema
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
        
        // Para mantener compatibilidad
        $alcohol_precio_l = $precios['Alcohol'];
        $esencia_precio_kg = $precios['Esencia_Difusor'];
        $frascos_precio_unidad = $frasco_precio;
        
        $costo_insumos = $costo_alcohol + $costo_esencia + $frasco_precio + $pack_extras;
        $costo_total = $costo_insumos;
        $precio_venta = $costo_total * (1 + $ganancia_porcentaje / 100);
        
        $stmt = $conn->prepare("UPDATE productos SET nombre=?, alcohol_ml=?, alcohol_precio_l=?, esencia_g=?, esencia_precio_kg=?, frascos_precio_unidad=?, costo_insumos=?, pack_extras=?, costo_total=?, ganancia_porcentaje=?, precio_venta=?, tipo_frasco_id=? WHERE id=?");
        $stmt->bind_param("sddddddddidii", $nombre, $alcohol_ml, $alcohol_precio_l, $esencia_g, $esencia_precio_kg, $frascos_precio_unidad, $costo_insumos, $pack_extras, $costo_total, $ganancia_porcentaje, $precio_venta, $tipo_frasco_id, $id);
    }
    
    $stmt->execute();
    header("Location: index.php?msg=Producto actualizado");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM productos WHERE id = $id");
    $prod = $res->fetch_assoc();
    if (!$prod) die('Producto no encontrado');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar producto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-pink-100 to-yellow-100 min-h-screen py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-pink-600">Editar producto</h2>
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Volver</a>
            </div>
            
            <form method="POST" class="space-y-6">
                <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                
                <!-- Nombre -->
                <div>
                    <label class="block mb-1 font-semibold">Nombre:</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($prod['nombre']) ?>" class="w-full border border-gray-200 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-pink-200" required>
                </div>
                
                <?php if ($prod['tipo'] === 'vela'): ?>
                    <!-- Campos de vela con nuevo sistema -->
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h3 class="font-semibold text-blue-800 mb-3">Materiales (en gramos)</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1 text-sm font-medium">Material APF (g):</label>
                                <input type="number" step="0.01" name="material_apf_g" value="<?= number_format((float)($prod['material_apf_g'] ?? 0), 2, '.', '') ?>" class="w-full border border-blue-300 rounded-lg p-2 vela-campo focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <p class="text-xs text-blue-600 mt-1">Precio actual: $<?= number_format($precios['APF'] ?? 0, 2) ?>/kg</p>
                            </div>
                            <div>
                                <label class="block mb-1 text-sm font-medium">Material VPF (g):</label>
                                <input type="number" step="0.01" name="material_vpf_g" value="<?= number_format((float)($prod['material_vpf_g'] ?? 0), 2, '.', '') ?>" class="w-full border border-blue-300 rounded-lg p-2 vela-campo focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <p class="text-xs text-blue-600 mt-1">Precio actual: $<?= number_format($precios['VPF'] ?? 0, 2) ?>/kg</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                        <h3 class="font-semibold text-purple-800 mb-3">Esencias</h3>
                        <div>
                            <label class="block mb-1 text-sm font-medium">Esencias (g):</label>
                            <input type="number" step="0.01" name="esc_g" value="<?= number_format((float)$prod['esc_g'], 2, '.', '') ?>" class="w-full border border-purple-300 rounded-lg p-2 vela-campo focus:outline-none focus:ring-2 focus:ring-purple-400" required>
                            <p class="text-xs text-purple-600 mt-1">Precio actual: $<?= number_format($precios['Esencias'] ?? 0, 2) ?>/kg</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 font-medium">Pack/hs/extras ($):</label>
                            <input type="number" step="0.01" name="pack_extras" value="<?= number_format((float)$prod['pack_extras'], 2, '.', '') ?>" class="w-full border border-gray-200 rounded-lg p-2 vela-campo focus:outline-none focus:ring-2 focus:ring-pink-100" required>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Costo insumos ($):</label>
                            <input type="number" step="0.01" name="costo_insumos" value="<?= number_format((float)$prod['costo_insumos'], 2, '.', '') ?>" class="w-full border border-gray-200 rounded-lg p-2 bg-gray-50" readonly>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- Campos de difusor con nuevo sistema -->
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mb-4">
                        <h3 class="font-semibold text-blue-800 mb-3">Alcohol</h3>
                        <div>
                            <label class="block mb-1 text-sm font-medium">Alcohol (ml):</label>
                            <input type="number" step="0.01" name="alcohol_ml" value="<?= number_format((float)$prod['alcohol_ml'], 2, '.', '') ?>" class="w-full border border-blue-300 rounded-lg p-2 difusor-campo focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                            <p class="text-xs text-blue-600 mt-1">Precio actual: $<?= number_format($precios['Alcohol'] ?? 0, 2) ?>/L</p>
                        </div>
                    </div>
                    
                    <div class="bg-purple-50 p-4 rounded-lg border border-purple-200 mb-4">
                        <h3 class="font-semibold text-purple-800 mb-3">Esencia</h3>
                        <div>
                            <label class="block mb-1 text-sm font-medium">Esencia (g):</label>
                            <input type="number" step="0.01" name="esencia_g" value="<?= number_format((float)$prod['esencia_g'], 2, '.', '') ?>" class="w-full border border-purple-300 rounded-lg p-2 difusor-campo focus:outline-none focus:ring-2 focus:ring-purple-400" required>
                            <p class="text-xs text-purple-600 mt-1">Precio actual: $<?= number_format($precios['Esencia_Difusor'] ?? 0, 2) ?>/kg</p>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200 mb-4">
                        <h3 class="font-semibold text-green-800 mb-3">Frasco</h3>
                        <div>
                            <label class="block mb-1 text-sm font-medium">Tipo de Frasco:</label>
                            <select name="tipo_frasco_id" class="w-full border border-green-300 rounded-lg p-2 difusor-campo bg-white focus:outline-none focus:ring-2 focus:ring-green-400" required>
                                <option value="">Selecciona un frasco...</option>
                                <?php 
                                $frascos->data_seek(0); // Reset pointer
                                while($frasco = $frascos->fetch_assoc()): 
                                    $selected = ($frasco['id'] == $prod['tipo_frasco_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $frasco['id'] ?>" data-precio="<?= $frasco['precio_unidad'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($frasco['nombre']) ?> - $<?= number_format($frasco['precio_unidad'], 2) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 font-medium">Pack/hs/extras ($):</label>
                            <input type="number" step="0.01" name="pack_extras_difusor" value="<?= number_format((float)$prod['pack_extras'], 2, '.', '') ?>" class="w-full border border-gray-200 rounded-lg p-2 difusor-campo focus:outline-none focus:ring-2 focus:ring-pink-100" required>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Costo insumos ($):</label>
                            <input type="number" step="0.01" name="costo_insumos_difusor" value="<?= number_format((float)$prod['costo_insumos'], 2, '.', '') ?>" class="w-full border border-gray-200 rounded-lg p-2 bg-gray-50" readonly>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Ganancia -->
                <div>
                    <label class="block mb-1 font-semibold">Porcentaje de ganancia (%):</label>
                    <input type="number" name="ganancia_porcentaje" value="<?= $prod['ganancia_porcentaje'] ?>" class="w-full border border-gray-200 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-pink-100" required>
                </div>
                
                <!-- Botón guardar -->
                <button type="submit" class="w-full bg-gradient-to-r from-pink-500 to-pink-600 text-white px-6 py-3 rounded-lg hover:from-pink-600 hover:to-pink-700 shadow-md text-lg font-semibold">
                    Actualizar producto
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Precios obtenidos desde PHP
        const precios = {
            APF: <?= $precios['APF'] ?? 0 ?>,
            VPF: <?= $precios['VPF'] ?? 0 ?>,
            Esencias: <?= $precios['Esencias'] ?? 0 ?>,
            Alcohol: <?= $precios['Alcohol'] ?? 0 ?>,
            Esencia_Difusor: <?= $precios['Esencia_Difusor'] ?? 0 ?>
        };

        function calcularCostoInsumos() {
            const matApfG = parseFloat(document.querySelector('input[name="material_apf_g"]')?.value) || 0;
            const matVpfG = parseFloat(document.querySelector('input[name="material_vpf_g"]')?.value) || 0;
            const escG = parseFloat(document.querySelector('input[name="esc_g"]')?.value) || 0;
            const pack = parseFloat(document.querySelector('input[name="pack_extras"]')?.value) || 0;

            const costoApf = (precios.APF / 1000) * matApfG;
            const costoVpf = (precios.VPF / 1000) * matVpfG;
            const costoEscencias = (precios.Esencias / 1000) * escG;

            const costoTotal = costoApf + costoVpf + costoEscencias + pack;
            
            const costoInput = document.querySelector('input[name="costo_insumos"]');
            if (costoInput) {
                costoInput.value = costoTotal.toFixed(2);
            }
        }

        function calcularCostoInsumosDifusor() {
            const alcoholMl = parseFloat(document.querySelector('input[name="alcohol_ml"]')?.value) || 0;
            const esenciaG = parseFloat(document.querySelector('input[name="esencia_g"]')?.value) || 0;
            const pack = parseFloat(document.querySelector('input[name="pack_extras_difusor"]')?.value) || 0;
            
            // Obtener precio del frasco seleccionado
            const frascoSelect = document.querySelector('select[name="tipo_frasco_id"]');
            const frascoOption = frascoSelect?.options[frascoSelect.selectedIndex];
            const precioFrasco = parseFloat(frascoOption?.dataset?.precio) || 0;

            // Calcular costos usando precios centralizados
            const precioPorMl = precios.Alcohol / 1000;
            const costoAlcohol = alcoholMl * precioPorMl;
            const costoEsencia = (precios.Esencia_Difusor / 1000) * esenciaG;

            const costoTotal = costoAlcohol + costoEsencia + precioFrasco + pack;
            
            const costoInput = document.querySelector('input[name="costo_insumos_difusor"]');
            if (costoInput) {
                costoInput.value = costoTotal.toFixed(2);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.vela-campo').forEach(function(e) {
                e.addEventListener('input', calcularCostoInsumos);
            });
            
            document.querySelectorAll('.difusor-campo').forEach(function(e) {
                e.addEventListener('input', calcularCostoInsumosDifusor);
                e.addEventListener('change', calcularCostoInsumosDifusor);
            });
            
            calcularCostoInsumos();
            calcularCostoInsumosDifusor();
        });
    </script>
</body>
</html>
<?php } ?>
