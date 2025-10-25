<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "vela");
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

// Manejar actualizaciones de precios
if ($_POST) {
    $updates = [];
    $stmt = $conn->prepare("UPDATE precios_materiales SET precio_kg = ? WHERE nombre = ? AND tipo = ?");
    
    // Actualizar materiales
    if (isset($_POST['precio_apf'])) {
        $precio_apf = floatval($_POST['precio_apf']);
        $stmt->bind_param("dss", $precio_apf, $nombre_apf, $tipo_material);
        $nombre_apf = 'APF';
        $tipo_material = 'material';
        $stmt->execute();
        $updates[] = "Material APF: $" . number_format($precio_apf, 2);
    }
    
    if (isset($_POST['precio_vpf'])) {
        $precio_vpf = floatval($_POST['precio_vpf']);
        $stmt->bind_param("dss", $precio_vpf, $nombre_vpf, $tipo_material);
        $nombre_vpf = 'VPF';
        $tipo_material = 'material';
        $stmt->execute();
        $updates[] = "Material VPF: $" . number_format($precio_vpf, 2);
    }
    
    if (isset($_POST['precio_esencias'])) {
        $precio_esencias = floatval($_POST['precio_esencias']);
        $stmt->bind_param("dss", $precio_esencias, $nombre_esencias, $tipo_esencia);
        $nombre_esencias = 'Esencias';
        $tipo_esencia = 'esencia';
        $stmt->execute();
        $updates[] = "Esencias: $" . number_format($precio_esencias, 2);
    }
    
    // Recalcular precios de todas las velas
    if (!empty($updates)) {
        recalcularPreciosVelas($conn);
        $mensaje = "Precios actualizados: " . implode(", ", $updates) . ". Se han recalculado los precios de todas las velas.";
    }
}

// Obtener precios actuales
$precios = [];
$result = $conn->query("SELECT nombre, precio_kg, tipo FROM precios_materiales");
while ($row = $result->fetch_assoc()) {
    $precios[$row['nombre']] = $row['precio_kg'];
}

function recalcularPreciosVelas($conn) {
    // Obtener precios actuales
    $precios = [];
    $result = $conn->query("SELECT nombre, precio_kg FROM precios_materiales");
    while ($row = $result->fetch_assoc()) {
        $precios[$row['nombre']] = $row['precio_kg'];
    }
    
    // Recalcular todas las velas
    $velas = $conn->query("SELECT * FROM productos WHERE tipo = 'vela'");
    $update_stmt = $conn->prepare("UPDATE productos SET costo_insumos = ?, costo_total = ?, precio_venta = ? WHERE id = ?");
    
    while ($vela = $velas->fetch_assoc()) {
        $material_apf_g = floatval($vela['material_apf_g'] ?? 0);
        $material_vpf_g = floatval($vela['material_vpf_g'] ?? 0);
        $esc_g = floatval($vela['esc_g']);
        $pack_extras = floatval($vela['pack_extras']);
        
        // Calcular costos con precios actualizados
        $costo_apf = ($precios['APF'] / 1000) * $material_apf_g;
        $costo_vpf = ($precios['VPF'] / 1000) * $material_vpf_g;
        $costo_esencias = ($precios['Esencias'] / 1000) * $esc_g;
        
        $costo_insumos = $costo_apf + $costo_vpf + $costo_esencias + $pack_extras;
        $costo_total = $costo_insumos;
        $precio_venta = $costo_total * (1 + $vela['ganancia_porcentaje'] / 100);
        
        $update_stmt->bind_param("dddi", $costo_insumos, $costo_total, $precio_venta, $vela['id']);
        $update_stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Precios - Materiales y Esencias</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-pink-100 to-yellow-100 min-h-screen py-8">

    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-extrabold text-pink-600">Gestión de Precios</h1>
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Volver al inicio
                </a>
            </div>
            <p class="text-gray-600 mt-2">Actualiza los precios de materiales y esencias. Los cambios se aplicarán automáticamente a todas las velas.</p>
        </div>

        <!-- Mensaje de éxito -->
        <?php if (isset($mensaje)): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de precios -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <form method="POST" class="space-y-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Precios por Kilogramo</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Material APF -->
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <label for="precio_apf" class="block text-sm font-medium text-blue-800 mb-2">
                            Material APF ($/kg)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input 
                                type="number" 
                                step="0.01" 
                                id="precio_apf" 
                                name="precio_apf" 
                                value="<?= number_format($precios['APF'] ?? 0, 2, '.', '') ?>"
                                class="w-full pl-8 pr-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                                required
                            >
                        </div>
                    </div>

                    <!-- Material VPF -->
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <label for="precio_vpf" class="block text-sm font-medium text-green-800 mb-2">
                            Material VPF ($/kg)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input 
                                type="number" 
                                step="0.01" 
                                id="precio_vpf" 
                                name="precio_vpf" 
                                value="<?= number_format($precios['VPF'] ?? 0, 2, '.', '') ?>"
                                class="w-full pl-8 pr-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-400"
                                required
                            >
                        </div>
                    </div>

                    <!-- Esencias -->
                    <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                        <label for="precio_esencias" class="block text-sm font-medium text-purple-800 mb-2">
                            Esencias ($/kg)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input 
                                type="number" 
                                step="0.01" 
                                id="precio_esencias" 
                                name="precio_esencias" 
                                value="<?= number_format($precios['Esencias'] ?? 0, 2, '.', '') ?>"
                                class="w-full pl-8 pr-3 py-2 border border-purple-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-400"
                                required
                            >
                        </div>
                    </div>
                </div>

                <!-- Botón guardar -->
                <div class="pt-4">
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-pink-500 to-pink-600 text-white px-6 py-3 rounded-lg hover:from-pink-600 hover:to-pink-700 shadow-md text-lg font-semibold flex items-center justify-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Actualizar Precios y Recalcular Velas
                    </button>
                </div>
            </form>
        </div>

        <!-- Información adicional -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6">
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-yellow-800">
                    <h3 class="font-semibold mb-1">¿Cómo funciona?</h3>
                    <ul class="text-sm list-disc list-inside space-y-1">
                        <li>Al actualizar estos precios, se recalcularán automáticamente todas las velas existentes</li>
                        <li>Los precios se guardan por kilogramo y se aplican según los gramos usados en cada vela</li>
                        <li>Puedes usar Material APF, VPF o ambos en tus velas</li>
                        <li>Los cambios son inmediatos y afectan todos los productos de tipo "vela"</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
